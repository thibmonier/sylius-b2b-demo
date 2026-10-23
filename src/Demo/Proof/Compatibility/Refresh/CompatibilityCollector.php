<?php

declare(strict_types=1);

namespace App\Demo\Proof\Compatibility\Refresh;

use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\GitHubApi;
use App\Demo\Proof\Refresh\SourceUnavailable;
use Symfony\Component\Clock\ClockInterface;

/**
 * Reads, for one plugin repository and through the public GitHub API only, what the compatibility page shows (US-171):
 * the links to other plugins (composer.json) and the versions its CI runs (build matrix). A repository that cannot be
 * read keeps its last verified values, marked stale, with their old date.
 */
final readonly class CompatibilityCollector
{
    private const array KEPT_KEYS = ['dependencies', 'ci'];

    public function __construct(
        private GitHubApi $github,
        private ClockInterface $clock,
        private string $owner = 'thibmonier',
    ) {
    }

    /** @param array<array-key, mixed> $previous the facts of the previous generation */
    public function plugin(string $slug, array $previous = []): CompatibilityFacts
    {
        $repository = $this->owner . '/' . $slug;

        try {
            return new CompatibilityFacts($this->facts($repository), []);
        } catch (SourceUnavailable) {
            return new CompatibilityFacts(self::kept($previous), ['github']);
        }
    }

    /** @return array<string, mixed> */
    private function facts(string $repository): array
    {
        $branch = $this->github->json('repos/' . $repository)['default_branch'] ?? null;
        if (!\is_string($branch) || 1 !== preg_match('/^[\w.\-]+$/', $branch)) {
            throw new SourceUnavailable('No default branch for ' . $repository);
        }
        $composer = $this->github->file($repository, 'composer.json', $branch) ?? throw new SourceUnavailable($repository . ' has no composer.json');
        $links = ComposerLinks::parse($composer);
        $workflow = $this->workflow($repository, $branch);
        $facts = ['dependencies' => $links + $this->stamp($this->blob($repository, $branch, 'composer.json'))];
        $axes = null === $workflow ? null : CiMatrix::axes($workflow['content']);
        if (null !== $axes && [] !== array_filter($axes)) {
            $facts['ci'] = $axes + $this->stamp($this->blob($repository, $branch, '.github/workflows/' . $workflow['name']));
        }
        $facts['fingerprint'] = Fingerprint::of($composer, $workflow['content'] ?? null);

        return $facts;
    }

    /** @return array{name: string, content: string}|null */
    private function workflow(string $repository, string $branch): ?array
    {
        $names = [];
        foreach ($this->github->json('repos/' . $repository . '/contents/.github/workflows?ref=' . $branch) as $entry) {
            if (\is_array($entry) && \is_string($entry['name'] ?? null) && 1 === preg_match('/^[\w.\-]+\.ya?ml$/', $entry['name'])) {
                $names[] = $entry['name'];
            }
        }
        $name = CiWorkflow::preferred($names);
        $content = null === $name ? null : $this->github->file($repository, '.github/workflows/' . $name, $branch);

        return null === $name || null === $content ? null : ['name' => $name, 'content' => $content];
    }

    /**
     * @param array<array-key, mixed> $previous
     *
     * @return array<string, mixed>
     */
    private static function kept(array $previous): array
    {
        $kept = [];
        foreach (self::KEPT_KEYS as $key) {
            if (\is_array($previous[$key] ?? null)) {
                $kept[$key] = ['stale' => true] + $previous[$key];
            }
        }
        if ([] !== $kept && \is_string($previous['fingerprint'] ?? null)) {
            $kept['fingerprint'] = $previous['fingerprint'];
        }

        return $kept;
    }

    /** @return array{source: string, checked_at: string} */
    private function stamp(string $source): array
    {
        if (!ProofReader::isPublicSource($source)) {
            throw new SourceUnavailable('Refusing a source that is not public: ' . $source);
        }

        return ['source' => $source, 'checked_at' => $this->clock->now()->format('Y-m-d')];
    }

    private function blob(string $repository, string $branch, string $path): string
    {
        return 'https://github.com/' . $repository . '/blob/' . $branch . '/' . $path;
    }
}
