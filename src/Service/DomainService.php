<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DomainHistoryRepository;
use App\Repository\DomainRepository;
use BahriCanli\DomainHunter\DomainParser;
use BahriCanli\DomainHunter\WhoisResult;
use BahriCanli\DomainHunter\WhoisService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class DomainService
{
    public function __construct(
        private readonly WhoisService            $whois,
        private readonly DomainParser             $parser,
        private readonly DomainRepository        $repository,
        private readonly DomainHistoryRepository $history,
        private readonly string                  $alertEmail,
        private readonly ?MailerInterface        $mailer = null,
        private readonly string                  $mailerFrom = '',
    ) {}

    public function delete(int $id): void
    {
        $this->history->deleteByDomainId($id);
        $this->repository->delete($id);
    }

    /**
     * @throws \InvalidArgumentException on bad domain format / unsupported TLD
     * @throws \RuntimeException on duplicate or WHOIS failure
     */
    /**
     * @return array{domain: string, registered: bool}
     */
    public function add(string $input): array
    {
        ['label' => $label, 'tld' => $tld] = $this->parser->parse($input);
        $normalized = strtoupper($label . '.' . $tld);

        if ($this->repository->existsByDomain($normalized)) {
            throw new \RuntimeException("$normalized is already being monitored.");
        }

        $result = $this->whois->lookup($label, $tld);
        $this->repository->insert($this->toRow($normalized, $result));

        return ['domain' => $normalized, 'registered' => $result !== null];
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
            ['label' => $label, 'tld' => $tld] = $this->parser->parse(strtolower($row['domain']));
            $result = $this->whois->lookup($label, $tld);
        } catch (\Throwable) {
            return [];
        }

        $newRow  = $this->toRow($row['domain'], $result);
        $changes = $this->detectChanges($row, $newRow);

        $this->repository->update($row['domain'], $newRow);

        if ($changes !== []) {
            foreach ($changes as $field => $pair) {
                $this->history->insert((int) $row['id'], $field, $pair['old'], $pair['new']);
            }
            if ($this->alertEmail !== '') {
                $this->sendAlert($row['domain'], $changes);
            }
        }

        return array_map(fn($f, $p) => "$f: \"{$p['old']}\" → \"{$p['new']}\"", array_keys($changes), $changes);
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

    /** @return array<string, array{old: string, new: string}> */
    private function detectChanges(array $old, array $new): array
    {
        $watch   = ['register', 'nameserv1', 'nameserv2', 'nameserv3', 'nameserv4', 'nameserv5',
                    'status1', 'status2', 'status3', 'create_date', 'update_date', 'expirate_date'];
        $changes = [];

        foreach ($watch as $field) {
            $a = (string) ($old[$field] ?? '');
            $b = (string) ($new[":$field"] ?? '');
            if ($a !== $b) {
                $changes[$field] = ['old' => $a, 'new' => $b];
            }
        }

        return $changes;
    }

    private function sendAlert(string $domain, array $changes): void
    {
        $subject = "Domain alert: $domain";
        $lines   = array_map(fn($f, $p) => "$f: \"{$p['old']}\" → \"{$p['new']}\"", array_keys($changes), $changes);
        $body    = "Domain Hunter detected changes for $domain\n\n"
                 . implode("\n", $lines)
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
