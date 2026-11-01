# Audit Batch 2 — Quote Request, Purchase Approval, Company Insights, Customer Asset Management, Digital Product Passport

**Date** : 2026-11-01
**Sprint** : 44 (US-254 — Lot 1)
**Auditeur** : Claude (fork audit-batch-2)

---

## Résumé exécutif

| Plugin | Src | Tests | Ratio | Archi | Qualité | Sécu | Tests | Global |
|--------|-----|-------|-------|-------|---------|------|-------|--------|
| quote-request | 76 | 41 | 54 % | 7/10 | 6/10 | 8/10 | 7/10 | **7.0** |
| purchase-approval | 59 | 36 | 61 % | 9/10 | 8/10 | 9/10 | 8/10 | **8.5** |
| company-insights | 86 | 45 | 52 % | 8/10 | 7/10 | 9/10 | 7/10 | **7.8** |
| customer-asset-management | 35 | 28 | 80 % | 9/10 | 9/10 | 8/10 | 9/10 | **8.8** |
| digital-product-passport | 15 | 15 | 100 % | 8/10 | 9/10 | 10/10 | 10/10 | **9.3** |

**Moyenne batch** : **8.3 / 10**

---

## 1. sylius-quote-request-plugin

### Architecture (7/10)

**Points forts :**
- Bonne séparation en domaines métier : Basket, Checkout, Conversion, Expiration, Pricing, RateLimit, Token, Workflow
- DIP respecté : services configurés via PHP (`config/services.php`) avec alias d'interfaces
- Événements métier (Event/) et listeners séparés
- Factory pattern pour la création d'entités

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| Q1 | **Majeur** | ShopController dépasse largement la limite de 200 lignes | Controller/Shop/ | 514 |
| Q2 | **Majeur** | AdminController dépasse la limite de 200 lignes | Controller/Admin/ | 370 |
| Q3 | **Mineur** | Manager dépasse la limite de 200 lignes | Manager/ | 238 |

**Recommandation** : décomposer les contrôleurs en actions invocables (one action per controller) ou en sous-contrôleurs thématiques. Extraire les orchestrations du Manager vers des Command Handlers.

### Qualité (6/10)

- Complexité cyclomatique élevée probable dans les contrôleurs (volume de lignes)
- Le nombre de domaines (Basket, Checkout, Conversion, etc.) montre une bonne modularité mais les contrôleurs centralisent trop de logique
- ECS/PHPStan/Rector configurés (Makefile présent)

### Sécurité (8/10)

- Pas de requêtes SQL brutes détectées
- RateLimit/ indique une protection contre les abus
- Token/ pour la gestion sécurisée des jetons de devis
- `composer audit` non exécutable localement (pas de vendor/) — CI à vérifier

### Tests (7/10)

- 41 fichiers de test pour 76 fichiers source (54 %)
- Ratio acceptable mais en dessous de la cible 80 %
- Structure de tests présente pour les domaines critiques

---

## 2. sylius-purchase-approval-plugin

### Architecture (9/10)

**Points forts :**
- Excellente décomposition : Approval domain avec des classes focalisées (ApprovalPolicy 75 lignes, ApprovalRequirement 21 lignes)
- **Bridge pattern** pour les dépendances optionnelles (AdminOrderCreation, Invoicing, WebhookOutbox) — patron exemplaire
- Security layer dédiée avec `ApprovalVoter` (50 lignes)
- Api/ séparé en Admin et Shop
- Command/ pour CQRS
- EventListener/ bien séparé des Events

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| P1 | **Mineur** | ShopController légèrement au-dessus de 200 lignes | Controller/ | 226 |

**Recommandation** : surveiller la taille du contrôleur Shop lors des prochaines évolutions. Pas d'action immédiate nécessaire.

### Qualité (8/10)

- Classes courtes et focalisées (SRP respecté)
- Nommage explicite (ApprovalPolicy, ApprovalRequirement, ApprovalVoter)
- Bonne granularité des composants

