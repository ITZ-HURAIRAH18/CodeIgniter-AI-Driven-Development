<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDateOfBirthToUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'date_of_birth' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'last_login',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'date_of_birth');
    }
}
