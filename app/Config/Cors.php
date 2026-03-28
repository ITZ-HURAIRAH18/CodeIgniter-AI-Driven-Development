<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{
    public array $default = [

        // ✅ Allow all origins (for development/debugging)
        'allowedOrigins' => ['*'],

        // Optional (keep empty unless needed)
        'allowedOriginsPatterns' => [],

        // ❌ Keep false unless using cookies/auth sessions
        'supportsCredentials' => false,

        // ✅ Important headers
        'allowedHeaders' => [
            'Content-Type',
            'Authorization',
            'X-Requested-With'
        ],

        // Optional
        'exposedHeaders' => [],

        // ✅ Allow all required methods
        'allowedMethods' => [
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'OPTIONS'
        ],

        // Cache preflight request
        'maxAge' => 7200,
    ];
}