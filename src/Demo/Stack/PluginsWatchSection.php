<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * Plugins section of the weekly version watch (2026-11-03): red default branches first, then the open Dependabot
 * pull requests by what to do with them. Minor and patch updates with green checks are merged under the standing GO;
 * majors and groups need a decision or a story.
 */
final readonly class PluginsWatchSection
{
    private const array LABELS = [
        DependabotPullRequest::CHECKS_SUCCESS => 'CI vert',
        DependabotPullRequest::CHECKS_FAILURE => 'CI rouge',
        DependabotPullRequest::CHECKS_PENDING => 'CI en cours',
    ];

    public function __construct(private string $owner)
    {
    }

    /** @param list<PluginRepositoryStatus> $statuses */
    public function render(array $statuses): string
    {
        $red = array_filter($statuses, static fn (PluginRepositoryStatus $s): bool => false === $s->mainGreen);
        $unknown = array_filter($statuses, static fn (PluginRepositoryStatus $s): bool => null === $s->mainGreen);
        [$ready, $decide, $blocked] = $this->sort($statuses);
        $count = array_sum(array_map(static fn (PluginRepositoryStatus $s): int => \count($s->pullRequests), $statuses));

        $parts = [
            "## 🧩 Plugins\n\n" . \sprintf('%d dépôts suivis · %d PR Dependabot ouvertes · %d `main` rouge', \count($statuses), $count, \count($red)) . "\n",
            $this->list('`main` rouge', array_map(fn (PluginRepositoryStatus $s): string => \sprintf('- [ ] %s : https://github.com/%s/%s/actions?query=branch%%3Amain', $s->repository, $this->owner, $s->repository), $red), 'Aucun'),
        ];
        if ([] !== $unknown) {
            $parts[] = $this->list('État de `main` inconnu', array_map(static fn (PluginRepositoryStatus $s): string => '- ' . $s->repository, $unknown), '');
        }
        $parts[] = $this->list('Prêtes à merger (minor/patch, CI vert)', $ready, 'Aucune');
        $parts[] = $this->list('Majeures ou groupes : décision ou story', $decide, 'Aucune');
        $parts[] = $this->list('Minor/patch en échec ou en cours', $blocked, 'Aucune');

        return implode("\n", $parts);
    }

    /**
     * @param list<PluginRepositoryStatus> $statuses
     *
     * @return array{list<string>, list<string>, list<string>} ready to merge, to decide, red or pending
     */
    private function sort(array $statuses): array
    {
        $buckets = [[], [], []];
        foreach ($statuses as $status) {
            foreach ($status->pullRequests as $pr) {
                $item = \sprintf('- [ ] %s#%d : %s', $status->repository, $pr->number, $pr->title);
                $major = \in_array(SemverUpdate::ofDependabotTitle($pr->title), ['major', null], true);
                $green = DependabotPullRequest::CHECKS_SUCCESS === $pr->checks;
                match (true) {
                    $major => $buckets[1][] = $item . ' (' . self::LABELS[$pr->checks] . ')',
                    $green => $buckets[0][] = $item,
                    default => $buckets[2][] = $item . ' (' . self::LABELS[$pr->checks] . ')',
                };
            }
        }

        return $buckets;
    }

    /** @param array<string> $items */
    private function list(string $title, array $items, string $empty): string
    {
        return "### $title\n" . ([] === $items ? $empty : implode("\n", $items)) . "\n";
    }
}
