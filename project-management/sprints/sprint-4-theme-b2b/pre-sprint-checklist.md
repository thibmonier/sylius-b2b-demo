# Checklist de démarrage — Sprint 4

## Prérequis (sprints précédents)

- [ ] Sprint 2 livré (US-008 canaux B2B/B2C, US-009 comptes de démo)
- [ ] Branche `feature/sprint-4-theme-b2b` créée depuis `main` à jour (`git fetch` + vérification de la branche courante)
- [ ] CI verte sur `main`
- [ ] `docker compose up` démarre l'application sans erreur
- [ ] `bin/console app:fixtures:load` passe sans erreur
- [ ] Les 21 plugins sont enregistrés (`lint:container` vert)
- [ ] Build des assets (webpack/Encore) fonctionnel en local et en CI

## Environnement technique

- [ ] `bin/console` répond dans le conteneur
- [ ] `doctrine:migrations:status` : aucune migration en attente
- [ ] Mailpit accessible (lien du bandeau)
- [ ] Planification de `app:demo:reset` connue (prochaine remise à zéro du bandeau)

## Analyse préalable

- [ ] Lister les hookables des plugins existants (base du snapshot T-013-06)
- [ ] Identifier les templates de login shop et admin à étendre (US-015)
- [ ] Inventorier les endroits qui dupliquent les comptes : `CustomerDemoFixture`, `CompanyDemoFixture`, `DemoAccountsController`, `config/packages/demo_fixtures.yaml`, `tests/Smoke/AccountsSmokeTest.php`
- [ ] Confirmer que Marc n'appartient qu'à TechParts SAS (US-009)
- [ ] Choisir le mécanisme de rendu des badges (décorateur de hook) avant T-016-02

## Stories prêtes pour le développement

- [ ] US-013 à US-018 au statut `ready-for-dev`, critères Gherkin relus (≥ 1 nominal, 2 alternatifs, 2 erreurs)
- [ ] Compléments US-015 validés (login shop/admin, correctif /demo/accounts, source unique)

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé (une phrase)
- [ ] 6 stories sélectionnées, 28 points
- [ ] Dépendances confirmées (US-013→US-014 ; US-013→US-016→US-017 ; US-015 et US-018 indépendantes)
- [ ] Ordre de réalisation validé

## Sprint Planning Part 2 — Comment

- [ ] Décomposition en tâches effectuée (voir `tasks/`) : 40 tâches, 57h30
- [ ] Estimations de 0,5 h à 8 h par tâche
- [ ] Graphe de dépendances et Task Board initialisés (`task-board.md`)

## Critères de fin de sprint

- [ ] Thème B2B actif sur le canal B2B, B2C inchangé, hookables des plugins préservés
- [ ] Page d'accueil et page Plugins complètes (21 + nouveaux)
- [ ] Bandeau, connexion en un clic, comptes affichés sur les logins shop et admin, `/demo/accounts` corrigé, source unique en place
- [ ] Badges shop et admin, désactivables, accessibles au clavier
- [ ] Mentions légales et À propos en FR/EN
- [ ] ECS, PHPStan, tests et CI verts ; aucun secret réel
- [ ] Aucune modification de `.bmad/sprint-status.yaml` hors transitions normales

## Ressources

| Ressource | Emplacement |
|-----------|-------------|
| Épic | `project-management/backlog/epics/EPIC-03.md` |
| ADR canaux par cookie | `docs/adr/0011-*` |
| Contrôleur à refactorer | `src/Demo/Controller/DemoAccountsController.php` |
| Fixtures comptes | `src/Demo/DataFixtures/` |
| Assets | `assets/` |
