<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-074: the footer of the B2B channel talks about the demo: no newsletter, no partner banner, no dead link. */
final class DemoFooterTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheFooterHasNoNewsletterNoPartnerNorDeadLink(string $locale): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/');

        $footer = $page->filter('footer');
        self::assertCount(1, $footer);
        self::assertCount(0, $footer->filter('a[href="#"]'), 'no dead link');
        self::assertCount(0, $footer->filter('form, input[type="email"]'), 'no newsletter');
        self::assertCount(0, $footer->filter('a[href*="sylius.com/partner"], a[href*="instagram"], a[href*="facebook"], a[href*="x.com"]'));
        self::assertCount(0, $footer->filter('img'), 'no partner logo');
        self::assertStringNotContainsStringIgnoringCase('newsletter', $footer->text());
        self::assertStringNotContainsString('Mollie', $footer->text());
    }

    #[DataProvider('locales')]
    public function testTheDemoColumnLinksTheDemoPages(string $locale): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/');

        $column = $page->filter('footer [data-demo-footer]');
        self::assertCount(1, $column);
        foreach (['/' . $locale . '/a-propos', '/' . $locale . '/plugins', '/demo/accounts'] as $href) {
            self::assertCount(1, $column->filter('a[href="' . $href . '"]'), $href);
        }
    }

    public function testWithoutConfiguredUrlsTheAuthorStaysPlainTextAndNoContactLink(): void
    {
        $this->setEnvironment(['DEMO_SITE_URL' => '', 'DEMO_CONTACT_URL' => '']);

        $column = self::createClient()->request('GET', '/fr_FR/')->filter('footer [data-demo-footer]');

        self::assertStringContainsString('Thibaut Monier', $column->text());
        self::assertCount(0, $column->filter('a[data-site-link], a[data-contact-link]'));
    }

    public function testConfiguredHttpsUrlsAreClickableAndSafe(): void
    {
        $this->setEnvironment(['DEMO_SITE_URL' => 'https://site.example/', 'DEMO_CONTACT_URL' => 'https://site.example/contact']);

        $column = self::createClient()->request('GET', '/en_US/')->filter('footer [data-demo-footer]');

        $site = $column->filter('a[data-site-link]');
        self::assertCount(1, $site);
        self::assertSame('https://site.example/', $site->attr('href'));
        self::assertStringContainsString('Thibaut Monier', $site->text());
        self::assertSame('noopener noreferrer', $site->attr('rel'));
        self::assertSame('https://site.example/contact', $column->filter('a[data-contact-link]')->attr('href'));
    }

    public function testAnHttpUrlIsRefusedAndTheLinkHidden(): void
    {
        $this->setEnvironment(['DEMO_SITE_URL' => 'http://site.example/', 'DEMO_CONTACT_URL' => 'javascript:alert(1)']);

        $page = self::createClient()->request('GET', '/fr_FR/');

        self::assertCount(0, $page->filter('footer a[data-site-link], footer a[data-contact-link]'));
        self::assertStringNotContainsString('javascript:', (string) $page->html());
        self::assertStringNotContainsString('http://site.example', (string) $page->html());
    }
}
