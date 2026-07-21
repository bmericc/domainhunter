# Domain Hunter

Uma aplicação PHP que rastreia nomes de domínio, monitora alterações no WHOIS e envia alertas por e-mail.

Suporta 80+ TLDs, Punycode/IDN, SQLite/MySQL e funciona tanto com uma **interface web** quanto com **CLI**.

🌐 [Türkçe](README.tr.md) | [English](README.md) | [Deutsch](README.de.md) | [Español](README.es.md) | **Português** | [Русский](README.ru.md) | [中文](README.zh.md) | [日本語](README.ja.md)

## Requisitos

- PHP 8.2+
- `ext-intl` (para Punycode)
- `ext-pdo`, `ext-pdo_sqlite` ou `ext-pdo_mysql`
- Composer

## Instalação

### Web + CLI (instalação completa)

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# Editar o arquivo .env
```

Aponte seu servidor web para o diretório `public/`.

### Apenas CLI — PHAR (sem instalação)

```bash
# Baixar o binário pronto:
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

Ou instalar globalmente:

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## Configuração

Arquivo `.env` (para uso com PHAR, coloque ao lado do `dh.phar` ou no diretório de trabalho):

```ini
# Idioma da interface web: en | tr | de | es | pt | ru | zh | ja  (padrão: en)
APP_LANG=pt

# SQLite (padrão — sem configuração)
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# Alertas por e-mail (notificar ao detectar alterações)
ALERT_EMAIL=admin@example.com

# SMTP (usa PHP mail() se não configurado)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

Exemplos de `MAILER_DSN`:

| Cenário | DSN |
|---------|-----|
| SMTP genérico | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| MTA do servidor (Postfix etc.) | `native://default` |
| Não configurado (PHP mail()) | — deixar vazio —

### Esquema do banco de dados

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## Uso do CLI

```bash
# Adicionar domínio
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode convertido automaticamente
dh domain:add shop.co.uk

# Listar domínios
dh domain:list
dh domain:list --order=expiry       # ordenar por data de expiração
dh domain:list --order=updated
dh domain:list --format=csv         # saída CSV

# Consultar / atualizar WHOIS
dh domain:refresh                   # todos os domínios
dh domain:refresh example.com       # domínio único

# Excluir domínio
dh domain:delete example.com
dh domain:delete example.com --force
```

## Monitoramento automático com Cron

```bash
# Consultar todos os domínios toda noite às 02:00
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# Alternativa: script cron.php
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## Compilar o PHAR

```bash
make phar
# ou
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## TLDs suportados

### Extensões .tr
`.com.tr` `.net.tr` `.org.tr` `.edu.tr` `.gov.tr` `.web.tr`
`.bel.tr` `.k12.tr` `.pol.tr` `.av.tr` `.dr.tr` `.tc`

### ccTLDs populares
`.de` `.uk` `.co.uk` `.org.uk` `.me.uk` `.nl` `.fr` `.eu` `.it`
`.es` `.pl` `.ru` `.cn` `.jp` `.kr` `.au` `.com.au` `.net.au`
`.ca` `.br` `.com.br` `.net.br` `.mx` `.ar` `.in` `.co.in`
`.ch` `.at` `.be` `.dk` `.fi` `.no` `.se` `.pt` `.cz` `.hu`
`.ro` `.ua` `.bg` `.gr` `.hr` `.sk` `.si` `.lt` `.lv` `.ee`
`.io` `.me` `.co` `.tv` `.us` `.biz` `.info` `.mobi`

### gTLDs
`.com` `.net` `.org` `.info` `.biz` `.name` `.pro` `.mobi`
`.tel` `.travel` `.jobs` `.museum` e mais

## Sobre o projeto

[Domain Hunter](https://domainhunter.tr) é um projeto de código aberto publicado por **[Bahri Meriç CANLI](https://bahri.info)**, desenvolvedor de software da Turquia. Lançado em 2006 como *domainhunter.org.tr* com o apoio da LKD (Associação de Usuários de Linux) e seu presidente honorário Mustafa Akgül, o projeto foi completamente modernizado e reescrito para PHP 8+ com interface web moderna, CLI, suporte a SMTP e localização em 8 idiomas.

## Licença

GNU General Public License v3.0 — ver [COPYING](COPYING)

Copyright (C) 2011 Bahri Meriç CANLI
