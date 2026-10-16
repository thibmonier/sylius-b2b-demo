<?php

declare(strict_types=1);

namespace App\Order;

interface OrderPoReferenceAwareInterface
{
    public const PO_REFERENCE_MAX_LENGTH = 64;

    public const PO_REFERENCE_PATTERN = '/^[A-Za-z0-9-]*\z/';

    public const COST_CENTER_MAX_LENGTH = 50;

    public const COST_CENTER_PATTERN = '/^[A-Za-z0-9 _.\-]*\z/';

    public function getPoReference(): ?string;

    public function setPoReference(?string $reference): void;

    public function getCostCenter(): ?string;

    public function setCostCenter(?string $costCenter): void;
}
