<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Compatibility;

use App\Demo\Proof\Compatibility\CompatibilityReader;
use App\Demo\Proof\Compatibility\DependencyGraph;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/** US-171: the graph is computed from the data, on the server; a mandatory link and a suggestion are two different kinds. */
final class DependencyGraphTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/compat-graph-' . bin2hex(random_bytes(4)) . '.json';
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->file);
    }

    public function testTradeCreditRequiresOrderHoldAndSuggestsFacturx(): void
    {
        $graph = $this->graph();

        $edges = [];
        foreach ($graph['edges'] as $edge) {
            $edges[$edge['from'] . '>' . $edge['to']] = $edge['kind'];
        }
        self::assertSame('required', $edges['trade>hold']);
        self::assertSame('optional', $edges['trade>facturx']);
    }

    public function testOnlyThePluginsThatHaveALinkAreDrawnAndALinkToAnUnknownPluginIsDropped(): void
    {
        $graph = $this->graph();

        $slugs = array_column($graph['nodes'], 'slug');
        sort($slugs);
        self::assertSame(['facturx', 'hold', 'insights', 'trade'], $slugs, 'quick has no link; the link to "outside" is not in the catalogue');
        self::assertCount(3, $graph['edges'], 'trade>hold, trade>facturx, insights>trade');
    }

    public function testADependencyIsDrawnToTheRightOfThePluginThatNeedsIt(): void
    {
        $graph = $this->graph();

        $x = array_column($graph['nodes'], 'x', 'slug');
        self::assertGreaterThan($x['trade'], $x['hold']);
        self::assertGreaterThan($x['trade'], $x['facturx']);
        self::assertGreaterThan($x['insights'], $x['trade']);
    }

    public function testNodesStayInsideTheDrawingAndTwoNodesNeverOverlap(): void
    {
        $graph = $this->graph();

        foreach ($graph['nodes'] as $node) {
            self::assertGreaterThanOrEqual(0, $node['x']);
            self::assertLessThanOrEqual($graph['width'], $node['x'] + $node['width']);
            self::assertLessThanOrEqual($graph['height'], $node['y'] + $node['height']);
        }
        $positions = array_map(static fn (array $node): string => $node['x'] . ',' . $node['y'], $graph['nodes']);
        self::assertSame($positions, array_values(array_unique($positions)));
    }

    public function testNoDataGivesAnEmptyGraph(): void
    {
        $report = new CompatibilityReader($this->file)->report();

        self::assertSame([], DependencyGraph::build($report, ['a' => 'A'])['nodes']);
    }

    /** @return array{nodes: list<array{slug: string, name: string, x: int, y: int, width: int, height: int}>, edges: list<array{from: string, to: string, kind: string, x1: int, y1: int, x2: int, y2: int}>, width: int, height: int} */
    private function graph(): array
    {
        $source = 'https://github.com/thibmonier/x/blob/main/composer.json';
        $plugin = static fn (array $requires, array $suggests): array => ['dependencies' => ['requires' => $requires, 'suggests' => $suggests, 'source' => $source, 'checked_at' => '2026-10-23']];
        file_put_contents($this->file, json_encode(['generated_at' => '2026-10-23T10:00:00Z', 'plugins' => [
            'trade' => $plugin(['hold'], ['facturx']),
            'hold' => $plugin([], []),
            'facturx' => $plugin([], []),
            'insights' => $plugin(['trade'], ['outside']),
            'quick' => $plugin([], []),
        ]], \JSON_THROW_ON_ERROR));

        return DependencyGraph::build(new CompatibilityReader($this->file)->report(), ['trade' => 'Trade Credit', 'hold' => 'Order Hold', 'facturx' => 'Factur-X', 'insights' => 'Company Insights', 'quick' => 'Quick Order']);
    }
}
