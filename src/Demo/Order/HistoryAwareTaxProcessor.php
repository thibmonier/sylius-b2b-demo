<?php

declare(strict_types=1);

namespace App\Demo\Order;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

/** Sylius' tax processor, left out while the history of the demo is built (see {@see HistoryWithoutTax}). */
#[AsDecorator(decorates: 'sylius.order_processing.order_taxes_processor')]
final readonly class HistoryAwareTaxProcessor implements OrderProcessorInterface
{
    public function __construct(
        #[AutowireDecorated]
        private OrderProcessorInterface $inner,
        private HistoryWithoutTax $history,
    ) {
    }

    public function process(OrderInterface $order): void
    {
        if ($this->history->isActive()) {
            return;
        }

        $this->inner->process($order);
    }
}
