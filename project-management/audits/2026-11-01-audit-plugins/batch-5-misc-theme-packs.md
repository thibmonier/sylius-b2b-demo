# Audit Batch 5 — Misc plugins, thème et packs

**Date** : 2026-11-01
**Périmètre** : 10 dépôts (8 plugins + 1 thème + 2 packs)
**Sprint** : 44 (US-255 lot 2)

---

## Résumé exécutif

| Dépôt | Src files | Test files | LoC src | LoC test | Ratio test/src | Vulns | Sévérité max |
|-------|-----------|------------|---------|----------|----------------|-------|--------------|
| sylius-order-hold-plugin | 32 | 23 | 1 550 | 2 442 | 157,5 % | 0 | mineur |
| sylius-registration-approval-plugin | 32 | 24 | 1 407 | 2 222 | 157,9 % | 0 | info |
| sylius-tier-pricing-plugin | 49 | 30 | 3 538 | 3 443 | 97,3 % | 0 | majeur |
| sylius-timeline-plugin | 31 | 19 | 2 370 | 2 391 | 100,8 % | 0 | info |
| sylius-trade-credit-plugin | 73 | 23 | 5 031 | 3 442 | 68,4 % | 0 | critique |
| sylius-webhook-outbox-plugin | 68 | 26 | 4 979 | 3 350 | 67,2 % | 0 | majeur |
| sylius-withdrawal-plugin | 27 | 11 | 1 598 | 912 | 57,0 % | 0 | majeur |
| sylius-industry-trade-theme | — | — | — (Twig/JS) | — | — | 1 | majeur |
| sylius-pack-ai-headless | 0 (meta) | 3 (gestures) | 0 | — | — | 0 | info |
| sylius-pack-compliance-tooling | 0 (meta) | 3 (gestures) | 0 | — | — | 0 | info |

**Constats globaux** :
- 0 vulnérabilité directe dans les plugins ; 1 CVE transitive dans le thème (symfony/mime)
- Architecture solide : tous les plugins exposent des interfaces pour le DIP
- Le ratio test/src varie fortement : de 57 % (withdrawal) à 158 % (order-hold, registration-approval)
- Un fichier dépasse 500 lignes (`TradeCreditReserver`), deux autres dépassent 300 lignes

---

## Constats détaillés par dépôt

### 1. sylius-order-hold-plugin

**Architecture** : ✅ Bonne
- Organisation claire : `AccountOwner/`, `PaymentEntry/`, `PaymentMethod/`, `UnpaidOrder/`, `Validator/`, `StateMachine/`
- Interfaces pour tous les services critiques (`UnpaidOrderCancellationExclusionInterface`, `PaymentMethodEligibilityCheckerInterface`, `PaymentEntryBlockerInterface`)
- Pattern composite pour les checkers

**Qualité** : ✅ Bonne
- Plus gros fichier : `HoldAwareUnpaidOrdersStateUpdater` (220 lignes) — justifié par la pagination keyset et la logique de décision multi-critère
- 32 src / 23 tests, ratio 157,5 %

**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| 1 | mineur | `HoldAwareUnpaidOrdersStateUpdater` (220L) dépasse le guideline 200L mais reste cohérent |

---

### 2. sylius-registration-approval-plugin

**Architecture** : ✅ Excellente
- Séparation par couches : `Approval/`, `Controller/Admin/`, `Controller/Shop/`, `Entity/`, `EventListener/`, `Mailer/`, `Validator/`
- Tous les fichiers src sous 91 lignes — aucun fichier volumineux

**Qualité** : ✅ Excellente
- Ratio test/src de 157,9 % — le plus haut de ce batch
- 24 fichiers de test couvrant Unit, Integration et Functional

**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| — | info | Plugin exemplaire, aucun constat négatif |

---

### 3. sylius-tier-pricing-plugin

**Architecture** : ✅ Bonne
- 9 interfaces (Repository, Entity, Pricing, Factory, OrderProcessing)
- 20 répertoires src bien organisés (Api, Cache, Command, Controller, Csv, Entity, Factory, Fixture, Form, Menu, Migrations, OrderProcessing, Pricing, Repository, Twig, Validator)

**Qualité** : ⚠️ Attention
- `TierPriceRuleCsvImporter` : 307 lignes, 7 méthodes — l'import CSV combine parsing, validation, résolution de références (channel, taxon, group, variant) et persistance
- `TierPriceRule` entity : 219 lignes — acceptable pour une entité riche
- `TierPricingExtension` (Twig) : 209 lignes
- Ratio test/src : 97,3 % — correct

