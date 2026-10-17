# Tâches — US-051 : Container-fill, la boîte est l'unité de vente

## Informations US

- **Epic** : EPIC-08
- **Persona** : Thibaut
- **Story Points** : 2
- **Sprint** : sprint-11-fiabilite-et-ouverture
- **Dépendances** : US-044, US-048

## Résumé

En tant que Thibaut, je veux que « boîte de N » et « lot de N » soient l'unité de vente dans les données container-fill, afin que le panier de la démo soit cohérent.

## Règles

- Chaque correctif commence par un test qui échoue avant correction.
- Le plugin container-fill n'est pas modifié.

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-051-01 | [TEST] | Tests rouges : seeder (1 unité par colis, sans multiple), fiche produit, plan de la liste d'Atelier | 1.5h | — | ✅ |
| T-051-02 | [BE] | `VariantLogisticsSeeder` : `units_per_case` 1, poids du colis = poids de la variante, aucun multiple ; suppression de `PackagingUnitParser` | 1h | T-051-01 | ✅ |
| T-051-03 | [BE] | Quantités en boîtes : liste d'achat, commandes de démo ; vérifier devis et paliers | 1h | T-051-02 | ✅ |
| T-051-04 | [TEST] | Mise à jour de `ContainerFillDemoTest`, `CompanyWishlistSmokeTest`, catalogue des plugins (`config/demo/plugins.yaml`) | 1h | T-051-02 | ✅ |
| T-051-05 | [DOC] | `docs/remplissage-contenants.md` : règle de l'unité de vente, quantités avant/après, plan de Claire | 0.5h | T-051-03 | ✅ |
| T-051-06 | [REV] | Passe ECS, PHPStan, tests ciblés sur base de test (dès que Docker est disponible) | 1h | T-051-04 | ✅ |

**Total : 6h.**

## Détail

### T-051-01 à T-051-06

Voir la story [US-051](../../../backlog/user-stories/US-051.md) et `docs/remplissage-contenants.md`.

**Critères de validation** :
- [x] Tests écrits avant le correctif
- [ ] Tests ciblés verts sur base de test
- [ ] ECS et PHPStan verts
