# Tâches — Sprint 010 (vitrine et reports)

> Sprint Planning Part 2 du 2026-10-30 (décomposition préparée le 2026-10-17). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-10`). Sprint 9 : [../../sprint-9-reports/tasks/README.md](../../sprint-9-reports/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-029](./US-029-tasks.md) | Récepteur de webhooks de démo (reprise du sprint 9) | 3 | 7 | 15.5h | 🔲 |
| [US-046](./US-046-tasks.md) | Encart Sylius Plus sur la page Plugins | 2 | 5 | 5.5h | 🔲 |
| [US-047](./US-047-tasks.md) | Images des produits de démo | 5 | 11 | 22.5h | 🔲 |
| [US-048](./US-048-tasks.md) | Reports techniques du sprint 9 | 3 | 14 | 19h | 🔲 |

**Total décomposé** : 37 tâches | **62,5 h** pour 13 points, dont 2 h calendaires (T-048-11 à T-048-13, GO utilisateur) hors charge continue, soit **60,5 h de charge**.

## Capacité

Ratio observé aux sprints 8 et 9 : 3,6 à 3,7 h/point. 13 points ≈ 47 h ; capacité cible retenue : **≈ 50 h**. Charge décomposée : 60,5 h, soit **+10,5 h (≈ 121 %)**. Les tâches sacrifiables couvrent 12 h : le sprint tient à 48,5 h si tout l'ordre ci-dessous est appliqué.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans ≈ 50 h. Rien de ce qui est listé ne fait partie du critère d'acceptation de la story, sauf la dernière ligne (critère « requêtes constantes » de la Wishlist, action 5 de la rétro).

| # | Tâche sacrifiée | Heures | Charge restante | Conséquence |
|---|-----------------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | — | 60,5h | — |
| 1 | T-048-06 `demo_container_fill` groupé | 2.5h | 58h | Cible documentée par T-048-04, non atteinte |
| 2 | T-048-05 `demo_orders` | 3h | 55h | Idem |
| 3 | T-047-09 crédits des images (page À propos) | 1h | 54h | Le manifeste suffit (CC0) |
| 4 | T-048-10 entrypoint, fixtures après interruption | 1.5h | 52,5h | Mineur préexistant, reste au backlog |
| 5 | T-048-08 et T-048-09 (capacité périmée, NFC) | 1.5h | 51h | Mineurs, restent au backlog |
| 6 | T-048-07 N+1 Wishlist | 2.5h | 48,5h | Listes restent à 28 requêtes à 50 produits |

**Non sacrifiables** : US-029 (arbitrage utilisateur), US-046 (dont T-046-04, validation bloquante du texte), US-047 hors T-047-09, T-048-01 à T-048-04, T-048-14. T-048-04 (profilage) est conservée même si T-048-05 et T-048-06 sont sacrifiées : l'objectif chiffré reste publié.

### Sacrifiables en un coup d'œil

⚠️ T-048-05, T-048-06, T-048-07, T-048-08, T-048-09, T-048-10, T-047-09 (12 h).

## Chemin critique et attentes

- **US-047** : T-047-01 → T-047-02 → T-047-04 → T-047-06 → T-047-07 → T-047-08 → T-047-11 (≈ 17,5 h). T-047-04 est un lancement manuel unique qui demande le GO de l'utilisateur et le réseau.
- **US-029** : T-029-01 → T-029-02 → T-029-05 → T-029-07 (≈ 10 h).
- **US-046** : T-046-04 est **bloquante** : aucun merge de l'encart sans la validation du texte par l'utilisateur.
- **Calendaires / utilisateur** : T-048-11 à T-048-13 (GO et accès Railway), T-047-04 (GO de lancement). Les dates de T-041-15 (≥ 2026-10-23) sont dépassées au démarrage du sprint.

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel.** Le Scrum Master refuse de clore une tâche dont le worker n'a pas rendu le bloc complet, et le consigne dans la rétro (objectif : 100 % des workers rendent le bloc, 0 relance faute de rapport).

```markdown
## Rapport de fin (obligatoire)

- **Tâches** : T-XXX-YY, T-XXX-ZZ
- **État** : terminé | bloqué | partiel (si bloqué ou partiel : cause et ce qu'il reste)
- **Fichiers créés ou modifiés** : liste des chemins
- **Vérifications exécutées** : commandes lancées et résultat (tests, ECS, PHPStan), ou « non exécutées » avec la raison
- **Commit** : fait (hash) | non fait, pourquoi (par défaut : aucun commit sans demande de l'utilisateur)
- **Écarts et décisions** : écart de périmètre ou d'estimation, décision prise
- **Besoin de l'utilisateur** : GO, accès ou validation attendus, sinon « aucun »
```

Un worker arrêté sans ce bloc n'est pas considéré comme ayant terminé : le Scrum Master vérifie l'état du dépôt (`git status`, diff) avant toute relance.

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [DB] | 1 | 2h | 3 % |
| [BE] | 12 | 28.5h | 46 % |
| [FE-WEB] | 3 | 5h | 8 % |
| [TEST] | 6 | 12h | 19 % |
| [DOC] | 5 | 4h | 6 % |
| [REV] | 5 | 4h | 6 % |
| [OPS] | 5 | 7h | 11 % |
| **Total** | **37** | **62.5h** | |

## Points d'attention

- **US-029** : tâches reprises à l'identique du sprint 9 (compatibilité avec le garde sortant M5, route exemptée par chemin exact, hôte `127.0.0.1`). Thread FrankenPHP libre : threads bornés 8/16 par ADR-0013.
- **US-046** : texte d'après la documentation officielle de Sylius Plus, aucune promesse de compatibilité, validation utilisateur avant merge, pas de promotion des plugins qui recouvrent Sylius Plus.
- **US-047** : Openverse (sans clé), CC0 uniquement, script manuel lancé une fois, jamais d'appel au runtime ni en CI, budget de taille du dépôt (hypothèse ≤ 80 Ko par image, ≤ 15 Mo au total, à confirmer au spike), disque local ~3 Go.
- **US-048** : aucun objectif chiffré avant le profilage (T-048-04) ; chaque correctif a un test rouge avant correction ; les tâches Railway ne sont jamais closes sur une estimation.
- **Ordre conseillé** : T-048-04 tôt (la base de comparaison change quand US-047 ajoute les images : mesurer avant et après si possible), puis US-047 et US-029 en parallèle, T-046-04 dès que l'encart est prêt.
- **Contraintes** : disque ~3 Go, aucun rebuild Docker local (CI pour les images), aucun commit, push, merge ni déploiement sans GO de l'utilisateur.

## Fichiers

- [US-029 - Récepteur de webhooks de démo](./US-029-tasks.md)
- [US-046 - Encart Sylius Plus sur la page Plugins](./US-046-tasks.md)
- [US-047 - Images des produits de démo](./US-047-tasks.md)
- [US-048 - Reports techniques du sprint 9](./US-048-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex. T-047-04) ; les identifiants de T-029-xx sont conservés ; T-041-15 devient T-048-11.
- **Taille** : 0.5h à 8h max.
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 Calendaire
- **Marqueur** : ⚠️ sacrifiable.
