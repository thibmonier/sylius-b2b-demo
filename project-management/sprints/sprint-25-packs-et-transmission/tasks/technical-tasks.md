# Tâches transverses : Sprint 025 (packs et transmission)

Tâches sans points, portées par l'orchestrateur. Elles appliquent les actions de la [rétrospective du sprint 24](../../sprint-24-integrateurs/sprint-retro.md).

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-TECH-25-01 | [DOC] | Ligne « bases de test nettoyées après chaque lot » ajoutée à `project-management/templates/checklist-integration.md` (action 1 de la rétro du sprint 24) | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-25-02 | [DOC] | Règle 10 de `project-management/templates/consigne-worker.md` : pas de `git` dans un worktree isolé, story à `git` séquencée dans l'arbre principal (action 2 de la rétro du sprint 24) | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-25-03 | [DOC] | Ligne « texte public relu contre sa source » ajoutée à `checklist-integration.md` (action 3 de la rétro du sprint 24) | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-25-04 | [DOC] | Règle 11 de `consigne-worker.md` : mêmes règles de test pour l'orchestrateur, tests par `bin/run-tests`, test rouge commité seul (action 4 de la rétro du sprint 24) | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-25-05 | [OPS] | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés ; **après chaque lot** : aucun run actif, `bin/clean-test-caches --all`, puis `df -h .` ; sous 6 Go libres, prévenir Thibaut avant le lot suivant ; un seul serveur lourd à la fois ; `/bin/rm` pour les suppressions (indicateur de l'action 1 : espace libre minimal d'au moins 5 Go) | 0,75 h | - | Orchestrateur | ✅ |
| T-TECH-25-06 | [OPS] | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push de la démo ; dans les dépôts des packs et du nouveau plugin, les contrôles du dépôt rejoués avant chaque push ; dernière ligne de sortie citée | 0,75 h | - | Orchestrateur | ✅ |
| T-TECH-25-07 | [OPS] | CI verte avant chaque merge (démo : « Quality and smoke tests », « Docker image » ; dépôts de packs et du plugin : tous les jobs), run terminé ; si la CI d'une PR ne démarre pas, `gh workflow run ci.yaml --ref <branche>` ; ordre de merge de la matrice des chevauchements respecté | 0,5 h | - | Orchestrateur | ✅ |
| T-TECH-25-08 | [REV] | Avant chaque passage en done : table « scénario → test ou preuve » complète, commit de tests rouges cité dans la PR pour les stories de code (action 4 : y compris pour le code de l'orchestrateur), lieu de vérification écrit avant le développement d'US-178, runs de CI cités pour US-176, US-177 et US-180 | 0,75 h | - | Orchestrateur | ✅ |
| T-TECH-25-09 | [DOC] | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-25-10 | [OPS] | Preuve publiée : la PR qui ajoute ou supprime un fichier de tests de la démo (US-178) embarque `php bin/proof-refresh` puis `php bin/proof-check` ; après un rebase, la preuve est rejouée, jamais fusionnée à la main | 0,5 h | - | Orchestrateur | ✅ |
| T-TECH-25-11 | [REV] | Texte public relu contre sa source avant le commit : README des packs et du nouveau plugin, page « Installer un pack », texte de la PR sur `recipes-contrib`, notes de release (action 3 de la rétro du sprint 24 ; indicateur : 0 texte public inexact) | 0,5 h | - | Orchestrateur | ✅ |
| T-TECH-25-12 | [OPS] | Registre des GO de Thibaut : aucun push, PR, tag, release, dépôt ni PR publique hors démo sans GO ; chaque GO noté (date, objet) dans la réponse de l'orchestrateur avant l'action ; aucune commande Railway | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-25-13 | [DOC] | Revue et rétrospective du sprint 25 : indicateurs de la rétro du sprint 24 (espace libre minimal, workers bloqués par un refus prévisible, textes publics inexacts, exécutions directes de `phpunit`, commits rouges seuls), puis clôture | 1 h | fin du périmètre | Orchestrateur | ✅ |

**Total estimé : 6,25 h** (13 tâches, aucune de Thibaut ; 1 h déjà faite le 2026-10-24 au planning).
