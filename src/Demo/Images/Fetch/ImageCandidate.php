<?php

declare(strict_types=1);

namespace App\Demo\Images\Fetch;

final readonly class ImageCandidate
{
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
