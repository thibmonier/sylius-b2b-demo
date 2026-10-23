# Tâches transverses : Sprint 024 (intégrateurs)

Tâches sans points, portées par l'orchestrateur. Elles appliquent les actions de la [rétrospective du sprint 23](../../sprint-23-garde-fous/sprint-retro.md).

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-TECH-24-01 | [DOC] | Règle 9 de `project-management/templates/consigne-worker.md` : lecture sans filtrage (outil Read ou `rtk proxy cat`) avant de modifier un fichier, diff d'un refactoring relu pour les commentaires supprimés (action 1 de la rétro du sprint 23) | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-24-02 | [DOC] | Section « Pièges connus » de `docs/tests-locaux.md` complétée : `rm` aliasé, `sed -E` sur macOS, sortie de test écrite dans un fichier, `catchExceptions(false)` (action 2 de la rétro du sprint 23) | 0,25 h | - | Orchestrateur | ✅ |
| T-TECH-24-03 | [OPS] | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés (environ 6 Go libres, seuil de 5 Go) ; un seul serveur lourd à la fois (démo locale ou MySQL de test), arrêté dès la fin de la vérification ; `/bin/rm` pour les suppressions | 0,5 h | - | Orchestrateur | 🔲 |
| T-TECH-24-04 | [OPS] | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push ; dernière ligne de sortie citée | 0,75 h | - | Orchestrateur | 🔲 |
| T-TECH-24-05 | [OPS] | CI verte avant chaque merge, pour la PR du sprint et pour la PR #54 (« Quality and smoke tests », « Docker image »), run terminé ; si la CI d'une PR ne démarre pas, `gh workflow run ci.yaml --ref <branche>` ; ordre de merge de la matrice des chevauchements respecté | 0,5 h | - | Orchestrateur | 🔲 |
| T-TECH-24-06 | [REV] | Avant chaque passage en done : table « scénario → test ou preuve » complète (action 1 du sprint 22), commit de tests rouges cité dans la PR pour les stories de code (action 2), lieu de vérification du rendu écrit avant le développement des stories d'interface US-171, US-172 et US-179 (action 4 du sprint 23) | 0,75 h | - | Orchestrateur | 🔲 |
| T-TECH-24-07 | [DOC] | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge (action 5 du sprint 22) | 0,25 h | - | Orchestrateur | 🔲 |
| T-TECH-24-08 | [OPS] | Preuve publiée : toute PR qui ajoute ou supprime un fichier de tests embarque `php bin/proof-refresh` puis `php bin/proof-check` (US-171, US-172, US-174, US-175 éventuelle) ; après un rebase, la preuve est rejouée, jamais fusionnée à la main (action 3 de la rétro du sprint 23) | 0,5 h | - | Orchestrateur | 🔲 |
| T-TECH-24-09 | [OPS] | 📅 Thibaut déploie `main` sur Railway après le merge de la PR du sprint, pour la vérification du rendu en production (US-171, US-172, US-179) ; aucune commande Railway par Claude | 0,25 h | merge de la PR du sprint | Thibaut | 📅 |
| T-TECH-24-10 | [DOC] | Revue et rétrospective du sprint 24 : indicateurs de la rétro du sprint 23 (commentaires supprimés par erreur, commandes de fond bloquées, builds de `main` en échec sur la preuve, plantages, espace minimal, tâches restées 🔲, contournements, rendu vérifié avant le done) | 1 h | fin du périmètre | Orchestrateur | 🔲 |

**Total estimé : 5 h** (10 tâches, dont 0,25 h de Thibaut ; 0,5 h déjà faite le 2026-10-23).
