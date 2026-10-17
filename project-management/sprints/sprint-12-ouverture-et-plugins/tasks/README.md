# Tâches — Sprint 012 (ouverture et plugins)

> Sprint Planning Part 2 du 2026-11-27 (décomposition préparée le 2026-10-17). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-12`). Sprint 11 : [../../sprint-11-fiabilite-et-ouverture/tasks/README.md](../../sprint-11-fiabilite-et-ouverture/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-052](./US-052-tasks.md) | Ouverture publique de la démo | 3 | 9 | 8.5h | 🔲 |
| [US-053](./US-053-tasks.md) | Simulateur PunchOut OCI dans la démo | 4 | 8 | 12.5h | 🔲 |
| [US-054](./US-054-tasks.md) | Plugin parc d'équipements client, tranche 1 | 4 | 9 | 14h | 🔲 |
| [US-055](./US-055-tasks.md) | Actions de rétro et dette | 3 | 8 | 8.5h | 🔲 |

**Total décomposé** : 34 tâches | **43.5 h** pour 14 points, dont 4 h calendaires (T-052-03 à T-052-07 et T-054-04 : GO, comptes et commandes Railway de l'utilisateur) hors charge continue, soit **39.5 h de charge**.

## Capacité

Ratio observé : 3,6 à 3,7 h/point, mais les stories d'ouverture (US-052) et de planification de plugin (US-054) ont une part importante de documentation et d'attente. Capacité cible retenue : **30-35 h**. Charge décomposée : 39.5 h, soit 4.5 h au-dessus de la fourchette : le sprint tient à 35 h si les sacrifices 1 à 3 sont appliqués.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans la capacité.

| # | Tâche sacrifiée | Heures | Charge restante | Conséquence |
|---|-----------------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | — | 39.5h | — |
| 1 | T-052-02 script de vérification du mode public | 2h | 37.5h | Les 12 vérifications restent des commandes `curl` du runbook, lancées à la main |
| 2 | T-054-07 test de fumée et T-054-08 README du plugin | 2h | 35.5h | Le plugin n'a qu'un squelette qui démarre (CI), README minimal reporté en US-056 |
| 3 | T-055-04 `docs/tests-locaux.md` | 0.5h | 35h | `bin/run-tests` documenté seulement dans son en-tête |
| 4 | Coupe franche : T-054-05, T-054-06, T-054-09 (skeleton, socle qualité, revue) | 6.5h | 28.5h | La tranche 1 se limite à la mini-spec, l'ADR et le verdict Plus ; le socle technique passe en backlog |

**Non sacrifiables** : toute US-052 hors T-052-02 (la bascule et les vérifications sont le but), toute US-053 (garde-fous inclus), T-054-01 à T-054-03 (spec, ADR, verdict Plus), T-055-02 et T-055-03 (cache de test isolé, source des faux échecs du sprint 11), T-055-07 (US-031). Les [OPS] calendaires ne se sacrifient pas : elles restent ouvertes si l'utilisateur n'a pas donné son GO.

### Sacrifiables en un coup d'œil

⚠️ T-052-02, T-054-07, T-054-08, T-055-04 (4.5 h) ; coupe franche T-054-05, T-054-06, T-054-09 (6.5 h).

## Chemin critique et attentes

- **US-052** : T-052-01 → T-052-02 → (GO) T-052-04 → T-052-05 / T-052-06 → T-052-08 → T-052-09 ; chemin de charge ≈ 5 h. Le sprint ne dépend pas du GO : sans lui, T-052-04 à T-052-06 restent ouvertes.
- **US-053** : T-053-01 → T-053-02 → T-053-03 → T-053-04 → T-053-05 → T-053-06 → T-053-07 → T-053-08 (≈ 12.5 h).
- **US-054** : T-054-01 → T-054-02 → T-054-05 → T-054-06 → T-054-07 → T-054-09 (≈ 11 h). T-054-04 (dépôt GitHub) attend le verdict T-054-03 et une confirmation explicite.
- **US-055** : T-055-02 → T-055-03 en première semaine (≈ 4 h) ; le reste est indépendant.
- **Calendaires / utilisateur** : T-052-03 (moniteur, avant la bascule), T-052-04 (bascule, GO explicite), T-052-05 (`X-Real-IP`, après la bascule), T-052-06 (parcours MCP), T-052-07 (coût Railway), T-054-04 (dépôt GitHub).

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 120 mots).** Le Scrum Master refuse de clore une tâche sans lui.

```markdown
## Rapport de fin (en premier, ≤ 120 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Nettoyage** : `var/cache-*` du worker supprimé (`rm -rf var/cache-<nom>`), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