**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| 1 | majeur | `TierPriceRuleCsvImporter` (307L) mélange parsing CSV, validation et résolution de références — candidat à un refactoring SRP (extraire un `CsvRowResolver` et un `CsvRowValidator`) |
| 2 | mineur | `TierPricingExtension` (Twig, 209L) contient beaucoup de logique de résolution de prix — candidat à un refactoring vers un service dédié |

---

### 4. sylius-timeline-plugin

**Architecture** : ✅ Excellente
- 10 interfaces couvrant Entity, Repository, Factory, Recorder, Backfill, GDPR
- Module GDPR dédié (`PersonalDataEntryFinderInterface`, `CustomerTimelineEraserInterface`) — bonne conformité
- Séparation `Recorder/`, `Backfill/`, `Gdpr/`, `Factory/`

**Qualité** : ✅ Bonne
- Plus gros fichier : `TimelineEntry` entity (211L) — acceptable
- `TimelineNoteController` (205L) — limite haute mais cohérent
- Ratio test/src : 100,8 % — excellent

**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| — | info | Plugin bien structuré avec support GDPR natif |

---

### 5. sylius-trade-credit-plugin

**Architecture** : ✅ Très bonne
- Le plus riche du batch : 31 répertoires src, 73 fichiers
- 10+ interfaces (Repository, Entity, Credit, Account)
- Modules bien découpés : Account, Ageing, Credit, FacturX, Import, OrderHold, Payment, Reminder, Reservation, Statement, Terms
- Point d'extension : `ExternalCreditProviderInterface` avec adaptateur alias

**Qualité** : 🔴 Critique sur un fichier
- **`TradeCreditReserver` : 507 lignes, 18 méthodes** — concentre la réservation de crédit, la vérification de paiement, la gestion des termes, le ledger DBAL et la concurrence transactionnelle
- `OrderPaymentTermsRepository` : 256L, `TradeCreditFixture` : 254L
- Ratio test/src : 68,4 % — le ratio le plus bas pour un plugin complexe

**Sécurité** : ✅ 0 vulnérabilités
- Utilisation de requêtes paramétrées DBAL (ParameterType, Types)
- UUID pour les identifiants

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| 1 | critique | `TradeCreditReserver` (507L) dépasse largement le guideline 200L — combiner réservation, vérification paiement, gestion termes et ledger DBAL viole SRP. Extraire : `ReservationValidator`, `LedgerWriter`, `PaymentTermsResolver` |
| 2 | majeur | Ratio test/src de 68,4 % pour le plugin le plus complexe du lot — besoin de tests supplémentaires, surtout sur les cas limites de concurrence et d'expiration |
| 3 | mineur | `TradeCreditFixture` (254L) — acceptable pour une fixture riche |

---

### 6. sylius-webhook-outbox-plugin

**Architecture** : ✅ Très bonne
- 24 répertoires src, 68 fichiers
- 11 interfaces (Outbox, Capture, Repository, Entity, Delivery, EventType)
- Bonne séparation : Capture/Detector, Delivery, Outbox, Payload, Signature, Http, Maintenance
- Pattern `VarDumper` pour le debug — bon DX

**Qualité** : ⚠️ Attention
- `DeliveryClaimer` : 310L, 15 méthodes — gère le claim concurrent (SELECT FOR UPDATE / SKIP LOCKED), le retry backoff, le fallback sans SKIP LOCKED
- `WebhookDispatcher` : 205L — limite acceptable
- Ratio test/src : 67,2 %

**Sécurité** : ✅ 0 vulnérabilités
- Signature HMAC pour les webhooks
- SELECT FOR UPDATE avec ConflictResolutionMode pour la concurrence

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| 1 | majeur | `DeliveryClaimer` (310L) — la logique de claim concurrent, retry et fallback sans SKIP LOCKED pourrait être scindée en `ConcurrentClaimer` et `RetryScheduler` |
| 2 | majeur | Ratio test/src de 67,2 % — les chemins de fallback (DB sans SKIP LOCKED) méritent des tests dédiés |

---

### 7. sylius-withdrawal-plugin

**Architecture** : ✅ Bonne
- 16 répertoires src, 27 fichiers
- 8 interfaces (Repository, Entity, Generator, Mailer, Order, Submitter)
- Module `RateLimit/` dédié

**Qualité** : ⚠️ Attention
- `WithdrawalRequest` entity : 236L — beaucoup de propriétés
- `WithdrawalController` : 210L
- Ratio test/src : **57,0 %** — le plus bas du batch

**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| 1 | majeur | Ratio test/src de 57,0 % — nettement sous le seuil de 80 %, besoin de tests supplémentaires sur les validations, le rate limiting et le workflow de soumission |
| 2 | mineur | `WithdrawalRequest` entity (236L) — riche mais acceptable pour une entité avec workflow |
| 3 | mineur | `WithdrawalController` (210L) — limite haute |

