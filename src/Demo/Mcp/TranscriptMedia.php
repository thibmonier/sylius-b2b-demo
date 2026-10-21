<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

/** The recording of the conversation: a video, or an animation (always with a still poster) and its text alternative. */
final readonly class TranscriptMedia
{
    public const VIDEO = 'video';

    public const GIF = 'gif';

    public function __construct(
        public string $kind,
        public string $src,
        public string $description,
        public ?string $poster = null,
    ) {
    }
}
