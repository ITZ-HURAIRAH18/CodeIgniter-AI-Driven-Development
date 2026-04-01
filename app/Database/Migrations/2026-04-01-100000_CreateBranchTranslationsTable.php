<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBranchTranslationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'branch_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'language' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['branch_id', 'language'], 'uq_branch_language');
        $this->forge->addKey('language', false, false, 'idx_language');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('branch_translations', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('branch_translations', true);
    }
}
