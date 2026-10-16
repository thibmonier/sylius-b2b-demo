<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Entity\Channel\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * US-021 — Configures the B2B demo channel to require PO reference
 * and enable cost center at checkout.
 */
final class PoReferenceChannelDemoFixture extends AbstractFixture
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function load(array $options): void
    {
        $b2bChannel = $this->entityManager
            ->getRepository(Channel::class)
            ->findOneBy(['code' => 'B2B']);

        if (!$b2bChannel instanceof Channel) {
            return;
        }

        $b2bChannel->setPoReferenceRequired(true);
        $b2bChannel->setCostCenterEnabled(true);

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_po_reference_channel';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        // No options needed.
    }
}
