<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Data of the B2B cart page (US-200): the saving of a line and the approval announced before the validation. */
final class CartThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly CartTierDiscountProvider $discounts,
        private readonly CartApprovalNoticeProvider $approvals,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_b2b_cart_line_discount', fn (OrderItemInterface $item): ?CartLineDiscount => $this->discounts->forItem($item)),
            new TwigFunction('app_b2b_cart_approval', fn (OrderInterface $order): ?CartApprovalNotice => $this->approvals->forOrder($order)),
        ];
    }
}
