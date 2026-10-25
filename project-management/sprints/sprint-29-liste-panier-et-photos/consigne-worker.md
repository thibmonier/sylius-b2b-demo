# Consigne de worker : Sprint 029

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md) (règles 1 à 20). L'orchestrateur copie ce bloc dans chaque consigne, remplace les `<…>` et colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Base | Cache |
|--------|---------|-------------|------|-------|
| w198 | US-198 (liste des produits) | `w198` | `b2b_w198` | `var/cache-w198` |
| w200 | US-200 (panier) | `w200` | `b2b_w200` | `var/cache-w200` |
| w197 | US-197 (photos : point focal, puis variantes de couleur) | `w197` | `b2b_w197` | `var/cache-w197` |

Dossier temporaire : `<scratchpad>/<worker>/`. Fichiers à ne pas toucher : ceux d'un autre worker ou de l'orchestrateur (voir la matrice des chevauchements de [tasks/README.md](./tasks/README.md)) ; `templates/shop/theme_b2b/product/` : US-198 seule ; `templates/shop/theme_b2b/cart/` : US-200 seule ; `fixtures/images/`, `src/Demo/Images/` et `ProductImageAttacher` : US-197 seule ; `bin/render-page`, `bin/clean-test-caches`, `bin/proof-*` : orchestrateur. `assets/shop/styles/b2b.scss` et `config/packages/demo_theme_b2b.yaml` : une section par story, modification par `Edit` ciblé relu juste avant, jamais de reformatage du fichier.

## Règles du sprint 29

1. **Tests** seulement via `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <arguments PHPUnit>`. Jamais `phpunit` en direct.
2. **Une seule suite complète à la fois.** Arrêt sous 5 Go libres ; **aucune suite complète sous 6 Go** ; `bin/check-disk` avant chaque lot ; mémoire libre et swap relevés.
3. **Un seul cache de rendu par worker** : `bin/render-page` réutilise le même cache, avec `bin/check-disk` avant chaque rendu ; aucun rendu sous le seuil ; aucun rendu en série sans nettoyage ni relevé de l'espace libre.
4. **Jamais de fichier `.env*`** (lecture, création, copie).
5. **Aucun `rm` refusé contourné** ni aucun refus d'outil contourné par un autre chemin : le noter dans le rapport, avec la commande refusée, et continuer.
6. **Branche partagée `feature/sprint-29-dev`**, créée depuis `main` ; **pas de checkout dans l'arbre partagé**.
7. **Commits limités aux chemins nommés** : `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` ; jamais `git commit -a` ni index partagé ; `git show --stat` avant chaque push. Commits Conventional Commits **avec le bon numéro de story** (US-197, US-198 ou US-200), relu contre la branche et la PR par `git log -1` avant le push.
8. **Pas de push, de tag, de release ni de PR sans la demande de l'orchestrateur** ; aucune commande Railway.
9. **Aucun Twig de la liste (US-198) ni du panier (US-200) avant le GO de Thibaut sur la maquette** (T-198-04, T-200-04), noté au [registre des décisions](./registre-decisions.md) ; avant le GO, seuls les travaux sans gabarit (cadrage, mesures, maquette, tests de ce qui existe déjà) sont faits.
10. **Aucun téléchargement de photo sans le GO explicite de Thibaut** (T-197-05). Avant le GO : recherche, licences relues, planche locale de liens et d'aperçus, **rien d'enregistré dans le dépôt**. **Aucune retouche** des photos.
11. **Aucun identifiant saisi en production** : les vérifications en production sont en lecture seule et sans connexion ; les pages connectées sont vérifiées en local (127.0.0.1, comptes des fixtures) et sur l'image reconstruite.
12. **Ton neutre**, aucune comparaison avec Sylius Plus, aucun chiffre non vérifiable ; **aucun détail de sécurité dans le dépôt public**. Aucune marque réelle dans un nom, une description ou une photo (la liste noire d'US-195 est le seul endroit qui les cite). Plugins non modifiés : leurs blocs sont repris par leurs hooks existants.
13. **Test rouge commité seul**, cité dans la PR ; `bin/proof-rebase` (ou, à défaut, rebase puis `php bin/proof-refresh` et `php bin/proof-check`) si la PR ajoute ou supprime un fichier de tests ; PR de la démo fusionnées en série.
14. **Contrôle complet avant de rendre la main** : PHPStan sur tout le dépôt (`php -d memory_limit=2G vendor/bin/phpstan analyse`, `src` et `tests`), ECS (`src tests`), Rector, lint Twig et YAML ; aucun compteur écrit en dur (valeur déduite du catalogue ou de la preuve).
15. **Mesures de production consignées le jour du déploiement** dans le fichier de tâches (LCP, 360 et 1280 px, pa11y WCAG2AAA) ; la story reste en review tant qu'une mesure manque. Les photos de la base ne changent qu'à la remise à zéro de 03:00 : le contrôle d'US-197 se fait le lendemain du déploiement, et la tâche est dite ouverte à l'avance.
16. **« Que devient cet état après un déploiement ? »** : tout fichier, répertoire ou cache écrit hors base est décrit (conteneur ou volume, ce qui le recrée au démarrage) et vérifié dans un conteneur neuf avant le merge. Le conteneur est neuf à chaque déploiement, la base survit. Aucun état hors base attendu ce sprint : à confirmer au cadrage de chaque story.
17. Les autres règles du gabarit (nettoyage de ses propres ressources, attente bornée, lecture sans filtrage, pas de `git` dans un worktree isolé, secrets fictifs) restent applicables.
18. **Aucune commande récursive ou destructive sur un chemin non vérifié** (action 1 de la rétro du sprint 28, incident du 2026-10-25 : `cp -Rn "$D"/. …` avec `$D` vide avait copié la racine du Mac). Jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans :
    ```sh
    set -u
    D="…"
    [ -n "$D" ] && [ -d "$D" ] || { echo "chemin vide ou absent" >&2; exit 1; }
    case "$D" in "$PWD"/*) ;; *) echo "hors du dossier attendu" >&2; exit 1 ;; esac
    ```
    Le chemin est aussi vérifié sous le dossier attendu ; un glob sans résultat est traité comme vide. L'orchestrateur n'approuve aucune commande sans cette garde. Les commandes de nettoyage destinées à Thibaut sont en **chemins absolus**, précédées d'un `du -sh`.
19. **Rejouer la séquence exacte de la CI avant de rendre la main** (action 2 de la rétro du sprint 28) : pour un dépôt qui a une CI, la préparation de la CI (installation, fixtures, base) est rejouée telle quelle en local avant le push ; la dernière ligne de sortie est citée. Aucun plugin n'est modifié ce sprint, la règle vaut pour la CI de la démo (« Quality and smoke tests », « Docker image »).
20. **Aucune fusion avec une question ouverte** (action 4 de la rétro du sprint 28) : avant tout enchaînement automatique de fusion, vérifier qu'aucune question à Thibaut n'est en suspens sur la PR (commentaire ou description) ; sinon la PR reste ouverte. **Toute décision ou tout GO est noté au registre avec son auteur** ; une phrase « accepté par Thibaut » n'est écrite que si elle y figure.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Un « vert » sans preuve est refusé.
