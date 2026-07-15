<?php

declare(strict_types=1);

namespace SeoAiChecker\Support;

final class Domain
{
    public static function normalize(string $host): string
    {
        $host = strtolower(trim($host));

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    public static function equals(string $a, string $b): bool
    {
        return self::normalize($a) === self::normalize($b);
    }

    public static function fromUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host !== null && $host !== false ? self::normalize($host) : null;
    }
}
