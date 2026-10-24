<?php

declare(strict_types=1);

namespace App\Demo\Command;

use App\Demo\Erp\ErpDemoSimulator;
use App\Demo\Erp\SimulationStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** US-188: one pass of "ERP Démo (fictif)" over the last file of orders of the exchange directory (files only, no network). */
#[AsCommand(name: 'app:demo:erp-simulate', description: 'Acknowledge the last file of orders of the exchange directory (simulated ERP of the demo)')]
final class ErpSimulateCommand extends Command
{
    public function __construct(private readonly ErpDemoSimulator $simulator)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->simulator->process();
        $output->writeln(match ($result->status) {
            SimulationStatus::NothingToProcess => 'Nothing to process.',
            SimulationStatus::Acknowledged => sprintf('%s acknowledged (simulated): %d read, %d accepted, %d rejected.', $result->source, $result->read, $result->accepted, $result->rejected),
            SimulationStatus::Rejected => sprintf('%s rejected (simulated): %d line(s), no acknowledgement.', $result->source, $result->read),
        });

        return Command::SUCCESS;
    }
}
