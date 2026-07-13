# Changelog

## [2.0.1] - 2026-07-13

### Değişti
- WHOIS/RDAP sorgulama ve domain-adı ayrıştırma (Punycode/IDN, compound-TLD
  tespiti) ayrı, framework-agnostic bir pakete taşındı:
  [`bahricanli/domainhunter`](https://github.com/bahricanli/laravel-domainhunter).
  Bu mantık artık bu proje ile [app.domainhunter.tr](https://app.domainhunter.tr)
  (Laravel, çok kullanıcılı rewrite) arasında tekrarsız paylaşılıyor.
- `App\Service\WhoisService` ve `App\Service\WhoisResult` kaldırıldı, yerine
  paylaşılan pakedin `BahriCanli\DomainHunter\WhoisService` /
  `WhoisResult` / `DomainParser` sınıfları kullanılıyor.
- Kullanıcıya görünen davranışta değişiklik yok (CLI ve web arayüzü aynı
  şekilde çalışır).

## [2.0.0] - 2026-06-22

Projenin tamamen yeniden yazımı. Orijinal 2011 kodundan geriye yalnızca fikir kaldı.

### Eklendi
- **Slim 4** micro-framework ile web arayüzü (PSR-7/PSR-15)
- **Twig 3** şablon motoru ile modern HTML arayüzü
- **Symfony Console 7** ile tam CLI desteği (`dh` / `domainhunter` binary)
  - `domain:add` — domain ekle (Punycode otomatik dönüşüm)
  - `domain:list` — tablo veya CSV çıktısı, sıralama seçenekleri
  - `domain:refresh` — WHOIS sorgulama ve değişiklik tespiti
  - `domain:delete` — domain silme (`--force` bayrağı ile onaysız)
- **SQLite desteği** — sıfır-konfigürasyon kurulum için varsayılan
- **MySQL desteği** — üretim ortamları için
- **Punycode / IDN desteği** — `türkiye.com.tr` → `xn--trkiye-62a.com.tr` (ext-intl)
- **80+ TLD** — tüm `.tr` alt uzantıları dahil popüler ccTLD ve gTLD'ler
- **PHAR dağıtımı** — `dh.phar` tek dosya olarak CLI'yı çalıştırır
- **phpdotenv** ile `.env` tabanlı yapılandırma
- **PHP-DI 7** dependency injection container (web)
- **Cron scripti** (`bin/cron.php`) ile otomatik WHOIS takibi
- PHAR-aware `.env` ve SQLite yolu tespiti (`Phar::running()`)
- `make phar` ile tek komutla derleme

### Değişti
- `mysql_*` fonksiyonları → PDO (prepared statements)
- PHP 4 tarzı OOP → PHP 8.1 readonly properties, typed properties
- `ereg*` → `preg_*`
- Hardcoded `.com` / `.net` → 80+ TLD dinamik yapısı
- `NOW()` SQL → PHP `date()` (SQLite uyumluluğu için)

### Kaldırıldı
- Tüm eski PHP 4/5 kodu (`adddomain.php`, `cron.php`, `index.php` vb.)
- Düz PHP şablonları
- `config.inc.php` yapılandırma sistemi

---

## [0.1.2] - 2011

İlk sürüm. Sadece `.com` ve `.net` TLD desteği, MySQL, PHP 4 tarzı kod.

Orijinal yazar: Bahri Meriç CANLI