### Sécurité (9/10)

- Voter dédié pour les autorisations
- Pas de requêtes SQL brutes
- Bridge pattern isole les dépendances tierces

### Tests (8/10)

- 36 fichiers de test pour 59 fichiers source (61 %)
- Couverture correcte des domaines critiques (Approval, Security)

---

## 3. sylius-company-insights-plugin

### Architecture (8/10)

**Points forts :**
- Architecture riche et bien structurée : Breakdown, Filter, Indicators, Monthly, Period, Ranking, Statistics, Widget
- Interfaces métier bien définies (BuyerRankingInterface, ProductRankingInterface, etc.)
- Bridge pattern pour les dépendances optionnelles (PurchaseApproval, TradeCredit)
- `RankingQueryTrait` mutualise les patterns SQL entre les classements
- Security: `InsightsVoter` + company dérivée du membre connecté (jamais de la requête)
- `CountedOrderSql` centralise la construction de requêtes

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| I1 | **Mineur** | InsightsController au-dessus de 200 lignes | Controller/ | 228 |
| I2 | **Info** | Utilisation intensive de DBAL brut (17+ requêtes) — toutes paramétrées, mais la maintenance est plus lourde qu'avec le QueryBuilder | Ranking/, Statistics/, etc. | — |
| I3 | **Info** | CsvExporter proche de la limite (191 lignes) | Export/ | 191 |

**Recommandation** : le DBAL brut est justifié par la complexité analytique des requêtes (agrégations, sous-requêtes). Toutes les requêtes utilisent des paramètres nommés (`:company`, `:from`, `:to`, `:currency`) via `PurchaseFilter::sql()` et `PurchaseFilter::parameters()`. Aucune injection possible. Surveiller la taille du contrôleur.

### Qualité (7/10)

- Trait `RankingQueryTrait` : DRY bien appliqué pour la mutualisation des requêtes
- Nombre élevé de fichiers (86) pour un plugin analytique — justifié par la richesse fonctionnelle
- Nommage métier clair

### Sécurité (9/10)

- **Toutes les requêtes DBAL sont paramétrées** — vérifié manuellement
- Voter dédié pour l'accès aux tableaux de bord
- Company ID provient exclusivement de la session utilisateur
- Aucune donnée sensible exposée dans les exports (agrégats uniquement)

### Tests (7/10)

- 45 fichiers de test pour 86 fichiers source (52 %)
- Ratio le plus bas du batch — la complexité analytique nécessiterait plus de tests
- Les requêtes DBAL sont particulièrement critiques à tester

---

## 4. sylius-customer-asset-management-plugin

### Architecture (9/10)

**Points forts :**
- Plugin compact et bien structuré
- Entity layer avec interfaces (CustomerAssetInterface, EquipmentModelInterface)
- **Fleet scoping pattern** : CompanyFleetScope, CustomerFleetScope implémentant FleetScopeInterface — patron de filtrage multi-niveau élégant
- Security layer : AdminAccessListener, CustomerRemovalListener, EquipmentModelDeletionGuard
- Filter/ pour le filtrage des assets

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| A1 | **Info** | 7 advisories `composer audit` — tous sur la dépendance transitive `sylius/sylius`, pas le code du plugin | composer.lock | — |

**Recommandation** : les advisories sont des vulnérabilités connues dans `sylius/sylius` (race condition promotions, percent-encoded API path bypass). Elles seront corrigées par la mise à jour de Sylius côté démo. Aucune action nécessaire côté plugin.

### Qualité (9/10)

- 35 fichiers source seulement — plugin focalisé (SRP au niveau plugin)
- Nommage explicite et métier
- Pas de fichier dépassant 200 lignes

### Sécurité (8/10)

