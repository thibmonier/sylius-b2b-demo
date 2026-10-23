<?php

declare(strict_types=1);

namespace App\Demo\Proof\Check;

/** Reads proof.json for the check (US-168): any defect is an UnreadableProof with a message for a human. */
final class ProofFile
{
    /** @return array<array-key, mixed> */
    public static function read(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new UnreadableProof($path . ' is missing');
        }

        try {
            $decoded = json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new UnreadableProof($path . ' is not valid JSON (' . $exception->getMessage() . ')', 0, $exception);
        }
        if (!\is_array($decoded) || !\is_array($decoded['plugins'] ?? null)) {
            throw new UnreadableProof($path . ' has no "plugins" object');
        }

        return $decoded;
    }
}
