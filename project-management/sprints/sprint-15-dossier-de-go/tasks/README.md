# Tâches : Sprint 015 (dossier de GO)

> Sprint Planning Part 2 du 2027-01-11 (décomposition préparée le 2026-10-18). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-15`). Sprint 14 : [../../sprint-14-bloquants-ouverture/tasks/README.md](../../sprint-14-bloquants-ouverture/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-078](./US-078-tasks.md) | SEO et vitesse mobile | 5 | 9 | 10 h | 🔲 |
| [US-081](./US-081-tasks.md) | Chaîne d'approvisionnement | 5 | 8 | 10 h | 🔲 |
| [US-082](./US-082-tasks.md) | Durcissement applicatif | 5 | 9 | 10 h | 🔲 |
| [US-083](./US-083-tasks.md) | Licence du plugin MCP | 1 | 4 | 2,5 h | 🔲 |
| [US-085](./US-085-tasks.md) | Dossier de GO | 2 | 5 | 4 h | 🔲 |
| [US-089](./US-089-tasks.md) | Accueil connecté | 5 | 8 | 10 h | 🔲 |
| [US-090](./US-090-tasks.md) | Paliers sur la fiche | 5 | 8 | 10 h | 🔲 |
| [US-091](./US-091-tasks.md) | Paliers au catalogue et au panier | 3 | 5 | 6 h | 🔲 |
| [US-139](./US-139-tasks.md) | Plugins à jour | 2 | 5 | 4 h | 🔲 |
| [US-140](./US-140-tasks.md) | Hôtes de confiance | 1 | 6 | 3,5 h | 🔲 |
| [US-141](./US-141-tasks.md) | Nettoyage des caches | 1 | 5 | 3,5 h | 🔲 |

**Total décomposé** : 72 tâches | **73,5 h** pour 35 points, dont 1,5 h calendaires (T-140-06, T-083-04, T-085-05 : actions de Thibaut), soit **72 h de charge**. Hors capacité : US-052 (3 points), tâches [OPS] de Thibaut T-052-03 à T-052-07 (moniteur, bascule, `X-Real-IP`, MCP, coût Railway), sur GO explicite après le dossier d'US-085.

## Capacité

Périmètre complet voulu par Thibaut (D1) : 35 points, soit ≈ 2,1 h par point (73,5 h pour 35 points) alors que le sprint 14 a tenu 32 points avec trois développeurs en parallèle (66 h) et que le ratio observé est de 2,5 à 3,1 h par point (≈ 30-35 h par développeur et par sprint). Sur 10 jours ouvrés (2027-01-11 au 2027-01-22), la charge de 72 h exige le même parallélisme qu'au sprint 14 (trois développeurs : thème, layout, contenu). Le Scrum Master suit la consommation au point d'étape du 2027-01-15 et applique l'ordre ci-dessous dès qu'il y a retard.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre (D1 : les Should d'abord), en s'arrêtant dès que la charge restante tient dans la capacité. Les Must ne sont pas sacrifiables.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | n/a | 72 h | n/a |
| 1 | US-078 entière | 10 h | 62 h | Pas de meta description ni d'aperçu de lien, LCP mobile non corrigé (Lighthouse 58 en SEO) ; consigné au dossier de GO comme risque résiduel |
| 2 | US-081 entière | 10 h | 52 h | Pas de scan d'image, de SBOM ni d'empreintes figées ; risque résiduel daté au dossier de GO (à accepter par Thibaut) |
| 3 | US-082 entière | 10 h | 42 h | En-têtes Cross-Origin, fichier caché, CSRF du simulateur et correctifs de dépendances non traités : le dossier de GO ne peut pas recommander GO sans acceptation explicite de Thibaut |
| 4 | US-083 (T-083-01 à T-083-03) | 2 h | 40 h | Licence non relue : la démo publique et les contenus MCP restent sous réserve ; NO-GO sur la partie MCP tant que Thibaut n'a pas tranché |
| 5 | US-141 (T-141-01 à T-141-03 et T-141-05) | 3 h | 37 h | Pas de script de nettoyage ; la procédure disque reste manuelle ; T-141-04 (doc de `bin/measure-mobile`) est conservée |

**Non sacrifiables** : toutes les Must (US-085, US-089, US-090, US-091, US-139, US-140) et leurs revues ; T-141-04 (action 2 de la rétro) ; les vérifications de rendu réel (D4). Après le sacrifice n° 5, ≈ 37 h restent : si le point d'étape montre encore un déficit, la décision de couper une Must revient à Thibaut. Toute story sacrifiée est consignée dans le dossier de GO (US-085) comme risque résiduel.

### Sacrifiables en un coup d'œil

⚠️ US-078 (10 h), US-081 (10 h), US-082 (10 h), US-083 (2 h), US-141 (3 h) : 35 h.

## Chemin critique et attentes

- **Plugins d'abord** : T-139-03 (composer) avant T-082-06 (montée de dépendances) : un seul worker à la fois sur `composer.lock`.
- **Prix négocié** : T-090-01 (spike D2) → T-090-03 (données) → T-090-04, T-090-05 → T-091-02, T-091-03 et T-089-04 ; un seul worker sur les gabarits de la fiche, du panier et de l'accueil à la fois ; US-078 (images, en-tête) après T-090-04 sur la fiche.
- **Infra** : US-082 (serveur, en-têtes) puis US-081 (`Dockerfile`, `ci.yaml`) : un worker à la fois sur ces fichiers.
- **Domaine** : T-140-03 → T-140-06 (Thibaut) ; la bascule `CANONICAL_IS_PUBLIC_HOST` est au dossier de GO (D3).
- **Dernière story** : US-085 après toutes les autres (T-085-01 à T-085-03 dès que les rapports existent) ; US-052 seulement sur le GO de Thibaut.
- **Rendu réel final** de toutes les pages touchées avant la revue (T-090-06, T-091-04, T-089-06, T-078-09).

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 100 mots au total, action 3 de la rétro du sprint 14).** Le Scrum Master refuse de clore une tâche sans lui et ne relance pas pour un rapport trop long : un rapport tronqué est un rapport refusé.

```markdown
## Rapport de fin (en premier, ≤ 100 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Rendu réel** : pages vues (URL, FR et EN) ou « sans objet »
- **Nettoyage** : `var/cache-<nom>` du worker supprimé (`bin/clean-test-caches` s'il existe), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

**Règle disque (action 1 de la rétro du sprint 14)** : `df -h .` au démarrage de chaque worker. Si le seuil de `bin/run-tests` (`MIN_FREE_GB`) bloque, le worker nettoie ses propres `var/cache-*` puis demande à l'orchestrateur ; il n'abaisse JAMAIS le seuil, ni par variable d'environnement ni dans le script.

**Règles propres au sprint** : tests via `TEST_DB_NAME=<db> WKHTMLTOPDF_BINARY="$PWD/tests/Fixtures/bin/fake-wkhtmltopdf" bin/run-tests …`, ECS sur `src tests` ; mobile mesuré avec `bin/measure-mobile` (`PUPPETEER_MODULE=$HOME/.npm/_npx/eb7a23b7405e0ca3/node_modules/puppeteer`, `PUPPETEER_EXECUTABLE_PATH=/Applications/Chromium.app/Contents/MacOS/Chromium`) ; `git add` par chemins explicites ; aucun push, aucune PR, aucun merge sans GO ; aucun détail exploitable dans les documents (dépôt public) ; `main` est protégée : PR et contrôles requis, attendre la fin du run CI avant le merge ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets.
