<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * US-186: the rules every photo of the home must follow, as a list of problems that name the file and the rule.
 * Shared by the test of the real directory and the test that proves the audit reports a bad one.
 */
final class HomePhotoAudit
{
    /** Heaviest file allowed: the largest variant of the hero (the budget of the story is 150 to 250 KB per photo). */
    public const MAX_BYTES = 250_000;

    private const REQUIRED_CREDIT_FIELDS = ['source', 'origin_url', 'creator', 'license', 'license_url'];

    /** @return list<string> */
    public static function problems(string $directory): array
    {
        $problems = [];
        $credited = [];
        $credits = self::credits($directory);
        foreach ($credits as $photo) {
            $slot = self::string($photo['slot'] ?? '');
            foreach (self::REQUIRED_CREDIT_FIELDS as $field) {
                if ('' === self::string($photo[$field] ?? '')) {
                    $problems[] = $slot . ': the credit has no ' . $field;
                }
            }
            foreach (['origin_url', 'license_url'] as $field) {
                if (!str_starts_with(self::string($photo[$field] ?? ''), 'https://')) {
                    $problems[] = $slot . ': ' . $field . ' is not an https URL';
                }
            }
            $files = \is_array($photo['files'] ?? null) ? $photo['files'] : [];
            if (\count($files) < 2) {
                $problems[] = $slot . ': fewer than two sizes for the srcset';
            }
            foreach ($files as $file) {
                $file = \is_array($file) ? $file : [];
                $name = self::string($file['file'] ?? '');
                $credited[] = $name;
                $path = $directory . '/' . $name;
                $problems = [...$problems, ...self::fileProblems($path, $name, (int) self::string($file['width'] ?? 0), (int) self::string($file['height'] ?? 0))];
            }
        }
        foreach (scandir($directory) ?: [] as $name) {
            if (1 === preg_match('/\.(?:webp|jpe?g|png|avif)$/', $name) && !\in_array($name, $credited, true)) {
                $problems[] = $name . ': no credit (source, author, licence) in credits.json';
            }
        }

        return $problems;
    }

    /** @return list<string> */
    private static function fileProblems(string $path, string $name, int $width, int $height): array
    {
        if (!is_file($path)) {
            return [$name . ': listed in credits.json but absent from the directory'];
        }
        $problems = [];
        if (!str_ends_with($name, '.webp')) {
            $problems[] = $name . ': not a modern format (WebP expected)';
        }
        if (filesize($path) > self::MAX_BYTES) {
            $problems[] = $name . ': heavier than ' . self::MAX_BYTES . ' bytes';
        }
        $size = getimagesize($path);
        if (false === $size || $size[0] !== $width || $size[1] !== $height) {
            $problems[] = $name . ': the dimensions of credits.json do not match the file';
        }

        return $problems;
    }

    /** @return list<array<mixed>> */
    private static function credits(string $directory): array
    {
        $file = $directory . '/credits.json';
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
        $photos = \is_array($data) && \is_array($data['photos'] ?? null) ? $data['photos'] : [];

        return array_values(array_filter($photos, '\is_array'));
    }

    private static function string(mixed $value): string
    {
        return \is_scalar($value) ? (string) $value : '';
    }
}
