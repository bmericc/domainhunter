-- Domain Hunter - modernized schema
-- MySQL 5.7+ / MariaDB 10.3+

CREATE TABLE IF NOT EXISTS `monitors` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `domain`        VARCHAR(64)     NOT NULL DEFAULT '',
    `register`      VARCHAR(255)    NOT NULL DEFAULT '',
    `whois_serv`    VARCHAR(100)    NOT NULL DEFAULT '',
    `ref_url`       VARCHAR(100)    NOT NULL DEFAULT '',
    `nameserv1`     VARCHAR(100)    NOT NULL DEFAULT '',
    `nameserv2`     VARCHAR(100)    NOT NULL DEFAULT '',
    `nameserv3`     VARCHAR(100)    NOT NULL DEFAULT '',
    `nameserv4`     VARCHAR(100)    NOT NULL DEFAULT '',
    `nameserv5`     VARCHAR(100)    NOT NULL DEFAULT '',
    `status1`       VARCHAR(100)    NOT NULL DEFAULT '',
    `status2`       VARCHAR(100)    NOT NULL DEFAULT '',
    `status3`       VARCHAR(100)    NOT NULL DEFAULT '',
    `create_date`   DATE            DEFAULT NULL,
    `update_date`   DATE            DEFAULT NULL,
    `expirate_date` DATE            DEFAULT NULL,
    `hunter_update` DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `domain` (`domain`),
    KEY `idx_expirate` (`expirate_date`),
    KEY `idx_hunter`   (`hunter_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
