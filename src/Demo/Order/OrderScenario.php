<?php

declare(strict_types=1);

namespace App\Demo\Order;

/**
 * Life-cycle states reached by a demo order (US-011). Each case is produced by the real Sylius state machines.
 */
enum OrderScenario: string
{
    /** Payment completed and shipment shipped. */
    case Shipped = 'shipped';

    /** Payment completed, shipment still to be sent. */
    case Paid = 'paid';

    /** Placed, awaiting the payment. */
    case AwaitingPayment = 'awaiting_payment';

    /** Over the buyer limit: an approval request is waiting for an approver. */
    case PendingApproval = 'pending_approval';

    /** Over the buyer limit, approved by an approver, then paid and shipped. */
    case ApprovedShipped = 'approved_shipped';

    /** Paid then refunded. */
    case Refunded = 'refunded';

    /** Order cancelled. */
    case Cancelled = 'cancelled';
}
