<?php

declare(strict_types=1);

namespace App\Demo\Command;

use App\Demo\Images\ImageManifest;
use App\Demo\Images\ImageManifestAuditor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** US-092: checks the image manifest (free licence, reviewed against its product, neutral visual when no photo fits). */
#[AsCommand(name: 'app:demo:audit-product-images', description: 'Refuses any demo product image that is not CC0 or not reviewed (US-092)')]
final class AuditProductImagesCommand extends Command
{
    public function __construct(
        private readonly ImageManifestAuditor $auditor,
        #[Autowire('%kernel.project_dir%/fixtures/images/manifest.csv')]
        private readonly string $manifestPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $violations = $this->auditor->violations(ImageManifest::load($this->manifestPath)->rows());
        foreach ($violations as $violation) {
            $output->writeln('<error>' . $violation . '</error>');
        }

        return [] === $violations ? Command::SUCCESS : Command::FAILURE;
    }
}
