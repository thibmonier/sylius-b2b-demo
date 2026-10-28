<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof;

use App\Demo\Proof\ProofFact;
use App\Demo\Proof\ProofReport;
use App\Demo\Proof\ProofSummary;
use PHPUnit\Framework\TestCase;

/** US-205: the figures of the summary tiles are counted from the verified facts of the report, never written by hand. */
final class ProofSummaryTest extends TestCase
{
    private const SOURCE = 'https://github.com/thibmonier/example/blob/main/file';

    public function testEachFigureCountsTheVerifiedFactsOfTheListedPluginsOnly(): void
    {
        $summary = ProofSummary::of($this->report(), ['alpha', 'beta', 'gamma', 'delta']);

        self::assertSame(4, $summary->plugins);
        self::assertSame(3, $summary->versions, 'delta has no version');
        self::assertSame(2, $summary->sylius23, 'gamma says false, delta says nothing');
        self::assertSame(2, $summary->ciPassed, 'beta failed, delta has no run');
        self::assertSame(2, $summary->phpstanMax);
        self::assertSame(23 + 15, $summary->testFiles);
        self::assertSame(2, $summary->pluginsWithTestFiles);
    }

    public function testAPluginOutsideTheListIsNotCounted(): void
    {
        $summary = ProofSummary::of($this->report(), ['alpha']);

        self::assertSame(1, $summary->plugins);
        self::assertSame(1, $summary->versions);
        self::assertSame(23, $summary->testFiles);
    }

    public function testTheTestFilesOfTheDemoComeFromTheDeliveryFactOrAreUnknown(): void
    {
        self::assertSame(311, ProofSummary::of($this->report(), ['alpha'])->demoTestFiles);
        self::assertNull(ProofSummary::of(new ProofReport('2026-10-27T10:00:00Z'), ['alpha'])->demoTestFiles);
    }

    public function testNoCoverageFigureMeansNoCoverageColumn(): void
    {
        $summary = ProofSummary::of($this->report(), ['alpha', 'beta']);

        self::assertSame(0, $summary->coverage);
        self::assertFalse($summary->hasCoverage());
    }

    public function testAVerifiedCoverageFigureIsCountedAndShowsTheColumn(): void
    {
        $report = new ProofReport('2026-10-27T10:00:00Z', ['alpha' => ['coverage' => $this->fact(87)]]);

        $summary = ProofSummary::of($report, ['alpha', 'beta']);

        self::assertSame(1, $summary->coverage);
        self::assertTrue($summary->hasCoverage());
    }

    public function testAnEmptyReportGivesZeroEverywhere(): void
    {
        $summary = ProofSummary::of(new ProofReport(null), ['alpha']);

        self::assertSame(1, $summary->plugins);
        self::assertSame(0, $summary->versions + $summary->sylius23 + $summary->ciPassed + $summary->phpstanMax + $summary->testFiles + $summary->coverage);
    }

    private function report(): ProofReport
    {
        return new ProofReport('2026-10-27T10:00:00Z', [
            'alpha' => ['version' => $this->fact('v1.0.0'), 'sylius_23' => $this->fact(true), 'ci' => $this->fact('success'), 'phpstan' => $this->fact('max'), 'tests' => $this->fact(23)],
            'beta' => ['version' => $this->fact('v1.1.0'), 'sylius_23' => $this->fact(true), 'ci' => $this->fact('failure'), 'phpstan' => $this->fact('8'), 'tests' => $this->fact(15)],
            'gamma' => ['version' => $this->fact('v2.0.0'), 'sylius_23' => $this->fact(false), 'ci' => $this->fact('success'), 'phpstan' => $this->fact('max')],
            'delta' => ['version' => ProofFact::unpublished(self::SOURCE, '2026-10-27')],
        ], ['tests' => $this->fact(311)]);
    }

    private function fact(string|int|bool $value): ProofFact
    {
        return ProofFact::verified($value, self::SOURCE, '2026-10-27');
    }
}
