# Checklist de démarrage — Sprint 2

## Prérequis (sprint précédent)

- [ ] Sprint 1 entièrement livré (26/26 points)
- [ ] Branche `feature/sprint-1-walking-skeleton` mergée sur `main`
- [ ] Branche `feature/sprint-2-donnees-b2b` créée depuis `main`
- [ ] CI verte sur `main` après le merge du sprint 1
- [ ] `docker compose up` démarre l'application sans erreur
- [ ] Page d'accueil répond 200 en HTTPS local
- [ ] Les 21 plugins sont enregistrés sans conflit (`lint:container` vert)
- [ ] Canaux B2B et B2C configurés avec leurs locales FR/EN (US-007)

## Environnement technique

- [ ] PHP 8.4 (ou Docker) opérationnel
- [ ] Docker Desktop opérationnel
- [ ] `bin/console` répond sans erreur dans le conteneur
- [ ] `doctrine:migrations:status` : aucune migration en attente
- [ ] Base de données accessible et vide (ou en état reset)

## Données & Fixtures

- [ ] Répertoire `fixtures/csv/` créé dans le dépôt
- [ ] Schéma CSV défini et documenté (colonnes obligatoires : SKU, MPN, EAN, nom FR, nom EN, taxon, poids, dimensions, conditionnement, norme) — avant tout développement US-008
- [ ] Au moins un fichier CSV d'exemple présent (catégorie EPI, quincaillerie ou fournitures) pour valider le parseur
- [ ] Structure des fixtures Symfony prévue (`src/DataFixtures/` ou `fixtures/`)
- [ ] Stratégie de chargement définie : Foundry, DoctrineFixturesBundle ou commande custom

## Stories prêtes pour le développement

- [ ] US-008 au statut `ready-for-dev`, critères d'acceptation relus
- [ ] US-009 au statut `ready-for-dev`, critères d'acceptation relus
- [ ] US-010 au statut `ready-for-dev` (peut attendre US-008 + US-009)
- [ ] US-012 au statut `ready-for-dev` (peut attendre les trois précédentes)

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé en équipe : « Données B2B cohérentes : catalogue industriel importé de CSV, entreprises/membres/comptes de démo, données inter-plugins cohérentes, commande de remise à zéro »
- [ ] 4 stories sélectionnées, 26 points
- [ ] Dépendances inter-stories confirmées (US-010 ← US-008 + US-009 ; US-012 ← tout)
- [ ] Ordre de réalisation validé

## Sprint Planning Part 2 — Comment

- [ ] Décomposition en tâches effectuée (voir `tasks/`)
- [ ] Estimations posées (0,5 h à 8 h par tâche max)
- [ ] Graphe de dépendances Mermaid créé (`task-board.md`)
- [ ] Task Board initialisé (`task-board.md`)

## Critères de fin de sprint (Definition of Done global)

- [ ] `bin/console app:fixtures:load` termine sans erreur
- [ ] 150 à 250 produits créés depuis les CSV (aucun lorem ipsum)
- [ ] Produits disponibles en FR et en EN sur les canaux B2B et B2C
- [ ] Au moins 5 entreprises créées avec membres et comptes rattachés
- [ ] `lint:container` vert après le chargement des fixtures
- [ ] Données cohérentes sur les 21 plugins (prix par canal, stock, expédition, devis, PunchOut, flux, documents…)
- [ ] `bin/console app:demo:reset` termine en moins de 2 minutes et remet l'état initial
- [ ] ECS et PHPStan passent sur le code ajouté/modifié
- [ ] Aucun secret dans le dépôt (mots de passe de démo acceptés s'ils sont triviaux et documentés)
- [ ] CI verte sur la branche du sprint

## Outils et ressources

| Ressource | URL / Commande |
|-----------|---------------|
| DoctrineFixturesBundle | `composer require --dev doctrine/doctrine-fixtures-bundle` |
| Foundry (fixtures) | `composer require --dev zenstruck/foundry` |
| Tech-spec §3 (données) | `project-management/tech-spec.md` §3 |
| ADR fixtures | `docs/adr/` (à créer si choix Foundry vs DoctrineFixtures) |
| Schéma CSV | `fixtures/csv/README.md` (à créer en début de sprint) |
| Sprint 1 handoff | `project-management/sprints/sprint-1-walking-skeleton/handoff.md` |
