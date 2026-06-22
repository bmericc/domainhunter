<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\DomainRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'domain:list', description: 'List all monitored domains')]
class DomainListCommand extends Command
{
    public function __construct(private readonly DomainRepository $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('order', 'o', InputOption::VALUE_REQUIRED, 'Sort by: expiry | updated | checked', 'checked')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table | csv', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $order  = $input->getOption('order');
        $format = $input->getOption('format');

        $orderMap = [
            'expiry'  => 'expirate_date ASC',
            'updated' => 'update_date DESC',
            'checked' => 'hunter_update DESC',
        ];
        $orderSql = $orderMap[$order] ?? $orderMap['checked'];

        $domains = $this->repository->all($orderSql);

        if ($domains === []) {
            $io->info('No domains monitored yet. Use domain:add to add one.');
            return Command::SUCCESS;
        }

        if ($format === 'csv') {
            $output->writeln('id,domain,display,status,registrar,created,updated,expires,last_check');
            foreach ($domains as $d) {
                $row = array_map(
                    fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                    [
                        $d['id'],
                        $d['domain'],
                        self::unicode($d['domain']),
                        $d['status1'],
                        $d['register'],
                        $d['create_date'],
                        $d['update_date'],
                        $d['expirate_date'],
                        $d['hunter_update'],
                    ]
                );
                $output->writeln(implode(',', $row));
            }
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($domains as $i => $d) {
            $display = self::unicode($d['domain']);
            $expire  = $d['expirate_date'] ?? '—';

            if ($expire && $expire !== '—' && $expire !== '0000-00-00') {
                $daysLeft = (int) round((strtotime($expire) - time()) / 86400);
                $expire   = match (true) {
                    $daysLeft < 30  => "<error>$expire ($daysLeft d)</error>",
                    $daysLeft < 90  => "<comment>$expire ($daysLeft d)</comment>",
                    default         => $expire,
                };
            }

            $rows[] = [
                $i + 1,
                $display !== $d['domain'] ? "$d[domain]\n<comment>$display</comment>" : $d['domain'],
                $d['status1'] ?: '—',
                $d['register'] ?: '—',
                $expire,
                substr($d['hunter_update'] ?? '—', 0, 16),
            ];
        }

        $io->table(['#', 'Domain', 'Status', 'Registrar', 'Expires', 'Last Check'], $rows);
        $io->text(sprintf('<info>%d</info> domain(s) total.', count($domains)));

        return Command::SUCCESS;
    }

    /** Return the Unicode (human-readable) form of a possibly-punycode domain. */
    private static function unicode(string $domain): string
    {
        if (!str_contains(strtolower($domain), 'xn--') || !function_exists('idn_to_utf8')) {
            return $domain;
        }
        $lower   = strtolower($domain);
        $unicode = idn_to_utf8($lower, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        return ($unicode !== false && $unicode !== $lower) ? strtoupper($unicode) : $domain;
    }
}
