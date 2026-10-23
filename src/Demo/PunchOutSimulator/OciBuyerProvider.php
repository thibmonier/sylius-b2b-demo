<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use App\Demo\DataFixtures\PunchoutDemoFixture;
use Doctrine\ORM\EntityManagerInterface;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;

/**
 * The SAP OCI buyer of the demo (Acme Industries): user name read from the buyer configured in the shop, password
 * from the demo fixture (a published, non-sensitive value). Like the cXML secret, it stays on the server and is
 * never rendered in a page.
 */
final readonly class OciBuyerProvider
{
    private const WRONG_PASSWORD = 'not-the-shared-secret';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /** @throws SimulatorException when the demo OCI buyer is not loaded */
    public function buyer(): Buyer
    {
        $buyer = $this->entityManager->getRepository(Buyer::class)->findOneBy(['code' => DemoBuyerCodes::OCI]);

        return $buyer instanceof Buyer ? $buyer : throw new SimulatorException('buyer_missing');
    }

    /** @throws SimulatorException */
    public function credentials(bool $wrongPassword = false): OciCredentials
    {
        $buyer = $this->buyer();

        return new OciCredentials($buyer->getCode(), (string) $buyer->getOciUsername(), $wrongPassword ? self::WRONG_PASSWORD : PunchoutDemoFixture::DEMO_SECRET);
    }

    /** @return list<string> */
    public function returnDomains(): array
    {
        try {
            return $this->buyer()->getReturnDomains();
        } catch (SimulatorException) {
            return [];
        }
    }
}
