<?php

declare(strict_types=1);

namespace App\Tests\Unit\Docker;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class MediaDirectoryPreparationTest extends TestCase
{
    private string $media;

    private string $project;

    protected function setUp(): void
    {
        $this->project = \dirname(__DIR__, 3);
        $this->media = sys_get_temp_dir() . '/media-prep-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        @chmod($this->media, 0o755);
        (new Filesystem())->remove($this->media);
    }

    public function testAnEmptyVolumeGetsTheImageAndCacheDirectories(): void
    {
        mkdir($this->media, 0o755, true);

        $process = $this->prepare();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertDirectoryExists($this->media . '/image');
        self::assertDirectoryExists($this->media . '/cache');
    }

    public function testExistingImagesAreKept(): void
    {
        mkdir($this->media . '/image/ab', 0o755, true);
        file_put_contents($this->media . '/image/ab/keep.jpg', 'x');

        $process = $this->prepare();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertFileExists($this->media . '/image/ab/keep.jpg');
    }

    public function testAMissingDirectoryIsCreated(): void
    {
        $process = $this->prepare();

        self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        self::assertDirectoryExists($this->media . '/image');
    }

    public function testANonWritableDirectoryFailsWithAnExplicitMessage(): void
    {
        if (0 === posix_geteuid()) {
            self::markTestSkipped('root can write anywhere.');
        }
        mkdir($this->media, 0o555, true);

        $process = $this->prepare();

        self::assertNotSame(0, $process->getExitCode());
        self::assertStringContainsString('not writable', $process->getErrorOutput());
        self::assertStringContainsString('volume', $process->getErrorOutput());
    }

    /** Regression of 2026-10-19: the image and cache directories created by root must be handed over too, not only the media root. */
    public function testRootHandsOverTheMediaRootAndBothSubdirectories(): void
    {
        $script = (string) file_get_contents(\dirname(__DIR__, 3) . '/docker/prepare-media.sh');

        self::assertStringContainsString('for directory in "$media" "$media/image" "$media/cache"', $script);
        self::assertStringContainsString('chown -R www-data:www-data "$directory" || fail', $script);
    }

    public function testTheEntrypointPreparesTheMediaThenDropsRootPrivileges(): void
    {
        $entrypoint = (string) file_get_contents($this->project . '/docker/entrypoint.sh');

        self::assertStringContainsString('docker/prepare-media.sh /app/public/media', $entrypoint);
        self::assertStringContainsString('setpriv --reuid=www-data --regid=www-data --init-groups', $entrypoint);
        self::assertLessThan(
            (int) strpos($entrypoint, 'app:demo:preflight'),
            (int) strpos($entrypoint, 'docker/prepare-media.sh'),
            'The volume must be prepared before anything touches it.',
        );
    }

    public function testTheImageStartsAsRootSoThatTheEntrypointCanFixTheVolumeOwner(): void
    {
        $dockerfile = (string) file_get_contents($this->project . '/docker/Dockerfile');
        $final = substr($dockerfile, (int) strpos($dockerfile, 'FROM base AS final'));

        self::assertDoesNotMatchRegularExpression('/^USER /m', $final);
    }

    private function prepare(): Process
    {
        $process = new Process(['sh', $this->project . '/docker/prepare-media.sh', $this->media]);
        $process->run();

        return $process;
    }
}
