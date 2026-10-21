<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Proof\Refresh;

use App\Demo\Proof\Refresh\GitHubApi;
use App\Demo\Proof\Refresh\PackagistApi;
use App\Demo\Proof\Refresh\SourceUnavailable;

/** In-memory Packagist and GitHub: a value, null (404) or a throwable per request. */
final class FakeSources implements GitHubApi, PackagistApi
{
    /** @var list<string> */
    public array $requests = [];

    /**
     * @param array<string, array<array-key, mixed>|\Throwable|null> $packages by package name
     * @param array<string, array<array-key, mixed>|\Throwable>      $json     by REST path
     * @param array<string, string|\Throwable|null>                  $files    by "repository:path"
     */
    public function __construct(
        private readonly array $packages = [],
        private readonly array $json = [],
        private readonly array $files = [],
    ) {
    }

    public function package(string $name): ?array
    {
        $this->requests[] = 'packagist:' . $name;

        if (!\array_key_exists($name, $this->packages)) {
            throw new SourceUnavailable('no answer for ' . $name);
        }

        return self::answer($this->packages[$name]);
    }

    public function json(string $path): array
    {
        $this->requests[] = 'github:' . $path;
        $answer = self::answer($this->json[$path] ?? throw new SourceUnavailable('no answer for ' . $path));

        return $answer ?? throw new SourceUnavailable('empty answer for ' . $path);
    }

    public function file(string $repository, string $path, string $ref): ?string
    {
        $this->requests[] = 'file:' . $repository . ':' . $path;
        $key = $repository . ':' . $path;
        $answer = \array_key_exists($key, $this->files) ? $this->files[$key] : null;
        if ($answer instanceof \Throwable) {
            throw $answer;
        }

        return $answer;
    }

    /**
     * @param array<array-key, mixed>|\Throwable|null $answer
     *
     * @return array<array-key, mixed>|null
     */
    private static function answer(array|\Throwable|null $answer): ?array
    {
        if ($answer instanceof \Throwable) {
            throw $answer;
        }

        return $answer;
    }
}