---

### 8. sylius-industry-trade-theme

**Architecture** : ✅ Conforme (thème Sylius)
- Type `sylius-theme`, pas de code PHP
- 1 790 templates Twig, 253 fichiers d'assets (JS/CSS/SCSS)
- Dépendances minimales : PHP, sylius/theme-bundle, sylius/sylius

**Qualité** : ✅ Acceptable pour un thème

**Sécurité** : ⚠️ Vulnérabilité transitive
- **1 CVE : symfony/mime PKSA-wtxr-p26d-nn42 (CVE-2026-45070)** — Email Header Injection via Non-Token Characters in Mime Parameter Names
- Vient de la dépendance sylius/sylius qui tire symfony/mime

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| 1 | majeur | CVE-2026-45070 sur symfony/mime — mettre à jour la contrainte sylius/sylius ou attendre un patch upstream |

---

### 9. sylius-pack-ai-headless (metapackage)

**Architecture** : ✅ Conforme
- Type `metapackage`, aucun code applicatif
- Regroupe : agentic-feeds, ai-catalog-copilot, b2b-mcp, webhook-outbox
- Tests de gestures (installation/recette Flex) présents

**Qualité** : ✅ Conforme
**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| — | info | Metapackage conforme, tests de recette présents |

---

### 10. sylius-pack-compliance-tooling (metapackage)

**Architecture** : ✅ Conforme
- Type `metapackage`, aucun code applicatif
- Regroupe : audit-log, bulk-actions, command-palette, facturx, gpsr, timeline
- Tests de gestures présents

**Qualité** : ✅ Conforme
**Sécurité** : ✅ 0 vulnérabilités

**Constats** :
| # | Sévérité | Constat |
|---|----------|---------|
| — | info | Metapackage conforme, tests de recette présents |

---

## Synthèse des constats

### Par sévérité

| Sévérité | Nb | Constats |
|----------|----|----------|
| 🔴 Critique | 1 | `TradeCreditReserver` 507L (SRP) |
| 🟠 Majeur | 6 | `TierPriceRuleCsvImporter` 307L (SRP), `DeliveryClaimer` 310L (SRP), ratio test/src withdrawal 57 %, ratio test/src trade-credit 68,4 %, ratio test/src webhook-outbox 67,2 %, CVE symfony/mime dans le thème |
| 🟡 Mineur | 5 | `HoldAwareUnpaidOrdersStateUpdater` 220L, `TierPricingExtension` 209L, `TradeCreditFixture` 254L, `WithdrawalRequest` 236L, `WithdrawalController` 210L |
| ℹ️ Info | 4 | registration-approval exemplaire, timeline bien structuré, 2 metapackages conformes |

### Points forts transversaux

1. **DIP systématique** : chaque plugin expose des interfaces pour ses services et repositories
2. **0 vulnérabilité directe** dans les plugins (seul un CVE transitif dans le thème)
3. **Requêtes paramétrées** : aucune concaténation SQL détectée, usage de DBAL ParameterType/Types
4. **UUID** utilisés dans trade-credit et webhook-outbox pour les identifiants non-prédictibles
5. **GDPR** : timeline-plugin intègre un module dédié d'effacement des données personnelles

### Axes d'amélioration prioritaires

1. **Refactoring SRP** : `TradeCreditReserver` (507L), `DeliveryClaimer` (310L), `TierPriceRuleCsvImporter` (307L)
2. **Couverture de test** : withdrawal (57 %), webhook-outbox (67,2 %), trade-credit (68,4 %) — viser ≥ 80 %
3. **Mise à jour dépendances** : symfony/mime CVE dans le thème

### Stories de correction proposées (backlog)

| Story proposée | Plugins concernés | Points estimés |
|----------------|-------------------|----------------|
| Refactoring `TradeCreditReserver` — extraire `ReservationValidator`, `LedgerWriter`, `PaymentTermsResolver` | trade-credit | 5 |
| Refactoring `DeliveryClaimer` — extraire `ConcurrentClaimer`, `RetryScheduler` | webhook-outbox | 3 |
| Refactoring `TierPriceRuleCsvImporter` — extraire `CsvRowResolver`, `CsvRowValidator` | tier-pricing | 3 |
| Améliorer la couverture de test du plugin withdrawal (objectif ≥ 80 %) | withdrawal | 3 |
| Améliorer la couverture de test des chemins concurrents et fallback | webhook-outbox, trade-credit | 5 |
| Mettre à jour les contraintes de dépendance pour résoudre CVE symfony/mime | industry-trade-theme | 1 |
