<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;

/** US-086: the example of the quick order page is made of real references; pasting it gives a valid preview. */
final class QuickOrderExampleSmokeTest extends WebTestCase
{
    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheExampleOfThePageIsAvailableAsPlaceholder(string $locale): void
    {
        $client = self::createClient();
        $client->request('GET', '/' . $locale . '/quick-order');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('SKU-001', (string) $client->getResponse()->getContent(), $locale);
        self::assertSame($this->example(), $client->getCrawler()->filter('#tmonier-quick-order-lines')->attr('placeholder'), $locale);
    }

    /** US-139: the example comes from the plugin option `example_lines`, not from a copy of its template. */
    public function testTheFormTemplateOfThePluginIsNotOverridden(): void
    {
        self::assertFileDoesNotExist(\dirname(__DIR__, 2) . '/templates/bundles/TmonierSyliusQuickOrderPlugin/shop/quick_order/_form.html.twig');
    }

    public function testEveryReferenceOfTheExampleExistsInTheCatalogue(): void
    {
        $skus = [];
        foreach (glob(\dirname(__DIR__, 2) . '/fixtures/csv/*.csv') ?: [] as $file) {
            $handle = fopen($file, 'r');
            self::assertNotFalse($handle);
            while (false !== ($row = fgetcsv($handle, escape: '\\'))) {
                $skus[] = $row[0] ?? '';
            }
            fclose($handle);
        }

        foreach ($this->lines() as [$sku]) {
            self::assertContains($sku, $skus, sprintf('"%s" was removed from the catalogue: update demo.quick_order_example_lines', $sku));
        }
    }

    public function testPastingTheExampleGivesAPreviewWithOnlyValidLines(): void
    {
        $client = self::createClient();
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => 'claire@acme.example']);
        self::assertInstanceOf(CustomerInterface::class, $customer);
        self::assertNotNull($customer->getUser());
        $client->loginUser($customer->getUser(), 'shop');

        $crawler = $client->request('GET', '/fr_FR/quick-order');
        $crawler = $client->submit($crawler->filter('[data-test-tmonier-quick-order-preview]')->form(), ['lines' => $this->example()]);

        self::assertResponseIsSuccessful();
        $statuses = $crawler->filter('[data-test-tmonier-quick-order-line]')->each(
            static fn ($row): string => (string) $row->attr('data-status'),
        );
        self::assertCount(\count($this->lines()), $statuses);
        self::assertSame([], array_values(array_diff($statuses, ['valid'])), 'every line of the example must be valid: ' . implode(',', $statuses));
    }

    private function example(): string
    {
        $config = Yaml::parseFile(\dirname(__DIR__, 2) . '/config/packages/demo_quick_order.yaml');
        self::assertIsArray($config);
        self::assertIsArray($config['parameters']);
        $value = $config['parameters']['demo.quick_order_example_lines'] ?? null;
        self::assertIsString($value);

        return $value;
    }

    /** @return list<array{string, string}> */
    private function lines(): array
    {
        return array_map(
            static fn (string $line): array => [(string) strtok($line, ";,\t "), $line],
            explode("\n", $this->example()),
        );
    }
}
