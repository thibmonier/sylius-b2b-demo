<?php

declare(strict_types=1);

namespace App\Demo\Erp;

use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusErpFlatfilePlugin\Deposit\AtomicFileDepositor;
use Tmonier\SyliusErpFlatfilePlugin\Import\ImportKind;

/**
 * "ERP Démo (fictif)" (US-187, B) drops three files in the exchange directory, atomically: prices and stock of real variants of the
 * catalogue with the values they have now (the prices of the demo do not move), and a price file invalid on purpose (a negative amount, a
 * currency foreign to the channel, next to a valid line: one invalid line rejects the whole batch). Files only: no network.
 */
final readonly class DemoPriceStockFiles
{
    private const VARIANTS = 3;

    public function __construct(
        #[Autowire(param: 'tmonier_erp_flatfile.exchange_directory')]
        private string $directory,
        private CatalogSample $catalog,
        private ClockInterface $clock,
    ) {
    }

    /** @return list<string> the names of the files deposited: valid prices, invalid prices, stock */
    public function deposit(): array
    {
        $prices = $this->catalog->prices(self::VARIANTS);
        $stock = $this->catalog->stock(self::VARIANTS);
        $files = [];
        if ([] !== $prices) {
            $files[] = ['prices', $this->priceFile($prices, false)];
            $files[] = ['prices-invalid', $this->priceFile($prices, true)];
        }
        if ([] !== $stock) {
            $files[] = ['stock', $this->stockFile($stock)];
        }

        $names = [];
        $stamp = $this->clock->now()->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
        foreach ($files as [$kind, $content]) {
            $prefix = 'prices-invalid' === $kind ? 'prices-' . $stamp . '-invalid' : $kind . '-' . $stamp;
            $name = $prefix . '-' . substr(hash('sha256', $content), 0, 8) . '.csv';
            (new AtomicFileDepositor($this->directory))->deposit($name, [$content]);
            $names[] = $name;
        }

        return $names;
    }

    /** @param list<SamplePrice> $prices */
    private function priceFile(array $prices, bool $invalid): string
    {
        $lines = [implode(';', ImportKind::Prices->header())];
        foreach ($prices as $index => $price) {
            $amount = $price->priceMinor;
            $currency = $price->currency;
            if ($invalid && 1 === $index) {
                $amount = -$amount;
            }
            if ($invalid && 2 === $index) {
                $currency = 'USD' === $currency ? 'GBP' : 'USD';
            }
            $lines[] = implode(';', [$price->variantCode, $amount, $currency, $price->channelCode]);
        }

        return implode("\n", $lines) . "\n";
    }

    /** @param list<SampleStock> $stock */
    private function stockFile(array $stock): string
    {
        return implode("\n", [implode(';', ImportKind::Stock->header()), ...array_map(static fn (SampleStock $line): string => $line->variantCode . ';' . $line->onHand, $stock)]) . "\n";
    }
}
