<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Demo\Theme\PriceBreaksProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Customer\Model\CustomerGroup;
use Sylius\Component\Customer\Model\CustomerGroupInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceCalculatorInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceRow;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceTable;

/**
 * US-090 / US-091 (decision D2): public tiers plus the price of the company, carried by its customer group.
 */
final class PriceBreaksProviderTest extends TestCase
{
    public function testPublicTiersAreNotNegotiated(): void
    {
        $breaks = $this->provider($this->publicTable(), $this->publicTable(), null)->forVariant(new ProductVariant());

        self::assertNotNull($breaks);
        self::assertCount(4, $breaks->rows);
        self::assertNull($breaks->negotiated());
        self::assertNull($breaks->groupName);
        self::assertSame(1850, $breaks->basePrice());
        self::assertSame(1573, $breaks->lowestPrice());
    }

    public function testRowsBelowThePublicPriceAreTheNegotiatedOnes(): void
    {
        $breaks = $this->provider($this->companyTable(), $this->publicTable(), 'Acme')->forVariant(new ProductVariant());

        self::assertNotNull($breaks);
        $negotiated = $breaks->negotiated();
        self::assertNotNull($negotiated);
        self::assertSame(2, $negotiated->minQuantity);
        self::assertSame(1628, $negotiated->unitPrice);
        self::assertSame('Acme', $breaks->groupName);
        self::assertSame([false, true, true, true], array_map(static fn ($row): bool => $row->negotiated, $breaks->rows));
    }

    public function testACompanyRowEqualToThePublicPriceIsNotNegotiated(): void
    {
        $company = new TierPriceTable([new TierPriceRow(1, 1850, 0.0, false), new TierPriceRow(10, 1757, 5.0, false)]);
        $public = new TierPriceTable([new TierPriceRow(1, 1850, 0.0, false), new TierPriceRow(10, 1757, 5.0, false)]);

        $breaks = $this->provider($company, $public, 'Acme')->forVariant(new ProductVariant());

        self::assertNotNull($breaks);
        self::assertNull($breaks->negotiated());
    }

    public function testNoTierMeansNoBreaks(): void
    {
        $none = new TierPriceTable([new TierPriceRow(1, 1850, 0.0, false)]);

        self::assertNull($this->provider($none, $none, null)->forVariant(new ProductVariant()));
        self::assertNull($this->provider($none, $none, null)->forVariant(null));
    }

    public function testNoChannelMeansNoBreaks(): void
    {
        $channels = $this->createStub(ChannelContextInterface::class);
        $channels->method('getChannel')->willThrowException(new ChannelNotFoundException());
        $provider = new PriceBreaksProvider($this->createStub(TierPriceCalculatorInterface::class), $channels, $this->createStub(CustomerContextInterface::class));

        self::assertNull($provider->forVariant(new ProductVariant()));
    }

    public function testHugeTiersAreSignificantAndNoiseIsNot(): void
    {
        $noise = new TierPriceTable([new TierPriceRow(1, 890, 0.0, false), new TierPriceRow(10, 889, 0.1, false)]);

        $breaks = $this->provider($noise, $noise, null)->forVariant(new ProductVariant());
        self::assertNotNull($breaks);
        self::assertFalse($breaks->hasSignificantGap());

        $breaks = $this->provider($this->publicTable(), $this->publicTable(), null)->forVariant(new ProductVariant());
        self::assertNotNull($breaks);
        self::assertTrue($breaks->hasSignificantGap());
    }

    public function testTheGapMustBeStrictlyAboveFivePercent(): void
    {
        $exactlyFive = new TierPriceTable([new TierPriceRow(1, 1000, 0.0, false), new TierPriceRow(10, 950, 5.0, false)]);
        $justAbove = new TierPriceTable([new TierPriceRow(1, 1000, 0.0, false), new TierPriceRow(10, 949, 5.1, false)]);

        self::assertFalse($this->provider($exactlyFive, $exactlyFive, null)->forVariant(new ProductVariant())?->hasSignificantGap());
        self::assertTrue($this->provider($justAbove, $justAbove, null)->forVariant(new ProductVariant())?->hasSignificantGap());
    }

    public function testTheRowsCarryTheRateOfTheRuleNotTheGapOfTheRoundedPrice(): void
    {
        // 5 % off 8.45 EUR is 8.0275, rounded to 8.03: the plugin reports a gap of 4.97 %, the rule says 5 %.
        $table = new TierPriceTable([new TierPriceRow(1, 845, 0.0, false), new TierPriceRow(10, 803, 5.0, false), new TierPriceRow(50, 761, 9.9, false)]);

        $breaks = $this->provider($table, $table, null)->forVariant(new ProductVariant());

        self::assertNotNull($breaks);
        self::assertSame([0.0, 5.0, 10.0], array_map(static fn ($row): float => $row->percentage, $breaks->rows));
    }

    private function publicTable(): TierPriceTable
    {
        return new TierPriceTable([new TierPriceRow(1, 1850, 0.0, false), new TierPriceRow(10, 1757, 5.0, false), new TierPriceRow(50, 1665, 10.0, false), new TierPriceRow(200, 1573, 15.0, false)]);
    }

    private function companyTable(): TierPriceTable
    {
        return new TierPriceTable([new TierPriceRow(1, 1850, 0.0, false), new TierPriceRow(2, 1628, 12.0, false), new TierPriceRow(50, 1517, 18.0, false), new TierPriceRow(200, 1517, 18.0, false)]);
    }

    private function provider(TierPriceTable $forCustomer, TierPriceTable $public, ?string $groupName): PriceBreaksProvider
    {
        $group = null === $groupName ? null : new CustomerGroup();
        $group?->setName($groupName);
        $calculator = $this->createStub(TierPriceCalculatorInterface::class);
        $calculator->method('table')->willReturnCallback(static fn ($variant, $channel, ?CustomerGroupInterface $g): TierPriceTable => null === $g ? $public : $forCustomer);
        $channels = $this->createStub(ChannelContextInterface::class);
        $channels->method('getChannel')->willReturn(new Channel());
        $customer = new Customer();
        $customer->setGroup($group);
        $customers = $this->createStub(CustomerContextInterface::class);
        $customers->method('getCustomer')->willReturn($customer);

        return new PriceBreaksProvider($calculator, $channels, $customers);
    }
}
