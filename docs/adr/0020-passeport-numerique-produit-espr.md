# ADR-0020 — Passeport numérique de produit (DPP) : modèle de données préparatoire et formulation prudente

- **Statut** : Proposé (2026-10-28)
- **Contexte projet** : sylius-b2b-demo, sprint 34, spike US-229 (3 points, borné à 6 h). Décision de Thibaut du 2026-10-28 : « DPP maintenant, ça donnera une base ». MVP planifié au sprint 36 : US-230, US-231, US-232, US-233 (14 points).

## Contexte

Le règlement (UE) 2024/1781 (ESPR — Ecodesign for Sustainable Products Regulation) impose qu'à terme, certains produits mis sur le marché de l'UE disposent d'un **passeport numérique de produit (DPP)** contenant des informations sur leur durabilité, leur réparabilité, leur recyclabilité et leur origine. Le registre européen DPP est opérationnel depuis le 20 juillet 2026.

**Ce qui est fixé** (consulté le 2026-10-28) :
- Le règlement (UE) 2024/1781 est publié et en vigueur ([EUR-Lex](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=fr)).
- Le registre DPP européen est opérationnel depuis le 20 juillet 2026 ([Actualité Commission](https://single-market-economy.ec.europa.eu/news/digital-product-passport-registry-now-live-2026-07-20_en)).
- Le règlement batteries (UE) 2023/1542 est publié et en vigueur.
- Le standard GS1 Digital Link 1.7.0 affiche le statut "Ratified Aug 2026" sur sa page de référence.

**Ce qui n'est PAS encore fixé** :
- **Aucun acte délégué par catégorie de produits n'est adopté à ce jour.** Ces actes précisent les données requises, les calendriers d'application et les modalités techniques pour chaque catégorie (batteries, textiles, fer/acier, etc.).
- La date « 18 février 2027 » pour les batteries est fixée par le règlement (UE) 2023/1542, art. 77 (passeport électronique obligatoire pour batteries LMT, industrielles > 2 kWh, véhicules électriques). Les champs de données obligatoires au 18 février 2027 ne sont pas tous finalisés (certains « en attente d'adoption de l'Omnibus »).
- Les normes harmonisées CEN/CENELEC JTC 24 (6 attendues en juillet 2026, 2 autres en novembre 2026) ne sont **pas encore publiées au Journal officiel**.
- Le calendrier par catégorie (fer/acier Q4 2026, textiles Q4 2027, mobilier 2028, etc.) est **indicatif** et « peut évoluer au fur et à mesure de l'avancement des travaux législatifs et techniques » (avertissement de la Commission).

**Décision de Thibaut** : développer **maintenant** un plugin open source (`tmonier/sylius-digital-product-passport-plugin`) fournissant une **base de données ouverte et versionnée**, sans promettre de conformité réglementaire tant que les actes délégués ne sont pas publiés. Le plugin doit permettre aux marchands d'anticiper l'obligation en structurant dès maintenant les données de durabilité de leurs produits.

## Recoupement avec Sylius Plus

**Vérification de non-recoupement effectuée le 2026-10-28** (détail hors dépôt public). Aucun recoupement identifié.

## Modèle de données minimal (US-230)

Le modèle de données retenu anticipe les exigences du règlement ESPR et des lignes directrices batteries, **tout en restant extensible** pour intégrer les futurs actes délégués.

### Entité `DigitalProductPassport`

| Champ | Type | Justification | Source |
|-------|------|---------------|--------|
| `id` | UUID | Identifiant interne | Standard |
| `productVariant` | Relation (1:1) | Lien avec `ProductVariant` Sylius | Architecture plugin |
| `productIdentifier` | GTIN-14 | Identifiant unique persistant | ESPR art. 10, GS1 Digital Link |
| `digitalLinkUri` | URI | URI GS1 Digital Link (QR code) | GS1 1.7.0, ESPR art. 10 |
| `originMaterials` | Texte | Origine des matériaux | ESPR, lignes directrices batteries |
| `recyclability` | Texte | Informations de recyclabilité | ESPR |
| `repairability` | Texte | Informations de réparabilité | ESPR |
| `spareParts` | Relation (ManyToMany) | Pièces détachées compatibles | EPIC-19, lien avec customer-asset-management |
| `version` | Integer | Version du modèle de données | Évolutivité |
| `createdAt` | DateTime | Date de création | Traçabilité |
| `updatedAt` | DateTime | Date de mise à jour | Traçabilité |

**Architecture** : entité Doctrine indépendante, liée à `ProductVariant` (relation 1:1 optionnelle). Le passeport peut exister ou non pour un produit donné.

### Extensibilité

Le modèle est **versionné** (`version`) pour permettre l'ajout de champs supplémentaires lors de la publication des actes délégués, sans migration destructive. Les champs actuels sont une **base minimale** anticipant les exigences communes (origine, recyclabilité, réparabilité).

## Identifiants et support de données (US-232)

### Standard retenu : GS1 Digital Link 1.7.0

- **URI canonique** : `https://id.gs1.org/01/{GTIN-14}?10={lot}&21={serial}`
- **GTIN-14** : GTIN-8, GTIN-12 et GTIN-13 complétés à 14 chiffres (padding).
- **Support physique** : QR Code standard (générateur : `endroid/qr-code`, déjà utilisé dans la démo).
- **Référence** : [GS1 Digital Link URI Syntax](https://ref.gs1.org/standards/digital-link/uri-syntax/).

Le QR code renvoie vers la page publique du DPP (US-231), accessible sans authentification.

## Page publique et accès (US-231)

- **Route** : `/dpp/{gtin}` ou `/dpp/{uuid}`.
- **Accès** : public, sans authentification (exigence ESPR art. 10 : les revendeurs doivent rendre le passeport accessible aux clients et clients potentiels).
- **Contenu** : affichage en lecture seule des données du passeport (origine, recyclabilité, réparabilité, pièces compatibles).
- **Mention obligatoire** : « Base de données préparatoire alignée sur le règlement ESPR (UE) 2024/1781. **Aucune promesse de conformité réglementaire** tant que les actes délégués par catégorie de produits ne sont pas publiés. »
- **Lien vers le texte officiel** : EUR-Lex (règlement ESPR).

## Pièces détachées compatibles (US-233)

Relation `ManyToMany` entre `DigitalProductPassport` et `Product` (pièces compatibles), affichée sur la page publique du DPP. Cette fonctionnalité s'appuie sur le plugin `customer-asset-management` (EPIC-15) pour la gestion des actifs clients et des pièces détachées.

## Formulation prudente (obligatoire)

### Ce qui DOIT figurer dans le README et la démo

| Contexte | Formulation |
|----------|-------------|
| Description du plugin | « Base de données ouverte et versionnée pour le passeport numérique de produit » |
| Portée | « Modèle de données préparatoire aligné sur le règlement ESPR (UE) 2024/1781 » |
| Calendrier | « En préparation de l'obligation prévue pour certaines catégories de produits (batteries : 18 février 2027, fixé par le règlement (UE) 2023/1542 ; autres catégories : calendrier indicatif de la Commission) » |
| Données | « Champs de données anticipés sur la base du règlement (origine, recyclabilité, réparabilité, etc.), susceptibles d'évoluer selon les actes délégués à venir » |
| Conformité | « **Aucune promesse de conformité réglementaire** tant que les actes délégués par catégorie de produits ne sont pas publiés » |

### Ce qui est INTERDIT

Les termes suivants sont **bannis** dans tout texte public (README, démo, templates, traductions) :
- « Conforme au règlement ESPR »
- « Certifié DPP »
- « Prêt pour février 2027 »
- « Répond aux exigences légales »
- « Validé par la Commission »
- « Conforme aux normes harmonisées »

**Contrôle (US-235)** : un test automatisé (`DppContentTest`) vérifiera l'absence de « conforme » et « certifié » dans :
- README du plugin
- Page de démo (`/dpp/{gtin}`)
- Traductions (`translations/messages.*.yaml`)
- Templates Twig (`templates/bundles/SyliusDigitalProductPassportPlugin/`)

## Décision proposée

1. **Modèle de données minimal** : entité `DigitalProductPassport` avec les champs ci-dessus (origine, recyclabilité, réparabilité, pièces compatibles), versionné pour extensibilité future.
2. **Identifiants** : GTIN-14 et GS1 Digital Link 1.7.0, QR Code standard.
3. **Page publique** : route `/dpp/{gtin}`, accessible sans authentification, avec mention de non-conformité.
4. **Formulation prudente obligatoire** : aucune promesse de conformité, test automatisé de contenu (US-235).
5. **Recoupement Sylius Plus** : aucun identifié.
6. **Publication** : plugin open source `tmonier/sylius-digital-product-passport-plugin`, dépôt durci comme US-209, README en français avec lien vers la démo et le profil de l'auteur.

## Stories découpées (MVP : sprint 36)

| Story | Contenu | Points |
|-------|---------|--------|
| [US-230](../../project-management/backlog/user-stories/US-230.md) | Squelette du plugin, entité `DigitalProductPassport`, migration, admin Sylius | 5 |
| [US-231](../../project-management/backlog/user-stories/US-231.md) | Page publique du DPP (`/dpp/{gtin}`), mention de non-conformité | 3 |
| [US-232](../../project-management/backlog/user-stories/US-232.md) | QR Code, GS1 Digital Link, génération et affichage | 3 |
| [US-233](../../project-management/backlog/user-stories/US-233.md) | Relation pièces compatibles, affichage sur la page DPP | 3 |
| [US-235](../../project-management/backlog/user-stories/US-235.md) | Fixtures, intégration démo, test de contenu (formulation prudente) | 3 |

**Total MVP** : 17 points (US-230 à US-233, US-235). US-234 (lien Factur-X, Could, 3 points) non planifiée.

## Questions ouvertes

1. **Périmètre des catégories** : faut-il limiter le modèle aux batteries (calendrier 2027), ou anticiper d'autres catégories (textiles, fer/acier) avec un modèle extensible ? → **Proposition : modèle extensible versionné, applicable à toutes catégories.**
2. **Intégration Factur-X** (US-234, Could) : faut-il prévoir dès maintenant l'interface pour le lien optionnel dans la facture électronique ? → **Proposition : reporter à une demande utilisateur.**
3. **Publication du plugin** : créer le dépôt dès le sprint 36 (MVP), ou attendre la finalisation de toutes les stories ? → **Proposition : création au sprint 36, tag v1.0.0 après US-235 (sprint 41).**
4. **Communication** : le DPP peut-il être mentionné dans les posts LinkedIn ? → **Proposition : oui, avec la formulation prudente (« base préparatoire, sans promesse de conformité »).**

## Conséquences

### Positives

- **Anticipation de la réglementation** : les marchands peuvent structurer dès maintenant leurs données de durabilité, sans attendre les actes délégués.
- **Modèle ouvert et versionné** : extensibilité future garantie, évolution sans migration destructive.
- **Aucune promesse de conformité** : transparence totale, pas de risque légal.
- **Standard GS1 ratifié** : identifiants pérennes et universels.
- **Aucun recoupement Sylius Plus** identifié.

### Négatives

- **Incertitude réglementaire** : les champs de données peuvent évoluer lors de la publication des actes délégués (migration nécessaire).
- **Complexité** : maintenance d'un modèle versionné, anticipation des besoins futurs.
- **Pas de certification** : le plugin ne garantit pas la conformité légale, risque de confusion si la communication n'est pas claire.

## Sources

- [Règlement (UE) 2024/1781 — ESPR](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=fr) (consulté le 2026-10-28)
- [Règlement (UE) 2023/1542 — Batteries](https://eur-lex.europa.eu/eli/reg/2023/1542/oj/eng) (consulté le 2026-10-28)
- [Commission européenne — Digital Product Passport](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en) (consulté le 2026-10-28)
- [Commission européenne — DPP Registry now live (20 juillet 2026)](https://single-market-economy.ec.europa.eu/news/digital-product-passport-registry-now-live-2026-07-20_en) (consulté le 2026-10-28)
- [Commission européenne — DPP Batteries](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport/batteries_en) (consulté le 2026-10-28)
- [Commission européenne — Guidance batteries (août 2026)](https://single-market-economy.ec.europa.eu/news/guidance-support-preparations-digital-batteries-passport-2026-08-21_en) (consulté le 2026-10-28)
- [GS1 Digital Link URI Syntax 1.7.0](https://ref.gs1.org/standards/digital-link/uri-syntax/) (consulté le 2026-10-28)

---

**Décision en attente de Thibaut** (2026-10-28).
