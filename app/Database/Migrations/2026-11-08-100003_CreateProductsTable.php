<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'tax_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('products', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('products', true);
    }
}
