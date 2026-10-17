<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * Just enough Markdown to compare two READMEs: headings, fenced code blocks, tables and link targets, outside code
 * fences, the standard block and the language link line.
 */
final class MarkdownDocument
{
    /** @var list<string> */
    public readonly array $lines;

    /** @var array<int, bool> true for the lines inside (or delimiting) a code fence */
    private array $inFence = [];

    /** @var array<int, bool> true for the lines of the standard block and the language link */
    private array $managed = [];

    /** @var list<array{level: int, text: string, line: int}> */
    private array $headings = [];

    /** @var list<string> */
    private array $codeBlocks = [];

    /** @var list<array{line: int, rows: int, columns: int}> */
    private array $tables = [];

    /** @var list<array{target: string, line: int}> */
    private array $links = [];

    /** @var list<string> */
    private array $currentCode = [];

    private int $blockStart = -1;

    private int $blockEnd = -1;

    private int $blockCount = 0;

    public function __construct(string $content)
    {
        $this->lines = \explode("\n", $content);
        $this->markFences();
        $this->markManaged();
        $this->collect();
    }

    /** @return list<array{level: int, text: string, line: int}> */
    public function headings(): array
    {
        return $this->headings;
    }

    /** @return list<string> */
    public function codeBlocks(): array
    {
        return $this->codeBlocks;
    }

    /** @return list<array{line: int, rows: int, columns: int}> */
    public function tables(): array
    {
        return $this->tables;
    }

    /** @return list<array{target: string, line: int}> */
    public function links(): array
    {
        return $this->links;
    }

    public function blockCount(): int
    {
        return $this->blockCount;
    }

    /** The standard block as it is in the file (markers included), or null. */
    public function block(): ?string
    {
        return $this->blockStart < 0 ? null : \implode("\n", \array_slice($this->lines, $this->blockStart, $this->blockEnd - $this->blockStart + 1));
    }

    public function blockStartLine(): int
    {
        return $this->blockStart + 1;
    }

    public function isInFence(int $index): bool
    {
        return $this->inFence[$index] ?? false;
    }

    public function isManaged(int $index): bool
    {
        return $this->managed[$index] ?? false;
    }

    /** Index of the first heading of the given level (any when null) outside the fences, or -1. */
    public function firstHeadingIndex(int $level): int
    {
        foreach ($this->headings as $heading) {
            if ($heading['level'] === $level) {
                return $heading['line'] - 1;
            }
        }

        return -1;
    }

    /** Index of the last level-2 heading that is a licence section, or -1. */
    public function licenceIndex(): int
    {
        $found = -1;
        foreach ($this->headings as $heading) {
            if (2 === $heading['level'] && 1 === \preg_match('/^licen[cs](e|ing)$/i', $heading['text'])) {
                $found = $heading['line'] - 1;
            }
        }

        return $found;
    }

    /** @return list<string> the GitHub anchors of the headings */
    public function anchors(): array
    {
        $anchors = [];
        $seen = [];
        foreach ($this->headings as $heading) {
            $slug = self::slug($heading['text']);
            $count = $seen[$slug] ?? 0;
            $seen[$slug] = $count + 1;
            $anchors[] = 0 === $count ? $slug : $slug . '-' . $count;
        }

        return $anchors;
    }

    public static function slug(string $heading): string
    {
        $text = \preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $heading) ?? $heading;
        $text = \mb_strtolower(\str_replace(['`', '*'], '', $text));
        $text = \preg_replace('/[^\p{L}\p{N}\s_-]/u', '', $text) ?? $text;

        return \str_replace(' ', '-', $text);
    }

    private function markFences(): void
    {
        $marker = null;
        foreach ($this->lines as $index => $line) {
            $this->inFence[$index] = false;
            if (null === $marker) {
                if (1 === \preg_match('/^\s*(`{3,}|~{3,})/', $line, $match)) {
                    $marker = $match[1];
                    $this->inFence[$index] = true;
                }

                continue;
            }
            $this->inFence[$index] = true;
            if (1 === \preg_match('/^\s*(`{3,}|~{3,})\s*$/', $line, $match) && $match[1][0] === $marker[0] && \strlen($match[1]) >= \strlen($marker)) {
                $marker = null;
            }
        }
    }

    private function markManaged(): void
    {
        $inBlock = false;
        foreach ($this->lines as $index => $line) {
            $this->managed[$index] = false;
            if ($this->inFence[$index]) {
                continue;
            }
            if (!$inBlock && StandardBlock::START === \trim($line)) {
                $inBlock = true;
                ++$this->blockCount;
                if ($this->blockStart < 0) {
                    $this->blockStart = $index;
                }
            }
            if ($inBlock) {
                $this->managed[$index] = true;
                if (StandardBlock::END === \trim($line)) {
                    $inBlock = false;
                    if ($this->blockEnd < $this->blockStart) {
                        $this->blockEnd = $index;
                    }
                }

                continue;
            }
            $this->managed[$index] = \str_contains($line, StandardBlock::LANG_MARKER);
        }
        if ($this->blockStart >= 0 && $this->blockEnd < $this->blockStart) {
            $this->blockStart = -1;
            $this->blockCount = 0;
        }
    }

    private function collect(): void
    {
        $table = null;
        foreach ($this->lines as $index => $line) {
            if ($this->managed[$index]) {
                continue;
            }
            if ($this->inFence[$index]) {
                $this->collectFence($index, $line);

                continue;
            }
            $table = $this->collectTable($table, $index, $line);
            $this->collectHeading($index, $line);
            $this->collectLinks($index, $line);
        }
        if (null !== $table) {
            $this->tables[] = $table;
        }
    }

    private function collectFence(int $index, string $line): void
    {
        if (!($this->inFence[$index - 1] ?? false)) {
            $this->currentCode = [];

            return;
        }
        if (!($this->inFence[$index + 1] ?? false)) {
            $this->codeBlocks[] = \implode("\n", $this->currentCode);

            return;
        }
        $this->currentCode[] = $line;
    }

    /**
     * @param array{line: int, rows: int, columns: int}|null $table
     *
     * @return array{line: int, rows: int, columns: int}|null
     */
    private function collectTable(?array $table, int $index, string $line): ?array
    {
        if (!\str_starts_with(\ltrim($line), '|')) {
            if (null !== $table) {
                $this->tables[] = $table;
            }

            return null;
        }
        if (null === $table) {
            return ['line' => $index + 1, 'rows' => 1, 'columns' => \substr_count(\preg_replace('/\\\\\|/', '', $line) ?? $line, '|')];
        }
        ++$table['rows'];

        return $table;
    }

    private function collectHeading(int $index, string $line): void
    {
        if (1 === \preg_match('/^(#{1,6})\s+(.*?)\s*#*\s*$/', $line, $match)) {
            $this->headings[] = ['level' => \strlen($match[1]), 'text' => $match[2], 'line' => $index + 1];
        }
    }

    private function collectLinks(int $index, string $line): void
    {
        if (1 === \preg_match('/^\s{0,3}\[[^\]]+\]:\s*(\S+)/', $line, $match)) {
            $this->links[] = ['target' => $match[1], 'line' => $index + 1];

            return;
        }
        \preg_match_all('/\]\(\s*<?([^)\s>]+)>?(?:\s+"[^"]*")?\s*\)/', $line, $matches);
        foreach ($matches[1] as $target) {
            $this->links[] = ['target' => $target, 'line' => $index + 1];
        }
    }
}
