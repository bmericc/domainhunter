<?php

declare(strict_types=1);

namespace App\Service;

class WhoisService
{
    // 'param' : query prefix required by some registries (e.g. DENIC)
    // 'parser': named parser method for non-standard WHOIS response formats
    private const SERVERS = [
        // ── Generic TLDs ─────────────────────────────────────────────────────
        'com'    => ['host' => 'whois.verisign-grs.com',    'free' => 'No match for'],
        'net'    => ['host' => 'whois.verisign-grs.com',    'free' => 'No match for'],
        'org'    => ['host' => 'whois.pir.org',              'free' => 'NOT FOUND'],
        'info'   => ['host' => 'whois.afilias.net',          'free' => 'NOT FOUND'],
        'biz'    => ['host' => 'whois.biz',                  'free' => 'Not found'],
        'io'     => ['host' => 'whois.nic.io',               'free' => 'is available'],
        'co'     => ['host' => 'whois.nic.co',               'free' => 'No Data Found'],
        'app'    => ['host' => 'whois.nic.google',           'free' => 'NOT FOUND'],
        'dev'    => ['host' => 'whois.nic.google',           'free' => 'NOT FOUND'],
        'ai'     => ['host' => 'whois.nic.ai',               'free' => 'Not found'],
        'me'     => ['host' => 'whois.nic.me',               'free' => 'NOT FOUND'],
        'tv'     => ['host' => 'tvwhois.verisign-grs.com',   'free' => 'No match for'],
        'xyz'    => ['host' => 'whois.nic.xyz',              'free' => 'No match for'],
        'online' => ['host' => 'whois.nic.online',           'free' => 'NOT FOUND'],
        'store'  => ['host' => 'whois.nic.store',            'free' => 'NOT FOUND'],
        'tech'   => ['host' => 'whois.nic.tech',             'free' => 'NOT FOUND'],
        'site'   => ['host' => 'whois.nic.site',             'free' => 'NOT FOUND'],
        'shop'   => ['host' => 'whois.nic.shop',             'free' => 'NOT FOUND'],
        'club'   => ['host' => 'whois.nic.club',             'free' => 'NOT FOUND'],
        'pro'    => ['host' => 'whois.afilias.net',          'free' => 'NOT FOUND'],
        'mobi'   => ['host' => 'whois.afilias.net',          'free' => 'NOT FOUND'],
        'name'   => ['host' => 'whois.nic.name',             'free' => 'No match for'],
        'tel'    => ['host' => 'whois.nic.tel',              'free' => 'NOT FOUND'],
        'global' => ['host' => 'whois.nic.global',           'free' => 'NOT FOUND'],
        'link'   => ['host' => 'whois.uniregistry.net',      'free' => 'NOT FOUND'],
        'click'  => ['host' => 'whois.uniregistry.net',      'free' => 'NOT FOUND'],
        'media'  => ['host' => 'whois.nic.media',            'free' => 'NOT FOUND'],
        'agency' => ['host' => 'whois.nic.agency',           'free' => 'NOT FOUND'],
        'blog'   => ['host' => 'whois.nic.blog',             'free' => 'NOT FOUND'],
        'cloud'  => ['host' => 'whois.nic.cloud',            'free' => 'NOT FOUND'],
        'email'  => ['host' => 'whois.nic.email',            'free' => 'NOT FOUND'],
        'network'=> ['host' => 'whois.nic.network',          'free' => 'NOT FOUND'],
        'digital'=> ['host' => 'whois.nic.digital',          'free' => 'NOT FOUND'],
        'design' => ['host' => 'whois.nic.design',           'free' => 'NOT FOUND'],
        'space'  => ['host' => 'whois.nic.space',            'free' => 'NOT FOUND'],
        'web'    => ['host' => 'whois.nic.web',              'free' => 'NOT FOUND'],

        // ── Europe ccTLDs ────────────────────────────────────────────────────
        'eu'     => ['host' => 'whois.eu',                   'free' => 'Status: AVAILABLE'],
        'uk'     => ['host' => 'whois.nic.uk',               'free' => 'No match for'],
        'de'     => ['host' => 'whois.denic.de',             'free' => 'Status: free', 'param' => '-T dn,ace '],
        'fr'     => ['host' => 'whois.afnic.fr',             'free' => 'No entries found'],
        'nl'     => ['host' => 'whois.domain-registry.nl',   'free' => 'is free'],
        'es'     => ['host' => 'whois.nic.es',               'free' => 'No entries found'],
        'it'     => ['host' => 'whois.nic.it',               'free' => 'Status: AVAILABLE'],
        'pl'     => ['host' => 'whois.dns.pl',               'free' => 'No information available'],
        'se'     => ['host' => 'whois.iis.se',               'free' => 'state: free'],
        'ch'     => ['host' => 'whois.nic.ch',               'free' => 'We do not have an entry'],
        'be'     => ['host' => 'whois.dns.be',               'free' => 'Status: FREE'],
        'at'     => ['host' => 'whois.nic.at',               'free' => 'nothing found'],
        'cz'     => ['host' => 'whois.nic.cz',               'free' => 'No entries found'],
        'dk'     => ['host' => 'whois.dk-hostmaster.dk',     'free' => 'Object does not exist'],
        'no'     => ['host' => 'whois.norid.no',             'free' => 'no matches'],
        'fi'     => ['host' => 'whois.fi',                   'free' => 'Domain not found'],
        'pt'     => ['host' => 'whois.dns.pt',               'free' => 'No entries found'],
        'ro'     => ['host' => 'whois.rotld.ro',             'free' => 'No entries found'],
        'hu'     => ['host' => 'whois.nic.hu',               'free' => 'No entries found'],
        'sk'     => ['host' => 'whois.sk-nic.sk',            'free' => 'Object does not exist'],
        'bg'     => ['host' => 'whois.register.bg',          'free' => 'does not exist'],
        'hr'     => ['host' => 'whois.dns.hr',               'free' => 'Object does not exist'],
        'gr'     => ['host' => 'whois.grnet.gr',             'free' => 'No entries found'],
        'lt'     => ['host' => 'whois.domreg.lt',            'free' => 'No entries found'],
        'lv'     => ['host' => 'whois.nic.lv',               'free' => 'No entries found'],
        'ee'     => ['host' => 'whois.tld.ee',               'free' => 'NOT FOUND'],
        'lu'     => ['host' => 'whois.dns.lu',               'free' => 'No entries found'],
        'ie'     => ['host' => 'whois.iedr.ie',              'free' => 'No entries found'],
        'is'     => ['host' => 'whois.isnic.is',             'free' => 'No entries found'],

        // ── Turkey ───────────────────────────────────────────────────────────
        'tr'     => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'com.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'net.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'org.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'edu.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'gov.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'web.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'bel.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'k12.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'pol.tr' => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'av.tr'  => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],
        'dr.tr'  => ['host' => 'whois.nic.tr',               'free' => 'Not found in database', 'parser' => 'tr'],

