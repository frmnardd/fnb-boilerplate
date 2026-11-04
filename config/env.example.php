<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - Environment Configuration Template
 * Copy this file to config/env.php and adjust values for your environment.
 */

return [
    'app' => [
        'name' => 'Kopi & Rasa Nusantara',
        'env' => 'development', // 'development' or 'production'
        'debug' => true,
        'url' => 'http://localhost:8000',
        'timezone' => 'Asia/Jakarta',
    ],

    'database' => [
        'driver' => 'sqlite', // 'mysql' or 'sqlite'
        'auto_fallback' => true, // Fallback to SQLite if MySQL is unavailable

        // MySQL Settings
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'fnb_boilerplate',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',

        // SQLite Settings
        'sqlite_path' => dirname(__DIR__) . '/database/fnb.sqlite',
    ],

    'session' => [
        'name' => 'fnb_session',
        'lifetime' => 86400, // 24 hours
        'secure' => false,   // Set true on HTTPS production
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'max_width' => 800,
        'quality' => 80, // WebP quality 80%
        'storage_path' => dirname(__DIR__) . '/storage/uploads',
        'public_path' => dirname(__DIR__) . '/public/uploads',
    ],
];
