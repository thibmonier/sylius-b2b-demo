<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Logistics;

use App\Demo\Logistics\ContainerCapacityCalculator;
use App\Demo\Logistics\ContainerSpace;
use App\Demo\Logistics\LogisticUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Hand-checked against the CSV figures (cm and kg), the plugin presets (mm and g) and the formula
 * min(floor(usable volume x efficiency / unit volume), floor(payload / unit weight)).
 */
final class ContainerCapacityCalculatorTest extends TestCase
{
    private const EUR_PALLET = [1200, 800, 1656, 1_000_000, 90];

    private const CARTON_S = [300, 200, 150, 10_000, 90];

    public function testItConvertsCentimetresAndKilogramsToMillimetresAndGrams(): void
    {
        $unit = LogisticUnit::fromCatalog(0.35, 8.0, 5.0, 5.0);

        self::assertSame(80, $unit->widthMm);
        self::assertSame(50, $unit->heightMm);
        self::assertSame(50, $unit->depthMm);
        self::assertSame(350, $unit->weightG);
    }

    public function testItRoundsFractionalCatalogFiguresToTheNearestInteger(): void
    {
        $unit = LogisticUnit::fromCatalog(0.1236, 2.25, 1.04, 3.0);

        self::assertSame(124, $unit->weightG);
        self::assertSame(23, $unit->widthMm);
        self::assertSame(10, $unit->heightMm);
    }

    public function testScrewBoxOnAnEurPalletIsBoundByThePayload(): void
    {
        // VISS-001: 0.35 kg, 8 x 5 x 5 cm = 200 000 mm3. Volume: 1 430 784 000 / 200 000 = 7153; payload: 1 000 000 / 350 = 2857.
        $unit = LogisticUnit::fromCatalog(0.35, 8.0, 5.0, 5.0);

        self::assertSame(2857, $this->calculator()->capacity($unit, $this->space(self::EUR_PALLET)));
    }

    public function testCartonPackOnAnEurPalletIsBoundByTheVolume(): void
    {
        // EMBA-001: 2.5 kg, 35 x 12 x 25 cm = 10 500 000 mm3. Volume: 1 430 784 000 / 10 500 000 = 136; payload: 400.
        $unit = LogisticUnit::fromCatalog(2.5, 35.0, 12.0, 25.0);

        self::assertSame(136, $this->calculator()->capacity($unit, $this->space(self::EUR_PALLET)));
    }

    public function testHardHatOnAnEurPalletIsBoundByTheVolume(): void
    {
        // EPI-CASK-001: 0.38 kg, 22 x 14 x 28 cm = 8 624 000 mm3. Volume: 165; payload: 2631.
        $unit = LogisticUnit::fromCatalog(0.38, 22.0, 14.0, 28.0);

        self::assertSame(165, $this->calculator()->capacity($unit, $this->space(self::EUR_PALLET)));
    }

    public function testScrewBoxInCartonSIsBoundByThePayload(): void
    {
        // Carton S: 300 x 200 x 150 mm x 90 % = 8 100 000 mm3 -> 40 boxes; payload 10 000 g -> 28 boxes.
        $unit = LogisticUnit::fromCatalog(0.35, 8.0, 5.0, 5.0);

        self::assertSame(28, $this->calculator()->capacity($unit, $this->space(self::CARTON_S)));
    }

    public function testAProductLargerThanTheContainerHasNoCapacity(): void
    {
        $unit = LogisticUnit::fromCatalog(2.5, 35.0, 12.0, 25.0);

        self::assertSame(0, $this->calculator()->capacity($unit, $this->space(self::CARTON_S)));
    }

    #[DataProvider('unusableUnits')]
    public function testAnUnknownWeightOrDimensionGivesNoCapacityAndNoDivisionByZero(float $kg, float $w, float $h, float $d): void
    {
        $unit = LogisticUnit::fromCatalog($kg, $w, $h, $d);

        self::assertFalse($unit->isUsable());
        self::assertSame(0, $this->calculator()->capacity($unit, $this->space(self::EUR_PALLET)));
        self::assertSame(0, $this->calculator()->unitsPerLayer($unit, $this->space(self::EUR_PALLET)));
    }

    /** @return iterable<string, array{float, float, float, float}> */
    public static function unusableUnits(): iterable
    {
        yield 'no weight' => [0.0, 8.0, 5.0, 5.0];
        yield 'negative weight' => [-1.0, 8.0, 5.0, 5.0];
        yield 'no width' => [0.35, 0.0, 5.0, 5.0];
        yield 'no height' => [0.35, 8.0, 0.0, 5.0];
        yield 'no depth' => [0.35, 8.0, 5.0, 0.0];
    }

    public function testAContainerWithoutPayloadHasNoCapacity(): void
    {
        $unit = LogisticUnit::fromCatalog(0.35, 8.0, 5.0, 5.0);

        self::assertSame(0, $this->calculator()->capacity($unit, $this->space([1200, 800, 1656, 0, 90])));
    }

    public function testUnitsPerLayerFollowTheFootprintOfTheProduct(): void
    {
        // EUR pallet floor 1200 x 800 x 90 % = 864 000 mm2. VISS-001 footprint 80 x 50 = 4000 -> 216; EMBA-001 350 x 250 = 87 500 -> 9.
        self::assertSame(216, $this->calculator()->unitsPerLayer(LogisticUnit::fromCatalog(0.35, 8.0, 5.0, 5.0), $this->space(self::EUR_PALLET)));
        self::assertSame(9, $this->calculator()->unitsPerLayer(LogisticUnit::fromCatalog(2.5, 35.0, 12.0, 25.0), $this->space(self::EUR_PALLET)));
    }

    /** @param array{int, int, int, int, int} $figures length, width, height (mm), payload (g), efficiency (%) */
    private function space(array $figures): ContainerSpace
    {
        return new ContainerSpace(...$figures);
    }

    private function calculator(): ContainerCapacityCalculator
    {
        return new ContainerCapacityCalculator();
    }
}
