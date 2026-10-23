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
- [ ] Espace libre contrôlé (`bin/check-disk`) avant de lancer le lot suivant ; alerte sous 15 Go, jamais de seuil abaissé.
