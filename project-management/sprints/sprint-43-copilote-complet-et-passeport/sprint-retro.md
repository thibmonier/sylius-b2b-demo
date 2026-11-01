# Rétrospective - Sprint 43

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-11-01 |
| Durée | 30 min |
| Format | Starfish (continuité inter-sprints) |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeur Claude (orchestrateur, workers) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

- **Sprint Goal** : compléter le copilote d'achat avec les garde-fous et l'adaptateur LLM réel, et ajouter le lien passeport numérique dans les factures Factur-X.
- **Capacité** : 13 points — 3 stories (US-214, US-216, US-234).
- **Résultat** : 13/13 points livrés (100 % vélocité).
- **Releases plugin** : copilot v1.3.0 → v1.3.1 (patch config), facturx v1.2.0.

## Observations (Starfish)

### Continuer
- Architecture ports/adapters dans les plugins : le `GuardedCopilotModel` en décorateur et le `FallbackCopilotAdapter` s'intègrent naturellement sans modifier le code existant.
- L'utilisation de `Extension::prepend()` pour injecter programmatiquement les configs framework/monolog, au lieu de fichiers YAML avec des références de paramètres.
- Réutilisation de code existant : US-234 a pu être livrée rapidement car la feature DPP du plugin facturx existait déjà localement, seul le service pont restait à créer côté démo.

### Commencer
- **Checklist pré-tag** : vérifier systématiquement `git status` + `git diff` avant de taguer un plugin, pour éviter qu'un fichier modifié localement échappe au commit (comme `config/config.yaml` dans v1.3.0).
- **Vérification namespace autoload-dev** : lire le `composer.json` `autoload-dev` du plugin avant de créer des fichiers de test, pour utiliser le bon préfixe de namespace.

### Arrêter
- Taguer sans vérifier que le `config/config.yaml` est cohérent avec la stratégie DI choisie (prepend programmatique vs import YAML).

### Plus de
- Vérification locale du conteneur (`lint:container`) avant de pousser une PR de la démo, pour détecter les erreurs de résolution de paramètres en amont.

### Moins de
- Corrections post-tag (le patch v1.3.1 aurait été évitable avec une checklist pré-tag).

## Suivi des actions de la rétrospective du sprint 42

| # | Action | Résultat au sprint 43 |
|---|--------|-----------------------|
| T-TECH-43-02 | Tests fonctionnels plugin avant PR démo | ✅ Done — copilot : 116 tests OK, facturx : 116 tests OK |
| T-TECH-43-03 | Pousser plugin + tag avant PR démo | ✅ Done — copilot v1.3.1, facturx v1.2.0 poussés |
| T-TECH-43-04 | Report T-TECH-42-03 (montant maquette Copilote) | ⏭ Reporté au sprint 44 (pas touché ce sprint) |

## Métriques du sprint

| Indicateur | S41 | S42 | **S43** |
|------------|-----|-----|---------|
| Points livrés / engagés | 13/13 | 11/11 | **13/13** |
| Stories en review | 2 | 3 | **0** |
| Commits fonctionnels (démo) | 3 | 8 | **3** |
| Tests ajoutés (plugin) | 2 | 16 | **8** |
| Forks / workers | 3 | 2 | **1** |
| Pushes forcés | 0 | 0 | **0** |
| Tests retirés ou affaiblis | 0 | 0 | **0** |
| Corrections post-worker | 1 | 0 | **3** (namespace, PHPStan, config) |
| Actions de rétro réalisées | 2/3 | 2/3 | **2/3** |
| Gate score initial | — | 81/100 | **—** |

## Actions (SMART)

| # | Action | Type | Responsable | Deadline |
|---|--------|------|-------------|----------|
| A-S43-01 | Avant chaque tag de plugin, exécuter `git status` + `git diff` pour vérifier que tous les fichiers modifiés localement sont commités | PROCESS | Claude (orchestrateur) | Immédiat, permanent |
| A-S43-02 | Pour tout plugin utilisant `prepend()`, le `config/config.yaml` ne doit jamais référencer de paramètres définis dans `load()` (commentaires uniquement ou configs autonomes) | PROCESS | Claude (orchestrateur) | Immédiat, permanent |
| A-S43-03 | Reporter T-TECH-43-04 (montant maquette Copilote) au sprint 44 | BACKLOG | PO | Sprint 44 planning |
