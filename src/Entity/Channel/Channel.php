<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel
{
    #[ORM\Column(name: 'po_reference_required', type: 'boolean', options: ['default' => false])]
    private bool $poReferenceRequired = false;

    #[ORM\Column(name: 'cost_center_enabled', type: 'boolean', options: ['default' => false])]
    private bool $costCenterEnabled = false;

    public function isPoReferenceRequired(): bool
    {
        return $this->poReferenceRequired;
    }

    public function setPoReferenceRequired(bool $poReferenceRequired): void
    {
        $this->poReferenceRequired = $poReferenceRequired;
    }

    public function isCostCenterEnabled(): bool
    {
        return $this->costCenterEnabled;
    }

    public function setCostCenterEnabled(bool $costCenterEnabled): void
    {
        $this->costCenterEnabled = $costCenterEnabled;
    }
}
