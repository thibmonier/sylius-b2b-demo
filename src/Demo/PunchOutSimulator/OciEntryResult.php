<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

use Symfony\Component\HttpFoundation\Cookie;

/** The answer of the shop to an OCI entry: its status, the page it sends the visitor to and the cookies of the session it opened. */
final readonly class OciEntryResult
{
    /** @param list<Cookie> $cookies */
    public function __construct(
        public int $status,
        public ?string $location,
        public array $cookies,
    ) {
    }

    /** The entry opened a session: a redirect to a page of the shop (a path, never another host). */
    public function isSession(): bool
    {
        return 303 === $this->status && null !== $this->location && 1 === preg_match('#^/(?!/)[^\\\\\s]*$#', $this->location);
    }
}
