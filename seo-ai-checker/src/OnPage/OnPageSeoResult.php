<?php

declare(strict_types=1);

namespace SeoAiChecker\OnPage;

final class OnPageSeoResult
{
    /**
     * @param string[] $h1s
     */
    public function __construct(
        public readonly string $url,
        public readonly ?string $title,
        public readonly ?string $metaDescription,
        public readonly ?string $canonical,
        public readonly ?string $metaRobots,
        public readonly array $h1s,
        public readonly int $h2Count,
        public readonly int $wordCount,
        public readonly ?string $keyword,
        public readonly float $keywordDensityPercent,
        public readonly bool $keywordInTitle,
        public readonly bool $keywordInH1,
        public readonly bool $keywordInDescription,
        public readonly int $imagesMissingAlt,
        public readonly int $internalLinks,
        public readonly int $externalLinks,
        public readonly bool $hasStructuredData,
        public readonly float $fetchTimeMs,
    ) {
    }

    public function titleLength(): int
    {
        return $this->title !== null ? mb_strlen($this->title) : 0;
    }

    public function descriptionLength(): int
    {
        return $this->metaDescription !== null ? mb_strlen($this->metaDescription) : 0;
    }
}
