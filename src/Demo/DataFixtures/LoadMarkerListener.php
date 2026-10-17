<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Entity\Demo\LoadMarker;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Listener\AbstractListener;
use Sylius\Bundle\FixturesBundle\Listener\AfterSuiteListenerInterface;
use Sylius\Bundle\FixturesBundle\Listener\SuiteEvent;

/**
 * US-048 / T-048-10: after the last fixture of the suite, writes the end-of-load marker the entrypoint looks for.
 * A listener rather than a last fixture: it stays last whatever fixtures are added to the suite later.
 */
final class LoadMarkerListener extends AbstractListener implements AfterSuiteListenerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function afterSuite(SuiteEvent $suiteEvent, array $options): void
    {
        $this->entityManager->persist(new LoadMarker());
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_load_marker';
    }
}
