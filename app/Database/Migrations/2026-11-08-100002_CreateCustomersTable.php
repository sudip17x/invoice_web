<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'address'    => ['type' => 'TEXT', 'null' => true],
            'tax_id'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customers', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('customers', true);
    }
}
