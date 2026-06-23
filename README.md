# Domain Hunter

A PHP application that tracks domain names, monitors WHOIS changes, and sends email alerts.

Supports 80+ TLDs, Punycode/IDN, SQLite/MySQL and works with both a **web interface** and **CLI**.

🌐 [Türkçe](README.tr.md) | **English** | [Deutsch](README.de.md) | [Español](README.es.md) | [Português](README.pt.md) | [Русский](README.ru.md) | [中文](README.zh.md) | [日本語](README.ja.md)

## Requirements

- PHP 8.1+
- `ext-intl` (for Punycode)
- `ext-pdo`, `ext-pdo_sqlite` or `ext-pdo_mysql`
- Composer

## Installation

### Web + CLI (full installation)

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# Edit the .env file
```

Point your web server to the `public/` directory.

### CLI only — PHAR (zero-install)

```bash
# Download the ready binary:
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

Or install system-wide:

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## Configuration

`.env` file (for PHAR usage, place next to `dh.phar` or in the working directory):

```ini
# Web interface language: en | tr | de | es | pt | ru | zh | ja  (default: en)
APP_LANG=en

# SQLite (default — zero-configuration)
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# Email alerts (notify on change)
ALERT_EMAIL=admin@example.com

# SMTP (falls back to PHP mail() if not set)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

`MAILER_DSN` examples:

| Scenario | DSN |
|----------|-----|
| Generic SMTP | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| Server MTA (Postfix etc.) | `native://default` |
| Not configured (PHP mail()) | — leave empty —

### Database Schema

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## CLI Usage

```bash
# Add a domain
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode converted automatically
dh domain:add shop.co.uk

# List domains
dh domain:list
dh domain:list --order=expiry       # sort by expiry date
dh domain:list --order=updated
dh domain:list --format=csv         # CSV output

# Query / refresh WHOIS
dh domain:refresh                   # all domains
dh domain:refresh example.com       # single domain

# Delete a domain
dh domain:delete example.com
dh domain:delete example.com --force
```

## Automated Monitoring with Cron

```bash
# Query all domains every night at 02:00
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# Alternative: cron.php script
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## Building the PHAR

```bash
make phar
# or
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## Supported TLDs

### .tr Extensions
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### Popular ccTLDs
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLDs
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` and more

## License

GNU General Public License v3.0 — see [COPYING](COPYING)

Original project: Copyright (C) 2011 Bahri Meriç CANLI
