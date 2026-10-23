<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\Plugin\DemoPluginCatalog;
use App\Demo\Proof\ProofReader;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * "Architecture of the demo" (US-170): how the demo itself is built, layer by layer. Every figure comes from proof.json
 * (tests, PHPStan, CI) or from the running configuration (plugins of the catalogue, Sylius, Symfony and PHP versions).
 */
final readonly class ArchitectureController
{
    public function __construct(
        private Environment $twig,
        private DemoPluginCatalog $plugins,
        private ProofReader $reader,
    ) {
    }

    #[Route('/{_locale}/architecture', name: 'app_demo_architecture', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/architecture.html.twig', [
            'report' => $this->reader->report(),
            'versions' => [
                '%plugins%' => \count($this->plugins->all()),
                '%sylius%' => SyliusCoreBundle::MAJOR_VERSION . '.' . SyliusCoreBundle::MINOR_VERSION,
                '%symfony%' => Kernel::MAJOR_VERSION . '.' . Kernel::MINOR_VERSION,
                '%php%' => \PHP_MAJOR_VERSION . '.' . \PHP_MINOR_VERSION,
            ],
        ]));
    }
}
