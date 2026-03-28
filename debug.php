<?php
require_once __DIR__ . '/vendor/autoload.php';

$config = \Config\Database::connect();

// Check branches assigned to manager (id=2)
$branches = $config->query("SELECT id, name, manager_id FROM branches WHERE manager_id = 2")->getResult();
echo "Branches for Manager ID 2:\n";
var_dump($branches);

// Check inventory for those branches
$inventory = $config->query("
  SELECT i.id, i.branch_id, i.quantity, i.reorder_level, 
         p.name as product_name, p.sale_price
  FROM inventory i
  JOIN products p ON p.id = i.product_id
  WHERE i.branch_id IN (SELECT id FROM branches WHERE manager_id = 2)
")->getResult();
echo "\nInventory for Manager's Branches:\n";
var_dump($inventory);

// Check orders
$orders = $config->query("
  SELECT id, branch_id, status FROM orders 
  WHERE branch_id IN (SELECT id FROM branches WHERE manager_id = 2)
  LIMIT 5
")->getResult();
echo "\nOrders for Manager's Branches:\n";
var_dump($orders);
