<?php

declare(strict_types=1);

namespace App\Demo\Mcp;

/** The transcript file is refused as a whole: the message names the line (when there is one) and the reason. */
final class InvalidTranscript extends \InvalidArgumentException
{
    public function __construct(string $reason, public readonly ?int $lineNumber = null)
    {
        parent::__construct('Transcript refused' . (null === $lineNumber ? '' : ', line ' . $lineNumber) . ': ' . $reason);
    }
}
