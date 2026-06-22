<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// .env'i PHAR dışında ara: PHAR yanı veya proje kökü
$pharFile = \Phar::running(false);
$envDir   = $pharFile !== '' ? dirname($pharFile) : dirname(__DIR__);
Dotenv\Dotenv::createImmutable($envDir)->safeLoad();

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/container.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(
    displayErrorDetails: (bool) ($_ENV['APP_DEBUG'] ?? false),
    logErrors: true,
    logErrorDetails: true,
);

(require __DIR__ . '/../config/routes.php')($app);

$app->run();
