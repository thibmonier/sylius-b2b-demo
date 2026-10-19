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
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteMessageInterface;
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteRequestInterface;
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteRequestLineInterface;
use Tmonier\SyliusQuoteRequestPlugin\Factory\QuoteRequestFactoryInterface;
use Tmonier\SyliusQuoteRequestPlugin\Workflow\QuoteRequestStatuses;

/**
 * Creates 3 demo quote requests, numbered with the current year and dated relatively to the reset (US-097):
 * - Q-YYYY-001 for Acme Industries (Claire): in negotiation, a proposal answered by a counter-proposal and a reply
 * - Q-YYYY-002 for TechParts SAS (Marc): just received (status new)
 * - Q-YYYY-003 for GlobalMaint SA (Henri): being priced by the sales team (in review)
 *
 * Depends on: demo_products_csv (-30), demo_companies (-15)
 * Priority: -50
 */
final class QuoteDemoFixture extends AbstractFixture
{
    private const SALES_TEAM = 'Équipe commerciale';

    /**
     * @param RepositoryInterface<CustomerInterface>       $customerRepository
     * @param RepositoryInterface<ChannelInterface>        $channelRepository
     * @param RepositoryInterface<ProductVariantInterface> $variantRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'tmonier_quote_request.factory')]
        private readonly QuoteRequestFactoryInterface $quoteRequestFactory,
        #[Autowire(service: 'sylius.repository.customer')]
        private readonly RepositoryInterface $customerRepository,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'sylius.repository.product_variant')]
        private readonly RepositoryInterface $variantRepository,
    ) {
    }

    public function load(array $options): void
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneBy(['code' => 'B2B']) ?? $this->channelRepository->findOneBy(['enabled' => true]);
        if (null === $channel) {
            return;
        }

        $now = new \DateTimeImmutable();
        $year = $now->format('Y');
        $this->negotiatedQuote($channel, $now, sprintf('Q-%s-001', $year));
        $this->receivedQuote($channel, $now, sprintf('Q-%s-002', $year));
        $this->quoteInReview($channel, $now, sprintf('Q-%s-003', $year));
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_quotes';
    }

    /** The request of Claire, the proposal of the sales team at a price per line, her counter-proposal, the reply. */
    private function negotiatedQuote(ChannelInterface $channel, \DateTimeImmutable $now, string $number): void
    {
        $quote = $this->newQuote($channel, 'claire@acme.example', $number, 'Acme Industries', $now->modify('-12 days'), ['EPI-CASK-001' => 60, 'EPI-GANT-001' => 120, 'EPI-CHAU-001' => 24]);
        if (null === $quote) {
            return;
        }

        foreach ($quote->getLines() as $line) {
            $line->setUnitPrice((int) round($line->getCatalogUnitPrice() * 0.9));
        }
        $quote->setStatus(QuoteRequestStatuses::PROPOSED);
        $quote->setProposedAt($now->modify('-10 days'));
        $quote->setValidUntil($now->modify('+14 days'));
        $name = $quote->getFullName();
        $this->say($quote, QuoteMessageInterface::AUTHOR_CUSTOMER, $name, '-12 days', 'Bonjour, nous équipons le nouvel atelier de Lyon : pouvez-vous nous faire une offre sur ces quantités, avec une livraison avant fin de mois ?');
        $this->say($quote, QuoteMessageInterface::AUTHOR_ADMIN, self::SALES_TEAM, '-10 days', 'Voici notre meilleur prix sur ces quantités : 10 % de moins que le tarif catalogue. Livraison sous 10 jours ouvrés.');
        $this->say($quote, QuoteMessageInterface::AUTHOR_CUSTOMER, $name, '-7 days', 'Merci. Si nous passons les gants à 200 paires sur l\'année, pouvez-vous améliorer le prix des gants ?');
        $this->say($quote, QuoteMessageInterface::AUTHOR_ADMIN, self::SALES_TEAM, '-6 days', 'Oui : un palier à 100 paires est ajouté sur les gants, le prix baisse encore. L\'offre reste valable jusqu\'à son échéance.');
        $this->addGlovesTier($quote);
        $this->entityManager->persist($quote);
    }

    private function receivedQuote(ChannelInterface $channel, \DateTimeImmutable $now, string $number): void
    {
        $quote = $this->newQuote($channel, 'marc@techparts.example', $number, 'TechParts SAS', $now->modify('-3 days'), ['VISS-001' => 10, 'VISS-002' => 20]);
        if (null === $quote) {
            return;
        }

        $this->say($quote, QuoteMessageInterface::AUTHOR_CUSTOMER, $quote->getFullName(), '-3 days', 'Bonjour, merci de nous chiffrer ces boîtes de visserie pour notre ligne de montage.');
        $this->entityManager->persist($quote);
    }

    private function quoteInReview(ChannelInterface $channel, \DateTimeImmutable $now, string $number): void
    {
        $quote = $this->newQuote($channel, 'henri@globalmaint.example', $number, 'GlobalMaint SA', $now->modify('-5 days'), ['EPI-CASK-001' => 40, 'OUTI-001' => 12]);
        if (null === $quote) {
            return;
        }

        $quote->setStatus(QuoteRequestStatuses::IN_REVIEW);
        $this->say($quote, QuoteMessageInterface::AUTHOR_CUSTOMER, $quote->getFullName(), '-5 days', 'Bonjour, nous avons besoin de ces articles pour la maintenance du site de Lyon.');
        $this->entityManager->persist($quote);
    }

    /**
     * The quote with its lines, or null when it exists already or its customer or variants are missing.
     *
     * @param array<string, int> $items quantity by variant code
     */
    private function newQuote(ChannelInterface $channel, string $email, string $number, string $company, \DateTimeImmutable $createdAt, array $items): ?QuoteRequestInterface
    {
        $customer = $this->customerRepository->findOneBy(['emailCanonical' => $email]);
        if (!$customer instanceof CustomerInterface || null !== $this->entityManager->getRepository(QuoteRequestInterface::class)->findOneBy(['number' => $number])) {
            return null;
        }

        $quote = $this->quoteRequestFactory->createForChannel($channel, (string) $channel->getDefaultLocale()?->getCode(), $customer);
        $quote->setNumber($number);
        $quote->setStatus(QuoteRequestStatuses::NEW);
        $quote->setCompany($company);
        $quote->setCreatedAt($createdAt);
        $quote->setUpdatedAt($createdAt);
        foreach ($this->variantRepository->findBy(['code' => array_keys($items)]) as $variant) {
            $line = $this->quoteRequestFactory->createLine($quote, $variant, $items[(string) $variant->getCode()]);
            if ($line instanceof QuoteRequestLineInterface) {
                $quote->addLine($line);
            }
        }

        return $quote->getLines()->isEmpty() ? null : $quote;
    }

    private function say(QuoteRequestInterface $quote, string $author, string $name, string $when, string $body): void
    {
        $message = $this->quoteRequestFactory->createMessage($author, $name, $body);
        $message->setCreatedAt((new \DateTimeImmutable())->modify($when));
        $quote->addMessage($message);
    }

    /** Volume discount on the gloves line: 3 % from 100 pairs (the quantity of the quote is above). */
    private function addGlovesTier(QuoteRequestInterface $quote): void
    {
        foreach ($quote->getLines() as $line) {
            if ('EPI-GANT-001' === $line->getVariantCode()) {
                $line->setDiscountType(QuoteRequestLineInterface::DISCOUNT_PERCENTAGE);
                $line->setDiscountTiers([['min_quantity' => 100, 'value' => 3.0]]);
            }
        }
    }
}
