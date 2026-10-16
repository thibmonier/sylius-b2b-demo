<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\BuyerCredential;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\BuyerIdentity;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\CredentialHasher;

/**
 * Creates a PunchOut cXML buyer configuration for Acme Industries.
 *
 * The shared secret is intentionally non-sensitive ("demo-secret-acme") and stored hashed.
 *
 * Depends on: demo_companies (-15)
 * Priority: -50
 */
final class PunchoutDemoFixture extends AbstractFixture
{
    public const BUYER_CODE = 'acme-punchout';

    /** Non-secret, displayed in the demo accounts page */
    public const DEMO_SECRET = 'demo-secret-acme';

    /**
     * @param RepositoryInterface<CustomerInterface> $customerRepository
     * @param RepositoryInterface<ChannelInterface>  $channelRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CredentialHasher $credentialHasher,
        #[Autowire(service: 'sylius.repository.customer')]
        private readonly RepositoryInterface $customerRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
    ) {
    }

    public function load(array $options): void
    {
        // Idempotent: remove previous version if any
        $existing = $this->entityManager->getRepository(Buyer::class)->findOneBy(['code' => self::BUYER_CODE]);
        if (null !== $existing) {
            return;
        }

        /** @var ChannelInterface|null $b2bChannel */
        $b2bChannel = $this->channelRepository->findOneBy(['code' => 'B2B'])
            ?? $this->channelRepository->findOneBy(['enabled' => true]);

        /** @var CustomerInterface|null $claire */
        $claire = $this->customerRepository->findOneBy(['emailCanonical' => 'claire@acme.example']);

        if (null === $b2bChannel) {
            return;
        }

        $buyer = new Buyer();
        $buyer->setCode(self::BUYER_CODE);
        $buyer->setName('Acme Industries — PunchOut B2B');
        $buyer->setProtocol('cxml');
        $buyer->setEnabled(true);
        $buyer->setChannel($b2bChannel);
        $buyer->setCustomer($claire);
        $buyer->setCompanyId('ACME_IND');
        // Strict allowlist of ERP return domains (no localhost: avoids an SSRF/open-redirect shape).
        $buyer->setReturnDomains(['erp.acme.example']);
        $buyer->setLocale('fr_FR');
        // US-026 (I3): an OrderRequest is accepted only with a payment method, and the on-account one
        // makes the order reserve Acme's trade credit and go through purchase approval, as in the shop.
        $buyer->setOrderPaymentMethod(TradeCreditDemoFixture::ON_ACCOUNT_PAYMENT_METHOD);
        $buyer->setOrderShippingMethod('carrier');

        // Identities (cXML From / Sender)
        foreach ([
            BuyerIdentity::ROLE_FROM => ['domain' => 'DUNS', 'identity' => '123456789'],
            BuyerIdentity::ROLE_SENDER => ['domain' => 'AribaNetworkUserId', 'identity' => 'acme-demo@ariba.example'],
        ] as $role => $data) {
            $identity = new BuyerIdentity();
            $identity->setRole($role);
            $identity->setDomain($data['domain']);
            $identity->setIdentity($data['identity']);
            $buyer->addIdentity($identity);
        }

        // Shared secret (hashed — non-sensitive demo value)
        $credential = new BuyerCredential();
        $credential->setLabel('demo');
        $credential->setPasswordHash($this->credentialHasher->hash(self::DEMO_SECRET));
        $buyer->addCredential($credential);

        $this->entityManager->persist($buyer);
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_punchout';
    }
}
