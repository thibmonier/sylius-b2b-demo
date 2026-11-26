<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusPlatformHealthPlugin\Data\SchemaDriftReaderInterface;

/**
 * US-318 (T-318-04): the database built by the migrations alone (bin/prepare-test-db) is the one the Doctrine
 * mapping describes, so the `data.schema_drift` probe of the health page stays `ok`. The count is the probe's own
 * reader, outside any console command, as on the page; an entity changed without its migration fails here.
 */
final class SchemaDriftTest extends KernelTestCase
{
    public function testTheMigratedDatabaseHasNoDriftFromTheMapping(): void
    {
        self::bootKernel();
        $reader = self::getContainer()->get('tmonier_sylius_platform_health.data.schema_drift_reader');
        self::assertInstanceOf(SchemaDriftReaderInterface::class, $reader);

        self::assertSame(0, $reader->countStatements(), sprintf(
            "Schema drift between the migrations and the mapping. What doctrine:schema:update would run:\n%s",
            implode("\n", $this->statements()),
        ));
    }

    /** @return list<string> the statements, for the failure message only */
    private function statements(): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        return (new SchemaTool($entityManager))->getUpdateSchemaSql($entityManager->getMetadataFactory()->getAllMetadata());
    }
}
