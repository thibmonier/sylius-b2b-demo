<?php

declare(strict_types=1);

namespace App\Tests\Support\Supply;

use App\Tests\Support\Typed;

/**
 * US-208: no development branch in production. A `dev-*` or `*.x-dev` constraint is accepted only when it is frozen to a
 * commit (`#<40 hex>`) and the reason is written in `extra.supply-chain.pinned-branches`.
 */
final class ComposerRules
{
    /**
     * @param array<array-key, mixed> $composer decoded composer.json
     * @param array<array-key, mixed> $lock     decoded composer.lock
     *
     * @return list<string>
     */
    public static function branchViolations(array $composer, array $lock): array
    {
        $reasons = Typed::find($composer, 'extra', 'supply-chain', 'pinned-branches');
        $reasons = \is_array($reasons) ? $reasons : [];
        $violations = [];

        foreach (['require', 'require-dev'] as $section) {
            $constraints = Typed::find($composer, $section);
            foreach (\is_array($constraints) ? $constraints : [] as $package => $constraint) {
                if (!\is_string($constraint) || !self::isBranch($constraint)) {
                    continue;
                }
                if (1 !== preg_match('/#[0-9a-f]{40}$/', $constraint)) {
                    $violations[] = sprintf('%s: "%s" is a development branch, use a stable version or freeze the commit (#<sha>)', $package, $constraint);
                } elseif (!isset($reasons[$package]) || !\is_string($reasons[$package]) || '' === trim($reasons[$package])) {
                    $violations[] = sprintf('%s: the frozen commit has no reason in extra.supply-chain.pinned-branches', $package);
                }
            }
        }

        foreach (['packages', 'packages-dev'] as $section) {
            $packages = Typed::find($lock, $section);
            foreach (\is_array($packages) ? $packages : [] as $package) {
                $name = Typed::string(Typed::at($package, 'name'), 'package name');
                $version = Typed::string(Typed::at($package, 'version'), 'version of ' . $name);
                if (self::isBranch($version) && !isset($reasons[$name])) {
                    $violations[] = sprintf('%s: locked on the development version "%s"', $name, $version);
                }
            }
        }

        return $violations;
    }

    private static function isBranch(string $version): bool
    {
        return 1 === preg_match('/(^dev-|-dev($|#))/', $version);
    }
}
