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

#[AsCommand(name: 'domain:add', description: 'Add a domain to the monitoring list')]
class DomainAddCommand extends Command
{
    public function __construct(private readonly DomainService $service)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('domain', InputArgument::REQUIRED, 'Domain name (e.g. example.com or türkiye.com.tr)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $domain = (string) $input->getArgument('domain');

        $io->text("Adding <info>$domain</info> …");

        try {
            ['domain' => $stored, 'registered' => $registered] = $this->service->add($domain);
            if ($registered) {
                $io->success("Domain added: $stored");
            } else {
                $io->warning("Domain added: $stored (not registered — no WHOIS data available)");
            }
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
