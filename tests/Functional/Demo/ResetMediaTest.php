<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * US-040 / M4: the nightly reset leaves no image on the disk that the database does not know. Run against the test
 * database loaded with the `demo` suite and the test image directory (var/media-test, config/packages/test).
 */
final class ResetMediaTest extends KernelTestCase
{
    use DatabaseAccess;

    private const ORPHAN = 'zz/zz/uploaded-by-sophie.webp';

    private string $imagesDirectory = '';

    protected function setUp(): void
    {
        self::bootKernel();
        $directory = self::getContainer()->getParameter('sylius_core.images_dir');
        \assert(\is_string($directory));
        $this->imagesDirectory = $directory;
        self::assertStringContainsString('media-test', $directory, 'The tests never touch the images of a real database.');
    }

    protected function tearDown(): void
    {
        @unlink(self::marker());
        (new Filesystem())->remove($this->imagesDirectory . '/' . self::ORPHAN);
        parent::tearDown();
    }

    public function testAnImageUploadedByTheVisitorsIsPurgedAndTheImagesOfTheFixturesStay(): void
    {
        $this->writeOrphan();

        $tester = $this->reset();

        self::assertSame(0, $tester->getStatusCode(), $tester->getDisplay() . $tester->getErrorOutput());
        self::assertFileDoesNotExist($this->imagesDirectory . '/' . self::ORPHAN);
        $paths = $this->db()->fetchFirstColumn('SELECT path FROM sylius_product_image UNION SELECT path FROM sylius_taxon_image');
        // The product images of the demo suite (US-047) are rows like any other: the purge must keep their files.
        self::assertNotEmpty($paths, 'The demo suite loads the product images.');
        foreach ($paths as $path) {
            self::assertIsString($path);
            self::assertFileExists($this->imagesDirectory . '/' . ltrim($path, '/'), 'The image of a fixture must stay.');
        }
    }

    public function testAFileThatCannotBeRemovedIsLoggedAndHealthzReportsIt(): void
    {
        $this->writeOrphan();
        // A directory named like the orphan cannot be unlinked as a file: the removal fails whoever runs the test.
        (new Filesystem())->remove($this->imagesDirectory . '/' . self::ORPHAN);
        mkdir($this->imagesDirectory . '/' . self::ORPHAN . '/inside', 0o777, true);
        touch($this->imagesDirectory . '/' . self::ORPHAN . '/inside/locked');
        chmod($this->imagesDirectory . '/' . self::ORPHAN . '/inside', 0o555);

        $tester = $this->reset();

        chmod($this->imagesDirectory . '/' . self::ORPHAN . '/inside', 0o755);
        if (0 === $tester->getStatusCode()) {
            self::markTestSkipped('The file system lets this user remove a read-only directory (root).');
        }
        self::assertNotSame(0, $tester->getStatusCode());
        self::assertStringContainsString('DEMO_RESET_FAILED', $tester->getErrorOutput());
        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM sylius_channel WHERE code = ?', ['B2B']), 'The data is not corrupted.');
        self::assertFileExists(self::marker(), 'The failure stays visible to /healthz.');
    }

    private function writeOrphan(): void
    {
        (new Filesystem())->mkdir(\dirname($this->imagesDirectory . '/' . self::ORPHAN));
        file_put_contents($this->imagesDirectory . '/' . self::ORPHAN, 'orphan');
    }

    private function reset(): CommandTester
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('app:demo:reset'));
        $tester->execute(['--no-media' => true, '--no-cache' => true, '--no-interaction' => true], ['capture_stderr_separately' => true]);

        return $tester;
    }

    private static function marker(): string
    {
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        \assert(\is_string($projectDir));

        return $projectDir . '/var/demo-reset-failed';
    }
}
