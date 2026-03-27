<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type'     => 'TINYINT',
                'constraint' => 3,
                'unsigned'  => true,
                'null'      => false,
            ],
            'branch_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email', 'uq_email');
        $this->forge->addKey('role_id', false, false, 'idx_role');
        $this->forge->addKey('branch_id', false, false, 'idx_branch');
        $this->forge->createTable('users', true);

        // Foreign keys
        $this->db->query('ALTER TABLE users ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)');
        $this->db->query('ALTER TABLE users ADD CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL');
        $this->db->query('ALTER TABLE branches ADD CONSTRAINT fk_branches_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE branches DROP FOREIGN KEY fk_branches_manager');
        $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_role');
        $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_branch');
        $this->forge->dropTable('users', true);
    }
}
