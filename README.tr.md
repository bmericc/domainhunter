# Domain Hunter

<p align="center">
  <a href="https://github.com/bmericc/domainhunter/actions/workflows/tests.yml"><img src="https://github.com/bmericc/domainhunter/actions/workflows/tests.yml/badge.svg" alt="tests"></a>
  <a href="https://scorecard.dev/viewer/?uri=github.com/bmericc/domainhunter"><img src="https://api.scorecard.dev/projects/github.com/bmericc/domainhunter/badge" alt="OpenSSF Scorecard"></a>
  <a href="https://github.com/bmericc/domainhunter/releases/latest"><img src="https://img.shields.io/github/v/release/bmericc/domainhunter" alt="Latest release"></a>
  <a href="https://github.com/bmericc/domainhunter/actions/workflows/tests.yml"><img src="https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4%20tested-777BB4?logo=php&logoColor=white" alt="PHP 8.2 | 8.3 | 8.4 tested"></a>
  <a href="COPYING"><img src="https://img.shields.io/badge/license-GPL--3.0-blue" alt="GPL-3.0"></a>
  <a href="https://github.com/sponsors/bmericc"><img src="https://img.shields.io/badge/sponsor-%E2%9D%A4-ea4aaa?logo=githubsponsors" alt="Sponsor"></a>
</p>

Domain adlarını takip eden, WHOIS değişikliklerini izleyen ve e-posta uyarısı gönderen PHP uygulaması.

80+ TLD desteği, Punycode/IDN, SQLite/MySQL ve hem **web arayüzü** hem **CLI** ile çalışır.

🌐 **Türkçe** | [English](README.md) | [Deutsch](README.de.md) | [Español](README.es.md) | [Português](README.pt.md) | [Русский](README.ru.md) | [中文](README.zh.md) | [日本語](README.ja.md)

## Gereksinimler

- PHP 8.2+
- `ext-intl` (Punycode için)
- `ext-pdo`, `ext-pdo_sqlite` veya `ext-pdo_mysql`
- Composer

## Kurulum

### Web + CLI (tam kurulum)

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# .env dosyasını düzenleyin
```

Web sunucusunu `public/` dizinine yönlendirin.

### Sadece CLI — PHAR (sıfır-kurulum)

```bash
# Hazır binary'i indirin:
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

Ya da sistem geneline kurun:

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## Yapılandırma

`.env` dosyası (PHAR kullanımında `dh.phar` yanına ya da çalışma dizinine):

```ini
# Web arayüzü dili: en | tr | de | es | pt | ru | zh | ja  (varsayılan: en)
APP_LANG=tr

# SQLite (varsayılan — sıfır-konfigürasyon)
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# E-posta uyarıları (değişiklik olunca bildir)
ALERT_EMAIL=admin@example.com

# SMTP (ayarlanmazsa PHP mail() kullanılır)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

`MAILER_DSN` örnekleri:

| Senaryo | DSN |
|---------|-----|
| Genel SMTP | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| Sunucu MTA (Postfix vb.) | `native://default` |
| Ayarsız (PHP mail()) | — boş bırakın —

### Veritabanı Şeması

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## CLI Kullanımı

```bash
# Domain ekle
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode otomatik dönüşür
dh domain:add shop.co.uk

# Listeye bak
dh domain:list
dh domain:list --order=expiry       # son kullanma tarihine göre sırala
dh domain:list --order=updated
dh domain:list --format=csv         # CSV çıktısı

# WHOIS sorgula / güncelle
dh domain:refresh                   # tüm domainler
dh domain:refresh example.com       # tek domain

# Domain sil
dh domain:delete example.com
dh domain:delete example.com --force
```

## Cron ile Otomatik Takip

```bash
# Her gece saat 02:00'de tüm domainleri sorgula
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# Alternatif: cron.php scripti
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## PHAR Derleme

```bash
make phar
# veya
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## Desteklenen TLD'ler

### .tr Uzantıları
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### Popüler ccTLD'ler
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLD'ler
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` ve daha fazlası

## Hakkında

[Domain Hunter](https://domainhunter.tr), Türkiye'den yazılım geliştirici **[Bahri Meriç CANLI](https://bahri.info)** tarafından yayımlanmış açık kaynaklı bir projedir. 2006 yılında LKD (Linux Kullanıcıları Derneği) ve onursal başkanı Mustafa Akgül'ün desteğiyle *domainhunter.org.tr* adıyla başlatılan proje, PHP 8+, modern web arayüzü, CLI, SMTP desteği ve 8 dil yerelleştirmesiyle tamamen yeniden yazılmıştır.

## Lisans

[GNU General Public License v3.0 veya sonrası](COPYING) (GPL-3.0-or-later) ile lisanslanmıştır.

Copyright (C) 2011 Bahri Meriç CANLI
