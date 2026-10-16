<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Authenticates the cart that comes back to the simulator (US-040, M8). The browser carries it from the shop to the
 * simulator, so the form field proves nothing by itself: the visitor could write any cart. The one thing a visitor
 * cannot forge is the journal of the shop: the PunchOut plugin writes the SHA-256 of every PunchOutOrderMessage it sends
 * (tmonier_punchout_message_log). A return is authentic when its bytes hash to such an entry, and it is used once.
 */
final readonly class ReturnAuthenticator
{
    public const AUTHENTIC = 'authentic';

    public const FORGED = 'forged';

    public const EXPIRED = 'expired';

    public const REPLAYED = 'replayed';

    public function __construct(
        private Connection $connection,
        #[Autowire(service: 'cache.app')]
        private CacheItemPoolInterface $cache,
        private ClockInterface $clock,
        // The journal hashes at most this many bytes of a message.
        #[Autowire(param: 'tmonier_punchout.max_request_bytes')]
        private int $maxBytes,
    ) {
    }

    /** @return self::AUTHENTIC|self::FORGED|self::EXPIRED|self::REPLAYED */
    public function claim(string $xml): string
    {
        if ('' === $xml || \strlen($xml) > $this->maxBytes) {
            return self::FORGED;
        }

        $sentAt = $this->sentAt(hash('sha256', $xml));
        if (null === $sentAt) {
            return self::FORGED;
        }
        if ($sentAt->getTimestamp() < $this->clock->now()->getTimestamp() - SignedState::LIFETIME) {
            return self::EXPIRED;
        }

        $item = $this->cache->getItem('demo_punchout_return_' . hash('sha256', $xml));
        if ($item->isHit()) {
            return self::REPLAYED;
        }
        $this->cache->save($item->set(true)->expiresAfter(SignedState::LIFETIME));

        return self::AUTHENTIC;
    }

    private function sentAt(string $hash): ?\DateTimeImmutable
    {
        $createdAt = $this->connection->fetchOne(
            "SELECT MAX(created_at) FROM tmonier_punchout_message_log WHERE direction = 'out' AND message_type = 'PunchOutOrderMessage' AND status = 200 AND body_hash = ?",
            [$hash],
        );

        return \is_string($createdAt) ? new \DateTimeImmutable($createdAt) : null;
    }
}
