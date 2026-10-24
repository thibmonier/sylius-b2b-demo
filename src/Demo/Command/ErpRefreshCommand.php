<?php

declare(strict_types=1);

namespace App\Demo\Command;

use App\Demo\Erp\ErpDemoExchange;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * US-195: run by the entrypoint at each start of the container. A deployment brings an empty exchange directory while the database
 * survives: when no file of orders is there, the exchange is rebuilt (export, then simulated acknowledgement); otherwise nothing is
 * done. A failure is a clear message and a failure code: the entrypoint lets the demo start anyway.
 */
#[AsCommand(name: 'app:demo:erp-refresh', description: 'Rebuild the ERP exchange of the demo when the exchange directory holds no file of orders')]
final class ErpRefreshCommand extends Command
{
    public function __construct(private readonly ErpDemoExchange $exchange)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $rebuilt = $this->exchange->refreshIfEmpty();
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>The ERP exchange of the demo could not be rebuilt (%s): the pages "Flat-file exchange" and "ERP exchanges" stay empty until the next reset.</error>', $e::class));

            return Command::FAILURE;
        }

        $output->writeln($rebuilt ? 'ERP exchange rebuilt: orders exported, acknowledgement simulated.' : 'Nothing to do: the exchange directory already holds a file of orders.');

        return Command::SUCCESS;
    }
}
