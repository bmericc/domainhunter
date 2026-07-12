<?php

declare(strict_types=1);

namespace App\Service;

class WhoisService
{
    // 'param' : query prefix required by some registries (e.g. DENIC)
    // 'parser': named parser method for non-standard WHOIS response formats
    private const SERVERS = [
        // ── Generic TLDs ─────────────────────────────────────────────────────
        'com'    => ['rdap' => 'https://rdap.verisign.com/com/v1/domain/'],
        'net'    => ['rdap' => 'https://rdap.verisign.com/net/v1/domain/'],
        'org'    => ['rdap' => 'https://rdap.publicinterestregistry.org/rdap/domain/'],
        'info'   => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'biz'    => ['rdap' => 'https://rdap.nic.biz/domain/'],
        'io'     => ['host' => 'whois.nic.io',               'free' => 'is available'],
        'co'     => ['host' => 'whois.nic.co',               'free' => 'No Data Found'],
        'app'    => ['rdap' => 'https://pubapi.registry.google/rdap/domain/'],
        'dev'    => ['rdap' => 'https://pubapi.registry.google/rdap/domain/'],
        'ai'     => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'me'     => ['host' => 'whois.nic.me',               'free' => 'NOT FOUND'],
        'tv'     => ['rdap' => 'https://rdap.nic.tv/domain/'],
        'xyz'    => ['rdap' => 'https://rdap.centralnic.com/xyz/domain/'],
        'online' => ['rdap' => 'https://rdap.radix.host/rdap/domain/'],
        'store'  => ['rdap' => 'https://rdap.radix.host/rdap/domain/'],
        'tech'   => ['rdap' => 'https://rdap.radix.host/rdap/domain/'],
        'site'   => ['rdap' => 'https://rdap.radix.host/rdap/domain/'],
        'shop'   => ['rdap' => 'https://rdap.gmoregistry.net/rdap/domain/'],
        'club'   => ['rdap' => 'https://rdap.nic.club/domain/'],
        'pro'    => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'mobi'   => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'name'   => ['rdap' => 'https://tld-rdap.verisign.com/name/v1/domain/'],
        'tel'    => ['rdap' => 'https://rdap.nic.tel/domain/'],
        'global' => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'link'   => ['rdap' => 'https://rdap.uniregistry.net/rdap/domain/'],
        'click'  => ['rdap' => 'https://rdap.registry.click/rdap/domain/'],
        'media'  => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'agency' => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'blog'   => ['rdap' => 'https://rdap.blog.fury.ca/rdap/domain/'],
        'cloud'  => ['rdap' => 'https://rdap.registry.cloud/rdap/domain/'],
        'email'  => ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'network'=> ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'digital'=> ['rdap' => 'https://rdap.identitydigital.services/rdap/domain/'],
        'design' => ['rdap' => 'https://rdap.nic.design/domain/'],
        'space'  => ['rdap' => 'https://rdap.radix.host/rdap/domain/'],
        'web'    => ['host' => 'whois.nic.web',              'free' => 'NOT FOUND'],

        // ── Europe ccTLDs ────────────────────────────────────────────────────
        'eu'     => ['host' => 'whois.eu',                   'free' => 'Status: AVAILABLE'],
        'uk'     => ['rdap' => 'https://rdap.nominet.uk/uk/domain/'],
        'de'     => ['host' => 'whois.denic.de',             'free' => 'Status: free', 'param' => '-T dn,ace '],
        'fr'     => ['rdap' => 'https://rdap.nic.fr/domain/'],
        'nl'     => ['rdap' => 'https://rdap.sidn.nl/domain/'],
        'es'     => ['host' => 'whois.nic.es',               'free' => 'No entries found'],
        'it'     => ['host' => 'whois.nic.it',               'free' => 'Status: AVAILABLE'],
        'pl'     => ['rdap' => 'https://rdap.dns.pl/domain/'],
        'se'     => ['host' => 'whois.iis.se',               'free' => 'state: free'],
        'ch'     => ['host' => 'whois.nic.ch',               'free' => 'We do not have an entry'],
        'be'     => ['host' => 'whois.dns.be',               'free' => 'Status: FREE'],
        'at'     => ['host' => 'whois.nic.at',               'free' => 'nothing found'],
        'cz'     => ['rdap' => 'https://rdap.nic.cz/domain/'],
        'dk'     => ['host' => 'whois.dk-hostmaster.dk',     'free' => 'Object does not exist'],
        'no'     => ['rdap' => 'https://rdap.norid.no/domain/'],
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
        'is'     => ['rdap' => 'https://rdap.isnic.is/rdap/domain/'],

        // ── Turkey ───────────────────────────────────────────────────────────
        'tr'     => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'com.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'net.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'org.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'edu.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'gov.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'web.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'bel.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'k12.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'pol.tr' => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'av.tr'  => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],
        'dr.tr'  => ['host' => 'whois.trabis.gov.tr',        'free' => 'Not found in database', 'parser' => 'tr'],

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
        'ca'     => ['rdap' => 'https://rdap.ca.fury.ca/rdap/domain/'],
        'mx'     => ['host' => 'whois.mx',                   'free' => 'Object does not exist'],
        'br'     => ['rdap' => 'https://rdap.registro.br/domain/'],
        'ar'     => ['rdap' => 'https://rdap.nic.ar/domain/'],

