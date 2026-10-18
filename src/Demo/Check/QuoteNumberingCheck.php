<?php

declare(strict_types=1);

namespace App\Demo\Check;

/**
 * US-097: data control of the demo quotes. A number "Q-YYYY-NNN" must carry the year of its date, which must be the
 * current year (the audit found Q-2024-001 dated 2026), and must be unique.
 */
final class QuoteNumberingCheck
{
    private const PATTERN = '/^Q-(\d{4})-(\d{3})$/';

    /**
     * @param iterable<array{number: string, createdAt: \DateTimeInterface}> $quotes
     *
     * @return list<string> one message per violation, empty when the numbering is coherent
     */
    public function violations(iterable $quotes, \DateTimeInterface $today): array
    {
        $violations = [];
        $seen = [];
        foreach ($quotes as $quote) {
            $number = $quote['number'];
            if (isset($seen[$number])) {
                $violations[] = sprintf('Quote number %s is used twice.', $number);
            }
            $seen[$number] = true;

            $violation = $this->violation($number, $quote['createdAt'], $today);
            if (null !== $violation) {
                $violations[] = $violation;
            }
        }

        return $violations;
    }

    private function violation(string $number, \DateTimeInterface $createdAt, \DateTimeInterface $today): ?string
    {
        if (1 !== preg_match(self::PATTERN, $number, $matches)) {
            return sprintf('Quote number %s does not follow "Q-YYYY-NNN".', $number);
        }
        if ($matches[1] !== $createdAt->format('Y')) {
            return sprintf('Quote %s is dated %s: the year of the number differs from the year of the date.', $number, $createdAt->format('Y-m-d'));
        }
        if ($matches[1] !== $today->format('Y')) {
            return sprintf('Quote %s is not numbered with the current year (%s).', $number, $today->format('Y'));
        }

        return null;
    }
}
