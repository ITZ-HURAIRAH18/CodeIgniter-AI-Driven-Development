<?php

require 'vendor/autoload.php';

$_SERVER['CI_ENVIRONMENT'] = 'development';
require 'public/index.php';

$db = \Config\Database::connect();

// Check if translations already exist
$existingCount = $db->table('user_translations')->countAllResults();

if ($existingCount > 0) {
    echo "Translations already exist. Skipping insertion.\n";
    exit(0);
}

// Insert translations for existing users
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

foreach ($data as $row) {
    $db->table('user_translations')->insert($row);
}

echo "Inserted " . count($data) . " translation records successfully!\n";
