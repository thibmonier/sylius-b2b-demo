<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * The private gate of the demo (US-032): HTTP basic authentication whose single account comes from the
 * environment (user + password hash). Active in the demo mode unless DEMO_BASIC_AUTH=0.
 */
final readonly class BasicAuthGate
{
    public function __construct(
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(param: 'demo.basic_auth.enabled')]
        private bool $enabled,
        #[Autowire(param: 'demo.basic_auth.user')]
        private string $user,
        #[Autowire(param: 'demo.basic_auth.password_hash')]
        #[\SensitiveParameter]
        private string $passwordHash,
    ) {
    }

    public function isActive(): bool
    {
        return $this->demoMode && $this->enabled;
    }

    /** True when the gate is on but cannot let anybody in: it must then refuse to serve (fail closed). */
    public function isMisconfigured(): bool
    {
        return $this->isActive() && ('' === $this->user || '' === $this->passwordHash || null === password_get_info($this->passwordHash)['algo']);
    }

    public function hasCredentials(Request $request): bool
    {
        return null !== $request->getUser();
    }

    public function authorizes(Request $request): bool
    {
        $user = $request->getUser();
        $password = $request->getPassword();
        if (null === $user || null === $password || $this->isMisconfigured()) {
            return false;
        }

        // Both checks always run: the answer time does not tell whether the user name was right.
        $userMatches = hash_equals($this->user, $user);
        $passwordMatches = password_verify($password, $this->passwordHash);

        return $userMatches && $passwordMatches;
    }
}
