<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyMemberInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusCompanyInsightsPlugin\Indicators\DashboardProvider;
use Tmonier\SyliusCompanyInsightsPlugin\Period\PeriodKey;
use Tmonier\SyliusCompanyInsightsPlugin\Security\InsightsPermissions;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\ApprovalPolicy;
use Tmonier\SyliusPurchaseApprovalPlugin\Repository\MemberLimitRepositoryInterface;
use Tmonier\SyliusTradeCreditPlugin\Statement\Statement;

/**
 * The column beside the orders, from the data the plugins already hold: the company with the figure that matters for the role
 * (order limit of a buyer, approval limit of an approver, twelve months of spending for an administrator who may read it), and
 * the position of the account on credit with what is overdue.
 */
final class AccountCompanyPanelBuilder
{
    public function __construct(
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private readonly CompanyMemberRepositoryInterface $members,
        #[Autowire(service: 'tmonier_purchase_approval.repository.member_limit')]
        private readonly MemberLimitRepositoryInterface $limits,
        private readonly ApprovalPolicy $policy,
        #[Autowire(service: 'tmonier_company_insights.dashboard_provider')]
        private readonly DashboardProvider $insights,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly AccountFormatter $formatter,
        private readonly UrlGeneratorInterface $urls,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** @return list<AccountFact> */
    public function facts(AccountContext $context, string $currency): array
    {
        $member = $context->member;
        $company = $context->company();
        if (!$member instanceof CompanyMemberInterface || !$company instanceof CompanyInterface) {
            return [];
        }
        $facts = [
            new AccountFact($this->t($context, 'company.role'), $this->roleLabel($context, $member)),
            new AccountFact($this->t($context, 'company.members'), (string) \count($this->members->findByCompany($company))),
        ];
        $role = $this->roleFact($context, $member, $company, $currency);

        return null === $role ? $facts : [...$facts, $role];
    }

    public function roleLabel(AccountContext $context, CompanyMemberInterface $member): string
    {
        return $this->translator->trans('tmonier_company_accounts.role.' . $member->getRole(), [], 'messages', $context->locale);
    }

    public function credit(AccountContext $context): ?AccountCredit
    {
        $statement = $context->statement;
        if (!$statement instanceof Statement) {
            return null;
        }
        $currency = (string) $statement->account->getCurrencyCode();
        $limit = $statement->position->creditLimit;
        $available = $statement->position->getAvailableAmount();
        $overdue = $statement->getOverdueAmount();
        [$note, $tone] = match (true) {
            $overdue > 0 => [$this->t($context, 'credit.overdue', ['%amount%' => $this->formatter->money($overdue, $currency, $context->locale)]), AccountTone::Late],
            [] === $statement->items => [$this->t($context, 'credit.none_open'), AccountTone::Neutral],
            default => [$this->t($context, 'credit.nothing_overdue'), AccountTone::Neutral],
        };

        return new AccountCredit(
            $this->formatter->money($available, $currency, $context->locale),
            $this->formatter->money($limit, $currency, $context->locale),
            $limit > 0 ? max(0, min(100, (int) round(($limit - $available) / $limit * 100))) : 0,
            $note,
            $tone,
            $this->urls->generate('tmonier_trade_credit_shop_statement', ['_locale' => $context->locale]),
        );
    }

    private function roleFact(AccountContext $context, CompanyMemberInterface $member, CompanyInterface $company, string $currency): ?AccountFact
    {
        if ($this->authorization->isGranted(InsightsPermissions::COMPANY_VIEW_INSIGHTS, $company)) {
            $spend = $this->insights->provide((int) $company->getId(), PeriodKey::Last12Months, $currency)->currencies[0];

            return new AccountFact($this->t($context, 'company.spend'), $this->formatter->money($spend->spend->value, $spend->currencyCode, $context->locale));
        }
        $limit = $this->limits->findOneByMember($member);
        if ($this->policy->isApprover($member)) {
            $value = $limit?->getApprovalLimit();

            return new AccountFact($this->t($context, 'company.approval_limit'), null === $value ? $this->t($context, 'company.no_limit') : $this->limit($context, $value, $currency));
        }
        $value = $limit?->getOrderLimit();

        return null === $value ? null : new AccountFact($this->t($context, 'company.order_limit'), $this->limit($context, $value, $currency));
    }

    /** The limits of the purchase-approval plugin are compared with the total excluding tax (amount basis `net`, enabled by US-202). */
    private function limit(AccountContext $context, int $value, string $currency): string
    {
        return $this->t($context, 'company.excl_vat', ['%amount%' => $this->formatter->money($value, $currency, $context->locale)]);
    }

    /** @param array<string, scalar> $parameters */
    private function t(AccountContext $context, string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters, 'account', $context->locale);
    }
}
