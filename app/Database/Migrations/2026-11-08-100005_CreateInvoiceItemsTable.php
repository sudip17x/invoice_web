<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoiceItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'invoice_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'product_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255],
            'qty'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '1.00'],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'tax_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'],
            'line_total'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('invoice_id');
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('invoice_id', 'invoices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('invoice_items', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('invoice_items', true);
    }
}
