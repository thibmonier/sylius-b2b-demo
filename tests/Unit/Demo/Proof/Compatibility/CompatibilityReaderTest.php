<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility;

use App\Demo\Proof\Compatibility\CompatibilityReader;
use App\Demo\Proof\Compatibility\CompatibilityReport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** US-171: the page reads a versioned file; what it cannot trust (no public source, no date) is dropped, never guessed. */
final class CompatibilityReaderTest extends TestCase
{
    private const COMPOSER = 'https://github.com/thibmonier/sylius-trade-credit-plugin/blob/main/composer.json';

    private const WORKFLOW = 'https://github.com/thibmonier/sylius-trade-credit-plugin/blob/main/.github/workflows/build.yaml';

    private const LOCK = 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/composer.lock';

    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/compat-reader-' . bin2hex(random_bytes(4)) . '.json';
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->file);
    }

    public function testAPluginEntryIsReadWithItsSourcesAndDates(): void
    {
        $plugin = $this->report($this->data())->plugin('sylius-trade-credit-plugin');

        self::assertTrue($plugin->known);
        self::assertSame(['sylius-order-hold-plugin'], $plugin->requires);
        self::assertSame(['sylius-facturx-plugin'], $plugin->suggests);
        self::assertSame(self::COMPOSER, $plugin->dependenciesSource);
        self::assertSame('2026-10-23', $plugin->dependenciesCheckedAt);
        self::assertSame(['8.3', '8.4'], $plugin->ci['php'] ?? null);
        self::assertSame(self::WORKFLOW, $plugin->ciSource);
        self::assertSame('v1.1.0', $plugin->demoVersion);
        self::assertFalse($plugin->dependenciesStale);
    }

    public function testTheDemoStackIsReadWithItsSource(): void
    {
        $demo = $this->report($this->data())->demo();

        self::assertNotNull($demo);
        self::assertSame('2.3.2', $demo->sylius);
        self::assertSame('8.1.9', $demo->symfony);
        self::assertSame(self::LOCK, $demo->source);
    }

    public function testAStaleSectionKeepsItsOldDateAndIsFlagged(): void
    {
        $data = $this->data();
        $data['plugins']['sylius-trade-credit-plugin']['ci']['stale'] = true;
        $data['plugins']['sylius-trade-credit-plugin']['ci']['checked_at'] = '2026-10-14';

        $plugin = $this->report($data)->plugin('sylius-trade-credit-plugin');

        self::assertTrue($plugin->ciStale);
        self::assertSame('2026-10-14', $plugin->ciCheckedAt);
    }

    public function testASectionWithoutPublicSourceOrDateIsDropped(): void
    {
        $data = $this->data();
        $data['plugins']['sylius-trade-credit-plugin']['dependencies']['source'] = 'http://example.com/composer.json';
        unset($data['plugins']['sylius-trade-credit-plugin']['ci']['checked_at']);

        $plugin = $this->report($data)->plugin('sylius-trade-credit-plugin');

        self::assertFalse($plugin->hasDependencies());
        self::assertSame([], $plugin->requires);
        self::assertFalse($plugin->hasCi());
        self::assertNull($plugin->ci);
    }

    public function testAnUnknownPluginIsAnEmptyEntryNotAnError(): void
    {
        $plugin = $this->report($this->data())->plugin('sylius-unknown-plugin');

        self::assertFalse($plugin->known);
        self::assertFalse($plugin->hasCi());
        self::assertFalse($plugin->inDemo());
    }

    public function testAMissingOrMalformedFileGivesAnEmptyReport(): void
    {
        foreach ([null, '{ not json', '[1, 2]'] as $content) {
            if (null !== $content) {
                file_put_contents($this->file, $content);
            }

            $report = new CompatibilityReader($this->file)->report();

            self::assertNull($report->generatedAt());
            self::assertNull($report->demo());
            self::assertFalse($report->plugin('sylius-trade-credit-plugin')->known);
        }
    }

    /** @param array<string, mixed> $data */
    private function report(array $data): CompatibilityReport
    {
        file_put_contents($this->file, json_encode($data, \JSON_THROW_ON_ERROR));

        return new CompatibilityReader($this->file)->report();
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        return [
            'generated_at' => '2026-10-23T10:00:00Z',
            'demo' => ['sylius' => '2.3.2', 'symfony' => '8.1.9', 'php' => '^8.5', 'source' => self::LOCK, 'checked_at' => '2026-10-23'],
            'plugins' => [
                'sylius-trade-credit-plugin' => [
                    'dependencies' => ['requires' => ['sylius-order-hold-plugin'], 'suggests' => ['sylius-facturx-plugin'], 'source' => self::COMPOSER, 'checked_at' => '2026-10-23'],
                    'ci' => ['php' => ['8.3', '8.4'], 'sylius' => ['2.2', '2.3'], 'symfony' => ['7.4'], 'database' => ['mysql:8.4'], 'source' => self::WORKFLOW, 'checked_at' => '2026-10-23'],
                    'demo' => ['version' => 'v1.1.0', 'source' => self::LOCK, 'checked_at' => '2026-10-23'],
                    'fingerprint' => 'abc',
                ],
            ],
        ];
    }
}
