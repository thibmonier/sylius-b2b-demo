<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

/**
 * Read-only check of one plugin: README.fr.md exists, both READMEs carry the standard block and the language link,
 * the French file has the same structure as the English one, and no forbidden wording or foreign link slipped in.
 */
final class ReadmeChecker
{
    private const ENGLISH = 'README.md';

    private const FRENCH = 'README.fr.md';

    /** Wording the tone charter (docs/vitrine-plugins.md) forbids everywhere. */
    private const FORBIDDEN = [
        '/free alternative|alternative gratuite|alternative libre|open[- ]source alternative|alternative open[- ]source/i',
        '/without paying|sans payer|cheaper|moins cher/i',
        '/(instead of|à la place de) sylius plus/i',
    ];

    /** Existing sentences that compare with Sylius Plus: a warning (an error with --strict). */
    private const PLUS_NAME = '/sylius plus|elesto/i';

    private const COMPARISON = '/alternative|paid|payant|proprietary|propriétaire|without|sans |only part|compar/i';

    /** Commercial or availability wording the standard block must never carry (US-068). */
    private const COMMERCIAL = '/\bmissions?\b|\bdevis\b|\bprestations?\b|calendly|freelance|available for|disponible pour/i';

    /** @var array<string, int> status per URL, so a link shared by 24 repositories is probed once */
    private array $probed = [];

    public function __construct(private readonly bool $strict = false, private readonly ?LinkProbe $probe = null)
    {
    }

    /** @return list<Finding> */
    public function check(FileSource $source, string $profileUrl): array
    {
        $english = $source->read(self::ENGLISH);
        $french = $source->read(self::FRENCH);
        $findings = [];
        if (null === $english) {
            return [new Finding(self::ENGLISH, 0, 'error', 'missing')];
        }
        if (null === $french) {
            $findings[] = new Finding(self::FRENCH, 0, 'error', 'missing');
        }

        $englishDocument = new MarkdownDocument($english);
        $findings = [...$findings, ...$this->checkFile(self::ENGLISH, $englishDocument, 'en', $profileUrl)];
        if (null === $french) {
            return $findings;
        }
        $frenchDocument = new MarkdownDocument($french);

        return [
            ...$findings,
            ...$this->checkFile(self::FRENCH, $frenchDocument, 'fr', $profileUrl),
            ...$this->compare($englishDocument, $frenchDocument),
        ];
    }

    /** @return list<Finding> */
    private function checkFile(string $file, MarkdownDocument $document, string $language, string $profileUrl): array
    {
        return [
            ...$this->checkLanguageLink($file, $document, $language),
            ...$this->checkBlock($file, $document, $language, $profileUrl),
            ...$this->checkWording($file, $document),
            ...$this->checkAnchors($file, $document),
        ];
    }

    /** @return list<Finding> */
    private function checkLanguageLink(string $file, MarkdownDocument $document, string $language): array
    {
        $expected = StandardBlock::languageLink($language);
        foreach (\array_slice($document->lines, 0, 8) as $line) {
            if (\trim($line) === $expected) {
                return [];
            }
        }

        return [new Finding($file, 0, 'error', 'the link to the other language is missing from the top of the file')];
    }

    /** @return list<Finding> */
    private function checkBlock(string $file, MarkdownDocument $document, string $language, string $profileUrl): array
    {
        $block = $document->block();
        if (null === $block) {
            return [new Finding($file, 0, 'error', 'the standard B2B demo block is missing')];
        }
        $findings = [];
        $line = $document->blockStartLine();
        if ($document->blockCount() > 1) {
            $findings[] = new Finding($file, $line, 'error', 'the standard B2B demo block appears more than once');
        }
        if ($block !== StandardBlock::render($language, $profileUrl)) {
            $findings[] = new Finding($file, $line, 'error', 'the standard B2B demo block differs from the reference text (run bin/vitrine-readme apply)');
        }
        foreach (\explode("\n", $block) as $offset => $text) {
            $findings = [...$findings, ...$this->checkBlockLine($file, $line + $offset, $text, $profileUrl)];
        }

        return [...$findings, ...$this->probeLinks($file, $line, $block)];
    }

    /** @return list<Finding> */
    private function checkBlockLine(string $file, int $line, string $text, string $profileUrl): array
    {
        $findings = [];
        \preg_match_all('#https?://[^\s)>\]]+#', $text, $matches);
        foreach ($matches[0] as $url) {
            if (!\in_array(\rtrim($url, '.,;'), [StandardBlock::DEMO_URL, StandardBlock::LIVE_URL, $profileUrl], true)) {
                $findings[] = new Finding($file, $line, 'error', \sprintf('link outside the demo repository, the online demo and the author profile in the standard block: %s', $url));
            }
        }
        if (1 === \preg_match('/sylius plus/i', $text)) {
            $findings[] = new Finding($file, $line, 'error', 'forbidden wording in the standard block: Sylius Plus must not be named');
        }

        if (1 === \preg_match(self::COMMERCIAL, $text, $match)) {
            $findings[] = new Finding($file, $line, 'error', \sprintf('forbidden commercial wording "%s" in the standard block', $match[0]));
        }

        return $findings;
    }

