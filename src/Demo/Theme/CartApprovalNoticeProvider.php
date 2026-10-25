<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalAmountResolverInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalRequirementChecker;

final class CartApprovalNoticeProvider
{
    public function __construct(
        #[Autowire(service: 'tmonier_purchase_approval.requirement_checker')]
        private readonly ApprovalRequirementChecker $requirements,
        #[Autowire(service: 'tmonier_purchase_approval.amount_resolver')]
        private readonly ApprovalAmountResolverInterface $amounts,
        private readonly ApproverNameProvider $approvers,
    ) {
    }

    public function forOrder(OrderInterface $order): ?CartApprovalNotice
    {
        if ($order->isEmpty()) {
            return null;
        }
        $requirement = $this->requirements->getRequirement($order);
        if (null === $requirement) {
            return null;
        }
        $company = $requirement->member->getCompany();
        $amount = $this->amounts->resolve($order);

        return new CartApprovalNotice(
            $requirement->orderLimit,
            $amount,
            (string) $order->getCurrencyCode(),
            $company?->getName(),
            $company instanceof CompanyInterface ? $this->approvers->firstNameFor($company, $requirement->member, $amount) : null,
        );
    }
}
