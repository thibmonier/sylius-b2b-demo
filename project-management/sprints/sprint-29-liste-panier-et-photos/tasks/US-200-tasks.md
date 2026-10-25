# Tâches : US-200 : Refonte de la page panier dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **sacrifice : n°2**)
- **Sprint** : sprint-29-liste-panier-et-photos
- **Worker proposé** : w200 (orchestrateur pour la maquette)
- **Story** : [US-200](../../../backlog/user-stories/US-200.md)

## Résumé de la US

**En tant qu'** acheteuse B2B  
**Je veux** un panier qui récapitule clairement les lignes, les remises de palier, les totaux HT et TVA et ce qui se passera à la validation  
**Afin de** passer commande ou de demander un devis sans hésiter

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) publiée par l'orchestrateur et **validée par Thibaut (T-200-04) avant toute intégration Twig**. Les tâches de code T-200-05 à T-200-11 sont 📅 tant que son GO n'est pas donné et noté au registre.
- **Rendu réel d'abord** : captures connectées en local seulement (127.0.0.1, comptes des fixtures), rendu du noyau en mode prod, **un seul cache de rendu**, `bin/check-disk` avant chaque rendu.
- **Reprise d'US-186 et d'US-189** : jetons de `b2b.scss`, polices auto-hébergées (CSP `font-src 'self'`), AAA, cibles de 44 px, `prefers-reduced-motion`, 360 et 1280 px sans défilement horizontal.
- Canal B2B seulement : **le panier B2C reste inchangé et le tunnel de commande reste vert** (test de commande de bout en bout) ; les hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`. Hooks existants conservés : `cart_item`, `cart_tier_discount`.
- **Blocs des plugins repris par leurs hooks existants, sans modifier les plugins** : référence de commande (PO), limite et circuit d'approbation, conversion en devis, enregistrement en liste d'achat, commande rapide. Les actions secondaires aboutissent comme avant la refonte.
- Récapitulatif collant sur bureau, en bas d'écran sur téléphone ; le total reste visible à 360 px. La commande rapide peut rester à sa place actuelle (négociable).
- Page connectée : **aucune connexion en production** ; vérification en local et sur l'image reconstruite (conteneur neuf, règle 16 : le panier vit en base, rien d'autre n'est écrit hors base). En production : seulement les pages publiques accessibles sans connexion (panier vide anonyme, si la page est servie).
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w200 TEST_DB_NAME=b2b_w200 TEST_CACHE_DIR=var/cache-w200 bin/run-tests` ; preuve : `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Local et image reconstruite** pour le panier connecté (trois lignes, palier, dépassement de limite, panier vide, tunnel de commande de bout en bout, pa11y WCAG2AAA FR et EN, 360 et 1280 px). **Production** (lecture seule, sans connexion) : seulement ce qui est public, notée « sans objet » sinon. Mesures consignées dans ce fichier le jour du déploiement.

## Fichiers principaux touchés

- `templates/shop/theme_b2b/cart/` (gabarits du panier : `summary`, `_item`, état vide), `config/packages/demo_theme_b2b.yaml` (hooks du panier)
- `assets/shop/styles/b2b.scss`, `translations/` (domaine dédié au panier)
- `tests/Functional/Demo/`, `tests/Smoke/` (nouveaux fichiers ; tests existants du panier, des paliers, des approbations et du tunnel relus)
- `config/demo/proof.json` (rafraîchi), `docs/theme-b2b.md`, `docs/images/cart-*`, README

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-200-01 | [DOC] | Cadrage : inventaire du panier relu contre le code (hooks `cart_item` et `cart_tier_discount`, blocs des plugins : PO, limite et approbation, devis, liste d'achat, commande rapide, tunnel de commande, canal B2C, tests existants) ; scénarios types retenus (trois lignes, palier, dépassement de limite, panier vide) ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-200-02 | [TEST] | État de référence « avant » : tests du panier et du tunnel de commande de bout en bout verts sur `main`, captures connectées locales (bureau et 360 px) des quatre cas ; production sans objet pour la page connectée (relevé de ce qui est public) | 0,5 h | T-200-01 | Claude | 🟡 (tests ✅ ; captures « avant » non faites : disque sous 5 Go) |
| T-200-03 | [FE-WEB] | Maquette Claude Design (bureau et téléphone) : tableau des lignes (photo, nom, référence, quantité, prix unitaire HT, remise de palier, total), récapitulatif collant (sous-total HT, remises, TVA, total TTC), messages d'approbation et de limite, actions secondaires groupées, panier vide, récapitulatif en bas d'écran sur téléphone, B2C inchangé ; publiée en artefact privé | 2 h | T-200-01, T-TECH-29-01 | Orchestrateur | ✅ (maquette validée) |
| T-200-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-200-03 | Thibaut | ✅ (GO de Thibaut, registre 8028523) |
| T-200-05 | [TEST] | Tests rouges, commités seuls et cités dans la PR : panier lisible (référence, quantité, prix HT, total, récapitulatif HT, TVA, TTC), remise de palier après changement de quantité sans perdre les autres lignes, message d'approbation au dépassement, devis et liste d'achat aboutissent, panier vide, 360 px avec total visible, B2C inchangé, tunnel de commande de bout en bout | 1 h | T-200-04 | Claude | ✅ `0a62cd6` |
| T-200-06 | [FE-WEB] | Gabarits et hooks du panier, styles `b2b.scss`, récapitulatif collant (bureau) et en bas d'écran (téléphone), AAA, cibles de 44 px, `prefers-reduced-motion` ; blocs des plugins repris par leurs hooks ; canal B2B seulement | 3 h | T-200-05 | Claude | ✅ `01e1990`, `111cd16` |
| T-200-07 | [DOC] | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | T-200-06 | Claude | ✅ domaine `cart` (FR, EN, pluriels ICU), charte testée |
| T-200-08 | [TEST] | Suite ciblée par `bin/run-tests` (panier, paliers, approbation, devis, liste, tunnel de commande de bout en bout), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN (quatre cas), `bin/measure-mobile` à 360 px ; commande exacte et dernière ligne citées | 1 h | T-200-06, T-200-07 | Claude | 🟡 (tunnel de bout en bout, pa11y AAA et Puppeteer 360/1280 verts ; `bin/measure-mobile` non lancé) |
| T-200-09 | [DOC] | Captures `docs/images/cart-*` sur un rendu du noyau en mode prod (connecté, local), `docs/theme-b2b.md` et README mis à jour | 0,5 h | T-200-08 | Claude | 🟡 (captures et `docs/theme-b2b.md` faits ; README non touché) |
| T-200-10 | [TEST] | Image reconstruite et conteneur neuf : panier connecté et tunnel de commande vérifiés (règle 16) ; production après le déploiement, lecture seule : ce qui est public, FR et EN, pa11y et 360 px ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 🟡 (image reconstruite et démarrée par la CI « Docker image » de #111 ; panier connecté non vérifiable en production, aucune connexion) |
| T-200-11 | [REV] | Revue (ton neutre, parité FR et EN, B2C et tunnel inchangés) et table « scénario → test ou preuve » remplie ; `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-200-10 | Claude | ✅ (table remplie ; écarts acceptés au registre) |

