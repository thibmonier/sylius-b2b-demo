<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

/** A real conversation between an assistant and the demo (US-103): 4 to 6 exchanges, dated, with an optional recording. */
final readonly class Transcript
{
    /** @param list<TranscriptExchange> $exchanges */
    public function __construct(
        public string $recordedAt,
        public string $locale,
        public string $assistant,
        public array $exchanges,
        public ?TranscriptMedia $media = null,
    ) {
    }
}
