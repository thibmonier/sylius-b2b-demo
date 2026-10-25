<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalPolicy;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;

/**
 * The first member of a company who could decide an order of a buyer for a given total: an approver (the policy of the
 * purchase-approval plugin) other than the buyer, whose own approval limit covers the total. Read only. The cart
 * (CartApprovalNoticeProvider) applies the same rule to an order that will wait; here it is an order that waits.
 */
final class ApproverFinder
{
    public function __construct(
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private readonly CompanyMemberRepositoryInterface $members,
        private readonly ApprovalPolicy $policy,
        #[Autowire(service: 'tmonier_purchase_approval.repository.member_limit')]
        private readonly MemberLimitRepositoryInterface $limits,
    ) {
    }

    public function firstNameFor(CompanyInterface $company, CompanyMemberInterface $buyer, int $total): ?string
    {
        foreach ($this->members->findByCompany($company) as $member) {
            if ($this->canDecide($member, $buyer, $total)) {
                return $member->getCustomer()?->getFirstName();
            }
        }

        return null;
    }

    private function canDecide(CompanyMemberInterface $member, CompanyMemberInterface $buyer, int $total): bool
    {
        if ($member->getId() === $buyer->getId() || !$this->policy->isApprover($member)) {
            return false;
        }
        $approvalLimit = $this->limits->findOneByMember($member)?->getApprovalLimit();

        return null === $approvalLimit || $total <= $approvalLimit;
    }
}
