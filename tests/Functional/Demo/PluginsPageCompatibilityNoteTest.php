<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-104 (decision D2, 2026-10-18): the Plugins page ends with a factual sentence, no comparison with the official offering. */
final class PluginsPageCompatibilityNoteTest extends WebTestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', "Ces plugins s'installent sur Sylius 2.3."];
        yield 'English' => ['en_US', 'These plugins install on Sylius 2.3.'];
    }

    #[DataProvider('locales')]
    public function testThePageEndsWithTheFactualSentenceAndNoOfficialOfferingNotice(string $locale, string $sentence): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/' . $locale . '/plugins');

        self::assertResponseIsSuccessful();
        $note = $page->filter('[data-plugins-compat-note]');
        self::assertCount(1, $note);
        self::assertSame($sentence, trim(html_entity_decode($note->text())));
        self::assertCount(0, $page->filter('[data-sylius-plus-notice], [data-sylius-plus-link], a[href*="sylius.com/plus"]'));
        self::assertStringNotContainsStringIgnoringCase('sylius plus', (string) $client->getResponse()->getContent());
    }

    /** Charter check: no published template or translation names the official commercial offering. */
    public function testNoShopTemplateOrTranslationNamesTheOfficialOffering(): void
    {
        $root = \dirname(__DIR__, 3);
        $offenders = [];
        foreach (['templates', 'translations'] as $dir) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/' . $dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && 1 === preg_match('/sylius plus|elesto/i', (string) file_get_contents($file->getPathname()))) {
                    $offenders[] = substr($file->getPathname(), \strlen($root) + 1);
                }
            }
        }

        self::assertSame([], $offenders, 'comparative wording to fix (tone charter, US-063)');
    }
}
