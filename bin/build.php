#!/usr/bin/env php
<?php

declare(strict_types=1);

// ── Ön kontroller ────────────────────────────────────────────────────────────

if (ini_get('phar.readonly')) {
    fwrite(STDERR, "Hata: php.ini'de phar.readonly=On\n");
    fwrite(STDERR, "Şununla çalıştırın: php -d phar.readonly=0 bin/build.php\n");
    exit(1);
}

$root    = dirname(__DIR__);
$outDir  = $root . '/builds';
$outFile = $outDir . '/dh.phar';

// ── Üretim bağımlılıklarını yükle ────────────────────────────────────────────

echo "→ composer install --no-dev --optimize-autoloader\n";
passthru(
    sprintf('composer install --no-dev --optimize-autoloader -d %s 2>&1', escapeshellarg($root)),
    $code
);
if ($code !== 0) {
    fwrite(STDERR, "Composer başarısız oldu.\n");
    exit(1);
}

// ── Çıktı dizini hazırla ─────────────────────────────────────────────────────

if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}
if (file_exists($outFile)) {
    unlink($outFile);
}

echo "→ PHAR oluşturuluyor: $outFile\n";

// ── PHAR oluştur ─────────────────────────────────────────────────────────────

$phar = new Phar($outFile);
$phar->setSignatureAlgorithm(Phar::SHA256);
$phar->startBuffering();

$addDir = static function (Phar $p, string $srcDir, string $pharBase, string $regex): int {
    if (!is_dir($srcDir)) {
        return 0;
    }
    $count = 0;
    $iter  = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if (!$file->isFile() || !preg_match($regex, $file->getFilename())) {
            continue;
        }
        $rel      = str_replace([$srcDir, DIRECTORY_SEPARATOR], ['', '/'], $file->getPathname());
        $pharPath = $pharBase . '/' . ltrim($rel, '/');
        $p->addFile((string) $file, $pharPath);
        $count++;
    }
    return $count;
};

// CLI PHAR: web dosyaları (templates/, public/) dahil değil
foreach ([
    ['src',    'src',    '/\.php$/'],
    ['config', 'config', '/\.php$/'],
    ['vendor', 'vendor', '/\.(php|json)$/'],
] as [$dir, $base, $regex]) {
    $n = $addDir($phar, $root . '/' . $dir, $base, $regex);
    echo "   + $dir/ ($n dosya)\n";
}

foreach (['bin/bootstrap.php', 'bin/dh'] as $file) {
    $phar->addFile($root . '/' . $file, $file);
}
echo "   + bin/bootstrap.php, bin/dh\n";

// ── CLI stub ─────────────────────────────────────────────────────────────────

$stub = <<<'PHP'
#!/usr/bin/env php
<?php
/**
 * Domain Hunter CLI — dh.phar
 *
 * Kullanım:
 *   php dh.phar domain:add example.com.tr
 *   php dh.phar domain:list
 *   php dh.phar domain:refresh
 *
 * Yapılandırma:
 *   .env dosyasını PHAR'ın yanına koyun (DB_DRIVER, DB_PATH, ALERT_EMAIL…)
 *   Varsayılan: SQLite, domainhunter.sqlite @ CWD
 */
Phar::mapPhar('dh.phar');
require 'phar://dh.phar/bin/dh';
__HALT_COMPILER();
PHP;

$phar->setStub($stub);
$phar->stopBuffering();
chmod($outFile, 0755);

// ── Özet ─────────────────────────────────────────────────────────────────────

$kb = (int) round(filesize($outFile) / 1024);
echo "\n✓ Hazır: builds/dh.phar ({$kb} KB)\n\n";
echo "  Hızlı başlangıç:\n";
echo "    cp builds/dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh\n";
echo "    dh domain:add example.com\n";
echo "    dh domain:add türkiye.com.tr\n";
echo "    dh domain:list --order=expiry\n";
echo "    dh domain:refresh\n\n";
echo "  ~/.domainhunter/ (otomatik oluşturulur):\n";
echo "    domainhunter.sqlite  ← veritabanı\n";
echo "    .env (opsiyonel)     ← yapılandırma\n\n";
echo "  .env örneği (~/.domainhunter/.env):\n";
echo "    DB_DRIVER=sqlite          # veya mysql\n";
echo "    ALERT_EMAIL=admin@foo.com\n\n";

// Dev bağımlılıklarını geri yükle
echo "→ composer install (dev bağımlılıkları geri yükleniyor)\n";
passthru(sprintf('composer install -d %s 2>&1', escapeshellarg($root)));
