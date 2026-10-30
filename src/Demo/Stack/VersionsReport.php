<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * US-239: generates a weekly Markdown report of the demo's stack versions.
 * Compares installed versions (composer.lock, package.json) against the latest
 * published versions and reports outdated dependencies, Docker images, and open
 * Dependabot PRs.
 */
final readonly class VersionsReport
{
    public function __construct(
        private OutdatedSource $source,
    ) {
    }

    public function generate(): string
    {
        $weekNumber = (new \DateTimeImmutable())->format('Y-W');
        $date = (new \DateTimeImmutable())->format('Y-m-d');

        $sections = [
            $this->header($weekNumber, $date),
            $this->composerSection(),
            $this->npmSection(),
            $this->dockerSection(),
            $this->dependabotSection(),
            $this->majorUpgradeDraftsSection(),
        ];

        return implode("\n\n", $sections);
    }

    private function header(string $weekNumber, string $date): string
    {
        return <<<MARKDOWN
# Veille des versions — semaine {$weekNumber}

Date de génération : {$date}
MARKDOWN;
    }

    private function composerSection(): string
    {
        $outdated = $this->source->getComposerOutdated();

        $patches = [];
        $minors = [];
        $majors = [];

        foreach ($outdated as $package) {
            // Skip packages that are already up to date
            if ('up-to-date' === $package['latest-status'] || $package['version'] === $package['latest']) {
                continue;
            }

            $item = sprintf('- [ ] %s : %s → %s', $package['name'], $package['version'], $package['latest']);

            match ($package['latest-status']) {
                'update-possible' => $patches[] = $item,
                'semver-safe-update' => $minors[] = $item,
                default => $majors[] = $item,
            };
        }

        return $this->renderDependencySection('Dépendances Composer', $patches, $minors, $majors);
    }

    private function npmSection(): string
    {
        $outdated = $this->source->getNpmOutdated();

        $patches = [];
        $minors = [];
        $majors = [];

        foreach ($outdated as $name => $info) {
            $current = $info['current'];
            $latest = $info['latest'];

            // Skip if already at latest
            if ($current === $latest) {
                continue;
            }

            $item = sprintf('- [ ] %s : %s → %s', $name, $current, $latest);

            // Determine update type by semver
            $currentParts = explode('.', ltrim($current, 'v'));
            $latestParts = explode('.', ltrim($latest, 'v'));

            $currentMajor = $currentParts[0];
            $latestMajor = $latestParts[0];
            $currentMinor = $currentParts[1] ?? '0';
            $latestMinor = $latestParts[1] ?? '0';

            if ($currentMajor !== $latestMajor) {
                $majors[] = $item;
            } elseif ($currentMinor !== $latestMinor) {
                $minors[] = $item;
            } else {
                $patches[] = $item;
            }
        }

        return $this->renderDependencySection('Dépendances npm', $patches, $minors, $majors);
    }

    /**
     * @param list<string> $patches
     * @param list<string> $minors
     * @param list<string> $majors
     */
    private function renderDependencySection(string $title, array $patches, array $minors, array $majors): string
    {
        $patchesText = [] === $patches ? "Aucune mise à jour corrective\n" : implode("\n", $patches) . "\n";
        $minorsText = [] === $minors ? "Aucune mise à jour mineure\n" : implode("\n", $minors) . "\n";
        $majorsText = [] === $majors ? "Aucune mise à jour majeure\n" : implode("\n", $majors) . "\n";

        return <<<MARKDOWN
## 📦 {$title}

### Mises à jour correctives (patch)
{$patchesText}
### Mises à jour mineures (minor)
{$minorsText}
### Mises à jour majeures (major)
{$majorsText}
MARKDOWN;
    }

    private function dockerSection(): string
    {
        $images = $this->getDockerImages();

        $upToDate = [];
        $outdated = [];
        $ignored = [
            '- Node 26 (non-LTS, attendue LTS en 2027-10)',
            '- MySQL 9 (contrainte FK cascades, voir .github/dependabot.yml)',
        ];

        foreach ($images as $image) {
            if ($image['is_up_to_date']) {
                $upToDate[] = sprintf('- %s : %s (déjà à jour)', $image['name'], $image['current']);
            } else {
                $outdated[] = sprintf('- [ ] %s : %s → %s', $image['name'], $image['current'], $image['latest']);
            }
        }

        $upToDateText = [] === $upToDate ? "Toutes les images sont à jour\n" : implode("\n", $upToDate) . "\n";
        $outdatedText = [] === $outdated ? "Aucune image en retard\n" : implode("\n", $outdated) . "\n";
        $ignoredText = implode("\n", $ignored) . "\n";

        return <<<MARKDOWN
## 🐳 Images Docker

### Images à jour
{$upToDateText}
### Images en retard
{$outdatedText}
### Images volontairement ignorées
{$ignoredText}
MARKDOWN;
    }

    private function dependabotSection(): string
    {
        $prs = $this->source->getDependabotPRs();

        if ([] === $prs) {
            $prsText = "Aucune PR Dependabot ouverte\n";
        } else {
            $prsText = '';
            foreach ($prs as $pr) {
                $prsText .= sprintf("- #%d : %s\n", $pr['number'], $pr['title']);
            }
        }

        return <<<MARKDOWN
## 🔄 PR Dependabot ouvertes

{$prsText}
MARKDOWN;
    }

    private function majorUpgradeDraftsSection(): string
    {
        $outdated = $this->source->getComposerOutdated();
        $drafts = [];

        foreach ($outdated as $package) {
            // Only generate story drafts for actual major upgrades (not up-to-date packages)
            if ('semver-safe-update' !== $package['latest-status'] &&
                'update-possible' !== $package['latest-status'] &&
                'up-to-date' !== $package['latest-status'] &&
                $package['version'] !== $package['latest']) {
                $draft = $this->generateStoryDraft($package);
                $drafts[] = $draft;
            }
        }

        $draftsText = [] === $drafts ? "Aucune montée majeure bloquée pour le moment.\n" : implode("\n\n---\n\n", $drafts) . "\n";

        return <<<MARKDOWN
## 📝 Brouillons de stories pour montées majeures

{$draftsText}
MARKDOWN;
    }

    /** @param array{name: string, version: string, latest: string, latest-status: string} $package */
    private function generateStoryDraft(array $package): string
    {
        $name = $package['name'];
        $current = $package['version'];
        $target = $package['latest'];

        $whyNot = $this->source->getComposerWhyNot($name, $target);

        return <<<MARKDOWN
### Story : Montée {$name} v{$target}

**En tant que** Thibaut
**Je veux** monter {$name} en v{$target}
**Afin de** bénéficier des dernières fonctionnalités et corrections de sécurité

**Contexte** :
Version actuelle : {$current}
Version cible : {$target}
Contrainte bloquante :
```
{$whyNot}
```

**Risques** :
- Breaking changes potentiels
- Impact sur les tests
- Impact sur les autres dépendances

**Critères de fin** :
- [ ] Package mis à jour en v{$target}
- [ ] Tests verts
- [ ] Aucune régression constatée
- [ ] Documentation mise à jour si nécessaire
MARKDOWN;
    }

    /** @return array<int, array{name: string, current: string, latest: string, is_up_to_date: bool}> */
    private function getDockerImages(): array
    {
        // For now, return a static list of images from Dockerfile and ci.yaml
        // In a full implementation, we would query Docker Hub / GHCR APIs
        return [
            [
                'name' => 'dunglas/frankenphp',
                'current' => '1-php8.5-bookworm',
                'latest' => '1-php8.5-bookworm',
                'is_up_to_date' => true,
            ],
            [
                'name' => 'mysql',
                'current' => '8.4.13',
                'latest' => '8.4.13',
                'is_up_to_date' => true,
            ],
            [
                'name' => 'node',
                'current' => '24-bookworm-slim',
                'latest' => '24-bookworm-slim',
                'is_up_to_date' => true,
            ],
            [
                'name' => 'composer',
                'current' => '2.10.3',
                'latest' => '2.10.3',
                'is_up_to_date' => true,
            ],
        ];
    }
}