        // ── Asia-Pacific ─────────────────────────────────────────────────────
        'jp'     => ['host' => 'whois.jprs.jp',              'free' => 'No match'],
        'cn'     => ['host' => 'whois.cnnic.cn',             'free' => 'No matching record'],
        'in'     => ['rdap' => 'https://rdap.nixiregistry.in/rdap/domain/'],
        'ru'     => ['host' => 'whois.tcinet.ru',            'free' => 'No entries found'],
        'kr'     => ['host' => 'whois.kr',                   'free' => 'Above domain name is not registered'],
        'sg'     => ['rdap' => 'https://rdap.sgnic.sg/rdap/domain/'],
        'hk'     => ['host' => 'whois.hkirc.hk',            'free' => 'The domain name does not exist'],
        'tw'     => ['rdap' => 'https://ccrdap.twnic.tw/tw/domain/'],
        'id'     => ['rdap' => 'https://rdap.pandi.id/rdap/domain/'],
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

        if (isset($server['rdap'])) {
            $json = $this->fetchRdap($server['rdap'] . $fqdn);
            if ($json === null) {
                return null;
            }
            $data = json_decode($json, true);
            if (!is_array($data)) {
                throw new \RuntimeException("RDAP server returned invalid JSON for .$tld.");
            }
            return $this->parseRdap($data);
        }

        $param = $server['param'] ?? '';
        $raw   = $this->fetch($server['host'], $fqdn, $param);

        if ($raw === null) {
            throw new \RuntimeException(
                "Could not reach WHOIS server for .$tld ({$server['host']}:43). " .
                "Check your internet connection or firewall (outbound port 43 must be open)."
            );
        }

        if (stripos($raw, $server['free']) !== false) {
            return null;
        }

