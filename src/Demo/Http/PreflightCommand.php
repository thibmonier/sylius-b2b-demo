<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** Container entrypoint check: exits non-zero when the demo would not be safe to serve (fail closed). */
#[AsCommand(name: 'app:demo:preflight', description: 'Check that the demo is configured safely (private gate credentials or, with the gate off, closed registrations; internal mailer)')]
final class PreflightCommand extends Command
{
    public function __construct(private readonly DemoConfigurationGuard $guard)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $violations = [...$this->guard->violations(), ...$this->guard->publicModeViolations()];
        foreach ($violations as $violation) {
            $output->writeln('<error>' . $violation . '</error>');
        }

        return [] === $violations ? Command::SUCCESS : Command::FAILURE;
    }
}
