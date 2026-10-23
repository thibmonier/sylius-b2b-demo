# Tâches : Sprint 025 (packs et transmission)

> Sprint Planning Part 2 : décomposition faite au lancement du sprint, le 2026-10-24, sur la branche `feature/sprint-25-packs-et-transmission`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-25`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 24](../../sprint-24-integrateurs/sprint-retro.md), [revue du sprint 24](../../sprint-24-integrateurs/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-176](./US-176-tasks.md) | Installation réelle d'un pack en CI | 5 | 14 | 10,5 h | non sacrifiable | 🔲 |
| [US-178](./US-178-tasks.md) | Page « Installer un pack » dans la démo | 2 | 8 | 4,75 h | non sacrifiable | 🔲 |
| [US-177](./US-177-tasks.md) | Recettes Symfony Flex des packs | 5 | 14 | 10,5 h | **n°1** | 🔲 |
| [US-180](./US-180-tasks.md) | Transmission des factures électroniques, tranche A | 5 | 13 | 10,5 h | **n°2** | 🔲 |
| [Transverse](./technical-tasks.md) | Ressources, contrôles avant push et avant done, preuve, statuts, revue et rétro (sans points) | 0 | 13 | 6,25 h | non sacrifiable | 🔲 (4 ✅) |
| **Total** | | **17** | **62** | **42,5 h** | | |

**Total décomposé : 17 points, 62 tâches, 42,5 h**, dont 1,25 h d'actions de Thibaut (quatre GO et une soumission à Packagist) : **41,25 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-24.** Suite de l'ADR 0015 : US-176 (installation réelle en CI), US-178 (page dans la démo) et US-177 (recettes Flex, la PR publique sur `symfony/recipes-contrib` attend son GO explicite). Suite de l'ADR 0016 : US-180 seulement (tranche A, nouveau plugin). L'ADR 0017 (ERP) n'a aucune réalisation ce sprint.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 17 points | 17 × 2,1 + 8 = **43,7 h** | 41,25 h | **2,45 h** |

La marge est plus faible qu'au sprint 24 (3,35 h) : les cycles de CI des dépôts de packs (T-176-10, T-177-11) sont le principal aléa, car la CI est le juge de l'installation. Si la charge dépasse la capacité, ordre de sacrifice décidé par Thibaut : **US-177, puis US-180**.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 41,25 h (17 points) | 43,7 h | Marge de 2,45 h |
| 1 | US-177 entière (recettes Flex) | 10 h | 31,25 h (12 points) | 33,2 h | Les README gardent les gestes manuels ; aucune PR sur `recipes-contrib` |
| 2 | US-180 entière (plugin de transmission) | 10 h | 21,25 h (7 points) | 22,7 h | Pas de nouveau plugin ; l'ADR 0016 garde la story A au backlog |

Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. Un arrêt de US-177 sur l'essai (Flex n'applique pas la recette à un métapaquet) n'est **pas** un sacrifice : c'est un scénario de la story, qui libère les tâches T-177-04 à T-177-13 (voir son fichier).

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [OPS] | 23 | 15 h |
| [DOC] | 14 | 7,75 h |
| [TEST] | 9 | 8,25 h |
| [BE] | 8 | 7,25 h |
| [REV] | 6 | 2,5 h |
| [DB] | 1 | 1 h |
| [FE-WEB] | 1 | 0,75 h |
| [FE-MOB] | 0 | 0 h |
| **Total** | **62** | **42,5 h** |

La part d'[OPS] (35 %) tient à la nature du sprint : dépôts distants, CI, GO et publication.

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-176 | US-177 | US-178 | US-180 |
|-----------------|:------:|:------:|:------:|:------:|
| Dépôt `sylius-pack-ai-headless` : workflow, `etc/`, `bin/`, `tests/`, README | ● | ● | ○ (lu) | |
| Dépôt `sylius-pack-compliance-tooling` : mêmes zones | ● | ● | ○ (lu) | |
| Dossier de recette des deux packs | | ● | | |
| Dépôt `sylius-einvoice-transmission-plugin` (nouveau, local) | | | | ● |
| `sylius-facturx-plugin`, `sylius-webhook-outbox-plugin` | | | | ○ |
| `src/Demo/Packs/`, `bin/packs-refresh`, `bin/proof-check` | | | ● | |
| `config/demo/packs.json`, `config/packages/demo_packs.yaml` | | | ● | |
| `src/Demo/Controller/`, `templates/demo/packs.html.twig`, `translations/` | | | ● (nouveaux) | |
| `templates/demo/integrate.html.twig`, `templates/demo/architecture.html.twig` | | | ● (un lien chacun) | |
| `tests/` de la démo | | | ● (nouveaux) | |
| `config/demo/proof.json` | | | ● (rafraîchi) | |
| `config/demo/plugins.yaml`, `config/demo/compatibility.json` | | | | |
| `docs/adr/0015-*.md`, `docs/adr/0016-*.md`, `docs/adr/index.md` | | ● (résultat de l'essai : orchestrateur) | | |
| `.bmad/sprint-status.yaml`, `docs/tests-locaux.md`, `project-management/templates/` | | | | |

Les deux dernières lignes ne sont modifiées que par l'orchestrateur : les workers n'y touchent pas. `config/demo/plugins.yaml` et `config/demo/compatibility.json` ne bougent pas ce sprint : le nouveau plugin n'entre pas dans le catalogue de la démo (story B de l'ADR 0016, hors sprint), ce qui évite un écart sur la preuve et la matrice.

**Conflits à prévoir** :

1. **Dépôts des packs : US-176 puis US-177.** Les deux stories modifient les mêmes fichiers (workflow, fichier de gestes, README). **Ordre imposé : US-177 démarre après le merge d'US-176** (T-177-01). Une branche par story et par dépôt.
2. **US-178 lit les packs, US-176 peut corriger leurs README.** La première génération des données de la page (T-178-05) attend le merge d'US-176 (T-176-13) ; le code et les tests de la page peuvent avancer avant, sur une fixture.
3. **`config/demo/proof.json` : seule US-178.** Aucun conflit dans le sprint ; après un rebase, `php bin/proof-refresh` puis `php bin/proof-check`, jamais de fusion à la main (T-TECH-25-10).
4. **`docs/adr/` et `.bmad/sprint-status.yaml`** : un seul écrivain, l'orchestrateur (T-177-03 ; décision de Thibaut sur la recette).
5. **US-180 est indépendante** (nouveau dossier local) ; son seul point commun avec les autres stories est la ressource lourde : une seule installation (vendor du plugin ou `bin/run-tests` de la démo) à la fois.

**Parallélisme possible** (un seul serveur lourd à la fois) :
- voie A (dépôts des packs, CI comme juge, rien de lourd en local) : US-176, puis US-177 ;
- voie B (nouveau plugin, local, installation lourde ponctuelle) : US-180 ;
- voie C (démo, `bin/run-tests`) : US-178, code et tests avant le merge d'US-176, génération des données et fin après ;
- les voies B et C ne lancent jamais leur serveur lourd en même temps ; les voies A et B ou A et C se recouvrent librement (A n'utilise que la CI).

**Ordre de merge proposé** : dépôts des packs d'abord (US-176, puis US-177 si la porte de décision est franchie), puis la PR du sprint dans la démo (US-178 et dossier de sprint), puis vérification du rendu en production après le déploiement automatique de `main` par Railway. Le dépôt du plugin de transmission (US-180) est indépendant : push, CI, tag sur GO.

## Règle 10 : toute tâche avec `git` dans l'arbre principal

Les quatre stories commitent ou poussent. **Aucune ne se confie à un worker lancé en worktree isolé** (action 2 de la rétro du sprint 24, règle 10 de [consigne-worker.md](../../../templates/consigne-worker.md)) : les workers travaillent dans l'arbre principal, séquencés par l'orchestrateur, un seul à la fois par dépôt. Un worker isolé ne reçoit que du travail sans `git` (lecture, rédaction d'un texte rendu dans la réponse).

## Lieu de vérification du rendu des stories d'interface (action 4 de la rétro du sprint 23)

| US | Lieu | Procédure | État de la story avant la preuve |
|----|------|-----------|----------------------------------|
| US-178 | Production après le déploiement automatique de `main` par Railway, lecture seule | pa11y WCAG2AAA FR et EN, 360 px et 1280 px (`docs/tests-locaux.md`, « Vérifier le rendu en production ») | review |

US-176, US-177 et US-180 n'ont pas de rendu : leurs preuves sont des runs de CI, des sorties d'essai et des suites de tests, citées dans leur table « Scénarios et preuves ».

## Actions de la rétrospective du sprint 24 appliquées

| Action | Application dans ce sprint |
|--------|----------------------------|
| 1. Nettoyer les bases de test après chaque lot | ✅ Ligne ajoutée à `checklist-integration.md` au planning (T-TECH-25-01) ; exécution à chaque lot : T-TECH-25-05 ; indicateur : espace libre minimal d'au moins 5 Go (4,0 Go au sprint 24) |
| 2. Capacités d'un worker isolé vérifiées avant délégation | ✅ Règle 10 ajoutée à `consigne-worker.md` (T-TECH-25-02) ; section ci-dessus ; indicateur : 0 worker bloqué par un refus prévisible |
| 3. Relire la source avant tout texte public | ✅ Ligne ajoutée à `checklist-integration.md` (T-TECH-25-03) ; exécution : T-TECH-25-11 ; indicateur : 0 texte public inexact |
| 4. Mêmes règles de test pour l'orchestrateur | ✅ Règle 11 ajoutée à `consigne-worker.md` (T-TECH-25-04) ; contrôle : T-TECH-25-08 ; indicateur : 0 exécution directe de `phpunit`, 100 % de commits rouges seuls |

Actions du sprint 22 et du sprint 23 maintenues : table « scénario → test ou preuve » dans chaque fichier de tâches (T-TECH-25-08), budget de ressources (T-TECH-25-05), statuts cochés dans le commit qui livre (T-TECH-25-09), preuve rafraîchie dans la PR (T-TECH-25-10), lieu de vérification écrit au planning, clôture dès le périmètre vérifié.

## Actions de Thibaut

| ID | Action | Durée |
|----|--------|-------|
| T-176-09 | GO pour pousser les branches, ouvrir les PR et les fusionner dans les deux dépôts de packs (installation réelle), sans tag | 0,25 h |
| T-177-10 | GO équivalent pour les recettes (seulement si l'essai franchit la porte de décision) | 0,25 h |
| T-177-12 | GO explicite, ou refus, pour la PR publique sur `symfony/recipes-contrib` | 0,25 h |
| T-180-11 | GO pour créer le dépôt `sylius-einvoice-transmission-plugin`, pousser et poser le tag `v0.1.0` | 0,25 h |
| T-180-13 | Soumettre le nouveau paquet à Packagist | 0,25 h |

Notes :
- **Aucun push, tag, release, création de dépôt ni PR publique hors démo sans GO de Thibaut.** Commit, push et PR de la démo sur demande ou GO permanent (« fusionner les PR dès qu'elles sont vertes »). Un GO couvre l'objet nommé, pas le suivant : le GO d'US-176 ne vaut pas pour US-177.
- **Aucune commande Railway.** Railway déploie `main` automatiquement ; la vérification en production est en lecture seule, sans connexion. Aucune tâche de déploiement pour Thibaut ce sprint.
- **Politique** : ton neutre, aucune comparaison avec une offre commerciale (Sylius Plus), y compris dans les ADR, les README, les pages et le texte de la PR sur `recipes-contrib` ; aucun plugin « concurrent » ou « à confirmer » dans un pack.
- **Sécurité** : aucun détail exploitable dans les dépôts publics (routes, valeurs, seuils) ; aucun détail de sécurité dans la démo, les packs, le plugin ou les README.

## Consigne de disque et de tests des workers

1. `df -h .`, `bin/check-disk` et mémoire libre avant chaque lot (environ 7 Go libres ; `bin/run-tests` refuse sous 5 Go, seuil jamais abaissé). **Aucune installation lourde en local pour les packs** : la CI est le juge (US-176, US-177).
2. Tests de la démo : **uniquement** `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`. **Jamais phpunit en direct** (orchestrateur compris, règle 11). Identités proposées : w176 et w177 (dépôts des packs, sans base), w178 (démo), w180 (plugin, tests dans Docker du plugin).
3. Un seul serveur lourd à la fois (démo locale, MySQL de test, ou vendor du plugin), arrêté dès la fin de la vérification.
4. **Après chaque lot** : aucun run actif, `bin/clean-test-caches --all` (orchestrateur seul), puis `df -h .` ; sous 6 Go libres, prévenir Thibaut avant le lot suivant.
5. Contrôles de qualité rejoués sur l'arbre exact avant chaque push (T-TECH-25-06).
6. Toute PR de la démo qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check` (T-TECH-25-10).
7. Refus d'outil : ne jamais le contourner ; le noter dans le rapport et continuer (règle 8 de `project-management/templates/consigne-worker.md`).
8. Lecture d'un fichier à modifier : outil Read ou `rtk proxy cat`, jamais `cat`, `sed` ou `head` filtrés (règle 9) ; diff d'un refactoring relu pour les commentaires supprimés.
9. Pièges du shell (`rm` aliasé, `sed -E`, sortie de test écrite dans un fichier, `catchExceptions(false)`) : section « Pièges connus » de `docs/tests-locaux.md`. Suppressions avec `/bin/rm`.
10. Dépôt existant ou cloné : `git fetch` et branche courante contrôlées avant tout commit (branches fusionnées périmées).
11. Chaque worker place le « Rapport de fin » du gabarit `consigne-worker.md` en tête de sa réponse finale.

## Prérequis d'environnement par story

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-176 | Clones des deux dépôts de packs ; accès `gh` en écriture **après GO** seulement ; CI des dépôts (minutes disponibles) ; aucune installation lourde locale | T-176-01, T-176-09 |
| US-177 | US-176 mergée ; projet jetable minimal hors dépôt (taille relevée, supprimé avec `/bin/rm`) ; GO de Thibaut pour le push puis, à part, pour la PR publique | T-177-01, T-177-10, T-177-12 |
| US-178 | Packs lisibles via `gh` (lecture) ; US-176 mergée pour la génération réelle des données ; conteneur MySQL du projet pour `bin/run-tests` ; Chromium et pa11y en dossier jetable pour la production | T-178-01, T-178-05, T-TECH-25-05 |
| US-180 | Dépôts `sylius-facturx-plugin` et `sylius-webhook-outbox-plugin` à jour (lecture) ; Docker ; au moins 5 Go libres ; dossier local `sylius-einvoice-transmission-plugin` ; GO de Thibaut avant tout dépôt distant | T-180-01, T-180-02, T-180-11 |
