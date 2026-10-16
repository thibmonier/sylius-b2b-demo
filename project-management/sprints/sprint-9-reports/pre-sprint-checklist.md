# Checklist de démarrage — Sprint 9

## Prérequis (sprint précédent)

- [x] Sprint 8 : statut `done` dans `.bmad/sprint-status.yaml`
- [ ] Revue et rétrospective du sprint 8 confirmées (`retrospective.md` présent, à vérifier)
- [x] Reports identifiés : US-006, US-029, T-041-06 à 09, T-041-15
- [ ] Branche `feature/sprint-9-reports` active (`git fetch` + vérifier la branche avant tout commit ; aucun commit sans demande)
- [ ] CI verte sur `main` et sur la branche
- [ ] `doctrine:migrations:status` : aucune migration en attente

## Definition of Ready

- [x] US-006, US-029, US-044 : critères d'acceptation Gherkin présents, points estimés
- [x] Dépendances d'US-044 (US-008, US-010, US-039) livrées
- [ ] US-044 décomposée en tâches (`tasks/US-044-tasks.md`) au Planning Part 2
- [ ] Données de test d'US-044 : CSV du catalogue disponibles pour dériver les dimensions et poids

## Environnement technique

- [ ] Disque local ~3 Go libres : pas de rebuild superflu, tests ciblés
- [ ] Pas de wkhtmltopdf en local (tests concernés en CI)
- [ ] `DEMO_MODE` vaut 0 par défaut
- [ ] Docker PHP 8.4 pour les tests des plugins

## Actions datées ou soumises au GO utilisateur

- [ ] T-041-15 relevé de coût Railway complet : à partir du 2026-10-23 uniquement
- [ ] Tout push, merge ou déploiement : GO explicite
- [ ] Publication éventuelle d'une version de container-fill : GO explicite
