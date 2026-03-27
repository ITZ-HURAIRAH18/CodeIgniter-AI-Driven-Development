<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// API v1 Routes — Multi-Branch Inventory & Order System
// All routes under /api/v1/
// Filters: cors (all), auth:jwt (protected), role (per-group)
// ============================================================

$routes->group('api/v1', ['filter' => 'cors'], function ($routes) {

    // ── Public endpoints ────────────────────────────────────────
    $routes->post('auth/login',   'Api\V1\AuthController::login');
    $routes->post('auth/refresh', 'Api\V1\AuthController::refresh');

    // ── Protected endpoints (JWT required) ──────────────────────
    $routes->group('', ['filter' => 'auth:jwt'], function ($routes) {

        // Auth
        $routes->post('auth/logout', 'Api\V1\AuthController::logout');
        $routes->get('auth/me',      'Api\V1\AuthController::me');

        // Branches — read: all roles; write: admin only
        $routes->get('branches',        'Api\V1\BranchController::index');
        $routes->get('branches/(:num)', 'Api\V1\BranchController::show/$1');

        $routes->group('branches', ['filter' => 'role:admin'], function ($routes) {
            $routes->post('',          'Api\V1\BranchController::create');
            $routes->put('(:num)',     'Api\V1\BranchController::update/$1');
            $routes->delete('(:num)',  'Api\V1\BranchController::delete/$1');
        });

        // Products — read: all; write: admin
        $routes->get('products',        'Api\V1\ProductController::index');
        $routes->get('products/(:num)', 'Api\V1\ProductController::show/$1');

        $routes->group('products', ['filter' => 'role:admin'], function ($routes) {
            $routes->post('',         'Api\V1\ProductController::create');
            $routes->put('(:num)',    'Api\V1\ProductController::update/$1');
            $routes->delete('(:num)', 'Api\V1\ProductController::delete/$1');
        });

        // Inventory — read: all; write: admin + branch_manager
        $routes->get('inventory',              'Api\V1\InventoryController::index');
        $routes->get('inventory/branch/(:num)', 'Api\V1\InventoryController::branch/$1');
        $routes->get('inventory/logs',         'Api\V1\InventoryController::logs');

        $routes->group('inventory', ['filter' => 'role:admin,branch_manager'], function ($routes) {
            $routes->post('add',    'Api\V1\InventoryController::add');
            $routes->post('adjust', 'Api\V1\InventoryController::adjust');
        });

        // Stock Transfers
        $routes->get('transfers',        'Api\V1\TransferController::index');
        $routes->get('transfers/(:num)', 'Api\V1\TransferController::show/$1');
        $routes->post('transfers',       'Api\V1\TransferController::create');

        $routes->group('transfers', ['filter' => 'role:admin,branch_manager'], function ($routes) {
            $routes->post('(:num)/approve',  'Api\V1\TransferController::approve/$1');
            $routes->post('(:num)/reject',   'Api\V1\TransferController::reject/$1');
            $routes->post('(:num)/complete', 'Api\V1\TransferController::complete/$1');
        });

        // Orders — all authenticated users can create; managers+ can list all
        $routes->get('orders',        'Api\V1\OrderController::index');
        $routes->get('orders/(:num)', 'Api\V1\OrderController::show/$1');
        $routes->post('orders',       'Api\V1\OrderController::create');
        $routes->post('orders/(:num)/cancel', 'Api\V1\OrderController::cancel/$1');
    });
});

// Catch-all for Vue.js SPA (if serving frontend from same domain)
// $routes->get('(.+)', function() { return view('spa'); }, ['filter' => 'cors']);