        $parserMethod = 'parse' . ucfirst($server['parser'] ?? 'generic');
        return $this->$parserMethod($raw);
    }

    private function fetchRdap(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => ['Accept: application/rdap+json'],
                CURLOPT_USERAGENT      => 'DomainHunter/2.0',
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
            if ($body === false || $err !== '') {
                throw new \RuntimeException("Could not reach RDAP server: $url ($err)");
            }
            if ($code === 404) {
                return null;
            }
            if ($code !== 200) {
                throw new \RuntimeException("RDAP server returned HTTP $code for: $url");
            }
            return (string) $body;
        }

        $ctx  = stream_context_create(['http' => [
            'timeout' => 15,
            'header'  => "Accept: application/rdap+json\r\nUser-Agent: DomainHunter/2.0\r\n",
            'ignore_errors' => true,
        ]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            throw new \RuntimeException("Could not reach RDAP server: $url");
        }
        $status = $http_response_header[0] ?? '';
        if (str_contains($status, '404')) {
            return null;
        }
        if (!str_contains($status, '200')) {
            throw new \RuntimeException("RDAP server returned non-200 for: $url ($status)");
        }
        return $body;
    }

    private function parseRdap(array $data): WhoisResult
    {
        $result = new WhoisResult();

        if (isset($data['ldhName'])) {
            $result->domainName = strtoupper($data['ldhName']);
        }

        foreach ($data['status'] ?? [] as $s) {
            $result->statuses[] = (string) $s;
        }

        foreach ($data['nameservers'] ?? [] as $ns) {
            if (isset($ns['ldhName'])) {
                $result->nameServers[] = strtolower($ns['ldhName']);
            }
        }

        foreach ($data['events'] ?? [] as $event) {
            $action = strtolower($event['eventAction'] ?? '');
            $date   = $this->parseDate($event['eventDate'] ?? '');
            match ($action) {
                'registration' => $result->creationDate   ??= $date,
                'expiration'   => $result->expirationDate ??= $date,
                'last changed' => $result->updatedDate    ??= $date,
                default        => null,
            };
        }

        foreach ($data['entities'] ?? [] as $entity) {
            if (!in_array('registrar', $entity['roles'] ?? [], true)) {
                continue;
            }
            foreach ($entity['vcardArray'][1] ?? [] as $field) {
                if (($field[0] ?? '') === 'fn') {
                    $result->registrar ??= (string) ($field[3] ?? '');
                    break;
                }
            }
            break;
        }

        return $result;
    }

    private function fetch(string $host, string $domain, string $param = ''): ?string
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            if ($attempt > 0) {
                sleep(2);
            }
            $fp = @fsockopen($host, 43, $errno, $errstr, 15);
            if (!$fp) {
                continue;
            }

            fputs($fp, $param . $domain . "\r\n");
            $data = '';
            while (!feof($fp)) {
                $data .= fgets($fp, 4096);
            }
            fclose($fp);

            if ($data !== '') {
                return $data;
            }
        }

        return null;
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
     * Parser for whois.trabis.gov.tr responses.
     *
     * Format uses "** Section:" headers. Status/frozen/transfer lines appear
     * at column-0 after the domain name header (no ** prefix). Registrar info
     * uses tab-indented "Key\t: Value" pairs. Name servers are bare hostnames
     * at column-0 under "** Domain Servers:". Dates are under "** Additional
     * Info:" with dot-padded keys ("Created on..........: YYYY-Mon-DD.").
     */
    private function parseTr(string $raw): WhoisResult
    {
        $result  = new WhoisResult();
        $section = '';

        foreach (explode("\n", $raw) as $line) {
            $line = rtrim($line);

            // "** Section Name:" or "** Section Name: value"
            if (preg_match('/^\*\*\s+([^:]+?)(?:\s*:\s*(.*))?$/', $line, $m)) {
                $section = strtolower(trim($m[1]));
                $val     = trim($m[2] ?? '');
                if ($section === 'domain name' && $val !== '') {
                    $result->domainName ??= strtoupper($val);
                }
                continue;
            }

            if ($line === '') {
                continue;
            }

            // Bare hostname under "** Domain Servers:" (no indent required)
            if ($section === 'domain servers' && preg_match('/^\s*([\w][\w.-]+\.[a-z]{2,})\s*$/i', $line, $m)) {
                $result->nameServers[] = strtolower(trim($m[1]));
                continue;
            }

            // "Key.....: Value" — handles both column-0 and indented lines,
            // and dot-padded keys like "Created on..............: date"
            if (preg_match('/^\s*([A-Za-z][^:]*?)\.*\s*:\s*(.+)/', $line, $m)) {
                $key = strtolower(trim($m[1]));
                $val = rtrim(trim($m[2]), '.');

                match (true) {
                    $key === 'domain status' && $val !== '-'
                        => $result->statuses[] = $val,

                    $section === 'registrar' && $key === 'organization name'
                        => $result->registrar ??= $val,

                    $section === 'additional info' && str_starts_with($key, 'created on')
                        => $result->creationDate ??= $this->parseDate($val),

                    $section === 'additional info' && str_starts_with($key, 'expires on')
                        => $result->expirationDate ??= $this->parseDate($val),

                    $section === 'additional info' && (
                        str_starts_with($key, 'updated on') || str_starts_with($key, 'modified')
                    ) => $result->updatedDate ??= $this->parseDate($val),

                    default => null,
                };
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
