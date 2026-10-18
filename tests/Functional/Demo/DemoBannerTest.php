<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

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
        yield 'French' => ['fr_FR', self::FRENCH, self::ENGLISH, 'Un projet B2B ? Parlons-en'];
        yield 'English' => ['en_US', self::ENGLISH, self::FRENCH, "A B2B project? Let's talk"];
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
        yield 'French' => ['fr_FR', 'Un projet B2B ? Parlons-en'];
        yield 'English' => ['en_US', "A B2B project? Let's talk"];
    }

    #[DataProvider('contacts')]
    public function testAnHttpsContactUrlShowsTheLink(string $locale, string $contact): void
    {
        $this->setEnvironment(['DEMO_CONTACT_URL' => 'https://example.com/contact']);

        $link = $this->bar($locale)->filter('a[data-contact-link]');

        self::assertCount(1, $link);
        self::assertSame('https://example.com/contact', $link->attr('href'));
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
        self::assertStringNotContainsString('Parlons-en', $bar->text());
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
}
