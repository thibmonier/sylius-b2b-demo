<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * Puts the standard B2B demo block and the cross-language link into README.md and README.fr.md of one plugin
 * repository. Idempotent: a repository already treated is left byte for byte as it is.
 */
final class ReadmeApplier
{
    private const FILES = ['en' => 'README.md', 'fr' => 'README.fr.md'];

    /** @return bool whether a file changed */
    public function apply(string $directory, string $profileUrl): bool
    {
        StandardBlock::assertProfileUrl($profileUrl);
        $plugin = \basename(\rtrim($directory, '/'));
        $contents = [];
        foreach (self::FILES as $language => $file) {
            $content = \is_file($directory . '/' . $file) ? \file_get_contents($directory . '/' . $file) : false;
            if (false === $content) {
                throw new \RuntimeException(\sprintf('%s: %s not found (the French README is written by hand, then treated by this tool).', $plugin, $file));
            }
            $contents[$language] = $content;
        }

        $changed = false;
        foreach (self::FILES as $language => $file) {
            $updated = $this->treat($contents[$language], $language, StandardBlock::isInDemo($plugin), $profileUrl);
            if ($updated !== $contents[$language]) {
                \file_put_contents($directory . '/' . $file, $updated);
                $changed = true;
            }
        }

        return $changed;
    }

    private function treat(string $content, string $language, bool $inDemo, string $profileUrl): string
    {
        $document = new MarkdownDocument($content);
        $lines = $this->withLanguageLink($document, $language);
        $block = \explode("\n", StandardBlock::render($language, $inDemo, $profileUrl));
        $document = new MarkdownDocument(\implode("\n", $lines));

        if ($document->blockCount() > 0) {
            return \implode("\n", $this->replaceBlock($document, $block));
        }

        return \implode("\n", $this->insertBlock($document, $block));
    }

    /** @return list<string> */
    private function withLanguageLink(MarkdownDocument $document, string $language): array
    {
        $lines = $document->lines;
        $link = StandardBlock::languageLink($language);
        foreach ($lines as $index => $line) {
            if (!$document->isInFence($index) && \str_contains($line, StandardBlock::LANG_MARKER)) {
                $lines[$index] = $link;

                return $lines;
            }
        }
        $title = $document->firstHeadingIndex(1);
        \array_splice($lines, $title < 0 ? 0 : $title + 1, 0, $title < 0 ? [$link, ''] : ['', $link]);

        return $lines;
    }

    /**
     * @param list<string> $block
     *
     * @return list<string>
     */
    private function replaceBlock(MarkdownDocument $document, array $block): array
    {
        $start = $document->blockStartLine() - 1;
        $length = \count(\explode("\n", (string) $document->block()));
        $lines = $document->lines;
        \array_splice($lines, $start, $length, $block);

        return $lines;
    }

    /**
     * @param list<string> $block
     *
     * @return list<string>
     */
    private function insertBlock(MarkdownDocument $document, array $block): array
    {
        $lines = $document->lines;
        $licence = $document->licenceIndex();
        if ($licence >= 0) {
            \array_splice($lines, $licence, 0, [...$block, '']);

            return $lines;
        }
        while ([] !== $lines && '' === \end($lines)) {
            \array_pop($lines);
        }

        return [...$lines, '', ...$block, ''];
    }
}
