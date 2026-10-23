<?php

declare(strict_types=1);

namespace App\Demo\Packs;

use App\Demo\Proof\Check\UnreadableProof;

/** Reads packs.json for the check (US-178): any defect is an UnreadableProof with a message for a human. */
final class PacksFile
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
        if (!\is_array($decoded) || !\is_array($decoded['packs'] ?? null)) {
            throw new UnreadableProof($path . ' has no "packs" object');
        }

        return $decoded;
    }
}
