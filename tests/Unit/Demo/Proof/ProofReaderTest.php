<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof;

use App\Demo\Proof\ProofFact;
use App\Demo\Proof\ProofReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-111: the technical proof only displays what has a public source and a verification date. A fact without a
 * source is dropped, an unpublished plugin says so, an unmeasured coverage is never estimated, and a missing or broken
 * data file never raises an error (the page says "not available").
 */
final class ProofReaderTest extends TestCase
{
    private const SLUG = 'sylius-quick-order-plugin';

    private const PACKAGIST = 'https://packagist.org/packages/tmonier/sylius-quick-order-plugin';

    private const WORKFLOW = 'https://github.com/thibmonier/sylius-quick-order-plugin/blob/main/.github/workflows/build.yaml';

    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'proof') ?: throw new \RuntimeException('no temporary file');
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testAVerifiedFactCarriesItsValueItsSourceAndItsDate(): void
    {
        $this->write(['plugins' => [self::SLUG => ['version' => ['value' => 'v1.1.0', 'source' => self::PACKAGIST, 'checked_at' => '2026-10-21']]]]);

        $fact = $this->reader()->report()->fact(self::SLUG, 'version');

        self::assertSame(ProofFact::VERIFIED, $fact->status);
        self::assertSame('v1.1.0', $fact->value);
        self::assertSame(self::PACKAGIST, $fact->source);
        self::assertSame('2026-10-21', $fact->checkedAt);
        self::assertFalse($fact->stale);
    }

    public function testAnUnpublishedPluginSaysSoWithTheSourceThatShowsIt(): void
    {
        $this->write(['plugins' => [self::SLUG => ['version' => ['state' => 'unpublished', 'source' => self::PACKAGIST, 'checked_at' => '2026-10-21']]]]);

        $fact = $this->reader()->report()->fact(self::SLUG, 'version');

        self::assertSame(ProofFact::UNPUBLISHED, $fact->status);
        self::assertNull($fact->value);
    }

    public function testACoverageThatIsNotMeasuredIsNeverAnEstimate(): void
    {
        $this->write(['plugins' => [self::SLUG => ['coverage' => ['value' => 83, 'source' => '', 'checked_at' => '2026-10-21']]]]);

        self::assertSame(ProofFact::UNMEASURED, $this->reader()->report()->fact(self::SLUG, 'coverage')->status);
        self::assertSame(ProofFact::UNMEASURED, $this->reader()->report()->fact('sylius-unknown-plugin', 'coverage')->status);
    }

    public function testAFigureWithoutSourceIsNotDisplayed(): void
    {
        $this->write(['plugins' => [self::SLUG => [
            'tests' => ['value' => 120, 'checked_at' => '2026-10-21'],
            'phpstan' => ['value' => 'max', 'source' => '', 'checked_at' => '2026-10-21'],
            'ci' => ['value' => 'success', 'source' => self::WORKFLOW],
        ]]]);

        foreach (['tests', 'phpstan', 'ci'] as $key) {
            $fact = $this->reader()->report()->fact(self::SLUG, $key);
            self::assertSame(ProofFact::UNAVAILABLE, $fact->status, $key);
            self::assertNull($fact->value, $key);
        }
    }

    /** @return iterable<string, array{string}> */
    public static function untrustedSources(): iterable
    {
        yield 'plain http' => ['http://packagist.org/packages/tmonier/x'];
        yield 'another host' => ['https://example.com/report'];
        yield 'credentials in the URL' => ['https://user:secret@github.com/thibmonier/x'];
        yield 'query string (token)' => ['https://github.com/thibmonier/x?access_token=abc'];
        yield 'local address' => ['https://localhost/x'];
        yield 'not an URL' => ['see the CI'];
    }

    #[DataProvider('untrustedSources')]
    public function testASourceOutsideTheTwoPublicHostsIsRefused(string $source): void
    {
        $this->write(['plugins' => [self::SLUG => ['version' => ['value' => 'v1.0.0', 'source' => $source, 'checked_at' => '2026-10-21']]]]);

        self::assertSame(ProofFact::UNAVAILABLE, $this->reader()->report()->fact(self::SLUG, 'version')->status);
    }

    public function testTheLastVerifiedValueIsServedWithItsDateWhenTheSourceFailedAtRefresh(): void
    {
        $this->write(['plugins' => [self::SLUG => ['ci' => ['value' => 'success', 'source' => self::WORKFLOW, 'checked_at' => '2026-10-14', 'stale' => true]]]]);

        $fact = $this->reader()->report()->fact(self::SLUG, 'ci');

        self::assertSame(ProofFact::VERIFIED, $fact->status);
        self::assertSame('2026-10-14', $fact->checkedAt);
        self::assertTrue($fact->stale);
    }

    public function testADeliveryFactIsReadLikeAPluginFact(): void
    {
        $this->write(['delivery' => ['sbom' => ['value' => 'CycloneDX', 'source' => 'https://github.com/thibmonier/sylius-b2b-demo/blob/main/.github/workflows/ci.yaml', 'checked_at' => '2026-10-21']]]);

        self::assertSame('CycloneDX', $this->reader()->report()->deliveryFact('sbom')->value);
        self::assertSame(ProofFact::UNAVAILABLE, $this->reader()->report()->deliveryFact('image_scan')->status);
    }

    public function testAMissingFileGivesAReportWithNothingAvailable(): void
    {
        $report = (new ProofReader($this->file . '.missing'))->report();

        self::assertNull($report->generatedAt());
        self::assertSame(ProofFact::UNAVAILABLE, $report->fact(self::SLUG, 'version')->status);
        self::assertSame(ProofFact::UNAVAILABLE, $report->deliveryFact('ci')->status);
    }

    /** @return iterable<string, array{string}> */
    public static function brokenFiles(): iterable
    {
        yield 'empty' => [''];
        yield 'not JSON' => ['{ nope'];
        yield 'a list instead of an object' => ['[1, 2]'];
        yield 'plugins is a string' => ['{"plugins": "x"}'];
        yield 'a fact that is a string' => ['{"plugins": {"a": {"version": "v1"}}}'];
    }

    #[DataProvider('brokenFiles')]
    public function testABrokenFileNeverRaisesAnError(string $content): void
    {
        file_put_contents($this->file, $content);

        $report = $this->reader()->report();

        self::assertSame(ProofFact::UNAVAILABLE, $report->fact('a', 'version')->status);
        self::assertSame(ProofFact::UNAVAILABLE, $report->deliveryFact('ci')->status);
    }

    public function testTheGenerationDateIsExposed(): void
    {
        $this->write(['generated_at' => '2026-10-21T10:00:00Z']);

        self::assertSame('2026-10-21T10:00:00Z', $this->reader()->report()->generatedAt());
    }

    private function reader(): ProofReader
    {
        return new ProofReader($this->file);
    }

    /** @param array<string, mixed> $data */
    private function write(array $data): void
    {
        file_put_contents($this->file, json_encode($data, \JSON_THROW_ON_ERROR));
    }
}
