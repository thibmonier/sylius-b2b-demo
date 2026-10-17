<?php

declare(strict_types=1);

namespace App\Entity\Demo;

use Doctrine\ORM\Mapping as ORM;

/**
 * US-048 / T-048-10: written once the whole `demo` fixture suite has loaded (see LoadMarkerListener). The entrypoint
 * reads it to tell a finished load from one that was killed half-way. It is a mapped entity so that the purger empties
 * it with the rest: a reload that dies after the purge leaves no marker.
 */
#[ORM\Entity]
#[ORM\Table(name: 'app_demo_load_marker')]
class LoadMarker
{
    #[ORM\Id]
    #[ORM\Column(type: 'smallint')]
    private int $id = 1;

    #[ORM\Column(name: 'loaded_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $loadedAt;

    public function __construct()
    {
        $this->loadedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLoadedAt(): \DateTimeImmutable
    {
        return $this->loadedAt;
    }
}
