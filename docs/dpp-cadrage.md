# Note de cadrage : Passeport Numérique de Produit (DPP) — Spike US-229

**Date de rédaction** : 2026-10-28  
**Timebox** : 6 heures  
**Sources consultées** : 2026-10-28

---

## Glossaire

| Terme | Définition | Source |
|-------|------------|--------|
| **ESPR** | Ecodesign for Sustainable Products Regulation — Règlement (UE) 2024/1781 du Parlement européen et du Conseil établissant un cadre pour les exigences d'écoconception applicables aux produits durables | [EUR-Lex](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=fr) |
| **DPP** | Digital Product Passport / Passeport numérique de produit — Base de données électronique associée à un produit contenant des informations sur sa durabilité, sa réparabilité, sa recyclabilité et son origine | Règlement (UE) 2024/1781, art. 2 |
| **Acte délégué** | Acte adopté par la Commission européenne précisant les exigences techniques par catégorie de produits (données requises, calendrier d'application, etc.) | Règlement (UE) 2024/1781, art. 4 |
| **Registre DPP** | Système européen centralisé de gestion des passeports numériques de produits, opérationnel depuis le 20 juillet 2026 | [Commission européenne](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en) |
| **GS1 Digital Link** | Standard GS1 permettant d'encoder des identifiants de produits (GTIN, etc.) dans des URI HTTPS, utilisables via QR codes | [GS1.org](https://www.gs1.org/standards/gs1-digital-link), version 1.7.0 (août 2026) |
| **Identifiant unique persistant** | Code unique et permanent associé à un produit (ex : GTIN) permettant d'accéder au passeport via un support de données (QR code) | Règlement (UE) 2024/1781, art. 10 |
| **Support de données** | QR code ou autre support physique sur le produit ou l'emballage permettant d'accéder au passeport numérique | Règlement (UE) 2024/1781, art. 10 |

---

## État des textes (consulté le 2026-10-28)

### Ce qui est FIXÉ

| Élément | Statut | Source |
|---------|--------|--------|
| **Règlement ESPR (UE) 2024/1781** | Publié au Journal officiel, **en vigueur** | [EUR-Lex](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=fr) |
| **Registre DPP européen** | **Opérationnel** depuis le 20 juillet 2026 | [Commission européenne](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en) |
| **Règlement batteries (UE) 2023/1542** | Publié, **en vigueur** | [EUR-Lex](https://eur-lex.europa.eu/eli/reg/2023/1542/oj/eng) |
| **GS1 Digital Link 1.7.0** | **Ratifié** en août 2026 | [GS1 Standards](https://ref.gs1.org/standards/digital-link/uri-syntax/) |
| **Obligation de principe** (art. 9) | Un produit ne peut être mis sur le marché que si un passeport est disponible, **conformément aux actes délégués** | Règlement (UE) 2024/1781, art. 9 |

### Ce qui est INDICATIF ou NON ENCORE PUBLIÉ

| Élément | Statut | Source |
|---------|--------|--------|
| **Actes délégués par catégorie de produits** | **AUCUN acte délégué adopté à ce jour** — Calendrier indicatif : fer/acier Q4 2026, textiles Q4 2027, mobilier 2028, matelas 2029 | [Commission européenne](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en) |
| **Date batteries (18 février 2027)** | Présentée comme date d'obligation, mais incluse dans le **calendrier indicatif** de la Commission | [Batteries DPP FAQ](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport/eu-digital-product-passport-faq-batteries_en) |
| **Normes harmonisées CEN/CENELEC JTC 24** | 6 normes attendues en décision d'exécution (juillet 2026), 2 autres (novembre 2026) — **Non encore publiées au Journal officiel** | [Commission européenne](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en) |
| **Champs de données par catégorie** | Définis par les actes délégués — Pour les batteries, des **lignes directrices** existent (août 2026), mais les champs obligatoires au 18 février 2027 sont encore partiellement « en attente d'adoption de l'Omnibus » | [Guidance batteries](https://single-market-economy.ec.europa.eu/news/guidance-support-preparations-digital-batteries-passport-2026-08-21_en) |
| **Période de transition** | « Au moins 18 mois » après l'adoption d'un acte délégué, sauf justification | Règlement (UE) 2024/1781, art. 4 |

**Avertissement** : La Commission précise que « les calendriers indicatifs peuvent évoluer au fur et à mesure de l'avancement des travaux législatifs et techniques ».

---

## Formulations à EMPLOYER

| Contexte | Formulation recommandée | Justification |
|----------|-------------------------|---------------|
| **Description du plugin** | « Base de données ouverte et versionnée pour le passeport numérique de produit » | Décrit la réalité technique sans engagement réglementaire |
| **Portée** | « Modèle de données préparatoire aligné sur le règlement ESPR (UE) 2024/1781 » | Reconnaît l'existence du règlement sans promettre la conformité |
| **Calendrier** | « En préparation de l'obligation prévue pour certaines catégories de produits (calendrier indicatif de la Commission : batteries 2027, textiles 2027, etc.) » | Reprend le caractère indicatif des calendriers officiels |
| **Données** | « Champs de données anticipés sur la base du règlement (origine, recyclabilité, réparabilité, etc.), susceptibles d'évoluer selon les actes délégués à venir » | Indique que les champs sont provisoires |
| **Identifiants** | « Support des identifiants GS1 Digital Link conformément au standard GS1 1.7.0 » | Standard ratifié, engagement tenable |
| **Conformité** | « **Aucune promesse de conformité réglementaire** tant que les actes délégués par catégorie de produits ne sont pas publiés » | Transparence totale |
| **Démo** | « Démonstration de la structure de données et du parcours utilisateur, avec des données fictives » | Clarifie qu'il ne s'agit pas d'un passeport réel |

---

## Formulations à ÉVITER (interdites)

| Formulation interdite | Pourquoi | Risque |
|----------------------|----------|--------|
| « Conforme au règlement ESPR » | Les actes délégués ne sont pas publiés | Fausse promesse légale |
| « Certifié DPP » | Aucun processus de certification n'existe à ce jour | Tromperie |
| « Prêt pour février 2027 » | La date batteries est indicative, les champs non finalisés | Engagement non tenable |
| « Répond aux exigences légales » | Les exigences par catégorie ne sont pas fixées | Faux |
| « Validé par la Commission » | Le plugin est indépendant, non validé officiellement | Usurpation |
| « Conforme aux normes harmonisées » | Les normes ne sont pas publiées au JO | Fausse conformité |

**Test de contenu (US-235)** : un test automatisé vérifiera que le README, la démo et tout texte public ne contiennent ni « conforme » ni « certifié » dans le contexte du DPP.

---

## Recoupement avec Sylius Plus

**Résultat de la recherche du 2026-10-28** : aucune fonctionnalité de passeport numérique de produit, de gestion de la durabilité ou de conformité ESPR trouvée dans la documentation publique de Sylius Plus.

**Sources consultées** :
- [Page principale Sylius Plus](https://sylius.com/plus/)
- [Features Overview PDF](https://sylius.com/wp-content/uploads/2021/06/Sylius-Features-Overview.pdf) (juin 2021)
- [Blog Sylius](https://sylius.com/blog/) (dernière mise à jour : février 2026, Module Subscriptions)

**Fonctionnalités de conformité identifiées dans Sylius Plus** :
- Module Contract Withdrawal (juin 2026) : droit de rétractation UE
- Aucune mention de DPP, ESPR, écoconception, recyclabilité ou durabilité

**Conclusion** : **Aucun recoupement identifié.** Le plugin peut être développé et promu publiquement conformément à la politique de communication du 2026-10-14 (pas de promotion si recoupement avec Sylius Plus).

---

## Identifiants GS1 Digital Link

**Standard retenu** : GS1 Digital Link 1.7.0, ratifié en août 2026.

**URI canonique** :
- Format : `https://id.gs1.org/01/{GTIN-14}/...`
- GTIN-8, GTIN-12 et GTIN-13 complétés à 14 chiffres (padding)
- Utilisable dans un QR Code standard

**Clés primaires identifiées** :
- `01` : GTIN (Global Trade Item Number) — identifiant produit universel
- `8006` : ITIP (Identification of an Individual Trade Item Piece)
- `8010` : CPID (Component/Part Identifier)
- `8013` : Global Model Number

**Support physique** : QR Code standard (Data Matrix également possible), conforme aux spécifications GS1 General Specifications 26.0.

**Référence** : [GS1 Digital Link URI Syntax](https://ref.gs1.org/standards/digital-link/uri-syntax/)

---

## Recommandations pour les stories US-230 à US-235

### Modèle de données minimal (US-230)

**Champs anticipés** (sujet à révision selon les actes délégués) :

| Champ | Type | Source | Justification |
|-------|------|--------|---------------|
| `product_identifier` | GTIN-14 | GS1 Digital Link | Identifiant unique persistant (art. 10 ESPR) |
| `digital_link_uri` | URI | GS1 Digital Link 1.7.0 | Support de données (QR code) |
| `origin_materials` | Texte | Règlement ESPR, orientations batteries | Traçabilité |
| `recyclability` | Texte | Règlement ESPR | Durabilité |
| `repairability` | Texte | Règlement ESPR | Durabilité |
| `spare_parts` | Relation | Plugin customer-asset-management | Pièces détachées compatibles (EPIC-19) |
| `version` | Integer | Proposition | Versionnage du modèle de données |
| `created_at` | DateTime | Standard | Traçabilité |
| `updated_at` | DateTime | Standard | Traçabilité |

**Architecture** : entité Doctrine séparée (`DigitalProductPassport`), liée à `ProductVariant` (1:1 optionnel).

### Page publique (US-231)

- Route publique `/dpp/{gtin}` ou `/dpp/{uuid}`
- Accessible sans authentification
- Affichage des données du passeport en lecture seule
- Lien « En savoir plus » vers EUR-Lex (règlement ESPR)
- **Mention obligatoire** : « Base de données préparatoire, sans promesse de conformité réglementaire »

### QR Code et GS1 Digital Link (US-232)

- Génération d'un QR Code à partir de l'URI GS1 Digital Link
- Format : `https://id.gs1.org/01/{GTIN-14}?10={lot}&21={serial}`
- Bibliothèque : `endroid/qr-code` (déjà utilisée dans la démo)
- Affichage dans la fiche produit et l'emballage (PDF imprimable)

### Pièces détachées compatibles (US-233)

- Relation `ManyToMany` entre `DigitalProductPassport` et `Product` (pièces compatibles)
- Affichage sur la page publique du DPP
- Lien vers le plugin `customer-asset-management` pour la gestion des actifs clients

### Formulation prudente (US-235)

- Test automatisé vérifiant l'absence de « conforme » et « certifié » dans :
  - README du plugin
  - Page de démo
  - Traductions (`translations/messages.*.yaml`)
  - Templates Twig
- Fixtures avec données fictives clairement marquées « Données de démonstration »

---

## Questions ouvertes pour Thibaut

1. **Périmètre des catégories de produits** : faut-il limiter le modèle de données aux batteries (calendrier 2027), ou anticiper d'autres catégories (textiles, fer/acier) avec un modèle extensible ?
2. **Intégration Factur-X** (US-234, Could, non planifiée) : faut-il prévoir dès maintenant l'interface pour le lien optionnel dans la facture électronique, ou attendre une demande ?
3. **Publication du plugin** : faut-il créer le dépôt `tmonier/sylius-digital-product-passport-plugin` dès le sprint 36 (MVP), ou attendre la finalisation de toutes les stories ?
4. **Communication** : le DPP peut-il être mentionné dans les posts LinkedIn, ou faut-il attendre la publication d'au moins un acte délégué pour éviter toute confusion ?

---

## Estimation des stories US-230 à US-235

| Story | Points initiaux | Estimation révisée | Justification |
|-------|----------------|-------------------|---------------|
| US-230 (modèle) | 5 | **5** | Confirmé : entité Doctrine, migration, admin |
| US-231 (page publique) | 3 | **3** | Confirmé : route, contrôleur, template simple |
| US-232 (QR / GS1) | 3 | **3** | Confirmé : génération QR, URI GS1 |
| US-233 (pièces compatibles) | 3 | **3** | Confirmé : relation ManyToMany, affichage |
| US-234 (Factur-X) | 3 | **3** (non planifiée) | Confirmé : lien optionnel dans XML Factur-X |
| US-235 (démo, formulation) | 3 | **3** | Confirmé : fixtures, test de contenu, intégration |

**Total retenu (Must)** : 17 points (US-229 à US-233, US-235).

---

## Sources consultées

- [Règlement (UE) 2024/1781 — ESPR](https://eur-lex.europa.eu/eli/reg/2024/1781/oj?locale=fr) (consulté le 2026-10-28)
- [Commission européenne — Digital Product Passport](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport_en) (consulté le 2026-10-28)
- [Commission européenne — DPP Batteries](https://single-market-economy.ec.europa.eu/single-market/digital-product-passport/batteries_en) (consulté le 2026-10-28)
- [Commission européenne — Guidance batteries (août 2026)](https://single-market-economy.ec.europa.eu/news/guidance-support-preparations-digital-batteries-passport-2026-08-21_en) (consulté le 2026-10-28)
- [Règlement (UE) 2023/1542 — Batteries](https://eur-lex.europa.eu/eli/reg/2023/1542/oj/eng) (consulté le 2026-10-28)
- [GS1 Digital Link URI Syntax 1.7.0](https://ref.gs1.org/standards/digital-link/uri-syntax/) (consulté le 2026-10-28)
- [GS1 Digital Link standard](https://www.gs1.org/standards/gs1-digital-link) (consulté le 2026-10-28)
- [Sylius Plus](https://sylius.com/plus/) (consulté le 2026-10-28)
- [Sylius Features Overview PDF](https://sylius.com/wp-content/uploads/2021/06/Sylius-Features-Overview.pdf) (consulté le 2026-10-28)

---

**Fin de la note de cadrage.**
