<?php

declare(strict_types=1);

// SQLite default: PHAR içine yazılamaz, gerçek dosya sistemi kullan
$pharFile = \Phar::running(false);
$isPhar   = $pharFile !== '';

return [
    'db' => [
        // PHAR ile dağıtılınca sıfır-konfigürasyon için varsayılan SQLite
        'driver'  => $_ENV['DB_DRIVER'] ?? ($isPhar ? 'sqlite' : 'mysql'),
        'host'    => $_ENV['DB_HOST']   ?? 'localhost',
        'name'    => $_ENV['DB_NAME']   ?? 'domainhunter',
        'user'    => $_ENV['DB_USER']   ?? 'root',
        'pass'    => $_ENV['DB_PASS']   ?? '',
        'charset' => 'utf8mb4',
        // PHAR modunda CWD'ye yaz; normal modda proje altına
        'path'    => isset($_ENV['DB_PATH'])
            ? (($_ENV['DB_PATH'][0] === '/' || (strlen($_ENV['DB_PATH']) > 1 && $_ENV['DB_PATH'][1] === ':'))
                ? $_ENV['DB_PATH']
                : dirname(__DIR__) . DIRECTORY_SEPARATOR . $_ENV['DB_PATH'])
            : ($isPhar
                ? (getcwd() . DIRECTORY_SEPARATOR . 'domainhunter.sqlite')
                : dirname(__DIR__) . '/database/domainhunter.sqlite'),
    ],
    'app' => [
        'lang'        => strtolower($_ENV['APP_LANG'] ?? 'en'),
        'alert_email' => $_ENV['ALERT_EMAIL'] ?? '',
        'mailer_dsn'  => $_ENV['MAILER_DSN']  ?? '',
        'mailer_from' => $_ENV['MAILER_FROM'] ?? '',
        'per_page'    => 20,
    ],
    'twig' => [
        // phar:// URI olarak da çalışır — Twig 3 destekler
        'template_path' => dirname(__DIR__) . '/templates',
        'cache_path'    => false,
    ],
];
