<?php

declare(strict_types=1);

namespace App\Demo\Proof\Refresh;

use Symfony\Component\Process\Process;

/**
 * The public GitHub REST API through the `gh` command, which carries its own authentication (rate limit of an
 * account): no token is read, stored or passed by this code, and it only issues GET requests.
 */
final class GhCliApi implements GitHubApi
{
    public function json(string $path): array
    {
        $output = $this->run(['gh', 'api', $path]);

        try {
            $data = json_decode($output ?? '', true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SourceUnavailable('GitHub sent an unreadable answer for ' . $path, 0, $exception);
        }

        return \is_array($data) ? $data : throw new SourceUnavailable('GitHub sent an unexpected answer for ' . $path);
    }

    public function file(string $repository, string $path, string $ref): ?string
    {
        return $this->run(['gh', 'api', 'repos/' . $repository . '/contents/' . $path . '?ref=' . rawurlencode($ref), '-H', 'Accept: application/vnd.github.raw'], true);
    }

    /** @param list<string> $command */
    private function run(array $command, bool $notFoundIsNull = false): ?string
    {
        $process = new Process($command, null, null, null, 60);

        try {
            $process->run();
        } catch (\Throwable $exception) {
            throw new SourceUnavailable('gh is not usable: ' . $exception->getMessage(), 0, $exception);
        }
        if ($process->isSuccessful()) {
            return $process->getOutput();
        }
        if ($notFoundIsNull && str_contains($process->getErrorOutput() . $process->getOutput(), 'HTTP 404')) {
            return null;
        }

        throw new SourceUnavailable(\sprintf('gh api %s failed: %s', $command[2], trim($process->getErrorOutput()) ?: 'exit ' . $process->getExitCode()));
    }
}
