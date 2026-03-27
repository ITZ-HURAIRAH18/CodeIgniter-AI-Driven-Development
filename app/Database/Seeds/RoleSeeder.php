<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'name' => 'admin',          'description' => 'System Administrator with full access', 'created_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'branch_manager',  'description' => 'Branch Manager with branch-level access', 'created_at' => date('Y-m-d H:i:s')],
            ['id' => 3, 'name' => 'sales_user',      'description' => 'Sales User with order creation access', 'created_at' => date('Y-m-d H:i:s')],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}