**Total estimé : 10,75 h** (11 tâches ; 10,25 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Cadrage (T-200-01, relu contre le code le 2026-10-25)

**Ce que contient aujourd'hui le panier (canal B2B).** Le panier est celui du thème Sylius ; la refonte part de ces hooks :

| Zone | Hook ou source | Origine |
|------|----------------|---------|
| Lignes | `sylius_shop.cart.index.content.form.sections.general.items.body` → `item` (gabarit `shop/theme_b2b/cart_item.html.twig`, déclaré dans `config/packages/tmonier_plugins_overrides_pricing.yaml`, il remplace celui du plugin de prix dégressifs) | thème (US-091) : fiche produit, palier appliqué, prix négocié, invitation « ajoutez-en N de plus » |
| Remise de palier | `...general.summary` → `b2b_tier_discount` (priorité 450, `cart_tier_discount.html.twig`, `not is_granted('DEMO_CONSUMER_CHANNEL')`) | thème (`demo_theme_b2b.yaml`) |
| Devis | `sylius_shop.cart.index.content` → `tmonier_quote_request_notice` (150) et `tmonier_quote_request` (50) | plugin quote-request (hors formulaire) |
| Commande rapide | `sylius_shop.cart.index.content` → `tmonier_quick_order_link` (50) | plugin quick-order (affiché aussi panier vide) |
| Session PunchOut | `sylius_shop.cart.index.content` → `tmonier_punchout_session` (200) ; `checkout` remplacé dans `...general#right` | plugin punchout-cxml |
| Jauge de conteneur et bouton de paiement | `...general#right` → `tmonier_container_fill_gauge` (50) ; `checkout` (gabarit de la démo `shop/container_fill/cart_checkout.html.twig`, `tmonier_plugins_overrides.yaml`) | plugin container-fill |
| Pastilles « propulsé par » | `sylius_shop.cart.index.content` (45, 44) et `...general#right` (40) | démo (`demo_plugin_badges.yaml`) |

**Surprises par rapport à la fiche de la story** (à répercuter dans la maquette et les tests) :

1. **La référence de commande (PO) n'est pas dans le panier** : elle est à la dernière étape du tunnel (`sylius_shop.checkout.complete.content.form`, `templates/checkout/po_reference_block.html.twig`).
2. **La limite et l'approbation n'ont aucun bloc dans le panier.** Le plugin n'a pas de hook de panier : le message « attend l'approbation » n'existe qu'après la commande (flash `awaiting_approval`). Le scénario « message d'approbation au dépassement » est donc du **contenu nouveau**, pas la reprise d'un bloc. Limite de Claire : 500,00 € (`tmonier_purchase_approval_member_limit`, membre 49, sans limite d'approbation) ; son panier (862,79 € pour cinq lignes) la dépasse : le dépassement est son panier par défaut.
3. **« Enregistrer en liste d'achat » n'existe pas depuis le panier** : les listes partagées (Atelier…) n'ont que le sens liste → panier (`app_shop_company_wishlist_add_to_cart`). Le critère « demander un devis ou enregistrer en liste aboutit comme avant » ne peut viser que le devis ; l'enregistrement en liste est à retirer de la fiche ou à créer (hors périmètre sans décision).
4. **Il n'existe aucun test de commande de bout en bout** dans `tests/` (aucun test ne franchit `checkout/complete` puis `thank_you`) : la phrase « tunnel de commande vert » de la story n'a pas de test à relancer. À créer en T-200-05 (ou à décider avec l'orchestrateur).
5. Le panier de Claire a **cinq lignes** (liste « Atelier »), pas trois ; les trois lignes types de la maquette sont ses trois premières. Aucune ligne de taxe n'est écrite en base sur ce panier (variantes sans catégorie de taxe, pas d'adresse) : la TVA affichée à l'écran est celle du calcul au moment du rendu, à vérifier au premier rendu réel.
6. Pas d'état hors base (règle 16) : le panier vit en base ; la story n'ajoute ni fichier ni cache.

**Tests existants qui touchent le panier** : `Smoke/LiveDemoDataSmokeTest` (panier de départ de Claire, FR et EN), `Smoke/PluginBadgesSmokeTest`, `Smoke/ChannelAccessSmokeTest` et `Smoke/PagesTest` (visiteur anonyme renvoyé vers la connexion), `Functional/Demo/ContainerFillDemoTest` (jauge et bouton de paiement), `Functional/Demo/JourneyTest` (parcours qui remplit le panier), `Smoke/RawTranslationKeysSmokeTest` (clés de traduction brutes), `Unit/Theme/CartTierDiscountProviderTest` et `PriceBreaksTemplatesTest`, `Unit/ContainerFill/ContainerFillCheckoutGuardTest`, `Unit/PunchOut/PunchOutQuoteCheckoutBlockerTest`, `Unit/Order/OrderPoReferenceTest`, `Smoke/ApprovalWorkflowSmokeTest` (limite de Claire).

**Scénarios types retenus** (panier de Claire, Acme Industries, base locale) :

| Scénario | Données |
|----------|---------|
| Trois lignes | casque (EPI-CASK-001) ×10, chaussure (EPI-CHAU-001) ×6, gants (EPI-GANT-001) ×24 : 797,20 € HT |
| Palier atteint | les trois lignes : −12 % dès 2 pièces, 108,80 € de remise comprise dans les prix unitaires |
| Dépassement de limite | 797,20 € (ou 862,79 € avec livraison pour cinq lignes) contre une limite de 500,00 € |
| Panier vide | panier sans ligne : lien du devis masqué, commande rapide proposée |

Données de la maquette : `<scratchpad>/w200/maquette-data.json`.

**Plan des tests (T-200-05)** : les sept scénarios du tableau ci-dessous, plus un test de commande de bout en bout (point 4), tous dans de nouveaux fichiers de `tests/Functional/Demo/` ou `tests/Smoke/`, comptes partagés isolés par `IsolatesSharedAccounts`.

## État de référence « avant » (T-200-02, sur `feature/sprint-29-dev`)

- Commande : `WORKER_ID=w200 TEST_DB_NAME=b2b_w200 TEST_CACHE_DIR=var/cache-w200 bin/run-tests --filter 'CartTierDiscountProviderTest|PriceBreaksTemplatesTest|PluginBadgesSmokeTest|LiveDemoDataSmokeTest|ApprovalWorkflowSmokeTest|ChannelAccessSmokeTest|ContainerFillDemoTest|JourneyTest|RawTranslationKeysSmokeTest|PagesTest|PunchOutQuoteCheckoutBlockerTest|ContainerFillCheckoutGuardTest|OrderPoReferenceTest'`
- Dernière ligne : `Tests: 177, Assertions: 1473, Deprecations: 23, Skipped: 1.` (code de sortie 0, « OK, but there were issues! », 1 min 45).
- **Captures connectées locales (bureau et 360 px) : non faites.** L'espace libre est tombé à 4,4 Gio après le lot (seuil de 5 Go, règle 2) ; à refaire quand l'espace le permet, avant le GO de la maquette ou en début d'intégration.
- Production : sans objet pour la page connectée ; seul le panier d'un visiteur anonyme (renvoi vers la connexion) est public, déjà couvert par `PagesTest`.

## Scénarios et preuves

| Scénario | Tâche de test | Test ou preuve |
|----------|---------------|----------------|
| Panier lisible (nominal) | T-200-05 (rouge), T-200-08 | `CartPageTest::testClaireReadsEveryLineWithItsReferenceQuantityUnitPriceAndTotal`, `testTheSummaryAddsUpWhatTheShopComputes` |
| Remise de palier (alternatif) | T-200-05, T-200-08 | `CartPageTest::testAVolumePriceShowsOnlyOnTheLinesThatReachTheTierAndKeepsTheOthers` (la quantité se recalcule côté serveur par le formulaire vivant, comme avant : non rejoué dans un navigateur) |
| Commande soumise à approbation (alternatif) | T-200-05 | `CartPageTest::testAnOrderAboveTheLimitOfTheBuyerIsAnnouncedBeforeTheValidation`, `testNoApprovalNoticeUnderTheLimit`, `testNoApprovalNoticeForABuyerWithoutAnOrderLimit`, `CartToOrderTest` (commande en attente d'approbation) |
| Actions B2B secondaires (alternatif) | T-200-05, T-200-08 | `CartPageTest::testTheQuoteAndTheQuickOrderStayWithinReachAndTheQuoteSucceeds` (devis ; « enregistrer en liste » retiré, décision de Thibaut) |
| Panier vide (erreur) | T-200-05, T-200-08 | `CartPageTest::testAnEmptyCartOffersTheCatalogueAndTheQuickOrder` ; pa11y AAA FR et EN sur l'état vide : aucun problème |
| Débordement sur téléphone (erreur) | T-200-05, T-200-08 (`bin/measure-mobile`), T-200-10 | mesure Puppeteer locale à 360 et 1280 px sur le rendu du noyau : `scrollWidth` égal à la largeur visible, aucun élément qui dépasse (FR, EN, état vide) ; `bin/measure-mobile` et production : à faire (T-200-10) |
| Régression d'accessibilité ou du tunnel (erreur) | T-200-08 (pa11y AAA FR et EN, tunnel de bout en bout), T-200-10 (image reconstruite) | pa11y WCAG2AAA local, panier par défaut et panier vide, FR et EN : « No issues found! » ; `CartToOrderTest` (tunnel jusqu'à la commande) ; B2C : `CartPageTest::testTheB2cCartIsUnchanged` |

## Mesures (jour du déploiement)

_Non consignées (sprint planifié le 2026-10-25)._ Panier connecté : local et image reconstruite, aucune connexion en production.

## Preuves (T-200-08, sur `feature/sprint-29-dev`, 2026-10-25)

- Tests ciblés : `WORKER_ID=w200 TEST_DB_NAME=b2b_w200 TEST_CACHE_DIR=var/cache-w200 bin/run-tests --filter 'CartPageTest|CartToOrderTest|CartTextCharterTest|CartTierDiscountProviderTest|PriceBreaksTemplatesTest|B2bThemeTest|PluginBadgesSmokeTest|LiveDemoDataSmokeTest|ChannelAccessSmokeTest|JourneyTest|RawTranslationKeysSmokeTest|PagesTest|ThemeColorTokensTest|HomeStylesTest'` : verts avant le dernier lot ; le dernier lot (`LiveDemoDataSmokeTest|CartTextCharterTest`) : `Tests: 119, Assertions: 1248, Deprecations: 3.` Hors périmètre, constaté : `ContainerFillDemoTest::testAnyNumberOfBoxesIsAccepted` rouge depuis le suivi de stock d'US-198 (200 boîtes refusées) et un second panier de Claire laissé par un test PunchOut dans un lot combiné.
- `vendor/bin/ecs check src tests` : `[OK] No errors found.` ; `php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan.dist.neon --no-progress` : `[OK] No errors` ; `composer rector-dry-run` : `[OK] Rector is done!` ; lint Twig : `All 164 Twig files contain valid syntax.` ; lint YAML : `All 163 YAML files contain valid syntax.`
- pa11y 9 (`standard: WCAG2AAA`) sur le rendu du noyau en ligne de commande (`bin/render-page --login=claire@acme.example`, `APP_ENV=test`, un seul cache), feuille de style compilée par `sass` : panier par défaut et panier vide, FR et EN : `No issues found!` (quatre pages).
- 360 et 1280 px (Puppeteer, `isMobile` à 360) : aucun débordement horizontal, boutons des lignes et des cartes de 44 px au moins. Le récapitulatif suit les lignes sur téléphone (le total n'est pas dans le premier écran) : pas de barre fixe en bas d'écran dans cette version.
- Preuve publiée : `php bin/proof-check` : « test files: published 275, actual 286 (difference +11) » (les fichiers des trois stories) ; `bin/proof-rebase` à faire à l'intégration.
- Écarts à la maquette : (1) étapes « Adresse » puis « Validation » : la référence PO et le centre de coût sont à la dernière étape, pas à l'adresse ; (2) le message d'approbation compare le total de la commande (livraison comprise) à la limite, comme le plugin, sans « HT » ; (3) pour Claire la remise vient du prix de sa société : « Prix négocié Acme Industries dès 2 pièces » et « dont remise de prix négocié » (le libellé « Prix dégressif » reste pour un palier public) ; (4) aucune ligne de TVA : les produits n'ont pas de catégorie de taxe, la boutique ne calcule aucune taxe, rien n'est inventé.
- Avant la refonte : captures « avant » non prises (disque) ; l'état de référence reste le lot de tests de T-200-02.

