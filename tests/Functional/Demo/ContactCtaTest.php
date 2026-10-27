<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/** US-142: the "contact me" call to action, in both languages, from the configured https URLs only (placeholder values here). */
final class ContactCtaTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const PROFILE = 'https://exemple.test/profil';

    private const CONTACT = 'https://exemple.test/contact';

    /** @return iterable<string, array{string, string, string, string}> */
    public static function placements(): iterable
    {
        foreach (['fr_FR' => ['Me contacter sur LinkedIn', 'Écrire un message'], 'en_US' => ['Contact me on LinkedIn', 'Write a message']] as $locale => [$profile, $contact]) {
            yield $locale . ' home' => [$locale, '/' . $locale . '/', 'home', $profile . '|' . $contact];
            yield $locale . ' product' => [$locale, '/' . $locale . '/products/outi-001-' . substr($locale, 0, 2), 'product', $profile . '|' . $contact];
            yield $locale . ' plugins' => [$locale, '/' . $locale . '/plugins', 'plugins-top', $profile . '|' . $contact];
            yield $locale . ' about' => [$locale, '/' . $locale . '/a-propos', 'about', $profile . '|' . $contact];
            yield $locale . ' mcp' => [$locale, '/' . $locale . '/mcp', 'mcp', $profile . '|' . $contact];
        }
    }

    #[DataProvider('placements')]
    public function testBothTargetsAreShownWithTheUtmOfTheirPlacement(string $locale, string $path, string $placement, string $labels): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $cta = $this->page($path)->filter('[data-cta="' . $placement . '"]');

        self::assertCount(1, $cta, $path);
        [$profile, $contact] = explode('|', $labels);
        self::assertSame(self::PROFILE . '?utm_source=demo-b2b&utm_medium=cta&utm_content=' . $placement, $cta->filter('a[data-cta-profile]')->attr('href'));
        self::assertSame(self::CONTACT . '?utm_source=demo-b2b&utm_medium=cta&utm_content=' . $placement, $cta->filter('a[data-cta-contact]')->attr('href'));
        self::assertStringContainsString($profile, $cta->filter('a[data-cta-profile]')->text());
        self::assertStringContainsString($contact, $cta->filter('a[data-cta-contact]')->text());
        self::assertSame('noopener noreferrer', $cta->filter('a[data-cta-profile]')->attr('rel'));
        self::assertSame('_blank', $cta->filter('a[data-cta-profile]')->attr('target'));
    }

    /** US-186: on the home, the call to action closes the dark "behind the scenes" section: gold profile button, outlined contact button. */
    public function testTheHomeCallToActionIsDarkAndInsideTheBackstageSection(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $cta = $this->page('/fr_FR/')->filter('[data-home-section="backstage"] [data-cta="home"]');

        self::assertCount(1, $cta);
        self::assertStringContainsString('b2b-btn-gold', (string) $cta->filter('a[data-cta-profile]')->attr('class'));
        self::assertStringContainsString('b2b-btn-ghost', (string) $cta->filter('a[data-cta-contact]')->attr('class'));
    }

    /**
     * The author buttons of the home are one group (profile, repository, proof) laid out on one line or stacked, never two
     * plus one: the call to action wrapper takes no Bootstrap display utility, which would win over its `display: contents`.
     */
    public function testTheHomeAuthorButtonsFormOneGroup(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => '']);

        $group = $this->page('/fr_FR/')->filter('[data-home-author] .b2b-home-author__buttons');

        self::assertCount(1, $group);
        self::assertCount(3, $group->filter('a'));
        self::assertSame('b2b-home-author__cta', $group->filter('[data-cta="home"]')->attr('class'));
    }

    public function testOnlyTheBookingTargetWhenTheContactUrlIsEmpty(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => '']);

        $cta = $this->page('/fr_FR/')->filter('[data-cta="home"]');

        self::assertCount(1, $cta->filter('a[data-cta-profile]'));
        self::assertCount(0, $cta->filter('a[data-cta-contact]'));
    }

    /** @return iterable<string, array{string, string}> */
    public static function unusable(): iterable
    {
        yield 'empty' => ['', ''];
        yield 'plain http' => ['http://exemple.test/profil', 'http://exemple.test/contact'];
        yield 'credentials' => ['https://user:secret@exemple.test/profil', 'javascript:alert(1)'];
    }

    #[DataProvider('unusable')]
    public function testWithoutUsableUrlTheComponentIsHiddenEverywhere(string $profile, string $contact): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => $profile, 'DEMO_CONTACT_URL' => $contact]);

        foreach (['/fr_FR/', '/fr_FR/plugins', '/fr_FR/a-propos', '/fr_FR/mcp'] as $path) {
            $page = $this->page($path);
            self::assertCount(0, $page->filter('[data-cta]'), $path);
            self::assertCount(0, $page->filter('[data-cta-profile], [data-cta-contact]'), $path);
        }
    }

    public function testEveryPluginCardCarriesALinkOfItsOwn(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $page = $this->page('/fr_FR/plugins');

        $cards = $page->filter('article[data-plugin]');
        self::assertGreaterThan(0, $cards->count());
        self::assertCount($cards->count(), $page->filter('article[data-plugin] [data-cta^="plugin-"]'));
        self::assertCount(2, $page->filter('[data-cta="plugins-top"], [data-cta="plugins-bottom"]'));
    }

    public function testThePunchOutSimulatorEndsWithTheLinks(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => self::CONTACT]);

        foreach (['fr' => 'Me contacter sur LinkedIn', 'en' => 'Contact me on LinkedIn'] as $lang => $label) {
            $cta = $this->page('/demo/punchout-simulator?lang=' . $lang)->filter('[data-cta="punchout-simulator"]');

            self::assertCount(1, $cta, $lang);
            self::assertStringContainsString($label, $cta->filter('a[data-cta-profile]')->text());
            self::assertStringContainsString('utm_content=punchout-simulator', (string) $cta->filter('a[data-cta-contact]')->attr('href'));
        }
    }

    public function testTheDemoBarLinkIsTaggedToo(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => '', 'DEMO_CONTACT_URL' => self::CONTACT]);

        $link = $this->page('/fr_FR/')->filter('[data-demo-banner] a[data-contact-link]');

        self::assertSame(self::CONTACT . '?utm_source=demo-b2b&utm_medium=cta&utm_content=demo-bar', $link->attr('href'));
    }

    /**
     * The bar contact link shows from md only: a bare `d-inline-flex` next to `d-none` wins over it in the utilities
     * order and the link then overflows the 375 and 320 px bar (measured with bin/measure-mobile).
     */
    public function testTheDemoBarLinkIsHiddenBelowTheMdWidth(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => '', 'DEMO_CONTACT_URL' => self::CONTACT]);

        $classes = preg_split('/\s+/', trim((string) $this->page('/fr_FR/')->filter('[data-demo-banner] a[data-contact-link]')->attr('class')));

        self::assertIsArray($classes);
        self::assertContains('d-none', $classes);
        self::assertContains('d-md-inline-flex', $classes);
        self::assertNotContains('d-inline-flex', $classes);
    }

    public function testTheComponentNeverBuildsAnIntrusiveWidget(): void
    {
        $this->setEnvironment(['DEMO_PROFILE_URL' => self::PROFILE, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $page = $this->page('/fr_FR/');

        self::assertCount(0, $page->filter('iframe, [data-cta] script, [class*="fixed-bottom"], [class*="sticky"]'));
        self::assertStringNotContainsString('calendly.com/assets', $page->html());
    }

    private function page(string $path): Crawler
    {
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', $path);
    }
}
