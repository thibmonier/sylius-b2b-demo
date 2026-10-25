# Consigne de worker : Sprint 028

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md) (règles 1 à 17). L'orchestrateur copie ce bloc dans chaque consigne, remplace les `<…>` et colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Base | Cache |
|--------|---------|-------------|------|-------|
| w195 | US-195 | `w195` | `b2b_w195` | `var/cache-w195` |
| w196 | US-196 | `w196` | `b2b_w196` | `var/cache-w196` |
| w187 | US-187 (tranche A : dépôt du plugin ; tranche B : démo) | `w187` | `b2b_w187` | `var/cache-w187` |

Dossier temporaire : `<scratchpad>/<worker>/`. Fichiers à ne pas toucher : ceux d'un autre worker ou de l'orchestrateur (voir la matrice des chevauchements de [tasks/README.md](./tasks/README.md)) ; `fixtures/csv/` : US-195 seule ; `DemoResetCommand` et `src/Demo/Erp/` : US-187 seule.

## Règles du sprint 28

1. **Tests** seulement via `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <arguments PHPUnit>`. Jamais `phpunit` en direct.
2. **Une seule suite complète à la fois.** Arrêt sous 5 Go libres ; **aucune suite complète sous 6 Go** ; `bin/check-disk` avant chaque lot ; mémoire libre et swap relevés.
3. **Un seul cache de rendu par worker** : `bin/render-page` réutilise le même cache, avec `bin/check-disk` avant chaque rendu ; aucun rendu sous le seuil. Aucun rendu en série sans nettoyage ni relevé de l'espace libre.
4. **Jamais de fichier `.env*`** (lecture, création, copie). Le dépôt du plugin se configure sans `.env*` (variables dans `phpunit.xml.dist` et dans l'environnement de la CI).
5. **Aucun `rm` refusé contourné** ni aucun refus d'outil contourné par un autre chemin : le noter dans le rapport, avec la commande refusée, et continuer.
6. **Branche partagée `feature/sprint-28-dev`**, déjà créée depuis `main` ; **pas de checkout dans l'arbre partagé**. Le dépôt du plugin a sa propre branche (nom donné par l'orchestrateur).
7. **Commits limités aux chemins nommés** : `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` ; jamais `git commit -a` ni index partagé ; `git show --stat` avant chaque push. Commits Conventional Commits **avec le bon numéro de story** (US-195, US-196 ou US-187), relu contre la branche et la PR par `git log -1` avant le push.
8. **Pas de push, de tag, de release ni de dépôt de plugin sans GO de Thibaut.** Le GO pour US-187 est la tâche T-187-10 ; avant elle, le plugin reste local. Thibaut soumet lui-même à Packagist. Aucune commande Railway.
9. **Aucun téléchargement de photo sans le GO explicite de Thibaut** (T-196-04). Avant le GO : recherche, licences relues, planche locale de liens et d'aperçus, **rien d'enregistré dans le dépôt**.
10. **Aucun identifiant saisi en production** : les vérifications en production sont en lecture seule et sans connexion.
11. **Ton neutre**, aucune comparaison avec Sylius Plus, aucun ERP réel nommé (« ERP Démo (fictif) » seulement), aucun chiffre non vérifiable ; **aucun détail de sécurité dans le dépôt public**. Aucune marque réelle dans un nom, une description ou une photo (la liste noire d'US-195 est le seul endroit qui les cite).
12. **Contrôle complet avant de rendre la main** : PHPStan sur tout le dépôt (`php -d memory_limit=2G vendor/bin/phpstan analyse`, `src` et `tests`), ECS (`src tests`), Rector, lint Twig et YAML ; aucun compteur écrit en dur (valeur déduite du catalogue ou de la preuve). Plugin : ECS, PHPStan max, Rector et tests sur la matrice CI du dépôt.
13. **Test rouge commité seul**, cité dans la PR (plugin compris) ; `php bin/proof-refresh` puis `php bin/proof-check` (ou `bin/proof-rebase`, T-TECH-28-02) si la PR ajoute ou supprime un fichier de tests ; PR de la démo fusionnées en série.
14. **Mesures de production consignées le jour du déploiement** dans le fichier de tâches (LCP, 360 et 1280 px, pa11y) ; la story reste en review tant qu'une mesure manque.
15. **« Que devient cet état après un déploiement ? »** : tout fichier, répertoire ou cache écrit hors base (répertoire d'échange, journal des lots, images) est décrit (conteneur ou volume, ce qui le recrée au démarrage) et vérifié dans un conteneur neuf avant le merge (US-187, T-187-16 et T-187-19).
16. **Plugin publié** : lire la matrice CI du dépôt avant d'écrire du code (le code tourne sur la version de PHP la plus basse de la matrice) ; vérifier la concordance de la version avec Packagist avant un tag ; la démo ne commite `composer.json` et le verrou qu'une fois le tag visible sur Packagist.
17. Les autres règles du gabarit (nettoyage de ses propres ressources, attente bornée, lecture sans filtrage, pas de `git` dans un worktree isolé, secrets fictifs) restent applicables.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Un « vert » sans preuve est refusé.
