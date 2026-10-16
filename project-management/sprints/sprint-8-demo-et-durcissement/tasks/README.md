# Tâches — Sprint 008 (Démo et durcissement)

> Décomposition du sprint 8 (Sprint Planning Part 2, 2026-10-16), après arbitrage de capacité de l'utilisateur : 5 stories, 25 points. US-030, US-040 et US-041 avaient été décomposées au sprint 7 puis reportées ; US-042 et US-043 sont décomposées ici. US-006 et US-029 sont au sprint 9 ([sprint-9-reports](../../sprint-9-reports/tasks/README.md)). Task board : [../task-board.md](../task-board.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-030](./US-030-tasks.md) | Guide de connexion MCP | 2 | 4 | 7h | 🔲 |
| [US-040](./US-040-tasks.md) | Durcissement complémentaire | 8 | 14 | 30h | 🔲 |
| [US-041](./US-041-tasks.md) | Qualité des données et du reset (sans T-041-06 à 09 ni T-041-15) | 5 | 10 | 17.5h | 🔲 |
| [US-042](./US-042-tasks.md) | Routes restreintes configurables dans channel-access | 5 | 11 | 17h | 🔲 |
| [US-043](./US-043-tasks.md) | Statut d'approbation dans le plugin b2b-mcp | 5 | 12 (+ T-043-08 fusionnée) | 19h | 🔲 |

**Total** : 51 tâches | 90.5h | 25 points (3,6 h/point).

## Arbitrage de capacité

Décision utilisateur du 2026-10-16 : le sprint 8 passe de 126,5 h (7 stories, 23 points provisoires) à 90,5 h (5 stories, 25 points), pour une capacité d'environ 92 h (25 points à 3,7 h/point).

| Mesure | Effet |
|--------|-------|
| US-006 (spike worker, 7h) et US-029 (webhooks de démo, 14h) → sprint 9 | -21h |
| T-041-06 à 09 (S2-R7 à R10, S4-R3 : 13,5h) et T-041-15 (0,5h) → sprint 9 | -14h |
| Revue de sécurité d'US-042 et d'US-043 mutualisée (T-042-06 = 2h ; T-043-08 fusionnée, 0h) | -1h |
| Ré-estimation : US-040 5→8, US-042 3→5, US-043 3→5 (heures réelles des fichiers de tâches) | points alignés sur les heures |

Sprint 9 : 35h (US-006 7h, US-029 14h, T-041-06 à 09 et T-041-15 14h). Aucune tâche du sprint 8 ne dépend d'une tâche reportée.

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [BE] | 17 | 39h | 43% |
| [FE-WEB] | 2 | 3.5h | 4% |
| [TEST] | 15 | 30.5h | 34% |
| [DOC] | 5 | 6.5h | 7% |
| [REV] | 7 | 7h | 8% |
| [OPS] | 5 | 4h | 4% |
| **TOTAL** | **51** | **90.5h** | |

## Adaptations décidées au planning

- **T-041-12** (relevé du coût Railway) : devient un relevé **partiel** (coût depuis le 2026-10-16 + projection mensuelle via `railway usage`, lecture seule, GO utilisateur, 1 h). Le relevé complet à J+7 (à partir du 2026-10-23), **T-041-15** (0,5 h), est reporté au sprint 9.
- **T-019-16** (intégration d'US-019 dans la démo, sprint 7) : faite, dépendance de T-040-12 satisfaite.
- Les références « T-033-05 » et « T-034-02 » dans US-041 sont des identifiants historiques de constats, non des dépendances.

## Dépendances avec les sprints précédents

- T-040-12 (`HardeningTest`) : T-019-16 ✅ satisfaite.
- T-040-02 (M2) : choix « inscription visiteur désactivée » ou « file d'attente invisible » selon l'état d'US-020 ; T-020-15 en reprend la cohérence.
- US-042 et US-043 : US-019 et US-020 du sprint 7 (plugins intégrés).
- **Revue de sécurité mutualisée** : T-042-06 couvre channel-access et b2b-mcp (une passe) et remplace T-043-08 ; elle dépend des tests et de la documentation des deux plugins (T-042-03 à 05, T-043-05 à 07) ; les CI T-042-07 et T-043-09 en dépendent.
- Ordre inter-stories du sprint : US-043 (T-043-12) avant T-030-* (le guide MCP décrit le comportement des comptes non approuvés) ; T-042-09 et T-043-11 seulement après le GO de publication correspondant.

## Traçabilité des constats reportés → tâches

| Constat | Tâche(s) | Preuve attendue (comportement rendu) |
|---------|----------|------|
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
| S2-R7, S2-R8 à R10 | T-041-06, T-041-07 | ➡️ sprint 9 |
| S4-R3 prix « dès » en lot | T-041-08, T-041-09 | ➡️ sprint 9 |
| T-033-05 bandeau prochaine remise à zéro | T-041-10, T-041-11 | HTML rendu avec l'échéance 03:00 Paris, FR et EN |
| T-034-02 relevé du coût Railway | T-041-12, T-041-13 (partiel) ; T-041-15 (complet) ➡️ sprint 9 | Coût à date et projection ; relevé J+7 au sprint 9 (à partir du 2026-10-23) |
| Routes restreintes configurables (rétro S7) | T-042-01 à T-042-11 | Route configurée refusée ; `RestrictPriceRoutesPass` supprimée ; plugin 0.2.0 après GO |
| Statut d'utilisabilité du compte MCP (rétro S7) | T-043-01 à T-043-13 | Compte en attente refusé au consentement et sur `/_mcp` ; `McpApprovalGuard` supprimé ; version mineure après GO |

## Actions externes (aucune sans accord explicite de l'utilisateur)

| ID | Action | Condition |
|----|--------|-----------|
| T-041-12 | `railway usage` en lecture (relevé partiel) | **GO utilisateur**, accès Railway |
| T-042-07, T-043-09 | Push de branche du plugin pour la CI | **GO utilisateur** |
| T-042-08 | Tag et release 0.2.0 de channel-access (Packagist) | **GO utilisateur**, revue de sécurité sans constat ouvert et CI verte |
| T-043-10 | Tag de la version mineure de b2b-mcp (Packagist) | **GO utilisateur**, idem |

Contraintes disque : aucune installation locale, aucun rebuild d'image ; la CI fait foi. Aucun commit sans demande.

## Fichiers

- [US-030 - Guide de connexion MCP](./US-030-tasks.md)
- [US-040 - Durcissement complémentaire](./US-040-tasks.md)
- [US-041 - Qualité des données et du reset](./US-041-tasks.md)
- [US-042 - Routes restreintes configurables dans channel-access](./US-042-tasks.md)
- [US-043 - Statut d'approbation dans le plugin b2b-mcp](./US-043-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex : T-042-05)
- **Taille** : 0.5h - 8h max
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 calendaire
- Ordre de sacrifice si le sprint dérive : T-041-10/11 (bandeau), puis T-030-* ; US-042 et US-043 : sans GO, les contournements de la démo restent. US-006, US-029 et T-041-06 à 09 sont déjà au sprint 9.
- Statut ➡️ : tâche reportée au sprint 9 (non comptée) ; ➡️ fusionnée : tâche absorbée par une autre (T-043-08 dans T-042-06).
