# Sprint 46 — Refactoring audit lot 2

## Sprint Goal

Corriger les constats majeurs de l'audit sprint 44, lot 2 : refactoring des fichiers > 300 lignes dans trade-credit, quote-request et facturx.

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 46 |
| Slug | refactoring-audit-2 |
| Début | 2026-11-01 |
| Fin | 2026-11-15 |
| Capacité | 13 points |
| Stories | 3 (US-259, US-260, US-261) |

## Périmètre

### US-259 — Refactoring TradeCreditReserver (5 pts)

**Plugin** : `sylius-trade-credit-plugin`
**Constat** : `TradeCreditReserver` fait 507 lignes avec 4 responsabilités (réservation + vérification + ledger + termes de paiement).
**Objectif** : extraire `ReservationValidator`, `LedgerWriter`, `PaymentTermsResolver`.
**Critères** :
- Aucun fichier > 200 lignes
- Tests existants toujours verts
- PHPStan max sans erreur
- CI du plugin verte

### US-260 — Refactoring contrôleurs quote-request (5 pts)

**Plugin** : `sylius-quote-request-plugin`
**Constat** : `ShopController` (514L) et `AdminController` (370L) — multi-responsabilités.
**Objectif** : décomposer chaque contrôleur en actions ou contrôleurs groupés par responsabilité.
**Critères** :
- Aucun fichier > 200 lignes
- Tests existants toujours verts
- PHPStan max sans erreur
- CI du plugin verte

### US-261 — Refactoring FacturXInvoiceMapper (3 pts)

**Plugin** : `sylius-facturx-plugin`
**Constat** : `FacturXInvoiceMapper` fait 348 lignes — mapper monolithique.
**Objectif** : extraire des sous-mappers par section (header, lines, totals, etc.).
**Critères** :
- Aucun fichier > 200 lignes
- Tests existants toujours verts
- PHPStan max sans erreur
- CI du plugin verte

### Tâches techniques

- T-TECH-46-01 : Marquer sprint 45 comme done (done)
- T-TECH-46-02 : Report T-TECH-43-04 (montant maquette Copilote)

## Particularités

Comme le sprint 45, ce sprint cible **3 dépôts de plugins distincts**. Chaque story est indépendante.
