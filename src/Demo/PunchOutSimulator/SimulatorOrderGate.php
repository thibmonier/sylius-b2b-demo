<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;

/**
 * Abuse guard of the public simulator (US-036, S5-R1): a signed session can be turned into an order once
 * (replays are refused) and the simulator creates at most a daily number of anonymous Acme orders.
 */
final readonly class SimulatorOrderGate
{
    public const CLAIMED = 'claimed';

    public const REPLAYED = 'replayed';

    public const CAP_REACHED = 'cap_reached';

    private const LOCK = 'demo_punchout_order_gate';

    public function __construct(
        #[Autowire(service: 'cache.app')]
        private CacheItemPoolInterface $cache,
        private ClockInterface $clock,
        #[Autowire(param: 'demo.punchout.daily_orders')]
        private int $dailyLimit,
        private LockFactory $locks,
    ) {
    }

    /**
     * Read and write under one lock: parallel posts of the same session would all see "not claimed" and the count
     * would lose updates (sprint 8 review, N1). The demo runs one instance, the flock store serialises its threads.
     *
     * @return self::CLAIMED|self::REPLAYED|self::CAP_REACHED
     */
    public function claim(string $nonce): string
    {
        $lock = $this->locks->createLock(self::LOCK);
        $lock->acquire(true);

        try {
            return $this->claimLocked($nonce);
        } finally {
            $lock->release();
        }
    }

    /** The order was not created: the session may be used again and the day's count is given back. */
    public function release(string $nonce): void
    {
        $lock = $this->locks->createLock(self::LOCK);
        $lock->acquire(true);

        try {
            $this->releaseLocked($nonce);
        } finally {
            $lock->release();
        }
    }

    /** @return self::CLAIMED|self::REPLAYED|self::CAP_REACHED */
    private function claimLocked(string $nonce): string
    {
        $item = $this->cache->getItem($this->nonceKey($nonce));
        if ($item->isHit()) {
            return self::REPLAYED;
        }
        $counter = $this->cache->getItem($this->counterKey());
        $count = $counter->isHit() && \is_int($counter->get()) ? $counter->get() : 0;
        if ($count >= $this->dailyLimit) {
            return self::CAP_REACHED;
        }

        $this->cache->save($item->set(true)->expiresAfter(SignedState::LIFETIME * 2));
        $this->cache->save($counter->set($count + 1)->expiresAfter(172800));

        return self::CLAIMED;
    }

    private function releaseLocked(string $nonce): void
    {
        $this->cache->deleteItem($this->nonceKey($nonce));
        $counter = $this->cache->getItem($this->counterKey());
        if ($counter->isHit() && \is_int($counter->get()) && $counter->get() > 0) {
            $this->cache->save($counter->set($counter->get() - 1)->expiresAfter(172800));
        }
    }

    private function nonceKey(string $nonce): string
    {
        return 'demo_punchout_nonce_' . hash('sha256', $nonce);
    }

    private function counterKey(): string
    {
        return 'demo_punchout_orders_' . $this->clock->now()->format('Ymd');
    }
}
