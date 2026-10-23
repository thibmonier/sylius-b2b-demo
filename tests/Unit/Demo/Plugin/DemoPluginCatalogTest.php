<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Plugin;

use App\Demo\Account\DemoAccountCatalog;
use App\Demo\Plugin\DemoPluginCatalog;
use App\Tests\Support\DemoCatalogs;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/** US-014 — the plugin registry matches composer.json and rejects invalid entries by plugin name. */
final class DemoPluginCatalogTest extends TestCase
{
    public function testTheCatalogHoldsTheComposerPluginsAndTheAnnouncedOnes(): void
    {
        $composer = json_decode((string) file_get_contents(\dirname(__DIR__, 4) . '/composer.json'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($composer);
        self::assertIsArray($composer['require']);
        $expected = [];
        foreach (array_keys($composer['require']) as $package) {
            if (str_starts_with((string) $package, 'tmonier/')) {
                $expected[] = substr((string) $package, 8);
            }
        }
        sort($expected);

        $installed = array_filter(self::catalog()->all(), static fn ($plugin): bool => $plugin->installed);
        $slugs = array_map(static fn ($plugin): string => $plugin->slug, $installed);
        sort($slugs);

        self::assertSame($expected, $slugs);
        self::assertCount(25, self::catalog()->all());
        self::assertSame(25, self::catalog()->count());
        self::assertSame([], array_values(array_filter(self::catalog()->all(), static fn ($plugin): bool => !$plugin->installed)));
    }

    public function testEveryPluginHasBothDescriptionsAndAKnownCategory(): void
    {
        foreach (self::catalog()->all() as $plugin) {
            self::assertNotSame('', $plugin->descriptionFr, $plugin->slug);
            self::assertNotSame('', $plugin->descriptionEn, $plugin->slug);
            self::assertNotSame($plugin->descriptionFr, $plugin->descriptionEn, $plugin->slug);
            self::assertContains($plugin->category, ['purchasing', 'billing', 'catalog', 'integration', 'compliance', 'admin'], $plugin->slug);
            self::assertSame('https://github.com/thibmonier/' . $plugin->slug, $plugin->githubUrl());
        }
    }

    public function testDescriptionFollowsTheLocale(): void
    {
        $plugin = self::catalog()->get('sylius-quick-order-plugin');

        self::assertNotNull($plugin);
        self::assertSame($plugin->descriptionFr, $plugin->description('fr_FR'));
        self::assertSame($plugin->descriptionEn, $plugin->description('en_US'));
        self::assertSame($plugin->descriptionEn, $plugin->description('de_DE'));
    }

    public function testAnUnknownSlugIsNull(): void
    {
        self::assertNull(self::catalog()->get('nope'));
    }

    public function testPluginsAreGroupedByCategory(): void
    {
        $groups = self::catalog()->byCategory();

        self::assertSame(25, array_sum(array_map('count', $groups)));
        self::assertArrayHasKey('purchasing', $groups);
    }

    public function testAnEntryWithoutDescriptionNamesTheFaultyPlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sylius-broken-plugin');

        new DemoPluginCatalog([['slug' => 'sylius-broken-plugin', 'name' => 'Broken', 'category' => 'admin', 'description' => ['fr' => 'Seulement FR']]], self::accounts());
    }

    public function testAnEntryWithoutNameNamesTheFaultyPlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sylius-nameless-plugin');

        new DemoPluginCatalog([['slug' => 'sylius-nameless-plugin', 'category' => 'admin', 'description' => ['fr' => 'a', 'en' => 'b']]], self::accounts());
    }

    public function testEveryAdvisedAccountExistsInTheAccountCatalog(): void
    {
        $accounts = self::accounts();
        $emails = array_map(static fn ($account): string => $account->email, [...$accounts->shopAccounts(), ...$accounts->adminUsers()]);

        foreach (self::catalog()->all() as $plugin) {
            self::assertContains($plugin->advisedAccount, $emails, $plugin->slug);
            self::assertNotSame($plugin->scenarioFr, $plugin->scenarioEn, $plugin->slug);
        }
    }

    public function testAnUnknownAdvisedAccountNamesThePlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sylius-ghost-plugin');

        new DemoPluginCatalog([self::entry('sylius-ghost-plugin', ['advised_account' => 'ghost@nowhere.example'])], self::accounts());
    }

    public function testAnEntryWithoutScenarioNamesTheFaultyPlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sylius-silent-plugin');

        $entry = self::entry('sylius-silent-plugin');
        unset($entry['scenario']);
        new DemoPluginCatalog([$entry], self::accounts());
    }

    public function testAnUnpublishedPluginHasNoPackagistLink(): void
    {
        $catalog = self::catalog();
        self::assertSame([], array_values(array_filter($catalog->all(), static fn ($plugin): bool => !$plugin->published)));
        self::assertNull(self::catalog()->get('sylius-quick-order-plugin')?->status());

        $draft = new DemoPluginCatalog([self::entry('sylius-draft-plugin', ['published' => false, 'installed' => false])], self::accounts())->get('sylius-draft-plugin');
        self::assertNotNull($draft);
        self::assertNull($draft->packagistUrl());
        self::assertSame('upcoming', $draft->status());
    }

    public function testAnInstalledButUnpublishedPluginIsInProgress(): void
    {
        $plugin = new DemoPluginCatalog([self::entry('sylius-draft-plugin', ['published' => false])], self::accounts())->get('sylius-draft-plugin');

        self::assertNull($plugin?->packagistUrl());
        self::assertSame('in_progress', $plugin?->status());
    }

    public function testPublishedPluginsLinkToPackagist(): void
    {
        $plugin = self::catalog()->get('sylius-quick-order-plugin');

        self::assertNotNull($plugin);
        self::assertSame('https://packagist.org/packages/tmonier/sylius-quick-order-plugin', $plugin->packagistUrl());
        self::assertSame($plugin->scenarioFr, $plugin->scenario('fr_FR'));
        self::assertSame($plugin->scenarioEn, $plugin->scenario('en_US'));
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    private static function entry(string $slug, array $override = []): array
    {
        return $override + [
            'slug' => $slug,
            'name' => 'X',
            'category' => 'admin',
            'description' => ['fr' => 'a', 'en' => 'b'],
            'scenario' => ['fr' => 'c', 'en' => 'd'],
            'advised_account' => 'sophie@fournitures-pro.example',
        ];
    }

    private static function accounts(): DemoAccountCatalog
    {
        return DemoCatalogs::accounts();
    }

    private static function catalog(): DemoPluginCatalog
    {
        return DemoCatalogs::plugins();
    }
}
