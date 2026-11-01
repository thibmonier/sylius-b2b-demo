# Audit Batch 4 — Admin Order Creation, Agentic Feeds, AI Catalog Copilot, Audit Log, B2B MCP, Bulk Actions, Command Palette, GPSR

**Date** : 2026-11-01
**Sprint** : 44 (US-255 — Lot 2)

---

## Résumé exécutif

| Plugin | Src | Tests | Ratio | Fichiers > 200L | Interfaces | Sévérité max |
|--------|-----|-------|-------|-----------------|------------|--------------|
| admin-order-creation | 56 | 30 | 53,5 % | 3 | 12 | 🔴 critique |
| agentic-feeds | 58 | 21 | 36,2 % | 3 | 9 | majeur |
| ai-catalog-copilot | 46 | 27 | 58,6 % | 7 | 11 | majeur |
| audit-log | 39 | 25 | 64,1 % | 3 | 6 | majeur |
| b2b-mcp | 99 | 28 | 28,2 % | 1 | 8 | majeur |
| bulk-actions | 42 | 21 | 50,0 % | 1 | 6 | majeur |
| command-palette | 19 | 19 | 100 % | 0 | 2 | info |
| gpsr | 36 | 11 | 30,5 % | 0 | 7 | majeur |

---

## 1. sylius-admin-order-creation-plugin

### Architecture (7/10)

**Points forts :**
- DIP exemplaire : `config/services.php` avec des alias d'interfaces explicites pour chaque service
- 12 interfaces dans le domaine (DraftOrderFactoryInterface, DraftOrderModifierInterface, DraftOrderCheckoutInterface, etc.)
- Séparation claire : Draft/, Checkout/, Controller/, Reorder/, Purger/, Mailer/, Security/
- `OrderAdjustmentVoter` pour la sécurité d'accès aux ajustements

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| AOC-01 | 🔴 **Critique** | `DraftOrderStepsController` dépasse massivement le guideline (633L) — contrôleur wizard à 4 étapes concentrant toute la logique d'affichage et de traitement | Controller/Admin/DraftOrderStepsController.php | 633 |
| AOC-02 | **Majeur** | `DraftOrderModifier` dépasse 200 lignes | Draft/DraftOrderModifier.php | 270 |
| AOC-03 | **Mineur** | `DraftOrderCheckout` dépasse 200 lignes | Checkout/DraftOrderCheckout.php | 237 |

**Recommandation** : extraire le contrôleur wizard en 4 contrôleurs d'étape (ItemsStepController, AddressStepController, ShippingPaymentStepController, SummaryStepController) ou en actions invocables.

### Sécurité ✅

- Voter dédié (`OrderAdjustmentVoter`) pour les permissions d'ajustement
- CSRF sur les formulaires
- Pas de requêtes SQL brutes
- `declare(strict_types=1)` partout

### Tests (53,5 %)

- 30 fichiers de test pour 56 fichiers source
- Ratio en dessous de la cible 80 %

---

## 2. sylius-agentic-feeds-plugin

### Architecture (8/10)

**Points forts :**
- Bonne séparation par domaine : Feed/, Format/, Validation/, Storage/, Outbox/, Command/
- `FeedFormatInterface` avec `FormatRegistry` (OCP : nouveaux formats = nouvelles classes)
- 9 interfaces dans le domaine
- `LocalFeedStorage` et `FlysystemFeedStorage` (DIP sur le stockage)
- Prix Omnibus-safe avec calcul de la TVA du canal

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| AF-01 | **Majeur** | `ItemBuilder` concentre la construction de l'item neutre (prix, stock, attributs, liens) | Feed/ItemBuilder.php | 356 |
| AF-02 | **Mineur** | `FeedGenerator` dépasse 200 lignes (batches, streams, status) | Feed/FeedGenerator.php | 244 |
| AF-03 | **Mineur** | `ProductFeed` entité riche | Entity/ProductFeed.php | 218 |
| AF-04 | **Majeur** | Ratio test/src de 36,2 % — nettement en dessous de la cible | — | — |

**Recommandation** : extraire de `ItemBuilder` les sous-builders (PriceBuilder, AttributeBuilder, IdentifierBuilder). Augmenter la couverture de test sur les chemins critiques (prix Omnibus, conversion de devises).

### Sécurité ✅

- Requêtes paramétrées
- Pas de données sensibles exposées dans les feeds
- `declare(strict_types=1)` partout

---

## 3. sylius-ai-catalog-copilot-plugin

### Architecture (7/10)

