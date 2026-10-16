<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Entity\Channel\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Tmonier\SyliusChannelAccessPlugin\Access\AccessMode;

/**
 * US-019 — The B2B demo channel hides its prices (and the buy button) from visitors who are not signed in;
 * the B2C channel stays open (the plugin default).
 */
final class ChannelAccessDemoFixture extends AbstractFixture
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function load(array $options): void
    {
        $b2bChannel = $this->entityManager->getRepository(Channel::class)->findOneBy(['code' => 'B2B']);

        if (!$b2bChannel instanceof Channel) {
            return;
        }

        $b2bChannel->setAccessMode(AccessMode::PricesHidden);
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_channel_access';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        // No options needed.
    }
}
