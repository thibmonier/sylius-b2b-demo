<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

/** Which workflow of a repository carries its CI matrix: one rule, used by the API reading and by the local clones. */
final class CiWorkflow
{
    private const array PREFERRED = ['build.yaml', 'build.yml', 'ci.yaml', 'ci.yml'];

    /** @param list<string> $names file names under .github/workflows */
    public static function preferred(array $names): ?string
    {
        if ([] === $names) {
            return null;
        }
        sort($names);

        return array_values(array_intersect(self::PREFERRED, $names))[0] ?? $names[0];
    }
}
