# Sprint Review - Sprint 33 (Indexation des pages vitrine et chaîne d'approvisionnement des dépôts de plugins)

> Préparée le 2026-10-28, avant la fusion de la PR #130 (brouillon ; à la préparation, 3 contrôles verts et 3 en cours). Ce qui ne peut se vérifier qu'après la fusion, le déploiement et la pose du réglage d'indexation par Thibaut est indiqué tel quel dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 33, `sprint-33-indexation-et-depots` |
| Période | 2026-10-28 (planifié et réalisé le même jour) |
| Points engagés | **13** (US-207 et US-209) |
| Points en done | **13** (code et dépôts livrés ; vérifications de production en attente) |
| PR de la démo | #130 (sprint, brouillon, 53 fichiers, +3 502 / -79) |
| PR des dépôts de plugins | 29 PR fusionnées, une par dépôt (27 plugins, 2 packs), plus 2 PR correctives sur les packs |
| Mode | Orchestrateur (Claude) et worker w209 pour US-209, sur la branche du sprint |

## Sprint Goal

> Voir `sprint-goal.md` : rendre les pages vitrine indexables sur décision explicite, et appliquer aux 29 dépôts de plugins les contrôles de chaîne d'approvisionnement de la démo.

**Atteint dans le code et dans les dépôts, à confirmer après déploiement.** Les deux stories sont livrées. Restent les contrôles qui exigent `main` déployé et le réglage d'indexation posé par Thibaut.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-207 Indexation des pages vitrine | 5 (fiche et registre) | Réglage `DEMO_INDEXING` (absent ou `0` : rien ne change ; `1` n'agit qu'en mode démo, porte privée fermée). Liste blanche unique par noms de routes, lue par l'en-tête noindex, `robots.txt` et le sitemap : accueil, Plugins, Architecture, Preuve technique, À propos, 3 guides d'intégration, Compatibilité, Installer un pack. Tout le reste reste noindex. Sitemap avec hreflang fr-FR, en-US et x-default ; données structurées WebSite, Person et SoftwareSourceCode, avec le drapeau neutre `structured_data: false` pour exclure les plugins en recoupement et l'import ERP. Option `bin/verify-public-mode --indexing`. | Tests rouges `5f81ac9` et `1bcec3e` (rouge prouvé) ; code `ef96eac` ; documents `fd9102b` ; mesures locales `88439a0` : pa11y WCAG2AAA sans problème sur 7 pages, largeurs à 320, 375 et 1280 px sans débordement, `robots.txt` avec et sans le réglage, sitemap de 20 entrées |
| US-209 Chaîne d'approvisionnement des 29 dépôts | 8 (fiche et registre) | `bin/plugin-supply-chain` (check, apply, report), 9 contrôles, 62 tests. **29 PR fusionnées sur 29**, CI verte avant chaque fusion, **aucun tag, aucune release**, seuls `.github/` et la section `config` de `composer.json` ont changé. | Tests rouges `c0f4872` et `06701ba` ; script `e7917e2` ; relevé de départ `7af5f04` ; `rapport-final-depots.md` (`4214876`) : 29 dépôts conformes aux 9 contrôles, CI de `main` verte partout au relevé |

> Note : la demande de revue indiquait US-207 à 8 points et US-209 à 5 ; la fiche, le registre et le task-board indiquent 5 et 8. Les valeurs sourcées sont reprises ici ; le total de 13 est identique.

Tests existants adaptés, chacun décidé au registre avant l'action et cité dans la PR : `SeoMetadataTest`, `ArchitecturePageTest`, `IntegratePageTest`, `PublicOpeningTest`, `RailwayPreservedVariablesTest`, fixture `demo-mode-router` et contrôle 6 de `verify-public-mode`.

Contrôles locaux : suite Unit, Smoke et Functional à 3891 tests verts à la fin d'US-207 ; 62 tests du script d'US-209 verts ; ECS, PHPStan (niveau max) et Rector verts sur tout le dépôt ; preuve technique rafraîchie (321 fichiers de test, `7fc32ca`). Les 3 derniers commits d'US-209 n'avaient pas été rejoués quand le disque est tombé à 3 Go ; une fois le disque libéré par Thibaut (9 Go), **suite complète locale rejouée le 2026-10-28, 3894 tests verts** (215 dépréciations, 1 test ignoré).

## Décisions de Thibaut (2026-10-28, registre du sprint)

- Périmètre de 13 points ; ordre de sacrifice : tranche Composer d'US-209, puis tranche workflows, US-207 protégée (jamais appliqué).
- GO sur la liste blanche d'US-207 (Compatibilité et Installer un pack ajoutés) et sur l'activation par `DEMO_INDEXING=1`, que Thibaut pose lui-même après le déploiement.
- GO sur les dépôts de plugins : une PR par dépôt, fusion si la CI est verte, aucun tag ni release ; sitemap déclaré par Thibaut lui-même dans la Search Console.
- « Tu peux fusionner » : l'orchestrateur fusionne les PR d'US-209 une par une.

