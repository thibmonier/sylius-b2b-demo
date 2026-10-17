# Checklist de démarrage — Sprint 11

## Prérequis (sprint précédent)

- [x] Sprint 10 : statut `done` dans `.bmad/sprint-status.yaml` (US-029, US-046, US-047, US-048 `done`)
- [ ] Revue et rétrospective du sprint 10 tenues (`sprint-review.md`, `sprint-retro.md`)
- [x] PR #19 du sprint 10 mergée et déployée, correctif #20 (`-d memory_limit=1G`)
- [x] Reports identifiés : défaut du marqueur d'échec sur erreur fatale, mineur de revue Caddy, T-048-11, limite `demo_orders`
- [ ] Branche `feature/sprint-11-fiabilite-et-ouverture` active (`git fetch` + vérifier la branche avant tout commit ; aucun commit sans demande)
- [ ] `doctrine:migrations:status` : aucune migration en attente

## Definition of Ready

- [x] US-049 et US-050 : critères d'acceptation Gherkin (1 nominal, 2 alternatifs, 2 erreurs), points estimés, DoD
- [x] Décomposition en tâches (`tasks/US-049-tasks.md`, `tasks/US-050-tasks.md`)
- [ ] Capacité acceptée : 38 h décomposées (35 h de charge) pour ≈ 30-35 h, ordre de sacrifice relu par l'utilisateur
- [x] Dépendances livrées : US-033, US-035, US-036, US-037, US-040, US-041, US-048, US-030

## Décisions et accords à confirmer

- [ ] US-049 : choix entre marqueur « en cours » et `register_shutdown_function` (proposé en T-049-01 par le test rouge)
- [ ] US-050 : `DEMO_REGISTRATION_OPEN=0` obligatoire en mode public, ou avertissement seulement (T-050-05)
- [ ] US-050 : `robots.txt` / noindex pour une démo non promue (T-050-06)
- [ ] US-050 : choix du moniteur externe (UptimeRobot ou Better Stack) et date de la bascule

## Environnement technique

- [ ] Base de test locale : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [ ] Docker PHP 8.4 pour les tests ; image Caddy disponible pour T-049-05
- [ ] `DEMO_MODE` vaut 0 par défaut ; les tests des modes privé et public posent leurs variables eux-mêmes
- [ ] Disque local ~3 Go : pas de rebuild Docker superflu

## Actions soumises au GO utilisateur

- [ ] T-049-08 : relevé de coût Railway complet (pas avant 2026-10-23)
- [ ] T-050-09 : création du moniteur externe sur `/healthz`
- [ ] T-050-10 : bascule d'ouverture publique (GO explicite)
- [ ] T-050-11 : parcours MCP avec un vrai assistant, après ouverture
- [ ] Tout push, merge ou déploiement : GO explicite
