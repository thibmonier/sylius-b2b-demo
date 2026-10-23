<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\Compatibility\Refresh\CompatibilityFreshness;
use PHPUnit\Framework\TestCase;

/**
 * US-171, scenario "matrice en retard": the proof check (same mechanism as US-168) reports a compatibility file that is
 * behind the catalogue or, when the repositories are at hand, behind a composer.json or a CI matrix.
 */
final class CompatibilityFreshnessTest extends TestCase
{
    public function testAFileInStepWithTheCatalogueAndTheSourcesHasNoGap(): void
    {
        $data = $this->data(['a' => 'fa', 'b' => 'fb']);

        self::assertSame([], CompatibilityFreshness::gaps($data, ['b', 'a'], ['a' => 'fa', 'b' => 'fb']));
    }

    public function testAPluginAddedOrRemovedFromTheCatalogueIsAGapThatNamesIt(): void
    {
        $gaps = CompatibilityFreshness::gaps($this->data(['a' => 'fa', 'gone' => 'fg']), ['a', 'new']);

        self::assertCount(1, $gaps);
        self::assertSame('compatibility plugins', $gaps[0]->figure);
        self::assertStringContainsString('missing from the compatibility data: new', $gaps[0]->detail);
        self::assertStringContainsString('no longer in the catalogue: gone', $gaps[0]->detail);
    }

    public function testAComposerJsonOrAMatrixChangedSinceTheLastGenerationIsAGapThatNamesThePlugin(): void
    {
        $gaps = CompatibilityFreshness::gaps($this->data(['a' => 'fa', 'b' => 'fb']), ['a', 'b'], ['a' => 'fa', 'b' => 'changed']);

        self::assertCount(1, $gaps);
        self::assertSame('compatibility sources', $gaps[0]->figure);
        self::assertStringContainsString('changed since the last generation: b', $gaps[0]->detail);
        self::assertStringContainsString('php bin/compat-refresh', $gaps[0]->describe());
    }

    public function testAPluginWithoutLocalSourcesIsNotCompared(): void
    {
        self::assertSame([], CompatibilityFreshness::gaps($this->data(['a' => 'fa', 'b' => 'fb']), ['a', 'b'], ['a' => 'fa']));
    }

    /**
     * @param array<string, string> $fingerprints
     *
     * @return array<string, mixed>
     */
    private function data(array $fingerprints): array
    {
        $plugins = [];
        foreach ($fingerprints as $slug => $fingerprint) {
            $plugins[$slug] = ['fingerprint' => $fingerprint];
        }

        return ['generated_at' => '2026-10-23T10:00:00Z', 'plugins' => $plugins];
    }
}
