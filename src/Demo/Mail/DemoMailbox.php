<?php

declare(strict_types=1);

namespace App\Demo\Mail;

use App\Demo\Account\DemoAccountCatalog;

/**
 * The public, read-only view of the Mailpit inbox: sensitive mails are neither listed nor readable, and
 * only the mails sent to the demo accounts are shown (an address typed by a visitor is never displayed).
 */
final readonly class DemoMailbox
{
    private const LIMIT = 50;

    public function __construct(
        private MailpitClient $client,
        private SensitiveMailFilter $filter,
        private DemoAccountCatalog $accounts,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * @return list<MailpitMessage>
     *
     * @throws MailpitUnavailableException
     */
    public function list(): array
    {
        return array_values(array_filter(
            $this->client->findMany($this->client->latestIds(self::LIMIT)),
            fn (MailpitMessage $message): bool => $this->isPublic($message),
        ));
    }

    /**
     * @throws MailpitUnavailableException
     */
    public function find(string $id): ?MailpitMessage
    {
        $message = $this->client->find($id);

        return null === $message || !$this->isPublic($message) ? null : $message;
    }

    private function isPublic(MailpitMessage $message): bool
    {
        return $this->sentToDemoAccountsOnly($message) && !$this->filter->isSensitive($message);
    }

    private function sentToDemoAccountsOnly(MailpitMessage $message): bool
    {
        preg_match_all('/[^\s<>,;"\']+@[^\s<>,;"\']+/', $message->to, $matches);
        if ([] === $matches[0]) {
            return false;
        }
        $demo = array_map(
            static fn ($account): string => mb_strtolower($account->email),
            [...$this->accounts->shopAccounts(), ...$this->accounts->adminUsers()],
        );

        return [] === array_diff(array_map(mb_strtolower(...), $matches[0]), $demo);
    }
}
