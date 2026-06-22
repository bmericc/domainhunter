<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DomainService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'domain:refresh', description: 'Query WHOIS and update one or all domains')]
class DomainRefreshCommand extends Command
{
    public function __construct(private readonly DomainService $service)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'domain',
            InputArgument::OPTIONAL,
            'Domain to refresh (leave blank to refresh all)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $domain = $input->getArgument('domain');

        if ($domain !== null) {
            $domain = strtoupper((string) $domain);
            $io->text("Refreshing <info>$domain</info> …");
            try {
                $changes = $this->service->refreshOne($domain);
                $this->printChanges($io, $domain, $changes);
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());
                return Command::FAILURE;
            }
            return Command::SUCCESS;
        }

        $io->text('Refreshing all domains …');
        $report = $this->service->refreshAll();

        if ($report === []) {
            $io->info('No domains to refresh.');
            return Command::SUCCESS;
        }

        foreach ($report as $d => $changes) {
            $this->printChanges($io, $d, $changes);
        }

        $changed = count(array_filter($report));
        $io->success(sprintf('%d domain(s) refreshed, %d with changes.', count($report), $changed));

        return Command::SUCCESS;
    }

    private function printChanges(SymfonyStyle $io, string $domain, array $changes): void
    {
        if ($changes === []) {
            $io->text("  <info>$domain</info> — no changes");
            return;
        }

        $io->text("  <comment>$domain</comment> — " . count($changes) . ' change(s):');
        foreach ($changes as $change) {
            $io->text("    • $change");
        }
    }
}
