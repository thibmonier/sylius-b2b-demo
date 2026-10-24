<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Erp\ErpDemoExchange;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;

/**
 * US-193 / US-188: the exchange directory of the demo in its known state at each load of the fixtures, so after the nightly reset: the
 * last placed orders of each channel in the outbox (no webhook delivery queued), one export, one pass of the simulated ERP
 * (see {@see ErpDemoExchange}). Loaded after the webhook endpoint (-80). A showcase page must never stop a reset: a failure is logged.
 * The start of a container runs `app:demo:erp-refresh` for the deployments that do not reload the fixtures (US-195).
 */
final class ErpExportDemoFixture extends AbstractFixture
{
    public function __construct(private readonly ErpDemoExchange $exchange, private readonly LoggerInterface $logger)
    {
    }

    public function load(array $options): void
    {
        try {
            $this->exchange->build();
        } catch (\Throwable $e) {
            $this->logger->warning('The demo exchange with the simulated ERP could not be built: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'demo_erp_export';
    }
}
