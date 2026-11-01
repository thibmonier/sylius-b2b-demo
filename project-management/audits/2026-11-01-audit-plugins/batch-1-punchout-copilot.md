# Audit Batch 1 — Plugins critiques (1/5)

**Date** : 2026-11-01
**Plugins** : punchout-cxml, company-accounts, b2b-sales-copilot, facturx, quick-order
**Référentiel** : architecture (SOLID, hexagonal, DIP), qualité (KISS, naming, complexité), sécurité (OWASP, dépendances), testing

---

## Vue d'ensemble

| Plugin | Fichiers src | Fichiers tests | Lignes src | Ratio tests/src | PHPStan | README |
|--------|-------------|---------------|-----------|----------------|---------|--------|
| punchout-cxml | 105 | 46 | 7 696 | 43,8 % | max | ✅ EN+FR |
| company-accounts | 64 | 31 | 3 723 | 48,4 % | max | ✅ EN+FR |
| b2b-sales-copilot | 17 | 21 | 971 | 123,5 % | max | ✅ EN+FR |
| facturx | 48 | 28 | 3 644 | 58,3 % | max | ✅ EN+FR |
| quick-order | 49 | 34 | 3 624 | 69,4 % | max | ✅ EN+FR |

---

## 1. sylius-punchout-cxml-plugin

### Architecture ✅

- Bonne séparation en 7 domaines : `Buyer/`, `Cart/`, `Classification/`, `Cxml/`, `Security/`, `Session/`, `Transfer/`
- 5 interfaces dans le domaine : `BuyerInterface`, `ItemClassifierInterface`, `CompanyDirectoryInterface`, `MessageLogInterface`, `PunchoutSessionInterface`
- DIP respecté : les services dépendent des interfaces

### Qualité ⚠️ Majeur

| Fichier | Lignes | Méthodes | Constat |
|---------|--------|----------|---------|
| `Form/Type/Admin/BuyerType.php` | 368 | 14 | **> 200 lignes** — SRP à vérifier (form type complexe) |
| `Buyer/Buyer.php` | 291 | — | **> 200 lignes** — Entité riche, acceptable si uniquement getters/setters |
| `Controller/CxmlEndpointAction.php` | 281 | 7 | **> 200 lignes** — Invocation d'action unique, envisager extraction de sous-services |
| `Session/PunchoutSession.php` | 235 | — | **> 200 lignes** — Entité riche |
| `Order/OrderBuilder.php` | 200 | — | À la limite |

### Sécurité ⚠️ Mineur

- **DIP violation dans le contrôleur** : instanciation directe de `new Session(new MockArraySessionStorage())` dans `CxmlEndpointAction.php:98-99` — devrait être injecté ou délégué à un factory
- Hachage des mots de passe : ✅ colonnes `password_hash` et `start_token_hash`
- Pas de secrets en dur, pas de superglobales

### Testing

- Ratio 43,8 % — en dessous de la cible 80 % en nombre de fichiers
- Couverture fonctionnelle correcte sur les scénarios cXML

### Constats

| # | Sévérité | Constat | Action proposée |
|---|----------|---------|----------------|
| P-01 | Majeur | `BuyerType.php` 368 lignes | Extraire en sous-form types (credentials, mapping, classification) |
| P-02 | Majeur | `CxmlEndpointAction.php` 281 lignes + instanciation directe Session | Extraire handler cXML, injecter session via factory |
| P-03 | Mineur | `Buyer.php` 291 lignes | Acceptable (entité Doctrine), pas d'action immédiate |
| P-04 | Info | Ratio tests 43,8 % | Augmenter progressivement via tests de régression |

---

## 2. sylius-company-accounts-plugin

### Architecture ✅

- Organisation en couches : `Entity/`, `Repository/`, `Api/Shop/`, `Bridge/`, `Security/`, `EventListener/`, `Controller/Shop/`
- Security voters correctement implémentés (`OrderVoter`, `CompanyVoter`)
- `CurrentMemberResolver` encapsule la résolution du membre connecté

