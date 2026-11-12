<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'invoice_no'  => ['type' => 'VARCHAR', 'constraint' => 40],
            'issue_date'  => ['type' => 'DATE'],
            'due_date'    => ['type' => 'DATE'],
            // draft | sent | paid | cancelled
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'subtotal'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'discount'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'tax_total'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'notes'       => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        // Numbers are sequential per user, so uniqueness is (user_id, invoice_no).
        $this->forge->addUniqueKey(['user_id', 'invoice_no']);
        $this->forge->addKey('customer_id');
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        // RESTRICT: a customer row can never vanish under an invoice (customers are soft-deleted anyway).
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('invoices', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('invoices', true);
    }
}
