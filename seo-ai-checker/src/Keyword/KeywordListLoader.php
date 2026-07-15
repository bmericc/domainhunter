<?php

declare(strict_types=1);

namespace SeoAiChecker\Keyword;

use RuntimeException;

final class KeywordListLoader
{
    /**
     * @return KeywordEntry[]
     */
    public function loadFromFile(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(sprintf('Anahtar kelime dosyasi okunamadi: %s', $path));
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException(sprintf('Anahtar kelime dosyasi okunamadi: %s', $path));
        }

        $entries = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('|', $line, 2);
            $keyword = trim($parts[0]);
            if ($keyword === '') {
                continue;
            }

            $url = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : null;
            $entries[] = new KeywordEntry($keyword, $url);
        }

        return $entries;
    }
}
