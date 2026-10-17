# Task Board — Sprint 012 (Ouverture et plugins)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-055-02 | US-055 | [BE] | `bin/run-tests` : cache de test isolé et vidé avant chaque suite | 2.5h | |
| T-055-03 | US-055 | [TEST] | Deux passes consécutives, après changement de constructeur | 1.5h | |
| T-055-01 | US-055 | [DOC] | Nettoyage de fin de worker et ligne `df` : reportés dans les modèles | 0.5h | |
| T-055-05 | US-055 | [DOC] | Consigne d'orchestration des relecteurs | 0.5h | |
| T-055-06 | US-055 | [REV] | Action 4 du sprint 10 : mineurs reportés au backlog | 1h | |
| T-055-07 | US-055 | [BE] | US-031 : agentic-feeds depuis Packagist, dépôt VCS retiré | 1.5h | |
| T-055-08 | US-055 | [REV] | Revue | 0.5h | |
| T-052-01 | US-052 | [DOC] | Relecture du runbook contre l'état réel, check-list de GO | 1h | |
| T-052-08 | US-052 | [DOC] | Docs après bascule : résultats, runbook, état public | 1.5h | |
| T-052-09 | US-052 | [REV] | Revue | 0.5h | |
| T-053-01 | US-053 | [DOC] | Analyse du contrat OCI du plugin et du simulateur | 1h | |
| T-053-02 | US-053 | [TEST] | Tests rouges : entrée OCI, retour `NEW_ITEM-*`, garde-fous | 1.5h | |
| T-053-03 | US-053 | [BE] | Formulaire `HOOK_URL` et entrée OCI | 2.5h | |
| T-053-04 | US-053 | [BE] | Retour OCI et affichage des lignes reçues | 2h | |
| T-053-05 | US-053 | [BE] | Garde-fous OCI : limiteurs, usage unique, plafond quotidien | 1.5h | |
| T-053-06 | US-053 | [TEST] | Tests fonctionnels du parcours OCI | 2h | |
| T-053-07 | US-053 | [DOC] | Documentation du simulateur, OCI retiré des limites | 1h | |
| T-053-08 | US-053 | [REV] | Revue | 1h | |
| T-054-01 | US-054 | [DOC] | Mini-spec du plugin | 2h | |
| T-054-02 | US-054 | [DOC] | ADR-0001 : modèle de données et filtre | 1.5h | |
| T-054-03 | US-054 | [REV] | Vérification de chevauchement avec Sylius Plus | 1.5h | |
| T-054-05 | US-054 | [BE] | Skeleton SyliusLabs du plugin (dépôt local) | 3h | |
| T-054-06 | US-054 | [BE] | Socle qualité : ECS, PHPStan max, Rector, tests, CI multi-versions | 2.5h | |
| T-054-09 | US-054 | [REV] | Revue | 1h | |
| T-052-02 | US-052 | [TEST] | ⚠️ Script de vérification du mode public (sacrifice n° 1) | 2h | |
| T-054-07 | US-054 | [TEST] | ⚠️ Test de fumée du plugin (sacrifice n° 2) | 1h | |
| T-054-08 | US-054 | [DOC] | ⚠️ README et CHANGELOG du plugin (sacrifice n° 2) | 1h | |
| T-055-04 | US-055 | [DOC] | ⚠️ `docs/tests-locaux.md` (sacrifice n° 3) | 0.5h | |

## 📅 Calendaire (GO utilisateur, hors chemin critique)

| ID | US | Tâche | Estimation | Condition |
|----|-----|-------|------------|-----------|
| T-052-03 | US-052 | [OPS] Moniteur externe sur `/healthz` (ex T-050-09) | 0.5h | Compte de l'utilisateur, avant la bascule |
| T-052-04 | US-052 | [OPS] Bascule en mode public (ex T-050-10) | 1h | GO explicite, commandes Railway lancées par l'utilisateur |
| T-052-05 | US-052 | [OPS] Vérification de `X-Real-IP` après la bascule | 0.5h | Après T-052-04, deux connexions |
| T-052-06 | US-052 | [OPS] Parcours MCP avec un vrai assistant (ex T-050-11) | 1h | Après T-052-04 |
| T-052-07 | US-052 | [OPS] Relevé de coût Railway (ex T-049-08) | 0.5h | Accès Railway, après le 2026-10-23 |
| T-054-04 | US-054 | [OPS] Création du dépôt GitHub public du plugin (par Claude) | 0.5h | Confirmation explicite, après le verdict T-054-03 |

## 🔄 En Cours
| ID | US | Tâche | Démarré | Assigné |
|----|-----|-------|---------|---------|

## 👀 En Review
| ID | US | Tâche | Reviewer |
|----|-----|-------|----------|

## ✅ Terminé
| ID | US | Tâche | Réel | Terminé |
|----|-----|-------|------|---------|

## 🚫 Bloqué
| ID | US | Raison | Action |
|----|-----|--------|--------|

## Métriques
- **Tâches** : 34 total | 0 terminées (0 %)
- **Heures** : 43.5h estimées (dont 4h calendaires) | 0h consommées | 43.5h restantes (capacité ≈ 30-35h, ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
