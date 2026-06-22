<?php

declare(strict_types=1);

use App\Repository\DomainRepository;
use App\Service\DomainService;
use App\Service\WhoisService;

require __DIR__ . '/../vendor/autoload.php';

// .env'i PHAR dışında ara: önce PHAR yanında, sonra CWD'de
$pharFile = \Phar::running(false);
if ($pharFile !== '') {
    $pharDir = dirname($pharFile);
    $envDir  = file_exists($pharDir . '/.env') ? $pharDir : (getcwd() ?: $pharDir);
} else {
    $envDir = dirname(__DIR__);
}
Dotenv\Dotenv::createImmutable($envDir)->safeLoad();

$settings = require __DIR__ . '/../config/settings.php';

require_once __DIR__ . '/../config/container.php';
$pdo = buildPdo($settings['db']);

$repository = new DomainRepository($pdo);
$whois      = new WhoisService();
$service    = new DomainService($whois, $repository, $settings['app']['alert_email']);
