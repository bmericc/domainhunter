<?php

declare(strict_types=1);

namespace App\Service;

class WhoisService
{
    private const SERVERS = [
        'com' => ['host' => 'whois.verisign-grs.com', 'free' => 'No match for'],
        'net' => ['host' => 'whois.verisign-grs.com', 'free' => 'No match for'],
        'org' => ['host' => 'whois.pir.org',           'free' => 'NOT FOUND'],
    ];

    /**
     * @throws \InvalidArgumentException if TLD is unsupported
     * @throws \RuntimeException if WHOIS server is unreachable
     */
    public function lookup(string $label, string $tld): ?WhoisResult
    {
        $tld = strtolower($tld);
        if (!isset(self::SERVERS[$tld])) {
            throw new \InvalidArgumentException("TLD .$tld is not supported.");
        }

        $server = self::SERVERS[$tld];
        $fqdn   = strtolower($label) . '.' . $tld;
        $raw    = $this->fetch($server['host'], $fqdn);

        if ($raw === null) {
            throw new \RuntimeException("Could not reach WHOIS server for .$tld.");
        }

        if (stripos($raw, $server['free']) !== false) {
            return null;
        }

        return $this->parse($raw);
    }

    private function fetch(string $host, string $domain): ?string
    {
        $fp = @fsockopen($host, 43, $errno, $errstr, 10);
        if (!$fp) {
            return null;
        }

        fputs($fp, $domain . "\r\n");
        $data = '';
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }
        fclose($fp);

        return $data !== '' ? $data : null;
    }

    private function parse(string $raw): WhoisResult
    {
        $result = new WhoisResult();

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '%' || $line[0] === '>') {
                continue;
            }
            if (!str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode(':', $line, 2));
            $key = strtolower($key);

            match ($key) {
                'domain name'
                    => $result->domainName ??= strtoupper($value),
                'registrar'
                    => $result->registrar ??= $value,
                'whois server'
                    => $result->whoisServer ??= $value,
                'referral url'
                    => $result->referralUrl ??= $value,
                'updated date'
                    => $result->updatedDate ??= $this->parseDate($value),
                'creation date'
                    => $result->creationDate ??= $this->parseDate($value),
                'registry expiry date',
                'registrar registration expiration date',
                'expiration date'
                    => $result->expirationDate ??= $this->parseDate($value),
                'name server'
                    => $result->nameServers[] = strtolower($value),
                'domain status'
                    => $result->statuses[] = explode(' ', $value)[0],
                default => null,
            };
        }

        return $result;
    }

    private function parseDate(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }
        $ts = strtotime($date);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }
}
