# Veille hebdomadaire des versions du socle

> **US-239** : Automatisation de la veille des versions du socle de la démo (Composer, npm, Docker, GitHub Actions).

## Fonctionnement

Chaque **lundi à 09:00 UTC**, le workflow `.github/workflows/stack-versions-weekly.yml` :

1. Exécute `bin/stack-versions-report` pour générer un rapport Markdown
2. Crée ou met à jour une issue GitHub « Veille des versions — semaine YYYY-WW »
3. Applique le label `dependencies` à l'issue

Le rapport compare les versions installées aux dernières publiées et regroupe les mises à jour par type (correctif, mineur, majeur).

## Sections du rapport

### 📦 Dépendances Composer

Dépendances directes en retard, groupées par type de mise à jour :
- **Patch** (1.2.3 → 1.2.4) : corrections de bugs
- **Minor** (1.2.0 → 1.3.0) : nouvelles fonctionnalités compatibles
- **Major** (1.0.0 → 2.0.0) : breaking changes possibles

Pour chaque montée majeure, un brouillon de story est généré avec :
- La contrainte bloquante identifiée par `composer why-not`
- Les risques potentiels
- Les critères de fin

### 📦 Dépendances npm

Même structure que Composer, pour les dépendances JavaScript du projet.

### 🐳 Images Docker

Comparaison des images utilisées (Dockerfile, ci.yaml) avec les derniers tags disponibles :
- **Images à jour** : rien à faire
- **Images en retard** : mise à jour possible
- **Images volontairement ignorées** : avec justification (voir `.github/dependabot.yml`)

Exemples d'images ignorées :
- Node 26 (non-LTS, attendue LTS en 2027-10)
- MySQL 9 (contrainte FK cascades, erreur 6575 sur le reset de la démo)

### 🔄 PR Dependabot ouvertes

Liste des PR Dependabot en attente de fusion. Les PR mineures et correctives vertes peuvent être fusionnées directement.

### 📝 Brouillons de stories pour montées majeures

Pour chaque montée majeure bloquée par une contrainte (Sylius, PHPUnit, MySQL), un brouillon de story prêt à coller au backlog.

## Règle de traitement hebdomadaire

1. **Chaque lundi**, l'issue de la semaine est créée ou mise à jour automatiquement
2. **PR Dependabot mineures et correctives vertes** : fusion directe (GO permanent sur la démo)
3. **Cases cochées dans l'issue** : Thibaut les convertit en stories au backlog
4. **Montées majeures** : analyse de la contrainte bloquante, décision au cas par cas

## Déclenchement manuel

Le workflow peut aussi être déclenché manuellement via l'onglet « Actions » de GitHub :

```
Actions → Weekly stack versions report → Run workflow
```

Utile pour tester le workflow ou générer un rapport hors calendrier.

## Script `bin/stack-versions-report`

Le script CLI génère le rapport Markdown en interrogeant :
- `composer outdated --direct --format=json` pour les dépendances PHP
- `npm outdated --json` pour les dépendances JavaScript
- `gh pr list --state open --json number,title,author` pour les PR Dependabot
- `composer why-not <package> <version>` pour identifier les contraintes bloquantes

Le script est testé unitairement (`tests/Unit/Stack/VersionsReportTest.php`) et utilisable en local :

```bash
bin/stack-versions-report
```

## Sécurité

- **Permissions minimales** : le workflow a uniquement `issues: write` et `contents: read`
- **Aucun secret** : seul le `GITHUB_TOKEN` fourni par GitHub Actions est utilisé
- **Actions épinglées** : toutes les actions GitHub sont épinglées par SHA (US-208)
- **Aucun détail sensible** : l'issue publique ne contient aucune information sensible (pas de seuils, pas de routes admin, pas de secrets)

## Références

- **US-239** : `project-management/backlog/user-stories/US-239.md`
- **Workflow** : `.github/workflows/stack-versions-weekly.yml`
- **Script** : `bin/stack-versions-report`
- **Classe de support** : `src/Demo/Stack/VersionsReport.php`
- **Tests** : `tests/Unit/Stack/VersionsReportTest.php`
- **Configuration Dependabot** : `.github/dependabot.yml`