### Qualité ⚠️ Mineur

| Fichier | Lignes | Constat |
|---------|--------|---------|
| `EventListener/CompanyChangeListener.php` | 250 | **> 200 lignes** — listener multi-responsabilités |
| `Controller/Shop/CompanyAddressController.php` | 185 | OK |
| Reste des fichiers | < 150 | ✅ |

### Sécurité ✅

- Voters d'autorisation en place
- Pas de secrets en dur
- Validation des permissions sur chaque requête

### Testing

- Ratio 48,4 % — en dessous de la cible

### Constats

| # | Sévérité | Constat | Action proposée |
|---|----------|---------|----------------|
| CA-01 | Mineur | `CompanyChangeListener.php` 250 lignes | Découper par événement (création, mise à jour, suppression) |
| CA-02 | Info | Ratio tests 48,4 % | Augmenter couverture sur les scénarios de changement de société |

---

## 3. sylius-b2b-sales-copilot-plugin

### Architecture ✅ Exemplaire

- Pattern ports/adapters parfaitement appliqué :
  - Port : `Model/CopilotModelInterface`
  - Adapters : `Infrastructure/Replay/ReplayCopilotAdapter`, `Infrastructure/SymfonyAi/SymfonyAiCopilotAdapter`
- Domaines spécialisés : `Guard/`, `Audit/`, `RateLimit/`
- Plugin le plus petit (971 lignes) et le mieux structuré

### Qualité ⚠️ Mineur

| Fichier | Lignes | Constat |
|---------|--------|---------|
| `Infrastructure/Replay/ReplayCopilotAdapter.php` | 261 | **> 200 lignes** — contient les réponses de replay (données de test) |
| Reste des fichiers | < 150 | ✅ |

### Sécurité ✅

- Rate limiting intégré
- Guard pattern pour les garde-fous
- Audit logging

### Testing ✅

- Ratio 123,5 % — excellent, plus de fichiers de tests que de fichiers source

### Constats

| # | Sévérité | Constat | Action proposée |
|---|----------|---------|----------------|
| CO-01 | Mineur | `ReplayCopilotAdapter.php` 261 lignes | Extraire les données de replay dans un fichier de fixtures séparé |

---

## 4. sylius-facturx-plugin

### Architecture ✅

- Organisation par domaine : `Mapper/`, `Xml/`, `Pdf/Font/`, `Entity/`, `DependencyInjection/`
- Séparation claire entre la génération PDF et le mapping XML Factur-X

### Qualité ⚠️ Majeur

| Fichier | Lignes | Méthodes | Constat |
|---------|--------|----------|---------|
| `Mapper/FacturXInvoiceMapper.php` | 348 | 12 (2 pub + 10 priv) | **> 200 lignes** — méthodes privées de mapping à extraire |
| `Pdf/Font/FontWidthsNormalizer.php` | 329 | — | **> 200 lignes** — données de largeur de polices (acceptable si data-heavy) |
| `Entity/ChannelSettings.php` | 294 | — | **> 200 lignes** — entité avec beaucoup de propriétés |
| `Xml/CiiXmlWriter.php` | 236 | — | **> 200 lignes** — écriture XML structurée |
| `Pdf/Font/TrueTypeMetrics.php` | 211 | — | **> 200 lignes** — parsing de métriques de polices |

### Sécurité ⚠️ Majeur

- **Dépendances vulnérables** (test app) :
  - 6 advisories Sylius (sylius/sylius <2.3.2) — à résoudre par mise à jour Sylius
  - CVE symfony/expression-language (CVE-2026-109449)
  - CVE symfony/mime (CVE-2026-45070)
  - 2 packages abandonnés
- Note : ces vulnérabilités sont dans les dépendances du test app, pas nécessairement dans la production

