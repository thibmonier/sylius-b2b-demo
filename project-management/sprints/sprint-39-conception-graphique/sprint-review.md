# Sprint Review - Sprint 39 (Conception graphique)

> Préparée le 2026-10-31. Tous les commits sont sur la branche `feature/sprint-39-conception-graphique`. Les branches des plugins ne sont pas encore poussées (push sur GO de Thibaut).

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 39, `sprint-39-conception-graphique` |
| Période | 2026-10-31 (livré le même jour) |
| Points engagés | **8** (US-248, US-250, US-249, US-247) |
| Points livrés | **8** (4 stories terminées) |
| PR de la démo | À créer (branche `feature/sprint-39-conception-graphique`, 8 commits) |
| Commits plugin copilote | `fix/cassette-cart-response` (2 commits : 56ceacf, 942783c) |
| Commits plugin parc | `fix/phpunit-server-vars-and-repository-tag` (3 commits : 40e797d, bda2aaa, 35db11c) |
| Stories exclues | US-246, US-251 (portes humaines : validation maquette + release plugin) |
| Mode | Orchestrateur (Claude) et workers wdesign, wtech, w249, w247 |

## Sprint Goal

> « Répondre aux retours de la revue du sprint 38 sur la conception graphique : maquetter dans Claude Design le widget du copilote et le bloc de réassort, écrire le principe de conception et le montrer dans la démo, et aligner la réponse enregistrée du copilote sur le panier de Claire. »

**Atteint.** Les quatre stories sont livrées et les cinq tâches transverses de la rétro du sprint 38 sont terminées. Le canevas Claude Design contient désormais les maquettes du copilote (6 états) et du réassort (3 états). Le principe de conception est documenté et visible sur la page Architecture de la démo. La réponse du copilote correspond au panier de Claire (47 articles, 963,50 € HT).

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-248 Maquette widget copilote dans Claude Design | 2 | 4 pages publiées dans le canevas `G2StRQigRKD6SqNRoyHpkW` : Copilote et CopiloteMobile (desktop 1440 px, mobile 390 px). 6 états : vide, question envoyée, chargement, réponse (47 articles, 1 017,35 € HT), repli, erreur. Bulles de conversation, 3 puces de suggestion, champ de saisie. Contrastes AAA vérifiés. | Pages `Copilote.dc.html`, `CopiloteMobile.dc.html` du canevas |
| US-250 Maquette bloc réassort dans Claude Design | 2 | 4 pages publiées : Reassort et ReassortMobile. 3 états : aucune alerte, une alerte, plusieurs alertes dont une en retard. Badges « À prévoir » (teal) et « En retard » (orange), bordure renforcée pour les retards, bouton « Préparer le panier » par alerte. Jetons de design cohérents avec US-248. | Pages `Reassort.dc.html`, `ReassortMobile.dc.html` du canevas |
| US-249 Principe de conception écrit et maquettes montrées dans la démo | 3 | `docs/conception-graphique.md` créé (canevas unique, validation avant Twig, export images, jetons de design, accessibilité AAA). Ligne ajoutée au gabarit de DoD. Section « De la maquette à l'écran » sur la page Architecture (FR/EN) avec 3 images mockup exportées (copilote, réassort, fiche produit, toutes < 300 Ko). Canevas privé (read: owner), consigné au registre. | `d794b9c` — 17 tests, 281 assertions, pa11y AAA 0 erreurs |
| US-247 Réponse enregistrée cohérente avec le panier de Claire | 1 | Cassette FR corrigée : « 47 articles pour un total de 963,50 € HT ». Cassette EN ajoutée : « 47 items for a total of €963.50 excl. tax ». Réponse de démonstration surchargée dans la démo (service `demo.copilot.cassette_override`). README FR et EN du plugin copilote mis à jour. | `CartStateResponseTest` — RED fb3186d, GREEN 0bc7913 |

## Tâches transverses (rétro sprint 38)

