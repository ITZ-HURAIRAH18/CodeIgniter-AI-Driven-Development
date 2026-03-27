<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockTransfersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'from_branch_id' => ['type' => 'INT', 'unsigned' => true],
            'to_branch_id'   => ['type' => 'INT', 'unsigned' => true],
            'initiated_by'   => ['type' => 'INT', 'unsigned' => true],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'completed'],
                'default'    => 'pending',
            ],
            'notes'      => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('from_branch_id', false, false, 'idx_from');
        $this->forge->addKey('to_branch_id', false, false, 'idx_to');
        $this->forge->createTable('stock_transfers', true);

        $this->db->query('ALTER TABLE stock_transfers ADD CONSTRAINT fk_st_from FOREIGN KEY (from_branch_id) REFERENCES branches(id)');
        $this->db->query('ALTER TABLE stock_transfers ADD CONSTRAINT fk_st_to FOREIGN KEY (to_branch_id) REFERENCES branches(id)');
        $this->db->query('ALTER TABLE stock_transfers ADD CONSTRAINT fk_st_user FOREIGN KEY (initiated_by) REFERENCES users(id)');

        // Stock transfer items
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'transfer_id' => ['type' => 'INT', 'unsigned' => true],
            'product_id'  => ['type' => 'INT', 'unsigned' => true],
            'quantity'    => ['type' => 'INT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['transfer_id', 'product_id'], 'uq_transfer_product');
        $this->forge->createTable('stock_transfer_items', true);

        $this->db->query('ALTER TABLE stock_transfer_items ADD CONSTRAINT fk_sti_transfer FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id)');
        $this->db->query('ALTER TABLE stock_transfer_items ADD CONSTRAINT fk_sti_product FOREIGN KEY (product_id) REFERENCES products(id)');
        $this->db->query('ALTER TABLE stock_transfer_items ADD CONSTRAINT chk_qty_positive CHECK (quantity > 0)');
    }

    public function down(): void
    {
        $this->forge->dropTable('stock_transfer_items', true);
        $this->forge->dropTable('stock_transfers', true);
    }
}
