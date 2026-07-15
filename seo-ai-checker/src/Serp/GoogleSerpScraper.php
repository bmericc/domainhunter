<?php

declare(strict_types=1);

namespace SeoAiChecker\Serp;

use GuzzleHttp\Client;
use SeoAiChecker\Support\Domain;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Google arama sonuc sayfasini (SERP) dogrudan HTTP ile ceker ve organik
 * siralamayi + AI Overview kutusunu (varsa) tespit etmeye calisir.
 *
 * ONEMLI SINIRLAMA: AI Overview cogunlukla istemci tarafinda (JavaScript ile)
 * render edilir ve Google hesabi/konum/cihaza gore degisebilir. Bu sinif
 * yalnizca statik HTML yanitini inceler; bu nedenle tespit "en iyi caba"
 * (best-effort) niteligindedir ve AI Overview gercekte gorunse bile burada
 * yakalanamayabilir. Guvenilir sonuc icin headless tarayici (Playwright vb.)
 * veya resmi bir SERP API'si degerlendirilmelidir.
 */
final class GoogleSerpScraper
{
    /** @var string[] */
    private array $aiOverviewMarkers;

    /** @var string[] */
    private array $aiOverviewSelectors;

    public function __construct(
        private readonly Client $client,
        private readonly string $hl = 'tr',
        private readonly string $gl = 'tr',
        private readonly int $numResults = 20,
        ?array $aiOverviewMarkers = null,
        ?array $aiOverviewSelectors = null,
    ) {
        $this->aiOverviewMarkers = $aiOverviewMarkers ?? require __DIR__ . '/../../config/ai_overview_markers.php';
        $this->aiOverviewSelectors = $aiOverviewSelectors ?? require __DIR__ . '/../../config/ai_overview_selectors.php';
    }

    public function search(string $keyword): SerpResult
    {
        $url = 'https://www.google.com/search?' . http_build_query([
            'q' => $keyword,
            'hl' => $this->hl,
            'gl' => $this->gl,
            'num' => $this->numResults,
            'pws' => 0,
        ]);

        $response = $this->client->get($url);
        $status = $response->getStatusCode();
        $html = (string) $response->getBody();

        if ($status !== 200 || $this->looksBlocked($html)) {
            return new SerpResult(
                keyword: $keyword,
                organicResults: [],
                aiOverview: new AiOverviewResult(present: false),
                blocked: true,
                blockReason: sprintf(
                    'Google istegi engellemis veya JS dogrulamasi istemis olabilir (HTTP %d). '
                    . 'Istek sikligini (--delay) azaltip farkli bir IP/proxy kullanmayi deneyin.',
                    $status
                ),
            );
        }

        $crawler = new Crawler($html);

        return new SerpResult(
            keyword: $keyword,
            organicResults: $this->parseOrganicResults($crawler),
            aiOverview: $this->detectAiOverview($crawler),
        );
    }

