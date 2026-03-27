<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'    => ['type' => 'INT', 'unsigned' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true],
            'order_number' => ['type' => 'VARCHAR', 'constraint' => 30],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'completed', 'cancelled'],
                'default'    => 'pending',
            ],
            'subtotal'    => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => '0.0000'],
            'tax_amount'  => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => '0.0000'],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => '0.0000'],
            'notes'       => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => false],
            'updated_at'  => ['type' => 'DATETIME', 'null' => false],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('order_number', 'uq_order_number');
        $this->forge->addKey('branch_id', false, false, 'idx_order_branch');
        $this->forge->addKey('user_id', false, false, 'idx_order_user');
        $this->forge->addKey('status', false, false, 'idx_order_status');
        $this->forge->addKey('created_at', false, false, 'idx_order_created');
        $this->forge->createTable('orders', true);

        $this->db->query('ALTER TABLE orders ADD CONSTRAINT fk_order_branch FOREIGN KEY (branch_id) REFERENCES branches(id)');
        $this->db->query('ALTER TABLE orders ADD CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id)');

        // Order items
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'order_id'   => ['type' => 'INT', 'unsigned' => true],
            'product_id' => ['type' => 'INT', 'unsigned' => true],
            'quantity'   => ['type' => 'INT'],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
            'tax_pct'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'],
            'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => '0.0000'],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id', false, false, 'idx_oi_order');
        $this->forge->addKey('product_id', false, false, 'idx_oi_product');
        $this->forge->createTable('order_items', true);

        $this->db->query('ALTER TABLE order_items ADD CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id)');
        $this->db->query('ALTER TABLE order_items ADD CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id)');

        // JWT token blacklist
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'token_jti'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_jti', 'uq_jti');
        $this->forge->addKey('expires_at', false, false, 'idx_expires');
        $this->forge->createTable('token_blacklist', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('order_items', true);
        $this->forge->dropTable('orders', true);
        $this->forge->dropTable('token_blacklist', true);
    }
}
