# Audit consolidé — 33 dépôts de plugins Sylius

**Date** : 2026-11-01
**Sprint** : 44 (US-254, US-255)
**Périmètre** : architecture (SOLID, hexagonal, DIP), qualité (KISS, DRY, complexité), sécurité (OWASP, dépendances), tests

---

## Chiffres clés

| Indicateur | Valeur |
|------------|--------|
| Dépôts audités | 33 (31 plugins + 1 thème + 2 packs) |
| Fichiers source PHP | 1 444 |
| Fichiers de test PHP | 759 |
| Lignes de code source | 94 612 |
| Ratio test/source global | 52,6 % |
| `declare(strict_types=1)` | **100 %** (tous les fichiers PHP) |
| PHPStan level | max (tous les plugins) |
| README EN + FR | **100 %** (tous les dépôts) |
| Fichiers > 200 lignes | 59 (4,1 % des fichiers source) |

---

## Constats par sévérité

### 🔴 Critique (3)

| # | Plugin | Constat | Lignes | Action |
|---|--------|---------|--------|--------|
| 1 | admin-order-creation | `DraftOrderStepsController` — contrôleur wizard monolithique | 633 | Décomposer en 4 contrôleurs d'étape |
| 2 | quick-order | `QuickOrderController` — 22 méthodes, violation SRP sévère | 415 | Extraire en actions invocables |
| 3 | trade-credit | `TradeCreditReserver` — réservation + vérification + ledger + termes | 507 | Extraire `ReservationValidator`, `LedgerWriter`, `PaymentTermsResolver` |

### 🟠 Majeur (20)

**Fichiers > 200 lignes (SRP) :**

| Plugin | Fichier | Lignes |
|--------|---------|--------|
| quote-request | ShopController | 514 |
| quote-request | AdminController | 370 |
| quote-request | QuoteRequest (entité) | 393 |
| agentic-feeds | ItemBuilder | 356 |
| ai-catalog-copilot | EnvironmentalClaimDetector | 407 |
| facturx | FacturXInvoiceMapper | 348 |
| facturx | FontWidthsNormalizer | 329 |
| quick-order | BulkCartAdder | 355 |
| quick-order | MatrixOrderComponent | 340 |
| tier-pricing | TierPriceRuleCsvImporter | 307 |
| webhook-outbox | DeliveryClaimer | 310 |
| punchout-cxml | BuyerType | 368 |
| punchout-cxml | CxmlEndpointAction | 281 |
| audit-log | ChangeCollector | 293 |
| bulk-actions | BulkActionController | 268 |

**Ratios de test insuffisants (< 50 %) :**

| Plugin | Ratio | Risque |
|--------|-------|--------|
| b2b-mcp | 28,2 % | Élevé (OAuth, sécurité) |
| gpsr | 30,5 % | Moyen |
| agentic-feeds | 36,2 % | Moyen |
| punchout-cxml | 43,8 % | Moyen |
| company-accounts | 48,4 % | Moyen |

**Dépendances :**

| Plugin | Constat |
|--------|---------|
| industry-trade-theme | CVE-2026-45070 (symfony/mime) — transitive via sylius/sylius |

### 🟡 Mineur (≈ 25)

Principalement des fichiers entre 200 et 260 lignes (entités Doctrine riches, configurations DI, contrôleurs proches de la limite). Aucun ne constitue un risque immédiat.

### ℹ️ Info

- Les 8 advisories `composer audit` présentes dans tous les plugins sont des vulnérabilités de `sylius/sylius` (test-application dependencies), pas du code des plugins
- Les 2 dépendances abandonnées (`behat/transliterator`, `php-http/message-factory`) sont transitives et non contrôlables

---

## Constats transversaux

### ✅ Points forts (à préserver)

| Pratique | Adoption |
|----------|----------|
| `declare(strict_types=1)` | 100 % |
| PHPStan level max | 100 % |
| README bilingue (EN + FR) | 100 % |
| `config/services.php` (DI explicite) | 100 % |
| Interfaces dans le domaine (DIP) | 100 % |
| Requêtes SQL paramétrées | 100 % (DBAL ParameterType, Doctrine DQL) |
| Migrations Doctrine Schema API (portables) | 100 % |
| Traductions 7 langues (TranslationsTest) | 100 % |
| Tests fonctionnels en transaction rollback | 100 % |
| Pas de secrets en dur | 100 % |

### ✅ Patterns exemplaires (à généraliser)

1. **Bridge pattern** (purchase-approval, company-insights, container-fill) — dépendances optionnelles entre plugins
2. **Ports/adapters** (copilot, einvoice-transmission) — isolation du fournisseur externe
3. **Fleet scoping** (customer-asset-management) — filtrage multi-niveau par entreprise
4. **Deny-by-default** (channel-access) — sécurité par défaut
5. **GDPR intégré** (timeline) — module d'effacement dédié
6. **Ratio test 100 %** (command-palette, digital-product-passport) — cible pour les nouveaux plugins

