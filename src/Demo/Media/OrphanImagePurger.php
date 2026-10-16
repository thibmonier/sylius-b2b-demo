<?php

declare(strict_types=1);

namespace App\Demo\Media;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Removes the files of the image directory that no image row points to (US-040, M4). The back-office account uploads
 * images and the fixture reload gives its own images new random paths, while the purger of the reload deletes rows
 * only: without this step the disk of the demo grows with every night.
 *
 * Runs after the reload is committed, so a failure here never corrupts the data: it is logged and reported, the
 * reset then fails visibly (/healthz).
 */
final readonly class OrphanImagePurger
{
    public function __construct(
        private ImagePathSource $source,
        private Filesystem $filesystem,
        private LoggerInterface $logger,
        #[Autowire('%sylius_core.images_dir%')]
        private string $directory,
    ) {
    }

    public function purge(): MediaPurgeReport
    {
        if (!is_dir($this->directory)) {
            return new MediaPurgeReport(0, []);
        }

        $known = [];
        foreach ($this->source->paths() as $path) {
            $known[ltrim($path, '/')] = true;
        }

        $deleted = 0;
        $failed = [];
        foreach ((new Finder())->files()->in($this->directory)->ignoreDotFiles(true) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            if (isset($known[$relative])) {
                continue;
            }
            if ($this->remove($file->getPathname(), $relative)) {
                ++$deleted;
            } else {
                $failed[] = $relative;
            }
        }
        $this->removeEmptyDirectories();

        return new MediaPurgeReport($deleted, $failed);
    }

    private function remove(string $path, string $relative): bool
    {
        try {
            $this->filesystem->remove($path);
        } catch (IOExceptionInterface $exception) {
            $this->logger->error('Orphan image could not be removed', ['path' => $relative, 'exception' => $exception->getMessage()]);

            return false;
        }

        return true;
    }

    private function removeEmptyDirectories(): void
    {
        $directories = array_keys(iterator_to_array((new Finder())->directories()->in($this->directory)->ignoreDotFiles(false)->sortByName()->reverseSorting(), true));
        foreach ($directories as $directory) {
            if (!(new \FilesystemIterator($directory))->valid()) {
                @rmdir($directory);
            }
        }
    }
}
