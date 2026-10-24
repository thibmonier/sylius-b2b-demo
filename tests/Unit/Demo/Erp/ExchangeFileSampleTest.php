<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Erp;

use App\Demo\Erp\ExchangeFileSample;
use App\Demo\Erp\ExchangeFileStatus;
use App\Demo\Erp\ExportedOrderChannels;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusErpFlatfilePlugin\Contract\OrderLinesCsv;

/**
 * US-193: the excerpt of the page "Flat-file exchange" is read from the last file really deposited in the exchange directory:
 * the header and three lines of the current channel, e-mails masked. Every failure is a neutral status, never an exception,
 * and the reader only reads: nothing is written to the directory.
 */
final class ExchangeFileSampleTest extends TestCase
{
    private string $directory;

    /** @var array<string, string> webhook id => channel code, as the outbox knows them */
    private array $known = [];

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/erp-sample-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/.incoming', 0777, true);
    }

    protected function tearDown(): void
    {
        chmod($this->directory, 0777);
        foreach (array_merge(glob($this->directory . '/.incoming/*') ?: [], glob($this->directory . '/*') ?: []) as $file) {
            is_file($file) && unlink($file);
        }
        rmdir($this->directory . '/.incoming');
        rmdir($this->directory);
    }

    public function testTheHeaderAndThreeLinesOfTheCurrentChannelOfTheLastFileAreShown(): void
    {
        $this->file('orders-1.csv', [$this->line(1, 'B2B')], 1000);
        $this->file('orders-2.csv', [$this->line(2, 'B2B'), $this->line(3, 'B2B'), $this->line(4, 'B2C'), $this->line(5, 'B2B'), $this->line(6, 'B2B')], 2000);

        $excerpt = $this->sample()->forChannel('B2B');

        self::assertSame(ExchangeFileStatus::Shown, $excerpt->status);
        $lines = explode("\n", rtrim($excerpt->csv, "\n"));
        self::assertSame(implode(';', OrderLinesCsv::HEADER), $lines[0]);
        self::assertSame(['0000000002', '0000000003', '0000000005'], array_map(static fn (string $line): string => explode(';', $line)[0], \array_slice($lines, 1)));
    }

    public function testTheEmailsAreMasked(): void
    {
        $this->file('orders-1.csv', [$this->line(1, 'B2B')]);

        $csv = $this->sample()->forChannel('B2B')->csv;

        self::assertStringContainsString('c***@acme.example', $csv);
        self::assertStringNotContainsString('claire', $csv);
    }

    public function testAnEmptyDirectoryHasNoFileYet(): void
    {
        self::assertSame(ExchangeFileStatus::NoFile, $this->sample()->forChannel('B2B')->status);
    }

    public function testTheProvisionalFilesAreNeverShown(): void
    {
        file_put_contents($this->directory . '/.incoming/orders-9.csv', implode(';', OrderLinesCsv::HEADER) . "\n" . $this->line(9, 'B2B') . "\n");

        self::assertSame(ExchangeFileStatus::NoFile, $this->sample()->forChannel('B2B')->status);
    }

    public function testAFileOfTheOtherChannelGivesANeutralStatus(): void
    {
        $this->file('orders-1.csv', [$this->line(1, 'B2C'), $this->line(2, 'B2C')]);

        $excerpt = $this->sample()->forChannel('B2B');

        self::assertSame(ExchangeFileStatus::OtherChannel, $excerpt->status);
        self::assertSame('', $excerpt->csv);
    }

    public function testAMissingDirectoryGivesANeutralStatusWithoutPath(): void
    {
        $missing = $this->directory . '/absent';

        $excerpt = (new ExchangeFileSample($missing, $this->channels()))->forChannel('B2B');

        self::assertSame(ExchangeFileStatus::Unavailable, $excerpt->status);
        self::assertStringNotContainsString($missing, $excerpt->csv);
    }

    public function testAnUnreadableDirectoryGivesANeutralStatus(): void
    {
        $this->file('orders-1.csv', [$this->line(1, 'B2B')]);
        chmod($this->directory, 0);

        $excerpt = $this->sample()->forChannel('B2B');

        self::assertSame(ExchangeFileStatus::Unavailable, $excerpt->status);
        self::assertSame('', $excerpt->csv);
    }

    public function testAnEmptyFileGivesANeutralStatus(): void
    {
        file_put_contents($this->directory . '/orders-1.csv', '');

        self::assertSame(ExchangeFileStatus::Empty, $this->sample()->forChannel('B2B')->status);
    }

    public function testAFileWithOnlyTheHeaderIsEmptyToo(): void
    {
        $this->file('orders-1.csv', []);

        self::assertSame(ExchangeFileStatus::Empty, $this->sample()->forChannel('B2B')->status);
    }

    public function testNothingIsWrittenToTheDirectory(): void
    {
        $this->file('orders-1.csv', [$this->line(1, 'B2B')]);
        $before = $this->snapshot();

        $this->sample()->forChannel('B2B');
        $this->sample()->forChannel('B2C');

        self::assertSame($before, $this->snapshot());
    }

    /** @param list<string> $lines */
    private function file(string $name, array $lines, int $time = 1000): void
    {
        file_put_contents($this->directory . '/' . $name, implode(';', OrderLinesCsv::HEADER) . "\n" . implode('', array_map(static fn (string $line): string => $line . "\n", $lines)));
        touch($this->directory . '/' . $name, $time);
    }

    /** One line of the contract; the channel of the order is the one the outbox knows for its webhook id. */
    private function line(int $n, string $channel): string
    {
        $id = sprintf('0192a000-0000-7000-8000-%012d', $n);
        $this->known[$id] = $channel;

        return sprintf('%010d;%s;2026-10-20T10:00:00Z;EUR;claire@acme.example;PAPER-A4;10;450;4500;FR', $n, $id);
    }

    private function sample(): ExchangeFileSample
    {
        return new ExchangeFileSample($this->directory, $this->channels());
    }

    private function channels(): ExportedOrderChannels
    {
        return new class($this->known) implements ExportedOrderChannels {
            /** @param array<string, string> $known */
            public function __construct(private readonly array $known)
            {
            }

            public function channelsOf(array $eventIds): array
            {
                return array_intersect_key($this->known, array_flip($eventIds));
            }
        };
    }

    /** @return array<string, string> */
    private function snapshot(): array
    {
        $files = [];
        foreach (glob($this->directory . '/{,.incoming/}*', \GLOB_BRACE) ?: [] as $file) {
            $files[$file] = is_file($file) ? md5_file($file) . filemtime($file) : 'dir';
        }

        return $files;
    }
}
