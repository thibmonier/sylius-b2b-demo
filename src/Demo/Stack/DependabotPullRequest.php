<?php

declare(strict_types=1);

namespace App\Demo\Stack;

final readonly class DependabotPullRequest
{
    public const string CHECKS_SUCCESS = 'success';

    public const string CHECKS_FAILURE = 'failure';

    public const string CHECKS_PENDING = 'pending';

    /** @param self::CHECKS_* $checks */
    public function __construct(
        public int $number,
        public string $title,
        public string $checks,
    ) {
    }
}
