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
    public const MIN_SECRET_LENGTH = 32;

    /** Host patterns that accept any Host header. */
    private const OPEN_HOSTS = ['', '^.*$', '.*', '^.*', '.*$', '^(.*)$', '(.*)'];

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
        #[Autowire(env: 'APP_SECRET')]
        #[\SensitiveParameter]
        private string $appSecret,
        #[Autowire(env: 'TRUSTED_HOSTS')]
        private string $trustedHosts,
        #[Autowire(param: 'demo.registration_open')]
        private bool $registrationOpen = false,
        #[Autowire(param: 'demo.punchout.public_endpoint')]
        private bool $punchOutPublicEndpoint = false,
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
        if (\strlen($this->appSecret) < self::MIN_SECRET_LENGTH) {
            $violations[] = 'APP_SECRET must be at least 32 characters long in the demo mode.';
        }
        if (\in_array(trim($this->trustedHosts), self::OPEN_HOSTS, true)) {
            $violations[] = 'TRUSTED_HOSTS must name the public host (a pattern accepting any host is refused in the demo mode).';
        }
        if (!$this->mailerIsInternal()) {
            $violations[] = 'MAILER_DSN must be the internal Mailpit (smtp://<allowed host>) or a null transport in the demo mode.';
        }

        return $violations;
    }

    /**
     * US-050: what must not be true once the private gate is off. Checked by `app:demo:preflight` only (the container
     * refuses to start), never per request: a local run or the test suite legitimately runs without the gate.
     *
     * @return list<string>
     */
    public function publicModeViolations(): array
    {
        if (!$this->demoMode || $this->gate->isActive()) {
            return [];
        }
        $violations = [];
        if ($this->registrationOpen) {
            $violations[] = 'The private gate is off but DEMO_REGISTRATION_OPEN is not 0: a visitor could enter personal data (registration, guest checkout, guest quote).';
        }
        if ($this->punchOutPublicEndpoint) {
            $violations[] = 'The private gate is off but DEMO_PUNCHOUT_PUBLIC_ENDPOINT is 1: the PunchOut endpoint would answer to any client.';
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
