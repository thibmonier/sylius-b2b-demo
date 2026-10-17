# Tâches — Sprint 013 (vitrine des plugins)

> Sprint Planning Part 2 du 2026-12-11 (décomposition préparée le 2026-10-17). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-13`). Sprint 12 : [../../sprint-12-ouverture-et-plugins/tasks/README.md](../../sprint-12-ouverture-et-plugins/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-063](./US-063-tasks.md) | Classement Sylius Plus et charte de ton | 2 | 6 | 6.5h | 🔲 |
| [US-064](./US-064-tasks.md) | Bloc standard, outil et README FR du lot A | 4 | 7 | 9.5h | 🔲 |
| [US-067](./US-067-tasks.md) | README FR du lot B et livraison des 24 dépôts | 3 | 6 | 8h | 🔲 |
| [US-065](./US-065-tasks.md) | Captures d'écran manquantes et existantes | 4 | 8 | 8.5h | 🔲 |
| [US-066](./US-066-tasks.md) | Préparation du sous-domaine de la démo | 2 | 7 | 5h | 🔲 |

**Total décomposé** : 34 tâches | **37.5 h** pour 15 points, dont 2.5 h calendaires (T-067-05, T-066-05, T-066-06 : GO et actions de l'utilisateur) hors charge continue, soit **35 h de charge**.

## Capacité

Ratio observé au sprint 12 : 3,1 h/point ; les stories de ce sprint sont surtout documentaires et scriptées (traductions, captures), d'où 2,5 h/point. Capacité cible retenue : **30-35 h**. Charge décomposée : 35 h, en haut de la fourchette : le sprint tient à 30,5 h si les sacrifices 1 à 4 sont appliqués.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans la capacité.

| # | Tâche sacrifiée | Heures | Charge restante | Conséquence |
|---|-----------------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | — | 35h | — |
| 1 | T-065-05 captures de comblement (boutique ou admin manquants des plugins déjà illustrés) | 1h | 34h | Seuls les plugins sans image sont complétés ; les manques restent listés dans `docs/vitrine-plugins.md` |
| 2 | T-064-06 et T-065-07 contrôles dédiés (structure FR, liens et poids des images) | 1h | 33h | Les contrôles passent par `bin/vitrine-readme --check` seul et une revue visuelle |
| 3 | T-067-04 note de GO | 0.5h | 32.5h | Le GO se fait sur la liste des branches rendue par l'outil |
| 4 | Coupe franche : T-066-02, T-066-03, T-066-07 (test, `railway.ts`, revue) | 2h | 30.5h | Seuls l'analyse et le runbook du sous-domaine sont livrés ; la configuration passe au sprint 14 |

**Non sacrifiables** : toute US-063 (le ton en dépend), T-064-01 à T-064-05, T-064-07 (bloc, outil, lot A), toute US-067 hors T-067-04, les captures des plugins sans image (T-065-01 à T-065-04, T-065-06, T-065-08), T-066-01 et T-066-04 (analyse et runbook). Les [OPS] calendaires ne se sacrifient pas : elles restent ouvertes si l'utilisateur n'a pas donné son GO ou le nom du sous-domaine.

### Sacrifiables en un coup d'œil

⚠️ T-065-05, T-064-06, T-065-07, T-067-04 (2.5 h) ; coupe franche T-066-02, T-066-03, T-066-07 (2 h).

## Chemin critique et attentes

- **US-063** : T-063-01 → T-063-02 → T-063-03 → T-063-04 → T-063-05 → T-063-06 (≈ 6.5 h) ; en premier, le ton conditionne le bloc.
- **US-064** : T-064-01 → T-064-02 → T-064-03 → T-064-04 → T-064-05 → T-064-06 → T-064-07 (≈ 9.5 h) ; T-064-02 attend T-063-04.
- **US-067** : T-067-01 (après T-064-04) → T-067-02 → T-067-03 → T-067-04 → (GO) T-067-05 → T-067-06.
- **US-065** : T-065-01 → T-065-02 → T-065-03 → T-065-04 → T-065-05 → T-065-06 (après T-067-03) → T-065-07 → T-065-08 ; indépendante des traductions jusqu'à T-065-06.
- **US-066** : T-066-01 → T-066-02 → T-066-03 → T-066-04 → T-066-05 (utilisateur, nom du sous-domaine) → T-066-06 → T-066-07.
- **Calendaires / utilisateur** : T-067-05 (GO d'ouverture des PR et de merge), T-066-05 (CNAME et domaine personnalisé), T-066-06 (certificat, après T-066-05). US-052 : T-052-03 à T-052-07.

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 120 mots).** Le Scrum Master refuse de clore une tâche sans lui.

```markdown
## Rapport de fin (en premier, ≤ 120 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Nettoyage** : `var/cache-*` du worker supprimé (`rm -rf var/cache-<nom>`), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

**Règles propres au sprint** : avant de toucher un dépôt de plugin, `git fetch` et vérification de la branche courante (mémoire plugin-repo-branch-check) ; partir de `main` à jour ; `git add` par chemins explicites ; aucun push, aucune PR, aucun merge sans GO ; pas de nettoyage destructif d'un dépôt sale (le signaler). Nettoyage de fin de worker et ligne `df` comme au sprint 12.
