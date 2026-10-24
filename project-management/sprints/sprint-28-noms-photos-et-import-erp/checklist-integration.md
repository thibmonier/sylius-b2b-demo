# Checklist d'intégration : Sprint 028

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale, à jour des actions de la rétro du sprint 27). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ».

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-28-06) ; sous 6 Go, aucune suite complète
- [ ] Un seul cache de rendu par worker et seuil disque appliqué aux rendus (T-TECH-28-01) avant le premier rendu d'US-196
- [ ] Branche `feature/sprint-28-dev` à jour avec `main` ; aucune configuration non commitée dans l'arbre partagé
- [ ] LCP mobile de la home remesuré (T-TECH-28-05)

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] Aucun compteur écrit en dur : valeur déduite du catalogue ou de la preuve
- [ ] `git show --stat` et `git log -1` de chaque commit : seuls les fichiers de la tâche, **bon numéro de story**, aucun `git commit -a`
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Commit de tests rouges seul, cité dans la PR (orchestrateur et plugin compris)
- [ ] Preuve publiée : `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check` si des fichiers de tests changent ; PR fusionnées en série
- [ ] Tâche cochée dans le commit qui la livre

## Portes de Thibaut

- [ ] Questions 3, 4 et 6 de l'ADR 0017 tranchées avant l'affinage d'US-187 (T-187-02), notées au registre
- [ ] Planche d'US-196 présentée ; **GO de téléchargement noté au registre avant T-196-06** ; aucune image dans le dépôt avant
- [ ] **GO de Thibaut noté avant tout push, tag ou release du plugin** (T-187-10) ; version visée comparée à Packagist ; Packagist soumis par Thibaut
- [ ] `composer.json` et verrou de la démo commités seulement après la visibilité du tag sur Packagist

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story
- [ ] **Mesures de production consignées le jour du déploiement** (US-195 : `EPI-CASK-001`, liste noire absente du rendu, pa11y ; US-196 : pa11y, largeurs, LCP du catalogue avant/après, page Crédits ; US-187 : pages publiques) ; page du back-office d'US-187 vérifiée en local et sur l'image reconstruite
- [ ] **Que devient cet état après un déploiement ?** US-187 : répertoire d'échange et journal des lots vérifiés dans un conteneur neuf (image reconstruite) avant le merge
- [ ] Texte public relu contre sa source ; « ERP Démo (fictif) » partout, aucun ERP réel nommé, aucune comparaison avec Sylius Plus ; licences des photos relues contre le manifeste ; aucune marque réelle hors liste noire
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; image reconstruite si `Dockerfile`, `docker/`, `config/`, assets ou dépendances changent (montée du plugin comprise)

## Après chaque lot

- [ ] Aucun run actif, `bin/clean-test-caches --all`, `df -h .` ; restes listés à Thibaut avec la commande ; swap relevé ; base de test du plugin supprimée
- [ ] Une seule suite complète à la fois
