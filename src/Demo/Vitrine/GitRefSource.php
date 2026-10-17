<?php

declare(strict_types=1);

namespace App\Demo\Vitrine;

use Symfony\Component\Process\Process;

/**
 * Reads a file as it is on a branch (`git show <ref>:<file>`), without checking anything out.
 */
final readonly class GitRefSource implements FileSource
{
    public function __construct(private string $repository, private string $ref)
    {
    }

    public function read(string $name): ?string
    {
        $process = new Process(['git', '-C', $this->repository, 'show', $this->ref . ':' . $name]);
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }
}
