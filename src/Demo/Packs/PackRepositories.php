<?php

declare(strict_types=1);

namespace App\Demo\Packs;

/** The pack repositories the page presents (ADR 0015): the only place that names them. */
final class PackRepositories
{
    public const array ALL = ['sylius-pack-ai-headless', 'sylius-pack-compliance-tooling'];

    private function __construct()
    {
    }
}
