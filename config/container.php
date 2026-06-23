<?php

declare(strict_types=1);

use App\Repository\DomainHistoryRepository;
use App\Repository\DomainRepository;
use App\Service\DomainService;
use App\Service\WhoisService;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

return [
    'settings' => fn() => require __DIR__ . '/settings.php',

    PDO::class => function (ContainerInterface $c) {
        $db = $c->get('settings')['db'];
        return buildPdo($db);
    },

    Twig::class => function (ContainerInterface $c) {
        $s    = $c->get('settings')['twig'];
        $twig = Twig::create($s['template_path'], ['cache' => $s['cache_path']]);

        $locale   = $c->get('settings')['app']['lang'];
        $langFile = dirname(__DIR__) . "/lang/{$locale}.php";
        if (!file_exists($langFile)) {
            $langFile = dirname(__DIR__) . '/lang/en.php';
        }
        $twig->getEnvironment()->addGlobal('t', require $langFile);

        return $twig;
    },

    DomainRepository::class        => fn(ContainerInterface $c) => new DomainRepository($c->get(PDO::class)),
    DomainHistoryRepository::class => fn(ContainerInterface $c) => new DomainHistoryRepository($c->get(PDO::class)),

    WhoisService::class => fn() => new WhoisService(),

    DomainService::class => function (ContainerInterface $c) {
        $app    = $c->get('settings')['app'];
        $mailer = $app['mailer_dsn'] !== ''
            ? new Mailer(Transport::fromDsn($app['mailer_dsn']))
            : null;
        return new DomainService(
            $c->get(WhoisService::class),
            $c->get(DomainRepository::class),
            $c->get(DomainHistoryRepository::class),
            $app['alert_email'],
            $mailer,
            $app['mailer_from'],
        );
    },
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
        $pdo = new PDO('sqlite:' . $db['path'], null, null, $options);
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS monitors (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                domain        TEXT    NOT NULL UNIQUE,
                register      TEXT    DEFAULT \'\',
                whois_serv    TEXT    DEFAULT \'\',
                ref_url       TEXT    DEFAULT \'\',
                nameserv1     TEXT    DEFAULT \'\',
                nameserv2     TEXT    DEFAULT \'\',
                nameserv3     TEXT    DEFAULT \'\',
                nameserv4     TEXT    DEFAULT \'\',
                nameserv5     TEXT    DEFAULT \'\',
                status1       TEXT    DEFAULT \'\',
                status2       TEXT    DEFAULT \'\',
                status3       TEXT    DEFAULT \'\',
                create_date   TEXT    DEFAULT \'\',
                update_date   TEXT    DEFAULT \'\',
                expirate_date TEXT    DEFAULT \'\',
                hunter_update TEXT    DEFAULT \'\'
            );
            CREATE TABLE IF NOT EXISTS monitor_history (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                domain_id  INTEGER NOT NULL,
                field      TEXT    NOT NULL,
                old_value  TEXT    DEFAULT \'\',
                new_value  TEXT    DEFAULT \'\',
                changed_at TEXT    NOT NULL
            );
        ');
        return $pdo;
    }

    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    return new PDO($dsn, $db['user'], $db['pass'], $options);
}
