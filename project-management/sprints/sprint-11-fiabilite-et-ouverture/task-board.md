# Task Board — Sprint 011 (Fiabilité et ouverture)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-049-01 | US-049 | [TEST] | Test rouge : erreur fatale pendant le reset | 1.5h | |
| T-049-02 | US-049 | [BE] | Marqueur « en cours » dans `ResetStatus` | 2.5h | |
| T-049-03 | US-049 | [TEST] | Test rouge : cache en sous-processus | 1h | |
| T-049-04 | US-049 | [BE] | `cache:clear` / `cache:warmup` en sous-processus | 2.5h | |
| T-049-07 | US-049 | [DOC] | Limite `demo_orders` actée, marqueur et sous-processus documentés | 0.5h | |
| T-049-09 | US-049 | [REV] | Revue | 1h | |
| T-050-03 | US-050 | [TEST] | Tests rouges des deux modes (privé, public) | 3h | |
| T-050-04 | US-050 | [BE] | Bandeaux, guide MCP et preflight suivent la porte | 3h | |
| T-050-05 | US-050 | [BE] | Mode public et inscriptions (décision, invité B2C, devis invité) | 2h | |
| T-050-01 | US-050 | [REV] | Revue de sécurité finale indépendante | 4h | |
| T-050-02 | US-050 | [BE] | Correction des constats bloquants | 4h | |
| T-050-07 | US-050 | [DOC] | Runbook `docs/ouverture-publique.md` | 3h | |
| T-050-12 | US-050 | [REV] | Revue | 1h | |
| T-049-05 | US-049 | [TEST] | ⚠️ Test rouge : plafond de corps Caddy (sacrifice n° 2) | 1.5h | |
| T-049-06 | US-049 | [BE] | ⚠️ `request_body max_size` dans le Caddyfile (sacrifice n° 2) | 1h | |
| T-050-06 | US-050 | [BE] | ⚠️ `robots.txt` / noindex (sacrifice n° 1) | 2h | |
| T-050-08 | US-050 | [OPS] | ⚠️ `railway.ts` préparé pour le mode public (sacrifice n° 3) | 1.5h | |

## 📅 Calendaire (GO utilisateur, hors chemin critique)

| ID | US | Tâche | Estimation | Condition |
|----|-----|-------|------------|-----------|
| T-049-08 | US-049 | [OPS] Relevé complet du coût Railway (ex T-048-11) | 0.5h | GO, accès Railway, pas avant 2026-10-23 |
| T-050-09 | US-050 | [OPS] Moniteur externe sur `/healthz` | 0.5h | Compte de l'utilisateur, avant la bascule |
| T-050-10 | US-050 | [OPS] Bascule le jour J | 1h | GO explicite, runbook T-050-07 |
| T-050-11 | US-050 | [OPS] Parcours MCP avec un vrai assistant (T-030-03) | 1h | Après la bascule |

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
- **Tâches** : 21 total | 0 terminées (0 %)
- **Heures** : 38h estimées (dont 3h calendaires) | 0h consommées | 38h restantes (capacité ≈ 30-35h, ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
