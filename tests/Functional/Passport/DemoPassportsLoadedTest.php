<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * US-235: the demo suite creates the 5 passports. The nightly reset (`app:demo:reset`, crontab `0 3 * * *`) reloads
 * this same suite with `sylius:fixtures:load demo`; DemoResetCommandTest checks the count after a real reset.
 */
final class DemoPassportsLoadedTest extends KernelTestCase
{
    public function testTheFivePassportsExistOnTheirProducts(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);

        $codes = [];
        foreach ($entityManager->getRepository(DigitalProductPassport::class)->findAll() as $passport) {
            $codes[] = $passport->getProductVariant()->getProduct()?->getCode();
        }
        sort($codes);

        self::assertSame(['EPI-CASK-001', 'EPI-CASK-002', 'EPI-CASK-003', 'OUTI-026', 'OUTI-028'], $codes);
    }
}
