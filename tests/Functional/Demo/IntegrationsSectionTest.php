<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

/** US-108: the integrations of the guide page, each with its state and its plugin (or the event contract when none is provided). */
final class IntegrationsSectionTest extends WebTestCase
{
    /** @return iterable<string, array{string, array<string, string>}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR', ['available' => 'Disponible', 'upcoming' => 'À venir', 'not_provided' => 'Non fourni']];
        yield 'en' => ['en_US', ['available' => 'Available', 'upcoming' => 'Upcoming', 'not_provided' => 'Not provided']];
    }

    /** @param array<string, string> $labels */
    #[DataProvider('locales')]
    public function testEveryIntegrationCarriesAStateAndLinksToItsPlugin(string $locale, array $labels): void
    {
        $items = $this->page($locale)->filter('[data-integration]');

        self::assertGreaterThanOrEqual(8, $items->count());
        foreach ($items as $node) {
            $item = new Crawler($node);
            $state = (string) $item->attr('data-integration-state');
            self::assertContains($state, ['available', 'in_progress', 'upcoming', 'not_provided']);
            self::assertNotSame('', trim($item->filter('[data-integration-state-label]')->text()));
            self::assertCount(1, $item->filter('a[data-integration-plugin], a[data-integration-contract]'), (string) $item->attr('data-integration'));
        }
        $erp = $items->filter('[data-integration="erp"]');
        self::assertSame('not_provided', $erp->attr('data-integration-state'));
        self::assertSame($labels['not_provided'], trim($erp->filter('[data-integration-state-label]')->text()));
        self::assertStringEndsWith('/sylius-webhook-outbox-plugin#readme', (string) $erp->filter('a[data-integration-contract]')->attr('href'));
        $portals = $items->filter('[data-integration="purchasing_portals"]');
        self::assertSame($labels['available'], trim($portals->filter('[data-integration-state-label]')->text()));
        self::assertStringEndsWith('/sylius-punchout-cxml-plugin', (string) $portals->filter('a[data-integration-plugin]')->attr('href'));
        $fleet = $items->filter('[data-integration="equipment_fleet"]');
        self::assertSame($labels['available'], trim($fleet->filter('[data-integration-state-label]')->text()));
        self::assertStringEndsWith('/sylius-customer-asset-management-plugin', (string) $fleet->filter('a[data-integration-plugin]')->attr('href'));
    }

    #[DataProvider('locales')]
    public function testTheDiagramHasATextAlternativeAndThePageNoContactButton(string $locale): void
    {
        $page = $this->page($locale);

        self::assertNotSame('', trim($page->filter('svg[data-integrate-architecture] desc')->text()));
        self::assertCount(0, $page->filter('[data-integrate-contact]'));
    }

    /** @return iterable<string, array{string}> */
    public static function translationFiles(): iterable
    {
        foreach (['fr', 'en'] as $language) {
            yield $language => [$language];
        }
    }

    /** Charter (shared with US-113): no commercial wording anywhere in the texts of the page. */
    #[DataProvider('translationFiles')]
    public function testNoCommercialWordingInTheTextsOfThePage(string $language): void
    {
        $texts = Yaml::parseFile(\dirname(__DIR__, 3) . '/translations/integrate.' . $language . '.yaml');
        self::assertIsArray($texts);
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->commercialWording(implode("\n", self::strings($texts))));
    }

    private function page(string $locale): Crawler
    {
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', '/' . $locale . '/integrer');
    }

    /**
     * @param array<mixed> $tree
     *
     * @return list<string>
     */
    private static function strings(array $tree): array
    {
        $found = [];
        array_walk_recursive($tree, static function (mixed $value) use (&$found): void {
            if (\is_string($value)) {
                $found[] = $value;
            }
        });

        return $found;
    }
}
