# Checklist d'intégration : Sprint 027

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale, à jour des actions de la rétro du sprint 26). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ».

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-27-08) ; sous 6 Go, aucune suite complète
- [ ] 503 en mode prod local compris et documenté (T-TECH-27-04, 05) avant la maquette d'US-189
- [ ] Branche `feature/sprint-27-dev` à jour avec `main` ; aucune configuration non commitée dans l'arbre partagé

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] Aucun compteur écrit en dur (plugins, fichiers de tests) : valeur déduite du catalogue ou de la preuve
- [ ] `git show --stat` de chaque commit : seuls les fichiers de la tâche ; aucun `git commit -a`
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Commit de tests rouges seul, cité dans la PR (orchestrateur compris)
- [ ] Preuve publiée : `php bin/proof-refresh` puis `php bin/proof-check` si des fichiers de tests changent
- [ ] Tâche cochée dans le commit qui la livre

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story
- [ ] **Mesures de production consignées le jour du déploiement** (US-189 : LCP avant/après, 360 et 1280 px, pa11y FR et EN ; US-193 et US-188 : pages publiques) ; page du back-office d'US-188 vérifiée en local et sur l'image reconstruite
- [ ] Texte public relu contre sa source ; « ERP Démo (fictif) » partout, aucun ERP réel nommé, aucune comparaison avec Sylius Plus
- [ ] US-189 : maquette validée par Thibaut avant le premier gabarit Twig (GO noté au registre)
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; image reconstruite si `Dockerfile`, `docker/`, `config/`, assets ou dépendances changent

## Après chaque lot

- [ ] Aucun run actif, `bin/clean-test-caches --all`, `df -h .` ; restes listés à Thibaut avec la commande ; swap relevé
- [ ] Une seule suite complète à la fois
