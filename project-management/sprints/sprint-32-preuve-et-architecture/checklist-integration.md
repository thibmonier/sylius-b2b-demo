# Checklist d'intégration : Sprint 032

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale, à jour des actions des rétros des sprints 27 à 31). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ».

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-32-01, T-TECH-32-09) ; **sous 5 Go, aucun test ni rendu local** ; **sous 6 Go, aucune suite complète** ; au-delà de 2 Go de swap ou sous 7 Go de disque, au plus 2 workers lancent des tests (règle 24)
- [ ] **T-TECH-32-01** (runs autorisés annoncés, file d'attente sur le verrou) fait avant le lancement des workers ; disque libéré par Thibaut (T-TECH-32-02), ou travail limité à l'écriture de tests et de code
- [ ] **T-TECH-32-03** (avertissement de build périmé) fait **avant la première capture** ; `public/build` frais
- [ ] Branche `feature/sprint-32-preuve-et-architecture` à jour ; aucune configuration non commitée dans l'arbre partagé
- [ ] État « avant » relevé avant tout code : `ProofPageTest` (T-205-02), `ArchitecturePageTest` (T-206-02), `ProductImageFetcherTest` (T-062-01) verts, ou « non exécuté » avec la raison du disque
- [ ] Cadrage d'US-206 : **canal B2C** vérifié (classes `b2b-home-*` chargées ou non), absence de bloc de code confirmée (T-206-01)

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] **Tests lancés dans l'arbre partagé**, jamais depuis un worktree au `vendor` en lien symbolique (règle 22) ; **`vendor/` de l'arbre partagé non modifié par un worker** (règle 25)
- [ ] Aucun compteur écrit en dur : valeur déduite du catalogue, de la configuration ou du rapport de preuve
- [ ] **Tests existants non modifiés** : `git diff --stat -- tests/Functional/Demo/ProofPageTest.php tests/Functional/Demo/ArchitecturePageTest.php tests/Unit/Demo/Images/ProductImageFetcherTest.php` vide
- [ ] `git show --stat` et `git log -1` de chaque commit : seuls les fichiers de la tâche, **bon numéro de story**, aucun `git commit -a` ; **fichiers communs conformes à la matrice** (règle 21)
- [ ] **Aucune commande `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans la garde de la règle 18** ; commandes de nettoyage destinées à Thibaut en chemins absolus, précédées d'un `du -sh`
- [ ] **Aucun plugin modifié** (`git diff --stat -- vendor composer.lock` vide)
- [ ] **Aucun détail de sécurité** dans les fichiers, les messages de commit et la PR (section « sécurité » de la page « Architecture » restée générale, règle 12) ; ton neutre, aucune comparaison avec Sylius Plus
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Commit de tests rouges seul, cité dans la PR
- [ ] Preuve publiée par `bin/proof-rebase` si des fichiers de tests changent ; PR fusionnées en série
- [ ] **Séquence exacte de la CI rejouée en local** (règle 19) avant le push, quand le disque le permet ; CI verte, run terminé, avant le merge
- [ ] Réponse de l'orchestrateur au worker : **identifiant de la décision cité** (règle 25) ; le worker a relu le registre avant d'attendre (règle 26)
- [ ] Tâche cochée dans le commit qui la livre

## Portes de Thibaut

- [ ] Maquette « Preuve technique » présentée ; **GO noté au registre avant T-205-05** ; aucun Twig de la page avant
- [ ] Maquette « Architecture » présentée ; **GO noté au registre avant T-206-05** ; aucun Twig de la page avant
- [ ] Disque libéré par Thibaut (T-TECH-32-02), relevé par `df`
- [ ] Ordre de sacrifice confirmé par Thibaut au registre (proposition : US-062, puis US-205 ; US-206 protégée)
- [ ] **Aucune PR fusionnée avec une question à Thibaut ouverte** (commentaire ou description relu avant l'enchaînement automatique)
- [ ] Toute phrase « accepté par Thibaut » retrouvée dans le [registre des décisions](./registre-decisions.md) ; **toute PR hors sprint a sa ligne au registre avant son ouverture** (règle 26)

## Avant la PR

- [ ] **Suite complète locale** (T-TECH-32-11) lancée avant le push ; sinon attente explicite dite dans la PR et espace demandé à Thibaut (aucune CI rouge découverte après des contrôles locaux verts)
- [ ] **Capture à 1280 px relue par l'orchestrateur** (règle 23, T-TECH-32-12) : home connectée, page « Preuve technique », page « Architecture » ; aucune vignette vide, aucune capture simulée ; captures de production, dites dans la PR, si le rendu local est impossible
- [ ] Un test compte les éléments des blocs à nombre fixe ajoutés (chiffres clés, sections du sommaire)
- [ ] Aucun changement de dépendances : `composer.lock` et `vendor/` inchangés

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story (noms de tests réels)
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public (pages « Preuve technique » et « Architecture ») : pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px
- [ ] Pages vérifiées en local et sur l'image reconstruite (aucune connexion en production) ; B2C : page « Architecture » vérifiée sur ce canal
- [ ] **Que devient cet état après un déploiement ?** Aucun état hors base attendu ; sinon décrit et vérifié dans un conteneur neuf avant le merge
- [ ] Texte public relu contre sa source (maquette validée) ; parité FR et EN ; aucune comparaison avec Sylius Plus
- [ ] Reliquats du sprint 31 : T-TECH-32-07 fait (US-203 en done, noté au registre) et capture du panier (T-TECH-32-04), ou reportés avec la raison du disque
