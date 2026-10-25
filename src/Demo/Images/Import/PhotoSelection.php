<?php

declare(strict_types=1);

namespace App\Demo\Images\Import;

/**
 * US-196: the photos chosen for the catalogue (fixtures/images/selection.json), one per type of article, plus the types
 * still waiting for a photo: their SKUs keep the image they have.
 */
final readonly class PhotoSelection
{
    /**
     * @param list<SelectedPhoto> $photos
     * @param list<string>        $pendingSkus
     * @param list<array{type: string, skus: list<string>, file: string}> $neutral types shown by a neutral visual of the category
     */
    private function __construct(
        private array $photos,
        private array $pendingSkus,
        private array $neutral,
    ) {
    }

    public static function load(string $path): self
    {
        $data = self::map(\json_decode((string) \file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR));

        $photos = [];
        foreach (self::list($data['photos'] ?? []) as $item) {
            $photo = self::map($item);
            $focus = self::list($photo['focus'] ?? []);
            $photos[] = new SelectedPhoto(
                self::string($photo['type'] ?? null),
                self::strings($photo['skus'] ?? null),
                self::string($photo['file'] ?? null),
                self::string($photo['title'] ?? null),
                self::string($photo['creator'] ?? null),
                self::string($photo['origin_url'] ?? null),
                self::string($photo['source'] ?? null),
                self::string($photo['license_url'] ?? null),
                self::float($focus[0] ?? 0.5),
                self::float($focus[1] ?? 0.5),
                \array_map(static fn (mixed $area): array => self::area($area), self::list($photo['blur'] ?? [])),
                self::string($photo['review'] ?? null),
            );
        }
        $pending = [];
        foreach (self::list($data['pending'] ?? []) as $type) {
            $pending = [...$pending, ...self::strings(self::map($type)['skus'] ?? null)];
        }

        $neutral = [];
        foreach (self::list($data['neutral'] ?? []) as $item) {
            $type = self::map($item);
            $neutral[] = ['type' => self::string($type['type'] ?? null), 'skus' => self::strings($type['skus'] ?? null), 'file' => self::string($type['file'] ?? null)];
        }

        return new self($photos, $pending, $neutral);
    }

    /** @return list<SelectedPhoto> */
    public function photos(): array
    {
        return $this->photos;
    }

    /** @return list<string> */
    public function pendingSkus(): array
    {
        return $this->pendingSkus;
    }

    /** @return list<array{type: string, skus: list<string>, file: string}> */
    public function neutral(): array
    {
        return $this->neutral;
    }

    /** @return array<string, mixed> */
    private static function map(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \RuntimeException('The photo selection is malformed: an object was expected.');
        }

        return \array_filter($value, \is_string(...), \ARRAY_FILTER_USE_KEY);
    }

    /** @return list<mixed> */
    private static function list(mixed $value): array
    {
        return \is_array($value) ? \array_values($value) : [];
    }

    private static function string(mixed $value): string
    {
        return \is_string($value) ? $value : throw new \RuntimeException('The photo selection is malformed: a text was expected.');
    }

    /** @return list<string> */
    private static function strings(mixed $value): array
    {
        return \array_map(self::string(...), self::list($value));
    }

    private static function float(mixed $value): float
    {
        return \is_int($value) || \is_float($value) ? (float) $value : throw new \RuntimeException('The photo selection is malformed: a number was expected.');
    }

    /** @return array{float, float, float, float} */
    private static function area(mixed $value): array
    {
        $area = self::list($value);

        return [self::float($area[0] ?? null), self::float($area[1] ?? null), self::float($area[2] ?? null), self::float($area[3] ?? null)];
    }
}
