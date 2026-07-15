<?php

declare(strict_types=1);

namespace SeoAiChecker\OnPage;

use GuzzleHttp\Client;
use SeoAiChecker\Support\Domain;
use Symfony\Component\DomCrawler\Crawler;

final class OnPageSeoAnalyzer
{
    public function __construct(private readonly Client $client)
    {
    }

    public function analyze(string $url, ?string $keyword = null): OnPageSeoResult
    {
        $start = microtime(true);
        $response = $this->client->get($url);
        $fetchTimeMs = (microtime(true) - $start) * 1000;

        $html = (string) $response->getBody();
        $crawler = new Crawler($html, $url);

        $title = $this->firstText($crawler, 'title');
        $metaDescription = $this->metaContent($crawler, 'description');
        $metaRobots = $this->metaContent($crawler, 'robots');
        $canonical = $this->linkHref($crawler, 'canonical');

        $h1s = $crawler->filter('h1')->each(static fn (Crawler $node) => trim($node->text('')));
        $h2Count = $crawler->filter('h2')->count();

        $bodyText = $crawler->filter('body')->count() > 0 ? $crawler->filter('body')->text('') : '';
        $normalizedText = trim(preg_replace('/\s+/u', ' ', $bodyText) ?? '');
        $words = $normalizedText === '' ? [] : preg_split('/\s+/u', $normalizedText);
        $wordCount = is_array($words) ? count($words) : 0;

        $keywordDensity = 0.0;
        $keywordInTitle = false;
        $keywordInH1 = false;
        $keywordInDescription = false;

        if ($keyword !== null && trim($keyword) !== '') {
            $lowerKeyword = mb_strtolower(trim($keyword));
            $lowerText = mb_strtolower($normalizedText);

            $occurrences = $lowerText === '' ? 0 : substr_count($lowerText, $lowerKeyword);
            $keywordWordCount = max(1, count(preg_split('/\s+/u', $lowerKeyword) ?: [$lowerKeyword]));
            $keywordDensity = $wordCount > 0
                ? ($occurrences * $keywordWordCount / $wordCount) * 100
                : 0.0;

            $keywordInTitle = $title !== null && str_contains(mb_strtolower($title), $lowerKeyword);
            $keywordInDescription = $metaDescription !== null && str_contains(mb_strtolower($metaDescription), $lowerKeyword);
            foreach ($h1s as $h1) {
                if (str_contains(mb_strtolower($h1), $lowerKeyword)) {
                    $keywordInH1 = true;
                    break;
                }
            }
        }

        $imagesMissingAlt = $crawler->filter('img')->reduce(
            static fn (Crawler $img) => trim((string) $img->attr('alt')) === ''
        )->count();

        $pageHost = Domain::fromUrl($url);
        $internalLinks = 0;
        $externalLinks = 0;
        foreach ($crawler->filter('a[href]') as $node) {
            $href = $node->getAttribute('href');
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:')) {
                continue;
            }

            $absolute = $this->toAbsoluteUrl($href, $url);
            $linkHost = $absolute !== null ? Domain::fromUrl($absolute) : null;

            if ($linkHost === null) {
                continue;
            }

            if ($pageHost !== null && Domain::equals($linkHost, $pageHost)) {
                $internalLinks++;
            } else {
                $externalLinks++;
            }
        }

        $hasStructuredData = $crawler->filter('script[type="application/ld+json"]')->count() > 0;

        return new OnPageSeoResult(
            url: $url,
            title: $title,
            metaDescription: $metaDescription,
            canonical: $canonical,
            metaRobots: $metaRobots,
            h1s: $h1s,
            h2Count: $h2Count,
            wordCount: $wordCount,
            keyword: $keyword,
            keywordDensityPercent: round($keywordDensity, 2),
            keywordInTitle: $keywordInTitle,
            keywordInH1: $keywordInH1,
            keywordInDescription: $keywordInDescription,
            imagesMissingAlt: $imagesMissingAlt,
            internalLinks: $internalLinks,
            externalLinks: $externalLinks,
            hasStructuredData: $hasStructuredData,
            fetchTimeMs: round($fetchTimeMs, 1),
        );
    }

    private function firstText(Crawler $crawler, string $selector): ?string
    {
        $node = $crawler->filter($selector);

        return $node->count() > 0 ? trim($node->first()->text('')) : null;
    }

    private function metaContent(Crawler $crawler, string $name): ?string
    {
        $node = $crawler->filter(sprintf('meta[name="%s"]', $name));
        if ($node->count() === 0) {
            return null;
        }

        $content = $node->first()->attr('content');

        return $content !== null ? trim($content) : null;
    }

    private function linkHref(Crawler $crawler, string $rel): ?string
    {
        $node = $crawler->filter(sprintf('link[rel="%s"]', $rel));

        return $node->count() > 0 ? $node->first()->attr('href') : null;
    }

    private function toAbsoluteUrl(string $href, string $baseUrl): ?string
    {
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }

        if (str_starts_with($href, '//')) {
            return 'https:' . $href;
        }

        $base = parse_url($baseUrl);
        if (!isset($base['scheme'], $base['host'])) {
            return null;
        }

        $origin = $base['scheme'] . '://' . $base['host'] . (isset($base['port']) ? ':' . $base['port'] : '');

        if (str_starts_with($href, '/')) {
            return $origin . $href;
        }

        return $origin . '/' . ltrim($href, '/');
    }
}
