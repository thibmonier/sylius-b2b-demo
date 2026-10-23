<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\PunchOutSimulator\BuyerCredentialsProvider;
use App\Demo\PunchOutSimulator\DemoBuyerCodes;
use App\Demo\PunchOutSimulator\OciBuyerProvider;
use App\Demo\PunchOutSimulator\SimulatorException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;

final class DemoBuyerCodesTest extends TestCase
{
    public function testTheSimulatorLooksTheOciBuyerUpByTheSharedCode(): void
    {
        /** @var EntityRepository<Buyer>&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['code' => DemoBuyerCodes::OCI])->willReturn(null);

        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('buyer_missing');

        (new OciBuyerProvider($this->entityManager($repository)))->buyer();
    }

    public function testACxmlBuyerAbsentFromTheSharedCodeIsRefused(): void
    {
        /** @var EntityRepository<Buyer>&\PHPUnit\Framework\MockObject\MockObject $repository */
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['code' => DemoBuyerCodes::CXML])->willReturn(null);

        $this->expectException(SimulatorException::class);
        $this->expectExceptionMessage('buyer_missing');

        (new BuyerCredentialsProvider($this->entityManager($repository)))->provide();
    }

    public function testTheTwoCodesDiffer(): void
    {
        self::assertNotSame(DemoBuyerCodes::CXML, DemoBuyerCodes::OCI);
    }

    /** @param EntityRepository<Buyer> $repository */
    private function entityManager(EntityRepository $repository): EntityManagerInterface
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        return $entityManager;
    }
}
