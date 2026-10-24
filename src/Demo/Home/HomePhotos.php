<?php

declare(strict_types=1);

namespace App\Demo\Home;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** US-186: the photos of the B2B home, read from the credits file that sits next to the images (no external call). */
final class HomePhotos
{
    /** @var array<string, HomePhoto>|null */
    private ?array $photos = null;

    public function __construct(
        #[Autowire('%kernel.project_dir%/assets/shop/images/home/credits.json')]
        private readonly string $creditsPath,
    ) {
    }

    /** @return list<HomePhoto> */
    public function all(): array
    {
        return array_values($this->load());
    }

    public function get(string $slot): HomePhoto
    {
        return $this->load()[$slot] ?? throw new \InvalidArgumentException(\sprintf('No photo "%s" in %s.', $slot, $this->creditsPath));
    }

    /** @return array<string, HomePhoto> */
    private function load(): array
    {
        if (null !== $this->photos) {
            return $this->photos;
        }
        $data = json_decode((string) file_get_contents($this->creditsPath), true, 512, \JSON_THROW_ON_ERROR);
        $this->photos = [];
        foreach (\is_array($data) && \is_array($data['photos'] ?? null) ? $data['photos'] : [] as $row) {
            $photo = $this->photo(\is_array($row) ? $row : []);
            $this->photos[$photo->slot] = $photo;
        }

        return $this->photos;
    }

    /** @param array<mixed> $row */
    private function photo(array $row): HomePhoto
    {
        $files = array_map(
            static fn (mixed $file): HomePhotoFile => new HomePhotoFile(self::text($file, 'file'), (int) self::text($file, 'width'), (int) self::text($file, 'height')),
            \is_array($row['files'] ?? null) ? array_values($row['files']) : [],
        );
        usort($files, static fn (HomePhotoFile $a, HomePhotoFile $b): int => $a->width <=> $b->width);
        if ([] === $files) {
            throw new \UnexpectedValueException(\sprintf('The photo "%s" has no file.', self::text($row, 'slot')));
        }

        return new HomePhoto(
            self::text($row, 'slot'),
            $files,
            self::text($row, 'source'),
            self::text($row, 'origin_url'),
            self::text($row, 'creator'),
            self::text($row, 'creator_url'),
            self::text($row, 'license'),
            self::text($row, 'license_url'),
        );
    }

    private static function text(mixed $row, string $key): string
    {
        $value = \is_array($row) ? ($row[$key] ?? '') : '';

        return \is_scalar($value) ? (string) $value : '';
    }
}
