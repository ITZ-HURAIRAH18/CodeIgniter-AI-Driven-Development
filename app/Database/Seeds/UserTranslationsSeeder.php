<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserTranslationsSeeder extends Seeder
{
    public function run()
    {
        // Check if translations already exist
        $existingCount = $this->db->table('user_translations')->countAllResults();
        
        if ($existingCount > 0) {
            echo "User translations already exist. Skipping...\n";
            return;
        }

        $data = [
            ['user_id' => 1, 'language' => 'en', 'name' => 'System Admin'],
            ['user_id' => 1, 'language' => 'ur', 'name' => 'نظام کا انتظامیہ'],
            ['user_id' => 1, 'language' => 'zh', 'name' => '系统管理员'],
            ['user_id' => 2, 'language' => 'en', 'name' => 'Branch Manager One'],
            ['user_id' => 2, 'language' => 'ur', 'name' => 'شاخ منیجر'],
            ['user_id' => 2, 'language' => 'zh', 'name' => '分支经理'],
            ['user_id' => 3, 'language' => 'en', 'name' => 'Sales User One'],
            ['user_id' => 3, 'language' => 'ur', 'name' => 'فروخت کار'],
            ['user_id' => 3, 'language' => 'zh', 'name' => '销售用户'],
        ];

        $this->db->table('user_translations')->insertBatch($data);
        echo "✅ Inserted " . count($data) . " user translation records\n";
    }
}
