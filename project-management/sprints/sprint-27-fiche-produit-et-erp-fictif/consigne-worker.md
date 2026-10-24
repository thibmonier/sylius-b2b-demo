# Consigne de worker : Sprint 027

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md) (règles 1 à 15). L'orchestrateur copie ce bloc dans chaque consigne, remplace les `<…>` et colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Base | Cache |
|--------|---------|-------------|------|-------|
| w189 | US-189 (maquette par l'orchestrateur) | `w189` | `b2b_w189` | `var/cache-w189` |
| w193 | US-193 | `w193` | `b2b_w193` | `var/cache-w193` |
| w188 | US-188 | `w188` | `b2b_w188` | `var/cache-w188` |

Dossier temporaire : `<scratchpad>/<worker>/`. Fichiers à ne pas toucher : ceux d'un autre worker ou de l'orchestrateur (voir la matrice des chevauchements de [tasks/README.md](./tasks/README.md)) ; `DemoResetCommand` et les fixtures : US-193 d'abord, US-188 ensuite.

## Règles du sprint 27

1. **Tests** seulement via `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <arguments PHPUnit>`. Jamais `phpunit` en direct.
2. **Une seule suite complète à la fois.** Arrêt sous 5 Go libres ; **aucune suite complète sous 6 Go** ; `bin/check-disk` avant chaque lot ; mémoire libre et swap relevés.
3. **Jamais de fichier `.env*`** (lecture, création, copie). Un nouveau dépôt de plugin se configure sans `.env*`.
4. **Aucun `rm` refusé contourné** ni aucun refus d'outil contourné par un autre chemin : le noter dans le rapport, avec la commande refusée, et continuer.
5. **Branche partagée `feature/sprint-27-dev`**, déjà créée depuis `main` ; **pas de checkout dans l'arbre partagé**.
6. **Commits limités aux chemins nommés** : `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` ; jamais `git commit -a` ni index partagé ; `git show --stat` avant chaque push. Commits Conventional Commits.
7. **Pas de push, de tag ni de dépôt de plugin sans GO de Thibaut.** Aucune commande Railway.
8. **Aucun identifiant saisi en production** : les vérifications en production sont en lecture seule et sans connexion.
9. **Ton neutre**, aucune comparaison avec Sylius Plus, aucun ERP réel nommé (« ERP Démo (fictif) » seulement), aucun chiffre non vérifiable ; **aucun détail de sécurité dans le dépôt public**.
10. **Contrôle complet avant de rendre la main** : PHPStan sur tout le dépôt (`php -d memory_limit=2G vendor/bin/phpstan analyse`, `src` et `tests`), ECS (`src tests`), Rector, lint Twig et YAML ; aucun compteur écrit en dur (valeur déduite du catalogue ou de la preuve).
11. **Test rouge commité seul**, cité dans la PR ; `php bin/proof-refresh` puis `php bin/proof-check` si la PR ajoute ou supprime un fichier de tests.
12. **Mesures de production consignées le jour du déploiement** dans le fichier de tâches (LCP, 360 et 1280 px, pa11y) ; la story reste en review tant qu'une mesure manque.
13. **US-189 : aucun gabarit Twig avant le GO de Thibaut sur la maquette** (T-189-04) ; polices Fraunces et Inter auto-hébergées, CSP `font-src 'self'`, fiche B2C inchangée.
14. Les autres règles du gabarit (nettoyage de ses propres ressources, attente bornée, lecture sans filtrage, pas de `git` dans un worktree isolé, secrets fictifs, plugin publié) restent applicables.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Un « vert » sans preuve est refusé.
