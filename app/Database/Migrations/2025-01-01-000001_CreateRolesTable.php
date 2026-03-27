<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'TINYINT',
                'constraint'     => 3,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'description' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
                'null'    => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name', 'uq_role_name');
        $this->forge->createTable('roles', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('roles', true);
    }
}
