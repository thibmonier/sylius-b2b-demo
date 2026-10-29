<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusPredictiveAnalyticsPlugin\Entity\StoredPrediction;

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
        // Arrange: update the existing prediction for Claire (Acme + EPI-GANT-001) to place it in the 7-day window
        $em = self::getContainer()->get(EntityManagerInterface::class);
        \assert($em instanceof EntityManagerInterface);

        $company = $em->getRepository(CompanyInterface::class)->findOneBy(['name' => 'Acme Industries']);
        \assert($company instanceof CompanyInterface);

        // Take any variant ordered by Acme (EPI-GANT-001 is in the fixtures)
        $variant = $em->getRepository(ProductVariantInterface::class)->findOneBy(['code' => 'EPI-GANT-001']);
        \assert($variant instanceof ProductVariantInterface);

        // Find the existing prediction created by the demo reset recalculation
        $prediction = $em->getRepository(StoredPrediction::class)->findOneBy([
            'company' => $company,
            'variant' => $variant,
        ]);
        \assert($prediction instanceof StoredPrediction);

        // Update it: median 14 days, last order 12 days ago → next in 2 days (within 7-day window)
        $reflection = new \ReflectionClass($prediction);
        $medianProperty = $reflection->getProperty('medianIntervalDays');
        $medianProperty->setValue($prediction, 14.0);
        $lastOrderProperty = $reflection->getProperty('lastOrderDate');
        $lastOrderProperty->setValue($prediction, (new \DateTimeImmutable())->modify('-12 days'));

        $em->flush();

        // Act: load Claire's account dashboard
        $page = $this->page(self::CLAIRE);

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

    private function page(string $email): Crawler
    {
        $client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');

        return $client->request('GET', self::DASHBOARD);
    }

    private function text(Crawler $node): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $node->text()));
    }
}
