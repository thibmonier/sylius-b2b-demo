<?php

declare(strict_types=1);

namespace App\Demo\Media;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ImageInterface;

/** The `path` of every image entity (products, taxons, avatars, plugins' own images), found from the mapping. */
final readonly class DoctrineImagePathSource implements ImagePathSource
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function paths(): iterable
    {
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            if ($metadata->isMappedSuperclass || !is_subclass_of($metadata->getName(), ImageInterface::class)) {
                continue;
            }
            $rows = $this->entityManager->createQuery(\sprintf('SELECT i.path FROM %s i WHERE i.path IS NOT NULL', $metadata->getName()))->getScalarResult();
            foreach ($rows as $row) {
                if (\is_array($row) && \is_string($row['path'] ?? null)) {
                    yield $row['path'];
                }
            }
        }
    }
}
