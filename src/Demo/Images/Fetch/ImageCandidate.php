<?php

declare(strict_types=1);

namespace App\Demo\Images\Fetch;

final readonly class ImageCandidate
{
    /** The id names the stored file: only a plain UUID is accepted (no path, never an empty name). */
    public const ID_PATTERN = '/^[0-9a-f-]{36}$/i';

    public function __construct(
        public string $id,
        public string $title,
        public string $creator,
        public string $license,
        public string $licenseUrl,
        public string $url,
        public string $originUrl,
        public string $source,
    ) {
    }

    /** The file name is derived from the Openverse id: the same image is never stored twice. */
    public function fileName(): string
    {
        return \substr(\str_replace('-', '', $this->id), 0, 12) . '.webp';
    }
}
