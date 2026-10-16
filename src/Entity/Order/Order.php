<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Order\OrderPoReferenceAwareInterface;
use App\Order\OrderPoReferenceAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderPoReferenceAwareInterface
{
    use OrderPoReferenceAwareTrait;
}
