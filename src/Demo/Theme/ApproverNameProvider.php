<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalPolicy;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;

/**
 * First name of the first approver of a company who may decide an order of a buyer: not the buyer, an approver by role,
 * and an approval limit that covers the amount (the amount the plugin compares: see `amount_basis`). One calculation for the
 * cart notice and for the account page.
 */
final class ApproverNameProvider
{
    public function __construct(
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private readonly CompanyMemberRepositoryInterface $members,
        private readonly ApprovalPolicy $policy,
        #[Autowire(service: 'tmonier_purchase_approval.repository.member_limit')]
        private readonly MemberLimitRepositoryInterface $limits,
    ) {
    }

    public function firstNameFor(CompanyInterface $company, CompanyMemberInterface $buyer, int $amount): ?string
    {
        foreach ($this->members->findByCompany($company) as $member) {
            if ($this->canDecide($member, $buyer, $amount)) {
                return $member->getCustomer()?->getFirstName();
            }
        }

        return null;
    }

    private function canDecide(CompanyMemberInterface $member, CompanyMemberInterface $buyer, int $amount): bool
    {
        if ($member->getId() === $buyer->getId() || !$this->policy->isApprover($member)) {
            return false;
        }
        $approvalLimit = $this->limits->findOneByMember($member)?->getApprovalLimit();

        return null === $approvalLimit || $amount <= $approvalLimit;
    }
}
