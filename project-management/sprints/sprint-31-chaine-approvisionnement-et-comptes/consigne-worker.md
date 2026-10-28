# Consigne de worker : Sprint 031

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md) (règles 1 à 25). L'orchestrateur copie ce bloc dans chaque consigne, remplace les `<…>` et colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).
>
> **Règle 1 en tête (action 5 de la rétro du sprint 30) : les tests se lancent seulement par `bin/run-tests`, jamais par `phpunit` en direct.**

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Base | Cache |
|--------|---------|-------------|------|-------|
| w208 | US-208 (chaîne d'approvisionnement PHP) | `w208` | `b2b_w208` | `var/cache-w208` |
| w203 | US-203 (pages de compte des plugins) | `w203` | `b2b_w203` | `var/cache-w203` |
| w204 | US-204 (page « Plugins ») | `w204` | `b2b_w204` | `var/cache-w204` |

Dossier temporaire : `<scratchpad>/<worker>/`. Fichiers à ne pas toucher : ceux d'un autre worker ou de l'orchestrateur, selon la **matrice des fichiers communs** de [tasks/README.md](./tasks/README.md) (règle 21) ; `composer.json`, `composer.lock`, `.github/`, `docker/Dockerfile`, `bin/check-lock-diff`, `docs/durcissement-demo.md` : US-208 seule ; `templates/bundles/` des plugins quote-request, trade-credit et b2b-mcp et `config/packages/demo_theme_b2b_account_plugins.yaml` : US-203 seule ; `templates/shop/plugins/` et `PluginsController` : US-204 seule ; `config/packages/demo_theme_b2b_account.yaml` et `templates/shop/theme_b2b/account/` : personne (US-199 livrée) ; `bin/render-page`, `bin/check-disk`, `bin/clean-test-caches`, `bin/proof-*`, `config/demo/proof.json` : orchestrateur ; `vendor/` de l'arbre partagé : orchestrateur seul ; plugins (`vendor/tmonier/*`) : personne.

## Règles du sprint 31

1. **Tests** seulement via `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <arguments PHPUnit>`. Jamais `phpunit` en direct.
2. **Une seule suite complète à la fois.** Arrêt sous 5 Go libres ; **aucune suite complète sous 6 Go** ; `bin/check-disk` avant chaque lot ; mémoire libre et swap relevés.
3. **Un seul cache de rendu par worker** : `bin/render-page` réutilise le même cache, avec `bin/check-disk` avant chaque rendu ; aucun rendu sous le seuil ; aucun rendu en série sans nettoyage ni relevé de l'espace libre.
4. **Jamais de fichier `.env*`** (lecture, création, copie).
5. **Aucun `rm` refusé contourné** ni aucun refus d'outil contourné par un autre chemin : le noter dans le rapport, avec la commande refusée, et continuer.
6. **Branche partagée `feature/sprint-31-dev`**, créée depuis `main` ; **pas de checkout dans l'arbre partagé**.
7. **Commits limités aux chemins nommés** : `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` ; jamais `git commit -a` ni index partagé ; `git show --stat` avant chaque push. Commits Conventional Commits **avec le bon numéro de story** (US-208, US-203 ou US-204), relu contre la branche et la PR par `git log -1` avant le push.
8. **Pas de push, de tag, de release ni de PR sans la demande de l'orchestrateur** ; aucune commande Railway.
9. **Aucun Twig de la page « Plugins » (US-204) ni du relevé de paiements à terme (US-203, T-203-09 et T-203-10) avant le GO de Thibaut sur la maquette** (T-204-04, T-203-04), noté au [registre des décisions](./registre-decisions.md) ; avant le GO, seuls les travaux sans gabarit (cadrage, état « avant », tests de ce qui existe déjà) sont faits. Les devis, les assistants IA et la page de consentement (US-203) reprennent la maquette validée d'US-199 et ne sont pas concernés ; US-208 n'a pas de maquette.
10. **Aucun plugin modifié** : les pages des plugins sont surchargées dans `templates/bundles/` (US-203) ; `composer.lock` inchangé pour les plugins. Une surcharge **garde tous les `data-test` et variables du plugin**, n'élargit aucun accès et ne change pas la logique de consentement. US-208 : `composer.lock` ne change que pour `sylius/wishlist-plugin`, vérifié dans un répertoire propre avec son `vendor`.
11. **Aucun identifiant saisi en production** : les vérifications en production sont en lecture seule et sans connexion ; les pages connectées sont vérifiées en local (127.0.0.1, comptes des fixtures) et sur l'image reconstruite.
12. **Ton neutre**, aucune comparaison avec Sylius Plus, aucun chiffre non vérifiable ; **aucun détail de sécurité dans le dépôt public**. **US-208 : aucun nom de secret, aucune valeur ni aucun détail d'infrastructure dans les fichiers, les messages de commit ni la PR** ; l'inventaire des secrets de CI est tenu hors du dépôt. Aucune marque réelle dans un nom, une description ou une photo (la liste noire d'US-195 est le seul endroit qui les cite).
13. **Test rouge commité seul**, cité dans la PR ; `bin/proof-rebase` (ou, à défaut, rebase puis `php bin/proof-refresh` et `php bin/proof-check`) si la PR ajoute ou supprime un fichier de tests ; PR de la démo fusionnées en série.
14. **Contrôle complet avant de rendre la main** : PHPStan sur tout le dépôt (`php -d memory_limit=2G vendor/bin/phpstan analyse`, `src` et `tests`), ECS (`src tests`), Rector, lint Twig et YAML ; aucun compteur écrit en dur (valeur déduite du catalogue ou de la preuve).
15. **Mesures de production consignées le jour du déploiement** dans le fichier de tâches pour ce qui est public (page « Plugins » : pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px) ; les pages connectées sont vérifiées en local et sur l'image reconstruite.
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

21. **Fichiers communs déclarés et attribués** (action 3 de la rétro du sprint 29, incident : un commit avait embarqué les lignes d'un autre worker dans `demo_theme_b2b.yaml`) : un fichier de hooks, un partial SCSS et un domaine de traduction **propres à chaque story** ; pour un fichier commun (une ligne d'import dans `b2b.scss`, une section de `docs/theme-b2b.md`), `git diff -- <fichier>` relu avant le commit : il ne contient que ses lignes, sinon le worker attend et prévient l'orchestrateur. Matrice : [tasks/README.md](./tasks/README.md).
22. **Aucun test depuis un worktree dont `vendor` est un lien symbolique** (action 5 de la rétro du sprint 29) : l'autoload de Composer résout le lien et exécute le code de l'arbre principal, pas celui du worktree. Les tests se lancent dans l'arbre partagé (ou dans un worktree avec son propre `vendor`).
23. **Captures réelles, relues à 1280 px avant la PR** (actions 2 et 4 de la rétro du sprint 29) : captures par `bin/render-page` avec les vignettes `b2b_photo*` (aucune capture simulée ni vignette vide) ; l'orchestrateur relit à 1280 px la home connectée et chaque page touchée avant d'ouvrir la PR ; un bloc à nombre fixe d'éléments a un test qui les compte.
24. **Parallélisme réglé sur la machine** (action 1 de la rétro du sprint 30) : avant de lancer les workers, l'orchestrateur relève `df -h /`, la mémoire libre et le swap ; **au-delà de 2 Go de swap ou sous 7 Go de disque, au plus deux workers lancent des tests en même temps**, les autres préparent du code. Le seuil se mesure avec `df`, jamais avec le Finder. Un worker qui n'a pas la main sur les tests le dit dans son rapport et prépare la tâche suivante.
25. **Échanges avec l'orchestrateur** (action 5 de la rétro du sprint 30) : chaque réponse de l'orchestrateur à un worker cite l'**identifiant de la décision** (commit du registre) ; le worker ignore sans relance une notification d'attente antérieure à cette réponse. **`vendor/` de l'arbre partagé** : aucun worker n'y lance `composer install`, `composer update` ni `composer require` (le passage du wishlist se vérifie dans un répertoire propre).

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Un « vert » sans preuve est refusé.
