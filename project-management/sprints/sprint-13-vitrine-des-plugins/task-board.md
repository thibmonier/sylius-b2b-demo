# Task Board — Sprint 013 (Vitrine des plugins)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-063-01 | US-063 | [DOC] | Inventaire des 24 plugins | 1.5h | |
| T-063-02 | US-063 | [DOC] | Relevé des fonctions de Sylius Plus (sources) | 1.5h | |
| T-063-03 | US-063 | [DOC] | Classement des 24 plugins | 1.5h | |
| T-063-04 | US-063 | [DOC] | Charte de ton FR et EN | 1h | |
| T-063-05 | US-063 | [DOC] | `docs/vitrine-plugins.md` | 0.5h | |
| T-063-06 | US-063 | [REV] | Revue | 0.5h | |
| T-064-01 | US-064 | [DOC] | Option de livraison, CI et branches (lecture seule) | 1h | |
| T-064-02 | US-064 | [DOC] | Bloc standard EN et FR | 1h | |
| T-064-03 | US-064 | [TEST] | Tests rouges de `bin/vitrine-readme` | 1.5h | |
| T-064-04 | US-064 | [BE] | `bin/vitrine-readme` | 2h | |
| T-064-05 | US-064 | [DOC] | `README.fr.md` du lot A (12) | 3h | |
| T-064-07 | US-064 | [REV] | Revue | 0.5h | |
| T-067-01 | US-067 | [DOC] | `README.fr.md` du lot B (12) | 3h | |
| T-067-02 | US-067 | [TEST] | `--check` sur les 24 dépôts | 1h | |
| T-067-03 | US-067 | [OPS] | Branches locales `docs/readme-fr-demo` (24) | 1.5h | |
| T-067-06 | US-067 | [REV] | Revue | 0.5h | |
| T-065-01 | US-065 | [DOC] | Audit des images existantes | 1.5h | |
| T-065-02 | US-065 | [DOC] | Protocole de capture | 0.5h | |
| T-065-03 | US-065 | [OPS] | Démo locale (`docker compose`) | 1h | |
| T-065-04 | US-065 | [FE-WEB] | Captures des plugins sans image | 2.5h | |
| T-065-06 | US-065 | [BE] | Insertion des images dans les README | 1h | |
| T-065-08 | US-065 | [REV] | Revue | 0.5h | |
| T-066-01 | US-066 | [DOC] | Analyse des endroits où l'hôte compte | 1h | |
| T-066-04 | US-066 | [DOC] | Runbook du sous-domaine et références | 1h | |
| T-064-06 | US-064 | [TEST] | ⚠️ Contrôle structurel du lot A (sacrifice n° 2) | 0.5h | |
| T-065-05 | US-065 | [FE-WEB] | ⚠️ Captures de comblement (sacrifice n° 1) | 1h | |
| T-065-07 | US-065 | [TEST] | ⚠️ Contrôle des images (sacrifice n° 2) | 0.5h | |
| T-067-04 | US-067 | [DOC] | ⚠️ Note de GO (sacrifice n° 3) | 0.5h | |
| T-066-02 | US-066 | [TEST] | ⚠️ Tests des hôtes de confiance (sacrifice n° 4) | 0.5h | |
| T-066-03 | US-066 | [BE] | ⚠️ `railway.ts` et configuration (sacrifice n° 4) | 1h | |
| T-066-07 | US-066 | [REV] | ⚠️ Revue (sacrifice n° 4) | 0.5h | |

## 📅 Calendaire (GO utilisateur, hors chemin critique)

| ID | US | Tâche | Estimation | Condition |
|----|-----|-------|------------|-----------|
| T-067-05 | US-067 | [OPS] Ouverture des 24 PR et merge (par Claude) | 1.5h | GO explicite, un pour l'ouverture, un pour le merge |
| T-066-05 | US-066 | [OPS] CNAME et domaine personnalisé Railway (par l'utilisateur) | 0.5h | Nom du sous-domaine fourni |
| T-066-06 | US-066 | [OPS] Vérification du certificat | 0.5h | Après T-066-05 |
| T-052-03 à T-052-07 | US-052 | [OPS] Moniteur, bascule, `X-Real-IP`, MCP, coût Railway (reportées) | — | GO et comptes de l'utilisateur |

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
- **Heures** : 37.5h estimées (dont 2.5h calendaires) | 0h consommées | 37.5h restantes (capacité ≈ 30-35h, ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
