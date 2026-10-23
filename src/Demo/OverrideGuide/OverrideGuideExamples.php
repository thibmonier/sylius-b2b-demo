<?php

declare(strict_types=1);

namespace App\Demo\OverrideGuide;

/**
 * US-172: the single list of examples behind the override guide (the page reads it, the tests check it), declared in
 * config/packages/demo_override_guide.yaml. Every example is a file of this repository with the lines to show.
 */
final readonly class OverrideGuideExamples
{
    /** @var list<OverrideExample> */
    private array $examples;

    private OverrideExample $lastResort;

    /**
     * @param list<array{case: string, path: string, lines: array{int, int}, command: string, related?: list<string>}> $examples
     * @param array{path: string, lines: array{int, int}, command: string}                                             $lastResort
     */
    public function __construct(array $examples, array $lastResort, private string $projectDir)
    {
        $this->examples = array_map(
            static fn (array $example): OverrideExample => new OverrideExample($example['case'], $example['path'], $example['lines'][0], $example['lines'][1], $example['command'], $example['related'] ?? []),
            $examples,
        );
        $this->lastResort = new OverrideExample('last-resort', $lastResort['path'], $lastResort['lines'][0], $lastResort['lines'][1], $lastResort['command']);
    }

    /** @return list<OverrideExample> */
    public function all(): array
    {
        return $this->examples;
    }

    public function lastResort(): OverrideExample
    {
        return $this->lastResort;
    }

    public function excerpt(OverrideExample $example): string
    {
        $lines = $this->lines($example->path) ?? [];

        return implode("\n", \array_slice($lines, $example->firstLine - 1, $example->lastLine - $example->firstLine + 1));
    }

    /** @return list<string> what no longer holds (a missing file, lines past its end), each naming the path */
    public function brokenReferences(): array
    {
        $broken = [];
        foreach ([...$this->examples, $this->lastResort] as $example) {
            foreach ([$example->path, ...$example->related] as $path) {
                if (null === $this->lines($path)) {
                    $broken[] = 'missing file: ' . $path;
                }
            }
            $lines = $this->lines($example->path);
            if (null !== $lines && $example->lastLine > \count($lines)) {
                $broken[] = \sprintf('lines %d-%d are past the end of %s', $example->firstLine, $example->lastLine, $example->path);
            }
        }

        return $broken;
    }

    /** @return list<string>|null null when the file does not exist */
    private function lines(string $path): ?array
    {
        $file = $this->projectDir . '/' . $path;
        if (!is_file($file)) {
            return null;
        }

        return explode("\n", rtrim((string) file_get_contents($file), "\n"));
    }
}
