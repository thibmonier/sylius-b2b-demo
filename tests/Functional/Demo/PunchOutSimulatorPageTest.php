<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

/** US-099: the simulator as a fictional purchasing portal: numbered steps, plain-language help, flow diagram, readable errors, FR and EN. */
final class PunchOutSimulatorPageTest extends WebTestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function pages(): iterable
    {
        foreach (['fr', 'en'] as $lang) {
            yield 'cxml ' . $lang => ['/demo/punchout-simulator?lang=' . $lang, $lang];
            yield 'oci ' . $lang => ['/demo/punchout-simulator/oci?lang=' . $lang, $lang];
        }
    }

    #[DataProvider('pages')]
    public function testTheSessionIsShownInFourNumberedSteps(string $path, string $lang): void
    {
        $steps = $this->page($path)->filter('ol[data-simulator-steps] > li');

        self::assertCount(4, $steps);
        self::assertSame('step', $steps->first()->attr('aria-current'));
        self::assertCount(1, $steps->filter('[aria-current="step"]'));
        foreach ($steps as $step) {
            self::assertNotSame('', trim((new Crawler($step))->text()));
        }
    }

    #[DataProvider('pages')]
    public function testEachTechnicalTermHasAKeyboardReachableDefinition(string $path, string $lang): void
    {
        $page = $this->page($path);

        $terms = $page->filter('[data-simulator-term]');
        self::assertGreaterThanOrEqual(4, $terms->count());
        foreach ($terms as $node) {
            $term = new Crawler($node);
            self::assertSame('0', $term->attr('tabindex'), (string) $term->attr('data-simulator-term'));
            $definition = $page->filter('#' . $term->attr('aria-describedby'));
            self::assertCount(1, $definition);
            self::assertSame('tooltip', $definition->attr('role'));
            self::assertGreaterThan(20, mb_strlen(trim($definition->text())));
        }
        $style = $page->filter('style')->text();
        self::assertStringContainsString(':focus', $style);
        self::assertStringContainsString(':hover', $style);
    }

    #[DataProvider('pages')]
    public function testTheFlowDiagramHasATextAlternativeAndNoRealBrandIsUsed(string $path, string $lang): void
    {
        $page = $this->page($path);

        $svg = $page->filter('svg[data-simulator-flow][role="img"]');
        self::assertCount(1, $svg);
        self::assertNotSame('', trim($svg->filter('title')->text()));
        self::assertNotSame('', trim($svg->filter('desc')->text()));
        self::assertDoesNotMatchRegularExpression('/\b(ariba|coupa|sap|jaggaer|oracle)\b/i', $page->filter('html')->html());
        self::assertCount(1, $page->filter('[data-simulator-portal]'), 'the fictional portal skin');
    }

    public function testEnglishPagesHaveNoFrenchLabel(): void
    {
        foreach (['/demo/punchout-simulator?lang=en', '/demo/punchout-simulator/oci?lang=en'] as $path) {
            $text = $this->page($path)->filter('main')->text();
            foreach (['Étape', 'Lancer', 'Simuler', 'Acheteur', 'panier reçu'] as $french) {
                self::assertStringNotContainsString($french, $text, $path);
            }
        }
    }

    public function testBothLanguagesDefineTheSameKeys(): void
    {
        $fr = Yaml::parseFile(\dirname(__DIR__, 3) . '/translations/simulator.fr.yaml');
        $en = Yaml::parseFile(\dirname(__DIR__, 3) . '/translations/simulator.en.yaml');
        self::assertIsArray($fr);
        self::assertIsArray($en);

        self::assertSame($this->keys($fr), $this->keys($en));
    }

    public function testAnExpiredSessionOffersToRestartIt(): void
    {
        $client = self::createClient();

        $crawler = $client->request('POST', '/demo/punchout-simulator/oci/return?state=forged.value&lang=en');

        self::assertResponseStatusCodeSame(410);
        $restart = $crawler->filter('[role="alert"] a[data-simulator-restart]');
        self::assertCount(1, $restart);
        self::assertSame('/demo/punchout-simulator/oci?lang=en', $restart->attr('href'));
    }

    public function testAReturnUrlOutsideTheAllowedDomainsIsRefusedWithTheAllowedDomains(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/demo/punchout-simulator/oci');
        $form = $crawler->filter('form[action*="/oci/start"]')->form(['HOOK_URL' => 'https://not-allowed.example/return']);

        $crawler = $client->submit($form);

        self::assertResponseStatusCodeSame(403);
        $alert = $crawler->filter('[role="alert"]')->text();
        self::assertStringContainsString('domaines', $alert);
        self::assertStringContainsString('erp.acme.example', $alert);
    }

    private function page(string $path): Crawler
    {
        self::ensureKernelShutdown();

        return self::createClient()->request('GET', $path);
    }

    /**
     * @param array<mixed> $tree
     *
     * @return list<string>
     */
    private function keys(array $tree, string $prefix = ''): array
    {
        $keys = [];
        foreach ($tree as $key => $value) {
            $path = $prefix . $key;
            $keys = [...$keys, ...(\is_array($value) ? $this->keys($value, $path . '.') : [$path])];
        }
        sort($keys);

        return $keys;
    }
}
