# Domain Hunter

PHP-приложение для отслеживания доменных имён, мониторинга изменений WHOIS и отправки e-mail уведомлений.

Поддерживает 80+ TLD, Punycode/IDN, SQLite/MySQL и работает как через **веб-интерфейс**, так и через **CLI**.

🌐 [Türkçe](README.tr.md) | [English](README.md) | [Deutsch](README.de.md) | [Español](README.es.md) | [Português](README.pt.md) | **Русский** | [中文](README.zh.md) | [日本語](README.ja.md)

## Требования

- PHP 8.1+
- `ext-intl` (для Punycode)
- `ext-pdo`, `ext-pdo_sqlite` или `ext-pdo_mysql`
- Composer

## Установка

### Web + CLI (полная установка)

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# Отредактируйте файл .env
```

Направьте веб-сервер на директорию `public/`.

### Только CLI — PHAR (без установки)

```bash
# Скачать готовый бинарник:
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

Или установить глобально:

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## Конфигурация

Файл `.env` (при использовании PHAR — разместите рядом с `dh.phar` или в рабочей директории):

```ini
# Язык веб-интерфейса: en | tr | de | es | pt | ru | zh | ja  (по умолчанию: en)
APP_LANG=ru

# SQLite (по умолчанию — без настройки)
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# E-mail уведомления (отправлять при изменениях)
ALERT_EMAIL=admin@example.com

# SMTP (использует PHP mail() если не задан)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

Примеры `MAILER_DSN`:

| Сценарий | DSN |
|----------|-----|
| Обычный SMTP | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| Серверный MTA (Postfix и др.) | `native://default` |
| Не настроен (PHP mail()) | — оставить пустым —

### Схема базы данных

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## Использование CLI

```bash
# Добавить домен
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode конвертируется автоматически
dh domain:add shop.co.uk

# Список доменов
dh domain:list
dh domain:list --order=expiry       # сортировка по дате истечения
dh domain:list --order=updated
dh domain:list --format=csv         # вывод в CSV

# Запросить / обновить WHOIS
dh domain:refresh                   # все домены
dh domain:refresh example.com       # один домен

# Удалить домен
dh domain:delete example.com
dh domain:delete example.com --force
```

## Автоматический мониторинг через Cron

```bash
# Опрашивать все домены каждую ночь в 02:00
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# Альтернатива: скрипт cron.php
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## Сборка PHAR

```bash
make phar
# или
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## Поддерживаемые TLD

### Расширения .tr
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### Популярные ccTLD
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLD
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` и другие

## О проекте

[Domain Hunter](https://domainhunter.tr) — проект с открытым исходным кодом, опубликованный **[Bahri Meriç CANLI](https://bahri.info)**, разработчиком программного обеспечения из Турции. Запущенный в 2006 году под именем *domainhunter.org.tr* при поддержке LKD (Ассоциации пользователей Linux) и её почётного председателя Мустафы Акгюля, проект был полностью переписан для PHP 8+ с современным веб-интерфейсом, CLI, поддержкой SMTP и локализацией на 8 языков.

## Лицензия

GNU General Public License v3.0 — см. [COPYING](COPYING)

Copyright (C) 2011 Bahri Meriç CANLI
