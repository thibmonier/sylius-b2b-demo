# Sprint 45 — Refactoring audit

## Sprint Goal

Corriger les 3 constats critiques de l'audit sprint 44 : refactoring des contrôleurs monolithiques et augmentation de la couverture de tests.

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 45 |
| Slug | refactoring-audit |
| Début | 2026-11-01 |
| Fin | 2026-11-15 |
| Capacité | 13 points |
| Stories | 3 (US-256, US-257, US-258) |

## Périmètre

### US-256 — Refactoring DraftOrderStepsController (5 pts)

**Plugin** : `sylius-admin-order-creation-plugin`
**Constat** : contrôleur wizard monolithique de 633 lignes, violation SRP sévère.
**Objectif** : décomposer en 4 contrôleurs d'étape (CustomerStep, ItemsStep, AddressStep, ConfirmStep).
**Critères** :
- Aucun fichier > 200 lignes
- Tests existants toujours verts
- PHPStan max sans erreur
- CI du plugin verte

### US-257 — Augmenter couverture tests b2b-mcp (5 pts)

**Plugin** : `sylius-b2b-mcp-plugin`
**Constat** : ratio test/source de 28,2 %, le plus faible du parc. Plugin avec des enjeux OAuth et sécurité.
**Objectif** : atteindre ≥ 70 % de couverture, focus sur les outils MCP et l'authentification OAuth.
**Critères** :
- Ratio test/source ≥ 70 %
- Tests unitaires pour chaque outil MCP
- Tests pour le flux OAuth
- PHPStan max sans erreur
- CI du plugin verte

### US-258 — Refactoring QuickOrderController (3 pts)

**Plugin** : `sylius-quick-order-plugin`
**Constat** : contrôleur de 415 lignes avec 22 méthodes, violation SRP sévère.
**Objectif** : extraire les méthodes en actions invocables (single-action controllers).
**Critères** :
- Aucun fichier > 200 lignes
- Tests existants toujours verts
- PHPStan max sans erreur
- CI du plugin verte

### Tâches techniques

- T-TECH-45-01 : Marquer sprint 44 comme done (done)
- T-TECH-45-02 : Report T-TECH-43-04 (montant maquette Copilote)

## Particularités

Ce sprint cible **3 dépôts de plugins distincts**, pas le dépôt de la démo. Chaque story est indépendante et peut être développée en parallèle sans conflit de fichiers.

## Livrables

- Code refactoré dans chaque plugin repo
- Tests ajoutés/maintenus
- CI verte sur chaque plugin
- Tags de version si applicable
- Documentation sprint dans le dépôt démo
