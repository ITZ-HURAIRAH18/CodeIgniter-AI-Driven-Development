<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAddressToBranchTranslations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('branch_translations', [
            'address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'description',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('branch_translations', 'address');
    }
}
