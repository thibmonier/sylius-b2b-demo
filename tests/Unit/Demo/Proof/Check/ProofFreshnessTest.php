<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Check;

use App\Demo\Proof\Check\ProofFreshness;
use App\Demo\Proof\Check\ProofGap;
use PHPUnit\Framework\TestCase;

/** US-168: the published figures of proof.json against the real state of the repository, without writing anything. */
final class ProofFreshnessTest extends TestCase
{
    public function testAnUpToDateProofHasNoGap(): void
    {
        self::assertSame([], ProofFreshness::gaps($this->proof(tests: 190, slugs: ['a', 'b']), 190, ['b', 'a']));
    }

    public function testATestCountBehindTheSuiteIsAGapWithBothFigures(): void
    {
        $gaps = ProofFreshness::gaps($this->proof(tests: 188, slugs: ['a']), 190, ['a']);

        self::assertCount(1, $gaps);
        self::assertSame('test files', $gaps[0]->figure);
        self::assertSame(188, $gaps[0]->published);
        self::assertSame(190, $gaps[0]->actual);
        self::assertSame('+2', $gaps[0]->difference());
    }

    public function testAPluginAddedOrRemovedFromTheCatalogueIsAGapThatNamesIt(): void
    {
        $gaps = ProofFreshness::gaps($this->proof(tests: 10, slugs: ['a', 'gone']), 10, ['a', 'new']);

        self::assertCount(1, $gaps);
        self::assertSame('plugins', $gaps[0]->figure);
        self::assertSame(2, $gaps[0]->published);
        self::assertSame(2, $gaps[0]->actual);
        self::assertSame('missing from the proof: new; no longer in the catalogue: gone', $gaps[0]->detail);
    }

    public function testAProofWithoutATestFigureIsAGap(): void
    {
        $gaps = ProofFreshness::gaps(['plugins' => ['a' => []], 'delivery' => []], 5, ['a']);

        self::assertCount(1, $gaps);
        self::assertNull($gaps[0]->published);
        self::assertSame('not published', $gaps[0]->difference());
    }

    public function testAGapReadsAsOneLine(): void
    {
        $gap = new ProofGap('test files', 188, 185);

        self::assertSame('test files: published 188, actual 185 (difference -3)', $gap->describe());
    }

    /**
     * @param list<string> $slugs
     *
     * @return array<string, mixed>
     */
    private function proof(int $tests, array $slugs): array
    {
        return [
            'plugins' => array_fill_keys($slugs, ['version' => ['value' => 'v1.0.0']]),
            'delivery' => ['tests' => ['value' => $tests, 'source' => 'https://github.com/thibmonier/sylius-b2b-demo/tree/main/tests', 'checked_at' => '2026-10-23']],
        ];
    }
}
