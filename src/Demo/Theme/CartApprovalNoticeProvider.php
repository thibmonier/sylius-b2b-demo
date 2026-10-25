<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalPolicy;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalRequirementChecker;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;

/**
 * Message of the cart for an order above the limit of the buyer (US-200). The rule is the one of the purchase-approval
 * plugin itself (its requirement checker: the same total, the same limit, the same eligibility), read before the order
 * exists; nothing is decided or stored here. The approver named is a member of the company who could decide the order.
 */
final class CartApprovalNoticeProvider
{
    public function __construct(
        #[Autowire(service: 'tmonier_purchase_approval.requirement_checker')]
        private readonly ApprovalRequirementChecker $requirements,
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private readonly CompanyMemberRepositoryInterface $members,
        private readonly ApprovalPolicy $policy,
        #[Autowire(service: 'tmonier_purchase_approval.repository.member_limit')]
        private readonly MemberLimitRepositoryInterface $limits,
    ) {
    }

    /** Null when the order needs no approval (no limit, under the limit, not a company order, empty cart). */
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

        return new CartApprovalNotice(
            $requirement->orderLimit,
            $order->getTotal(),
            (string) $order->getCurrencyCode(),
            $company?->getName(),
            $company instanceof CompanyInterface ? $this->approverName($company, $requirement->member, $order) : null,
        );
    }

    private function approverName(CompanyInterface $company, CompanyMemberInterface $buyer, OrderInterface $order): ?string
    {
        foreach ($this->members->findByCompany($company) as $member) {
            if ($this->canDecide($member, $buyer, $order)) {
                return $member->getCustomer()?->getFirstName();
            }
        }

        return null;
    }

    private function canDecide(CompanyMemberInterface $member, CompanyMemberInterface $buyer, OrderInterface $order): bool
    {
        if ($member->getId() === $buyer->getId() || !$this->policy->isApprover($member)) {
            return false;
        }
        $approvalLimit = $this->limits->findOneByMember($member)?->getApprovalLimit();

        return null === $approvalLimit || $order->getTotal() <= $approvalLimit;
    }
}
