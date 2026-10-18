<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Check;

use App\Demo\Check\QuoteNumberingCheck;
use PHPUnit\Framework\TestCase;

final class QuoteNumberingCheckTest extends TestCase
{
    private const TODAY = '2026-10-18 09:00:00';

    public function testCoherentNumbersAreAccepted(): void
    {
        $quotes = [
            ['number' => 'Q-2026-001', 'createdAt' => new \DateTimeImmutable('2026-10-06')],
            ['number' => 'Q-2026-002', 'createdAt' => new \DateTimeImmutable('2026-10-15')],
        ];

        self::assertSame([], $this->check($quotes));
    }

    public function testANumberOfAnotherYearThanItsDateIsRefused(): void
    {
        $quotes = [['number' => 'Q-2024-001', 'createdAt' => new \DateTimeImmutable('2026-09-18')]];

        $violations = $this->check($quotes);

        self::assertCount(1, $violations);
        self::assertStringContainsString('Q-2024-001', $violations[0]);
    }

    public function testAQuoteOfAnotherYearThanTheCurrentOneIsRefusedEvenIfItsNumberMatchesItsDate(): void
    {
        $quotes = [['number' => 'Q-2025-004', 'createdAt' => new \DateTimeImmutable('2025-11-03')]];

        self::assertCount(1, $this->check($quotes));
    }

    public function testAMalformedNumberIsRefused(): void
    {
        $quotes = [['number' => 'Q000012', 'createdAt' => new \DateTimeImmutable('2026-10-06')]];

        self::assertCount(1, $this->check($quotes));
    }

    public function testADuplicatedNumberIsRefused(): void
    {
        $quotes = [
            ['number' => 'Q-2026-001', 'createdAt' => new \DateTimeImmutable('2026-10-06')],
            ['number' => 'Q-2026-001', 'createdAt' => new \DateTimeImmutable('2026-10-07')],
        ];

        self::assertCount(1, $this->check($quotes));
    }

    /**
     * @param list<array{number: string, createdAt: \DateTimeInterface}> $quotes
     *
     * @return list<string>
     */
    private function check(array $quotes): array
    {
        return (new QuoteNumberingCheck())->violations($quotes, new \DateTimeImmutable(self::TODAY));
    }
}
