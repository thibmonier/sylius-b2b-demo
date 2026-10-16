<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** Status (and StartPage URL for a setup response) answered by the shop endpoint. */
final readonly class CxmlResponse
{
    public function __construct(
        public int $code,
        public string $text,
        public string $message,
        public ?string $startUrl,
    ) {
    }

    public function isOk(): bool
    {
        return 200 === $this->code;
    }

    /** The one-time token of the start URL; null when the URL is not a plugin start page. */
    public function startToken(): ?string
    {
        if (null === $this->startUrl) {
            return null;
        }
        $path = parse_url($this->startUrl, \PHP_URL_PATH);

        return is_string($path) && 1 === preg_match('#^/punchout/start/([A-Za-z0-9_-]{43,})$#', $path, $matches) ? $matches[1] : null;
    }
}
