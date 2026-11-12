<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 120],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 190],
            'password_hash'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'user'],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            // login throttle (bonus): 5 failures => locked for 10 minutes
            'failed_attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'locked_until'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
