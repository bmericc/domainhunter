<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DomainRepository;

class DomainService
{
    public function __construct(
        private readonly WhoisService    $whois,
        private readonly DomainRepository $repository,
        private readonly string          $alertEmail,
    ) {}

    /**
     * @throws \InvalidArgumentException on bad domain format
     * @throws \RuntimeException on duplicate or WHOIS failure
     */
    public function add(string $input): void
    {
        ['label' => $label, 'tld' => $tld] = $this->parseDomain($input);
        $normalized = strtoupper($label . '.' . $tld);

        if ($this->repository->existsByDomain($normalized)) {
            throw new \RuntimeException("$normalized is already being monitored.");
        }

        $result = $this->whois->lookup($label, $tld);
        $this->repository->insert($this->toRow($normalized, $result));
    }

    public function refreshAll(): void
    {
        foreach ($this->repository->all() as $row) {
            $this->refresh($row);
        }
    }

    private function refresh(array $row): void
    {
        try {
            ['label' => $label, 'tld' => $tld] = $this->parseDomain(strtolower($row['domain']));
            $result = $this->whois->lookup($label, $tld);
        } catch (\Throwable) {
            return;
        }

        $newRow  = $this->toRow($row['domain'], $result);
        $changes = $this->detectChanges($row, $newRow);

        $this->repository->update($row['domain'], $newRow);

        if ($changes !== [] && $this->alertEmail !== '') {
            $this->sendAlert($row['domain'], $changes);
        }
    }

    private function parseDomain(string $input): array
    {
        $input = strtolower(trim($input));
        $input = preg_replace('/^www\./', '', $input) ?? $input;
        $parts = explode('.', $input);

        if (count($parts) < 2) {
            throw new \InvalidArgumentException("Invalid domain format. Example: example.com or example.com.tr");
        }

        // Detect compound TLDs (e.g. com.tr, co.uk, com.au) before falling back to single-part
        if (count($parts) >= 3) {
            $candidate = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
            if (in_array($candidate, $this->whois->compoundTlds(), true)) {
                $label = implode('.', array_slice($parts, 0, -2));
                return $this->validateLabel($label, $candidate);
            }
        }

        $tld   = array_pop($parts);
        $label = implode('.', $parts);

        return $this->validateLabel($label, $tld);
    }

    private function validateLabel(string $label, string $tld): array
    {
        if (strlen($label) < 2) {
            throw new \InvalidArgumentException("Domain label is too short.");
        }
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?$/', $label)) {
            throw new \InvalidArgumentException("Domain label contains invalid characters.");
        }
        return ['label' => $label, 'tld' => $tld];
    }

    private function toRow(string $domain, ?WhoisResult $r): array
    {
        $ns = array_pad($r?->nameServers ?? [], 5, '');
        $st = array_pad($r?->statuses ?? [], 3, '');

        return [
            ':domain'        => $domain,
            ':register'      => $r?->registrar ?? '',
            ':whois_serv'    => $r?->whoisServer ?? '',
            ':ref_url'       => $r?->referralUrl ?? '',
            ':nameserv1'     => $ns[0],
            ':nameserv2'     => $ns[1],
            ':nameserv3'     => $ns[2],
            ':nameserv4'     => $ns[3],
            ':nameserv5'     => $ns[4],
            ':status1'       => $st[0],
            ':status2'       => $st[1],
            ':status3'       => $st[2],
            ':create_date'   => $r?->creationDate,
            ':update_date'   => $r?->updatedDate,
            ':expirate_date' => $r?->expirationDate,
        ];
    }

    private function detectChanges(array $old, array $new): array
    {
        $watch = ['register', 'nameserv1', 'nameserv2', 'status1', 'create_date', 'update_date', 'expirate_date'];
        $changes = [];

        foreach ($watch as $field) {
            $a = $old[$field] ?? '';
            $b = $new[":$field"] ?? '';
            if ($a !== $b) {
                $changes[] = "$field: \"$a\" → \"$b\"";
            }
        }

        return $changes;
    }

    private function sendAlert(string $domain, array $changes): void
    {
        $body    = "Domain alert for $domain\n\n" . implode("\n", $changes) . "\n\nDomain Hunter";
        $subject = "Domain alert for $domain";
        mail($this->alertEmail, $subject, $body, 'From: domainhunter@' . gethostname());
    }
}
