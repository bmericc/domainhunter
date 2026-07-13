<?php

declare(strict_types=1);

use App\Repository\DomainHistoryRepository;
use App\Repository\DomainRepository;
use App\Service\DomainService;
use BahriCanli\DomainHunter\DomainParser;
use BahriCanli\DomainHunter\WhoisService;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

require __DIR__ . '/../vendor/autoload.php';

$pharFile = \Phar::running(false);
if ($pharFile !== '') {
    $home    = getenv('HOME') ?: getenv('USERPROFILE') ?: '';
    $dataDir = $home . DIRECTORY_SEPARATOR . '.domainhunter';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0700, true);
    }
    $envDir = $dataDir;
} else {
    $envDir = dirname(__DIR__);
}
Dotenv\Dotenv::createImmutable($envDir)->safeLoad();

$settings = require __DIR__ . '/../config/settings.php';

require_once __DIR__ . '/../config/container.php';
$pdo = buildPdo($settings['db']);

$repository = new DomainRepository($pdo);
$history    = new DomainHistoryRepository($pdo);
$whois      = new WhoisService();
$parser     = new DomainParser($whois);

$app    = $settings['app'];
$mailer = $app['mailer_dsn'] !== ''
    ? new Mailer(Transport::fromDsn($app['mailer_dsn']))
    : null;

$service = new DomainService($whois, $parser, $repository, $history, $app['alert_email'], $mailer, $app['mailer_from']);
