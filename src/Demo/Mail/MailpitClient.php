<?php

declare(strict_types=1);

namespace App\Demo\Mail;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Read-only client of the Mailpit HTTP API, called server-side only (US-028).
 * It issues GET requests exclusively: no send, relay or delete call exists in this class.
 */
final readonly class MailpitClient
{
    private const ID_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';

    private HttpClientInterface $http;

    public function __construct(
        HttpClientInterface $http,
        #[Autowire(param: 'demo.mailpit_api_url')]
        private string $apiUrl,
    ) {
        // Short timeouts, no redirect: a slow or hijacked Mailpit must not hang or pivot the page.
        $this->http = $http->withOptions(['timeout' => 2.0, 'max_duration' => 4.0, 'max_redirects' => 0]);
    }

    public function isConfigured(): bool
    {
        return '' !== trim($this->apiUrl);
    }

    /**
     * Ids of the latest mails, newest first.
     *
     * @return list<string>
     *
     * @throws MailpitUnavailableException
     */
    public function latestIds(int $limit): array
    {
        $data = $this->get('/api/v1/messages?limit=' . max(1, $limit));
        $ids = [];
        foreach (\is_array($data['messages'] ?? null) ? $data['messages'] : [] as $summary) {
            if (\is_array($summary) && \is_string($summary['ID'] ?? null) && 1 === preg_match(self::ID_PATTERN, $summary['ID'])) {
                $ids[] = $summary['ID'];
            }
        }

        return $ids;
    }

    /**
     * @throws MailpitUnavailableException
     */
    public function find(string $id): ?MailpitMessage
    {
        return $this->findMany([$id])[0] ?? null;
    }

    /**
     * The messages that exist, in the order of the ids. Every request is sent before any response is read, so the
     * calls run concurrently and the whole batch is bounded by about one max_duration.
     *
     * @param list<string> $ids
     *
     * @return list<MailpitMessage>
     *
     * @throws MailpitUnavailableException
     */
    public function findMany(array $ids): array
    {
        $responses = [];
        foreach ($ids as $id) {
            if (1 === preg_match(self::ID_PATTERN, $id)) {
                $responses[$id] = $this->send('/api/v1/message/' . $id);
            }
        }

        $messages = [];
        foreach ($responses as $id => $response) {
            $data = $this->decode($response, true);
            if (null !== $data) {
                $messages[] = self::message((string) $id, $data);
            }
        }

        return $messages;
    }

    /** @param array<string, mixed> $data */
    private static function message(string $id, array $data): MailpitMessage
    {
        return new MailpitMessage(
            $id,
            self::string($data['Subject'] ?? null),
            self::address($data['From'] ?? null),
            self::addresses($data['To'] ?? null),
            self::date($data['Date'] ?? null),
            self::string($data['Text'] ?? null),
            self::string($data['HTML'] ?? null),
            trim(self::addresses($data['Cc'] ?? null) . ', ' . self::addresses($data['Bcc'] ?? null), ', '),
        );
    }

    /**
     * @return ($notFoundIsNull is true ? array<string, mixed>|null : array<string, mixed>)
     */
    private function get(string $path, bool $notFoundIsNull = false): ?array
    {
        return $this->decode($this->send($path), $notFoundIsNull);
    }

    /** @throws MailpitUnavailableException */
    private function send(string $path): ResponseInterface
    {
        if (!$this->isConfigured()) {
            throw new MailpitUnavailableException('Mailpit API URL is not configured.');
        }

        try {
            return $this->http->request('GET', rtrim($this->apiUrl, '/') . $path);
        } catch (ExceptionInterface $e) {
            throw new MailpitUnavailableException('Mailpit is unavailable.', 0, $e);
        }
    }

    /**
     * @return ($notFoundIsNull is true ? array<string, mixed>|null : array<string, mixed>)
     *
     * @throws MailpitUnavailableException
     */
    private function decode(ResponseInterface $response, bool $notFoundIsNull): ?array
    {
        try {
            if ($notFoundIsNull && 404 === $response->getStatusCode()) {
                return null;
            }
            $data = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new MailpitUnavailableException('Mailpit is unavailable.', 0, $e);
        }

        return $data;
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }

    private static function address(mixed $value): string
    {
        if (!\is_array($value)) {
            return '';
        }
        $name = self::string($value['Name'] ?? null);
        $address = self::string($value['Address'] ?? null);

        return '' !== $name ? \sprintf('%s <%s>', $name, $address) : $address;
    }

    private static function addresses(mixed $value): string
    {
        return implode(', ', array_map(self::address(...), \is_array($value) ? $value : []));
    }

    private static function date(mixed $value): ?\DateTimeImmutable
    {
        try {
            return \is_string($value) ? new \DateTimeImmutable($value) : null;
        } catch (\Exception) {
            return null;
        }
    }
}
