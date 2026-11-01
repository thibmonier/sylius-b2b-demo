# Tâches - US-234 : Passeport numérique produit — lien optionnel dans la facture Factur-X

## Informations US
- **Epic** : EPIC-19
- **Persona** : Isabelle (administratrice), Sophie (back-office)
- **Story Points** : 3
- **Sprint** : sprint-43-copilote-complet-et-passeport

## Résumé de la US
**En tant que** marchand
**Je veux** que la facture Factur-X indique le lien du passeport des produits facturés
**Afin de** relier le justificatif et la fiche produit

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-234-01 | [DOC] | Lire le mapper Factur-X, `InvoiceLine`, `CiiXmlWriter` et le DPP plugin | 0,25 h | — | 🔲 |
| T-234-02 | [TEST] | RED : test unitaire — lien DPP dans la ligne de facture (option activée) | 0,5 h | T-234-01 | 🔲 |
| T-234-03 | [BE] | GREEN : champ `documentReference` optionnel dans `InvoiceLine`, mapper enrichi | 1 h | T-234-02 | 🔲 |
| T-234-04 | [TEST] | RED : test unitaire — option désactivée, facture inchangée | 0,25 h | T-234-03 | 🔲 |
| T-234-05 | [BE] | GREEN : config `dpp_link_in_invoice: false` par défaut | 0,25 h | T-234-04 | 🔲 |
| T-234-06 | [TEST] | RED : test unitaire — produit sans passeport, pas de lien | 0,25 h | T-234-05 | 🔲 |
| T-234-07 | [BE] | GREEN : guard null dans le mapper | 0,25 h | T-234-06 | 🔲 |
| T-234-08 | [TEST] | RED : test de validation — XML valide contre XSD après ajout du lien | 0,5 h | T-234-07 | 🔲 |
| T-234-09 | [BE] | GREEN : écriture du `BT-128` (document référence) dans `CiiXmlWriter` | 0,5 h | T-234-08 | 🔲 |
| T-234-10 | [TEST] | Test fonctionnel — facture avec et sans DPP, validation PDF/A-3 | 0,5 h | T-234-09 | 🔲 |
| T-234-11 | [OPS] | Intégration démo : activer l'option, ajouter des produits avec DPP | 0,25 h | T-234-10 | 🔲 |
| T-234-12 | [DOC] | README facturx : section « Lien du passeport numérique » | 0,25 h | T-234-11 | 🔲 |
| T-234-13 | [REV] | CI verte plugin facturx + démo | 0,25 h | T-234-12 | 🔲 |

**Total estimé** : 5 h

---

## Détail des tâches

### T-234-01 : Lecture du code existant
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : —

Relire le `FacturXInvoiceMapper`, le modèle `InvoiceLine`, le `CiiXmlWriter` (notamment la section BG-25 des lignes de facture), et le plugin DPP (`DigitalLinkGenerator`, `PassportController`) pour identifier l'URL publique du passeport.

### T-234-02 : RED — lien DPP dans la ligne de facture
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-234-01
- **Scénario** : « Lien dans la facture (nominal) »

Test unitaire : option activée, produit avec DPP → la ligne contient un `documentReference` avec l'URL du passeport. Le test doit échouer (rouge prouvé).

### T-234-03 : GREEN — champ `documentReference` dans `InvoiceLine`
- **Type** : [BE]
- **Estimation** : 1 h
- **Dépend de** : T-234-02

Ajouter un champ optionnel `?string $documentReference` dans `InvoiceLine` (BT-128 AdditionalReferencedDocument). Enrichir le mapper pour résoudre l'URL du passeport via le `DigitalLinkGenerator` du plugin DPP (si disponible).

### T-234-04 : RED — option désactivée
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-03
- **Scénario** : « Option désactivée »

Test unitaire : option désactivée → facture identique à aujourd'hui.

### T-234-05 : GREEN — configuration par défaut
- **Type** : [BE]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-04

Ajouter `dpp_link_in_invoice: false` dans la config du bundle facturx. Désactivé par défaut.

### T-234-06 : RED — produit sans passeport
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-05
- **Scénario** : « Produit sans passeport »

Test unitaire : ligne sans DPP → aucun lien ajouté.

### T-234-07 : GREEN — guard null
- **Type** : [BE]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-06

Guard dans le mapper : si le produit n'a pas de passeport, `documentReference` reste null.

### T-234-08 : RED — validation XML
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-234-07
- **Scénario** : « Validation du profil »

Test : la facture avec lien DPP passe la validation XSD et les règles EN 16931.

### T-234-09 : GREEN — écriture BT-128 dans le XML
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-234-08

Ajouter l'écriture de `ram:AdditionalReferencedDocument` dans `CiiXmlWriter` pour chaque ligne ayant un `documentReference`. Type code = 916 (document référencé).

### T-234-10 : Test fonctionnel
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-234-09

Test fonctionnel : génération d'une facture avec et sans DPP, validation du PDF/A-3 résultant.

### T-234-11 : Intégration démo
- **Type** : [OPS]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-10

Activer l'option dans la démo. Les produits industriels ayant déjà un DPP (sprint 37+) afficheront automatiquement le lien.

### T-234-12 : README
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-11

Section « Digital Product Passport link » dans le README du plugin facturx (FR/EN).

### T-234-13 : CI verte
- **Type** : [REV]
- **Estimation** : 0,25 h
- **Dépend de** : T-234-12

CI verte (PHPStan max, ECS, tests, validation XSD).
