<?php

declare(strict_types=1);

namespace SeoAiChecker\Report;

use SeoAiChecker\OnPage\OnPageSeoResult;
use SeoAiChecker\Serp\SerpResult;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleReporter
{
    public function report(SymfonyStyle $io, SerpResult $serp, string $targetDomain, ?OnPageSeoResult $onPage): void
    {
        $io->section(sprintf('Anahtar kelime: "%s"', $serp->keyword));

        if ($serp->blocked) {
            $io->error($serp->blockReason ?? 'Google istegi engellendi.');

            return;
        }

        $position = $serp->positionOf($targetDomain);
        if ($position !== null) {
            $io->success(sprintf('%s domaini %d. sirada bulundu.', $targetDomain, $position));
        } else {
            $io->warning(sprintf('%s domaini ilk %d organik sonuc arasinda bulunamadi.', $targetDomain, count($serp->organicResults)));
        }

        if ($serp->organicResults !== []) {
            $rows = [];
            foreach (array_slice($serp->organicResults, 0, 10) as $result) {
                $rows[] = [
                    $result->position,
                    $result->domain,
                    mb_strlen($result->title) > 70 ? mb_substr($result->title, 0, 67) . '...' : $result->title,
                ];
            }
            $io->table(['#', 'Domain', 'Baslik'], $rows);
        }

        $ai = $serp->aiOverview;
        if (!$ai->present) {
            $io->text('AI Overview: tespit edilmedi (Google bu sorgu icin gostermiyor olabilir ya da JS ile render edildigi icin yakalanamadi).');
        } else {
            $io->text('AI Overview: bulundu.');
            if ($ai->citedDomains !== []) {
                $cited = $ai->citesDomain($targetDomain) ? 'EVET, listede var' : 'hayir, listede yok';
                $io->text(sprintf('  Kaynak domainler: %s', implode(', ', $ai->citedDomains)));
                $io->text(sprintf('  %s AI Overview kaynaklari arasinda mi? %s', $targetDomain, $cited));
            } elseif ($ai->note !== null) {
                $io->text('  ' . $ai->note);
            }
        }

        if ($onPage !== null) {
            $this->reportOnPage($io, $onPage);
        }
    }

    private function reportOnPage(SymfonyStyle $io, OnPageSeoResult $onPage): void
    {
        $io->text('');
        $io->text(sprintf('On-page SEO (%s):', $onPage->url));

        $rows = [
            ['Title', sprintf('%s (%d karakter)', $onPage->title ?? '(yok)', $onPage->titleLength())],
            ['Meta description', sprintf('%s (%d karakter)', $onPage->metaDescription ?? '(yok)', $onPage->descriptionLength())],
            ['Canonical', $onPage->canonical ?? '(yok)'],
            ['Meta robots', $onPage->metaRobots ?? '(yok)'],
            ['H1 sayisi', (string) count($onPage->h1s)],
            ['H2 sayisi', (string) $onPage->h2Count],
            ['Kelime sayisi', (string) $onPage->wordCount],
            ['Alt etiketi eksik gorsel', (string) $onPage->imagesMissingAlt],
            ['Ic/dis link', sprintf('%d / %d', $onPage->internalLinks, $onPage->externalLinks)],
            ['Yapisal veri (JSON-LD)', $onPage->hasStructuredData ? 'var' : 'yok'],
            ['Sayfa yanit suresi', sprintf('%.0f ms', $onPage->fetchTimeMs)],
        ];

        if ($onPage->keyword !== null) {
            $rows[] = ['Anahtar kelime yogunlugu', sprintf('%.2f%%', $onPage->keywordDensityPercent)];
            $rows[] = [
                'Anahtar kelime konumu',
                sprintf(
                    'Title: %s | H1: %s | Description: %s',
                    $onPage->keywordInTitle ? 'var' : 'yok',
                    $onPage->keywordInH1 ? 'var' : 'yok',
                    $onPage->keywordInDescription ? 'var' : 'yok',
                ),
            ];
        }

        $io->table(['Alan', 'Deger'], $rows);
    }
}
