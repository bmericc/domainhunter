<?php

declare(strict_types=1);

namespace SeoAiChecker\Serp;

use SeoAiChecker\Support\Domain;

final class SerpResult
{
    /**
     * @param OrganicResult[] $organicResults
     */
    public function __construct(
        public readonly string $keyword,
        public readonly array $organicResults,
        public readonly AiOverviewResult $aiOverview,
        public readonly bool $blocked = false,
        public readonly ?string $blockReason = null,
    ) {
    }

    public function positionOf(string $domain): ?int
    {
        foreach ($this->organicResults as $result) {
            if (Domain::equals($result->domain, $domain)) {
                return $result->position;
            }
        }

        return null;
    }
}
