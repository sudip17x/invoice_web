<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\CustomerModel;
use App\Models\InvoiceItemModel;
use App\Models\InvoiceModel;
use App\Models\ProductModel;
use App\Models\SettingModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DomainException;
use RuntimeException;
use Throwable;

/**
 * Invoice write operations. Controllers stay thin; every multi-table write
 * happens in one transaction, and totals are always recalculated server-side.
 */
class InvoiceService
{
    /** Allowed status transitions. Paid and Cancelled are final (read-only). */
    private const TRANSITIONS = [
        'draft' => ['sent', 'cancelled'],
        'sent'  => ['paid', 'cancelled'],
    ];

    private BaseConnection $db;
    private InvoiceModel $invoices;
    private InvoiceItemModel $items;

    public function __construct()
    {
        $this->db       = Database::connect();
        $this->invoices = new InvoiceModel();
        $this->items    = new InvoiceItemModel();
    }

    /**
     * @param array<string, mixed>              $header customer_id, issue_date, due_date, discount, notes
     * @param list<array<string, mixed>>        $rawItems
     */
    public function create(int $userId, array $header, array $rawItems): int
    {
        $this->assertCustomerOwned($userId, (int) $header['customer_id']);
        $calc   = $this->calculate($userId, $header, $rawItems);
        $prefix = (new SettingModel())->forUser($userId)['invoice_prefix'] ?: 'INV';
        $year   = (int) date('Y', strtotime((string) $header['issue_date']));

        $this->db->transBegin();

        try {
            $last      = $this->invoices->lastNumberForUpdate($userId, $prefix, $year);
            $invoiceNo = InvoiceNumber::next($last, $prefix, $year);

            $id = $this->invoices->skipValidation(true)->insert($this->headerRow($header, $calc) + [
                'user_id'    => $userId,
                'invoice_no' => $invoiceNo,
                'status'     => 'draft',
            ]);

            if ($id === false) {
                throw new RuntimeException('Could not save the invoice.');
            }

            $this->insertItems((int) $id, $calc['items']);
            $this->db->transCommit();

            return (int) $id;
        } catch (Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    /**
     * @param array<string, mixed>       $header
     * @param list<array<string, mixed>> $rawItems
     */
    public function update(int $userId, int $invoiceId, array $header, array $rawItems): void
    {
        $invoice = $this->requireInvoice($userId, $invoiceId);

        if ($invoice['status'] !== 'draft') {
            throw new DomainException('Only draft invoices can be edited.');
        }

        $this->assertCustomerOwned($userId, (int) $header['customer_id']);
        $calc = $this->calculate($userId, $header, $rawItems);

        $this->db->transBegin();

        try {
            $ok = $this->invoices->skipValidation(true)->update($invoiceId, $this->headerRow($header, $calc));

            if ($ok === false) {
                throw new RuntimeException('Could not update the invoice.');
            }

            $this->items->where('invoice_id', $invoiceId)->delete();
            $this->insertItems($invoiceId, $calc['items']);
            $this->db->transCommit();
        } catch (Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }
    }

    public function changeStatus(int $userId, int $invoiceId, string $newStatus): void
    {
        $invoice = $this->requireInvoice($userId, $invoiceId);
        $allowed = self::TRANSITIONS[$invoice['status']] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new DomainException(sprintf('Cannot change a %s invoice to %s.', $invoice['status'], $newStatus));
        }

        $this->invoices->skipValidation(true)->update($invoiceId, ['status' => $newStatus]);
    }

    public function delete(int $userId, int $invoiceId): void
    {
        $invoice = $this->requireInvoice($userId, $invoiceId);

        if (! in_array($invoice['status'], ['draft', 'cancelled'], true)) {
            throw new DomainException('Only draft or cancelled invoices can be deleted.');
        }

        $this->invoices->delete($invoiceId); // invoice_items go with it (FK ON DELETE CASCADE)
    }

    /**
     * @return array<string, mixed>
     */
    private function requireInvoice(int $userId, int $invoiceId): array
    {
        $invoice = $this->invoices->where('user_id', $userId)->find($invoiceId);

        if ($invoice === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Invoice not found.');
        }

        return $invoice;
    }

    private function assertCustomerOwned(int $userId, int $customerId): void
    {
        $found = (new CustomerModel())->where('user_id', $userId)->find($customerId);

        if ($found === null) {
            throw new DomainException('Please choose one of your customers.');
        }
    }

    /**
     * Server-side recalculation. product_id is kept only if the product belongs to the user.
     *
     * @param array<string, mixed>       $header
     * @param list<array<string, mixed>> $rawItems
     *
     * @return array{items: list<array<string, mixed>>, subtotal: float, discount: float, tax_total: float, grand_total: float}
     */
    private function calculate(int $userId, array $header, array $rawItems): array
    {
        $productModel = new ProductModel();
        $clean        = [];

        foreach ($rawItems as $row) {
            $productId = null;

            if (ctype_digit((string) ($row['product_id'] ?? '')) && (int) $row['product_id'] > 0) {
                $owned     = $productModel->withDeleted()->where('user_id', $userId)->find((int) $row['product_id']);
                $productId = $owned !== null ? (int) $row['product_id'] : null;
            }

            $clean[] = [
                'product_id'  => $productId,
                'description' => trim((string) $row['description']),
                'qty'         => $row['qty'],
                'unit_price'  => $row['unit_price'],
                'tax_percent' => $row['tax_percent'] ?? 0,
            ];
        }

        return InvoiceCalculator::calculate($clean, (float) ($header['discount'] ?? 0));
    }

    /**
     * @param array<string, mixed> $header
     * @param array<string, mixed> $calc
     *
     * @return array<string, mixed>
     */
    private function headerRow(array $header, array $calc): array
    {
        return [
            'customer_id' => (int) $header['customer_id'],
            'issue_date'  => $header['issue_date'],
            'due_date'    => $header['due_date'],
            'notes'       => ($header['notes'] ?? '') !== '' ? $header['notes'] : null,
            'subtotal'    => $calc['subtotal'],
            'discount'    => $calc['discount'],
            'tax_total'   => $calc['tax_total'],
            'grand_total' => $calc['grand_total'],
        ];
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function insertItems(int $invoiceId, array $lines): void
    {
        $rows = array_map(static fn (array $l): array => [
            'invoice_id'  => $invoiceId,
            'product_id'  => $l['product_id'],
            'description' => $l['description'],
            'qty'         => $l['qty'],
            'unit_price'  => $l['unit_price'],
            'tax_percent' => $l['tax_percent'],
            'line_total'  => $l['line_total'],
        ], $lines);

        if ($rows === [] || $this->items->skipValidation(true)->insertBatch($rows) === false) {
            throw new RuntimeException('Could not save invoice items.');
        }
    }
}
