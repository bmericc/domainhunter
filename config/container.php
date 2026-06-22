<?php

declare(strict_types=1);

use App\Repository\DomainRepository;
use App\Service\DomainService;
use App\Service\WhoisService;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;

return [
    'settings' => fn() => require __DIR__ . '/settings.php',

    PDO::class => function (ContainerInterface $c) {
        $s = $c->get('settings')['db'];
        $dsn = "mysql:host={$s['host']};dbname={$s['name']};charset={$s['charset']}";
        return new PDO($dsn, $s['user'], $s['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    Twig::class => function (ContainerInterface $c) {
        $s = $c->get('settings')['twig'];
        return Twig::create($s['template_path'], ['cache' => $s['cache_path']]);
    },

    DomainRepository::class => fn(ContainerInterface $c) => new DomainRepository($c->get(PDO::class)),

    WhoisService::class => fn() => new WhoisService(),

    DomainService::class => fn(ContainerInterface $c) => new DomainService(
        $c->get(WhoisService::class),
        $c->get(DomainRepository::class),
        $c->get('settings')['app']['alert_email'],
    ),
];
