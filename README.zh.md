# Domain Hunter

一款用于跟踪域名、监控 WHOIS 变更并发送邮件提醒的 PHP 应用程序。

支持 80+ 顶级域名、Punycode/IDN、SQLite/MySQL，同时提供 **Web 界面** 和 **CLI** 两种使用方式。

🌐 [Türkçe](README.tr.md) | [English](README.md) | [Deutsch](README.de.md) | [Español](README.es.md) | [Português](README.pt.md) | [Русский](README.ru.md) | **中文** | [日本語](README.ja.md)

## 环境要求

- PHP 8.1+
- `ext-intl`（Punycode 支持）
- `ext-pdo`、`ext-pdo_sqlite` 或 `ext-pdo_mysql`
- Composer

## 安装

### Web + CLI（完整安装）

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# 编辑 .env 文件
```

将 Web 服务器指向 `public/` 目录。

### 仅 CLI — PHAR（免安装）

```bash
# 下载可执行文件：
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

或全局安装：

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## 配置

`.env` 文件（使用 PHAR 时，放置于 `dh.phar` 旁或当前工作目录）：

```ini
# Web 界面语言：en | tr | de | es | pt | ru | zh | ja（默认：en）
APP_LANG=zh

# SQLite（默认 — 无需配置）
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# 邮件提醒（检测到变更时发送）
ALERT_EMAIL=admin@example.com

# SMTP（未设置时使用 PHP mail()）
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

`MAILER_DSN` 示例：

| 场景 | DSN |
|------|-----|
| 通用 SMTP | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| 服务器 MTA（Postfix 等） | `native://default` |
| 未配置（PHP mail()） | — 留空 —

### 数据库结构

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## CLI 使用方法

```bash
# 添加域名
dh domain:add example.com
dh domain:add türkiye.com.tr        # 自动转换 Punycode
dh domain:add shop.co.uk

# 查看列表
dh domain:list
dh domain:list --order=expiry       # 按到期日期排序
dh domain:list --order=updated
dh domain:list --format=csv         # CSV 输出

# 查询 / 刷新 WHOIS
dh domain:refresh                   # 所有域名
dh domain:refresh example.com       # 单个域名

# 删除域名
dh domain:delete example.com
dh domain:delete example.com --force
```

## 通过 Cron 自动监控

```bash
# 每晚 02:00 查询所有域名
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# 备选方案：cron.php 脚本
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## 编译 PHAR

```bash
make phar
# 或
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## 支持的顶级域名

### .tr 扩展名
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### 常见 ccTLD
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLD
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` 等

## 许可证

GNU General Public License v3.0 — 参见 [COPYING](COPYING)

原始项目：Copyright (C) 2011 Bahri Meriç CANLI
