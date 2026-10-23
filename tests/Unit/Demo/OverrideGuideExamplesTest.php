<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo;

use App\Demo\OverrideGuide\OverrideGuideExamples;
use PHPUnit\Framework\TestCase;

/** US-172: the registry behind the override guide; the page and the tests read the same list of real examples. */
final class OverrideGuideExamplesTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/override-guide-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/config', recursive: true);
        file_put_contents($this->directory . '/config/hooks.yaml', "one\ntwo\nthree\nfour\n");
        file_put_contents($this->directory . '/config/layout.twig', "layout\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->directory . '/config/hooks.yaml');
        @unlink($this->directory . '/config/layout.twig');
        @rmdir($this->directory . '/config');
        @rmdir($this->directory);
    }

    public function testTheExcerptIsExactlyTheLinesOfTheExample(): void
    {
        $registry = $this->registry('config/hooks.yaml', [2, 3]);

        self::assertSame("two\nthree", $registry->excerpt($registry->all()[0]));
    }

    public function testAnExistingExampleHasNoBrokenReference(): void
    {
        self::assertSame([], $this->registry('config/hooks.yaml', [1, 4])->brokenReferences());
    }

    public function testARenamedFileIsReportedWithItsPath(): void
    {
        $broken = $this->registry('config/renamed.yaml', [1, 2])->brokenReferences();

        self::assertSame(['missing file: config/renamed.yaml'], $broken);
    }

    public function testARelatedFileAndTheLastResortAreChecked(): void
    {
        $registry = new OverrideGuideExamples(
            [['case' => 'template', 'path' => 'config/hooks.yaml', 'lines' => [1, 2], 'contains' => 'one', 'command' => 'bin/console x', 'related' => ['config/gone.yaml']]],
            ['path' => 'config/also-gone.twig', 'lines' => [1, 1], 'contains' => 'x', 'command' => 'diff a b'],
            $this->directory,
        );

        self::assertSame(['missing file: config/gone.yaml', 'missing file: config/also-gone.twig'], $registry->brokenReferences());
    }

    public function testALineRangePastTheEndOfTheFileIsReported(): void
    {
        $broken = $this->registry('config/hooks.yaml', [3, 9], 'three')->brokenReferences();

        self::assertSame(['lines 3-9 are past the end of config/hooks.yaml'], $broken);
    }

    public function testAWitnessTextThatLeftTheRangeIsReportedWithFileRangeAndWitness(): void
    {
        $registry = $this->registry('config/hooks.yaml', [3, 4], 'one');

        self::assertSame(['config/hooks.yaml lines 3-4 no longer contain "one"'], $registry->brokenReferences());
    }

    public function testAWitnessTextInsideTheRangeIsAccepted(): void
    {
        self::assertSame([], $this->registry('config/hooks.yaml', [3, 4], 'four')->brokenReferences());
    }

    public function testTheCasesKeepTheirDeclaredOrder(): void
    {
        $registry = new OverrideGuideExamples(
            [
                ['case' => 'template', 'path' => 'config/hooks.yaml', 'lines' => [1, 1], 'contains' => 'one', 'command' => 'a'],
                ['case' => 'service', 'path' => 'config/hooks.yaml', 'lines' => [2, 2], 'contains' => 'two', 'command' => 'b'],
            ],
            ['path' => 'config/layout.twig', 'lines' => [1, 1], 'contains' => 'layout', 'command' => 'c'],
            $this->directory,
        );

        self::assertSame(['template', 'service'], array_map(static fn ($example): string => $example->case, $registry->all()));
        self::assertSame('config/layout.twig', $registry->lastResort()->path);
    }

    /** @param array{int, int} $lines */
    private function registry(string $path, array $lines, string $witness = 'one'): OverrideGuideExamples
    {
        return new OverrideGuideExamples(
            [['case' => 'template', 'path' => $path, 'lines' => $lines, 'contains' => $witness, 'command' => 'bin/console debug:config x']],
            ['path' => 'config/layout.twig', 'lines' => [1, 1], 'contains' => 'layout', 'command' => 'diff a b'],
            $this->directory,
        );
    }
}
