# Tâches — Sprint 011 (fiabilité et ouverture)

> Sprint Planning Part 2 du 2026-11-13 (décomposition préparée le 2026-10-17). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-11`). Sprint 10 : [../../sprint-10-vitrine-et-reports/tasks/README.md](../../sprint-10-vitrine-et-reports/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-049](./US-049-tasks.md) | Fiabilité de la remise à zéro | 3 | 9 | 12h | 🔲 |
| [US-050](./US-050-tasks.md) | Préparer l'ouverture publique de la démo | 5 | 12 | 26h | 🔲 |

**Total décomposé** : 21 tâches | **38 h** pour 8 points, dont 3 h calendaires (T-049-08, T-050-09, T-050-10, T-050-11 : GO et comptes de l'utilisateur) hors charge continue, soit **35 h de charge**.

## Capacité

Ratio observé : 3,6 à 3,7 h/point. 8 points ≈ 30 h ; capacité cible retenue : **30-35 h**. Charge décomposée : 35 h, en haut de la fourchette. Les tâches sacrifiables couvrent 6 h : le sprint tient à 29 h si tout l'ordre ci-dessous est appliqué.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans la capacité.

| # | Tâche sacrifiée | Heures | Charge restante | Conséquence |
|---|-----------------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | — | 35h | — |
| 1 | T-050-06 `robots.txt` / noindex | 2h | 33h | Décision consignée dans le runbook, implémentation reportée |
| 2 | T-049-05 et T-049-06 plafond Caddy `request_body max_size` | 2.5h | 30.5h | Constat mineur de revue reste ouvert, tracé au backlog |
| 3 | T-050-08 `railway.ts` préparé pour le mode public | 1.5h | 29h | Les variables à changer restent décrites dans le runbook, éditées à la bascule |

**Non sacrifiables** : T-049-01 à T-049-04 (cœur de la fiabilité du reset), T-049-07, T-049-09, toute US-050 hors T-050-06 et T-050-08 (la revue de sécurité T-050-01 et ses correctifs T-050-02 conditionnent l'ouverture). Les [OPS] calendaires ne se sacrifient pas : elles restent ouvertes si l'utilisateur n'a pas donné son GO.

### Sacrifiables en un coup d'œil

⚠️ T-050-06, T-049-05, T-049-06, T-050-08 (6 h).

## Chemin critique et attentes

- **US-049** : T-049-01 → T-049-02 (marqueur) ; T-049-03 → T-049-04 (sous-processus) ; T-049-05 → T-049-06 (Caddy) ; chemin le plus long ≈ 4 h.
- **US-050** : T-050-03 → T-050-04 → T-050-05 → T-050-01 → T-050-02 → T-050-07 → T-050-12 (≈ 20,5 h). La revue T-050-01 passe sur le mode public déjà implémenté.
- **Calendaires / utilisateur** : T-049-08 (relevé de coût, après le 2026-10-23), T-050-09 (moniteur externe, avant la bascule), T-050-10 (bascule, GO explicite, éventuellement hors sprint), T-050-11 (parcours MCP, après la bascule).

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 120 mots).** Le Scrum Master refuse de clore une tâche sans lui.

```markdown
## Rapport de fin (en premier, ≤ 120 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

Un worker arrêté sans ce bloc n'a pas terminé : le Scrum Master vérifie l'état du dépôt (`git status`, diff) avant toute relance.

## Isolation des workers (obligatoire)

Plusieurs workers travaillent dans le même dépôt. Règles à coller dans chaque consigne :

- `git add` par **chemins explicites**, jamais `git add -A`, `git add .` ni `git commit -a`.
- Un commit ne doit **jamais embarquer le fichier d'un autre worker** : vérifier `git diff --cached --stat` avant de valider ; en cas de doute, ne pas commiter et le signaler dans le rapport.
- Chaque tâche liste ses fichiers (indicatifs) ; deux workers ne modifient pas le même fichier en parallèle (voir « Ressources partagées » dans [../sprint-dependencies.md](../sprint-dependencies.md)).
- Aucun commit sans demande de l'utilisateur.

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [BE] | 7 | 17h | 45 % |
| [TEST] | 4 | 7h | 18 % |
| [DOC] | 2 | 3.5h | 9 % |
| [REV] | 3 | 6h | 16 % |
| [OPS] | 5 | 4.5h | 12 % |
| **Total** | **21** | **38h** | |

## Points d'attention

- **US-049** : test rouge d'abord pour chaque correctif ; l'erreur fatale est simulée sans épuiser réellement la mémoire de la CI (sous-processus de test dédié). Le `-d memory_limit=1G` du crontab est conservé.
- **US-050** : l'agent de revue est indépendant (aucun contexte d'implémentation) et travaille sur le mode public déjà en place. Politique : démo publique mais non promue sur les canaux Sylius, aucune donnée réelle. Bascule uniquement sur GO explicite.
- **Contraintes** : disque ~3 Go, aucun rebuild Docker local, aucun commit, push, merge ni déploiement sans GO de l'utilisateur.

## Fichiers

- [US-049 - Fiabilité de la remise à zéro](./US-049-tasks.md)
- [US-050 - Préparer l'ouverture publique de la démo](./US-050-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex. T-050-04) ; T-048-11 (ex T-041-15) devient T-049-08.
- **Taille** : 0.5h à 8h max.
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 Calendaire
- **Marqueur** : ⚠️ sacrifiable.
