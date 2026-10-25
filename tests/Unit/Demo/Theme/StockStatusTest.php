<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Theme;

use App\Demo\Theme\StockStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductVariant;

/** US-198: the three levels of stock of the cards (in stock, low below the threshold, out of stock), and "no information" when the stock is not tracked. */
final class StockStatusTest extends TestCase
{
    /** @return iterable<string, array{bool, int, int, string, bool}> */
    public static function variants(): iterable
    {
        yield 'plenty' => [true, StockStatus::LOW_THRESHOLD, 0, StockStatus::IN, true];
        yield 'just below the threshold' => [true, StockStatus::LOW_THRESHOLD - 1, 0, StockStatus::LOW, true];
        yield 'last one' => [true, 1, 0, StockStatus::LOW, true];
        yield 'nothing left' => [true, 0, 0, StockStatus::OUT, false];
        yield 'everything on hold' => [true, 5, 5, StockStatus::OUT, false];
        yield 'not tracked' => [false, 0, 0, StockStatus::UNTRACKED, true];
    }

    #[DataProvider('variants')]
    public function testTheLevelFollowsTheAvailableQuantity(bool $tracked, int $onHand, int $onHold, string $level, bool $canBuy): void
    {
        $variant = new ProductVariant();
        $variant->setTracked($tracked);
        $variant->setOnHand($onHand);
        $variant->setOnHold($onHold);

        $status = StockStatus::ofVariant($variant);

        self::assertSame($level, $status->level);
        self::assertSame($canBuy, $status->canBuy());
        self::assertSame(max(0, $onHand - $onHold), $status->available);
    }

    public function testAProductWithoutVariantHasNoStockInformation(): void
    {
        self::assertSame(StockStatus::UNTRACKED, StockStatus::ofVariant(null)->level);
    }
}
