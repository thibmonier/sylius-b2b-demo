# Tâches : US-182 : Garde : les `code` des tableaux de la démo se coupent à toutes les largeurs

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) sur téléphone
- **Story Points** : 1 (Should, sacrifice : non sacrifiable (action 2 de la rétro du sprint 25))
- **Sprint** : sprint-26-vitrine-et-flux
- **Worker proposé** : w185 (après US-185 ou en cas de sacrifice d'US-185) ou w186 si disponible
- **Story** : [US-182](../../../backlog/user-stories/US-182.md)

## Résumé de la US

**En tant que** visiteuse sur téléphone  
**Je veux** que les tableaux des pages de la démo tiennent dans l'écran  
**Afin de** lire une page sans défilement horizontal

## Règles et contraintes

- Garde d'architecture sans base ni réseau sur `templates/demo/` : toute page dont un tableau contient du `code` déclare, hors media query, `overflow-wrap: anywhere` pour ces `code` (ou hérite d'une règle commune).
- Les pages que la garde trouve non conformes sont corrigées dans la même story.
- Tests uniquement par `bin/run-tests` ; commit de tests rouges seul ; `php bin/proof-refresh` puis `php bin/proof-check` (nouveau fichier de tests).
- Contrôle en production à la clôture : largeur des pages à tableau à 360 px et 1280 px, 0 défaut d'affichage (indicateur de l'action 2 de la rétro du sprint 25).
- Règles du sprint, consigne de worker et actions de la rétro du sprint 25 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Garde en CI ; contrôle de largeur **en production après déploiement** (lecture seule, 360 px et 1280 px).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `tests/Unit/Architecture/` (nouveau fichier de garde)
- `templates/demo/*.html.twig` (corrections éventuelles : `packs`, `compatibility`, `proof`, `integrate`, …)
- `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-182-01 | [DOC] | Cadrage : pages de `templates/demo/` dont un tableau contient du `code`, règles `overflow-wrap` actuelles (en ligne, dans `<style>`, hors media query) | 0,25 h | - | Claude | ✅ |
| T-182-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : garde d'architecture sur `templates/demo/` (page conforme, sans `code`, règle globale, règle absente, règle seulement dans une media query) | 0,75 h | T-182-01 | Claude | ✅ |
| T-182-03 | [FE-WEB] | Pages que la garde trouve non conformes corrigées (règle hors media query sur les `code` des tableaux) | 0,5 h | T-182-02 | Claude | ✅ |
| T-182-04 | [TEST] | Suite ciblée par `bin/run-tests` (`WORKER_ID=w182 …`), ECS, PHPStan max, Rector ; dernières lignes citées | 0,25 h | T-182-03 | Claude | ✅ |
| T-182-05 | [TEST] | Contrôle en production après déploiement : largeur des pages à tableau à 360 px et 1280 px (lecture seule), 0 défaut d'affichage | 0,25 h | merge de la PR | Claude | 🔲 |

**État à la clôture (2026-10-24) : T-182-05 reste 🔲.** Le contrôle de largeur en production n'est pas consigné dans le dépôt ; à confirmer par une mesure en lecture seule (360 px et 1280 px).

**Total estimé : 2 h** (5 tâches ; 2 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Page conforme (nominal) | `TableCodeWrapGuardTest::testAPageWithTheRuleOutsideAMediaQueryIsCompliant` ; page réelle : `testEveryPageOfTheDemoWithCodeInATableWrapsIt` (vert après correction de `accounts` et du simulateur PunchOut) |
| Tableau sans code (alternatif) | `TableCodeWrapGuardTest::testAPageWhoseTablesHaveNoCodeIsNotConcerned` |
| Règle globale (alternatif) | `TableCodeWrapGuardTest::testACommonRuleCoversThePagesThatInheritTheLayout` |
| Règle absente (erreur) | `TableCodeWrapGuardTest::testAPageWithoutTheRuleIsNamed` ; contrôle PHP direct sur HEAD avant correction : `accounts.html.twig`, `punchout_simulator/simulator.html.twig` nommés |
| Règle seulement sur mobile (erreur) | `TableCodeWrapGuardTest::testARuleOnlyInAMediaQueryDoesNotCount` (et `testARuleOnTheCellDoesNotCountBecauseBootstrapOverridesIt`) |

Run : `WORKER_ID=w185 TEST_DB_NAME=b2b_w185 TEST_CACHE_DIR=var/cache-w185 bin/run-tests tests/Unit/Architecture` : `OK (13 tests, 13 assertions)`. Rouge : classe de garde absente au commit `f0f5d2f` (contrôle PHP direct, pas de retour de commit dans l'arbre partagé). T-182-05 (production) après le merge.
