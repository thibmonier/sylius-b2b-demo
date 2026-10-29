<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-219: the reorder alerts block appears on Claire's account dashboard when a prediction is in the 7-day window.
 */
final class PredictiveRhythmsBlockTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    private const CLAIRE = 'claire@acme.example';

    private const DASHBOARD = '/fr_FR/account/dashboard';

    /**
     * US-219: when Claire has a reorder alert in the 7-day window, the block appears on her account dashboard with
     * the product name and explanation.
     */
    public function testReorderAlertsBlockAppearsWhenPredictionIsInWindow(): void
    {
        // Arrange: create client first (before any kernel boot)
        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->isolateSharedAccounts($client);

        // Get EntityManager via client container
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        \assert($em instanceof EntityManagerInterface);
        $connection = $em->getConnection();

        // Get IDs for Acme and EPI-GANT-001
        $companyId = $connection->fetchOne('SELECT id FROM tmonier_company WHERE name = ?', ['Acme Industries']);
        self::assertIsInt($companyId, 'Acme Industries must exist in fixtures.');
        $variantId = $connection->fetchOne('SELECT id FROM sylius_product_variant WHERE code = ?', ['EPI-GANT-001']);
        self::assertIsInt($variantId, 'EPI-GANT-001 must exist in fixtures.');

        // Update the existing prediction: median 14 days, last order 12 days ago → next in 2 days (within 7-day window)
        $now = new \DateTimeImmutable();
        $lastOrderDate = $now->modify('-12 days');
        $medianIntervalDays = 14.0;
        $nextOrderExpectedDate = $lastOrderDate->modify(sprintf('+%d days', (int) $medianIntervalDays));
        $daysUntilNextOrder = (int) $now->diff($nextOrderExpectedDate)->format('%R%a');

        // Verify the calculation puts the next order in the 7-day alert window (0 to 7 days from now)
        self::assertGreaterThanOrEqual(0, $daysUntilNextOrder, 'The next order must be in the future or today.');
        self::assertLessThanOrEqual(7, $daysUntilNextOrder, 'The next order must be within the 7-day alert window.');

        $rowsAffected = $connection->executeStatement(
            'UPDATE tmonier_predictive_stored_prediction
             SET median_interval_days = ?, last_order_date = ?, reason = NULL
             WHERE company_id = ? AND variant_id = ?',
            [$medianIntervalDays, $lastOrderDate->format('Y-m-d H:i:s'), $companyId, $variantId],
        );

        self::assertSame(1, $rowsAffected, 'The UPDATE must affect exactly 1 row (the prediction for Acme + EPI-GANT-001).');

        // Act: login as Claire and load her account dashboard
        $this->login($client, self::CLAIRE);
        $page = $client->request('GET', self::DASHBOARD);
        self::assertResponseIsSuccessful();

        // Assert: the reorder alerts block appears
        $block = $page->filter('[data-test="reorder-alerts"]');
        self::assertCount(1, $block, 'The reorder alerts block must appear when a prediction is in the 7-day window.');

        // Assert: at least one alert item is present
        $items = $page->filter('[data-test="reorder-alert-item"]');
        self::assertGreaterThanOrEqual(1, $items->count(), 'At least one reorder alert item must be present.');

        // Assert: the product name appears
        $productName = $this->text($items->eq(0)->filter('.reorder-alert__product'));
        self::assertStringContainsString('Gants', $productName, 'The product name must appear in the alert (EPI-GANT-001).');
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');
    }

    private function text(Crawler $node): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $node->text()));
    }
}
