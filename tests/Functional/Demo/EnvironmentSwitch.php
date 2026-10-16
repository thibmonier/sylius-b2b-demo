<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\After;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sets environment variables for the duration of a test (the kernel reads them when it boots) and puts
 * back the previous values, including the static trusted hosts / proxies of the HTTP foundation.
 */
trait EnvironmentSwitch
{
    /** @var array<string, string|null> */
    private array $previousEnvironment = [];

    /** @param array<string, string> $variables */
    private function setEnvironment(array $variables): void
    {
        foreach ($variables as $name => $value) {
            if (!\array_key_exists($name, $this->previousEnvironment)) {
                $previous = $_SERVER[$name] ?? $_ENV[$name] ?? (false === getenv($name) ? null : getenv($name));
                $this->previousEnvironment[$name] = \is_string($previous) ? $previous : null;
            }
            $_SERVER[$name] = $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }

    #[After]
    protected function restoreEnvironment(): void
    {
        foreach ($this->previousEnvironment as $name => $value) {
            if (null === $value) {
                unset($_SERVER[$name], $_ENV[$name]);
                putenv($name);

                continue;
            }
            $_SERVER[$name] = $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
        $this->previousEnvironment = [];
        Request::setTrustedHosts([]);
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);
    }

    /** A documentation-range IP unique to this test run: the limiter counters live in a persistent pool. */
    private static function visitorIp(): string
    {
        return \sprintf('203.0.%d.%d', random_int(1, 254), random_int(1, 254));
    }

    private static function gateHash(string $password): string
    {
        return password_hash($password, \PASSWORD_DEFAULT);
    }
}
