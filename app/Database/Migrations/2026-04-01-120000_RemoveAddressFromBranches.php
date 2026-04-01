<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveAddressFromBranches extends Migration
{
    public function up(): void
    {
        // Drop the redundant address column from branches table
        // All addresses are now stored in branch_translations table
        if ($this->db->getFieldData('branches', 'address')) {
            $this->forge->dropColumn('branches', 'address');
        }
    }

    public function down(): void
    {
        if (!$this->db->getFieldData('branches', 'address')) {
            $this->forge->addColumn('branches', [
                'address' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'name',
                ],
            ]);
        }
    }
}
