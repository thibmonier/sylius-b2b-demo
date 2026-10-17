<?php

declare(strict_types=1);

namespace App\Demo\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Container entrypoint check (US-048 / T-048-10): tells whether the `demo` fixture suite ran to its end.
 * Exit code 0 when it did, 3 when it did not (empty database, or a load killed half-way); any other code is an error
 * (database unreachable...) and must not be read as "load the fixtures", which purges the database first.
 */
#[AsCommand(name: 'app:demo:fixtures-loaded', description: 'Exit code 0 when the demo fixtures finished loading, 3 when they must be loaded')]
final class FixturesLoadedCommand extends Command
{
    public const EXIT_NOT_LOADED = 3;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $markers = $this->connection->fetchOne('SELECT COUNT(*) FROM app_demo_load_marker');

        return \is_numeric($markers) && (int) $markers > 0 ? Command::SUCCESS : self::EXIT_NOT_LOADED;
    }
}
