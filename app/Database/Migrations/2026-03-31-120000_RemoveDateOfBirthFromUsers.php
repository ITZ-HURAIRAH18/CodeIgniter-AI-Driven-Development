<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDateOfBirthFromUsers extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('users', 'date_of_birth');
    }

    public function down()
    {
        $this->forge->addColumn('users', [
            'date_of_birth' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
        ]);
    }
}
