# Tâches : US-202 : Catégories de taxe sur le catalogue de démonstration : TVA affichée au panier et sur les commandes

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 3 (Should, **sacrifice : n°1**)
- **Sprint** : sprint-30-compte-tableau-de-bord-et-tva
- **Worker proposé** : w202
- **Story** : [US-202](../../../backlog/user-stories/US-202.md)

## Résumé de la US

**En tant qu'** acheteuse B2B  
**Je veux** voir le montant hors taxes, la TVA et le total TTC de mon panier et de mes commandes  
**Afin de** retrouver les montants de ma facture et de mon budget

## Règles et contraintes

- **Sans maquette** : la maquette du panier validée au sprint 29 montre déjà HT, TVA et TTC ; la ligne TVA et le total TTC reprennent la place qu'elle leur donne (masquées au sprint 29 faute de catégorie de taxe).
- **Ce qui existe déjà** (relevé au planning) : catégorie `standard` (« TVA normale ») et taux `fr_standard` (20 %, zone `FR`, `included_in_price: false`) dans `config/packages/demo_fixtures.yaml` ; aucun produit ne porte de catégorie (`ProductCsvFixture` ne l'affecte pas) ; l'historique de 18 mois est écrit en masse (`BulkOrderWriter`).
- **Prix B2B affichés HT, inchangés** : aucun prix de la liste, de la fiche ou du panier ne doit bouger ; comparaison avant/après consignée.
- **Limite d'approbation comparée au total HT** (décision de Thibaut du 2026-10-25). **Constat du planning** : le plugin purchase-approval compare `$order->getTotal()` (`ApprovalRequirementChecker`, `ApprovalPolicy`, `OrderApprovalRequestListener`), qui inclura la TVA. Cadrage T-202-01 : décoration de ces services **dans la démo**, sans modifier le plugin ; si la décoration n'est pas possible proprement, question à Thibaut (option du plugin = PR, tag et release sur son GO) **avant tout code**, notée au registre ; le message d'approbation du panier (`CartApprovalNotice`) annonce le même montant.
- Commandes de l'historique : TVA présente ou décision notée (les montants du tableau de bord des achats, US-201, restent HT ou TTC de façon documentée, sans changement silencieux ; coordination avec w201 par l'orchestrateur).
- Produits créés par l'import ERP simulé (US-187, US-188) : catégorie de taxe affectée.
- **État hors base** (règle 16) : aucun attendu ; les nouveaux prix et taxes n'arrivent en production qu'à la remise à zéro de 03:00 suivant le déploiement (dit à l'avance).
- Fichiers communs (règle 21) : `config/packages/demo_fixtures.yaml` et `fixtures/` : US-202 seule ce sprint ; gabarits du panier `templates/shop/theme_b2b/cart/` : US-202 seule ce sprint ; domaine de traduction `cart` : US-202 seule.
- Tests uniquement par `WORKER_ID=w202 TEST_DB_NAME=b2b_w202 TEST_CACHE_DIR=var/cache-w202 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local et image reconstruite** : panier de Claire (HT, livraison, TVA 20 %, TTC), commande passée, produit importé, limite. **Production** (lecture seule, sans connexion, après la remise à zéro de 03:00) : prix HT publics inchangés sur la liste et la fiche.

## Fichiers principaux touchés

- `src/Demo/DataFixtures/ProductCsvFixture.php` (ou configuration des fixtures), import ERP simulé de la démo, `src/Demo/Theme/Cart*` et une décoration d'approbation dans `src/Demo/` si le cadrage la retient
- `templates/shop/theme_b2b/cart/` (récapitulatif), `translations/cart.{fr,en}.yaml`
- `tests/Functional/Demo/`, `tests/Unit/Demo/` (nouveaux fichiers ; tests du panier, des paliers, de l'approbation et du tunnel relus)
- `config/demo/proof.json` (par `bin/proof-rebase`), `docs/images/cart-*`, `docs/theme-b2b.md` (section du panier)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-202-01 | [DOC] | Cadrage : affectation de la catégorie (fixtures CSV, import ERP, historique en masse), mode de calcul (`included_in_price: false`), récapitulatif du panier et des commandes, **comparaison HT de la limite** (services du plugin à décorer dans la démo ou question à Thibaut), montants du tableau de bord des achats ; plan des tests ; prix de référence relevés (liste, fiche, panier de Claire) | 0,75 h | - | Claude | ✅ |
| T-202-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : panier HT, livraison, TVA 20 %, TTC ; prix affichés inchangés ; commande avec TVA ; produit importé avec catégorie ; limite comparée au HT et message du panier cohérent | 0,75 h | T-202-01 | Claude | ✅ |
| T-202-03 | [DB] | Catégorie `standard` affectée aux produits des fixtures et aux produits de l'import ERP simulé ; historique de commandes cohérent (ou décision notée) ; fixtures rechargées en local | 1 h | T-202-02 | Claude | ✅ |
| T-202-04 | [FE-WEB] | Récapitulatif du panier : ligne TVA et total TTC réaffichés à la place de la maquette validée ; récapitulatif de commande et e-mails relus ; AAA et 360 px conservés | 0,75 h | T-202-03 | Claude | ✅ |
| T-202-05 | [BE] | Limite d'approbation comparée au HT par décoration dans la démo (contrôle, politique, montant de la demande, message du panier) ; plugin non modifié ; si impossible, arrêt et question à Thibaut notée au registre | 0,75 h | T-202-03 | Claude | ✅ |
| T-202-06 | [TEST] | Suite ciblée par `bin/run-tests` (panier, paliers, approbation, tunnel de commande de bout en bout, import ERP, tableau de bord des achats), prix comparés avant/après, PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y du panier FR et EN ; commande exacte et dernière ligne citées | 0,75 h | T-202-04, T-202-05 | Claude | ✅ |
| T-202-07 | [DOC] | Captures `docs/images/cart-*` mises à jour par `bin/render-page`, section du panier de `docs/theme-b2b.md` | 0,5 h | T-202-06 | Claude | ✅ |
| T-202-08 | [TEST] | Image reconstruite et conteneur neuf : panier et commande avec TVA ; production **après la remise à zéro de 03:00** suivant le déploiement, lecture seule : prix HT publics inchangés ; mesures consignées | 0,5 h | merge de la PR | Claude | 🔲 |
| T-202-09 | [REV] | Revue (ton neutre, parité FR et EN, prix inchangés, plugin non modifié) et table « scénario → test ou preuve » remplie | 0,25 h | T-202-08 | Claude | 🔲 |

**Total estimé : 6 h** (9 tâches, toutes pour Claude).

## Cadrage et preuves (w202, 2026-10-25)

- **Catégorie de taxe** : affectée à la variante dans `ProductCsvFixture` (catégorie `standard`, taux `fr_standard` 20 %, `included_in_price: false`) : les prix de la liste, de la fiche et du panier restent HT (18,90 € du casque, 797,20 € de sous-total du panier de Claire, inchangés). La livraison est taxée comme la marchandise (catégorie `standard` sur la méthode `carrier`, décision de l'orchestrateur : la maquette validée applique la TVA sur le sous-total et la livraison).
- **Import ERP simulé** : il ne crée aucun produit, il met à jour les prix et les stocks de variantes du catalogue ; elles portent donc la catégorie (test `CartTaxTest`).
- **Panier** : `templates/shop/theme_b2b/cart/summary.html.twig` affichait déjà la ligne TVA et « Total TTC » dès que la TVA existe : aucun Twig modifié.
- **Historique de commandes** (`BulkOrderWriter`) : laissé sans TVA (montants HT, comme avant), pour ne pas changer en silence les montants du tableau de bord des achats (US-201) ; choix d'orchestrateur, noté au registre par l'orchestrateur.
- **Limite d'approbation** (décision de Thibaut, option A, registre 57f6d56) : le plugin compare `getTotal()` et ses services sont `final` (pas de décoration possible) : plugin purchase-approval 1.4.0 (réglage `amount_basis`, PR #9 et #10), `amount_basis: net` dans `config/packages/demo_purchase_approval.yaml`. Le message du panier (`CartApprovalNoticeProvider`) annonce le même montant (hors taxes) par le résolveur ; le calcul de l'approbateur est dans `ApproverNameProvider::firstNameFor()`.
- **Constat de la livraison** : la zone `EU` (zone de livraison) était aussi retenue comme zone de taxe dès que l'adresse de livraison était connue : la TVA disparaissait au checkout. Zone `EU` en portée `shipping` (fixtures) ; `CartToOrderTest` vérifie la TVA de la commande passée.
- Test d'isolation corrigé dans `tests/Support/BuildsDemoCarts.php` (deux paniers créés dans la même seconde).

## Mesures (w202, 2026-10-25, local : rendu du noyau `APP_ENV=test` avec `--login`, Claire, vignettes réelles)

| Contrôle | Commande | Résultat |
|----------|----------|----------|
| Lint Twig | `php -d memory_limit=1G bin/console lint:twig templates/shop/theme_b2b/cart` | `All 11 Twig files contain valid syntax.` |
| Lint YAML | `php -d memory_limit=1G bin/console lint:yaml config translations` | `All 171 YAML files contain valid syntax.` |
| pa11y WCAG2AAA, panier FR | `pa11y --config cfg.json http://127.0.0.1:8731/fr_FR-cart.html` | `No issues found!` |
| pa11y WCAG2AAA, panier EN | idem `en_US-cart.html` | `No issues found!` |
| Débordement horizontal à 1280 px et à 360 px | puppeteer, `scrollWidth - clientWidth` | 0 et 0 |
| Captures | `docs/images/cart-buyer-fr.jpg` (1280), `cart-buyer-360-fr.jpg` (360) | relues : TVA (20%), Total TTC, vignettes réelles |

Limite de `bin/render-page` : il ne génère les vignettes que pour les URL absolues `http://127.0.0.1/media/cache/...` ; le panier émet des URL relatives (`/media/cache/...`), la copie de travail du script en tient compte (non commitée, fichier de l'orchestrateur).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Panier avec TVA (nominal) | T-202-02, T-202-07 | `CartTaxTest::testTheCartShowsTheSubtotalExcludingTaxTheShippingTheVatAndTheTotalIncludingTax` |
| Prix affichés inchangés (alternatif) | T-202-02, T-202-06 | `CartTaxTest::testThePricesExcludingTaxOfTheListAndTheProductPageDidNotMove` |
| Commande avec TVA (alternatif) | T-202-02 | `CartTaxTest::testThePlacedOrderKeepsItsVatAndItsTotalIncludingTax` |
| Produit importé par l'ERP (erreur) | T-202-02 | `CartTaxTest::testTheVariantsTouchedByTheErpImportCarryTheCategoryAndTheTaxIsComputedOnThem` |
| Limite d'approbation (erreur) | T-202-02, T-202-05 | |
