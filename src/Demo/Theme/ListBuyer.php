<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Customer\Context\CustomerContextInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;

/**
 * What the notice of the product list says about the signed-in buyer (US-198): the company whose prices apply and the order limit above
 * which an order waits for an approval. Both come from the data (company of the member, limit of the purchase-approval plugin); a buyer
 * without company or without limit gets no such sentence.
 */
final readonly class ListBuyer
{
    public function __construct(
        private CustomerContextInterface $customerContext,
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private CompanyMemberRepositoryInterface $members,
        #[Autowire(service: 'tmonier_purchase_approval.repository.member_limit')]
        private MemberLimitRepositoryInterface $limits,
    ) {
    }

    /** @return array{company: ?string, orderLimit: ?int} */
    public function current(): array
    {
        $customer = $this->customerContext->getCustomer();
        $member = null === $customer ? null : $this->members->findOneByCustomer($customer);
        if (null === $member) {
            return ['company' => $customer?->getGroup()?->getName(), 'orderLimit' => null];
        }

        return ['company' => $member->getCompany()?->getName(), 'orderLimit' => $this->limits->findOneByMember($member)?->getOrderLimit()];
    }
}
