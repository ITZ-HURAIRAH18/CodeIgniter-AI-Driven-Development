<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. Insert branches first (no manager yet — circular dependency)
        $this->db->table('branches')->insertBatch([
            ['id' => 1, 'name' => 'Main Branch', 'address' => '123 Main Street, City', 'phone' => '+1-000-0001', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'North Branch', 'address' => '456 North Ave, City',  'phone' => '+1-000-0002', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 2. Insert users
        $this->db->table('users')->insertBatch([
            [
                'role_id'   => 1,
                'branch_id' => null,
                'name'      => 'System Admin',
                'email'     => 'admin@system.com',
                'password'  => password_hash('Admin@12345',    PASSWORD_BCRYPT),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id'   => 2,
                'branch_id' => 1,
                'name'      => 'Branch Manager One',
                'email'     => 'manager@branch1.com',
                'password'  => password_hash('Manager@12345', PASSWORD_BCRYPT),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_id'   => 3,
                // No branch assignment for sales user
                'branch_id' => null,
                'name'      => 'Sales User One',
                'email'     => 'sales@branch1.com',
                'password'  => password_hash('Sales@12345',   PASSWORD_BCRYPT),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 3. Assign managers to branches
        $this->db->table('branches')->where('id', 1)->update(['manager_id' => 2]);

        // 4. Seed sample products
        $this->db->table('products')->insertBatch([
            ['sku' => 'PROD-001', 'name' => 'Widget A',   'cost_price' => '10.0000', 'sale_price' => '25.0000', 'tax_percentage' => '17.00', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['sku' => 'PROD-002', 'name' => 'Widget B',   'cost_price' => '20.0000', 'sale_price' => '50.0000', 'tax_percentage' => '17.00', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['sku' => 'PROD-003', 'name' => 'Gadget Pro', 'cost_price' => '80.0000', 'sale_price' => '150.0000','tax_percentage' => '17.00', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 5. Seed inventory for branches
        $this->db->table('inventory')->insertBatch([
            ['branch_id' => 1, 'product_id' => 1, 'quantity' => 100, 'reorder_level' => 10, 'updated_at' => $now],
            ['branch_id' => 1, 'product_id' => 2, 'quantity' => 50,  'reorder_level' => 5,  'updated_at' => $now],
            ['branch_id' => 1, 'product_id' => 3, 'quantity' => 25,  'reorder_level' => 5,  'updated_at' => $now],
            ['branch_id' => 2, 'product_id' => 1, 'quantity' => 75,  'reorder_level' => 10, 'updated_at' => $now],
            ['branch_id' => 2, 'product_id' => 2, 'quantity' => 30,  'reorder_level' => 5,  'updated_at' => $now],
        ]);
    }
}
