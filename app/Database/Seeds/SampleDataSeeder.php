<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Libraries\InvoiceCalculator;
use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = $this->db->table('users')->where('email', UserSeeder::ADMIN_EMAIL)->get()->getRowArray();

        if ($user === null || $this->db->table('customers')->where('user_id', $user['id'])->countAllResults() > 0) {
            return; // nothing to attach to, or already seeded
        }

        $uid = (int) $user['id'];
        $now = date('Y-m-d H:i:s');

        $this->db->table('settings')->insert([
            'user_id'        => $uid,
            'company_name'   => 'Demo Company Pvt Ltd',
            'address'        => "12 Park Street\nKolkata, West Bengal 700016",
            'phone'          => '+91 90000 00000',
            'email'          => 'billing@demo.example',
            'tax_id'         => '19ABCDE1234F1Z5',
            'bank_details'   => "Demo Bank\nA/C: 0000 0000 0000\nIFSC: DEMO0000001",
            'invoice_prefix' => 'INV',
            'footer_note'    => 'Thank you for your business.',
        ]);

        $customers = [
            ['Acme Traders', 'accounts@acme.example', '+91 98000 11111', "5 Lake Road\nKolkata", '19AAACA1111A1Z1'],
            ['Bluewave Solutions', 'hello@bluewave.example', '+91 98000 22222', "88 MG Road\nBengaluru", '29AAACB2222B1Z2'],
            ['Citrus Retail LLP', 'pay@citrus.example', '+91 98000 33333', "21 Linking Road\nMumbai", '27AAACC3333C1Z3'],
        ];
        $customerIds = [];

        foreach ($customers as [$name, $email, $phone, $address, $taxId]) {
            $this->db->table('customers')->insert([
                'user_id' => $uid, 'name' => $name, 'email' => $email, 'phone' => $phone,
                'address' => $address, 'tax_id' => $taxId, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $customerIds[] = (int) $this->db->insertID();
        }

        $products = [
            ['Web Development (per day)', 'Developer day rate', '500.00', '18.00'],
            ['Hosting (monthly)', 'Managed cloud hosting', '1200.00', '18.00'],
            ['Consulting (per hour)', 'Architecture / DevOps consulting', '800.00', '18.00'],
            ['Domain Renewal', '1 year', '900.00', '0.00'],
        ];
        $productRows = [];

        foreach ($products as [$name, $desc, $price, $tax]) {
            $this->db->table('products')->insert([
                'user_id' => $uid, 'name' => $name, 'description' => $desc, 'unit_price' => $price,
                'tax_percent' => $tax, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $productRows[] = ['id' => (int) $this->db->insertID(), 'name' => $name, 'price' => $price, 'tax' => $tax];
        }

        // [customer index, status, days ago issued, days until due (relative to issue), lines [product idx, qty], discount]
        $invoices = [
            [0, 'paid', 40, 15, [[0, 2], [3, 1]], 100],
            [1, 'sent', 20, 10, [[1, 3]], 0],
            [2, 'draft', 2, 14, [[2, 10], [0, 1]], 250],
        ];
        $seq = 1;

        foreach ($invoices as [$ci, $status, $ago, $terms, $lines, $discount]) {
            $issue = date('Y-m-d', strtotime("-{$ago} days"));
            $items = [];

            foreach ($lines as [$pi, $qty]) {
                $p       = $productRows[$pi];
                $items[] = [
                    'product_id' => $p['id'], 'description' => $p['name'],
                    'qty' => $qty, 'unit_price' => $p['price'], 'tax_percent' => $p['tax'],
                ];
            }

            $calc = InvoiceCalculator::calculate($items, (float) $discount);

            $this->db->table('invoices')->insert([
                'user_id'     => $uid,
                'customer_id' => $customerIds[$ci],
                'invoice_no'  => sprintf('INV-%d-%04d', (int) date('Y'), $seq++),
                'issue_date'  => $issue,
                'due_date'    => date('Y-m-d', strtotime($issue . " +{$terms} days")),
                'status'      => $status,
                'subtotal'    => $calc['subtotal'],
                'discount'    => $calc['discount'],
                'tax_total'   => $calc['tax_total'],
                'grand_total' => $calc['grand_total'],
                'notes'       => 'Sample invoice.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $invoiceId = (int) $this->db->insertID();

            foreach ($calc['items'] as $l) {
                $this->db->table('invoice_items')->insert([
                    'invoice_id' => $invoiceId, 'product_id' => $l['product_id'], 'description' => $l['description'],
                    'qty' => $l['qty'], 'unit_price' => $l['unit_price'], 'tax_percent' => $l['tax_percent'],
                    'line_total' => $l['line_total'],
                ]);
            }
        }
    }
}