    /** @return list<Finding> */
    private function probeLinks(string $file, int $line, string $block): array
    {
        if (null === $this->probe) {
            return [];
        }
        \preg_match_all('#https?://[^\s)>\]]+#', $block, $matches);
        $findings = [];
        foreach (\array_unique($matches[0]) as $url) {
            $status = $this->probed[$url] ??= $this->probe->status($url);
            if ($status >= 200 && $status < 400) {
                continue;
            }
            $unverifiable = \in_array($status, [403, 429, 999], true);
            $findings[] = new Finding($file, $line, $unverifiable ? 'warning' : 'error', \sprintf('link %s: %s', $unverifiable ? 'cannot be verified (the site refuses robots)' : 'does not answer', $url . ' (HTTP ' . $status . ')'));
        }

        return $findings;
    }

    /** @return list<Finding> */
    private function checkWording(string $file, MarkdownDocument $document): array
    {
        $findings = [];
        foreach ($document->lines as $index => $text) {
            if ($document->isInFence($index) || $document->isManaged($index)) {
                continue;
            }
            foreach (self::FORBIDDEN as $pattern) {
                if (1 === \preg_match($pattern, $text, $match)) {
                    $findings[] = new Finding($file, $index + 1, 'error', \sprintf('forbidden wording "%s" (tone charter)', $match[0]));
                }
            }
            if (1 === \preg_match(self::PLUS_NAME, $text) && 1 === \preg_match(self::COMPARISON, $text)) {
                $findings[] = new Finding($file, $index + 1, $this->strict ? 'error' : 'warning', 'existing text compares with Sylius Plus (tone charter, to decide)');
            }
        }

        return $findings;
    }

    /** @return list<Finding> */
    private function checkAnchors(string $file, MarkdownDocument $document): array
    {
        $anchors = $document->anchors();
        $findings = [];
        foreach ($document->links() as $link) {
            if (\str_starts_with($link['target'], '#') && !\in_array(\substr($link['target'], 1), $anchors, true)) {
                $findings[] = new Finding($file, $link['line'], 'error', \sprintf('the anchor %s matches no heading', $link['target']));
            }
        }

        return $findings;
    }

    /** @return list<Finding> */
    private function compare(MarkdownDocument $english, MarkdownDocument $french): array
    {
        $findings = [];
        $levels = static fn (MarkdownDocument $document): array => \array_column($document->headings(), 'level');
        if ($levels($english) !== $levels($french)) {
            $findings[] = new Finding(self::FRENCH, 0, 'error', 'heading structure differs from README.md (level or number of headings)');
        }
        foreach ($english->codeBlocks() as $index => $code) {
            if (($french->codeBlocks()[$index] ?? null) !== $code) {
                $findings[] = new Finding(self::FRENCH, 0, 'error', \sprintf('code block %d differs from README.md', $index + 1));
            }
        }
        if (\count($french->codeBlocks()) > \count($english->codeBlocks())) {
            $findings[] = new Finding(self::FRENCH, 0, 'error', 'README.fr.md has more code blocks than README.md');
        }

        return [...$findings, ...$this->compareTables($english, $french), ...$this->compareLinks($english, $french)];
    }

    /** @return list<Finding> */
    private function compareTables(MarkdownDocument $english, MarkdownDocument $french): array
    {
        $findings = [];
        $frenchTables = $french->tables();
        if (\count($frenchTables) !== \count($english->tables())) {
            $findings[] = new Finding(self::FRENCH, 0, 'error', \sprintf('README.fr.md has %d tables, README.md has %d', \count($frenchTables), \count($english->tables())));
        }
        foreach ($english->tables() as $index => $table) {
            $other = $frenchTables[$index] ?? null;
            if (null === $other) {
                continue;
            }
            if ($other['rows'] !== $table['rows']) {
                $findings[] = new Finding(self::FRENCH, $other['line'], 'error', \sprintf('table %d has %d rows, README.md has %d', $index + 1, $other['rows'], $table['rows']));
            }
            if ($other['columns'] !== $table['columns']) {
                $findings[] = new Finding(self::FRENCH, $other['line'], 'error', \sprintf('table %d has %d columns, README.md has %d', $index + 1, $other['columns'] - 1, $table['columns'] - 1));
            }
        }

        return $findings;
    }

    /** @return list<Finding> */
    private function compareLinks(MarkdownDocument $english, MarkdownDocument $french): array
    {
        $targets = static fn (MarkdownDocument $document): array => \array_values(\array_filter(
            \array_column($document->links(), 'target'),
            static fn (string $target): bool => !\str_starts_with($target, '#'),
        ));
        $expected = $targets($english);
        $actual = $targets($french);
        foreach ($expected as $index => $target) {
            if (($actual[$index] ?? null) !== $target) {
                return [new Finding(self::FRENCH, 0, 'error', \sprintf('link targets differ from README.md (link %d: %s, expected %s)', $index + 1, $actual[$index] ?? 'none', $target))];
            }
        }
        if (\count($actual) > \count($expected)) {
            return [new Finding(self::FRENCH, 0, 'error', \sprintf('link targets differ from README.md (link %d: %s, expected none)', \count($expected) + 1, $actual[\count($expected)]))];
        }

        return [];
    }
}
