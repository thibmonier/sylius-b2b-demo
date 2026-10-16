<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Media;

use App\Demo\Media\ImagePathSource;
use App\Demo\Media\OrphanImagePurger;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/** US-040 / M4: the uploads that no image row points to are purged, those of the fixtures stay. */
final class OrphanImagePurgerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/orphan-images-' . bin2hex(random_bytes(4));
        mkdir($this->directory . '/0d/ed', 0o777, true);
        mkdir($this->directory . '/ab/cd', 0o777, true);
        touch($this->directory . '/.gitkeep');
        touch($this->directory . '/0d/ed/fixture.webp');
        touch($this->directory . '/ab/cd/uploaded-by-sophie.webp');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->directory);
    }

    public function testAnOrphanIsRemovedAndTheImagesOfTheFixturesStay(): void
    {
        $report = $this->purger(['0d/ed/fixture.webp'])->purge();

        self::assertFileDoesNotExist($this->directory . '/ab/cd/uploaded-by-sophie.webp');
        self::assertFileExists($this->directory . '/0d/ed/fixture.webp');
        self::assertFileExists($this->directory . '/.gitkeep');
        self::assertSame(1, $report->deleted);
        self::assertFalse($report->hasFailures());
    }

    public function testTheDirectoryOfAnOrphanIsRemovedWithIt(): void
    {
        $this->purger(['0d/ed/fixture.webp'])->purge();

        self::assertDirectoryDoesNotExist($this->directory . '/ab');
        self::assertDirectoryExists($this->directory . '/0d/ed');
    }

    public function testAPathStoredWithALeadingSlashIsStillKept(): void
    {
        $this->purger(['/0d/ed/fixture.webp'])->purge();

        self::assertFileExists($this->directory . '/0d/ed/fixture.webp');
    }

    public function testAFileThatCannotBeRemovedIsLoggedAndReportedWithoutStoppingThePurge(): void
    {
        touch($this->directory . '/ab/cd/second-orphan.webp');
        $logger = new class() extends AbstractLogger {
            /** @var list<string> */
            public array $errors = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                if ('error' === $level) {
                    $this->errors[] = (string) $message . ' ' . json_encode($context);
                }
            }
        };
        $stubborn = new class() extends Filesystem {
            /** @param string|iterable<string> $files */
            public function remove(string|iterable $files): void
            {
                if (\is_string($files) && str_ends_with($files, 'uploaded-by-sophie.webp')) {
                    throw new IOException('Failed to remove file.');
                }
                parent::remove($files);
            }
        };

        $report = (new OrphanImagePurger($this->source(['0d/ed/fixture.webp']), $stubborn, $logger, $this->directory))->purge();

        self::assertTrue($report->hasFailures());
        self::assertSame(['ab/cd/uploaded-by-sophie.webp'], $report->failed);
        self::assertSame(1, $report->deleted, 'The other orphan is purged anyway.');
        self::assertCount(1, $logger->errors);
        self::assertStringContainsString('uploaded-by-sophie.webp', $logger->errors[0]);
        self::assertFileExists($this->directory . '/0d/ed/fixture.webp');
    }

    public function testAMissingDirectoryIsNotAnError(): void
    {
        $purger = new OrphanImagePurger($this->source([]), new Filesystem(), new \Psr\Log\NullLogger(), $this->directory . '/absent');

        self::assertSame(0, $purger->purge()->deleted);
    }

    /** @param list<string> $paths */
    private function purger(array $paths): OrphanImagePurger
    {
        return new OrphanImagePurger($this->source($paths), new Filesystem(), new \Psr\Log\NullLogger(), $this->directory);
    }

    /** @param list<string> $paths */
    private function source(array $paths): ImagePathSource
    {
        return new readonly class($paths) implements ImagePathSource {
            /** @param list<string> $paths */
            public function __construct(private array $paths)
            {
            }

            public function paths(): iterable
            {
                return $this->paths;
            }
        };
    }
}