## Éléments non terminés ou à confirmer

- **Après le déploiement** : Thibaut pose `DEMO_INDEXING=1` ; puis T-207-13 (`bin/verify-public-mode --indexing` en production, lecture seule) et T-207-14 (déclaration du sitemap dans la Search Console par Thibaut). **Non faites.**
- **Non faites, liées au disque** : T-207-02 (état de référence « avant »), T-TECH-33-07 (contrôle connecté d'US-203), T-TECH-33-09 (images produit locales et capture du panier).
- **Faite avec écart** : T-209-13 (vérification finale sur les dépôts faite ; suite ciblée et Rector non rejoués au moment où le disque était à 3 Go, rejoués ensuite).
- **Transverses non faites** : T-TECH-33-04, -05, -06 et -08 (outillage de machine et de rendu) : le task-board les porte encore à faire, à reporter ou à solder à la clôture.
- **Tableaux de suivi** : task-board et fichiers de tâches à rapprocher du registre à la clôture (T-209-14, mémoire, faite ; T-209-15, cette revue ; T-207-15, revue d'US-207).
- Dependabot ouvre ses premières PR d'actions dans plusieurs dépôts de plugins : à traiter au fil de l'eau.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| **Fusions d'US-209 hors de la règle du registre** : 7 fusions par l'orchestrateur, 22 par le worker w209 lui-même (plus 2 PR correctives des packs), alors que le registre prévoyait que l'orchestrateur fusionne | Bilan vérifié : 29/29 fusionnées, 0 PR ouverte, aucun tag ni release ; chaque fusion CI verte | Consigné au registre (`3df4d01`), remonté à Thibaut pour appréciation ; à traiter en rétrospective |
| **YAML invalide injecté dans les workflows des 2 packs** : l'étape d'audit insérée avait une indentation invalide | La première CI de ces deux PR n'avait rien testé | Détecté par la CI de Dependabot ; corrigé par 2 PR, script et test corrigés (`1db88b6` à `f173fe5`), 29 workflows relus par un analyseur YAML |
| **Disque tombé à 3 Go** | Les 3 derniers commits d'US-209 rejoués ensuite ; suite complète locale rejouée après libération du disque (3894 tests verts) | CI de la PR #130 ; rétrospective |
| Écart à la fiche : aucun des 29 dépôts ne versionne `composer.lock` (la fiche en annonçait 3 sans verrou) | Audit Composer après l'installation partout, revue du verrou sans objet | Noté au registre |
| Aléas de CI sans lien avec le changement : 3 exécutions de `main` échouées une fois, réussies au second passage | Aucun | Relancer, jamais contourner |

## Métriques

| Métrique | Sprint 33 | Sprint 32 |
|----------|-----------|-----------|
| Points engagés / en done | 13 / 13 (contrôles de production en attente) | 9 / 9 |
| Sacrifices appliqués | 0 | 0 |
| Stories de code avec commit rouge | 2 sur 2 (US-207 et US-209) | 3 sur 3 |
| Dépôts de plugins conformes aux 9 contrôles | 29 sur 29 | sans objet |
| Tests du script d'US-209 | 62 | sans objet |
| Écarts de processus consignés | 1 (fusions) | non mesuré |
| Publication ou tag sans GO | 0 | 0 |
| Défauts signalés après le déploiement | non mesuré (pas encore déployé) | 0 relevé à la clôture |

## Démonstration (ordre suggéré)

1. La liste blanche et le sitemap (20 entrées en local) : pages listées, hreflang, données structurées.
2. `robots.txt` avec et sans le réglage d'indexation : comportement par défaut inchangé.
3. `bin/plugin-supply-chain report` : 29 dépôts sur 29 conformes aux 9 contrôles ; `rapport-final-depots.md`.
4. La CI de la PR #130 une fois terminée.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-207 | En revue ; indexation effective après `DEMO_INDEXING=1` et vérification en production |
| US-209 | En revue ; 29 dépôts livrés, aucun tag ni release |
| Reliquats disque (T-TECH-33-07, -09, -13, T-207-02) | À reporter ou solder à la clôture |

## Prochaines étapes

1. Fusion de la PR #130 sur GO de Thibaut, après CI verte.
2. Après déploiement : Thibaut pose `DEMO_INDEXING=1`, puis T-207-13, puis T-207-14 par Thibaut.
3. Rétrospective (écart des fusions, disque, YAML des packs), puis clôture.
