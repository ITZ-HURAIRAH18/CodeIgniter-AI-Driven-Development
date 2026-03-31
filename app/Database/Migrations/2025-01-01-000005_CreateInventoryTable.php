<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'branch_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'product_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'quantity' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'reorder_level' => [
                'type'    => 'INT',
                'default' => 10,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['branch_id', 'product_id'], 'uq_branch_product');
        $this->forge->addKey('product_id', false, false, 'idx_inv_product');
        $this->forge->createTable('inventory', true);

        $this->db->query('ALTER TABLE inventory ADD CONSTRAINT fk_inv_branch FOREIGN KEY (branch_id) REFERENCES branches(id)');
        $this->db->query('ALTER TABLE inventory ADD CONSTRAINT fk_inv_product FOREIGN KEY (product_id) REFERENCES products(id)');
        $this->db->query('ALTER TABLE inventory ADD CONSTRAINT chk_qty CHECK (quantity >= 0)');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory', true);
    }
}
