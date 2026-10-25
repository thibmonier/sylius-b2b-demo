<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use App\Wishlist\CompanyWishlistRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusQuoteRequestPlugin\Repository\QuoteRequestRepositoryInterface;
use Tmonier\SyliusQuoteRequestPlugin\Workflow\QuoteRequestStatuses;

/** The two sentences under "Lists and quotes": the first shared list of the company, the latest quote still running. */
final class AccountListsAndQuotesBuilder
{
    public function __construct(
        private readonly CompanyWishlistRepository $lists,
        #[Autowire(service: 'tmonier_quote_request.repository.quote_request')]
        private readonly QuoteRequestRepositoryInterface $quotes,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function lists(AccountContext $context): string
    {
        $company = $context->company();
        $first = null === $company ? null : ($this->lists->findByCompany($company)[0] ?? null);
        if (null === $first) {
            return $this->t($context, 'lists.none');
        }
        $by = $first->getCreatedBy()?->getId() === $context->customer->getId() ? 'you' : 'colleague';

        return $this->t($context, 'lists.first.' . $by, ['%name%' => $first->getName(), '%count%' => $first->getWishlist()->getWishlistProducts()->count()]);
    }

    public function quotes(AccountContext $context): string
    {
        $quotes = $this->quotes->findByCustomer($context->customer);
        if ([] === $quotes) {
            return $this->t($context, 'quotes.never');
        }
        foreach ($quotes as $quote) {
            if (\in_array($quote->getStatus(), [QuoteRequestStatuses::NEW, QuoteRequestStatuses::IN_REVIEW, QuoteRequestStatuses::PROPOSED, QuoteRequestStatuses::ACCEPTED], true)) {
                $status = $this->translator->trans('tmonier_quote_request.status.' . $quote->getStatus(), [], 'messages', $context->locale);

                return $this->t($context, 'quotes.running', ['%number%' => (string) $quote->getNumber(), '%status%' => mb_strtolower($status)]);
            }
        }

        return $this->t($context, 'quotes.none_running');
    }

    /** @param array<string, scalar> $parameters */
    private function t(AccountContext $context, string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters, 'account', $context->locale);
    }
}
