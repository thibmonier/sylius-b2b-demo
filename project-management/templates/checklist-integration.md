# Checklist d'intégration et gabarit de rapport de fin

> US-161 (actions 3 et 4 de la rétrospective du sprint 17, T-161-06 et T-161-09). L'orchestrateur applique cette checklist à chaque rapport de worker avant de déclarer « vert ». Le gabarit de consigne est [consigne-worker.md](./consigne-worker.md).

## Gabarit de rapport de fin (≤ 100 mots, à coller dans la consigne)

- **Tâches** : T-XXX-YY
- **Worker** : nom (sous-dossier de scratchpad, base `b2b_<worker>`, cache `var/cache-<worker>`)
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Preuves rejouables** : voir le tableau ci-dessous
- **Rendu réel** : pages vues (URL, FR et EN) ou « sans objet »
- **Nettoyage** : cache et base du worker supprimés (`WORKER_ID=<worker> bin/clean-test-caches`), espace libre en Go
- **Commit** : fait (hash) | non fait
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »

### Preuves rejouables

| Contrôle | Commande exacte | Dernière ligne de sortie attendue |
|----------|-----------------|-----------------------------------|
| PHPStan | `php -d memory_limit=2G vendor/bin/phpstan analyse` | `[OK] No errors` |
| ECS | `php -d memory_limit=1G vendor/bin/ecs check` (`src tests`) | `[OK] No errors found.` |
| Rector | `vendor/bin/rector process --dry-run` | `[OK] Rector is done!` |
| Lint Twig / YAML | `php bin/console lint:twig templates` / `lint:yaml config translations` | `[OK] All ... files contain valid syntax.` |
| Suite | `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <chemin>` | `OK (N tests, M assertions)` |

Un contrôle « non exécuté » donne la raison (disque, durée, hors périmètre).

## Checklist de l'orchestrateur

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; **un rapport sans preuve est refusé** tant que la preuve rejouable n'est pas fournie.
- [ ] PHPStan et ECS (`src tests`) **rejoués par l'orchestrateur** sur l'arbre intégré (pas seulement dans le worktree du worker).
- [ ] Une suite annoncée en échec est **rejouée seule** (`--filter` ou le fichier) avant toute enquête : échec partagé = mettre la branche à jour puis relancer ce test.
- [ ] Les noms des jobs requis par la protection de `main` (« Quality and smoke tests », « Docker image ») sont inchangés si le workflow a été touché.
- [ ] Une tâche de fond (run, attente) est **arrêtée explicitement (`TaskStop`)**, l'arrêt **vérifié** (verrou `var/run-tests.lock` libre, processus absent), puis seulement relancée.
- [ ] **Image reconstruite après correctif** : si un correctif touche `Dockerfile`, `docker/`, `config/`, les assets ou les dépendances, l'orchestrateur (seul) reconstruit l'image, la démarre et valide les pages clés avant la clôture de la revue ; un « vert » sur le code seul ne suffit pas.
- [ ] **Preuve publiée** (action 3 de la rétro du sprint 23) : si la PR ajoute ou supprime un fichier de tests, `php bin/proof-refresh` puis `php bin/proof-check` avant le push ; sinon le build de `main` échoue à l'étape « Published proof is up to date ».
- [ ] **Bases de test nettoyées après chaque lot** (action 1 de la rétro du sprint 24) : aucun run actif, `bin/clean-test-caches --all`, puis `df -h .` ; sous 6 Go libres, prévenir Thibaut avant le lot suivant.
- [ ] **Texte public relu contre sa source** (action 3 de la rétro du sprint 24) : un README, une note de release ou une page relus par l'orchestrateur contre le fichier, la page ou le registre qu'ils décrivent, avant le commit.
- [ ] **Budget disque d'un dépôt hors de la démo et restes listés** (action 1 de la rétro du sprint 25) : au planning, toute story qui crée ou installe un dépôt hors de la démo porte une ligne de budget disque (`vendor/`, base, essais) ; à la fin de chaque lot, l'orchestrateur relève `df -h .` et envoie à Thibaut la liste des restes qu'un refus d'outil a empêché de supprimer, avec la commande à lancer ; sous 6 Go libres, aucun nouveau lot avant ce nettoyage.
- [ ] **Concordance des versions avant publication** (action 3 de la rétro du sprint 25) : avant un tag, une release ou une PR publique, comparer la version visée (dossier de recette, tag, contrainte `composer.json`) aux versions publiées sur Packagist.
- [ ] Espace libre contrôlé (`bin/check-disk`) avant de lancer le lot suivant ; alerte sous 15 Go, jamais de seuil abaissé.
