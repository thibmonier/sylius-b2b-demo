# Checklist d'intégration : Sprint 029

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale, à jour des actions des rétros des sprints 27 et 28). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ».

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-29-06) ; **sous 6 Go (5,5 Go au planning), aucune suite complète** : Thibaut prévenu avant le premier lot
- [ ] Un seul cache de rendu par worker et seuil disque appliqué aux rendus (T-TECH-29-01) **avant la première capture de maquette**
- [ ] `bin/proof-rebase` disponible (T-TECH-29-02) avant le premier rebase
- [ ] Branche `feature/sprint-29-dev` créée depuis `main` et à jour ; aucune configuration non commitée dans l'arbre partagé
- [ ] LCP mobile de la home remesuré (T-TECH-29-03) ; LCP de la liste « avant » mesuré (T-198-02) avant le premier gabarit

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] Aucun compteur écrit en dur : valeur déduite du catalogue ou de la preuve
- [ ] `git show --stat` et `git log -1` de chaque commit : seuls les fichiers de la tâche, **bon numéro de story**, aucun `git commit -a`
- [ ] **Aucune commande `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans la garde de la règle 18** (`set -u`, `[ -n "$D" ] && [ -d "$D" ]`, chemin sous le dossier attendu) ; commandes de nettoyage destinées à Thibaut en chemins absolus, précédées d'un `du -sh`
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Commit de tests rouges seul, cité dans la PR
- [ ] Preuve publiée : `bin/proof-rebase` (ou `php bin/proof-refresh` puis `php bin/proof-check`) si des fichiers de tests changent ; PR fusionnées en série
- [ ] **Séquence exacte de la CI rejouée en local** (règle 19) avant le push ; CI verte, run terminé, avant le merge
- [ ] Tâche cochée dans le commit qui la livre

## Portes de Thibaut

- [ ] Maquette de la liste présentée ; **GO noté au registre avec son auteur avant T-198-05** ; aucun gabarit Twig de la liste avant
- [ ] Maquette du panier présentée ; **GO noté au registre avant T-200-05** ; aucun gabarit Twig du panier avant
- [ ] Planche d'US-197 présentée ; **GO de téléchargement noté au registre avant T-197-06** ; aucune image dans le dépôt avant
- [ ] **Aucune PR fusionnée avec une question à Thibaut ouverte** (commentaire ou description relu avant l'enchaînement automatique)
- [ ] Toute phrase « accepté par Thibaut » retrouvée dans le [registre des décisions](./registre-decisions.md)

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story
- [ ] **Mesures de production consignées le jour du déploiement** (US-198 : pa11y AAA FR et EN, 360 et 1280 px, LCP du catalogue avant/après ; US-200 : ce qui est public ; US-197 : le lendemain, après la remise à zéro de 03:00 : pa11y, largeurs, LCP, page Crédits)
- [ ] **US-200 : tunnel de commande de bout en bout vert** ; panier connecté vérifié en local et sur l'image reconstruite (aucune connexion en production) ; B2C inchangé
- [ ] **Que devient cet état après un déploiement ?** Aucun état hors base attendu ; sinon décrit et vérifié dans un conteneur neuf (image reconstruite) avant le merge
- [ ] Texte public relu contre sa source (maquette validée) ; parité FR et EN ; aucune comparaison avec Sylius Plus ; licences des photos relues contre le manifeste ; aucune marque réelle hors liste noire
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; image reconstruite si `Dockerfile`, `docker/`, `config/`, assets ou dépendances changent

## Après chaque lot

- [ ] Aucun run actif, `bin/clean-test-caches --all`, `df -h .` ; restes listés à Thibaut avec la commande (chemins absolus, `du -sh` d'abord) ; swap relevé
- [ ] Une seule suite complète à la fois
