<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public const ADMIN_EMAIL    = 'admin@example.com';
    public const ADMIN_PASSWORD = 'Admin@12345';

    public function run(): void
    {
        $exists = $this->db->table('users')->where('email', self::ADMIN_EMAIL)->countAllResults();

        if ($exists > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'name'          => 'Admin',
            'email'         => self::ADMIN_EMAIL,
            'password_hash' => password_hash(self::ADMIN_PASSWORD, PASSWORD_DEFAULT),
            'role'          => 'admin',
            'is_active'     => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    }
}
