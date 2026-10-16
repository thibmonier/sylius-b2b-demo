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
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param list<string> $lines
     */
    private function writeCsv(array $lines): string
    {
        $path = $this->tmpDir . '/test_' . \uniqid('', true) . '.csv';
        \file_put_contents($path, \implode("\n", $lines) . "\n");

        return $path;
    }
}
