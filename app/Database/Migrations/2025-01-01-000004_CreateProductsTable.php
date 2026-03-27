<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sku' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cost_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'default'    => '0.0000',
            ],
            'sale_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'default'    => '0.0000',
            ],
            'tax_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => '0.00',
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => 'pcs',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sku', 'uq_sku');
        $this->forge->addKey('status', false, false, 'idx_status');
        $this->forge->createTable('products', true);

        $this->db->query('ALTER TABLE products ADD FULLTEXT KEY ft_product_search (name, sku)');
    }

    public function down(): void
    {
        $this->forge->dropTable('products', true);
    }
}
