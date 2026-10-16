<?php

declare(strict_types=1);

namespace App\Order;

interface OrderPoReferenceAwareInterface
{
    public function getPoReference(): ?string;

    public function setPoReference(?string $reference): void;

    public function getCostCenter(): ?string;

    public function setCostCenter(?string $costCenter): void;
}
