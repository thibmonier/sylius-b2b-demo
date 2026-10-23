<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\DataSets;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/** US-076: the demo bar speaks the visitor's language only, and shows the contact link only when its URL is a valid https one. */
final class DemoBannerTest extends WebTestCase
{
    use EnvironmentSwitch;

    private const FRENCH = ['Démo', 'Prochaine remise', 'Se connecter', 'Comptes', 'Canal'];

    private const ENGLISH = ['demo', 'Next reset', 'Log in as', 'accounts', 'Channel'];

    /** @return iterable<string, array{string, list<string>, list<string>, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', self::FRENCH, self::ENGLISH, 'Cette démo vous parle ? Échangeons'];
        yield 'English' => ['en_US', self::ENGLISH, self::FRENCH, 'Does this demo speak to you? Get in touch'];
    }

    /**
     * @param list<string> $expected
     * @param list<string> $forbidden
     */
    #[DataProvider('locales')]
    public function testTheBarUsesOneLanguage(string $locale, array $expected, array $forbidden, string $contact): void
    {
        $bar = $this->bar($locale);
        $text = preg_replace('/\s+/', ' ', $bar->text());

        foreach ($expected as $word) {
            self::assertStringContainsStringIgnoringCase($word, (string) $text);
        }
        foreach ($forbidden as $word) {
            self::assertStringNotContainsStringIgnoringCase($word, (string) $text, 'the other language must not appear');
        }
        self::assertStringNotContainsString(' / ', (string) $text, 'no "French / English" pairs');
    }

    public function testTheBarKeepsItsFunctionsInOneRowOfControls(): void
    {
        $bar = $this->bar('fr_FR');

        self::assertCount(1, $bar->filter('.container.flex-nowrap'), 'one line: no wrapping container');
        self::assertCount(2, $bar->filter('details'), 'quick login and menu fold what does not fit');
        self::assertGreaterThan(0, $bar->filter('details form [name="_csrf_shop_security_token"]')->count());
        self::assertCount(1, $bar->filter('[data-demo-badges-slot] [data-demo-badges-toggle]'));
        self::assertCount(1, $bar->filter('[data-demo-next-reset]'));
    }

    /** US-179: in French the menu rows overflowed a 360 px screen; their labels may now wrap and the panel never grows past its column. */
    #[DataProvider('localesWith1Argument')]
    public function testTheMenuPanelsLetTheirLabelsWrapInsteadOfOverflowing(string $locale): void
    {
        $panels = $this->bar($locale)->filter('[data-demo-menu-panel]');

        self::assertCount(2, $panels, 'quick login and demo menu');
        foreach ($panels as $panel) {
            $panel = new Crawler($panel);
            self::assertStringContainsString('text-break', (string) $panel->attr('class'), 'long labels break');
            self::assertStringContainsString('grid-template-columns: minmax(0, 1fr)', (string) $panel->attr('style'), 'rows may shrink below their content');
            self::assertCount(0, $panel->filter('.text-nowrap'), 'no label that refuses to wrap');
        }
        $channel = $panels->filter('[data-demo-channel]');
        self::assertCount(1, $channel);
        self::assertStringContainsString('flex-wrap', (string) $channel->attr('class'), 'the channel row folds onto two lines');
    }

    public function testTheMenusAreWiredToTheCloserController(): void
    {
        $bar = $this->bar('fr_FR');

        self::assertCount(1, $bar->filter('[data-controller="demo-menus"]'));
        self::assertCount(2, $bar->filter('details[data-demo-menus-target="menu"][data-action="toggle->demo-menus#toggled"]'));
        self::assertStringContainsString('keydown.esc->demo-menus#close', (string) $bar->filter('[data-controller="demo-menus"]')->attr('data-action'));
        self::assertStringContainsString('click@window->demo-menus#outside', (string) $bar->filter('[data-controller="demo-menus"]')->attr('data-action'));
        self::assertFileExists(\dirname(__DIR__, 3) . '/assets/shop/controllers/demo-menus_controller.js');
    }

    /** @return iterable<string, array{string, string}> */
    public static function contacts(): iterable
    {
        yield 'French' => ['fr_FR', 'Cette démo vous parle ? Échangeons'];
        yield 'English' => ['en_US', 'Does this demo speak to you? Get in touch'];
    }

    #[DataProvider('contacts')]
    public function testAnHttpsContactUrlShowsTheLink(string $locale, string $contact): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => 'https://example.com/contact']);

        $link = $this->bar($locale)->filter('a[data-contact-link]');

        self::assertCount(1, $link);
        self::assertSame('https://example.com/contact?utm_source=demo-b2b&utm_medium=cta&utm_content=demo-bar', $link->attr('href'));
        self::assertStringContainsString($contact, $link->text());
        self::assertSame('noopener noreferrer', $link->attr('rel'));
    }

    /** @return iterable<string, array{string}> */
    public static function unusableUrls(): iterable
    {
        yield 'empty' => [''];
        yield 'plain http' => ['http://example.com/contact'];
        yield 'not a URL' => ['javascript:alert(1)'];
    }

    #[DataProvider('unusableUrls')]
    public function testAnEmptyOrNonHttpsContactUrlHidesTheLink(string $url): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => $url]);

        $bar = $this->bar('fr_FR');

        self::assertCount(0, $bar->filter('[data-contact-link]'));
        self::assertStringNotContainsString('Échangeons', $bar->text());
    }

    private function bar(string $locale): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $page = $client->request('GET', '/' . $locale . '/');
        self::assertResponseIsSuccessful();
        $bar = $page->filter('[data-demo-banner]');
        self::assertCount(1, $bar);

        return $bar;
    }

    /** @return iterable<array-key, list<mixed>> */
    public static function localesWith1Argument(): iterable
    {
        return DataSets::firstArguments(self::locales(), 1);
    }
}
