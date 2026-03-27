<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Honeypot\Filters\HoneypotFilter;
use CodeIgniter\Security\Filters\CSRFFilter;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;

class Filters extends BaseFilters
{
    /**
     * Map alias → filter class.
     */
    public array $aliases = [
        'csrf'        => CSRFFilter::class,
        'toolbar'     => PerformanceMetrics::class,
        'performance' => PerformanceMetrics::class,
        'honeypot'    => HoneypotFilter::class,
        'pagecache'   => PageCache::class,
        'forcehttps'  => ForceHTTPS::class,
        // Custom filters
        'auth'        => \App\Filters\AuthJWTFilter::class,
        'role'        => \App\Filters\RoleFilter::class,
        'cors'        => \App\Filters\CorsFilter::class,
    ];

    /**
     * Always-run filters.
     */
    public array $globals = [
        'before' => [
            'cors',
            // 'forcehttps', // Uncomment in production
        ],
        'after' => [
            'toolbar',
            'cors',
        ],
    ];

    public array $methods = [];
    public array $filters = [];
}
