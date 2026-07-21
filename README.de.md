# Domain Hunter

Eine PHP-Anwendung zur Überwachung von Domains, Verfolgung von WHOIS-Änderungen und Versand von E-Mail-Benachrichtigungen.

Unterstützt 80+ TLDs, Punycode/IDN, SQLite/MySQL und funktioniert sowohl mit einem **Webinterface** als auch über die **CLI**.

🌐 [Türkçe](README.tr.md) | [English](README.md) | **Deutsch** | [Español](README.es.md) | [Português](README.pt.md) | [Русский](README.ru.md) | [中文](README.zh.md) | [日本語](README.ja.md)

## Voraussetzungen

- PHP 8.2+
- `ext-intl` (für Punycode)
- `ext-pdo`, `ext-pdo_sqlite` oder `ext-pdo_mysql`
- Composer

## Installation

### Web + CLI (Vollinstallation)

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# .env-Datei bearbeiten
```

Webserver auf das `public/`-Verzeichnis zeigen lassen.

### Nur CLI — PHAR (ohne Installation)

```bash
# Fertiges Binary herunterladen:
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

Oder systemweit installieren:

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## Konfiguration

`.env`-Datei (bei PHAR-Nutzung neben `dh.phar` oder im Arbeitsverzeichnis ablegen):

```ini
# Sprache des Webinterfaces: en | tr | de | es | pt | ru | zh | ja  (Standard: en)
APP_LANG=de

# SQLite (Standard — keine Konfiguration nötig)
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# E-Mail-Benachrichtigungen (bei Änderungen benachrichtigen)
ALERT_EMAIL=admin@example.com

# SMTP (nutzt PHP mail() falls nicht gesetzt)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

`MAILER_DSN`-Beispiele:

| Szenario | DSN |
|----------|-----|
| Allgemeines SMTP | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| Server-MTA (Postfix usw.) | `native://default` |
| Nicht konfiguriert (PHP mail()) | — leer lassen —

### Datenbankschema

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## CLI-Nutzung

```bash
# Domain hinzufügen
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode wird automatisch konvertiert
dh domain:add shop.co.uk

# Domains auflisten
dh domain:list
dh domain:list --order=expiry       # nach Ablaufdatum sortieren
dh domain:list --order=updated
dh domain:list --format=csv         # CSV-Ausgabe

# WHOIS abfragen / aktualisieren
dh domain:refresh                   # alle Domains
dh domain:refresh example.com       # einzelne Domain

# Domain löschen
dh domain:delete example.com
dh domain:delete example.com --force
```

## Automatische Überwachung mit Cron

```bash
# Alle Domains jeden Abend um 02:00 Uhr abfragen
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# Alternative: cron.php-Skript
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## PHAR kompilieren

```bash
make phar
# oder
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## Unterstützte TLDs

### .tr-Endungen
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### Beliebte ccTLDs
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLDs
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` und mehr

## Über das Projekt

[Domain Hunter](https://domainhunter.tr) ist ein Open-Source-Projekt, veröffentlicht von **[Bahri Meriç CANLI](https://bahri.info)**, einem Softwareentwickler aus der Türkei. Das Projekt wurde 2006 als *domainhunter.org.tr* mit Unterstützung der LKD (Linux-Benutzervereinigung) und ihres Ehrenvorsitzenden Mustafa Akgül gestartet und wurde vollständig für PHP 8+ mit modernem Webinterface, CLI, SMTP-Unterstützung und 8-sprachiger Lokalisierung neu geschrieben.

## Lizenz

GNU General Public License v3.0 — siehe [COPYING](COPYING)

Copyright (C) 2011 Bahri Meriç CANLI
