<?php

declare(strict_types=1);

use App\Repository\DomainRepository;
use App\Service\DomainService;
use App\Service\WhoisService;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$settings = require __DIR__ . '/../config/settings.php';

// Re-use the same PDO factory defined in config/container.php
require_once __DIR__ . '/../config/container.php';
$pdo = buildPdo($settings['db']);

$repository = new DomainRepository($pdo);
$whois      = new WhoisService();
$service    = new DomainService($whois, $repository, $settings['app']['alert_email']);