    private function looksBlocked(string $html): bool
    {
        $needles = [
            'unusual traffic',
            'detected unusual traffic',
            '/sorry/index',
            'recaptcha',
            'our systems have detected',
            'httpservice/retry/enablejs',
        ];

        $lower = mb_strtolower($html);
        foreach ($needles as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return OrganicResult[]
     */
    private function parseOrganicResults(Crawler $crawler): array
    {
        $results = [];
        $seenUrls = [];
        $position = 0;

        $anchors = $crawler->filterXPath('//a[.//h3]');

        foreach ($anchors as $node) {
            $anchorCrawler = new Crawler($node);
            $href = $anchorCrawler->attr('href') ?? '';
            $title = trim($anchorCrawler->filter('h3')->first()->text(''));

            $realUrl = $this->resolveRealUrl($href);
            if ($realUrl === null || $title === '') {
                continue;
            }

            $domain = Domain::fromUrl($realUrl);
            if ($domain === null || $this->isGoogleOwnedHost($domain)) {
                continue;
            }

            if (isset($seenUrls[$realUrl])) {
                continue;
            }
            $seenUrls[$realUrl] = true;

            $position++;
            $results[] = new OrganicResult(
                position: $position,
                url: $realUrl,
                domain: $domain,
                title: $title,
            );

            if ($position >= $this->numResults) {
                break;
            }
        }

        return $results;
    }

    private function detectAiOverview(Crawler $crawler): AiOverviewResult
    {
        $bodyText = '';
        $body = $crawler->filter('body');
        if ($body->count() > 0) {
            $bodyText = mb_strtolower($body->text(''));
        }

        $markerFound = false;
        foreach ($this->aiOverviewMarkers as $marker) {
            if (str_contains($bodyText, mb_strtolower($marker))) {
                $markerFound = true;
                break;
            }
        }

        $selectorFound = false;
        foreach ($this->aiOverviewSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $selectorFound = true;
                break;
            }
        }

        if (!$markerFound && !$selectorFound) {
            return new AiOverviewResult(present: false);
        }

        $citedDomains = $this->extractAiOverviewSources($crawler);

        $note = $citedDomains === []
            ? 'AI Overview metni tespit edildi fakat icindeki kaynak linkleri otomatik olarak cikarilamadi; sayfayi manuel kontrol edin.'
            : null;

        return new AiOverviewResult(present: true, citedDomains: $citedDomains, note: $note);
    }

    /**
     * @return string[]
     */
    private function extractAiOverviewSources(Crawler $crawler): array
    {
        if ($this->aiOverviewMarkers === []) {
            return [];
        }

        try {
            $textNodes = $crawler->filterXPath('//text()[normalize-space()]');
        } catch (\Throwable) {
            return [];
        }

        $markerNode = null;
        foreach ($textNodes as $textNode) {
            $text = mb_strtolower(trim($textNode->nodeValue ?? ''));
            if ($text === '') {
                continue;
            }

            foreach ($this->aiOverviewMarkers as $marker) {
                if (str_contains($text, mb_strtolower($marker))) {
                    $markerNode = $textNode->parentNode;
                    break 2;
                }
            }
        }

        if ($markerNode === null) {
            return [];
        }

        $ancestor = $markerNode;
        $bestDomains = [];

        for ($level = 0; $level < 8 && $ancestor !== null; $level++) {
            $ancestor = $ancestor->parentNode;
            if (!$ancestor instanceof \DOMElement) {
                continue;
            }

            $domains = $this->collectDomainsFromContainer(new Crawler($ancestor));
            if (count($domains) >= 1) {
                $bestDomains = $domains;
                // Kucuk bir konteyner (sayfanin tamami degil) yakaladigimizda dur.
                if (count($domains) <= 10) {
                    break;
                }
            }
        }

        return $bestDomains;
    }

    /**
     * @return string[]
     */
    private function collectDomainsFromContainer(Crawler $container): array
    {
        $domains = [];

        foreach ($container->filter('a') as $node) {
            $href = $node->getAttribute('href');
            $realUrl = $this->resolveRealUrl($href);
            if ($realUrl === null) {
                continue;
            }

            $domain = Domain::fromUrl($realUrl);
            if ($domain === null || $this->isGoogleOwnedHost($domain)) {
                continue;
            }

            $domains[$domain] = true;
        }

        return array_keys($domains);
    }

    private function resolveRealUrl(string $href): ?string
    {
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) {
            return null;
        }

        if (str_starts_with($href, '/url?')) {
            $query = parse_url($href, PHP_URL_QUERY) ?: '';
            parse_str($query, $params);

            return $params['q'] ?? null;
        }

        if (str_starts_with($href, '//')) {
            return 'https:' . $href;
        }

        if (str_starts_with($href, '/')) {
            // Google'in kendi ic navigasyon linki (ör. /search?..., /preferences...).
            return null;
        }

        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }

        return null;
    }

    private function isGoogleOwnedHost(string $domain): bool
    {
        foreach (['google.', 'gstatic.com', 'googleadservices.com', 'googlesyndication.com', 'doubleclick.net'] as $needle) {
            if (str_contains($domain, $needle)) {
                return true;
            }
        }

        return false;
    }
}
