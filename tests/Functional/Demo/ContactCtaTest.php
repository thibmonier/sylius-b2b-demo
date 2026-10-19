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

    private const BOOKING = 'https://exemple.test/rdv';

    private const CONTACT = 'https://exemple.test/contact';

    /** @return iterable<string, array{string, string, string, string}> */
    public static function placements(): iterable
    {
        foreach (['fr_FR' => ['Prendre rendez-vous', 'Écrire un message'], 'en_US' => ['Book a meeting', 'Write a message']] as $locale => [$booking, $contact]) {
            yield $locale . ' home' => [$locale, '/' . $locale . '/', 'home', $booking . '|' . $contact];
            yield $locale . ' product' => [$locale, '/' . $locale . '/products/outi-001-' . substr($locale, 0, 2), 'product', $booking . '|' . $contact];
            yield $locale . ' plugins' => [$locale, '/' . $locale . '/plugins', 'plugins-top', $booking . '|' . $contact];
            yield $locale . ' about' => [$locale, '/' . $locale . '/a-propos', 'about', $booking . '|' . $contact];
            yield $locale . ' mcp' => [$locale, '/' . $locale . '/mcp', 'mcp', $booking . '|' . $contact];
        }
    }

    #[DataProvider('placements')]
    public function testBothTargetsAreShownWithTheUtmOfTheirPlacement(string $locale, string $path, string $placement, string $labels): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => self::BOOKING, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $cta = $this->page($path)->filter('[data-cta="' . $placement . '"]');

        self::assertCount(1, $cta, $path);
        [$booking, $contact] = explode('|', $labels);
        self::assertSame(self::BOOKING . '?utm_source=demo-b2b&utm_medium=cta&utm_content=' . $placement, $cta->filter('a[data-cta-booking]')->attr('href'));
        self::assertSame(self::CONTACT . '?utm_source=demo-b2b&utm_medium=cta&utm_content=' . $placement, $cta->filter('a[data-cta-contact]')->attr('href'));
        self::assertStringContainsString($booking, $cta->filter('a[data-cta-booking]')->text());
        self::assertStringContainsString($contact, $cta->filter('a[data-cta-contact]')->text());
        self::assertSame('noopener noreferrer', $cta->filter('a[data-cta-booking]')->attr('rel'));
        self::assertSame('_blank', $cta->filter('a[data-cta-booking]')->attr('target'));
    }

    /** The banner stands out from the page: primary background, white text, white booking button turning primary on hover. */
    public function testTheBannerIsInvertedAndTheBookingButtonIsWhite(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => self::BOOKING, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $cta = $this->page('/fr_FR/')->filter('[data-cta="home"]');

        self::assertCount(1, $cta->filter('.text-bg-primary'));
        $booking = (string) $cta->filter('a[data-cta-booking]')->attr('style');
        self::assertStringContainsString('--bs-btn-bg: var(--bs-white)', $booking);
        self::assertStringContainsString('--bs-btn-hover-bg: var(--bs-primary)', $booking);
        self::assertStringContainsString('btn-outline-light', (string) $cta->filter('a[data-cta-contact]')->attr('class'));
    }

    public function testOnlyTheBookingTargetWhenTheContactUrlIsEmpty(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => self::BOOKING, 'DEMO_CONTACT_URL' => '']);

        $cta = $this->page('/fr_FR/')->filter('[data-cta="home"]');

        self::assertCount(1, $cta->filter('a[data-cta-booking]'));
        self::assertCount(0, $cta->filter('a[data-cta-contact]'));
    }

    /** @return iterable<string, array{string, string}> */
    public static function unusable(): iterable
    {
        yield 'empty' => ['', ''];
        yield 'plain http' => ['http://exemple.test/rdv', 'http://exemple.test/contact'];
        yield 'credentials' => ['https://user:secret@exemple.test/rdv', 'javascript:alert(1)'];
    }

    #[DataProvider('unusable')]
    public function testWithoutUsableUrlTheComponentIsHiddenEverywhere(string $booking, string $contact): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => $booking, 'DEMO_CONTACT_URL' => $contact]);

        foreach (['/fr_FR/', '/fr_FR/plugins', '/fr_FR/a-propos', '/fr_FR/mcp'] as $path) {
            $page = $this->page($path);
            self::assertCount(0, $page->filter('[data-cta]'), $path);
            self::assertCount(0, $page->filter('[data-cta-booking], [data-cta-contact]'), $path);
        }
    }

    public function testEveryPluginCardCarriesALinkOfItsOwn(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => self::BOOKING, 'DEMO_CONTACT_URL' => self::CONTACT]);

        $page = $this->page('/fr_FR/plugins');

        $cards = $page->filter('article[data-plugin]');
        self::assertGreaterThan(0, $cards->count());
        self::assertCount($cards->count(), $page->filter('article[data-plugin] [data-cta^="plugin-"]'));
        self::assertCount(2, $page->filter('[data-cta="plugins-top"], [data-cta="plugins-bottom"]'));
    }

    public function testThePunchOutSimulatorEndsWithTheLinks(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => self::BOOKING, 'DEMO_CONTACT_URL' => self::CONTACT]);

        foreach (['fr' => 'Prendre rendez-vous', 'en' => 'Book a meeting'] as $lang => $label) {
            $cta = $this->page('/demo/punchout-simulator?lang=' . $lang)->filter('[data-cta="punchout-simulator"]');

            self::assertCount(1, $cta, $lang);
            self::assertStringContainsString($label, $cta->filter('a[data-cta-booking]')->text());
            self::assertStringContainsString('utm_content=punchout-simulator', (string) $cta->filter('a[data-cta-contact]')->attr('href'));
        }
    }

    public function testTheDemoBarLinkIsTaggedToo(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => '', 'DEMO_CONTACT_URL' => self::CONTACT]);

        $link = $this->page('/fr_FR/')->filter('[data-demo-banner] a[data-contact-link]');

        self::assertSame(self::CONTACT . '?utm_source=demo-b2b&utm_medium=cta&utm_content=demo-bar', $link->attr('href'));
    }

    public function testTheComponentNeverBuildsAnIntrusiveWidget(): void
    {
        $this->setEnvironment(['DEMO_BOOKING_URL' => self::BOOKING, 'DEMO_CONTACT_URL' => self::CONTACT]);

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
