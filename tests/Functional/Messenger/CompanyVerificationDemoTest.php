<?php

declare(strict_types=1);

namespace App\Tests\Functional\Messenger;

use App\Tests\Functional\Demo\DatabaseAccess;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Tmonier\SyliusCompanyVerificationPlugin\Entity\CompanyVerification;
use Tmonier\SyliusCompanyVerificationPlugin\Message\VerifyCompany;
use Tmonier\SyliusCompanyVerificationPlugin\Status\RegisterStatus;
use Tmonier\SyliusCompanyVerificationPlugin\Status\VatStatus;

/** T-TECH-55-03: company-verification in the demo: one recorded result, an asynchronous message, no service called by a test. */
final class CompanyVerificationDemoTest extends KernelTestCase
{
    use DatabaseAccess;

    public function testAcmeIndustriesIsRecordedAsActiveWithAValidVatNumber(): void
    {
        $verifications = $this->entityManager()->getRepository(CompanyVerification::class)->findAll();

        self::assertCount(1, $verifications);
        self::assertSame('ACME_IND', $verifications[0]->getCompany()->getCode());
        self::assertSame(RegisterStatus::Active, $verifications[0]->getRegisterStatus());
        self::assertSame(VatStatus::Valid, $verifications[0]->getVatStatus());
    }

    public function testTheFixturesLeaveNoMessageInTheQueue(): void
    {
        self::bootKernel();

        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM messenger_messages'));
    }

    public function testTheVerificationMessageIsSentToTheAsyncTransportAndNotHandled(): void
    {
        $bus = self::getContainer()->get('sylius.command_bus');
        $transport = self::getContainer()->get('messenger.transport.async');
        \assert($bus instanceof MessageBusInterface && $transport instanceof InMemoryTransport);
        $transport->reset();

        $bus->dispatch(new VerifyCompany(0));

        self::assertCount(1, $transport->getSent());
    }

    private function entityManager(): EntityManagerInterface
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);

        return $entityManager;
    }
}
