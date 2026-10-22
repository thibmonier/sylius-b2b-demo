<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Charter;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** US-113 / US-108: the charter of the public texts (a title that reads as a vendor demo, a figure nobody can check, a commercial wording). */
final class PublicTextCharterTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function ambiguousTitles(): iterable
    {
        yield 'fr vendor demo' => ['Fournitures Pro, la démo B2B de Sylius'];
        yield 'en vendor demo' => ['Fournitures Pro, the Sylius B2B demo'];
        yield 'official' => ['La démo officielle des plugins B2B'];
        yield 'en official' => ['The official B2B demo'];
        yield 'by the vendor' => ['A demo by Sylius'];
    }

    #[DataProvider('ambiguousTitles')]
    public function testATitleThatReadsAsAVendorDemoIsReported(string $text): void
    {
        self::assertNotSame([], (new PublicTextCharter())->ambiguities($text), $text);
    }

    /** @return iterable<string, array{string}> */
    public static function neutralTitles(): iterable
    {
        yield 'fr author' => ['Fournitures Pro, une démo B2B réalisée par Thibaut Monier'];
        yield 'en author' => ['Fournitures Pro, a B2B demo built by Thibaut Monier'];
        yield 'stack mention' => ['Une boutique construite avec Sylius 2.3 (licence MIT)'];
    }

    #[DataProvider('neutralTitles')]
    public function testANeutralTitleIsAccepted(string $text): void
    {
        self::assertSame([], (new PublicTextCharter())->ambiguities($text), $text);
    }

    public function testAFigureThatIsNotInTheWhitelistIsReported(): void
    {
        $charter = new PublicTextCharter();

        self::assertSame(['98'], $charter->unsourcedFigures('Une couverture de 98 % sur tous les plugins.'));
        self::assertSame(['40', '1200'], $charter->unsourcedFigures('Déjà 40 clients et 1200 commandes.'));
    }

    public function testAWhitelistedFigureAndPlaceholdersAreAccepted(): void
    {
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->unsourcedFigures('25 ans de delivery, avec %count% plugins open source.'));
        self::assertSame([], $charter->unsourcedFigures('25 years of delivery, with 24 plugins.', ['24']));
    }

    public function testEveryWhitelistedFigureNamesItsPublicSource(): void
    {
        foreach (PublicTextCharter::VERIFIABLE_FIGURES as $figure => $source) {
            self::assertMatchesRegularExpression('/^\d+$/', (string) $figure);
            self::assertNotSame('', $source, 'figure ' . $figure);
        }
    }

    /** @return iterable<string, array{string}> */
    public static function commercial(): iterable
    {
        yield 'sur mesure' => ['Intégration sur mesure de votre ERP'];
        yield 'devis' => ['Demandez un devis pour cette intégration'];
        yield 'nous développons' => ['Nous développons pour vous le connecteur'];
        yield 'tailor-made' => ['A tailor-made connector'];
        yield 'we build' => ['We build it for you'];
        yield 'quote us' => ['Get a quote for this connector'];
    }

    #[DataProvider('commercial')]
    public function testACommercialWordingIsReported(string $text): void
    {
        self::assertNotSame([], (new PublicTextCharter())->commercialWording($text), $text);
    }

    public function testAnInformativeTextHasNoCommercialWording(): void
    {
        self::assertSame([], (new PublicTextCharter())->commercialWording('Intégration non fournie : le contrat d’événements du plugin décrit les messages à consommer.'));
    }
}
