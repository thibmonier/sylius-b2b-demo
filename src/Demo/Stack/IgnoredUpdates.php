<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * Exclusions volontaires de la veille hebdomadaire des versions (demande de Thibaut du 2026-11-22).
 *
 * La veille re-listait chaque semaine des paquets dont la décision était pourtant prise
 * (apexcharts abandonnée pour sa licence, la chaîne de build en attente de Sylius), faute
 * d'une liste d'exclusion pour Composer et npm — les images Docker en avaient une, codée en dur.
 *
 * Une entrée sans échéance est une décision définitive. Une entrée datée cesse de s'appliquer
 * une fois l'échéance passée : le paquet réapparaît alors dans la veille, ce qui évite qu'une
 * exclusion pourrisse en silence.
 */
final readonly class IgnoredUpdates
{
    /** @param array<string, list<array{package: string, reason: string, story?: string, until?: string}>> $entries */
    public function __construct(private array $entries = [])
    {
    }

    public static function fromFile(string $path, \DateTimeImmutable $now): self
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException(sprintf('Fichier d\'exclusions illisible : %s', $path));
        }

        /** @var array<string, string|list<array{package: string, reason: string, story?: string, until?: string}>> $decoded */
        $decoded = json_decode((string) file_get_contents($path), true, flags: \JSON_THROW_ON_ERROR);
        // The `_comment` key explains the file to the humans who edit it: only the ecosystems (lists) matter.
        $data = array_filter($decoded, is_array(...));

        $today = $now->format('Y-m-d');
        $entries = [];
        foreach ($data as $ecosystem => $list) {
            // Dates are ISO (Y-m-d): the comparison is lexical, and the review day itself still counts.
            $entries[$ecosystem] = array_values(array_filter($list, static fn (array $entry): bool => !isset($entry['until']) || $entry['until'] >= $today));
        }

        return new self($entries);
    }

    public function ignores(string $ecosystem, string $package): bool
    {
        return array_any($this->entries[$ecosystem] ?? [], static fn (array $entry): bool => $entry['package'] === $package);
    }

    /** @return list<string> */
    public function lines(string $ecosystem): array
    {
        return array_map(static function (array $entry): string {
            $details = array_filter([$entry['story'] ?? null, isset($entry['until']) ? 'jusqu\'au ' . $entry['until'] : null]);

            return sprintf('- %s : %s%s', $entry['package'], $entry['reason'], [] === $details ? '' : ' (' . implode(', ', $details) . ')');
        }, $this->entries[$ecosystem] ?? []);
    }
}
