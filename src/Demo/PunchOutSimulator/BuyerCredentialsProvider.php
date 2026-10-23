<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use App\Demo\DataFixtures\PunchoutDemoFixture;
use Doctrine\ORM\EntityManagerInterface;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\BuyerIdentity;

/**
 * The credentials of the demo buyer (Acme Industries): identities read from the buyer configured in the
 * shop, shared secret from the demo fixture (a published, non-sensitive value). The secret stays on the
 * server: it is written into the cXML sent from here and never rendered in a page.
 */
final readonly class BuyerCredentialsProvider
{
    private const WRONG_SECRET = 'not-the-shared-secret';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /** @throws SimulatorException when the demo buyer is not loaded */
    public function provide(bool $wrongSecret = false): BuyerCredentials
    {
        $identities = [];
        foreach ($this->buyer()->getIdentities() as $identity) {
            $identities[$identity->getRole()] = $identity;
        }
        $from = $identities[BuyerIdentity::ROLE_FROM] ?? null;
        $sender = $identities[BuyerIdentity::ROLE_SENDER] ?? null;
        if (!$from instanceof BuyerIdentity || !$sender instanceof BuyerIdentity) {
            throw new SimulatorException('buyer_missing');
        }

        return new BuyerCredentials(
            $from->getDomain(),
            $from->getIdentity(),
            $sender->getDomain(),
            $sender->getIdentity(),
            $wrongSecret ? self::WRONG_SECRET : PunchoutDemoFixture::DEMO_SECRET,
        );
    }

    /** @return list<string> */
    public function returnDomains(): array
    {
        return $this->buyer()->getReturnDomains();
    }

    private function buyer(): Buyer
    {
        $buyer = $this->entityManager->getRepository(Buyer::class)->findOneBy(['code' => DemoBuyerCodes::CXML]);

        return $buyer instanceof Buyer ? $buyer : throw new SimulatorException('buyer_missing');
    }
}
