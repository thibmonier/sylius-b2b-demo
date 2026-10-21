<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

/** A public source (Packagist, GitHub) did not answer usefully: the last verified value stays, marked stale. */
final class SourceUnavailable extends \RuntimeException
{
}
