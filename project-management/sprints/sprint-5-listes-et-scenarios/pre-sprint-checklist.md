# Checklist de démarrage — Sprint 5

## Prérequis (sprints précédents)

- [ ] Sprint 4 livré ou clôturé (bandeau de démo, DEMO_MAILPIT_URL, source unique des comptes)
- [ ] US-010, US-023 (ADR-0012), US-009, US-003 livrées
- [ ] Branche `feature/sprint-5-listes-et-scenarios` créée depuis `main` à jour (`git fetch` + vérification de la branche courante)
- [ ] CI verte sur `main`
- [ ] `docker compose up` démarre sans erreur ; `bin/console app:fixtures:load` passe
- [ ] `doctrine:migrations:status` : aucune migration en attente

## Environnement technique

- [ ] Mailpit joignable depuis le conteneur web (URL interne de l'API) ; URL publique du lien distincte
- [ ] Dépôt `/Users/tmonier/Projects/sylius_plugins/sylius-company-accounts-plugin` : `git fetch`, branche courante vérifiée, `chore/quality-tooling` mergée ou non, main à jour (v1.1.0)
- [ ] Docker 8.4 opérationnel pour les tests du plugin

## Analyse préalable

- [ ] Relire ADR-0012 (critères go/no-go, bascule vers plugin maison)
- [ ] Lister les e-mails envoyés par la démo et repérer ceux qui portent un jeton (US-028)
- [ ] Inventorier le simulateur de la TestApplication du plugin punchout (US-027)
- [ ] Repérer les fixtures de commandes existantes (US-011)
- [ ] Confirmer la politique de publication : aucune release v1.2 sans accord explicite

## Stories prêtes pour le développement

- [ ] US-011, US-024, US-025, US-027, US-028 au statut `ready-for-dev`, Gherkin relus (≥ 1 nominal, 2 alternatifs, 2 erreurs)
- [ ] Ré-estimations proposées arbitrées (US-025, US-028)

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé (une phrase)
- [ ] 5 stories sélectionnées, 29 points
- [ ] Jalons de décision compris : go/no-go US-024 (T-024-04), accord de publication v1.2 (T-025-08)
- [ ] Ordre de réalisation validé

## Sprint Planning Part 2 — Comment

- [ ] Décomposition en tâches effectuée (voir `tasks/`) : 46 tâches, 76.5h (chemin GO)
- [ ] Estimations de 0,5 h à 8 h par tâche
- [ ] Graphe de dépendances et Task Board initialisés (`task-board.md`)

## Critères de fin de sprint

- [ ] Historique de commandes et factures Factur-X pour chaque entreprise
- [ ] US-024 : GO livré, ou NO-GO tracé (story bloquée, plugin maison au sprint 7)
- [ ] Carnet d'adresses intégré à la démo ; v1.2 publiée uniquement avec accord explicite
- [ ] Simulateur cXML/OCI fonctionnel ; boîte d'e-mails en lecture seule sans e-mail sensible
- [ ] Couverture mesurée et `doctrine:schema:validate --skip-sync` en CI
- [ ] ECS, PHPStan, tests et CI verts ; aucun secret réel
- [ ] Aucune modification de `.bmad/sprint-status.yaml` hors transitions normales

## Ressources

| Ressource | Emplacement |
|-----------|-------------|
| ADR wishlist | `docs/adr/0012-wishlist-officielle-vs-plugin-maison.md` |
| Plugin company-accounts | `/Users/tmonier/Projects/sylius_plugins/sylius-company-accounts-plugin` |
| Backlog | `project-management/backlog/user-stories/` |
