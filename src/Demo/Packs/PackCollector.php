<?php

declare(strict_types=1);

namespace App\Demo\Packs;

use App\Demo\Proof\ProofReader;
use App\Demo\Proof\Refresh\GitHubApi;
use App\Demo\Proof\Refresh\SourceUnavailable;
use Symfony\Component\Clock\ClockInterface;

/**
 * Reads, for one pack repository and through the public GitHub API only, what the packs page shows (US-178): the plugins
 * and their bounds (composer.json), the install command, the roles and the steps left by hand (both READMEs). A repository
 * that cannot be read, or whose README disagrees with its composer.json, keeps its last verified values, marked stale, with
 * their old date.
 */
final readonly class PackCollector
{
    private const string PLUGIN_PREFIX = 'tmonier/';

    public function __construct(
        private GitHubApi $github,
        private ClockInterface $clock,
        private string $owner = 'thibmonier',
    ) {
    }

    /** @param array<array-key, mixed> $previous the entry of the previous generation */
    public function pack(string $name, array $previous = []): PackFacts
    {
        $repository = $this->owner . '/' . $name;

        try {
            return new PackFacts($this->facts($repository), []);
        } catch (SourceUnavailable) {
            return new PackFacts([] === $previous ? [] : ['stale' => true] + $previous, ['github']);
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
        $english = $this->github->file($repository, 'README.md', $branch) ?? throw new SourceUnavailable($repository . ' has no README.md');
        $french = $this->github->file($repository, 'README.fr.md', $branch) ?? throw new SourceUnavailable($repository . ' has no README.fr.md');
        $definition = self::definition($composer);
        $en = ReadmeTables::parse($english);
        $fr = ReadmeTables::parse($french);
        $name = $definition['name'];
        if ($en['command'] !== 'composer require ' . $name) {
            throw new SourceUnavailable('The install command of the README is not the name of the package ' . $name);
        }
        $facts = [
            'composer' => $name,
            'command' => $en['command'],
            'title' => ['en' => self::capitalized($en['title'] ?? throw new SourceUnavailable('No title in README.md')), 'fr' => self::capitalized($fr['title'] ?? throw new SourceUnavailable('No title in README.fr.md'))],
            'summary' => ['en' => $en['summary'] ?? '', 'fr' => $fr['summary'] ?? ''],
            'plugins' => $this->plugins($definition['require'], $en['plugins'], $fr['plugins']),
            'suggests' => $this->suggests($definition['suggest'], $en['suggests'], $fr['suggests']),
        ];
        $facts['steps'] = $this->steps($en['steps'], $fr['steps'], array_column($facts['plugins'], 'slug'));

        return $facts + [
            'source' => $this->blob($repository, $branch, 'README.md'),
            'composer_source' => $this->blob($repository, $branch, 'composer.json'),
            'checked_at' => $this->clock->now()->format('Y-m-d'),
            'fingerprint' => PackFingerprint::of($composer, $english, $french),
        ];
    }

    /** The README titles follow the name of the repository ("compliance and tooling"); the page shows them as headings. */
    private static function capitalized(string $title): string
    {
        return mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1);
    }

    /** @return array{name: string, require: array<string, string>, suggest: list<string>} */
    private static function definition(string $composerJson): array
    {
        try {
            $data = json_decode($composerJson, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SourceUnavailable('composer.json is not readable: ' . $exception->getMessage(), 0, $exception);
        }
        $name = \is_array($data) ? ($data['name'] ?? null) : null;
        if (!\is_string($name)) {
            throw new SourceUnavailable('composer.json has no name');
        }

        return ['name' => $name, 'require' => self::pluginMap($data['require'] ?? null), 'suggest' => array_keys(self::pluginMap($data['suggest'] ?? null))];
    }

    /** @return array<string, string> plugin slug => bound (or description), in the order of the section */
    private static function pluginMap(mixed $section): array
    {
        $found = [];
        foreach (\is_array($section) ? $section : [] as $package => $value) {
            if (str_starts_with((string) $package, self::PLUGIN_PREFIX)) {
                $found[substr((string) $package, \strlen(self::PLUGIN_PREFIX))] = \is_string($value) ? $value : '';
            }
        }

        return $found;
    }

    /**
     * @param array<string, string>                                        $required
     * @param list<array{slug: string, bound: string, role: string}>       $en
     * @param list<array{slug: string, bound: string, role: string}>       $fr
     *
     * @return list<array{slug: string, bound: string, role: array{en: string, fr: string}}>
     */
    private function plugins(array $required, array $en, array $fr): array
    {
        $listed = array_column($en, 'slug');
        $slugs = array_keys($required);
        sort($slugs);
        $sortedEn = $listed;
        sort($sortedEn);
        $sortedFr = array_column($fr, 'slug');
        sort($sortedFr);
        if ($slugs !== $sortedEn || $slugs !== $sortedFr) {
            throw new SourceUnavailable('The plugins of the READMEs are not the ones of the composer.json');
        }
        $roles = array_column($fr, 'role', 'slug');
        $plugins = [];
        foreach ($en as $plugin) {
            $plugins[] = ['slug' => $plugin['slug'], 'bound' => $required[$plugin['slug']], 'role' => ['en' => $plugin['role'], 'fr' => $roles[$plugin['slug']]]];
        }

        return $plugins;
    }

    /**
     * @param list<string>                                    $suggested
     * @param list<array{slug: string, reason: string}>       $en
     * @param list<array{slug: string, reason: string}>       $fr
     *
     * @return list<array{slug: string, reason: array{en: string, fr: string}}>
     */
    private function suggests(array $suggested, array $en, array $fr): array
    {
        $english = array_column($en, 'reason', 'slug');
        $french = array_column($fr, 'reason', 'slug');

        return array_map(static fn (string $slug): array => ['slug' => $slug, 'reason' => ['en' => $english[$slug] ?? '', 'fr' => $french[$slug] ?? '']], $suggested);
    }

    /**
     * @param list<array{slug: string, bundles: list<string>, config: list<string>, routes: list<string>, migrations: bool, other: string}> $en
     * @param list<array{slug: string, bundles: list<string>, config: list<string>, routes: list<string>, migrations: bool, other: string}> $fr
     * @param list<string>                                                                                                                  $members
     *
     * @return list<array{slug: string, bundles: list<string>, config: list<string>, routes: list<string>, migrations: bool, other: array{en: string, fr: string}}>
     */
    private function steps(array $en, array $fr, array $members): array
    {
        if (array_column($en, 'slug') !== array_column($fr, 'slug')) {
            throw new SourceUnavailable('The steps of the two READMEs are not the same');
        }
        if ([] !== array_diff(array_column($en, 'slug'), $members)) {
            throw new SourceUnavailable('A step of the README is for a plugin that is not in the pack');
        }
        $steps = [];
        foreach ($en as $index => $step) {
            $steps[] = [
                'slug' => $step['slug'],
                'bundles' => $step['bundles'],
                'config' => $step['config'],
                'routes' => $step['routes'],
                'migrations' => $step['migrations'],
                'other' => ['en' => $step['other'], 'fr' => $fr[$index]['other']],
            ];
        }

        return $steps;
    }

    private function blob(string $repository, string $branch, string $path): string
    {
        $source = 'https://github.com/' . $repository . '/blob/' . $branch . '/' . $path;

        return ProofReader::isPublicSource($source) ? $source : throw new SourceUnavailable('Refusing a source that is not public: ' . $source);
    }
}
