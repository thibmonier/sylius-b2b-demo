<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * T-TECH-58-04: the CI skips its heavy jobs when a pull request only changes follow-up documents. Any doubt runs
 * everything: an empty list, a path outside the documentation, a workflow file.
 */
final class CiChangedScopeTest extends TestCase
{
    /** @param list<string> $paths */
    #[DataProvider('documentationOnly')]
    public function testAPullRequestThatOnlyChangesDocumentationIsDocs(array $paths): void
    {
        self::assertSame('docs', $this->scope($paths));
    }

    /** @return iterable<string, array{list<string>}> */
    public static function documentationOnly(): iterable
    {
        yield 'sprint follow-up' => [['.bmad/sprint-status.yaml', 'project-management/sprints/sprint-58-donnees-files-cron/board.md']];
        yield 'docs and their images' => [['docs/captures-vitrine.md', 'docs/images/platform-health-fr-light-1280.png']];
        yield 'markdown anywhere' => [['README.md', 'README.fr.md', 'config/demo/NOTES.md']];
        yield 'blank lines ignored' => [['', 'docs/erp-demo.md', '']];
    }

    /** @param list<string> $paths */
    #[DataProvider('codeOrDoubt')]
    public function testAnythingElseRunsTheWholeCi(array $paths): void
    {
        self::assertSame('code', $this->scope($paths));
    }

    /** @return iterable<string, array{list<string>}> */
    public static function codeOrDoubt(): iterable
    {
        yield 'one source file among documents' => [['project-management/backlog/README.md', 'src/Demo/Packs/PacksReader.php']];
        yield 'a workflow' => [['.github/workflows/ci.yaml']];
        yield 'demo configuration read by the tests' => [['config/demo/proof.json']];
        yield 'a prefix that only looks like a docs folder' => [['docs.php', 'src/docs/Thing.php', 'project-management-old/x.php']];
        yield 'a markdown-like extension' => [['templates/demo/page.md.twig']];
        yield 'nothing changed' => [[]];
    }

    /** @param list<string> $paths */
    private function scope(array $paths): string
    {
        $process = new Process([\dirname(__DIR__, 3) . '/bin/ci-changed-scope']);
        $process->setInput(implode("\n", $paths) . ([] === $paths ? '' : "\n"));
        $process->mustRun();

        return trim($process->getOutput());
    }
}
