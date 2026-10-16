<?php

declare(strict_types=1);

namespace App\Order;

use Doctrine\ORM\Mapping as ORM;

trait OrderPoReferenceAwareTrait
{
    #[ORM\Column(name: 'po_reference', type: 'string', length: 100, nullable: true)]
    private ?string $poReference = null;

    #[ORM\Column(name: 'cost_center', type: 'string', length: 50, nullable: true)]
    private ?string $costCenter = null;

    public function getPoReference(): ?string
    {
        return $this->poReference;
    }

    public function setPoReference(?string $reference): void
    {
        $this->poReference = $reference;
    }

    public function getCostCenter(): ?string
    {
        return $this->costCenter;
    }

    public function setCostCenter(?string $costCenter): void
    {
        $this->costCenter = $costCenter;
    }
}
