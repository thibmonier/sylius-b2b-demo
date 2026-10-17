<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;

/**
 * An OCI return is used once. The signed state carries a nonce; the first return that brings it marks it, any other one
 * is a replay. No daily cap: an OCI return creates no order, it only shows the cart.
 */
final readonly class OciReturnGate
{
    public function __construct(
        #[Autowire(service: 'cache.app')]
        private CacheItemPoolInterface $cache,
        private LockFactory $locks,
    ) {
    }

    /** @return bool true for the first return of this nonce */
    public function claim(string $nonce): bool
    {
        $key = 'demo_punchout_oci_return_' . hash('sha256', $nonce);
        // Check and mark under a lock: parallel posts of the same return would all pass otherwise.
        $lock = $this->locks->createLock($key);
        $lock->acquire(true);

        try {
            $item = $this->cache->getItem($key);
            if ($item->isHit()) {
                return false;
            }
            $this->cache->save($item->set(true)->expiresAfter(SignedState::LIFETIME));

            return true;
        } finally {
            $lock->release();
        }
    }
}