### Testing

- Ratio 58,3 %

### Constats

| # | Sévérité | Constat | Action proposée |
|---|----------|---------|----------------|
| FX-01 | Majeur | `FacturXInvoiceMapper.php` 348 lignes, 10 méthodes privées | Extraire en sous-mappers (Party, Items, Payment, Dates) |
| FX-02 | Majeur | Vulnérabilités dépendances test app | Mettre à jour sylius/sylius ≥2.3.2, symfony/* |
| FX-03 | Mineur | `FontWidthsNormalizer.php` 329 lignes | Acceptable (données de police), pas d'action immédiate |
| FX-04 | Mineur | `ChannelSettings.php` 294 lignes | Acceptable (entité Doctrine avec beaucoup de champs) |
| FX-05 | Info | Ratio tests 58,3 % | Augmenter couverture sur le mapping XML |

---

## 5. sylius-quick-order-plugin

### Architecture ✅

- Organisation en domaines : `Adder/`, `Input/`, `Matrix/`, `Quantity/`
- API Platform correctement intégré (`Api/BulkAddItemsProcessor`)

### Qualité 🔴 Critique

| Fichier | Lignes | Méthodes | Constat |
|---------|--------|----------|---------|
| `Controller/Shop/QuickOrderController.php` | **415** | **22** | 🔴 **Très au-dessus de 200 lignes, 22 méthodes — violation SRP sévère** |
| `Adder/BulkCartAdder.php` | 355 | — | **> 200 lignes** — logique d'ajout complexe |
| `Twig/Component/MatrixOrderComponent.php` | 340 | — | **> 200 lignes** — composant Twig Live complexe |

### Sécurité ✅

- Rate limiter intégré dans le contrôleur
- Token de panier validé via API Platform
- `PasteParser` gère correctement l'entrée utilisateur

### Testing

- Ratio 69,4 % — proche de la cible

### Constats

| # | Sévérité | Constat | Action proposée |
|---|----------|---------|----------------|
| QO-01 | 🔴 Critique | `QuickOrderController.php` 415 lignes, 22 méthodes | **Refactoring prioritaire** : extraire en actions invokables (SearchAction, AddAction, BulkAddAction, PasteAction, CsvUploadAction…) |
| QO-02 | Majeur | `BulkCartAdder.php` 355 lignes | Extraire la validation et le report d'erreurs en services dédiés |
| QO-03 | Majeur | `MatrixOrderComponent.php` 340 lignes | Extraire la logique de calcul de la matrice en service |
| QO-04 | Info | Ratio tests 69,4 % | Bon niveau, à maintenir |

---

## Synthèse Batch 1

### Par sévérité

| Sévérité | Nombre | Plugins concernés |
|----------|--------|-------------------|
| 🔴 Critique | 1 | quick-order (QuickOrderController 415 lignes / 22 méthodes) |
| ⚠️ Majeur | 5 | punchout (2), facturx (2), quick-order (1→ déjà compté en critique, +2 majeurs) |
| ℹ️ Mineur | 4 | punchout (1), company-accounts (1), copilot (1), facturx (1) |
| ℹ️ Info | 4 | punchout (1), company-accounts (1), facturx (1), quick-order (1) |

### Points forts transversaux

- ✅ PHPStan level max sur les 5 plugins
- ✅ README EN + FR présents partout
- ✅ Pas de secrets en dur
- ✅ Bonne séparation en domaines
- ✅ Interfaces dans le domaine (DIP globalement respecté)
- ✅ copilot : architecture ports/adapters exemplaire

### Axes d'amélioration transversaux

1. **Taille des fichiers** : 12 fichiers dépassent les 200 lignes sur les 5 plugins
2. **Ratios de tests** : punchout (43,8 %) et company-accounts (48,4 %) sont en dessous de 70 %
3. **Dépendances** : facturx a des vulnérabilités dans le test app (sylius <2.3.2)
