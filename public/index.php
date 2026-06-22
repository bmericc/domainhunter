<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

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
