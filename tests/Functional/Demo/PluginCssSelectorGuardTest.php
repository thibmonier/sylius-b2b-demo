<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * T-TECH-42-01: guard against CSS selector drift between demo overrides and plugin HTML.
 *
 * The demo SCSS files target BEM classes produced by plugins. If a plugin
 * renames or removes a class, the demo styles become dead code. This test
 * extracts every BEM selector (block__element pattern) from the demo SCSS
 * and asserts that at least one matching element exists in the rendered page.
 */
final class PluginCssSelectorGuardTest extends WebTestCase
{
    /**
     * @dataProvider provideScssAndPage
     */
    public function testEveryBemSelectorInDemoScssMatchesRenderedHtml(
        string $scssFile,
        string $uri,
        string $loginAs,
    ): void {
        $selectors = $this->extractBemSelectors($scssFile);
        if ([] === $selectors) {
            self::markTestSkipped(\sprintf('No BEM selectors found in %s', basename($scssFile)));
        }

        $client = self::createClient();
        $this->login($client, $loginAs);
        $crawler = $client->request('GET', $uri);
        self::assertResponseIsSuccessful();

        foreach ($selectors as $selector) {
            self::assertGreaterThan(
                0,
                $crawler->filter('.' . $selector)->count(),
                \sprintf(
                    'BEM class ".%s" is styled in %s but absent from %s — the plugin may have renamed or removed it.',
                    $selector,
                    basename($scssFile),
                    $uri,
                ),
            );
        }
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideScssAndPage(): iterable
    {
        $styles = \dirname(__DIR__, 3) . '/assets/shop/styles';

        yield 'copilot widget on dashboard' => [
            $styles . '/_copilot.scss',
            '/fr_FR/account/dashboard',
            'claire@acme.example',
        ];
    }

    /**
     * @return list<string>
     */
    private function extractBemSelectors(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $content = (string) file_get_contents($path);
        preg_match_all('/\.([a-z][\w-]+__[\w-]+)/', $content, $matches);

        return array_values(array_unique($matches[1]));
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
