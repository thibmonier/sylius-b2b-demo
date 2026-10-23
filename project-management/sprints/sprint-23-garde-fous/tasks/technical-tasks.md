# Tâches transverses : Sprint 023 (garde-fous et cadrages)

Tâches sans points, portées par l'orchestrateur. Elles appliquent les actions de la [rétrospective du sprint 22](../../sprint-22-finalisation/sprint-retro.md).

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-TECH-23-01 | [OPS] | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés ; un seul serveur lourd à la fois (démo locale ou MySQL de test), arrêté dès la fin de la vérification (action 3 de la rétro du sprint 22) | 0,5 h | - | Orchestrateur | ✅ |
| T-TECH-23-01b | [OPS] | Thibaut libère de l'espace disque pour atteindre au moins 5 Go libres (3,4 Go au lancement ; 5,9 Go le 2026-10-23 après son action) | 0,25 h | - | Thibaut | ✅ |
| T-TECH-23-02 | [OPS] | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push ; dernière ligne de sortie citée | 0,75 h | - | Orchestrateur | ✅ |
| T-TECH-23-03 | [OPS] | PR du sprint : CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; si la CI de la PR ne démarre pas, `gh workflow run ci.yaml --ref <branche>` | 0,5 h | - | Orchestrateur | 🔄 |
| T-TECH-23-04 | [REV] | Avant chaque passage en done : table « scénario → test ou preuve » complète dans le fichier de tâches (action 1) et commit de tests rouges cité dans la PR pour les stories de code (action 2) | 0,75 h | - | Orchestrateur | 🔄 |
| T-TECH-23-05 | [DOC] | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge (action 5) | 0,25 h | - | Orchestrateur | 🔄 |
| T-TECH-23-06 | [DOC] | Revue et rétrospective du sprint 23 : indicateurs du sprint 22 (scénarios non livrés, commits rouges, plantages, espace minimal, tâches restées 🔲, contournements) | 1 h | fin du périmètre | Orchestrateur | 🔲 |

**Total estimé : 4 h** (7 tâches, dont 0,25 h de Thibaut).
