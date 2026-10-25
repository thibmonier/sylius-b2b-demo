# Checklist d'intégration : Sprint 030

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale, à jour des actions des rétros des sprints 27 à 29). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ».

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-30-07) ; **sous 6 Go, aucune suite complète** (8 Go à la clôture du sprint 29)
- [ ] **T-TECH-30-01** (un seul cache de rendu par worker, seuil disque appliqué aux rendus) et **T-TECH-30-03** (vignettes `b2b_photo*` dans `bin/render-page`) faits **avant la première capture de maquette**
- [ ] **T-TECH-30-02** (`bin/proof-rebase`) fait **avant la première PR**
- [ ] **T-TECH-30-04** (`ErpDemoExchangeFixtureTest` isolé) fait avant la première suite complète
- [ ] Branche `feature/sprint-30-dev` créée depuis `main` et à jour ; aucune configuration non commitée dans l'arbre partagé
- [ ] Prix de référence relevés (liste, fiche, panier de Claire) avant le premier code d'US-202 (T-202-01)

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] **Tests lancés dans l'arbre partagé**, jamais depuis un worktree au `vendor` en lien symbolique (règle 22)
- [ ] Aucun compteur écrit en dur : valeur déduite du catalogue ou de la preuve
- [ ] `git show --stat` et `git log -1` de chaque commit : seuls les fichiers de la tâche, **bon numéro de story**, aucun `git commit -a` ; **fichiers communs conformes à la matrice** (règle 21 : aucun commit embarquant les lignes d'un autre worker)
- [ ] **Aucune commande `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans la garde de la règle 18** ; commandes de nettoyage destinées à Thibaut en chemins absolus, précédées d'un `du -sh`
- [ ] **Aucun plugin modifié** (`git diff --stat -- vendor composer.lock` vide pour les plugins)
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Commit de tests rouges seul, cité dans la PR
- [ ] Preuve publiée par `bin/proof-rebase` si des fichiers de tests changent ; PR fusionnées en série
- [ ] **Séquence exacte de la CI rejouée en local** (règle 19) avant le push ; CI verte, run terminé, avant le merge
- [ ] Tâche cochée dans le commit qui la livre

## Portes de Thibaut

- [ ] Maquette « Mon compte » présentée ; **GO noté au registre avec son auteur avant T-199-05** ; aucun gabarit Twig du compte avant
- [ ] Maquette du tableau de bord présentée ; **GO noté au registre avant T-201-05** ; aucun gabarit Twig du tableau de bord avant
- [ ] US-202 : si la limite HT ne peut pas être obtenue par décoration dans la démo, **question à Thibaut notée au registre avant tout code** (T-202-05)
- [ ] **Aucune PR fusionnée avec une question à Thibaut ouverte** (commentaire ou description relu avant l'enchaînement automatique)
- [ ] Toute phrase « accepté par Thibaut » retrouvée dans le [registre des décisions](./registre-decisions.md)

## Avant la PR

- [ ] **Capture à 1280 px relue par l'orchestrateur** (règle 23, T-TECH-30-09) : home connectée, compte des trois rôles, tableau de bord, panier ; aucune vignette vide, aucune capture simulée
- [ ] Prix HT de la liste, de la fiche et du panier comparés avant/après (US-202)

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public ; US-202 : après la remise à zéro de 03:00 suivante, dit à l'avance
- [ ] Pages connectées vérifiées en local et sur l'image reconstruite (aucune connexion en production) ; B2C inchangé
- [ ] **Que devient cet état après un déploiement ?** Aucun état hors base attendu ; sinon décrit et vérifié dans un conteneur neuf avant le merge
- [ ] Texte public relu contre sa source (maquette validée) ; parité FR et EN ; aucune comparaison avec Sylius Plus
