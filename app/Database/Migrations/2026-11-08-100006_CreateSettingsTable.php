<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'company_name'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'address'        => ['type' => 'TEXT', 'null' => true],
            'phone'          => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'          => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'tax_id'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'logo_path'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'bank_details'   => ['type' => 'TEXT', 'null' => true],
            'invoice_prefix' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'INV'],
            'footer_note'    => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('settings', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('settings', true);
    }
}
