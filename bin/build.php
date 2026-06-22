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

/**
 * Bir dizini PHAR'a ekler; sadece $regex ile eşleşen dosyaları alır.
 */
$addDir = static function (Phar $p, string $srcDir, string $pharBase, string $regex): void {
    if (!is_dir($srcDir)) {
        return;
    }
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if (!$file->isFile() || !preg_match($regex, $file->getFilename())) {
            continue;
        }
        $rel      = str_replace([$srcDir, DIRECTORY_SEPARATOR], ['', '/'], $file->getPathname());
        $pharPath = $pharBase . '/' . ltrim($rel, '/');
        $p->addFile((string) $file, $pharPath);
    }
};

$counts = [];

$tasks = [
    ['src',       'src',       '/\.php$/'],
    ['config',    'config',    '/\.php$/'],
    ['templates', 'templates', '/\.twig$/'],
    ['vendor',    'vendor',    '/\.(php|json)$/'],
];

foreach ($tasks as [$dir, $base, $regex]) {
    $before = count($phar);
    $addDir($phar, $root . '/' . $dir, $base, $regex);
    $counts[$dir] = count($phar) - $before;
    echo "   + $dir/ ({$counts[$dir]} dosya)\n";
}

// CLI ve web entry point'leri ekle
foreach (['bin/bootstrap.php', 'bin/dh', 'public/index.php'] as $file) {
    $phar->addFile($root . '/' . $file, $file);
}
echo "   + bin/bootstrap.php, bin/dh, public/index.php\n";

// ── Çift modlu stub: CLI veya web ────────────────────────────────────────────

$stub = <<<'PHP'
#!/usr/bin/env php
<?php
/**
 * Domain Hunter — dh.phar
 *
 * CLI modu : php dh.phar domain:list
 * Web modu : php -S localhost:8080 dh.phar
 *            (veya Apache/Nginx'i dh.phar'a yönlendir)
 *
 * .env      : PHAR'ın yanına veya CWD'ye koyun
 * Veritabanı: DB_DRIVER=sqlite (varsayılan) veya DB_DRIVER=mysql
 */
Phar::mapPhar('dh.phar');

if (PHP_SAPI === 'cli') {
    require 'phar://dh.phar/bin/dh';
} else {
    // Slim'in URL oluşturma için SCRIPT_NAME boş olmalı
    $_SERVER['SCRIPT_NAME'] = '';
    require 'phar://dh.phar/public/index.php';
}

__HALT_COMPILER();
PHP;

$phar->setStub($stub);
$phar->stopBuffering();
chmod($outFile, 0755);

// ── Özet ─────────────────────────────────────────────────────────────────────

$kb = (int) round(filesize($outFile) / 1024);
echo "\n✓ Hazır: builds/dh.phar ({$kb} KB)\n\n";
echo "  Kullanım:\n";
echo "    php builds/dh.phar list                    # tüm komutları göster\n";
echo "    php builds/dh.phar domain:add example.com\n";
echo "    php builds/dh.phar domain:list\n";
echo "    php -S localhost:8080 builds/dh.phar       # web arayüzü\n\n";
echo "  Kolay erişim:\n";
echo "    cp builds/dh.phar /usr/local/bin/dh && chmod +x /usr/local/bin/dh\n";
echo "    dh domain:add example.com.tr\n\n";

// Dev bağımlılıklarını geri yükle
echo "→ composer install (dev bağımlılıkları geri yükleniyor)\n";
passthru(sprintf('composer install -d %s 2>&1', escapeshellarg($root)));
