-- Domain Hunter - SQLite schema
-- Used when DB_DRIVER=sqlite in .env

CREATE TABLE IF NOT EXISTS monitors (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    domain        TEXT    NOT NULL DEFAULT '',
    register      TEXT    NOT NULL DEFAULT '',
    whois_serv    TEXT    NOT NULL DEFAULT '',
    ref_url       TEXT    NOT NULL DEFAULT '',
    nameserv1     TEXT    NOT NULL DEFAULT '',
    nameserv2     TEXT    NOT NULL DEFAULT '',
    nameserv3     TEXT    NOT NULL DEFAULT '',
    nameserv4     TEXT    NOT NULL DEFAULT '',
    nameserv5     TEXT    NOT NULL DEFAULT '',
    status1       TEXT    NOT NULL DEFAULT '',
    status2       TEXT    NOT NULL DEFAULT '',
    status3       TEXT    NOT NULL DEFAULT '',
    create_date   TEXT    DEFAULT NULL,
    update_date   TEXT    DEFAULT NULL,
    expirate_date TEXT    DEFAULT NULL,
    hunter_update TEXT    DEFAULT NULL,
    UNIQUE (domain)
);

CREATE INDEX IF NOT EXISTS idx_expirate ON monitors (expirate_date);
CREATE INDEX IF NOT EXISTS idx_hunter   ON monitors (hunter_update);
