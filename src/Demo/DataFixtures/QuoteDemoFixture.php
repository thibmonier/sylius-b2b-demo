<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteRequestInterface;
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteRequestLineInterface;
use Tmonier\SyliusQuoteRequestPlugin\Factory\QuoteRequestFactoryInterface;
use Tmonier\SyliusQuoteRequestPlugin\Workflow\QuoteRequestStatuses;

/**
 * Creates 2 demo quote requests:
 * - Q-2024-001 for Acme Industries (Claire), status = proposed (represents "envoyé")
 * - Q-2024-002 for TechParts SAS (Marc), status = new (represents "brouillon")
 *
 * Depends on: demo_products_csv (-30), demo_companies (-15)
 * Priority: -50
 */
final class QuoteDemoFixture extends AbstractFixture
{
    /**
     * @param RepositoryInterface<CustomerInterface> $customerRepository
     * @param RepositoryInterface<ChannelInterface>  $channelRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_quote_request.factory')]
        private readonly QuoteRequestFactoryInterface $quoteRequestFactory,
        #[Autowire(service: 'sylius.repository.customer')]
        private readonly RepositoryInterface $customerRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
    ) {
    }

    public function load(array $options): void
    {
        /** @var ChannelInterface|null $b2bChannel */
        $b2bChannel = $this->channelRepository->findOneBy(['code' => 'B2B'])
            ?? $this->channelRepository->findOneBy(['enabled' => true]);

        if (null === $b2bChannel) {
            return;
        }

        $localeCode = $b2bChannel->getDefaultLocale()?->getCode() ?? 'fr_FR';

        // Q-2024-001: Acme Industries / Claire — status = proposed (envoyé)
        $claire = $this->findCustomerByEmail('claire@acme.example');
        if (null !== $claire && null === $this->findQuoteByNumber('Q-2024-001')) {
            $epiVariants = $this->findVariantsByPrefix('EPI-', 3, $b2bChannel);
            if ([] !== $epiVariants) {
                $quote = $this->createQuote($b2bChannel, $localeCode, $claire, 'Q-2024-001', 'Acme Industries', QuoteRequestStatuses::PROPOSED);
                foreach ($epiVariants as $qty => $variant) {
                    $this->addLine($quote, $variant, $qty + 2, $b2bChannel);
                }
                $this->entityManager->persist($quote);
            }
        }

        // Q-2024-002: TechParts SAS / Marc — status = new (brouillon)
        $marc = $this->findCustomerByEmail('marc@techparts.example');
        if (null !== $marc && null === $this->findQuoteByNumber('Q-2024-002')) {
            $hardwareVariants = $this->findVariantsByPrefix('VISS-', 5, $b2bChannel);
            if ([] !== $hardwareVariants) {
                $quote = $this->createQuote($b2bChannel, $localeCode, $marc, 'Q-2024-002', 'TechParts SAS', QuoteRequestStatuses::NEW);
                foreach ($hardwareVariants as $qty => $variant) {
                    $this->addLine($quote, $variant, ($qty + 1) * 10, $b2bChannel);
                }
                $this->entityManager->persist($quote);
            }
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_quotes';
    }

    private function createQuote(
        ChannelInterface $channel,
        string $localeCode,
        CustomerInterface $customer,
        string $number,
        string $company,
        string $status,
    ): QuoteRequestInterface {
        $quote = $this->quoteRequestFactory->createForChannel($channel, $localeCode, $customer);
        $quote->setNumber($number);
        $quote->setStatus($status);
        $quote->setCompany($company);
        $quote->setCreatedAt(new \DateTimeImmutable('-30 days'));

        return $quote;
    }

    private function addLine(QuoteRequestInterface $quote, ProductVariantInterface $variant, int $quantity, ChannelInterface $channel): void
    {
        $line = $this->quoteRequestFactory->createLine($quote, $variant, $quantity);
        if ($line instanceof QuoteRequestLineInterface) {
            $line->setQuoteRequest($quote);
            $quote->addLine($line);
            $this->entityManager->persist($line);
        }
    }

    private function findCustomerByEmail(string $email): ?CustomerInterface
    {
        /** @var CustomerInterface|null $customer */
        $customer = $this->customerRepository->findOneBy(['emailCanonical' => strtolower($email)]);

        return $customer;
    }

    private function findQuoteByNumber(string $number): ?QuoteRequestInterface
    {
        /** @var QuoteRequestInterface|null $existing */
        $existing = $this->entityManager->getRepository(QuoteRequestInterface::class)->findOneBy(['number' => $number]);

        return $existing;
    }

    /**
     * @return list<ProductVariantInterface>
     */
    private function findVariantsByPrefix(string $prefix, int $limit, ChannelInterface $channel): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        /** @var list<ProductVariantInterface> $variants */
        $variants = $qb
            ->select('v')
            ->from(ProductVariantInterface::class, 'v')
            ->innerJoin('v.product', 'p')
            ->innerJoin('p.channels', 'c')
            ->innerJoin('v.channelPricings', 'cp')
            ->andWhere('c = :channel')
            ->andWhere('v.enabled = true')
            ->andWhere('p.enabled = true')
            ->andWhere('p.code LIKE :prefix')
            ->setParameter('channel', $channel)
            ->setParameter('prefix', $prefix . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $variants;
    }
}
