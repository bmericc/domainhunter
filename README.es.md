# Domain Hunter

Una aplicación PHP que rastrea nombres de dominio, monitorea cambios en WHOIS y envía alertas por correo electrónico.

Soporta más de 80 TLDs, Punycode/IDN, SQLite/MySQL y funciona tanto con una **interfaz web** como con **CLI**.

🌐 [Türkçe](README.tr.md) | [English](README.md) | [Deutsch](README.de.md) | **Español** | [Português](README.pt.md) | [Русский](README.ru.md) | [中文](README.zh.md) | [日本語](README.ja.md)

## Requisitos

- PHP 8.1+
- `ext-intl` (para Punycode)
- `ext-pdo`, `ext-pdo_sqlite` o `ext-pdo_mysql`
- Composer

## Instalación

### Web + CLI (instalación completa)

```bash
git clone https://github.com/bmericc/domainhunter.git
cd domainhunter
composer install
cp .env.example .env
# Editar el archivo .env
```

Apunta tu servidor web al directorio `public/`.

### Solo CLI — PHAR (sin instalación)

```bash
# Descargar el binario listo:
curl -L https://github.com/bmericc/domainhunter/releases/latest/download/dh.phar -o dh
chmod +x dh
./dh domain:add example.com
```

O instalar en todo el sistema:

```bash
cp dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh
dh domain:list
```

## Configuración

Archivo `.env` (para uso con PHAR, colocarlo junto a `dh.phar` o en el directorio de trabajo):

```ini
# Idioma de la interfaz web: en | tr | de | es | pt | ru | zh | ja  (predeterminado: en)
APP_LANG=es

# SQLite (predeterminado — sin configuración)
DB_DRIVER=sqlite
DB_PATH=/var/lib/domainhunter/domainhunter.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=domainhunter
DB_USER=root
DB_PASS=secret

# Alertas por correo (notificar al detectar cambios)
ALERT_EMAIL=admin@example.com

# SMTP (usa PHP mail() si no se configura)
MAILER_DSN=smtp://user:pass@smtp.example.com:587
MAILER_FROM=domainhunter@example.com
```

Ejemplos de `MAILER_DSN`:

| Escenario | DSN |
|-----------|-----|
| SMTP genérico | `smtp://user:pass@smtp.example.com:587` |
| Gmail | `smtp://user:app-pass@smtp.gmail.com:465?encryption=tls` |
| MTA del servidor (Postfix, etc.) | `native://default` |
| Sin configurar (PHP mail()) | — dejar vacío —

### Esquema de base de datos

```bash
# SQLite
sqlite3 domainhunter.sqlite < database/schema.sqlite.sql

# MySQL
mysql -u root -p domainhunter < database/schema.sql
```

## Uso de CLI

```bash
# Agregar dominio
dh domain:add example.com
dh domain:add türkiye.com.tr        # Punycode se convierte automáticamente
dh domain:add shop.co.uk

# Listar dominios
dh domain:list
dh domain:list --order=expiry       # ordenar por fecha de vencimiento
dh domain:list --order=updated
dh domain:list --format=csv         # salida CSV

# Consultar / actualizar WHOIS
dh domain:refresh                   # todos los dominios
dh domain:refresh example.com       # un solo dominio

# Eliminar dominio
dh domain:delete example.com
dh domain:delete example.com --force
```

## Monitoreo automático con Cron

```bash
# Consultar todos los dominios cada noche a las 02:00
0 2 * * * /usr/local/bin/dh domain:refresh >> /var/log/domainhunter.log 2>&1

# Alternativa: script cron.php
0 2 * * * php /path/to/domainhunter/bin/cron.php >> /var/log/domainhunter.log 2>&1
```

## Compilar el PHAR

```bash
make phar
# o
php -d phar.readonly=0 bin/build.php
# → builds/dh.phar
```

## TLDs soportados

### Extensiones .tr
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
`.tel` `.travel` `.jobs` `.museum` y más

## Acerca del proyecto

[Domain Hunter](https://domainhunter.tr) es un proyecto de código abierto publicado por **[Bahri Meriç CANLI](https://bahri.info)**, desarrollador de software de Turquía. Lanzado en 2006 como *domainhunter.org.tr* con el apoyo de LKD (Asociación de Usuarios de Linux) y su presidente honorario Mustafa Akgül, el proyecto ha sido completamente modernizado y reescrito para PHP 8+ con interfaz web moderna, CLI, soporte SMTP y localización en 8 idiomas.

## Licencia

GNU General Public License v3.0 — ver [COPYING](COPYING)

Copyright (C) 2011 Bahri Meriç CANLI
