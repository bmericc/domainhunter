#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$settings   = require __DIR__ . '/../config/settings.php';
$s          = $settings['db'];
$dsn        = "mysql:host={$s['host']};dbname={$s['name']};charset={$s['charset']}";
$pdo        = new PDO($dsn, $s['user'], $s['pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$repository = new App\Repository\DomainRepository($pdo);
$whois      = new App\Service\WhoisService();
$service    = new App\Service\DomainService($whois, $repository, $settings['app']['alert_email']);

echo "[" . date('Y-m-d H:i:s') . "] Domain Hunter cron starting...\n";
$service->refreshAll();
echo "[" . date('Y-m-d H:i:s') . "] Done.\n";
