<?php

declare(strict_types=1);

namespace App\Demo\Mail;

use App\Demo\Account\DemoAccountCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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
        /** @var list<string> */
        #[Autowire(param: 'demo.public_mail_subjects')]
        private array $subjectPatterns,
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

    /**
     * Allow-list (US-080): a mail is public only when every recipient is a shop account of the
     * fixtures AND its subject is a listed type. Anything else, administrators included, stays hidden.
     * The sensitive filter remains a second barrier.
     */
    private function isPublic(MailpitMessage $message): bool
    {
        return $this->isListedType($message) && $this->sentToShopAccountsOnly($message) && !$this->filter->isSensitive($message);
    }

    private function isListedType(MailpitMessage $message): bool
    {
        foreach ($this->subjectPatterns as $pattern) {
            if (1 === preg_match($pattern, $message->subject)) {
                return true;
            }
        }

        return false;
    }

    private function sentToShopAccountsOnly(MailpitMessage $message): bool
    {
        preg_match_all('/[^\s<>,;"\']+@[^\s<>,;"\']+/', $message->to . ' ' . $message->copies, $matches);
        if ([] === $matches[0]) {
            return false;
        }
        $demo = array_map(
            static fn ($account): string => mb_strtolower($account->email),
            $this->accounts->shopAccounts(),
        );

        return [] === array_diff(array_map(mb_strtolower(...), $matches[0]), $demo);
    }
}
