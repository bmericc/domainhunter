<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\DomainRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'domain:delete', description: 'Remove a domain from monitoring')]
class DomainDeleteCommand extends Command
{
    public function __construct(private readonly DomainRepository $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name to remove (e.g. EXAMPLE.COM)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $domain = strtoupper((string) $input->getArgument('domain'));

        if (!$this->repository->existsByDomain($domain)) {
            $io->error("Domain $domain is not in the monitoring list.");
            return Command::FAILURE;
        }

        if (!$input->getOption('force')) {
            if (!$io->confirm("Delete <fg=red>$domain</> from monitoring?", false)) {
                $io->text('Aborted.');
                return Command::SUCCESS;
            }
        }

        $this->repository->deleteByDomain($domain);
        $io->success("$domain removed.");

        return Command::SUCCESS;
    }
}
