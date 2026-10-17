<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Demo\Http\PublicHost;
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
 * Creates the PunchOut buyers of Acme Industries: a cXML one and, for the OCI simulator (US-053), an SAP OCI one.
 *
 * The shared secret is intentionally non-sensitive ("demo-secret-acme") and stored hashed.
 *
 * Depends on: demo_companies (-15)
 * Priority: -50
 */
final class PunchoutDemoFixture extends AbstractFixture
{
    public const BUYER_CODE = 'acme-punchout';

    public const OCI_BUYER_CODE = 'acme-punchout-oci';

    public const OCI_USERNAME = 'acme-oci-demo';

    /** Non-secret, displayed in the demo accounts page */
    public const DEMO_SECRET = 'demo-secret-acme';

    public const ERP_DOMAIN = 'erp.acme.example';

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
        #[Autowire(env: 'DEFAULT_URI')]
        private readonly string $defaultUri,
        #[Autowire(env: 'default::DEMO_PUBLIC_HOST')]
        private readonly ?string $publicHost = null,
    ) {
    }

    /**
     * The simulator (US-027) receives the cart on the public host of the demo, so that host must be a return
     * domain of the buyer. The plugin only accepts HTTPS return URLs: an HTTP DEFAULT_URI (local) adds nothing.
     *
     * US-066: the custom host (DEMO_PUBLIC_HOST) is a return domain too, the simulator builds its return URL from the request host.
     *
     * @return list<string>
     */
    public static function returnDomains(string $defaultUri, string $publicHost = ''): array
    {
        $parts = parse_url($defaultUri);
        $host = strtolower(is_array($parts) ? $parts['host'] ?? '' : '');
        $domains = [self::ERP_DOMAIN];
        if (is_array($parts) && 'https' === ($parts['scheme'] ?? null)) {
            $domains[] = $host;
        }
        $domains[] = PublicHost::normalize($publicHost) ?? '';

        return array_values(array_unique(array_filter($domains, static fn (string $domain): bool => '' !== $domain)));
    }

    public function load(array $options): void
    {
        $this->loadCxmlBuyer();
        $this->loadOciBuyer();
    }

    private function loadCxmlBuyer(): void
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
        // Strict allowlist: the ERP domain and the public HTTPS host of the demo (no localhost: avoids an
        // SSRF/open-redirect shape).
        $buyer->setReturnDomains(self::returnDomains($this->defaultUri, (string) $this->publicHost));
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

    /**
     * The same company on the OCI protocol: a user name and the same published secret (stored hashed). An OCI
     * buyer never authenticates on the cXML endpoint, so it is a buyer of its own.
     */
    private function loadOciBuyer(): void
    {
        if (null !== $this->entityManager->getRepository(Buyer::class)->findOneBy(['code' => self::OCI_BUYER_CODE])) {
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
        $buyer->setCode(self::OCI_BUYER_CODE);
        $buyer->setName('Acme Industries — PunchOut OCI');
        $buyer->setProtocol('oci');
        $buyer->setOciUsername(self::OCI_USERNAME);
        $buyer->setEnabled(true);
        $buyer->setChannel($b2bChannel);
        $buyer->setCustomer($claire);
        $buyer->setCompanyId('ACME_IND');
        $buyer->setReturnDomains(self::returnDomains($this->defaultUri, (string) $this->publicHost));
        $buyer->setLocale('fr_FR');

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
