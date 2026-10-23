<?php

declare(strict_types=1);

namespace App\Demo\Proof\Check;

/** proof.json cannot be checked: missing, not JSON, or without its plugins (US-168). */
final class UnreadableProof extends \RuntimeException
{
}
