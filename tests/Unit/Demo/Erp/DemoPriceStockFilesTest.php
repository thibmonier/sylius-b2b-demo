<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Demo\Erp\CatalogSample;
use App\Demo\Erp\DemoPriceStockFiles;
use App\Demo\Erp\SamplePrice;
use App\Demo\Erp\SampleStock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tmonier\SyliusErpFlatfilePlugin\Import\ImportKind;

/**
 * US-187 (B): "ERP Démo (fictif)" drops a file of prices and a file of stock built from the real catalogue of the demo (real variant
 * codes, the values the demo already has: its prices do not move), and one price file made invalid on purpose (a negative amount, a
 * currency foreign to the channel). Atomic deposit, files only: no network.
 */
final class DemoPriceStockFilesTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/erp-demo-files-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/.incoming', 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (array_merge(glob($this->directory . '/.incoming/*') ?: [], glob($this->directory . '/*') ?: []) as $file) {
            is_file($file) && unlink($file);
        }
        rmdir($this->directory . '/.incoming');
        rmdir($this->directory);
    }

    public function testThreeFilesAreDepositedAndNamedByKindWithAUniqueName(): void
    {
        $names = $this->files()->deposit();

        self::assertCount(3, $names);
        $onDisk = array_map('basename', glob($this->directory . '/*.csv') ?: []);
        $expected = $names;
        sort($onDisk);
        sort($expected);
        self::assertSame($expected, $onDisk);
        $kinds = array_map(static fn (string $name): ?ImportKind => ImportKind::ofFile($name), $names);
        self::assertSame([ImportKind::Prices, ImportKind::Prices, ImportKind::Stock], $kinds);
        self::assertCount(3, array_unique($names));
        foreach ($names as $name) {
            self::assertMatchesRegularExpression('/^(?:prices|stock)-20261025T100000Z-(?:invalid-)?[0-9a-f]{8}\.csv$/', $name);
        }
    }

    public function testTheValidPriceFileCarriesTheCurrentPricesWithTheHeaderOfTheContract(): void
    {
        $this->files()->deposit();

        $lines = $this->lines('prices-*.csv', 'invalid');

        self::assertSame('variant_code;price_minor;currency;channel', $lines[0]);
        self::assertSame(['BOLT-M6;450;EUR;B2B', 'NUT-M6;120;EUR;B2B', 'PAPER-A4;990;EUR;B2B'], \array_slice($lines, 1));
    }

    public function testTheStockFileCarriesTheCurrentQuantities(): void
    {
        $this->files()->deposit();

        $lines = $this->lines('stock-*.csv');

        self::assertSame('variant_code;on_hand', $lines[0]);
        self::assertSame(['BOLT-M6;800', 'NUT-M6;0', 'PAPER-A4;125'], \array_slice($lines, 1));
    }

    public function testTheInvalidPriceFileHoldsANegativeAmountAndAForeignCurrency(): void
    {
        $this->files()->deposit();

        $lines = $this->lines('prices-*-invalid-*.csv');

        self::assertSame('variant_code;price_minor;currency;channel', $lines[0]);
        self::assertContains('NUT-M6;-120;EUR;B2B', $lines, 'a negative amount');
        self::assertContains('PAPER-A4;990;USD;B2B', $lines, 'a currency that is not the one of the channel');
        self::assertGreaterThanOrEqual(3, \count($lines), 'a valid line too: one invalid line rejects the whole batch');
    }

    public function testNoProvisionalFileRemainsAndANewCallMakesNewNames(): void
    {
        $first = $this->files()->deposit();
        $second = (new DemoPriceStockFiles($this->directory, $this->catalog(), new MockClock('2026-10-25T10:00:01Z')))->deposit();

        self::assertSame([], glob($this->directory . '/.incoming/*'));
        self::assertSame([], array_intersect($first, $second));
    }

    public function testAnEmptyCatalogueDepositsNothing(): void
    {
        $empty = new class() implements CatalogSample {
            public function prices(int $limit): array
            {
                return [];
            }

            public function stock(int $limit): array
            {
                return [];
            }
        };

        self::assertSame([], (new DemoPriceStockFiles($this->directory, $empty, new MockClock('2026-10-25T10:00:00Z')))->deposit());
        self::assertSame([], glob($this->directory . '/*'));
    }

    public function testTheClassNeverReachesTheNetwork(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../../../src/Demo/Erp/DemoPriceStockFiles.php');

        foreach (['HttpClient', 'curl_', 'fsockopen', 'stream_socket', 'file_get_contents(\'http', 'Component\\Process', 'exec(', 'shell_exec'] as $call) {
            self::assertStringNotContainsString($call, $source);
        }
    }

    /** @return list<string> */
    private function lines(string $pattern, string $without = ''): array
    {
        $files = array_values(array_filter(glob($this->directory . '/' . $pattern) ?: [], static fn (string $file): bool => '' === $without || !str_contains($file, $without)));
        self::assertCount(1, $files, $pattern);

        return explode("\n", rtrim((string) file_get_contents($files[0]), "\n"));
    }

    private function files(): DemoPriceStockFiles
    {
        return new DemoPriceStockFiles($this->directory, $this->catalog(), new MockClock('2026-10-25T10:00:00Z'));
    }

    private function catalog(): CatalogSample
    {
        return new class() implements CatalogSample {
            public function prices(int $limit): array
            {
                return \array_slice([new SamplePrice('BOLT-M6', 'B2B', 'EUR', 450), new SamplePrice('NUT-M6', 'B2B', 'EUR', 120), new SamplePrice('PAPER-A4', 'B2B', 'EUR', 990)], 0, $limit);
            }

            public function stock(int $limit): array
            {
                return \array_slice([new SampleStock('BOLT-M6', 800), new SampleStock('NUT-M6', 0), new SampleStock('PAPER-A4', 125)], 0, $limit);
            }
        };
    }
}