**Points forts :**
- Pipeline claire : Facts → Prompt → Generator → Guard → SuggestionGenerator
- `TextGeneratorInterface` isolant le fournisseur AI (DIP)
- `DemoTextGenerator` comme défaut (jamais d'appel AI en dev/test)
- `ContentGuard` et `EnvironmentalClaimDetector` pour les garde-fous réglementaires (Directive EU 2024/825)

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| AIC-01 | **Majeur** | `EnvironmentalClaimDetector` concentre les patterns multilingues (7 langues) et la logique de détection | Guard/EnvironmentalClaimDetector.php | 407 |
| AIC-02 | **Majeur** | `GenerateSuggestionsCommand` dépasse 200 lignes | Command/GenerateSuggestionsCommand.php | 260 |
| AIC-03 | **Mineur** | `AiSuggestion` entité riche | Entity/AiSuggestion.php | 243 |
| AIC-04 | **Mineur** | `ContentGuard` dépasse 200 lignes | Guard/ContentGuard.php | 218 |
| AIC-05 | **Mineur** | `ProductFactsExtractor` dépasse 200 lignes | Facts/ProductFactsExtractor.php | 209 |
| AIC-06 | **Mineur** | `SuggestionController` dépasse 200 lignes | Controller/Admin/SuggestionController.php | 206 |
| AIC-07 | **Info** | 7 fichiers > 200 lignes — le plus élevé du batch | — | — |

**Recommandation** : la taille de `EnvironmentalClaimDetector` est partiellement justifiée par les patterns multilingues (données, pas logique). Envisager l'extraction des patterns en configuration YAML ou en constantes de classe par langue. Décomposer `GenerateSuggestionsCommand` en handler/service.

### Sécurité ✅

- Prompts contiennent uniquement des données catalogue (pas de données client)
- Textes stockés en plain text (échappés par Sylius)
- `declare(strict_types=1)` partout

---

## 4. sylius-audit-log-plugin

### Architecture (8/10)

**Points forts :**
- `ChangeCollector` + `AuditLogWriter` (DBAL, savepoint) — recording qui ne bloque jamais le flush audité
- Translations pliées dans le translatable, collections dans leur owner — bon DRY
- `FieldPolicy` (ignored/masked fields), `ValueNormalizer` — bon design
- `RecordingPolicy` configurable (sources, commandes ignorées)

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| AL-01 | **Majeur** | `ChangeCollector` est le cœur du plugin mais concentre trop de responsabilités (UoW reading, translation folding, collection handling, reference resolution) | Recorder/ChangeCollector.php | 293 |
| AL-02 | **Mineur** | `Configuration` est riche mais inhérent au DI bundle pattern | DependencyInjection/Configuration.php | 252 |
| AL-03 | **Mineur** | `AuditLogEntry` entité riche | Entity/AuditLogEntry.php | 221 |

**Recommandation** : envisager l'extraction d'un `TranslationFoldingCollector` et d'un `CollectionChangeCollector` depuis `ChangeCollector` pour respecter SRP. La `Configuration` est acceptable pour un bundle Symfony complexe.

### Sécurité ✅

- Ne stocke jamais de secrets (masked fields, patterns)
- Recording ne bloque jamais le flush (catch, log, continue)
- `declare(strict_types=1)` partout

---

## 5. sylius-b2b-mcp-plugin

### Architecture (9/10)

**Points forts :**
- Le plus riche du batch (99 fichiers source, 17 répertoires)
- OAuth 2.1 complet (league/oauth2-server) avec tokens hachés, PKCE S256, consent
- `ToolRunner` centralise : auth, scope, idempotency, audit, refusals
- 8 interfaces + outils optionnels chargés conditionnellement par bundle
- `AccountUsabilityChain` (first refusal wins) — bon pattern composite
- Rate limiting, idempotency store DBAL

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| MCP-01 | **Mineur** | `RequestQuoteTool` dépasse légèrement 200 lignes | Mcp/Tool/RequestQuoteTool.php | 209 |
| MCP-02 | **Majeur** | Ratio test/src de 28,2 % — le plus bas de tout l'écosystème pour un plugin critique (OAuth, sécurité) | — | — |

**Recommandation** : priorité haute pour augmenter la couverture de test, en particulier sur les chemins OAuth (authorization, token, revocation), les outils MCP et le rate limiting.

### Sécurité ✅

- Tokens et secrets hachés (SHA-256, password_hash)
- PKCE S256 obligatoire
- Rate limiting par IP et par utilisateur
- Audit logging de chaque appel outil
- Scopes granulaires

---

## 6. sylius-bulk-actions-plugin

### Architecture (8/10)

**Points forts :**
- Pattern Strategy : `BulkActionInterface` + registry + processor par batch
- `ResourceEventDispatcher` reproduit le comportement du ResourceController (pre/post events)
- Pas de table plugin (aucune migration)
- Grid intégration via type bulk action configuré

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| BA-01 | **Majeur** | `BulkActionController` dépasse 200 lignes (prepare, execute, ship pages) | Controller/Admin/BulkActionController.php | 268 |

**Recommandation** : extraire les trois pages du contrôleur en actions invocables (PrepareAction, ExecuteAction, ShipAction).

### Sécurité ✅

- CSRF sur les formulaires d'exécution
- `max_items` enforced dans le processor et le contrôleur
- Pas de requêtes SQL brutes

---

## 7. sylius-command-palette-plugin

### Architecture (9/10) ✅ Exemplaire

**Points forts :**
- Plugin le plus compact (19 fichiers source, 1 209 lignes)
- Organisation claire : Controller/, Page/, Search/, Twig/
- Ratio test/src de 100 % (19/19)
- Aucun fichier > 200 lignes (max : 134 lignes pour `AdminPageCatalog`)
- 2 interfaces

**Constats :**

| # | Sévérité | Constat |
|---|----------|---------|
| — | **Info** | Plugin exemplaire — aucun constat négatif |

---

## 8. sylius-gpsr-plugin

### Architecture (8/10)

**Points forts :**
- Bonne séparation : Entity/, Form/, Fixture/, Grid/, Resolver/, Twig/, EventListener/
- `AddressableTrait` mutualise les champs d'adresse — bon DRY
- 7 interfaces pour 36 fichiers source
- `EquipmentModelDeletionGuard` pattern

**Constats :**

| # | Sévérité | Constat | Fichier | Lignes |
|---|----------|---------|---------|--------|
| GP-01 | **Majeur** | Ratio test/src de 30,5 % (11 fichiers de test pour 36 source) — très en dessous du seuil | — | — |

**Recommandation** : augmenter la couverture de test, en particulier sur les formulaires GPSR et les fixtures.

### Sécurité ✅

- Pas de requêtes SQL brutes
- Migrations portables (Doctrine Schema API)

---

## Synthèse des constats par sévérité

### Critique (1)

| # | Plugin | Constat | Action suggérée |
|---|--------|---------|-----------------|
| AOC-01 | admin-order-creation | `DraftOrderStepsController` 633 lignes | Décomposer en 4 contrôleurs d'étape |

### Majeur (8)

| # | Plugin | Constat | Action suggérée |
|---|--------|---------|-----------------|
| AOC-02 | admin-order-creation | `DraftOrderModifier` 270L | Surveiller, extraire si grandit |
| AF-01 | agentic-feeds | `ItemBuilder` 356L | Extraire sous-builders |
| AF-04 | agentic-feeds | Ratio tests 36,2 % | Augmenter couverture |
| AIC-01 | ai-catalog-copilot | `EnvironmentalClaimDetector` 407L | Extraire patterns en config |
| AIC-02 | ai-catalog-copilot | `GenerateSuggestionsCommand` 260L | Décomposer en handler |
| AL-01 | audit-log | `ChangeCollector` 293L | Extraire collectors spécialisés |
| MCP-02 | b2b-mcp | Ratio tests 28,2 % | Priorité haute |
| BA-01 | bulk-actions | `BulkActionController` 268L | Extraire en actions invocables |
| GP-01 | gpsr | Ratio tests 30,5 % | Augmenter couverture |

### Mineur (8)

| # | Plugin | Constat |
|---|--------|---------|
| AOC-03 | admin-order-creation | `DraftOrderCheckout` 237L |
| AF-02 | agentic-feeds | `FeedGenerator` 244L |
| AF-03 | agentic-feeds | `ProductFeed` 218L (entité) |
| AIC-03 | ai-catalog-copilot | `AiSuggestion` 243L (entité) |
| AIC-04 | ai-catalog-copilot | `ContentGuard` 218L |
| AIC-05 | ai-catalog-copilot | `ProductFactsExtractor` 209L |
| AIC-06 | ai-catalog-copilot | `SuggestionController` 206L |
| AL-02 | audit-log | `Configuration` 252L |
| AL-03 | audit-log | `AuditLogEntry` 221L (entité) |
| MCP-01 | b2b-mcp | `RequestQuoteTool` 209L |

### Info (1)

| # | Plugin | Constat |
|---|--------|---------|
| — | command-palette | Plugin exemplaire |

---

## Points forts transversaux

1. **`declare(strict_types=1)` à 100 %** sur les 8 plugins (vérification exhaustive)
2. **DIP systématique** : `config/services.php` avec alias d'interfaces dans chaque plugin
3. **Sécurité exemplaire du b2b-mcp** : OAuth 2.1 complet, tokens hachés, PKCE, rate limiting, audit
4. **command-palette** : rapport test/source parfait (100 %), aucun fichier > 200 lignes

## Stories backlog proposées

| Story | Description | Points | Priorité |
|-------|-------------|--------|----------|
| US-XXX | Refactoring `DraftOrderStepsController` (633L → 4 contrôleurs d'étape) | 5 | Haute |
| US-XXX | Augmenter couverture tests b2b-mcp (28,2 % → 80 %) | 8 | Haute |
| US-XXX | Refactoring `ItemBuilder` agentic-feeds (356L → sous-builders) | 3 | Moyenne |
| US-XXX | Refactoring `EnvironmentalClaimDetector` ai-catalog-copilot (407L → config) | 3 | Moyenne |
| US-XXX | Augmenter couverture tests gpsr (30,5 % → 80 %) | 3 | Moyenne |
| US-XXX | Augmenter couverture tests agentic-feeds (36,2 % → 80 %) | 3 | Moyenne |
