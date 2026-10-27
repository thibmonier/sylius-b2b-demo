# Checklist d'intégration : Sprint 031

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale, à jour des actions des rétros des sprints 27 à 30). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ».

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-31-01, T-TECH-31-07) ; **au-delà de 2 Go de swap ou sous 7 Go de disque, au plus 2 workers lancent des tests** (règle 24) ; **sous 6 Go, aucune suite complète**
- [ ] **T-TECH-31-04** (vignettes pour URL relatives et absolues, `memory_limit=1G`) fait **avant la première capture ou maquette**
- [ ] **T-TECH-31-01** fait avant le lancement des workers
- [ ] Branche `feature/sprint-31-dev` créée depuis `main` et à jour ; aucune configuration non commitée dans l'arbre partagé
- [ ] Contrôle « si vous avez été touché » (T-208-01) fait au début d'US-208 et son résultat noté sans détail sensible
- [ ] État « avant » relevé avant tout code : quatre tests de la page « Plugins » verts (T-204-02), tests du compte verts et captures des pages des plugins (T-203-02)

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] **Tests lancés dans l'arbre partagé**, jamais depuis un worktree au `vendor` en lien symbolique (règle 22) ; **`vendor/` de l'arbre partagé non modifié par un worker** (règle 25)
- [ ] Aucun compteur écrit en dur : valeur déduite du catalogue ou de la preuve
- [ ] `git show --stat` et `git log -1` de chaque commit : seuls les fichiers de la tâche, **bon numéro de story**, aucun `git commit -a` ; **fichiers communs conformes à la matrice** (règle 21)
- [ ] **Aucune commande `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans la garde de la règle 18** ; commandes de nettoyage destinées à Thibaut en chemins absolus, précédées d'un `du -sh`
- [ ] **Aucun plugin modifié** (`git diff --stat -- vendor composer.lock` : seule la ligne du wishlist pour US-208)
- [ ] **Aucun nom de secret, aucune valeur ni détail d'infrastructure** dans les fichiers, les messages de commit et la PR (US-208, règle 12)
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Commit de tests rouges seul, cité dans la PR
- [ ] Preuve publiée par `bin/proof-rebase` si des fichiers de tests changent ; PR fusionnées en série
- [ ] **Séquence exacte de la CI rejouée en local** (règle 19) avant le push ; CI verte, run terminé, avant le merge
- [ ] Réponse de l'orchestrateur au worker : **identifiant de la décision cité** (règle 25)
- [ ] Tâche cochée dans le commit qui la livre

## Portes de Thibaut

- [ ] Planche du relevé présentée ; **GO noté au registre avec son auteur avant T-203-09** ; aucun Twig du relevé avant
- [ ] Maquette « Plugins » présentée ; **GO noté au registre avant T-204-05** ; aucun Twig de la page avant
- [ ] Job zizmor vert sur la PR, puis ajouté aux contrôles requis de `main` par Thibaut (T-208-11), noté au registre
- [ ] **Aucune PR fusionnée avec une question à Thibaut ouverte** (commentaire ou description relu avant l'enchaînement automatique)
- [ ] Toute phrase « accepté par Thibaut » retrouvée dans le [registre des décisions](./registre-decisions.md)

## Avant la PR

- [ ] **Suite complète locale** (T-TECH-31-02) lancée avant le push ; sinon attente explicite dite dans la PR et espace demandé à Thibaut (aucune CI rouge découverte après des contrôles locaux verts)
- [ ] **Capture à 1280 px relue par l'orchestrateur** (règle 23, T-TECH-31-09) : home connectée, devis (liste et détail), relevé, connexions, page de consentement, page « Plugins » ; aucune vignette vide, aucune capture simulée
- [ ] Test d'historique HT et de commandes récentes TTC présent et vert (T-TECH-31-03)
- [ ] Wishlist : `composer install` dans l'arbre partagé lancé par l'orchestrateur **après** la fusion d'US-208 seulement

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story (noms de tests réels)
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public (page « Plugins ») ; vérification d'US-208 en lecture seule sur l'image reconstruite
- [ ] Pages connectées vérifiées en local et sur l'image reconstruite (aucune connexion en production) ; B2C inchangé ; refus d'accès identique
- [ ] **Que devient cet état après un déploiement ?** Aucun état hors base attendu ; sinon décrit et vérifié dans un conteneur neuf avant le merge
- [ ] Texte public relu contre sa source (maquette validée) ; parité FR et EN ; aucune comparaison avec Sylius Plus
