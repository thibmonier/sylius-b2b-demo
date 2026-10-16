<?php

declare(strict_types=1);

namespace App\PunchOut;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Resource\Factory\FactoryInterface;

/**
 * Order factory of the PunchOut carts (US-027 workaround, to remove with the punchout-cxml fixes of sprint 7).
 *
 * PunchoutCartFactory attaches the buyer's customer with setCustomer(), which leaves Sylius' "created by guest"
 * flag at true: the trade-credit plugin then refuses the on-account payment of every PunchOut OrderRequest
 * ("no_account"). setCustomerWithAuthorization() is the only way Sylius clears that flag; the customer the
 * plugin sets right after does not raise it again. Only PunchoutCartFactory receives this factory.
 *
 * @implements FactoryInterface<OrderInterface>
 */
final readonly class AuthorizedPunchOutOrderFactory implements FactoryInterface
{
    /** @param FactoryInterface<OrderInterface> $orderFactory */
    public function __construct(private FactoryInterface $orderFactory)
    {
    }

    public function createNew(): OrderInterface
    {
        $order = $this->orderFactory->createNew();
        $order->setCustomerWithAuthorization(null);

        return $order;
    }
}
