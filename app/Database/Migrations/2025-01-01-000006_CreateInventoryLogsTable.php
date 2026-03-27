<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'branch_id'     => ['type' => 'INT', 'unsigned' => true],
            'product_id'    => ['type' => 'INT', 'unsigned' => true],
            'user_id'       => ['type' => 'INT', 'unsigned' => true],
            'movement_type' => [
                'type'       => 'ENUM',
                'constraint' => ['add', 'adjust', 'transfer_in', 'transfer_out', 'sale', 'return'],
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'reference_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'qty_before' => ['type' => 'INT'],
            'qty_change'  => ['type' => 'INT'],
            'qty_after'  => ['type' => 'INT'],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id', false, false, 'idx_log_branch');
        $this->forge->addKey('product_id', false, false, 'idx_log_product');
        $this->forge->addKey(['reference_type', 'reference_id'], false, false, 'idx_log_ref');
        $this->forge->addKey(['branch_id', 'created_at'], false, false, 'idx_log_branch_date');
        $this->forge->createTable('inventory_logs', true);

        $this->db->query('ALTER TABLE inventory_logs ADD CONSTRAINT fk_log_branch FOREIGN KEY (branch_id) REFERENCES branches(id)');
        $this->db->query('ALTER TABLE inventory_logs ADD CONSTRAINT fk_log_product FOREIGN KEY (product_id) REFERENCES products(id)');
        $this->db->query('ALTER TABLE inventory_logs ADD CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id)');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory_logs', true);
    }
}
