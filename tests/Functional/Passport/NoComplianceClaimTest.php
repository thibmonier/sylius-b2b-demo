<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use App\Tests\Support\ComplianceClaimScanner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * US-235: no text of the demo presents the passport as compliant or certified (rule: see ComplianceClaimScanner).
 */
final class NoComplianceClaimTest extends WebTestCase
{
    public function testThePluginSheetMakesNoComplianceClaimInFrenchOrEnglish(): void
    {
        $plugins = Yaml::parseFile(\dirname(__DIR__, 3) . '/config/demo/plugins.yaml');
        self::assertIsArray($plugins);
        $entry = $this->findEntry($plugins, 'sylius-digital-product-passport-plugin');
        self::assertNotNull($entry, 'The passport plugin sheet is missing from config/demo/plugins.yaml.');

        $text = json_encode($entry, \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
        self::assertSame([], ComplianceClaimScanner::claims(str_replace('","', '". "', $text)));
    }

    public function testThePublicPageOfEveryPassportMakesNoComplianceClaimInBothLocales(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);
        $passports = $entityManager->getRepository(DigitalProductPassport::class)->findAll();
        self::assertNotSame([], $passports);

        foreach ($passports as $passport) {
            foreach (['fr_FR', 'en_US'] as $locale) {
                $client->request('GET', \sprintf('/%s/product-passport/%d', $locale, $passport->getId()));
                self::assertResponseIsSuccessful();
                self::assertSame([], ComplianceClaimScanner::claims((string) $client->getResponse()->getContent()), $locale . ' passport ' . $passport->getId());
            }
        }
    }

    /**
     * @param array<array-key, mixed> $node
     *
     * @return array<array-key, mixed>|null
     */
    private function findEntry(array $node, string $slug): ?array
    {
        if (($node['slug'] ?? null) === $slug) {
            return $node;
        }
        foreach ($node as $child) {
            $found = \is_array($child) ? $this->findEntry($child, $slug) : null;
            if (null !== $found) {
                return $found;
            }
        }

        return null;
    }
}
