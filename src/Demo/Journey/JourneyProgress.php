<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Where the visitor stands in each guided journey (US-105), kept in the session. The fingerprint of the last demo reset
 * seen is stored with it: when it changes, the progress is dropped and the next page announces it once.
 */
final class JourneyProgress
{
    private const KEY = 'demo_journeys';

    public function __construct(private readonly RequestStack $requests)
    {
    }

    /** The step last visited in the journey, 1 when none. */
    public function step(string $journey): int
    {
        $step = $this->read()['steps'][$journey] ?? 1;

        return $step > 0 ? $step : 1;
    }

    public function hasStarted(string $journey): bool
    {
        return isset($this->read()['steps'][$journey]);
    }

    public function moveTo(string $journey, int $step): void
    {
        $state = $this->read();
        $state['steps'][$journey] = max(1, $step);
        $this->write($state);
    }

    /**
     * Compares the fingerprint of the last reset with the one stored. Returns true when a reset happened since the
     * visitor made progress (the progress is then dropped and a notice is pending).
     */
    public function syncWithReset(string $fingerprint): bool
    {
        $state = $this->read();
        $known = $state['reset'] ?? null;
        if ($known === $fingerprint) {
            return false;
        }
        $hadProgress = [] !== ($state['steps'] ?? []);
        if (null === $known && !$hadProgress) {
            $state['reset'] = $fingerprint;
            $this->write($state);

            return false;
        }
        $state['reset'] = $fingerprint;
        $state['steps'] = [];
        $state['notice'] = $hadProgress;
        $this->write($state);

        return $hadProgress;
    }

    /** True once after a reset dropped the progress. */
    public function consumeResetNotice(): bool
    {
        $state = $this->read();
        if (true !== ($state['notice'] ?? false)) {
            return false;
        }
        $state['notice'] = false;
        $this->write($state);

        return true;
    }

    /** @return array{steps?: array<string, int>, reset?: string, notice?: bool} */
    private function read(): array
    {
        $state = $this->session()?->get(self::KEY, []);

        return \is_array($state) ? self::shape($state) : [];
    }

    /**
     * @param array<array-key, mixed> $state
     *
     * @return array{steps?: array<string, int>, reset?: string, notice?: bool}
     */
    private static function shape(array $state): array
    {
        $shape = [];
        $steps = $state['steps'] ?? [];
        foreach (\is_array($steps) ? $steps : [] as $journey => $step) {
            if (\is_int($step)) {
                $shape['steps'][(string) $journey] = $step;
            }
        }
        if (\is_string($state['reset'] ?? null)) {
            $shape['reset'] = $state['reset'];
        }
        if (true === ($state['notice'] ?? null)) {
            $shape['notice'] = true;
        }

        return $shape;
    }

    /** @param array<string, mixed> $state */
    private function write(array $state): void
    {
        $this->session()?->set(self::KEY, $state);
    }

    private function session(): ?SessionInterface
    {
        try {
            return $this->requests->getSession();
        } catch (SessionNotFoundException) {
            return null;
        }
    }
}
