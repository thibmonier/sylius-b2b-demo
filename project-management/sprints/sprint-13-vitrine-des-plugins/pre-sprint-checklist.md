# Checklist de démarrage — Sprint 13

## Prérequis (sprint précédent)

- [ ] Sprint 12 : statut `done` dans `.bmad/sprint-status.yaml` (US-053, US-054, US-055 `done` ; US-052 `carried-over`)
- [ ] Revue et rétrospective du sprint 12 tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Reports identifiés : US-052 (T-052-03 à T-052-07, tâches [OPS] de l'utilisateur)
- [ ] Branche `feature/sprint-13-vitrine-des-plugins` active (`git fetch` + vérifier la branche avant tout commit)
- [ ] `doctrine:migrations:status` : aucune migration en attente

## Definition of Ready

- [x] US-063 à US-067 : critères d'acceptation Gherkin (1 nominal, 2 alternatifs, 2 erreurs), points estimés, DoD
- [x] Décomposition en tâches (`tasks/US-063-tasks.md` à `tasks/US-067-tasks.md`)
- [x] Capacité : 37,5 h décomposées (35 h de charge) pour ≈ 30-35 h, ordre de sacrifice à faire valider par l'utilisateur
- [x] Dépendances livrées : US-014 (page Plugins), US-052 (runbook), politique Sylius Plus

## Décisions et accords à confirmer

- [x] D1 à D8 (2026-10-17), voir [sprint-goal.md](./sprint-goal.md)
- [x] **Livraison des dépôts** : une PR par dépôt, un GO unique de merge (2026-10-17)
- [x] **Préfixe du sous-domaine** : `sylius-b2b` (2026-10-17) ; le domaine du site reste inconnu (US-068)
- [ ] URL du site et de la page « me contacter » : inconnues, attendues pour US-068 et US-069
- [ ] Sacrifices n° 1 à 4 acceptés

## Environnement technique

- [ ] Base de test locale : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [ ] Démo locale : `docker compose up` (voir `compose.yaml`), Docker PHP 8.4 pour les tests
- [ ] **Espace disque : `df -h .` avant chaque lot lourd ; arrêt si moins de 5 Go libres**
- [ ] Navigateur piloté disponible pour les captures
- [ ] Les 24 dépôts : `git fetch` fait, branche courante relevée (plusieurs ne sont pas sur `main`)
- [ ] `var/cache-*` des workers du sprint précédent supprimés

## Actions soumises au GO utilisateur

- [ ] T-067-05 : ouverture des 24 PR, puis merge (deux GO)
- [ ] T-066-05 : CNAME et domaine personnalisé Railway (par l'utilisateur)
- [ ] T-052-03 à T-052-07 : moniteur, bascule, `X-Real-IP`, parcours MCP, coût Railway (reportées)
- [ ] Tout push, merge, tag, déploiement ou commande Railway : GO explicite
