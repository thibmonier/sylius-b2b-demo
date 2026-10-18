# Tâches : Sprint 016 (démo crédible)

> Sprint Planning Part 2 du 2027-01-25 (décomposition préparée le 2026-10-18). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-16`). Sprint 15 : [../../sprint-15-dossier-de-go/tasks/README.md](../../sprint-15-dossier-de-go/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-145](./US-145-tasks.md) | Images persistantes | 3 | 8 | 7 h | 🔲 |
| [US-142](./US-142-tasks.md) | « Me contacter » | 5 | 9 | 10,5 h | 🔲 |
| [US-143](./US-143-tasks.md) | Umami | 3 | 8 | 7 h | 🔲 |
| [US-092](./US-092-tasks.md) | Photos cohérentes | 5 | 10 | 10,5 h | 🔲 |
| [US-096](./US-096-tasks.md) | Données vivantes 1/2 | 5 | 9 | 10 h | 🔲 |
| [US-097](./US-097-tasks.md) | Données vivantes 2/2 et journal d'audit | 5 | 10 | 10 h | 🔲 |
| [US-061](./US-061-tasks.md) | Crédits des images | 1 | 4 | 2 h | 🔲 |

**Total décomposé** : 58 tâches | **57 h** pour 27 points, dont 2 h calendaires (T-145-08, T-142-09, T-143-08, T-092-10 : actions de Thibaut), soit **55 h de charge**.

## Capacité

Périmètre « démo d'abord » décidé par Thibaut (D1) : 27 points, soit ≈ 2,1 h par point (57 h pour 27 points), même ratio qu'au sprint 15 (73,5 h pour 35 points, tenus avec trois développeurs en parallèle). Les sept stories décidées ne totalisent que 27 points (≈ 34 annoncés au cadrage) : l'écart est un tampon de capacité, aucune story n'est ajoutée sans décision de Thibaut. Trois pistes parallèles qui ne partagent pas de fichiers : images (US-145, US-061 → US-092), thème (US-142 → US-143), fixtures (US-096 → US-097). Le Scrum Master suit la consommation au point d'étape du 2027-01-29 et applique l'ordre ci-dessous dès qu'il y a retard.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre (D1 : US-061, puis les tâches de finition), en s'arrêtant dès que la charge restante tient dans la capacité. Les Must ne sont pas sacrifiables, US-145 en tête.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | n/a | 55 h | n/a |
| 1 | US-061 entière (T-061-01 à T-061-04) | 2 h | 53 h | Pas de page Crédits publique ; le critère « crédits visibles » d'US-092 est reporté ; les crédits restent dans le manifeste |
| 2 | T-092-04 et T-092-08 (visuels de repli de catégorie, documentation des images) | 1,5 h | 51,5 h | Un produit sans photo adaptée garde son image actuelle jusqu'à nouvel ordre ; doc d'images à rattraper |
| 3 | T-142-07, T-143-06, T-096-08, T-097-09 (documentation de finition) | 2 h | 49,5 h | Variables et fixtures non documentées : dette datée à solder au sprint 17 |
| 4 | T-097-06 (événements métier au journal d'audit) | 1 h | 48,5 h | Journal d'audit toujours vide ; repris par US-088 (sprint 19) ; dette consignée dans la revue |

**Non sacrifiables** : toutes les Must (US-145, US-142, US-143, US-092, US-096, US-097) et leurs revues ; les vérifications de rendu réel (D4). Après le sacrifice n° 4, ≈ 48,5 h restent : si le point d'étape montre encore un déficit, la décision de couper une Must revient à Thibaut. Toute tâche sacrifiée est consignée dans la revue comme dette datée.

### Sacrifiables en un coup d'œil

⚠️ US-061 (2 h), T-092-04 et T-092-08 (1,5 h), T-142-07, T-143-06, T-096-08, T-097-09 (2 h), T-097-06 (1 h) : 6,5 h.

## Chemin critique et attentes

- **Images d'abord** : US-145 en tête (prérequis bloquant du dossier de GO) ; T-145-08 (Thibaut) vérifie sur la démo après le déploiement.
- **Crédits** : T-061-03 (page Crédits) avant T-092-06.
- **Thème** : T-142-03 → T-142-05 avant T-143-04 (événements sur les liens du composant) ; un seul worker sur `docs/deploiement-railway.md` à la fois (US-145, puis US-142, puis US-143).
- **Fixtures** : T-096-04 → T-096-05 avant T-097-03 ; un seul worker sur les fixtures et le reset à la fois ; `bin/profile-reset` en fin de chaque story (< 10 minutes).
- **Rendu réel final** de toutes les pages touchées avant la revue (T-142-06, T-143-05, T-092-07, T-096-07, T-097-08).
- **Actions de Thibaut** : T-145-08, T-142-09, T-143-08, T-092-10 ; sans elles, les composants d'URL restent masqués (D3).

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 100 mots au total).** Le rapport est rédigé **avant** la dernière vérification (action 2 de la rétro du sprint 15). Le Scrum Master refuse de clore une tâche sans lui et ne relance pas pour un rapport trop long : un rapport tronqué est un rapport refusé.

```markdown
## Rapport de fin (en premier, ≤ 100 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Rendu réel** : pages vues (URL, FR et EN) ou « sans objet »
- **Nettoyage** : `var/cache-<nom>` du worker supprimé (`bin/clean-test-caches`), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

**Règle disque et gel (actions 1 et 3 de la rétro du sprint 15)** : `df -h .` au démarrage de chaque worker et `bin/clean-test-caches` avant chaque lot de tests. En cas de gel disque, seul `bin/run-tests` est autorisé, ou une demande à l'orchestrateur : jamais de `phpunit` en direct, jamais de seuil `MIN_FREE_GB` abaissé (ni par variable d'environnement ni dans le script).

**Échec sur un test partagé (action 4 de la rétro du sprint 15)** : avant de signaler un échec sur un test touché par un autre développeur, mettre à jour le dépôt (état courant de la branche) et relancer ce test ; ne signaler que s'il échoue toujours.

**Règles propres au sprint** : tests via `TEST_DB_NAME=<db> WKHTMLTOPDF_BINARY="$PWD/tests/Fixtures/bin/fake-wkhtmltopdf" bin/run-tests …`, ECS sur `src tests` ; mobile mesuré avec `bin/measure-mobile` (variables Puppeteer documentées par T-141-04), pa11y WCAG2AAA ; seul l'orchestrateur reconstruit l'image Docker ; `git add` par chemins explicites ; aucun push, aucune PR, aucun merge sans GO ; aucun détail exploitable dans les documents (dépôt public) ; `main` est protégée : PR et contrôles requis, attendre la fin du run CI avant le merge ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets ; URL d'US-142 et d'US-143 vides par défaut, valeurs factices dans les tests (D3).
