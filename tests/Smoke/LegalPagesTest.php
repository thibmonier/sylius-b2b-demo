<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LegalPagesTest extends WebTestCase
{
    /** @return iterable<string, array{string, string, string}> */
    public static function pages(): iterable
    {
        yield 'legal notice fr' => ['/fr_FR/mentions-legales', 'Mentions légales', 'fictives'];
        yield 'legal notice en' => ['/en_US/mentions-legales', 'Legal notice', 'ictitious'];
        yield 'about fr' => ['/fr_FR/a-propos', 'À propos', 'fictives'];
        yield 'about en' => ['/en_US/a-propos', 'About', 'ictitious'];
    }

    #[DataProvider('pages')]
    public function testPagesAreAnonymousAndLocalised(string $path, string $title, string $fictional): void
    {
        $client = self::createClient();
        foreach (['B2B', 'B2C'] as $channel) {
            $client->request('GET', '/demo/channel/' . $channel);
            $client->request('GET', $path);

            self::assertResponseStatusCodeSame(200);
            $content = (string) $client->getResponse()->getContent();
            self::assertStringContainsString($title, $content);
            self::assertStringContainsString($fictional, $content);
        }
    }

    public function testAboutListsTheShowcasedPlugins(): void
    {
        $client = self::createClient();
        $client->request('GET', '/en_US/a-propos');
        $content = (string) $client->getResponse()->getContent();

        /** @var array{require: array<string, string>} $composer */
        $composer = json_decode((string) file_get_contents(__DIR__ . '/../../composer.json'), true, 512, \JSON_THROW_ON_ERROR);
        $plugins = array_filter(array_keys($composer['require']), static fn (string $p): bool => str_starts_with($p, 'tmonier/'));

        self::assertCount(23, $plugins);
        foreach ($plugins as $package) {
            self::assertStringContainsString('https://github.com/thibmonier/' . substr($package, 8), $content);
        }
    }

    public function testMissingPublisherAndHostShowAPlaceholder(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/mentions-legales');

        $placeholders = $client->getCrawler()->filterXpath('//*[@data-placeholder]');
        if ('' === self::legalParameter('demo.legal.publisher')) {
            self::assertGreaterThanOrEqual(1, $placeholders->count());
            self::assertStringContainsString('À COMPLÉTER', $placeholders->text());
        }
    }

    /** CI release gate: run with DEMO_LEGAL_REQUIRED=1 to fail while the legal details are unset. */
    public function testLegalDetailsAreConfiguredWhenRequired(): void
    {
        if ('1' !== getenv('DEMO_LEGAL_REQUIRED')) {
            self::markTestSkipped('Set DEMO_LEGAL_REQUIRED=1 to enforce the publisher and host.');
        }

        self::bootKernel();
        self::assertNotSame('', self::legalParameter('demo.legal.publisher'));
        self::assertNotSame('', self::legalParameter('demo.legal.host'));
    }

    public function testAnUnknownLegalUrlIsA404(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/mentions-inconnues');

        self::assertResponseStatusCodeSame(404);
    }

    public function testFooterLinksToBothPages(): void
    {
        $client = self::createClient();
        $client->request('GET', '/fr_FR/');
        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('/fr_FR/mentions-legales', $content);
        self::assertStringContainsString('/fr_FR/a-propos', $content);
    }

    private static function legalParameter(string $name): string
    {
        $value = self::getContainer()->getParameter($name);
        self::assertIsString($value);

        return trim($value);
    }
}
