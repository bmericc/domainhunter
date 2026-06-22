<?php

declare(strict_types=1);

return [
    'db' => [
        'driver'  => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host'    => $_ENV['DB_HOST']   ?? 'localhost',
        'name'    => $_ENV['DB_NAME']   ?? 'domainhunter',
        'user'    => $_ENV['DB_USER']   ?? 'root',
        'pass'    => $_ENV['DB_PASS']   ?? '',
        'charset' => 'utf8mb4',
        'path'    => $_ENV['DB_PATH']   ?? dirname(__DIR__) . '/database/domainhunter.sqlite',
    ],
    'app' => [
        'alert_email' => $_ENV['ALERT_EMAIL'] ?? '',
        'per_page'    => 20,
    ],
    'twig' => [
        'template_path' => dirname(__DIR__) . '/templates',
        'cache_path'    => false,
    ],
];
