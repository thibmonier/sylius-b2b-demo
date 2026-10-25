<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Sylius\Component\Core\Model\CustomerInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusTradeCreditPlugin\Statement\Statement;

/** Who looks at the page and what is read once for all the blocks of the page (member, company, statement of the account on credit). */
final readonly class AccountContext
{
    public function __construct(
        public CustomerInterface $customer,
        public string $locale,
        public ?CompanyMemberInterface $member,
        public ?Statement $statement,
    ) {
    }

    public function company(): ?CompanyInterface
    {
        return $this->member?->getCompany();
    }
}
