# Checklist de démarrage — Sprint 12

## Prérequis (sprint précédent)

- [ ] Sprint 11 : statut `done` dans `.bmad/sprint-status.yaml` (US-049, US-050, US-051 `done`)
- [ ] Revue et rétrospective du sprint 11 tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] PR #21 du sprint 11 mergée et déployée (GO de l'utilisateur), CI verte
- [ ] Reports identifiés : T-050-09, T-050-10, T-050-11, T-049-08 et vérification de `X-Real-IP` (US-052)
- [ ] Branche `feature/sprint-12-ouverture-et-plugins` active (`git fetch` + vérifier la branche avant tout commit ; aucun commit sans demande)
- [ ] `doctrine:migrations:status` : aucune migration en attente

## Definition of Ready

- [x] US-052 à US-055 : critères d'acceptation Gherkin (1 nominal, 2 alternatifs, 2 erreurs), points estimés, DoD
- [x] Décomposition en tâches (`tasks/US-052-tasks.md` à `tasks/US-055-tasks.md`)
- [x] Capacité acceptée : 43,5 h décomposées (39,5 h de charge) pour ≈ 30-35 h, ordre de sacrifice accepté par l'utilisateur (2026-10-17)
- [x] Dépendances livrées : US-027, US-036, US-049, US-050 (en attente du merge de la PR #21)

## Décisions et accords à confirmer

- [x] US-052 : le GO est donné par l'utilisateur pendant le sprint, date non fixée (2026-10-17) ; choix du moniteur (UptimeRobot ou Better Stack)
- [x] US-053 : le plafond quotidien des commandes ne s'applique pas à OCI (aucune commande créée) ; limiteurs par IP et usage unique appliqués (Tech Lead, 2026-10-17)
- [x] US-054 : dépôt GitHub public dans tous les cas (2026-10-17) ; nom définitif du plugin et du dépôt à confirmer
- [x] US-054 : publié sur Packagist même si Sylius Plus couvre le besoin, sans promotion ; Claude crée le dépôt et le tag après confirmation, l'utilisateur soumet à Packagist (2026-10-17)

## Environnement technique

- [ ] Base de test locale : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [ ] Docker PHP 8.4 pour les tests ; plugin de référence : `/Users/tmonier/Projects/sylius_plugins/sylius-channel-access-plugin` (le plus récent)
- [ ] **Espace disque : `df -h .` avant chaque suite complète et avant un lot de travaux lourds ; arrêt si moins de 5 Go libres** (action 1 de la rétro du sprint 11)
- [ ] `var/cache-*` des workers du sprint précédent supprimés
- [ ] Aucun rebuild Docker local superflu

## Actions soumises au GO utilisateur

- [ ] T-052-03 : création du moniteur externe sur `/healthz`
- [ ] T-052-04 : bascule d'ouverture publique (GO explicite, commandes Railway lancées par l'utilisateur)
- [ ] T-052-05 : vérification de `X-Real-IP` après la bascule (deux connexions)
- [ ] T-052-06 : parcours MCP avec un vrai assistant, après ouverture
- [ ] T-052-07 : relevé de coût Railway (après le 2026-10-23)
- [ ] T-054-04 : création du dépôt GitHub public du plugin par Claude (confirmation explicite) ; tag par Claude après confirmation et soumission Packagist par l'utilisateur en backlog (US-059)
- [ ] Tout push, merge, déploiement ou commande Railway : GO explicite