### ⚠️ Axes d'amélioration

1. **Taille des fichiers** : 59 fichiers dépassent 200 lignes (3 > 500 lignes). Les contrôleurs « wizard » et les importers CSV sont les principales cibles de refactoring SRP.
2. **Couverture de test** : ratio global 52,6 % — 5 plugins sous 50 %. Le b2b-mcp (28,2 %) est le plus critique (OAuth, sécurité).
3. **Dépendances** : 1 seul CVE direct (symfony/mime dans le thème), mais le parc sylius/sylius a 8 advisories dans les test-apps — à surveiller lors des montées de version.

---

## Top 10 des fichiers à refactorer (par impact)

| # | Plugin | Fichier | Lignes | Raison |
|---|--------|---------|--------|--------|
| 1 | admin-order-creation | DraftOrderStepsController | 633 | Wizard monolithique |
| 2 | quote-request | ShopController | 514 | Multi-responsabilités |
| 3 | trade-credit | TradeCreditReserver | 507 | SRP (4 responsabilités) |
| 4 | quick-order | QuickOrderController | 415 | 22 méthodes |
| 5 | ai-catalog-copilot | EnvironmentalClaimDetector | 407 | Patterns data + logique |
| 6 | quote-request | QuoteRequest (entité) | 393 | Entité très riche |
| 7 | quote-request | AdminController | 370 | Multi-responsabilités |
| 8 | punchout-cxml | BuyerType | 368 | Form type complexe |
| 9 | agentic-feeds | ItemBuilder | 356 | Builder multi-aspects |
| 10 | quick-order | BulkCartAdder | 355 | Validation + ajout |

---

## Plugins exemplaires

| Plugin | Note | Raison |
|--------|------|--------|
| digital-product-passport | 9,3/10 | 15 src / 15 tests (100 %), 0 advisory, aucun fichier > 200L |
| channel-access | 9,5/10 | 20 src / 21 tests (105 %), deny-by-default, path traversal guard, 0 advisory |
| command-palette | 9/10 | 19 src / 19 tests (100 %), aucun fichier > 200L |
| registration-approval | 9/10 | 32 src / 24 tests (158 %), tous les fichiers < 91L |
| b2b-sales-copilot | 9/10 | Architecture ports/adapters exemplaire, 17 src / 21 tests (124 %) |
| purchase-approval | 8,5/10 | Bridge pattern, voter dédié, bonne couverture |
| einvoice-transmission | 9/10 | Ports/adapters, DBAL savepoint, test doubles propres |

---

## Stories de correction proposées (backlog sprint 45+)

### Priorité haute (13 pts)

| Story | Description | Points |
|-------|-------------|--------|
| US-256 | Refactoring contrôleurs admin-order-creation (DraftOrderStepsController 633L → 4 contrôleurs) | 5 |
| US-257 | Augmenter couverture tests b2b-mcp (28,2 % → ≥ 70 %, focus OAuth et outils) | 5 |
| US-258 | Refactoring QuickOrderController (415L → actions invocables) | 3 |

### Priorité moyenne (24 pts)

| Story | Description | Points |
|-------|-------------|--------|
| US-259 | Refactoring TradeCreditReserver (507L → 3 services) | 5 |
| US-260 | Refactoring contrôleurs quote-request (Shop 514L + Admin 370L) | 5 |
| US-261 | Refactoring FacturXInvoiceMapper (348L → sous-mappers) | 3 |
| US-262 | Augmenter couverture tests gpsr (30,5 % → ≥ 70 %) | 3 |
| US-263 | Augmenter couverture tests agentic-feeds (36,2 % → ≥ 70 %) | 3 |
| US-264 | Refactoring DeliveryClaimer webhook-outbox (310L → 2 services) | 3 |
| US-265 | Mettre à jour contraintes dépendances thème (CVE symfony/mime) | 2 |

### Priorité basse (surveillance)

- Entités Doctrine riches (200-290 lignes) : pas d'action immédiate, surveiller lors des évolutions
- Configurations DI (> 200 lignes) : inhérent au pattern bundle Symfony
- Fichiers de fixtures (> 200 lignes) : acceptables pour des données de test riches

---

## Conclusion

L'écosystème de 33 dépôts présente une **architecture solide et homogène** :
- 100 % strict_types, PHPStan max, DI explicite, interfaces dans le domaine, requêtes paramétrées
- 0 vulnérabilité directe dans le code des plugins
- Patterns exemplaires (ports/adapters, bridge, deny-by-default, GDPR)

Les **axes d'amélioration** sont concentrés sur :
1. **3 fichiers critiques** (> 500 lignes) à refactorer en priorité
2. **5 plugins** avec un ratio de test < 50 % (b2b-mcp en tête)
3. **1 CVE transitive** dans le thème (symfony/mime)

Aucun constat de sécurité exploitable n'a été identifié dans le code des plugins.
