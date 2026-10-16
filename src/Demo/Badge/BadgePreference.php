<?php

declare(strict_types=1);

namespace App\Demo\Badge;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Whether the "powered by <plugin>" badges are shown (US-016/US-017): demo mode only, shown by
 * default, hidden once the visitor switched them off (cookie, no JavaScript needed).
 */
final readonly class BadgePreference
{
    public const COOKIE = 'demo_badges';

    public const HIDDEN = 'off';

    public function __construct(
        private RequestStack $requests,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
    ) {
    }

    public function visible(): bool
    {
        if (!$this->demoMode) {
            return false;
        }

        return self::HIDDEN !== $this->requests->getCurrentRequest()?->cookies->get(self::COOKIE);
    }
}
