<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bin;

use App\Demo\Vitrine\DirectorySource;
use App\Demo\Vitrine\Finding;
use App\Demo\Vitrine\ReadmeApplier;
use App\Demo\Vitrine\ReadmeChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * US-064 / T-064-03: the standard B2B demo block, the cross-language links and the structural check of the French
 * README (bin/vitrine-readme). Everything runs on temporary directories; no plugin repository is touched.
 */
final class VitrineReadmeTest extends TestCase
{
    private const ENGLISH = <<<'MD'
        # Sylius Foo Plugin

        [![Build](https://example.com/build.svg)](https://example.com/build)

        **Foo for Sylius 2.**

        ## Features

        | Need | Feature |
        |------|---------|
        | Foo | The foo |

        See [installation](#installation) and the [docs](https://example.com/docs).

        ## Installation

        ```bash
        # Install
        composer require tmonier/sylius-foo-plugin
        ```

        ## License

        MIT.

        MD;

    private const FRENCH = <<<'MD'
        # Sylius Foo Plugin

        [![Build](https://example.com/build.svg)](https://example.com/build)

        **Foo pour Sylius 2.**

        ## Fonctionnalités

        | Besoin | Fonctionnalité |
        |--------|----------------|
        | Foo | Le foo |

        Voir l'[installation](#installation) et la [documentation](https://example.com/docs).

        ## Installation

        ```bash
        # Install
        composer require tmonier/sylius-foo-plugin
        ```

        ## Licence

        MIT.

        MD;

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/vitrine-' . bin2hex(random_bytes(4)) . '/sylius-foo-plugin';
        (new Filesystem())->mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(dirname($this->dir));
    }

    public function testApplyAddsTheLanguageLinkAndTheBlockToBothReadmes(): void
    {
        $this->write(self::ENGLISH, self::FRENCH);

        self::assertTrue((new ReadmeApplier())->apply($this->dir));

        $english = $this->read('README.md');
        $french = $this->read('README.fr.md');
        self::assertStringContainsString('Français : [README.fr.md](README.fr.md)', $this->head($english));
        self::assertStringContainsString('English: [README.md](README.md)', $this->head($french));
        self::assertStringContainsString("## B2B demo\n", $english);
        self::assertStringContainsString("## Démo B2B\n", $french);
        self::assertStringContainsString('https://github.com/thibmonier/sylius-b2b-demo', $english);
        self::assertLessThan(strpos($english, '## License'), strpos($english, '## B2B demo'), 'The block comes before the licence.');
        self::assertLessThan(strpos($french, '## Licence'), strpos($french, '## Démo B2B'));
    }

    public function testApplyIsIdempotent(): void
    {
        $this->write(self::ENGLISH, self::FRENCH);
        (new ReadmeApplier())->apply($this->dir);
        $english = $this->read('README.md');
        $french = $this->read('README.fr.md');

        self::assertFalse((new ReadmeApplier())->apply($this->dir));

        self::assertSame($english, $this->read('README.md'));
        self::assertSame($french, $this->read('README.fr.md'));
        self::assertSame(1, substr_count($english, 'vitrine:demo-b2b:start'));
        self::assertSame(1, substr_count($english, 'vitrine:lang'));
    }

    public function testApplyRefusesAMissingFrenchReadme(): void
    {
        file_put_contents($this->dir . '/README.md', self::ENGLISH);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('README.fr.md');

        (new ReadmeApplier())->apply($this->dir);
    }

    public function testThePluginAbsentFromTheDemoSaysSoAndPromisesNothing(): void
    {
        $dir = dirname($this->dir) . '/sylius-customer-asset-management-plugin';
        (new Filesystem())->mkdir($dir);
        file_put_contents($dir . '/README.md', self::ENGLISH);
        file_put_contents($dir . '/README.fr.md', self::FRENCH);

        (new ReadmeApplier())->apply($dir);

        self::assertStringContainsString('not present in the demo', (string) file_get_contents($dir . '/README.md'));
        self::assertStringContainsString('non présent dans la démo', (string) file_get_contents($dir . '/README.fr.md'));
        self::assertSame([], $this->errors($this->check($dir, 'sylius-customer-asset-management-plugin')));
    }

    public function testTheBlockIsAppendedWhenThereIsNoLicenceSection(): void
    {
        $this->write("# Foo\n\nIntro.\n\n## Usage\n\nText.\n", "# Foo\n\nIntro.\n\n## Utilisation\n\nTexte.\n");

        (new ReadmeApplier())->apply($this->dir);

        self::assertStringEndsWith("<!-- vitrine:demo-b2b:end -->\n", $this->read('README.md'));
        self::assertSame([], $this->errors($this->check()));
    }

    public function testATreatedPairPassesTheCheck(): void
    {
        $this->treated();

        self::assertSame([], $this->check());
    }

    public function testAMissingFrenchReadmeFailsTheCheck(): void
    {
        file_put_contents($this->dir . '/README.md', self::ENGLISH);

        self::assertContains('README.fr.md: missing', $this->messages($this->check()));
    }

    public function testAMissingLanguageLinkFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace('English: [README.md](README.md) <!-- vitrine:lang -->', '', $this->read('README.fr.md')));

        self::assertContains('README.fr.md: the link to the other language is missing from the top of the file', $this->messages($this->check()));
    }

    public function testAMissingBlockFailsTheCheck(): void
    {
        $this->write(self::ENGLISH, self::FRENCH);

        $messages = $this->messages($this->check());

        self::assertContains('README.md: the standard B2B demo block is missing', $messages);
        self::assertContains('README.fr.md: the standard B2B demo block is missing', $messages);
    }

    public function testAnEditedBlockFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace('La démo est un exemple', 'La démo est un très bel exemple', $this->read('README.fr.md')));

        self::assertContains('README.fr.md: the standard B2B demo block differs from the reference text (run bin/vitrine-readme apply)', $this->messages($this->check()));
    }

    public function testADifferentHeadingStructureFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace("## Installation\n", "### Installation\n", $this->read('README.fr.md')));

        self::assertContains('README.fr.md: heading structure differs from README.md (level or number of headings)', $this->messages($this->check()));
    }

    public function testAHeadingInsideACodeBlockIsNotAHeading(): void
    {
        $english = str_replace("# Install\n", "# Install\n## not a heading\n", self::ENGLISH);
        $french = str_replace("# Install\n", "# Install\n## not a heading\n", self::FRENCH);
        $this->write($english, $french);
        (new ReadmeApplier())->apply($this->dir);

        self::assertSame([], $this->errors($this->check()));
    }

    public function testATranslatedCodeBlockFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace('# Install', '# Installer', $this->read('README.fr.md')));

        self::assertContains('README.fr.md: code block 1 differs from README.md', $this->messages($this->check()));
    }

    public function testADifferentTableShapeFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace("| Foo | Le foo |\n", "| Foo | Le foo |\n| Bar | Le bar |\n", $this->read('README.fr.md')));

        self::assertContains('README.fr.md: table 1 has 4 rows, README.md has 3', $this->messages($this->check()));
    }

    public function testALinkTargetThatChangedFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace('https://example.com/docs', 'https://example.com/docs-fr', $this->read('README.fr.md')));

        self::assertContains('README.fr.md: link targets differ from README.md (link 3: https://example.com/docs-fr, expected https://example.com/docs)', $this->messages($this->check()));
    }

    public function testAnAnchorToAMissingHeadingFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace("## Installation\n", "## Mise en place\n", $this->read('README.fr.md')));

        self::assertContains('README.fr.md: the anchor #installation matches no heading', $this->messages($this->check()));
        self::assertSame([15], array_values(array_map(static fn (Finding $finding): int => $finding->line, array_filter($this->check(), static fn (Finding $finding): bool => str_contains($finding->message, 'anchor')))));
    }

    public function testAForbiddenTermFailsTheCheckWithFileAndLine(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.fr.md', str_replace('Voir l\'', "Une alternative gratuite.\nVoir l'", $this->read('README.fr.md')));

        $findings = array_filter($this->check(), static fn (Finding $finding): bool => 'error' === $finding->severity && str_contains($finding->message, 'forbidden'));

        self::assertCount(1, $findings);
        $finding = array_values($findings)[0];
        self::assertSame('README.fr.md', $finding->file);
        self::assertSame(15, $finding->line);
    }

    public function testASiteLinkInTheBlockFailsTheCheck(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.md', str_replace('<!-- vitrine:demo-b2b:end -->', "More: https://my-site.example/contact\n<!-- vitrine:demo-b2b:end -->", $this->read('README.md')));

        $messages = $this->messages($this->check());

        self::assertContains('README.md: the standard B2B demo block differs from the reference text (run bin/vitrine-readme apply)', $messages);
        self::assertTrue($this->hasMessageContaining($messages, 'link outside the demo repository'));
    }

    public function testAComparisonWithPlusInExistingTextIsAWarningUnlessStrict(): void
    {
        $this->write(
            str_replace('**Foo for Sylius 2.**', '**Foo for Sylius 2.** An MIT alternative for merchants without Sylius Plus.', self::ENGLISH),
            self::FRENCH,
        );
        (new ReadmeApplier())->apply($this->dir);

        $findings = $this->check();

        self::assertSame([], $this->errors($findings));
        self::assertCount(1, array_filter($findings, static fn (Finding $finding): bool => 'warning' === $finding->severity && 7 === $finding->line));
        self::assertNotSame([], $this->errors((new ReadmeChecker(true))->check(new DirectorySource($this->dir), 'sylius-foo-plugin')));
    }

    public function testSylusPlusInTheBlockIsAnError(): void
    {
        $this->treated();
        file_put_contents($this->dir . '/README.md', str_replace('<!-- vitrine:demo-b2b:end -->', "Better than Sylius Plus.\n<!-- vitrine:demo-b2b:end -->", $this->read('README.md')));

        self::assertTrue($this->hasMessageContaining($this->messages($this->check()), 'forbidden'));
    }

    private function treated(): void
    {
        $this->write(self::ENGLISH, self::FRENCH);
        (new ReadmeApplier())->apply($this->dir);
    }

    private function write(string $english, string $french): void
    {
        file_put_contents($this->dir . '/README.md', $english);
        file_put_contents($this->dir . '/README.fr.md', $french);
    }

    private function read(string $file): string
    {
        return (string) file_get_contents($this->dir . '/' . $file);
    }

    private function head(string $content): string
    {
        return implode("\n", array_slice(explode("\n", $content), 0, 6));
    }

    /**
     * @return list<Finding>
     */
    private function check(?string $dir = null, string $plugin = 'sylius-foo-plugin'): array
    {
        return (new ReadmeChecker())->check(new DirectorySource($dir ?? $this->dir), $plugin);
    }

    /**
     * @param list<Finding> $findings
     *
     * @return list<Finding>
     */
    private function errors(array $findings): array
    {
        return array_values(array_filter($findings, static fn (Finding $finding): bool => 'error' === $finding->severity));
    }

    /**
     * @param list<Finding> $findings
     *
     * @return list<string>
     */
    private function messages(array $findings): array
    {
        return array_map(static fn (Finding $finding): string => $finding->file . ': ' . $finding->message, $this->errors($findings));
    }

    /**
     * @param list<string> $messages
     */
    private function hasMessageContaining(array $messages, string $needle): bool
    {
        foreach ($messages as $message) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
