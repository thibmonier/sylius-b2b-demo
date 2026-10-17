<?php

declare(strict_types=1);

namespace App\Demo\Images\Fetch;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Searches CC0 images on Openverse (https://api.openverse.org, public API, no key). Used only by the manual fetch
 * script. The API filter is not trusted alone: every result is checked again and a non-CC0 one is dropped.
 */
final class OpenverseClient
{
    private const ENDPOINT = 'https://api.openverse.org/v1/images/';

    /** People, drawings and branding have no place in a product catalogue. */
    private const UNWANTED = '/\b(portrait|selfie|man|men|woman|women|girl|boy|child|children|people|person|worker|workers|model|face|humou?r|cartoon|comic|illustration|vector|clipart|clip art|drawing|painting|logo|poster|meme|collage|sticker|stamp|banknote)\b/';

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $userAgent,
        private readonly ?string $cacheDir = null,
        private readonly ?\Closure $pause = null,
    ) {
    }

    /** @return list<ImageCandidate> the CC0 results that look like the query, best match first */
    public function search(string $query): array
    {
        $candidates = $this->candidates($this->results($query, true), $query);
        if (\count($candidates) < 3) {
            $byId = [];
            foreach (\array_merge($candidates, $this->candidates($this->results($query, false), $query)) as $candidate) {
                $byId[$candidate->id] = $candidate;
            }
            $candidates = \array_values($byId);
        }

        return $candidates;
    }

    /** @return list<array<string, mixed>> */
    private function results(string $query, bool $photographOnly): array
    {
        $cacheFile = $this->cacheDir !== null ? $this->cacheDir . '/' . \sha1($query . ($photographOnly ? '|photo' : '')) . '.json' : null;
        if ($cacheFile !== null && \is_file($cacheFile)) {
            return $this->onlyObjects(\json_decode((string) \file_get_contents($cacheFile), true, 512, \JSON_THROW_ON_ERROR));
        }

        $options = ['q' => $query, 'license' => 'cc0', 'page_size' => 20, 'mature' => 'false', 'size' => 'medium,large'];
        if ($photographOnly) {
            $options['category'] = 'photograph';
        }
        if ($this->pause !== null) {
            ($this->pause)();
        }
        $data = $this->httpClient->request('GET', self::ENDPOINT, ['query' => $options, 'headers' => ['User-Agent' => $this->userAgent]])->toArray();
        $results = $this->onlyObjects($data['results'] ?? []);
        if ($cacheFile !== null) {
            \file_put_contents($cacheFile, \json_encode($results, \JSON_THROW_ON_ERROR));
        }

        return $results;
    }

    /** @return list<array<string, mixed>> */
    private function onlyObjects(mixed $value): array
    {
        $objects = [];
        foreach (\is_array($value) ? $value : [] as $item) {
            if (\is_array($item)) {
                $objects[] = \array_combine(\array_map('strval', \array_keys($item)), $item);
            }
        }

        return $objects;
    }

    /** @param array<string, mixed> $result */
    private function field(array $result, string $key): string
    {
        $value = $result[$key] ?? '';

        return \is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param list<array<string, mixed>> $results
     *
     * @return list<ImageCandidate>
     */
    private function candidates(array $results, string $query): array
    {
        $words = \array_map(static fn (string $word): string => \strlen($word) > 3 && !\str_ends_with($word, 'ss') ? \rtrim($word, 's') : $word, \explode(' ', \strtolower($query)));
        $scored = [];
        foreach ($results as $position => $result) {
            $candidate = $this->candidate($result);
            $text = $this->text($result);
            if ($candidate === null || \preg_match(self::UNWANTED, $text) === 1 || !\str_contains($text, \end($words))) {
                continue;
            }
            $scored[] = [$candidate, \count(\array_filter($words, static fn (string $word): bool => \str_contains($text, $word))), $position];
        }
        \usort($scored, static fn (array $a, array $b): int => [$b[1], $a[2]] <=> [$a[1], $b[2]]);

        return \array_map(static fn (array $entry): ImageCandidate => $entry[0], $scored);
    }

    /** @param array<string, mixed> $result */
    private function text(array $result): string
    {
        $tags = $result['tags'] ?? [];
        $names = [];
        foreach (\is_array($tags) ? $tags : [] as $tag) {
            $names[] = \is_array($tag) ? $this->field($this->onlyObjects([$tag])[0] ?? [], 'name') : '';
        }

        return \strtolower($this->field($result, 'title') . ' ' . \implode(' ', $names));
    }

    /** @param array<string, mixed> $result */
    private function candidate(array $result): ?ImageCandidate
    {
        $url = $this->field($result, 'url');
        $extension = \strtolower($this->field($result, 'filetype') ?: \pathinfo((string) \parse_url($url, \PHP_URL_PATH), \PATHINFO_EXTENSION));
        if (
            \strtolower($this->field($result, 'license')) !== 'cc0' ||
            !\str_contains($this->field($result, 'license_url'), 'publicdomain/zero') ||
            ($result['mature'] ?? false) === true ||
            !\str_starts_with($url, 'https://') ||
            ($extension !== '' && !\in_array($extension, self::ALLOWED_EXTENSIONS, true))
        ) {
            return null;
        }

        return new ImageCandidate(
            $this->field($result, 'id'),
            $this->field($result, 'title'),
            $this->field($result, 'creator'),
            'cc0',
            $this->field($result, 'license_url'),
            $url,
            $this->field($result, 'foreign_landing_url') ?: $url,
            $this->field($result, 'source'),
        );
    }
}
