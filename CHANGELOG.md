# Changelog

## [2.0.5] - 2026-07-21

### Düzeltildi
- CLI'nin `--version`/`-V` bayrağının bastığı sürüm (`bin/dh`, ve onun
  symlink'i `bin/domainhunter`) v2.0.2'den beri `2.0.1` olarak kalmıştı.
  `2.0.5`'e güncellendi.

## [2.0.4] - 2026-07-19

### Eklendi
- Proje ilk kez otomatik testlere kavuştu: PHPUnit test paketi (`tests/`),
  gerçek şemayı taklit eden bellek-içi SQLite fixture'ı ve WHOIS ağ
  çağrısı yapmayan sahte `WhoisService`. `DomainRepository`,
  `DomainHistoryRepository` ve `DomainService` (ekleme/yenileme/değişiklik
  tespiti/uyarı e-postası) kapsanıyor.
- PHP 8.2-8.4 matrisinde testleri çalıştıran GitHub Actions workflow'u.

### Düzeltildi
- `composer.json`'daki `php` gereksinimi `^8.1` idi ama zaten zorunlu olan
  `symfony/console: ^7.0` her sürümünde PHP >=8.2 istiyor; bu tutarsızlık
  daha önce hiç test edilmediği için fark edilmemişti. Gereksinim `^8.2`
  olarak düzeltildi ve `composer.lock` PHP 8.2 tabanına göre yeniden
  kilitlendi (önceki lock, PHP 8.4 gerektiren bazı Symfony bileşenlerini
  içeriyordu ve daha düşük sürümlerde `composer install` başarısız
  oluyordu).

## [2.0.3] - 2026-07-19

### Düzeltildi
- Alt alan adları (`sub.example.com.br` gibi) hem düz hem bileşik TLD'lerde
  hatalı reddediliyordu. Kök neden yine paylaşılan `bahricanli/domainhunter`
  paketindeydi. `bahricanli/domainhunter` v1.0.3'e güncellendi:
  - `DomainParser::parse()` artık alt alan adı etiketlerini atıp her zaman
    doğrudan tescilli alan adına çözümleniyor.
  - Bileşik TLD ayrımı artık elle tutulan bir listeyle tahmin edilmek yerine
    publicsuffix.org'un ICANN bölümünü kullanan gerçek bir Public Suffix List
    algoritmasıyla yapılıyor (ör. `example.com.br` artık doğru şekilde
    `com.br` soneki olarak tanınıyor).
- Kullanıcıya görünen davranışta değişiklik yok (CLI ve web arayüzü aynı
  şekilde çalışır).

## [2.0.2] - 2026-07-14

### Düzeltildi
- `.be` domainleri için WHOIS sorgulama hatalı sonuç veriyordu (registrar
  ve nameserver bilgileri boş geliyor, kayıt tarihi yanlış hesaplanıyordu).
  Kök neden paylaşılan `bahricanli/domainhunter` paketindeydi: `whois.dns.be`
  yanıt formatı genel ayrıştırıcı tarafından desteklenmiyordu ve tarih
  ayrıştırma mantığı bazı formatlarda yılı siliyordu. `bahricanli/domainhunter`
  v1.0.1'e güncellendi.

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
