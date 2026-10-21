<?php

declare(strict_types=1);

namespace App\Demo\Journey;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Where the visitor stands in each guided journey (US-105), kept in the session. Reading never writes (US-160): the
 * progress moves only through {@see advance()}, called by an explicit POST. The fingerprint of the last demo reset seen is
 * stored with it: when it changes, the progress is dropped and the pages announce it until the visitor acts.
 */
final class JourneyProgress
{
    private const KEY = 'demo_journeys';

    public function __construct(private readonly RequestStack $requests)
    {
    }

    /** The step last reached in the journey, 1 when none. */
    public function step(string $journey): int
    {
        $step = $this->read()['steps'][$journey] ?? 1;

        return $step > 0 ? $step : 1;
    }

    public function hasStarted(string $journey): bool
    {
        return isset($this->read()['steps'][$journey]);
    }

    /**
     * True when a demo reset happened since the visitor made progress. Pure read, safe on a GET: nothing is written, so
     * the notice stays until the visitor acts ({@see advance()}) and the old progress is ignored by the callers meanwhile.
     */
    public function hasBeenReset(string $fingerprint): bool
    {
        $state = $this->read();
        if (($state['reset'] ?? null) === $fingerprint) {
            return false;
        }

        return [] !== ($state['steps'] ?? []);
    }

    /**
     * The explicit action of the visitor (a POST): records the step reached. A reset seen meanwhile is acknowledged here,
     * the progress of before is dropped and the fingerprint stored.
     */
    public function advance(string $journey, int $step, string $fingerprint): void
    {
        $state = $this->read();
        if (($state['reset'] ?? null) !== $fingerprint) {
            $state['steps'] = [];
        }
        $state['reset'] = $fingerprint;
        $state['steps'][$journey] = max(1, $step);
        $this->write($state);
    }

    /** @return array{steps?: array<string, int>, reset?: string} */
    private function read(): array
    {
        $state = $this->readableSession()?->get(self::KEY, []);

        return \is_array($state) ? self::shape($state) : [];
    }

    /**
     * @param array<array-key, mixed> $state
     *
     * @return array{steps?: array<string, int>, reset?: string}
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

        return $shape;
    }

    /** @param array<string, mixed> $state */
    private function write(array $state): void
    {
        $this->session()?->set(self::KEY, $state);
    }

    /** Reading must not open a session: a visitor without one has no progress, and a GET leaves no cookie behind. */
    private function readableSession(): ?SessionInterface
    {
        $session = $this->session();
        if (null === $session || $session->isStarted()) {
            return $session;
        }

        return $this->requests->getCurrentRequest()?->hasPreviousSession() ? $session : null;
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
