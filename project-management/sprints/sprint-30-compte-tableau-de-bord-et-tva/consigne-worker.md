# Consigne de worker : Sprint 030

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md) (règles 1 à 23). L'orchestrateur copie ce bloc dans chaque consigne, remplace les `<…>` et colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Base | Cache |
|--------|---------|-------------|------|-------|
| w199 | US-199 (« Mon compte ») | `w199` | `b2b_w199` | `var/cache-w199` |
| w201 | US-201 (tableau de bord des achats, surcharge dans la démo) | `w201` | `b2b_w201` | `var/cache-w201` |
| w202 | US-202 (catégories de taxe, TVA au panier) | `w202` | `b2b_w202` | `var/cache-w202` |

Dossier temporaire : `<scratchpad>/<worker>/`. Fichiers à ne pas toucher : ceux d'un autre worker ou de l'orchestrateur, selon la **matrice des fichiers communs** de [tasks/README.md](./tasks/README.md) (règle 21) ; `templates/shop/theme_b2b/account/` : US-199 seule ; `templates/shop/theme_b2b/insights/` et `templates/bundles/` du plugin company-insights : US-201 seule ; `templates/shop/theme_b2b/cart/`, fixtures, import ERP simulé et décoration d'approbation : US-202 seule ; `config/packages/demo_theme_b2b.yaml` : personne ce sprint (un fichier de hooks propre par story) ; `bin/render-page`, `bin/clean-test-caches`, `bin/proof-*`, `config/demo/proof.json` : orchestrateur ; plugins (`vendor/tmonier/*`) : personne.

## Règles du sprint 30

1. **Tests** seulement via `WORKER_ID=<worker> TEST_DB_NAME=b2b_<worker> TEST_CACHE_DIR=var/cache-<worker> bin/run-tests <arguments PHPUnit>`. Jamais `phpunit` en direct.
2. **Une seule suite complète à la fois.** Arrêt sous 5 Go libres ; **aucune suite complète sous 6 Go** ; `bin/check-disk` avant chaque lot ; mémoire libre et swap relevés.
3. **Un seul cache de rendu par worker** : `bin/render-page` réutilise le même cache, avec `bin/check-disk` avant chaque rendu ; aucun rendu sous le seuil ; aucun rendu en série sans nettoyage ni relevé de l'espace libre.
4. **Jamais de fichier `.env*`** (lecture, création, copie).
5. **Aucun `rm` refusé contourné** ni aucun refus d'outil contourné par un autre chemin : le noter dans le rapport, avec la commande refusée, et continuer.
6. **Branche partagée `feature/sprint-30-dev`**, créée depuis `main` ; **pas de checkout dans l'arbre partagé**.
7. **Commits limités aux chemins nommés** : `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` ; jamais `git commit -a` ni index partagé ; `git show --stat` avant chaque push. Commits Conventional Commits **avec le bon numéro de story** (US-199, US-201 ou US-202), relu contre la branche et la PR par `git log -1` avant le push.
8. **Pas de push, de tag, de release ni de PR sans la demande de l'orchestrateur** ; aucune commande Railway.
9. **Aucun Twig du compte (US-199) ni du tableau de bord (US-201) avant le GO de Thibaut sur la maquette** (T-199-04, T-201-04), noté au [registre des décisions](./registre-decisions.md) ; avant le GO, seuls les travaux sans gabarit (cadrage, état « avant », tests de ce qui existe déjà) sont faits. US-202 n'a pas de maquette.
10. **Aucun plugin modifié** (décision de Thibaut pour US-201) : entrées et gabarits des plugins repris par leurs hooks ou surchargés dans `templates/bundles/` ; `composer.lock` inchangé pour les plugins. US-202 : la limite d'approbation est comparée au HT par une décoration **dans la démo** ; si ce n'est pas possible proprement, arrêt et question à Thibaut avant tout code.
11. **Aucun identifiant saisi en production** : les vérifications en production sont en lecture seule et sans connexion ; les pages connectées sont vérifiées en local (127.0.0.1, comptes des fixtures) et sur l'image reconstruite.
12. **Ton neutre**, aucune comparaison avec Sylius Plus, aucun chiffre non vérifiable ; **aucun détail de sécurité dans le dépôt public**. Aucune marque réelle dans un nom, une description ou une photo (la liste noire d'US-195 est le seul endroit qui les cite). 
13. **Test rouge commité seul**, cité dans la PR ; `bin/proof-rebase` (ou, à défaut, rebase puis `php bin/proof-refresh` et `php bin/proof-check`) si la PR ajoute ou supprime un fichier de tests ; PR de la démo fusionnées en série.
14. **Contrôle complet avant de rendre la main** : PHPStan sur tout le dépôt (`php -d memory_limit=2G vendor/bin/phpstan analyse`, `src` et `tests`), ECS (`src tests`), Rector, lint Twig et YAML ; aucun compteur écrit en dur (valeur déduite du catalogue ou de la preuve).
15. **Mesures de production consignées le jour du déploiement** dans le fichier de tâches pour ce qui est public (pa11y WCAG2AAA, 360 et 1280 px) ; les pages connectées sont vérifiées en local et sur l'image reconstruite. Les prix et taxes de la base ne changent qu'à la remise à zéro de 03:00 : le contrôle de production d'US-202 se fait le lendemain du déploiement, et la tâche est dite ouverte à l'avance.
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

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Un « vert » sans preuve est refusé.
