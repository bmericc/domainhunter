<?php

declare(strict_types=1);

use App\Action\DomainAddAction;
use App\Action\DomainDeleteAction;
use App\Action\DomainDetailAction;
use App\Action\DomainListAction;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

    $app->get('/', DomainListAction::class);
    $app->get('/domains/add', DomainAddAction::class);
    $app->post('/domains/add', DomainAddAction::class);
    $app->get('/domains/{id:[0-9]+}', DomainDetailAction::class);
    $app->post('/domains/{id:[0-9]+}/delete', DomainDeleteAction::class);
};
