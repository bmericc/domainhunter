<?php

declare(strict_types=1);

namespace SeoAiChecker\Keyword;

final class KeywordEntry
{
    public function __construct(
        public readonly string $keyword,
        public readonly ?string $url = null,
    ) {
    }
}
