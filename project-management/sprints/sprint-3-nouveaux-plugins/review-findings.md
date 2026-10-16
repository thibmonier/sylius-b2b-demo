# Revue de code — sprint 3 (fonctionnalités B2B)

Date : 2026-10-16. Branche `feature/sprint-3-nouveaux-plugins`, PR #3.
Méthode : 2 revues indépendantes (Symfony/code, sécurité OWASP Top 10:2025), puis vérification de chaque constat dans le code et dans `vendor/` avant correction.

Périmètre in-démo : US-021, US-022, US-023 (spike), US-026. US-019/US-020 extraites en plugins autonomes (décision du 2026-10-16), hors de cette PR.

## Verdict

Aucune XSS, aucune injection XML/XXE, aucune injection SQL. **Un vrai bug** trouvé et corrigé : la validation serveur du PO ne s'exécutait jamais. Plusieurs défauts de conception corrigés dans ce sprint ; les correctifs qui relèvent des plugins sont reportés.

## Corrigé dans ce sprint (commit `1ed997f`)

| # | Constat | Sévérité | Source | Correctif |
|---|---------|----------|--------|-----------|
| C1 | **Validation serveur du PO / centre de coût jamais exécutée** : contraintes dans le groupe `Default`, alors que `CompleteType` valide `sylius` + `sylius_checkout_complete`. Un PO > 100 caractères provoquait une 500 (VARCHAR 100). Le test ne vérifiait que la déclaration. | Moyenne (bug réel) | Sécu #1, Symfony #3 | Contraintes déclarées dans les groupes `sylius`/`sylius_checkout_complete`, regex ancrées (`\z`), allowlist sur le centre de coût, tests via le vrai Validator. |
| C2 | **Usurpation du PO via les notes** : le subscriber PunchOut agissait sur toute commande ; un client écrivant « PunchOut purchase order XYZ » dans les notes obtenait un PO. | Moyenne | Symfony #2, Sécu #2 | Le subscriber n'agit que si la route courante est `tmonier_punchout_cxml_endpoint` (POST /punchout/cxml, vérifiée dans `vendor/…/config/routes.yaml`). Test : commande shop avec le préfixe → aucun PO. |
| C3 | **PO PunchOut non validé** (ni regex ni longueur, tronqué à 100). | Élevée (revue) | Symfony #1 | Mêmes règles que le formulaire ; valeur invalide → pas de PO + warning, plus de troncature silencieuse. |
| C4 | Centre de coût affiché et persisté même si le canal le désactive. | Moyenne | Symfony #3, Sécu #6 | Champ ajouté seulement si `Channel::isCostCenterEnabled()`. |
| C5 | Longueurs incohérentes (64 / 100). | Faible | Symfony #4 | Constantes uniques `PO_REFERENCE_MAX_LENGTH` (64) et `COST_CENTER_MAX_LENGTH` (50) dans `OrderPoReferenceAwareInterface`. |
| C6 | Writer Factur-X : caractères interdits en XML 1.0 / UTF-8 invalide → facture non conforme EN 16931 ; warnings libxml. | Faible | Sécu #3, Symfony #7 | Nettoyage `[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]`, `mb_check_encoding`, `libxml_use_internal_errors` + test du retour ; dégradation en facture sans BT-10. |

## Non-défauts vérifiés

- **Unicité de `sylius_order.number`** (Symfony #6) : index unique `UNIQ_6196A1F996901F54` → la recherche par numéro dans le writer est sûre.
- **XSS** : PO rendu en `{{ }}` auto-échappé partout (admin, e-mail), aucun `|raw`. Pas d'injection d'en-têtes e-mail (le PO n'est que dans le corps).
- **XXE** : `loadXML` ne parse que la sortie du writer interne, avec `LIBXML_NONET`, sans `NOENT`/`DTDLOAD`.
- **Moyen de paiement `on_account`** : éligibilité imposée par `TradeCreditEligibilityChecker` (client connecté, entreprise, encours…), canal B2B, gateway offline.

## Reporté (relève des plugins)

| # | Constat | Plugin | Note |
|---|---------|--------|------|
| R1 | Associations Doctrine ciblant les classes concrètes du cœur Sylius (`Order`, `Customer`, `Channel`) → contournées par `resolve_target_entities` dans la démo. | punchout-cxml | Patch : cibler les interfaces, puis retirer le contournement et ajouter `doctrine:schema:validate` en CI. Publication avec l'accord de l'utilisateur. |
| R2 | Le plugin ne dispatche aucun événement à la création de commande ; l'orderID n'est lisible que dans les notes. | punchout-cxml | Patch : dispatcher un événement portant l'orderID (ou instancier `ReceivedPurchaseOrder`, aujourd'hui mappé mais jamais créé). |
| R3 | Blocage du checkout par liste de routes ; `PATCH /api/v2/shop/orders/{token}/complete` non couvert. | punchout-cxml / quote-request | Guard de workflow sur `sylius_order_checkout.complete` pour un panier PunchOut. |
| R4 | « PO requis » imposé seulement dans le formulaire shop (contournable par l'API). | démo → futur plugin PO | Règle de domaine (contrainte de classe sur Order ou guard de workflow). |
| R5 | Règle container-fill « tous produits » bloquant les paniers sans données logistiques. | container-fill | Ignorer les lignes sans données logistiques. Aucun impact aujourd'hui (aucune règle chargée). |
| R6 | Subscriber PunchOut dépendant du libellé des notes (contrat implicite). | punchout-cxml | Résolu par R2. |

## Hors périmètre

- US-019 (mode d'accès par canal) et US-020 (inscription validée) : plugins autonomes publiés, à traiter en projets dédiés.
- US-023 : ADR-0012 (statut Proposé) — à confirmer au POC Sylius 2.3 de US-024.
