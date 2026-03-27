<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================
// Root Route — API Status
// ============================================================
$routes->get('/', function() {
    return response()->setJSON(['status' => 'API Running', 'version' => '1.0.0', 'endpoint' => '/api/v1']);
});

// ============================================================
// API v1 Routes — Multi-Branch Inventory & Order System
// All routes under /api/v1/
// Filters: cors (all), auth:jwt (protected), role (per-group)
// ============================================================

$routes->group('api/v1', ['filter' => 'cors'], function ($routes) {

    // ── CORS Preflight Handler — OPTIONS for all API routes ────────────
    $routes->options('(.*)', 'Api\V1\TestController::corsPreFlight');

    // ── Test/Debug endpoints ────────────────────────────────────────
    $routes->get('test/health', 'Api\V1\TestController::health');
    $routes->get('test/db',     'Api\V1\TestController::db');
    $routes->post('test/login-debug', 'Api\V1\TestController::loginDebug');

    // ── Public endpoints ────────────────────────────────────────
    $routes->post('auth/login',   'Api\V1\AuthController::login');
    $routes->post('auth/refresh', 'Api\V1\AuthController::refresh');

    // ── Protected endpoints (JWT required) ──────────────────────
    $routes->group('', ['filter' => 'auth'], function ($routes) {

        // Auth
        $routes->post('auth/logout', 'Api\V1\AuthController::logout');
        $routes->get('auth/me',      'Api\V1\AuthController::me');

        // Users — read: all (for dropdowns/selects)
        $routes->get('users', 'Api\V1\UserController::index');

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

        // Inventory — read/write: admin + branch_manager
        $routes->group('inventory', ['filter' => 'role:admin,branch_manager'], function ($routes) {
            $routes->get('',        'Api\V1\InventoryController::index');
            $routes->get('branch/(:num)', 'Api\V1\InventoryController::branch/$1');
            $routes->get('logs',    'Api\V1\InventoryController::logs');
            $routes->post('add',    'Api\V1\InventoryController::add');
            $routes->post('adjust', 'Api\V1\InventoryController::adjust');
        });

        // Stock Transfers — read/write: admin + branch_manager
        $routes->group('transfers', ['filter' => 'role:admin,branch_manager'], function ($routes) {
            $routes->get('',                 'Api\V1\TransferController::index');
            $routes->get('(:num)',           'Api\V1\TransferController::show/$1');
            $routes->post('',                'Api\V1\TransferController::create');
            $routes->post('(:num)/approve',  'Api\V1\TransferController::approve/$1');
            $routes->post('(:num)/reject',   'Api\V1\TransferController::reject/$1');
            $routes->post('(:num)/complete', 'Api\V1\TransferController::complete/$1');
        });

        // Orders — read: all users; write: branch_manager + sales_user only (NOT admin)
        $routes->get('orders',        'Api\V1\OrderController::index');
        $routes->get('orders/(:num)', 'Api\V1\OrderController::show/$1');

        // Order creation restricted: Manager + Sales User only (NOT Admin)
        $routes->group('orders', ['filter' => 'role:branch_manager,sales_user'], function ($routes) {
            $routes->post('',                    'Api\V1\OrderController::create');
            $routes->post('(:num)/cancel',       'Api\V1\OrderController::cancel/$1');
        });
    });
});

// Catch-all for Vue.js SPA (if serving frontend from same domain)
// $routes->get('(.+)', function() { return view('spa'); }, ['filter' => 'cors']);
