<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DomainRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class DomainService
{
    public function __construct(
        private readonly WhoisService      $whois,
        private readonly DomainRepository  $repository,
        private readonly string            $alertEmail,
        private readonly ?MailerInterface  $mailer = null,
        private readonly string            $mailerFrom = '',
    ) {}

    /**
     * @throws \InvalidArgumentException on bad domain format / unsupported TLD
     * @throws \RuntimeException on duplicate or WHOIS failure
     */
    public function add(string $input): string
    {
        ['label' => $label, 'tld' => $tld] = $this->parseDomain($input);
        $normalized = strtoupper($label . '.' . $tld);

        if ($this->repository->existsByDomain($normalized)) {
            throw new \RuntimeException("$normalized is already being monitored.");
        }

        $result = $this->whois->lookup($label, $tld);
        $this->repository->insert($this->toRow($normalized, $result));

        return $normalized;
    }

    /**
     * Refresh all monitored domains; returns a map of domain → change list.
     *
     * @return array<string, string[]>
     */
    public function refreshAll(): array
    {
        $report = [];
        foreach ($this->repository->all() as $row) {
            $report[$row['domain']] = $this->refreshRow($row);
        }
        return $report;
    }

    /**
     * Refresh a single domain by its stored name (e.g. "EXAMPLE.COM.TR").
     *
     * @return string[] list of changes detected
     * @throws \RuntimeException if domain is not in the database
     */
    public function refreshOne(string $domain): array
    {
        $domain = strtoupper($domain);
        $row    = $this->repository->findByDomain($domain);
        if ($row === null) {
            throw new \RuntimeException("Domain $domain not found in the database.");
        }
        return $this->refreshRow($row);
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    /** @return string[] changes detected */
    private function refreshRow(array $row): array
    {
        try {
            ['label' => $label, 'tld' => $tld] = $this->parseDomain(strtolower($row['domain']));
            $result = $this->whois->lookup($label, $tld);
        } catch (\Throwable) {
            return [];
        }

        $newRow  = $this->toRow($row['domain'], $result);
        $changes = $this->detectChanges($row, $newRow);

        $this->repository->update($row['domain'], $newRow);

        if ($changes !== [] && $this->alertEmail !== '') {
            $this->sendAlert($row['domain'], $changes);
        }

        return $changes;
    }

    private function parseDomain(string $input): array
    {
        $input = strtolower(trim($input));
        $input = preg_replace('/^www\./', '', $input) ?? $input;
        $parts = explode('.', $input);

        if (count($parts) < 2) {
            throw new \InvalidArgumentException("Invalid domain format. Example: example.com or example.com.tr");
        }

        // Detect compound TLDs (e.g. com.tr, co.uk, com.au) before single-part fallback
        if (count($parts) >= 3) {
            $candidate = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
            if (in_array($candidate, $this->whois->compoundTlds(), true)) {
                $label = $this->toPunycode(implode('.', array_slice($parts, 0, -2)));
                return $this->validateLabel($label, $candidate);
            }
        }

        $tld   = array_pop($parts);
        $label = $this->toPunycode(implode('.', $parts));

        return $this->validateLabel($label, $tld);
    }

    /**
     * Converts a Unicode label to its ASCII-compatible encoding (Punycode).
     * Passes ASCII labels through unchanged.
     */
    private function toPunycode(string $label): string
    {
        if (!function_exists('idn_to_ascii') || mb_detect_encoding($label, 'ASCII', true) !== false) {
            return $label;
        }

        $ascii = idn_to_ascii($label, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($ascii === false) {
            throw new \InvalidArgumentException("Cannot convert \"$label\" to ASCII/Punycode.");
        }

        return $ascii;
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
        $watch   = ['register', 'nameserv1', 'nameserv2', 'status1', 'create_date', 'update_date', 'expirate_date'];
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
        $subject = "Domain alert: $domain";
        $body    = "Domain Hunter detected changes for $domain\n\n"
                 . implode("\n", $changes)
                 . "\n\n--\nDomain Hunter";
        $from    = $this->mailerFrom ?: ('domainhunter@' . gethostname());

        if ($this->mailer !== null) {
            $email = (new Email())
                ->from($from)
                ->to($this->alertEmail)
                ->subject($subject)
                ->text($body);
            $this->mailer->send($email);
            return;
        }

        mail($this->alertEmail, $subject, $body, "From: $from");
    }
}
