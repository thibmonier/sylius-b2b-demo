<?php

declare(strict_types=1);

namespace App\Demo\Media;

/** Where the database says the uploaded images are, relative to the image directory. */
interface ImagePathSource
{
    /** @return iterable<string> */
    public function paths(): iterable;
}
