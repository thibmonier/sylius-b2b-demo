<?php

declare(strict_types=1);

namespace App\Demo\Stack;

/**
 * PHP major-upgrade section of the weekly watch (ADR-0021). The evaluation of 2026-11-20 found the
 * ecosystem ready and the infrastructure not: no FrankenPHP image for the target, and one locked
 * package excluding it. This section re-runs both checks every Monday so the question is answered by
 * the report instead of by a fresh investigation.
 */
final readonly class PhpUpgradeWatchSection
{
    public function __construct(
        private string $phpVersion = '8.6.0',
        private string $imageTag = 'php8.6',
    ) {
    }

    public function render(PhpUpgradeWatchSource $source): string
    {
        $tags = $source->frankenPhpTagCount($this->imageTag);
        $excluding = $source->packagesExcluding($this->phpVersion);
        $target = $this->shortVersion();

        $lines = [
            $this->imageLine($tags, $target),
            $this->packagesLine($excluding, $target),
        ];
        $verdict = 0 < $tags && [] === $excluding
            ? \sprintf('**Les deux verrous sont levés.** Rouvrir l\'ADR-0021 et planifier la montée vers PHP %s.', $target)
            : \sprintf('Montée vers PHP %s toujours bloquée. Aucune action cette semaine.', $target);

        return \sprintf("## 🐘 Montée PHP %s\n\n%s\n\n%s\n", $target, implode("\n", $lines), $verdict);
    }

    private function imageLine(int $tags, string $target): string
    {
        if (0 === $tags) {
            return \sprintf('- [ ] **Image FrankenPHP** : aucun tag `%s` publié. Les images PHP %s officielles n\'ont pas de variante ZTS, que FrankenPHP exige.', $this->imageTag, $target);
        }

        return \sprintf('- [x] **Image FrankenPHP** : %d tag(s) `%s` publié(s).', $tags, $this->imageTag);
    }

    /** @param array<string, string> $excluding */
    private function packagesLine(array $excluding, string $target): string
    {
        if ([] === $excluding) {
            return \sprintf('- [x] **Dépendances** : aucun paquet verrouillé n\'exclut PHP %s.', $target);
        }

        $details = implode(', ', array_map(
            static fn (string $name, string $constraint): string => \sprintf('`%s` (%s)', $name, $constraint),
            array_keys($excluding),
            $excluding,
        ));

        return \sprintf('- [ ] **Dépendances** : %d paquet(s) excluent PHP %s — %s', \count($excluding), $target, $details);
    }

    private function shortVersion(): string
    {
        return implode('.', \array_slice(explode('.', $this->phpVersion), 0, 2));
    }
}