**Nettoyage de fin de worker (action 1 de la rétro du sprint 11)** : avant de rendre son rapport, le worker supprime le dossier `var/cache-*` qu'il a créé (`rm -rf var/cache-<son nom>`) et, s'il a utilisé `bin/run-tests`, laisse celui-ci retirer son cache. Il note l'espace libre (`df -h .`) dans le rapport. Le Scrum Master vérifie `df` avant de lancer un autre worker lourd ; **arrêt si moins de 5 Go libres**.

Un worker arrêté sans ce bloc n'a pas terminé : le Scrum Master vérifie l'état du dépôt (`git status`, diff) et les dossiers `var/cache-*` avant toute relance.

## Consigne d'orchestration des relecteurs (action 3 de la rétro du sprint 11)

- Les agents de relecture spécialisés (`security-auditor`, `symfony-reviewer`, `php-reviewer`) sont lancés avec **12 tours minimum** pour une revue de plusieurs dizaines de fichiers ; si la limite ne peut pas être relevée, la revue large est confiée à un agent `general-purpose`.
- La consigne se termine par : « Dans tes derniers tours, rends ton rapport même incomplet : constats trouvés, fichiers non relus. Ne termine jamais sans rapport. »
- Un relecteur arrêté par la limite de tours sans rapport est un échec d'orchestration à consigner dans la rétro, pas une revue réussie. Cible du sprint : 0 relance pour limite de tours.
- Le relecteur indépendant ne reçoit pas le contexte d'implémentation, mais la liste des fichiers à relire et le périmètre.

## Isolation des workers (obligatoire)

Plusieurs workers travaillent dans le même dépôt. Règles à coller dans chaque consigne :

- `git add` par **chemins explicites**, jamais `git add -A`, `git add .` ni `git commit -a`.
- Un commit ne doit **jamais embarquer le fichier d'un autre worker** : vérifier `git diff --cached --stat` avant de valider ; en cas de doute, ne pas commiter et le signaler dans le rapport.
- Chaque tâche liste ses fichiers (indicatifs) ; deux workers ne modifient pas le même fichier en parallèle (voir « Ressources partagées » dans [../sprint-dependencies.md](../sprint-dependencies.md)).
- Le plugin de US-054 vit dans un dépôt séparé : ses commits n'entrent jamais dans le dépôt de la démo, et inversement.
- Aucun commit sans demande de l'utilisateur.

## Actions externes (obligatoire)

- Aucun push, merge, création de dépôt, tag, soumission Packagist ni commande Railway (`redeploy`, `variables`, `config apply`) par un worker.
- Ces actions sont des tâches [OPS] de l'utilisateur ; le worker prépare la commande exacte et attend la confirmation.
- Aucune lecture de `.env` ni de secret.

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [BE] | 7 | 15.5h | 36 % |
| [TEST] | 5 | 8h | 18 % |
| [DOC] | 10 | 10.5h | 24 % |
| [REV] | 6 | 5.5h | 13 % |
| [OPS] | 6 | 4h | 9 % |
| **Total** | **34** | **43.5h** | |

## Points d'attention

- **US-052** : Claude ne lance aucune commande Railway ; il prépare le diff de `.railway/railway.ts` et les commandes, vérifie ensuite avec `curl` et les tests du mode public. Retour arrière en un geste décrit dans `docs/ouverture-publique.md`.
- **US-053** : test rouge d'abord pour chaque correctif ; le plugin n'est pas modifié (écart de contrat tracé, pas contourné). Mêmes garde-fous que le cXML.
- **US-054** : ne pas coder le fonctionnel du plugin (YAGNI) : il est en backlog (US-056 à US-060). Si Sylius Plus couvre le besoin, ne rien promouvoir (politique de communication).
- **US-055** : `bin/run-tests` ne touche jamais la base de développement ni le vrai `var/`.
- **Contraintes** : disque (seuil 5 Go), aucun rebuild Docker local superflu, aucun commit, push, merge ou déploiement sans GO de l'utilisateur.

## Fichiers

- [US-052 - Ouverture publique de la démo](./US-052-tasks.md)
- [US-053 - Simulateur PunchOut OCI dans la démo](./US-053-tasks.md)
- [US-054 - Plugin parc d'équipements client, tranche 1](./US-054-tasks.md)
- [US-055 - Actions de rétro et dette](./US-055-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex. T-053-04) ; T-050-09, T-050-10, T-050-11 et T-049-08 (sprint 11) deviennent T-052-03, T-052-04, T-052-06 et T-052-07.
- **Taille** : 0.5h à 8h max.
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 Calendaire
- **Marqueur** : ⚠️ sacrifiable.
