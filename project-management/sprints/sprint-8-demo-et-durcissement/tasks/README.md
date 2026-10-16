# Tâches — Sprint 008 (Démo et durcissement)

> **Planifié, à démarrer au sprint 8.** Ces tâches ont été décomposées pendant le sprint 7 puis déplacées ici (décision de l'utilisateur du 2026-10-16, arbitrage de capacité : le sprint 7 est recentré sur les plugins). Le sprint-goal et le task-board du sprint 8 seront produits à son démarrage.

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-006](./US-006-tasks.md) | Spike : mode worker FrankenPHP | 2 | 5 | 7h | 🔲 |
| [US-029](./US-029-tasks.md) | Récepteur de webhooks de démo | 3 | 7 | 14h | 🔲 |
| [US-030](./US-030-tasks.md) | Guide de connexion MCP | 2 | 4 | 7h | 🔲 |
| [US-040](./US-040-tasks.md) | Durcissement complémentaire | 5 | 14 | 30h | 🔲 |
| [US-041](./US-041-tasks.md) | Qualité des données et du reset | 5 | 14 | 30.5h | 🔲 |

**Total** : 44 tâches | 88.5h | 17 points (5.2 h/point).

## Répartition par type

| Type | Nb tâches | Heures | % |
|------|-----------|--------|---|
| [DB] | 1 | 2h | 2% |
| [BE] | 15 | 40.5h | 46% |
| [FE-WEB] | 3 | 6h | 7% |
| [TEST] | 13 | 28h | 32% |
| [DOC] | 5 | 6h | 7% |
| [REV] | 6 | 5.5h | 6% |
| [OPS] | 1 | 0.5h | 1% |
| **TOTAL** | **44** | **88.5h** | |

## Dépendances avec le sprint 7

- T-040-12 (non-régression globale `HardeningTest`) dépend de **T-019-16** (intégration du plugin d'accès par canal dans la démo, sprint 7) : le sprint 7 doit avoir intégré US-019 avant cette tâche.
- T-040-02 (M2) prévoit « inscription visiteur désactivée, ou file d'attente invisible avec US-020 » : choix à arrêter selon l'état d'US-020 en fin de sprint 7 (T-020-15 en reprend la cohérence).
- Aucune autre dépendance entre les tâches du sprint 8 et celles du sprint 7.

## Traçabilité des constats reportés → tâches

| Constat | Tâche(s) | Preuve attendue (comportement rendu) |
|---------|----------|------|
| T-006-* (spike worker, sprint 6 glissé) | T-006-01 à T-006-05 | Test de fuite vert en mode worker, ADR-0013 avec gain mesuré |
| US-029 (sprint 6 reportée) | T-029-01 à T-029-07 | Webhook signé visible dans « Webhooks reçus » ; URL externe refusée |
| US-030 (sprint 6 reportée) | T-030-01 à T-030-04 | Commandes de Claire listées via MCP en suivant le guide |
| M2 clients saisis par les visiteurs (S6) | T-040-02, T-040-03 | Aucune donnée visiteur dans la liste clients rendue |
| M4 images orphelines (S6) | T-040-04, T-040-05 | Fichier orphelin purgé, fixtures conservées, échec signalé par /healthz |
| M5 clients HTTP du MCP (S6) | T-040-06, T-040-07 | Hôte non autorisé refusé par le garde sortant |
| M6 `/healthz` (S6) | T-040-08, T-040-09 | Rafale limitée, fréquence normale jamais en 429, pas de SQL par appel |
| M8 retour du simulateur (S6) | T-040-10, T-040-11 | Rejeu et falsification refusés, aucune commande créée |
| S2-R3 lignes CSV mal formées | T-041-01, T-041-05 | Erreur « fichier:ligne », 210 produits inchangés |
| S2-R4 doublons de SKU | T-041-02, T-041-05 | Doublon inter-fichiers signalé |
| S2-R5 `attributes_json` invalide | T-041-03, T-041-05 | Erreur explicite, pas de TypeError |
| S2-R6 BOM, en-tête, encodage | T-041-04, T-041-05 | BOM lu, encodage invalide rejeté |
| S2-R7 ≈ 420 requêtes au reset | T-041-06 (sacrifiable) | Requêtes du reset réduites d'au moins 50 % |
| S2-R8, R9, R10 | T-041-07 (sacrifiable) | Tests de vérification verts, statut écrit |
| S4-R3 prix « dès » en lot | T-041-08, T-041-09 | Requêtes constantes pour 5, 20 et 50 produits, mêmes valeurs |
| T-033-05 bandeau prochaine remise à zéro | T-041-10, T-041-11 | HTML rendu avec l'échéance 03:00 Paris, FR et EN |
| T-034-02 relevé du coût Railway | T-041-12, T-041-13 | Coût réel relevé à partir du 2026-10-23 ou tâche ouverte datée |

## Actions externes

| ID | Action | Condition |
|----|--------|-----------|
| T-041-12 | Relevé du coût Railway (calendaire) | Accès Railway de l'utilisateur ; à partir du 2026-10-23 (déploiement du 2026-10-16 + 7 jours) |

Aucune commande externe (railway, gh, push, Packagist) sans accord explicite de l'utilisateur.

## Fichiers

- [US-006 - Spike : mode worker FrankenPHP](./US-006-tasks.md)
- [US-029 - Récepteur de webhooks de démo](./US-029-tasks.md)
- [US-030 - Guide de connexion MCP](./US-030-tasks.md)
- [US-040 - Durcissement complémentaire](./US-040-tasks.md)
- [US-041 - Qualité des données et du reset](./US-041-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex : T-040-05)
- **Taille** : 0.5h - 8h max
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 calendaire
- Ordre de sacrifice si le sprint dérive : T-041-06/07 (S2-R7 à R10, 7.5h), puis T-041-08/09 (6h), puis T-041-10/11 (3h) ; US-006, US-029 et US-030 sont des Should/Could.