        // ── UK SLDs ──────────────────────────────────────────────────────────
        'co.uk'  => ['host' => 'whois.nic.uk',               'free' => 'No match for'],
        'org.uk' => ['host' => 'whois.nic.uk',               'free' => 'No match for'],
        'me.uk'  => ['host' => 'whois.nic.uk',               'free' => 'No match for'],
        'net.uk' => ['host' => 'whois.nic.uk',               'free' => 'No match for'],
        'ltd.uk' => ['host' => 'whois.nic.uk',               'free' => 'No match for'],
        'plc.uk' => ['host' => 'whois.nic.uk',               'free' => 'No match for'],

        // ── Australia SLDs ───────────────────────────────────────────────────
        'au'     => ['host' => 'whois.auda.org.au',          'free' => 'No Data Found'],
        'com.au' => ['host' => 'whois.auda.org.au',          'free' => 'No Data Found'],
        'net.au' => ['host' => 'whois.auda.org.au',          'free' => 'No Data Found'],
        'org.au' => ['host' => 'whois.auda.org.au',          'free' => 'No Data Found'],
        'id.au'  => ['host' => 'whois.auda.org.au',          'free' => 'No Data Found'],

        // ── Americas ─────────────────────────────────────────────────────────
        'us'     => ['host' => 'whois.nic.us',               'free' => 'Not found'],
        'ca'     => ['host' => 'whois.cira.ca',              'free' => 'Domain status: available'],
        'mx'     => ['host' => 'whois.mx',                   'free' => 'Object does not exist'],
        'br'     => ['host' => 'whois.registro.br',          'free' => 'No match for'],
        'ar'     => ['host' => 'whois.nic.ar',               'free' => 'No entries found'],

        // ── Asia-Pacific ─────────────────────────────────────────────────────
        'jp'     => ['host' => 'whois.jprs.jp',              'free' => 'No match'],
        'cn'     => ['host' => 'whois.cnnic.cn',             'free' => 'No matching record'],
        'in'     => ['host' => 'whois.registry.in',          'free' => 'NOT FOUND'],
        'ru'     => ['host' => 'whois.tcinet.ru',            'free' => 'No entries found'],
        'kr'     => ['host' => 'whois.kr',                   'free' => 'Above domain name is not registered'],
        'sg'     => ['host' => 'whois.sgnic.sg',             'free' => 'No entries found'],
        'hk'     => ['host' => 'whois.hkirc.hk',            'free' => 'The domain name does not exist'],
        'tw'     => ['host' => 'whois.twnic.net',            'free' => 'No entries found'],
        'id'     => ['host' => 'whois.id',                   'free' => 'NOT FOUND'],
        'my'     => ['host' => 'whois.mynic.my',             'free' => 'DOMAIN NOT FOUND'],

