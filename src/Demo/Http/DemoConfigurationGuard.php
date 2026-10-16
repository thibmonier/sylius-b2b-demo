<?php

declare(strict_types=1);

namespace App\Demo\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Transport\Dsn;

/**
 * Fail-closed checks of the public demo: in the demo mode the private gate must have its credentials
 * and the mailer must be the internal Mailpit (or a sink), so that no real e-mail can ever leave. Read
 * by the request gate, the healthcheck and `app:demo:preflight` (container entrypoint).
 */
final readonly class DemoConfigurationGuard
{
    private const SINK_SCHEMES = ['null', 'in-memory'];

    public function __construct(
        private BasicAuthGate $gate,
        #[Autowire(param: 'demo.mode')]
        private bool $demoMode,
        #[Autowire(env: 'MAILER_DSN')]
        #[\SensitiveParameter]
        private string $mailerDsn,
        #[Autowire(param: 'demo.mailer.allowed_hosts')]
        private string $allowedMailerHosts,
    ) {
    }

    /** @return list<string> messages without any secret or host name */
    public function violations(): array
    {
        if (!$this->demoMode) {
            return [];
        }
        $violations = [];
        if ($this->gate->isMisconfigured()) {
            $violations[] = 'The private gate is enabled but DEMO_BASIC_AUTH_USER / DEMO_BASIC_AUTH_PASSWORD_HASH are missing or invalid.';
        }
        if (!$this->mailerIsInternal()) {
            $violations[] = 'MAILER_DSN must be the internal Mailpit (smtp://<allowed host>) or a null transport in the demo mode.';
        }

        return $violations;
    }

    private function mailerIsInternal(): bool
    {
        try {
            $dsn = Dsn::fromString($this->mailerDsn);
        } catch (\Throwable) {
            return false;
        }
        if (\in_array($dsn->getScheme(), self::SINK_SCHEMES, true)) {
            return true;
        }

        return 'smtp' === $dsn->getScheme() && HostAllowList::fromCsv($this->allowedMailerHosts)->allows($dsn->getHost());
    }
}