- Guards de suppression (EquipmentModelDeletionGuard)
- Listeners de sécurité (AdminAccess, CustomerRemoval)
- Fleet scoping empêche l'accès croisé entre entreprises
- Les advisories `composer audit` ne sont pas exploitables via le plugin

### Tests (9/10)

- 28 fichiers de test pour 35 fichiers source (80 %)
- **Meilleur ratio du batch** (hors DPP)
- Couverture alignée avec la cible projet

---

## 5. sylius-digital-product-passport-plugin

### Architecture (8/10)

**Points forts :**
- Plugin le plus compact (15 fichiers source)
- Service layer propre : PassportRevisionService avec interface, DigitalLinkGenerator, GtinValidator, QrGenerator, CompatiblePartsProvider
- Migrations portables (Doctrine Schema API, pas de SQL brut)

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| D1 | **Mineur** | L'entité `DigitalProductPassport` contient `toJson()` — responsabilité de sérialisation qui pourrait être extraite dans un Serializer | Entity/DigitalProductPassport.php | 199 |
| D2 | **Info** | Pas d'interface pour l'entité principale (contrairement aux autres plugins) | Entity/ | — |

**Recommandation** : pour un plugin de cette taille, l'absence d'interface entité et le `toJson()` sur l'entité sont des compromis acceptables (YAGNI). À surveiller si le plugin grandit.

### Qualité (9/10)

- Tous les fichiers sous 200 lignes (max : 199 pour l'entité)
- Nommage clair et métier
- `GtinValidator` pour la validation des identifiants produit

### Sécurité (10/10)

- `composer audit` : **0 advisory**
- Pas de requêtes SQL brutes
- Pas de données sensibles manipulées
- Migrations portables

### Tests (10/10)

- **15 fichiers de test pour 15 fichiers source (100 %)**
- Ratio parfait — exemplaire pour l'écosystème
- Couverture 1:1

---

## Synthèse des constats par sévérité

### Majeur (2)

| # | Plugin | Constat | Action suggérée |
|---|--------|---------|-----------------|
| Q1 | quote-request | ShopController 514 lignes | Décomposer en actions invocables |
| Q2 | quote-request | AdminController 370 lignes | Décomposer en sous-contrôleurs |

### Mineur (3)

| # | Plugin | Constat | Action suggérée |
|---|--------|---------|-----------------|
| Q3 | quote-request | Manager 238 lignes | Extraire vers Command Handlers |
| P1 | purchase-approval | ShopController 226 lignes | Surveiller |
| D1 | digital-product-passport | `toJson()` sur l'entité | Surveiller si le plugin grandit |

### Info (4)

| # | Plugin | Constat |
|---|--------|---------|
| I1 | company-insights | InsightsController 228 lignes |
| I2 | company-insights | 17+ requêtes DBAL brutes (toutes paramétrées) |
| I3 | company-insights | CsvExporter 191 lignes |
| A1 | customer-asset-management | 7 advisories transitives sylius/sylius |
| D2 | digital-product-passport | Pas d'interface pour l'entité principale |

---

## Stories backlog proposées

| Story | Description | Points | Priorité |
|-------|-------------|--------|----------|
| US-XXX | Refactoring contrôleurs quote-request (décomposition en actions invocables) | 5 | Haute |
| US-XXX | Amélioration couverture tests company-insights (requêtes DBAL critiques) | 3 | Moyenne |

---

## Bonnes pratiques identifiées (à généraliser)

1. **Bridge pattern** (purchase-approval, company-insights) : à utiliser systématiquement pour les dépendances optionnelles entre plugins
2. **Fleet scoping** (customer-asset-management) : patron de filtrage multi-niveau réutilisable
3. **Ratio tests 1:1** (digital-product-passport) : cible à viser pour les nouveaux plugins
4. **Voter dédié** (purchase-approval, company-insights) : systématique pour la sécurité d'accès
5. **Requêtes paramétrées** (company-insights) : même en DBAL brut, toujours via `PurchaseFilter::parameters()`