        // ── Africa & Middle East ─────────────────────────────────────────────
        'za'     => ['host' => 'whois.registry.net.za',      'free' => 'No information available'],
        'ae'     => ['host' => 'whois.aeda.net.ae',          'free' => 'No Data Found'],
        'sa'     => ['host' => 'whois.nic.net.sa',           'free' => 'No match for'],
    ];

    /**
     * Returns TLDs that contain a dot (e.g. "com.tr", "co.uk").
     * Used by DomainService for compound-TLD detection during parsing.
     */
    public function compoundTlds(): array
    {
        return array_values(array_filter(
            array_keys(self::SERVERS),
            static fn(string $k) => str_contains($k, '.'),
        ));
    }

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
        $param  = $server['param'] ?? '';
        $raw    = $this->fetch($server['host'], $fqdn, $param);

        if ($raw === null) {
            throw new \RuntimeException("Could not reach WHOIS server for .$tld.");
        }

        if (stripos($raw, $server['free']) !== false) {
            return null;
        }

        $parserMethod = 'parse' . ucfirst($server['parser'] ?? 'generic');
        return $this->$parserMethod($raw);
    }

    private function fetch(string $host, string $domain, string $param = ''): ?string
    {
        $fp = @fsockopen($host, 43, $errno, $errstr, 10);
        if (!$fp) {
            return null;
        }

        fputs($fp, $param . $domain . "\r\n");
        $data = '';
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }
        fclose($fp);

        return $data !== '' ? $data : null;
    }

    // ── Parsers ───────────────────────────────────────────────────────────────

    private function parseGeneric(string $raw): WhoisResult
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
                'domain name', 'domain'
                    => $result->domainName ??= strtoupper($value),
                'registrar', 'registrar name', 'sponsoring registrar', 'organisation'
                    => $result->registrar ??= $value,
                'whois server', 'registrar whois server'
                    => $result->whoisServer ??= $value,
                'referral url', 'registrar url'
                    => $result->referralUrl ??= $value,
                'updated date', 'last modified', 'last update', 'changed', 'modified',
                'last-modified', 'last updated'
                    => $result->updatedDate ??= $this->parseDate($value),
                'creation date', 'created date', 'created', 'registered',
                'registered on', 'registration time'
                    => $result->creationDate ??= $this->parseDate($value),
                'registry expiry date', 'registrar registration expiration date',
                'expiration date', 'expiry date', 'expires', 'expire date',
                'paid-till', 'free-date', 'renewal date'
                    => $result->expirationDate ??= $this->parseDate($value),
                'name server', 'nserver', 'nameserver', 'name servers'
                    => $result->nameServers[] = strtolower(explode(' ', $value)[0]),
                'domain status', 'status'
                    => $result->statuses[] = explode(' ', $value)[0],
                default => null,
            };
        }

        return $result;
    }

    /**
     * Parser for whois.nic.tr responses.
     *
     * The Turkish NIC uses a sectioned format with "** Section:" headers,
     * tabbed key-value pairs, and bare hostnames for name servers.
     */
    private function parseTr(string $raw): WhoisResult
    {
        $result  = new WhoisResult();
        $section = '';

        foreach (explode("\n", $raw) as $line) {
            $line = rtrim($line);

            // "** Domain Name:  example.com.tr"  or  "** Registrar:"
            if (preg_match('/^\*\*\s+([^:]+?)(?::\s*(.*))?$/', $line, $m)) {
                $section = strtolower(trim($m[1]));
                $val     = trim($m[2] ?? '');
                if ($section === 'domain name' && $val !== '') {
                    $result->domainName ??= strtoupper($val);
                }
                continue;
            }

            // Tabbed key : value lines (e.g. "	Organization Name : Foo")
            if (preg_match('/^\s+([^:]+?)\s*:\s*(.+)/', $line, $m)) {
                $key = strtolower(trim($m[1]));
                $val = trim($m[2]);

                match (true) {
                    $section === 'registrar' && $key === 'organization name'
                        => $result->registrar ??= $val,
                    str_starts_with($key, 'created on')
                        => $result->creationDate ??= $this->parseDate($val),
                    str_starts_with($key, 'expires on')
                        => $result->expirationDate ??= $this->parseDate($val),
                    str_starts_with($key, 'updated on') || str_starts_with($key, 'modified')
                        => $result->updatedDate ??= $this->parseDate($val),
                    default => null,
                };
                continue;
            }

            // Bare hostname lines under "** Name Servers:" section
            if ($section === 'name servers' && preg_match('/^\s+([\w][\w.-]+\.[a-z]{2,})\s*$/i', $line, $m)) {
                $result->nameServers[] = strtolower(trim($m[1]));
                continue;
            }

            // Status line (e.g. "Active")
            if ($section === 'domain status' && preg_match('/^\s*(Active|Hold|Suspended|Pending|Locked)\s*$/i', $line, $m)) {
                $result->statuses[] = trim($m[1]);
            }
        }

        return $result;
    }

    private function parseDate(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }
        // Strip trailing timezone names / extra tokens (e.g. "2024-01-01T00:00:00Z")
        $date = preg_replace('/\s+\w+$/', '', $date) ?? $date;
        $ts = strtotime($date);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }
}