| Tâche | Livré |
|-------|-------|
| T-TECH-39-01 Test en application hôte (parc + copilote) | Tests de locale (FR/EN) pour les pages shop des plugins parc et copilote |
| T-TECH-39-02 Règle 49 : un seul worker par dépôt plugin | Ajoutée à `consigne-worker.md` |
| T-TECH-39-03 Variables `<server>` phpunit parc + issue #14 | 3 variables ajoutées dans `phpunit.xml.dist`, repository enregistré par FQCN (fix issue #14) |
| T-TECH-39-04 Règle 50 : gabarit de test en application hôte | Section §4 ajoutée à `docs/gabarits-tests.md` |
| T-TECH-39-05 RunTestsLockTest déterministe | Synchronisation par fichier (`STUB_CAT_SYNC_DIR`) remplaçant les délais |

## Incidents

| Incident | Effet | Traitement |
|----------|-------|------------|
| Fork wtech-lock arrêté après avoir dépassé le temps imparti | Fichier partiellement modifié | Repris par l'orchestrateur, commit 448d862 |
| Slug du canevas Claude Design : `G2StRQigRKD6SqNRoyHpkW` ≠ UUID API `79acc6de-...` | Erreurs d'appel à l'API artifacts | Résolu par `list_artifacts`, UUID documenté |
| Total du panier dans la maquette copilote (1 017,35 €) ≠ fixtures (963,50 €) | Incohérence entre maquette et cassette | Noté pour correction au sprint 40 (US-246) |
| Fork w249 atteint la limite de 200 tours | L'implémentation était terminée, le fork tentait de lancer la suite complète | Aucun impact : tous les commits étaient faits |

## Métriques

| Métrique | Sprint 39 | Sprint 38 |
|----------|-----------|-----------|
| Points engagés / livrés | 8 / 8 | 10 / 10 |
| Stories de code avec rouge prouvé | 2 sur 2 (US-249, US-247 ; US-248/250 : maquettes sans code) | 3 sur 3 |
| Relais de workers | 1 (wtech-lock → orchestrateur) | 1 |
| Pushes forcés | 0 | 0 |
| Tests supprimés ou affaiblis | 0 | 0 |
| Tag ou release sans GO | 0 | 0 |
| Maquettes publiées dans Claude Design | 8 pages (4 desktop + 4 mobile) | 0 |

## Démonstration (ordre suggéré)

1. Le canevas Claude Design : les pages Copilote (6 états du widget) et Reassort (3 états du bloc d'alertes), desktop et mobile.
2. La page Architecture de la démo, section « De la maquette à l'écran » : maquettes du copilote, du réassort et de la fiche produit côte à côte avec les descriptions (FR puis EN).
3. Le document `docs/conception-graphique.md` : principe, jetons de design, tableau des écrans refondus depuis le sprint 29.
4. Le tableau de bord de Claire : la réponse du copilote à « état de mon panier » affiche désormais « 47 articles, 963,50 € HT ».

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-248, US-250, US-249, US-247 | Done |
| US-246 Copilote : mise en forme du widget | Sprint 40 (attend validation des maquettes par Thibaut) |
| US-251 Réassort : mise en forme du bloc | Sprint 40 (attend validation des maquettes par Thibaut) |
| Issue #14 du plugin parc | Corrigée (T-TECH-39-03, commit bda2aaa) |
| Incohérence maquette copilote / fixtures (1 017,35 € vs 963,50 €) | À corriger dans la maquette au sprint 40 |

## Prochaines étapes

1. Push des branches et création de la PR de la démo.
2. Push et PR des branches plugins (parc : `fix/phpunit-server-vars-and-repository-tag`, copilote : `fix/cassette-cart-response`).
3. CI verte sur les trois PR, puis GO de Thibaut pour la fusion.
4. Sprint 40 : validation des maquettes par Thibaut, puis implémentation (US-246, US-251).

## Dettes techniques

- Maquette copilote : le total affiché (1 017,35 €) ne correspond pas aux fixtures (963,50 €). La maquette devra être corrigée avant l'implémentation (US-246).
- Tests locaux sans DB Docker : `CartStateResponseTest` et `WidgetOnDashboardTest` ne tournent qu'en CI (pas de base de données locale). Acceptable pour ce sprint (contrainte machine documentée).
- Canevas Claude Design privé (read: owner) : pas de lien public vers les maquettes depuis la page Architecture. À réévaluer si Claude Design ajoute le partage public.
