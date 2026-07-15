<?php

declare(strict_types=1);

namespace SeoAiChecker\Http;

use GuzzleHttp\Client;

final class HttpClientFactory
{
    private const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
        . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    public static function create(string $acceptLanguage, ?string $userAgent = null, ?string $proxy = null): Client
    {
        $options = [
            'headers' => [
                'User-Agent' => $userAgent !== null && $userAgent !== '' ? $userAgent : self::DEFAULT_USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => $acceptLanguage,
            ],
            'timeout' => 20.0,
            'connect_timeout' => 10.0,
            'allow_redirects' => ['max' => 5],
            'http_errors' => false,
        ];

        if ($proxy !== null && $proxy !== '') {
            $options['proxy'] = $proxy;
        }

        return new Client($options);
    }
}
