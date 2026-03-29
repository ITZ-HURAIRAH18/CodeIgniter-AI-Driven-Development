<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveBranchIdFromUsersTable extends Migration
{
    public function up(): void
    {
        // Drop the foreign key constraint first (safely)
        try {
            $this->db->query('ALTER TABLE users DROP FOREIGN KEY fk_users_branch');
        } catch (\Exception $e) {
            // Foreign key might not exist, continue
        }
        
        // Drop the column if it exists (safely)
        $fields = $this->db->getFieldData('users');
        $columnExists = false;
        foreach ($fields as $field) {
            if ($field->name === 'branch_id') {
                $columnExists = true;
                break;
            }
        }
        
        if ($columnExists) {
            $this->forge->dropColumn('users', 'branch_id');
        }
    }

    public function down(): void
    {
        // Re-add the column
        $this->forge->addColumn('users', [
            'branch_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'role_id',
            ],
        ]);
        
        // Re-add the foreign key
        $this->db->query('ALTER TABLE users ADD CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL');
    }
}
