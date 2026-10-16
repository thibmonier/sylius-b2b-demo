<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Csv;

use App\Demo\Infrastructure\Csv\CsvImportException;
use App\Demo\Infrastructure\Csv\CsvProductReader;
use PHPUnit\Framework\TestCase;

final class CsvProductReaderTest extends TestCase
{
    private CsvProductReader $reader;

    private string $tmpDir;

    protected function setUp(): void
    {
        $this->reader = new CsvProductReader();
        $this->tmpDir = \sys_get_temp_dir() . '/csv_reader_test_' . \uniqid('', true);
        \mkdir($this->tmpDir, 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (\glob($this->tmpDir . '/*.csv') ?: [] as $file) {
            \unlink($file);
        }
        \rmdir($this->tmpDir);
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function testReadValidCsvReturnsExpectedRows(): void
    {
        $csvPath = $this->writeCsv([
            'sku,mpn,ean,name_fr,name_en,description_fr,description_en,taxon,attributes_json,weight_kg,width_cm,height_cm,depth_cm,price_eur_ht',
            'EPI-CASK-001,MSA-001,3701000000010,Casque blanc,White helmet,Casque de chantier blanc,White construction helmet,protection-tete,"{""epi_norm"":""EN 397""}",0.45,22,16,18,19.90',
            'EPI-CASK-002,MSA-002,3701000000027,Casque jaune,Yellow helmet,Casque de chantier jaune,Yellow construction helmet,protection-tete,"{""epi_norm"":""EN 397""}",0.45,22,16,18,19.90',
        ]);

        $rows = $this->reader->read($csvPath);

        self::assertCount(2, $rows);
        self::assertSame('EPI-CASK-001', $rows[0]['sku']);
        self::assertSame('Casque blanc', $rows[0]['name_fr']);
        self::assertSame('White helmet', $rows[0]['name_en']);
        self::assertSame('protection-tete', $rows[0]['taxon']);
        self::assertSame('19.90', $rows[0]['price_eur_ht']);
        self::assertSame('EPI-CASK-002', $rows[1]['sku']);
    }

    public function testReadIgnoresBlankLines(): void
    {
        $csvPath = $this->writeCsv([
            'sku,mpn,ean,name_fr,name_en,description_fr,description_en,taxon,attributes_json,weight_kg,width_cm,height_cm,depth_cm,price_eur_ht',
            'EPI-CASK-001,MSA-001,3701000000010,Casque blanc,White helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,19.90',
            '',
            'EPI-CASK-002,MSA-002,3701000000027,Casque jaune,Yellow helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,21.50',
        ]);

        $rows = $this->reader->read($csvPath);

        self::assertCount(2, $rows);
    }

    // -------------------------------------------------------------------------
    // Missing required column
    // -------------------------------------------------------------------------

    public function testThrowsExceptionForMissingRequiredColumn(): void
    {
        // 'name_fr' column is absent
        $csvPath = $this->writeCsv([
            'sku,mpn,ean,name_en,description_fr,description_en,taxon,attributes_json,weight_kg,width_cm,height_cm,depth_cm,price_eur_ht',
            'EPI-CASK-001,MSA-001,3701000000010,White helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,19.90',
        ]);

        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessageMatches('/name_fr/');
        $this->expectExceptionMessageMatches('/Import aborted/');

        $this->reader->read($csvPath);
    }

    public function testThrowsExceptionForMissingSkuColumn(): void
    {
        $csvPath = $this->writeCsv([
            'mpn,ean,name_fr,name_en,description_fr,description_en,taxon,attributes_json,weight_kg,width_cm,height_cm,depth_cm,price_eur_ht',
            'MSA-001,3701000000010,Casque blanc,White helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,19.90',
        ]);

        $this->expectException(CsvImportException::class);
        $this->expectExceptionMessageMatches('/sku/');

        $this->reader->read($csvPath);
    }

    // -------------------------------------------------------------------------
    // Duplicate SKU
    // -------------------------------------------------------------------------

    public function testThrowsExceptionForDuplicateSku(): void
    {
        $csvPath = $this->writeCsv([
            'sku,mpn,ean,name_fr,name_en,description_fr,description_en,taxon,attributes_json,weight_kg,width_cm,height_cm,depth_cm,price_eur_ht',
            // line 2 — first occurrence of EPI-CASK-001
            'EPI-CASK-001,MSA-001,3701000000010,Casque blanc,White helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,19.90',
            // line 3 — duplicate
            'EPI-CASK-001,MSA-001B,3701000000027,Casque blanc bis,White helmet bis,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,19.90',
        ]);

        try {
            $this->reader->read($csvPath);
            self::fail('Expected CsvImportException was not thrown.');
        } catch (CsvImportException $e) {
            self::assertStringContainsString('EPI-CASK-001', $e->getMessage());
            // First occurrence is line 2, duplicate is line 3
            self::assertStringContainsString('2', $e->getMessage());
            self::assertStringContainsString('3', $e->getMessage());
            self::assertStringContainsString('Import aborted', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // US-041 — S2-R3: a malformed row stops the import, it is never skipped
    // -------------------------------------------------------------------------

    public function testARowWithTooFewColumnsFailsWithTheFileAndTheLine(): void
    {
        $csvPath = $this->writeCsv([
            self::HEADER,
            'EPI-CASK-001,MSA-001,3701000000010,Casque blanc,White helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18,19.90',
            'EPI-CASK-002,MSA-002,3701000000027,Casque jaune,White helmet,Desc FR,Desc EN,protection-tete,"{}",0.45,22,16,18',
        ]);

        $message = $this->failureOf(fn () => $this->reader->read($csvPath));

        self::assertStringContainsString($csvPath . ':3', $message);
        self::assertStringContainsString('13', $message);
        self::assertStringContainsString('14', $message);
        self::assertStringContainsString('Import aborted', $message);
    }

    public function testARowWithTooManyColumnsFails(): void
    {
        $csvPath = $this->writeCsv([self::HEADER, 'EPI-CASK-001,MSA-001,3701000000010,Casque,Helmet,D,D,protection-tete,"{}",0.45,22,16,18,19.90,surplus']);

        self::assertStringContainsString($csvPath . ':2', $this->failureOf(fn () => $this->reader->read($csvPath)));
    }

    // -------------------------------------------------------------------------
    // US-041 — S2-R4: the same SKU in two files
    // -------------------------------------------------------------------------

    public function testAnSkuInTwoFilesFailsAndNamesBothFiles(): void
    {
        $first = $this->writeCsv([self::HEADER, self::row('EPI-CASK-001')]);
        $second = $this->writeCsv([self::HEADER, self::row('QUI-VIS-001'), self::row('EPI-CASK-001')]);

        $message = $this->failureOf(fn () => $this->reader->readAll([$first, $second]));

        self::assertStringContainsString('EPI-CASK-001', $message);
        self::assertStringContainsString($first . ':2', $message);
        self::assertStringContainsString($second . ':3', $message);
    }

    public function testDistinctSkusInSeveralFilesAreReadInOrder(): void
    {
        $first = $this->writeCsv([self::HEADER, self::row('EPI-CASK-001')]);
        $second = $this->writeCsv([self::HEADER, self::row('QUI-VIS-001'), self::row('QUI-VIS-002')]);

        $all = $this->reader->readAll([$first, $second]);

        self::assertSame([$first, $second], \array_keys($all));
        self::assertCount(1, $all[$first]);
        self::assertSame('QUI-VIS-002', $all[$second][1]['sku']);
    }

    // -------------------------------------------------------------------------
    // US-041 — S2-R5: attributes_json
    // -------------------------------------------------------------------------

    /** @return iterable<string, array{string}> */
    public static function invalidAttributes(): iterable
    {
        yield 'broken JSON' => ['{"epi_norm":"EN 397"'];
        yield 'not an object' => ['"EN 397"'];
        yield 'a list' => ['["EN 397"]'];
        yield 'number value' => ['{"packaging_unit":12}'];
        yield 'nested value' => ['{"material":{"name":"HDPE"}}'];
        yield 'null value' => ['{"material":null}'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidAttributes')]
    public function testInvalidAttributesFailWithTheFileAndTheLine(string $json): void
    {
        $csvPath = $this->writeCsv([self::HEADER, self::row('EPI-CASK-001'), self::row('EPI-CASK-002', \str_replace('"', '""', $json))]);

        $message = $this->failureOf(fn () => $this->reader->read($csvPath));

        self::assertStringContainsString($csvPath . ':3', $message);
        self::assertStringContainsString('attributes_json', $message);
    }

    public function testEmptyAndValidAttributesAreAccepted(): void
    {
        $csvPath = $this->writeCsv([self::HEADER, self::row('A-1', ''), self::row('A-2', '{}'), self::row('A-3', '{""epi_norm"":""EN 397"",""material"":""HDPE""}')]);

        self::assertCount(3, $this->reader->read($csvPath));
    }

    // -------------------------------------------------------------------------
    // US-041 — S2-R6: BOM, header, encoding
    // -------------------------------------------------------------------------

    public function testAUtf8BomIsIgnored(): void
    {
        $csvPath = $this->writeCsv([self::HEADER, self::row('EPI-CASK-001')], "\xEF\xBB\xBF");

        $rows = $this->reader->read($csvPath);

        self::assertSame('EPI-CASK-001', $rows[0]['sku']);
    }

    public function testSpacesAroundTheHeaderNamesAreIgnored(): void
    {
        $header = ' sku , name_fr,name_en ,taxon, price_eur_ht ';
        $csvPath = $this->writeCsv([$header, 'EPI-1,Casque,Helmet,protection-tete,19.90']);

        $rows = $this->reader->read($csvPath);

        self::assertSame(['sku', 'name_fr', 'name_en', 'taxon', 'price_eur_ht'], \array_keys($rows[0]));
    }

    public function testAnInvalidEncodingIsRejectedWithTheLine(): void
    {
        // "Casque é" encoded in ISO-8859-1.
        $csvPath = $this->writeCsv([self::HEADER, self::row('EPI-CASK-001'), \mb_convert_encoding(self::row('EPI-CASK-002', '', 'Casque é'), 'ISO-8859-1', 'UTF-8')]);

        $message = $this->failureOf(fn () => $this->reader->read($csvPath));

        self::assertStringContainsString($csvPath . ':3', $message);
        self::assertStringContainsString('UTF-8', $message);
    }

    // -------------------------------------------------------------------------
    // US-041 — non-regression: the three real files
    // -------------------------------------------------------------------------

    public function testTheThreeDemoFilesGive210DistinctProducts(): void
    {
        $directory = \dirname(__DIR__, 4) . '/fixtures/csv/';

        $all = $this->reader->readAll([$directory . 'epi.csv', $directory . 'quincaillerie.csv', $directory . 'fournitures.csv']);

        $skus = \array_merge(...\array_values(\array_map(static fn (array $rows): array => \array_column($rows, 'sku'), $all)));
        self::assertCount(210, $skus);
        self::assertSame($skus, \array_unique($skus));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private const HEADER = 'sku,mpn,ean,name_fr,name_en,description_fr,description_en,taxon,attributes_json,weight_kg,width_cm,height_cm,depth_cm,price_eur_ht';

    /** @param string $attributes already escaped for a quoted CSV cell */
    private static function row(string $sku, string $attributes = '', string $name = 'Casque'): string
    {
        return $sku . ',MPN,3701000000010,' . $name . ',Helmet,Desc FR,Desc EN,protection-tete,"' . $attributes . '",0.45,22,16,18,19.90';
    }

    private function failureOf(callable $read): string
    {
        try {
            $read();
        } catch (CsvImportException $e) {
            return $e->getMessage();
        }
        self::fail('Expected CsvImportException was not thrown.');
    }

    /**
     * @param list<string> $lines
     */
    private function writeCsv(array $lines, string $prefix = ''): string
    {
        $path = $this->tmpDir . '/test_' . \uniqid('', true) . '.csv';
        \file_put_contents($path, $prefix . \implode("\n", $lines) . "\n");

        return $path;
    }
}
