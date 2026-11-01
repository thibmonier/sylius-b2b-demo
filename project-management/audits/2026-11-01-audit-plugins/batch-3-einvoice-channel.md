# Audit Batch 3 — einvoice-transmission, erp-flatfile, predictive-analytics, container-fill, channel-access

**Date** : 2026-11-01
**Périmètre** : Architecture, Qualité, Sécurité

---

## 1. sylius-einvoice-transmission-plugin

### Architecture ✅

- **Ports/adapters** exemplaire : `InvoiceTransmitterInterface` (port), `NullInvoiceTransmitter` (défaut), adaptateurs concrets libres d'implémentation.
- **DIP** : `InvoiceTransmissionHandler` orchestre via des interfaces (`TransmissionJournalInterface`, `InvoiceTransmitterInterface`).
- `DbalTransmissionJournal` utilise DBAL directement (pas l'Entity Manager) pour gérer proprement les `UniqueConstraintViolationException` sans fermer l'EM — décision documentée et justifiée.
- `InvoiceGeneratedListener` attrape toutes les exceptions pour ne jamais bloquer la génération de facture — bon choix défensif.
- Entité `InvoiceTransmission` : modèle de lecture, écrit via DBAL.

### Qualité ✅

- 21 fichiers source, 15 fichiers de tests (unit + integration).
- Test doubles propres : `RecordingInvoiceTransmitter`, `InMemoryTransmissionJournal`.
- `declare(strict_types=1)` partout.
- Classes `final readonly` où approprié.
- Dépendances minimales : `symfony/clock`, `sylius-facturx-plugin ^1.0`.

### Sécurité ✅

- Requêtes paramétrées (DBAL avec placeholders).
- Pas de donnée sensible exposée.
- `composer audit` : 0 advisory (2 abandoned transitifs : `behat/transliterator`, `php-http/message-factory` — transitifs Sylius, non contrôlables).

### Constats

| Sévérité | Constat |
|----------|---------|
| **Info** | Les 2 dépendances abandonnées sont transitives (Sylius). Aucune action requise. |

---

## 2. sylius-erp-flatfile-plugin

### Architecture ✅

- Export (commandes → CSV) et Import (prix/stock depuis CSV) bien séparés.
- `AtomicFileDepositor` : écriture dans `.incoming/` puis rename atomique ; nettoyage en cas d'échec.
- Interfaces claires : `FileDepositor`, `PendingOrderEvents`, `ExportJournal`, `CatalogLookup`, `CatalogUpdates`, `ImportJournal`.
- `OrderExporter` : export par batch avec journal, streaming CSV via Generator.

### Qualité ✅

- 31 fichiers source, 17 fichiers de tests (unit + integration) avec doubles in-memory.
- `BatchImporter` à 190 lignes : proche de la limite (200), mais justifié par la logique d'import (prix all-or-nothing, stock ligne par ligne, 12 codes de rejet, validation UTF-8/BOM/troncature, détection de doublons, idempotence par checksum).
- `declare(strict_types=1)` partout.
- Dépendances : `webhook-outbox-plugin ^1.0`, `symfony/clock`, `symfony/console`.

### Sécurité ✅

- Validation des fichiers CSV : UTF-8, BOM, troncature, doublons.
- Import atomique (all-or-nothing pour les prix).
- Pas d'injection possible via les données CSV (requêtes paramétrées).

### Constats

| Sévérité | Constat |
|----------|---------|
| **Mineur** | `BatchImporter` à 190 lignes — surveiller si des ajouts futurs le font dépasser 200 lignes. Envisager l'extraction de la validation UTF-8/BOM en classe dédiée si le fichier grandit. |

---

## 3. sylius-b2b-predictive-analytics-plugin

### Architecture ✅

- Interface `PredictiveReorderCalculatorInterface` (port), implémentation `DbalPredictiveReorderCalculator`.
- Value Objects immutables : `ReorderPrediction` (`final readonly`, named constructors `predicted()` / `insufficientHistory()`), `PredictionFailureReason` (enum).
- Fonctions pures extraites : `IntervalCalculator` (calcul d'intervalles et de médiane) — testable indépendamment.
- `ReorderAlertProvider` : cloisonné par entreprise, filtre par variantes actives, exclut les alertes masquées (DQL avec LEFT JOIN).
- Réutilisation de `CountedOrderSql` de company-insights pour la définition des commandes comptées.

### Qualité ✅

- 18 fichiers source, 15 fichiers de tests (unit + functional).
- `declare(strict_types=1)` partout.
- `DbalPredictiveReorderCalculator` : utilise DBAL pour les requêtes de lecture, EM uniquement pour la persistance des StoredPrediction.
- Contrôleurs `final readonly` avec CSRF, vérification d'authentification et d'appartenance à l'entreprise.
- Injection de `ClockInterface` pour la testabilité.

### Sécurité ✅

- CSRF vérifié sur les deux contrôleurs shop (`DismissPredictionController`, `PrepareCartController`).
- Authentification vérifiée : `ShopUserInterface` + `CompanyMemberInterface` + Company.
- Requêtes SQL paramétrées.
- Pas de donnée sensible exposée.

### Constats

| Sévérité | Constat |
|----------|---------|
| **Info** | `PrepareCartController` (145 lignes) gère le rattachement des entités détachées (canal, client) — complexité inhérente au contexte Sylius, bien commentée. |

---

## 4. sylius-container-fill-plugin

### Architecture ✅

- Plugin le plus riche (86 fichiers source) : architecture bien structurée en modules.
- `Planner/` : algorithme pur (pas d'I/O) — `SlotWeightPlanner` (first-fit decreasing), `LayerPlanner` (mode couches). Testés unitairement avec des fixtures dédiées (`PlannerFixtures`).
- `Rule/` : `RuleEvaluator` et `SuggestionFinder` (purs), `CartFillChecker` (service orchestrateur avec cache par requête).
- `Logistics/` : `VariantLogisticsProvider` charge en 2 requêtes, `VariantLogistics` résout les unités. `EntityId` pour le typage sûr.
- Checkout : 4 points de garde (subscriber, validateur, workflow guard, snapshot listener) — aucun ne modifie le groupe de validation `sylius` (les paniers partiels peuvent sauvegarder).
- CSV import/export atomique.
- Bridge quick-order via compiler pass conditionnel.

### Qualité ✅

- 86 fichiers source, 29 fichiers de tests (unit + integration + functional).
- Traductions en 7 langues (en, fr, de, es, it, nl, pl) vérifiées par `TranslationsTest`.
- `NoSkippedTestsTest` : aucun test marqué `skip`.
- `declare(strict_types=1)` partout.
- CLAUDE.md très détaillé (architecture, commandes, règles).

### Sécurité ✅

- Validation des entrées : `CaseMultipleValidator`, `OrderContainerFillSatisfiedValidator`, `ValidContainerRuleValidator`, `ValidContainerTypeValidator`.
- Pas d'injection SQL (DBAL paramétré).
- Accessibilité du gauge : `role="meter"`, `aria-valuetext`, `prefers-reduced-motion`.

### Constats

| Sévérité | Constat |
|----------|---------|
| **Mineur** | `SlotWeightPlanner` (254 lignes) dépasse la limite de 200 lignes/classe. L'algorithme bin-packing justifie la taille, mais une extraction de `packLine()` + helpers en classe interne pourrait améliorer la lisibilité. |
| **Info** | `ContainerFillDemoFixture` et `ContainerTypePresetFixture` sont idempotentes — bonne pratique. |

---

## 5. sylius-channel-access-plugin

### Architecture ✅

- Plugin compact et bien découpé (20 fichiers source).
- Interface `AccessModeResolverInterface` (5 méthodes, contrat gelé pour 0.x) : consommée par d'autres plugins.
- `AccessMode` enum avec deny-by-default (valeur inconnue → `closed`).
- `PatternRequestMatcher` : ne matche jamais un chemin contenant `..`, `//` ou NUL — protection path traversal.
- `SeePricesVoter` : voter Symfony standard, décision déléguée au resolver.
- `PriceGuardOrderModifier` : décorateur de `sylius.modifier.order` — empêche l'ajout au panier sur canal fermé.
- `PriceHidingNormalizer` (priorité 65, avant Sylius à 64) : supprime les champs prix de l'API.

### Qualité ✅

- 20 fichiers source, 21 fichiers de tests (unit + integration + functional).
- Ratio tests/source > 1 : excellente couverture.
- `declare(strict_types=1)` partout.
- PHP ^8.3, Sylius ^2.3 (pas de rétrocompatibilité 2.2 — choix assumé).
- `PrivateCacheListener` : `private, no-store` sur toute réponse non-open — bonne hygiène cache.

### Sécurité ✅

- Deny-by-default : canal inconnu → closed.
- Protection path traversal dans `PatternRequestMatcher`.
- `VisitorAccessListener` (priorité 6, après le firewall) : 302 login pour les pages, 401 JSON pour l'API.
- `ClosedChannelExceptionListener` : même refus au lieu d'un 404/403 (pas de fuite d'information).
- Cache privé forcé sur les réponses non-open.
- `RestrictionOverlapPass` : vérifie au compile-time que les restrictions ne se chevauchent pas.

### Constats

| Sévérité | Constat |
|----------|---------|
| Aucun | Plugin exemplaire en termes de sécurité et de design. |

---

## Synthèse du batch 3

| Plugin | Archi | Qualité | Sécu | Constats |
|--------|-------|---------|------|----------|
| einvoice-transmission | ✅ | ✅ | ✅ | 0 critique, 0 majeur, 0 mineur |
| erp-flatfile | ✅ | ✅ | ✅ | 0 critique, 0 majeur, 1 mineur |
| predictive-analytics | ✅ | ✅ | ✅ | 0 critique, 0 majeur, 0 mineur |
| container-fill | ✅ | ✅ | ✅ | 0 critique, 0 majeur, 1 mineur |
| channel-access | ✅ | ✅ | ✅ | 0 critique, 0 majeur, 0 mineur |

**Constats mineurs (2)** :
1. `erp-flatfile` : `BatchImporter` à 190 lignes — surveiller la croissance.
2. `container-fill` : `SlotWeightPlanner` à 254 lignes — extraction envisageable.

**Aucun constat critique ni majeur.** Les 5 plugins respectent les principes SOLID, DIP, KISS/DRY/YAGNI, et les bonnes pratiques de sécurité (CSRF, parameterized queries, deny-by-default, input validation).
