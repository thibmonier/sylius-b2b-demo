<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Images;

use App\Demo\Images\ImageManifest;
use App\Demo\Images\ImageManifestAuditor;
use App\Demo\Images\Import\PhotoSelection;
use App\Demo\Infrastructure\Csv\CsvProductReader;
use PHPUnit\Framework\TestCase;

/**
 * US-197 (tranche 2): the colour a product announces is the colour of its photo. A hard hat "jaune" is not shown white. The choices
 * were made by Thibaut on the board of 2026-10-25 (project-management/sprints/sprint-29-liste-panier-et-photos/registre-decisions.md).
 */
final class ProductPhotoColourTest extends TestCase
{
    /** Colour announced by a hard hat => type of the photo that shows it. */
    private const HARD_HAT_TYPES = [
        'blanc' => 'casque-chantier', 'jaune' => 'casque-jaune', 'rouge' => 'casque-rouge', 'orange' => 'casque-orange', 'bleu' => 'casque-bleu',
    ];

    /** Author of the photo chosen for each type of the board (the licence is the one of the bank). */
    private const CHOSEN = [
        'casque-jaune' => ['Richard Heinen', 'unsplash'], 'casque-rouge' => ['Ümit Yıldırım', 'unsplash'], 'casque-orange' => ['Thirdman', 'pexels'],
        'casque-bleu' => ['Carsten Ruthemann', 'pexels'], 'gilet-haute-visibilite' => ['Tanya Paquet', 'unsplash'], 'botte-caoutchouc' => ['Jonathan Cosens Photography', 'unsplash'],
    ];

    /** No photo of the announced colour exists without a person: the white one stays (decision of 2026-10-25, T-197-05). */
    private const KEPT_WHITE = ['EPI-CASK-017'];

    private PhotoSelection $selection;

    private ImageManifest $manifest;

    /** @var array<string, string> text of the product (name and description, lower case) by SKU */
    private array $texts = [];

    protected function setUp(): void
    {
        $dir = \dirname(__DIR__, 4);
        $this->selection = PhotoSelection::load($dir . '/fixtures/images/selection.json');
        $this->manifest = ImageManifest::load($dir . '/fixtures/images/manifest.csv');
        foreach ((new CsvProductReader())->read($dir . '/fixtures/csv/epi.csv') as $row) {
            $this->texts[$row['sku']] = \mb_strtolower(($row['name_fr'] ?? '') . ' ' . ($row['description_fr'] ?? ''));
        }
    }

    public function testAHardHatIsShownInTheColourItsNameAnnounces(): void
    {
        $wrong = [];
        foreach ($this->texts as $sku => $text) {
            if (!\str_starts_with($sku, 'EPI-CASK-') || \in_array($sku, self::KEPT_WHITE, true)) {
                continue;
            }
            $colour = $this->colourOf($text, \array_keys(self::HARD_HAT_TYPES));
            $type = $this->typeOf($sku);
            if ($colour !== null && $type !== self::HARD_HAT_TYPES[$colour]) {
                $wrong[$sku] = $colour . ' shown by ' . $type;
            }
        }

        self::assertSame([], $wrong);
    }

    public function testTheGreenHardHatKeepsTheWhitePhotoForALackOfAFreeOneAndSaysSo(): void
    {
        foreach (self::KEPT_WHITE as $sku) {
            self::assertSame('casque-chantier', $this->typeOf($sku), $sku);
            self::assertStringContainsString('vert', $this->texts[$sku] ?? '', $sku . ' is no longer announced green: update the decision');
        }
    }

    public function testEveryHardHatColourOfTheBoardHasItsOwnPhotoShownOnlyByItsSkus(): void
    {
        foreach (self::HARD_HAT_TYPES as $colour => $type) {
            $skus = $this->skusOf($type);
            self::assertNotSame([], $skus, $type);
            foreach ($skus as $sku) {
                self::assertStringContainsString($colour, $this->texts[$sku] ?? '', $sku . ' does not announce ' . $colour);
            }
        }
    }

    public function testTheYellowHighVisibilityVestsAreShownOnAYellowVestAndTheGreenBootsOnGreenBoots(): void
    {
        foreach ($this->skusOf('gilet-haute-visibilite') as $sku) {
            self::assertStringContainsString('jaune', $this->texts[$sku] ?? '', $sku);
        }
        $boots = $this->skusOf('botte-caoutchouc');
        self::assertSame(['EPI-CHAU-006', 'EPI-CHAU-010', 'EPI-CHAU-017'], $boots);
        foreach ($boots as $sku) {
            self::assertStringContainsString('vert', $this->texts[$sku] ?? '', $sku);
        }
    }

    public function testTheRubberBootIsAPhotoNowAndTheRainJacketKeepsItsNeutralVisualForALackOfAFreePhoto(): void
    {
        $neutral = \array_column($this->selection->neutral(), 'type');

        self::assertSame(['veste-pluie'], $neutral, 'Only the rain jacket still waits for a free photo without a brand (T-197-04).');
    }

    public function testThePhotosChosenOnTheBoardAreCreditedToTheirAuthorWithTheLicenceOfTheirBank(): void
    {
        foreach (self::CHOSEN as $type => [$creator, $licence]) {
            $row = $this->manifest->row($this->skusOf($type)[0] ?? '') ?? [];
            self::assertSame([$creator, $licence, 'validated'], [$row['creator'] ?? '', $row['license'] ?? '', $row['status'] ?? ''], $type);
            self::assertSame([], (new ImageManifestAuditor())->violations([$row]), $type);
        }
    }

    /** @return list<string> */
    private function skusOf(string $type): array
    {
        foreach ($this->selection->photos() as $photo) {
            if ($photo->type === $type) {
                return $photo->skus;
            }
        }

        return [];
    }

    private function typeOf(string $sku): ?string
    {
        foreach ($this->selection->photos() as $photo) {
            if (\in_array($sku, $photo->skus, true)) {
                return $photo->type;
            }
        }

        return null;
    }

    /** @param list<string> $colours */
    private function colourOf(string $text, array $colours): ?string
    {
        foreach ($colours as $colour) {
            if (\str_contains($text, $colour)) {
                return $colour;
            }
        }

        return null;
    }
}
