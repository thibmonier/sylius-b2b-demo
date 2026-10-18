<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** US-080: the real allow-list (container parameter) is anchored and matches the subjects the fixtures really produce. */
final class PublicMailSubjectsSmokeTest extends KernelTestCase
{
    /** Subjects seen on the local Mailpit after a reset (invoices, approval requests, order confirmations). */
    private const FIXTURE_SUBJECTS = [
        'Invoice generated',
        'La commande n° 000000006 attend votre approbation',
        'Confirmation de commande',
    ];

    /** @return list<string> */
    private static function patterns(): array
    {
        self::bootKernel();
        $patterns = self::getContainer()->getParameter('demo.public_mail_subjects');
        self::assertIsArray($patterns);

        return array_values(array_map('strval', $patterns));
    }

    public function testEveryPatternIsAnchoredAtBothEnds(): void
    {
        foreach (self::patterns() as $pattern) {
            self::assertMatchesRegularExpression('/^\/\^.+\$\/[a-z]*$/', $pattern, $pattern . ' must be anchored with ^ and $');
        }
    }

    public function testEveryPatternMatchesASubjectProducedByTheFixtures(): void
    {
        foreach (self::patterns() as $pattern) {
            $matching = array_filter(self::FIXTURE_SUBJECTS, static fn (string $subject): bool => 1 === preg_match($pattern, $subject));
            self::assertNotSame([], $matching, $pattern . ' matches no subject of the fixtures: remove or fix it');
        }
    }

    public function testEveryFixtureSubjectIsCoveredAndLookalikesAreNot(): void
    {
        $patterns = self::patterns();
        $matches = static fn (string $subject): bool => [] !== array_filter($patterns, static fn (string $p): bool => 1 === preg_match($p, $subject));

        foreach (self::FIXTURE_SUBJECTS as $subject) {
            self::assertTrue($matches($subject), $subject);
        }
        foreach (['Invoice generated, reset your password', 'Re: Confirmation de commande', "Invoice generated\nBcc: x"] as $lookalike) {
            self::assertFalse($matches($lookalike), $lookalike);
        }
    }
}
