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
        $db = $c->get('settings')['db'];
        return buildPdo($db);
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

function buildPdo(array $db): PDO
{
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($db['driver'] === 'sqlite') {
        $dir = dirname($db['path']);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return new PDO('sqlite:' . $db['path'], null, null, $options);
    }

    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    return new PDO($dsn, $db['user'], $db['pass'], $options);
}
