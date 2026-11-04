<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use App\Tests\Support\ComplianceClaimScanner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ComplianceClaimScannerTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function claims(): iterable
    {
        yield 'conforme' => ['Le passeport numérique est conforme au règlement.'];
        yield 'conformité garantie' => ['Passeport produit : conformité garantie.'];
        yield 'certifié' => ['Un passeport certifié pour vos produits.'];
        yield 'compliant' => ['This digital product passport is compliant with ESPR.'];
        yield 'certified' => ['The DPP is certified.'];
        yield 'affirmed compliance' => ['The passport brings compliance.'];
    }

    #[DataProvider('claims')]
    public function testAClaimAboutThePassportIsFound(string $text): void
    {
        self::assertNotSame([], ComplianceClaimScanner::claims($text));
    }

    /** @return iterable<string, array{string}> */
    public static function allowed(): iterable
    {
        yield 'fr disclaimer' => ['Données structurées pour préparer le passeport, sans promesse de conformité réglementaire.'];
        yield 'en disclaimer' => ['Structured data to prepare the passport, with no promise of regulatory compliance.'];
        yield 'other subject' => ['La facture électronique est conforme à Factur-X.'];
        yield 'html is read as text' => ['<p>Passeport produit</p><script>var conforme = 1;</script>'];
    }

    #[DataProvider('allowed')]
    public function testTheDisclaimerAndOtherSubjectsAreNotClaims(string $text): void
    {
        self::assertSame([], ComplianceClaimScanner::claims($text));
    }
}
