# SEO / AI Overview Checker

Web scraping ile Google arama sonuçlarında (SERP) sıralama takibi, Google
**AI Overview** kutusunda görünürlük kontrolü ve hedef sayfa için temel
on-page SEO analizi yapan bağımsız bir PHP/CLI aracı.

Bu araç domainhunter (WHOIS takip) uygulamasından tamamen bağımsızdır; ayrı
bir `composer.json`, kendi bağımlılıkları ve kendi CLI giriş noktası (`bin/seo-check`)
ile çalışır.

## Ne yapar?

Verilen bir domain ve anahtar kelime listesi için, her anahtar kelime başına:

1. **SERP sıralaması** — Google'ın ilk ~20 organik sonucunu çeker, hedef
   domaininizin kaçıncı sırada çıktığını raporlar.
2. **AI Overview kontrolü** — Sonuç sayfasında bir AI Overview kutusu olup
   olmadığını, varsa kutu içinde hangi domainlerin kaynak olarak
   gösterildiğini ve kendi domaininizin bu kaynaklar arasında olup
   olmadığını raporlar.
3. **On-page SEO analizi** — (isteğe bağlı, varsayılan açık) hedef sayfayı
   çekip title/meta description uzunluğu, H1/H2 sayısı, kelime yoğunluğu,
   `alt` etiketi eksik görseller, iç/dış link sayısı ve yapısal veri
   (JSON-LD) varlığı gibi temel kontrolleri yapar.

## Önemli sınırlamalar ve yasal uyarı

- **Doğrudan HTTP scraping** kullanılır (SERP API'si değil). Bu yaklaşım
  bilinçli olarak tercih edilmiştir; ancak Google, otomatik istekleri sıkça
  CAPTCHA/"unusual traffic" sayfasıyla veya JavaScript doğrulaması isteyen
  bir yönlendirmeyle (`/httpservice/retry/enablejs`) engelleyebilir. Araç bu
  durumları tespit edip "engellendi" olarak raporlar; sonuç alamıyorsanız
  önce bunu kontrol edin.
- **AI Overview genellikle istemci tarafında (JavaScript ile) render edilir**
  ve hesap/konum/cihaza göre değişebilir. Bu araç yalnızca statik HTML
  yanıtını inceler; bu yüzden tespit **en iyi çaba (best-effort)**
  niteliğindedir ve gerçekte görünen bir AI Overview burada
  yakalanamayabilir. Daha güvenilir sonuç için headless tarayıcı
  (ör. Playwright) veya resmi bir SERP API'si değerlendirilebilir.
- Google'ın SERP HTML yapısı sık değişir; ayrıştırma mantığı genel
  sezgisel (heuristic) kurallara dayanır ve zamanla güncellenmesi
  gerekebilir (bkz. `config/ai_overview_markers.php` ve
  `config/ai_overview_selectors.php`).
- Bu aracı yalnızca **kendi sitelerinizi/kendi izniniz olan siteleri**
  denetlemek için, makul istek sıklığıyla (`--delay`) kullanın. Google'ın
  hizmet şartlarını ve robots.txt kurallarını göz önünde bulundurun; yoğun
  veya toplu (mass) scraping yapılandırmayın.

## Kurulum

```bash
cd seo-ai-checker
composer install
cp .env.example .env
# .env dosyasını ihtiyacınıza göre düzenleyin (dil, gecikme, proxy vb.)
```

## Kullanım

```bash
# Tek anahtar kelime, tek domain
php bin/seo-check --domain=example.com --keyword="anahtar kelime"

# Birden fazla anahtar kelime
php bin/seo-check --domain=example.com -k "anahtar kelime 1" -k "anahtar kelime 2"

# Dosyadan anahtar kelime listesi (bkz. keywords.example.txt)
php bin/seo-check --domain=example.com --keywords-file=keywords.example.txt

# On-page analizi atla, sadece SERP/AI Overview kontrolü yap
php bin/seo-check --domain=example.com --keywords-file=keywords.example.txt --skip-onpage

# Sonuçları JSON olarak da kaydet
php bin/seo-check --domain=example.com --keywords-file=keywords.example.txt --json=rapor.json

# Dil/bölge, gecikme ve proxy ayarları
php bin/seo-check --domain=example.com --keyword="php nedir" --hl=en --gl=us --delay=6000 --proxy=http://127.0.0.1:8080
```

### Seçenekler

| Seçenek | Açıklama | Varsayılan |
|---|---|---|
| `--domain`, `-d` | Takip edilen domain (zorunlu) | — |
| `--keyword`, `-k` | Kontrol edilecek anahtar kelime (tekrarlanabilir) | — |
| `--keywords-file`, `-f` | `keyword` veya `keyword\|url` satırları içeren dosya | — |
| `--url`, `-u` | On-page analiz için varsayılan sayfa | `https://{domain}/` |
| `--skip-onpage` | On-page analizi devre dışı bırakır | kapalı |
| `--hl` | Google arayüz dili | `tr` (`.env`'den) |
| `--gl` | Google bölge kodu | `tr` (`.env`'den) |
| `--delay` | İstekler arası bekleme (ms) | `4000` |
| `--proxy` | HTTP proxy | — |
| `--user-agent` | Özel User-Agent | Chrome masaüstü UA |
| `--json` | Sonuçları ayrıca JSON dosyasına yazar | — |

Anahtar kelime dosyasındaki satır başına isteğe bağlı `|url` kısmı,
o anahtar kelime için on-page analizinin hangi sayfada yapılacağını
belirtmenizi sağlar (ör. o kelimeyle hedeflenen iniş sayfası).

## AI Overview tespiti nasıl çalışır?

`config/ai_overview_markers.php` içindeki metin ifadeleri (ör. "AI overview",
"Yapay zeka genel bakışı") sayfa metninde aranır. Eşleşme bulunursa, en
yakın üst kapsayıcı (ancestor) element içindeki linkler taranarak kaynak
domainler çıkarılmaya çalışılır. Google markup'ı değiştikçe bu ifadeler ve
gerekirse `config/ai_overview_selectors.php` içindeki CSS seçiciler
güncellenmelidir.

## Geliştirme

```bash
find src -name '*.php' -exec php -l {} \;
```
