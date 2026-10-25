# Gabarit de consigne de worker

> US-161 (actions 2, 3 et 4 de la rétrospective du sprint 17). L'orchestrateur copie ce gabarit dans chaque consigne, remplace les `<…>` et colle **tel quel** le bloc « Rapport de fin » de [../sprints/sprint-18-repositionnement/tasks/README.md](../sprints/sprint-18-repositionnement/tasks/README.md) à la fin. Détails des outils : [docs/tests-locaux.md](../../docs/tests-locaux.md).

## Identité et ressources du worker

| Ressource | Valeur | Règle |
|-----------|--------|-------|
| Nom / `WORKER_ID` | `<worker>` | lettres et chiffres, **sans `_`** ; unique dans le sprint |
| Dossier temporaire | `<scratchpad>/<worker>/` | un sous-dossier par worker ; jamais de fichier de travail à la racine du scratchpad |
| Base de test | `TEST_DB_NAME=b2b_<worker>` | une base par worker |
| Cache de test | `TEST_CACHE_DIR=var/cache-<worker>` | un cache par worker |
| Fichiers à ne pas toucher | `<liste>` | ceux d'un autre worker ou de l'orchestrateur ; modification seulement par `Edit` ciblé, relu juste avant |

## Règles de lancement

1. **Disque** : `df -h .` au démarrage et **avant chaque lot** (`bin/check-disk` le fait aussi à chaque `bin/run-tests`). Sous 15 Go : prévenir l'orchestrateur sans attendre. Le seuil `MIN_FREE_GB` (5 Go) n'est jamais abaissé ; **aucune suite complète sous 6 Go** (action 3 de la rétro du sprint 26).
2. **Tests** : `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <arguments PHPUnit>`. Jamais `phpunit` en direct. Un second run est refusé par le verrou (code 4).
3. **Nettoyage** : « nettoyer avant chaque lot » vise **ses propres** ressources : `WORKER_ID=<worker> bin/clean-test-caches`. `bin/clean-test-caches --all` est **réservé à l'orchestrateur**, hors lots ; le script le refuse à un worker.
4. **Attente bornée** : pour attendre la fin d'un run, `bin/run-tests --wait` (lit le verrou, jamais `pgrep`) ; il sort au bout de **30 minutes** (code 5) : le worker termine alors avec un rapport « partiel » qui dit sur quoi il attendait.
5. **Arrêt d'une tâche de fond** : l'orchestrateur l'arrête explicitement (`TaskStop`), **vérifie** l'arrêt (verrou libre, processus absent), puis seulement relance.

6. **Plugin publié** : lire la matrice CI du dépôt avant d'écrire du code (le code tourne sur la version de PHP la plus basse de la matrice) et respecter l'ordre de publication de la fiche ; ni tag ni merge par le worker.
7. **Secrets fictifs** : aucun jeton ou clé au format réel dans les tests ; les recomposer à l'exécution (concaténation) pour ne pas déclencher les scanners.
8. **Refus d'outil** : ne jamais contourner un refus par un autre chemin (autre commande, autre binaire, autre outil). Le noter dans le rapport, avec la commande refusée, et continuer le reste du travail.
9. **Lecture sans filtrage** (action 1 de la rétro du sprint 23) : un fichier à modifier se lit avec l'outil Read ou `rtk proxy cat`, jamais avec `cat`, `sed` ou `head` réécrits par RTK, qui masquent commentaires et docblocks. Le diff d'un refactoring est relu pour les commentaires supprimés.
10. **Pas de `git` dans un worktree isolé** (action 2 de la rétro du sprint 24) : une tâche qui change de branche, commite ou pousse se fait dans l'arbre principal, séquencée par l'orchestrateur ; un worker lancé avec un worktree isolé ne reçoit que du travail sans `git`.
11. **Mêmes règles pour l'orchestrateur** (action 4 de la rétro du sprint 24) : quand l'orchestrateur code, tests uniquement par `bin/run-tests` et test rouge commité seul avant le code.
12. **Nouveau plugin sans `.env` et sans second serveur** (action 4 de la rétro du sprint 25) : un nouveau dépôt de plugin se configure sans fichier `.env*` (variables dans `phpunit.xml.dist` et dans l'environnement de la CI) et utilise le MySQL de la démo, avec une base à son nom supprimée à la fin ; jamais de second serveur de base de données. Le budget disque du dépôt (`vendor/`, base) est écrit au planning et relevé à la fin du lot.
13. **Commits limités aux chemins nommés** (action 1 de la rétro du sprint 26) : `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` ; jamais `git commit -a`, jamais un commit depuis un index partagé, jamais de configuration non commitée laissée dans l'arbre commun. `git show --stat` avant chaque push : seuls les fichiers du worker y figurent. **Le numéro de story du message** est relu contre la branche et le titre de la PR (`git show --stat` puis `git log -1`) avant le push ; un correctif hors story cite la PR ou la story d'origine (action 4 de la rétro du sprint 27).
14. **Contrôle complet avant de rendre la main** (action 2 de la rétro du sprint 26) : PHPStan sur **tout le dépôt**, `src` et `tests` compris (`php -d memory_limit=2G vendor/bin/phpstan analyse`), en plus des tests ciblés. Aucun compteur écrit en dur (plugins, fichiers de tests, lignes de catalogue) : la valeur est déduite du catalogue (`config/demo/plugins.yaml`) ou de la preuve (`config/demo/proof.json`).
15. **Mesures de production consignées le jour du déploiement** (action 5 de la rétro du sprint 26) : LCP « après », largeur à 360 et 1280 px et pa11y sont notés dans le fichier de tâches de la story le jour où `main` est déployé, pas après coup.
16. **Que devient cet état après un déploiement ?** (action 3 de la rétro du sprint 27) : pour tout fichier, répertoire, cache ou état écrit hors base, la fiche dit où il vit (conteneur ou volume), ce qui le recrée au démarrage, et la vérification se fait dans un conteneur neuf (image reconstruite) avant le merge. Le conteneur est neuf à chaque déploiement, la base survit.
17. **PR fusionnées en série et `proof.json`** (action 2 de la rétro du sprint 27) : une seule PR de la démo est fusionnée à la fois ; après chaque rebase sur `origin/main`, `bin/proof-rebase` (T-TECH-28-02 ; en attendant, rebase puis `php bin/proof-refresh` et `php bin/proof-check`) régénère `config/demo/proof.json` ; jamais de résolution à la main.

## Preuves attendues dans le rapport (action 3)

Pour chacun de PHPStan, ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Un « vert » sans preuve est refusé.

Le rapport de fin, la checklist d'intégration de l'orchestrateur (rejeu de PHPStan et ECS, suite en échec rejouée seule, image reconstruite après correctif) : [checklist-integration.md](./checklist-integration.md).
