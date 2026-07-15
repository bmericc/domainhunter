<?php

declare(strict_types=1);

namespace SeoAiChecker\Console;

use GuzzleHttp\Exception\GuzzleException;
use SeoAiChecker\Http\HttpClientFactory;
use SeoAiChecker\Keyword\KeywordEntry;
use SeoAiChecker\Keyword\KeywordListLoader;
use SeoAiChecker\OnPage\OnPageSeoAnalyzer;
use SeoAiChecker\OnPage\OnPageSeoResult;
use SeoAiChecker\Report\ConsoleReporter;
use SeoAiChecker\Serp\GoogleSerpScraper;
use SeoAiChecker\Serp\SerpResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'seo:check', description: 'Google SERP siralamasi, AI Overview gorunurlugu ve on-page SEO kontrolu yapar')]
final class CheckCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Takip edilen domain (ornek: example.com)')
            ->addOption('keyword', 'k', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Kontrol edilecek anahtar kelime (birden fazla kez kullanilabilir)')
            ->addOption('keywords-file', 'f', InputOption::VALUE_REQUIRED, 'Her satirda bir anahtar kelime (istege bagli "kelime|url") iceren dosya')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'On-page SEO analizi icin varsayilan sayfa (belirtilmezse https://{domain}/ kullanilir)')
            ->addOption('skip-onpage', null, InputOption::VALUE_NONE, 'On-page SEO analizini atla, sadece SERP/AI Overview kontrolu yap')
            ->addOption('hl', null, InputOption::VALUE_REQUIRED, 'Google arayuz dili', getenv('GOOGLE_HL') ?: 'tr')
            ->addOption('gl', null, InputOption::VALUE_REQUIRED, 'Google bolge kodu', getenv('GOOGLE_GL') ?: 'tr')
            ->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Istekler arasi bekleme (ms)', getenv('REQUEST_DELAY_MS') ?: '4000')
            ->addOption('proxy', null, InputOption::VALUE_REQUIRED, 'HTTP proxy (opsiyonel)', getenv('HTTP_PROXY') ?: null)
            ->addOption('user-agent', null, InputOption::VALUE_REQUIRED, 'Ozel User-Agent (opsiyonel)', getenv('USER_AGENT') ?: null)
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'Sonuclari JSON olarak da yaz (dosya yolu)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $domain = $input->getOption('domain');
        if (!is_string($domain) || trim($domain) === '') {
            $io->error('--domain secenegi zorunludur (ornek: --domain=example.com).');

            return Command::FAILURE;
        }
        $domain = trim($domain);

        $entries = $this->collectKeywordEntries($input, $io);
        if ($entries === []) {
            $io->error('En az bir anahtar kelime belirtmelisiniz (--keyword veya --keywords-file).');

            return Command::FAILURE;
        }

        $hl = (string) $input->getOption('hl');
        $gl = (string) $input->getOption('gl');
        $delayMs = max(0, (int) $input->getOption('delay'));
        $proxy = $input->getOption('proxy');
        $userAgent = $input->getOption('user-agent');
        $skipOnPage = (bool) $input->getOption('skip-onpage');
        $defaultUrl = $input->getOption('url');
        $jsonPath = $input->getOption('json');

        $acceptLanguage = sprintf('%s-%s,%s;q=0.9,en-US;q=0.8,en;q=0.7', $hl, strtoupper($gl), $hl);
        $client = HttpClientFactory::create(
            $acceptLanguage,
            $userAgent !== null ? (string) $userAgent : null,
            $proxy !== null ? (string) $proxy : null,
        );

        $serpScraper = new GoogleSerpScraper($client, $hl, $gl);
        $onPageAnalyzer = new OnPageSeoAnalyzer($client);
        $reporter = new ConsoleReporter();

        $io->title(sprintf('SEO / AI Overview Kontrolu: %s', $domain));
        $io->note(
            'Bu arac Google sonuc sayfasini dogrudan HTTP ile ceker; Google sizi gecici olarak '
            . 'engelleyebilir (CAPTCHA) ve AI Overview genellikle JS ile render edildigi icin '
            . 'her zaman yakalanamayabilir. Sonuclari bu sinirlamalarla birlikte degerlendirin.'
        );

        $jsonReport = [];
        $first = true;

        foreach ($entries as $entry) {
            if (!$first && $delayMs > 0) {
                usleep($delayMs * 1000);
            }
            $first = false;

            $serp = $this->safeSearch($serpScraper, $entry->keyword, $io);
            if ($serp === null) {
                continue;
            }

            $onPage = null;
            if (!$skipOnPage) {
                $targetUrl = $entry->url
                    ?? ($defaultUrl !== null ? (string) $defaultUrl : null)
                    ?? sprintf('https://%s/', $domain);

                $onPage = $this->safeAnalyze($onPageAnalyzer, $targetUrl, $entry->keyword, $io);
            }

            $reporter->report($io, $serp, $domain, $onPage);

            if ($jsonPath !== null) {
                $jsonReport[] = $this->toReportArray($serp, $onPage, $domain);
            }
        }

        if ($jsonPath !== null) {
            file_put_contents(
                (string) $jsonPath,
                json_encode($jsonReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
            );
            $io->text(sprintf('JSON rapor yazildi: %s', $jsonPath));
        }

        return Command::SUCCESS;
    }

    /**
     * @return KeywordEntry[]
     */
    private function collectKeywordEntries(InputInterface $input, SymfonyStyle $io): array
    {
        $entries = [];

        $keywordsFile = $input->getOption('keywords-file');
        if ($keywordsFile !== null) {
            try {
                $entries = array_merge($entries, (new KeywordListLoader())->loadFromFile((string) $keywordsFile));
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());
            }
        }

        foreach ((array) $input->getOption('keyword') as $keyword) {
            if (trim((string) $keyword) !== '') {
                $entries[] = new KeywordEntry(trim((string) $keyword));
            }
        }

        return $entries;
    }

    private function safeSearch(GoogleSerpScraper $scraper, string $keyword, SymfonyStyle $io): ?SerpResult
    {
        try {
            return $scraper->search($keyword);
        } catch (GuzzleException $e) {
            $io->error(sprintf('"%s" icin Google istegi basarisiz: %s', $keyword, $e->getMessage()));

            return null;
        }
    }

    private function safeAnalyze(OnPageSeoAnalyzer $analyzer, string $url, string $keyword, SymfonyStyle $io): ?OnPageSeoResult
    {
        try {
            return $analyzer->analyze($url, $keyword);
        } catch (GuzzleException $e) {
            $io->warning(sprintf('On-page analiz basarisiz (%s): %s', $url, $e->getMessage()));

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toReportArray(SerpResult $serp, ?OnPageSeoResult $onPage, string $domain): array
    {
        return [
            'keyword' => $serp->keyword,
            'blocked' => $serp->blocked,
            'block_reason' => $serp->blockReason,
            'target_domain' => $domain,
            'target_position' => $serp->positionOf($domain),
            'organic_results' => array_map(
                static fn ($r) => ['position' => $r->position, 'url' => $r->url, 'domain' => $r->domain, 'title' => $r->title],
                $serp->organicResults
            ),
            'ai_overview' => [
                'present' => $serp->aiOverview->present,
                'cited_domains' => $serp->aiOverview->citedDomains,
                'target_cited' => $serp->aiOverview->present ? $serp->aiOverview->citesDomain($domain) : null,
                'note' => $serp->aiOverview->note,
            ],
            'on_page' => $onPage === null ? null : [
                'url' => $onPage->url,
                'title' => $onPage->title,
                'title_length' => $onPage->titleLength(),
                'meta_description' => $onPage->metaDescription,
                'meta_description_length' => $onPage->descriptionLength(),
                'canonical' => $onPage->canonical,
                'meta_robots' => $onPage->metaRobots,
                'h1s' => $onPage->h1s,
                'h2_count' => $onPage->h2Count,
                'word_count' => $onPage->wordCount,
                'keyword_density_percent' => $onPage->keywordDensityPercent,
                'keyword_in_title' => $onPage->keywordInTitle,
                'keyword_in_h1' => $onPage->keywordInH1,
                'keyword_in_description' => $onPage->keywordInDescription,
                'images_missing_alt' => $onPage->imagesMissingAlt,
                'internal_links' => $onPage->internalLinks,
                'external_links' => $onPage->externalLinks,
                'has_structured_data' => $onPage->hasStructuredData,
                'fetch_time_ms' => $onPage->fetchTimeMs,
            ],
        ];
    }
}
