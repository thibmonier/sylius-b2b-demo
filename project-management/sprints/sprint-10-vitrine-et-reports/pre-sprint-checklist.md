# Checklist de démarrage — Sprint 10

## Prérequis (sprint précédent)

- [ ] Sprint 9 : statut `done` dans `.bmad/sprint-status.yaml` (US-006 et US-044 `done`)
- [ ] Revue et rétrospective du sprint 9 tenues (`sprint-review.md`, `sprint-retro.md` remplis en séance)
- [ ] PR #16 du sprint 9 mergée (GO utilisateur) et CI verte sur `main`
- [x] Reports identifiés : US-029, actions 1 à 5 de la rétro, points ouverts de la revue
- [ ] Branche `feature/sprint-10-vitrine-et-reports` active (`git fetch` + vérifier la branche avant tout commit ; aucun commit sans demande)
- [ ] `doctrine:migrations:status` : aucune migration en attente

## Definition of Ready

- [x] US-029, US-046, US-047 : critères d'acceptation Gherkin présents, points estimés
- [x] US-048 créée (1 nominal, 2 alternatifs, 2 erreurs, DoD)
- [x] Décomposition en tâches (`tasks/US-029`, `US-046`, `US-047`, `US-048`)
- [ ] Capacité acceptée : 62,5 h pour ≈ 50 h, ordre de sacrifice relu par l'utilisateur
- [ ] Dépendances livrées : US-004, US-008, US-014, US-040, garde M5, threads bornés (US-006)

## Décisions et accords à confirmer

- [ ] US-046 : texte soumis à l'utilisateur avant merge (T-046-04) ; relire `sylius-plus-communication-policy`
- [ ] US-047 : Openverse, CC0 uniquement, budget de taille confirmé après le spike T-047-01
- [ ] US-047 : GO de l'utilisateur pour le lancement unique du script (T-047-04)

## Environnement technique

- [ ] Disque local ~3 Go libres : pas de rebuild superflu, tests ciblés
- [ ] Pas de wkhtmltopdf en local : faux binaire (voir T-048-02)
- [ ] `DEMO_MODE` vaut 0 par défaut
- [ ] Docker PHP 8.4 pour les tests des plugins
- [ ] Accès réseau pour le seul script d'images (jamais pour le reset ni la CI)

## Actions soumises au GO utilisateur

- [ ] T-048-11 à T-048-13 : relevé de coût, `/api/v2/shop/*`, mémoire du reset sur Railway (accès au compte)
- [ ] Tout push, merge ou déploiement : GO explicite
- [ ] Soumission Packagist ou publication de plugin : sans objet pour ce sprint
