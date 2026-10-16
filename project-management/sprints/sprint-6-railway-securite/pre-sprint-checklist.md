# Checklist de démarrage — Sprint 6

## Prérequis (sprints précédents)

- [ ] Sprint 5 livré ou clôturé (PR #5 mergée, revue faite, rétro)
- [ ] Branche `feature/sprint-6-railway-securite` créée depuis `main` à jour (`git fetch` + vérification de la branche courante)
- [ ] CI verte sur `main`
- [ ] `docker compose up` démarre sans erreur ; `bin/console app:fixtures:load` passe
- [ ] `doctrine:migrations:status` : aucune migration en attente
- [ ] US-002, US-005, US-009, US-012 livrées

## Actions sur le compte Railway (utilisateur, avant ou pendant US-032/034)

- [ ] Accord explicite de l'utilisateur pour créer le projet Railway (action externe)
- [ ] Compte Railway accessible, dépôt GitHub relié
- [ ] Projet avec services web, MySQL et Mailpit (réseau privé, aucun domaine public sur Mailpit)
- [ ] Limite d'usage ≤ 25 $/mois posée (US-034)
- [ ] Notifications e-mail Railway activées pour les échecs de déploiement et de tâche (US-033)
- [ ] Variables posées côté Railway uniquement (jamais dans le dépôt) : `APP_SECRET`, `DATABASE_URL`, `DEFAULT_URI` (URL HTTPS publique), `DEMO_MODE=1`, identifiants de l'auth HTTP basique, `DEMO_MAILPIT_URL`/URL interne Mailpit
- [ ] Rappel : la démo reste PRIVÉE (auth HTTP basique active) jusqu'au GO explicite après US-037

## Environnement technique

- [ ] Rappels : `DEMO_MODE` vaut 0 par défaut ; pas de wkhtmltopdf sur le Mac local (la CI l'installe)
- [ ] Disque local presque plein : éviter les rebuilds d'images, privilégier la CI
- [ ] Docker 8.4 opérationnel pour les tests
- [ ] Mode d'exécution actuel (FrankenPHP classique) documenté avant le spike US-006

## Analyse préalable

- [ ] Relire les constats reportés : sprint 4 R1-R6, sprint 5 R1-R4, sprint 2 R1-R10 (voir `sprint-goal.md`, section Traçabilité)
- [ ] Relire `DemoResetCommand` et ses fixtures (garde démo-aware, atomicité) pour US-033
- [ ] Inventorier les routes d'administration à restreindre pour Sophie (administrateurs, canaux, passerelles, webhooks, clés) pour US-035
- [ ] Inventorier les formulaires publics (login, inscription, checkout, simulateur, `/demo/*`) pour la limitation de débit US-036
- [ ] Inventorier les appels et e-mails sortants possibles (mailer, PunchOut, webhooks) pour US-036
- [ ] Confirmer la politique : aucune ouverture publique sans GO explicite

## Stories prêtes pour le développement

- [ ] US-006, US-032, US-033, US-034, US-035, US-036, US-037 au statut `ready-for-dev`, Gherkin relus (≥ 1 nominal, 2 alternatifs, 2 erreurs ; les stories actuelles n'ont qu'un scénario nominal à compléter)
- [ ] Critères d'acceptation complétés avec les décisions du 2026-10-16 (auth HTTP basique, alertes e-mail Railway, constats reportés)
- [ ] US-029 et US-030 confirmées au sprint 7

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé (une phrase)
- [ ] 7 stories sélectionnées, 21 points
- [ ] Jalons compris : spike FrankenPHP, accès au compte Railway, revue US-037, GO utilisateur
- [ ] Ordre de réalisation validé

## Sprint Planning Part 2 — Comment

- [ ] Décomposition en tâches (`/project:decompose-tasks 006`), estimations de 0,5 h à 8 h
- [ ] Tâches dépendant du compte Railway identifiées et marquées
- [ ] Arbitrage des constats latents sprint 2 R3-R10
- [ ] Graphe de dépendances et Task Board initialisés (`task-board.md`)

## Critères de fin de sprint

- [ ] Démo déployée sur Railway en HTTPS, privée (auth HTTP basique), migrations en pre-deploy, healthcheck
- [ ] Mailpit interne, non exposé publiquement
- [ ] Reset nocturne 03:00 Europe/Paris testé, alerte d'échec vérifiée (journal + e-mail Railway)
- [ ] Limite d'usage posée ; coût relevé après une semaine (ou relevé planifié)
- [ ] Admin de démo restreint, durcissement actif (débit, sortant, en-têtes)
- [ ] ADR sur le mode worker FrankenPHP
- [ ] Revue de sécurité US-037 : aucun constat critique ou majeur ouvert ; tous les constats reportés clos ou acceptés par écrit
- [ ] GO d'ouverture publique laissé à l'utilisateur (non pris dans ce sprint)
- [ ] ECS, PHPStan, tests et CI verts ; aucun secret réel

## Ressources

| Ressource | Emplacement |
|-----------|-------------|
| Constats sprint 2 | `project-management/sprints/sprint-2-donnees-b2b/review-findings.md` |
| Constats sprint 4 | `project-management/sprints/sprint-4-theme-b2b/review-findings.md` |
| Constats sprint 5 | `project-management/sprints/sprint-5-listes-et-scenarios/review-findings.md` |
| Backlog | `project-management/backlog/user-stories/` |
| Statut des sprints | `.bmad/sprint-status.yaml` (non modifié ici) |
