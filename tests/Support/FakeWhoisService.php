<?php

declare(strict_types=1);

namespace App\Tests\Support;

use BahriCanli\DomainHunter\WhoisResult;
use BahriCanli\DomainHunter\WhoisService;

/**
 * Stubs WhoisService::lookup() with a scripted, in-memory queue of results
 * so DomainService can be tested without making real WHOIS/RDAP calls.
 */
final class FakeWhoisService extends WhoisService
{
    /** @var array<string, WhoisResult|null> keyed by "label.tld" */
    private array $results = [];

    /** @var array{label: string, tld: string}[] */
    public array $calls = [];

    public function willReturn(string $label, string $tld, ?WhoisResult $result): void
    {
        $this->results["$label.$tld"] = $result;
    }

    public function lookup(string $label, string $tld): ?WhoisResult
    {
        $this->calls[] = ['label' => $label, 'tld' => $tld];

        return $this->results["$label.$tld"] ?? null;
    }

    public static function resultWith(array $overrides = []): WhoisResult
    {
        $result = new WhoisResult();
        foreach ($overrides as $property => $value) {
            $result->$property = $value;
        }

        return $result;
    }
}
