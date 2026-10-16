<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusFacturXPlugin\Entity\ChannelSettings;
use Tmonier\SyliusFacturXPlugin\Repository\ChannelSettingsRepositoryInterface;

/**
 * US-011: turns the Factur-X on for the B2B channel (seller data of the fictive shop), so that the
 * invoices created with the demo orders are Factur-X PDF/A-3. The identifiers are fictive but well formed.
 *
 * Priority: -55 (after the channel, before the orders at -60).
 */
final class FacturXSettingsDemoFixture extends AbstractFixture
{
    /** @param RepositoryInterface<ChannelInterface> $channelRepository */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'tmonier_facturx.repository.channel_settings')]
        private readonly ChannelSettingsRepositoryInterface $settingsRepository,
    ) {
    }

    public function load(array $options): void
    {
        $channel = $this->channelRepository->findOneBy(['code' => 'B2B']);
        if (!$channel instanceof ChannelInterface || null !== $this->settingsRepository->findOneByChannel($channel)) {
            return;
        }

        $settings = new ChannelSettings();
        $settings->setChannel($channel);
        $settings->setEnabled(true);
        $settings->setLegalName('Fournitures Pro SAS (fictive)');
        $settings->setSiren('123456782');
        $settings->setSiret('12345678200002');
        $settings->setLegalForm('SAS');
        $settings->setTradeRegister('RCS Lyon 123 456 782');
        $settings->setPaymentTerms('Paiement à 30 jours');
        $settings->setPaymentDueDays(30);
        $settings->setIban('FR7630001007941234567890185');
        $settings->setBic('BDFEFRPPCCT');

        $this->entityManager->persist($settings);
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_facturx_settings';
    }
}
