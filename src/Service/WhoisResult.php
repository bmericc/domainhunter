<?php

declare(strict_types=1);

namespace App\Service;

class WhoisResult
{
    public ?string $domainName    = null;
    public ?string $registrar     = null;
    public ?string $whoisServer   = null;
    public ?string $referralUrl   = null;
    public array   $nameServers   = [];
    public array   $statuses      = [];
    public ?string $creationDate  = null;
    public ?string $updatedDate   = null;
    public ?string $expirationDate = null;
}
