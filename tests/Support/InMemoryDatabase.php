<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * Builds an in-memory SQLite PDO with the same schema config/container.php
 * creates at runtime, so repository/service tests exercise real SQL.
 */
final class InMemoryDatabase
{
    public static function create(): \PDO
    {
        $pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        $pdo->exec('
            CREATE TABLE monitors (
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
            CREATE TABLE monitor_history (
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
}
