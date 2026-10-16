<?php

declare(strict_types=1);

namespace App\Demo\Mail;

/** The public, read-only view of the Mailpit inbox: sensitive mails are neither listed nor readable. */
final readonly class DemoMailbox
{
    private const LIMIT = 50;

    public function __construct(
        private MailpitClient $client,
        private SensitiveMailFilter $filter,
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
            fn (MailpitMessage $message): bool => !$this->filter->isSensitive($message),
        ));
    }

    /**
     * @throws MailpitUnavailableException
     */
    public function find(string $id): ?MailpitMessage
    {
        $message = $this->client->find($id);

        return null === $message || $this->filter->isSensitive($message) ? null : $message;
    }
}
