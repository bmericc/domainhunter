# Domain Hunter

ドメイン名を追跡し、WHOIS の変更を監視して、メールでアラートを送信する PHP アプリケーションです。

80以上のTLD、Punycode/IDN、SQLite/MySQL に対応し、**Web インターフェース**と **CLI** の両方で動作します。

🌐 [Türkçe](README.tr.md) | [English](README.md) | [Deutsch](README.de.md) | [Español](README.es.md) | [Português](README.pt.md) | [Русский](README.ru.md) | [中文](README.zh.md) | **日本語**

## 動作要件

- PHP 8.2+
- `ext-intl`（Punycode 用）
- `ext-pdo`、`ext-pdo_sqlite` または `ext-pdo_mysql`
- Composer

## インストール

### Web + CLI（フルインストール）

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# .env ファイルを編集する
```

Web サーバーを `public/` ディレクトリに向けてください。

### CLI のみ — PHAR（インストール不要）

```bash
# 実行可能バイナリをダウンロード：
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

またはシステム全体にインストール：

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## 設定

`.env` ファイル（PHAR 使用時は `dh.phar` の隣または作業ディレクトリに配置）：

```ini
# Web インターフェースの言語: en | tr | de | es | pt | ru | zh | ja（デフォルト: en）
APP_LANG=ja

# SQLite（デフォルト — 設定不要）
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# メールアラート（変更検出時に通知）
ALERT_EMAIL=admin@example.com

# SMTP（未設定の場合は PHP mail() を使用）
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

`MAILER_DSN` の例：

| シナリオ | DSN |
|----------|-----|
| 一般的な SMTP | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| サーバー MTA（Postfix 等） | `native://default` |
| 未設定（PHP mail()） | — 空のまま —

### データベーススキーマ

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## CLI の使い方

```bash
# ドメインを追加
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode は自動変換されます
dh domain:add shop.co.uk

# 一覧表示
dh domain:list
dh domain:list --order=expiry       # 有効期限でソート
dh domain:list --order=updated
dh domain:list --format=csv         # CSV 出力

# WHOIS を照会 / 更新
dh domain:refresh                   # 全ドメイン
dh domain:refresh example.com       # 単一ドメイン

# ドメインを削除
dh domain:delete example.com
dh domain:delete example.com --force
```

## Cron による自動監視

```bash
# 毎夜 02:00 に全ドメインを照会
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# 代替: cron.php スクリプト
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## PHAR のビルド

```bash
make phar
# または
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## 対応 TLD

### .tr 拡張子
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### 主要な ccTLD
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLD
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` など

## プロジェクトについて

[Domain Hunter](https://domainhunter.tr) は、トルコのソフトウェア開発者 **[Bahri Meriç CANLI](https://bahri.info)** によって公開されたオープンソースプロジェクトです。2006 年に LKD（Linux ユーザー協会）および名誉会長 Mustafa Akgül の支援のもと *domainhunter.org.tr* として開始され、PHP 8+、モダンな Web インターフェース、CLI、SMTP サポート、8 言語対応で全面的に書き直されました。

## ライセンス

GNU General Public License v3.0 — [COPYING](COPYING) 参照

Copyright (C) 2011 Bahri Meriç CANLI
