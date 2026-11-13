# Audit Omnibus (art. 6 bis dir. 98/6/CE) : plugin Sylius ou renoncer ?

Date : 2026-11-13. Lecture seule.

**Décision de Thibaut (2026-11-13) : abandonné pour le moment.** Aucun plugin, aucun guide planifié. À rouvrir si un canal B2C apparaît.

Légende : [V] vérifié (code local ou texte), [S] source secondaire, [H] hypothèse.

## 1. Cadre juridique
- EUR-Lex et Légifrance : **non joignables** (404/403 via l'outil). Texte primaire NON relu. [limite]
- [S] Dir. (UE) 2019/2161 : le prix antérieur est le prix le plus bas pratiqué ≥ 30 jours avant la réduction. Consommateurs seulement, exceptions périssables et réductions successives (référence = prix avant la première réduction).
- [S] France : art. L112-1-1 C. conso., créé par ord. 2021-1734, en vigueur 28/05/2022. Même logique. Réductions réservées à une clientèle spécifique hors champ (DGCCRF). Produit < 30 jours : prix le plus bas depuis la mise en vente (une source).
  - https://french-business-law.com/rench-legislation-art/article-l112-1-1-of-the-french-consumer-code
  - https://www.economie.gouv.fr/dgccrf/des-regles-plus-claires-contre-les-faux-rabais
- [S] Autres volets Omnibus (avis vérifiés, classement, prix personnalisés) : non relus. Ils relèvent de l'information, pas d'un calcul de prix. [H] Peu d'intérêt pour un plugin.
- B2B : le texte vise les consommateurs. [H] Une boutique B2B pure n'est pas concernée. Le risque naît d'un canal ouvert aux particuliers.

## 2. Sylius 2.3.2 en standard [V] (vendor/sylius/sylius/src/Sylius, v2.3.2)
Couvert :
- Journal des prix : `PriceChangeLogger.php:28-37` écrit un `ChannelPricingLogEntry` (prix + originalPrice) à chaque changement ; observer sur `price` et `originalPrice` (`CreateLogEntryOnPriceChangeObserver.php`, `observedFields`).
- Config par canal : `ChannelPriceHistoryConfig.php:25-30` : période (défaut 30 j), visibilité (défaut true), taxons exclus.
- Calcul : `ProductLowestPriceBeforeDiscountProcessor.php` : actif seulement si `originalPrice` non nul et `price < originalPrice` ; prix mini sur la période.
- Exclusion de taxons, enfants inclus : `ProductVariantLowestPriceDisplayChecker.php:26-48`.
- Purge : `ChannelPricingLogEntriesRemover` ; admin : grille `ChannelPricingLogEntryGrid` ; API : `ProductVariantNormalizer.php:85` expose `lowestPriceBeforeDiscount`.
- Boutique : page produit (`prices/lowest_price_before_discount.html.twig`) et tableau de variantes.

Trous (constatés dans le code) :
1. **Fiches produit en liste** : `product/common/price.html.twig:1-4` barre `original_price` sans ligne « prix le plus bas ». Cartes liste/taxon/accueil concernées (carte : `twig_hooks/shared/product/card.yaml:31`). Confirmé aussi par `sylius-tier-pricing-plugin/README.md:259-265`.
2. **Panier** : `cart/.../unit_price.html.twig` barre l'originalPrice sans prix antérieur.
3. **Promotions panier** : non historisées, car seuls les `ChannelPricing` sont journalisés. Une remise panier affichée comme réduction n'a pas de prix antérieur. [H] Rarement annoncée comme « -x % » sur le prix.
4. Prix « compare at » saisi à la main dans `originalPrice` : le calcul se base sur le journal, mais le barré affiche quand même la valeur manuelle. [V] côté template, [H] côté risque.
5. Pas de prix par groupe de clients en standard, donc pas d'historique par groupe.
6. Pas d'export de preuve DGCCRF : grille admin seulement (`ChannelPricingLogEntryGrid`). Purge au-delà de la période : [H] à vérifier si la conservation couvre un contrôle a posteriori.
7. Aucune notion de produit nouveau (< 30 j) vérifiée : le processeur renvoie null sans entrée de journal. [H] comportement à confirmer.

## 3. Plugins de Thibaut qui touchent aux prix [V]
- **tier-pricing** : ne modifie jamais `ChannelPricing` (`TierPriceOrderProcessor.php`, en-tête) ; en canal consommateur, pas de barré pour un palier (`OrderItemOriginalPriceToDisplayExtension.php`) ; référence CJEU C-330/23 et section « Legal notes » (README:247-275) qui documente déjà les trous 1-2. Aucun contournement.
- **quick-order** : la matrice reprend le barré core + la ligne Omnibus (`README.md:26`, `MatrixBuilder`, tests dédiés).
- **agentic-feeds** : `sale_price` seulement si promotion catalogue et prix antérieur connu (`Feed/ItemBuilder.php:25-30`). Omnibus-safe par construction.
- **quote-request** : verrouille des lignes (négociées, pas de réduction annoncée). **punchout** : prix net transféré. **erp-flatfile** : importe des prix via `DoctrineCatalog` ; [H] ses écritures passent par Doctrine donc par l'observer, à tester.
- Démo : aucun traitement Omnibus dans `sylius-b2b-demo/src` ni `templates` (grep). B2B, hors champ.
- Conclusion : pas de contournement. Le seul vrai écart est celui du core (listes, panier), déjà documenté.

## 4. Écosystème
- `sylius/price-history-plugin` (Packagist) : Sylius 1.11/1.12 uniquement, dernière stable v1.0.0 avril 2023 ; intégré au core depuis. Obsolète pour 2.x. https://packagist.org/packages/sylius/price-history-plugin
- Blog Sylius : l'historique est une fonctionnalité core (open source). https://sylius.com/blog/the-eu-omnibus-directive-in-ecommerce/
- Plugins Sylius 2 tiers sur Omnibus : recherche rapide, aucun trouvé. [H] non exhaustif.

## 5. Recommandation : **ne pas créer de plugin Omnibus**
Arguments :
- Le core fait l'essentiel (journal, période, taxons, API, fiche produit).
- Cible B2B de la démo : article hors champ. Le public B2C des plugins est incertain (YAGNI).
- Valeur ajoutée possible faible : trous 1-2 = surcharge de 2 templates, pas un plugin.

Plus petit, si l'on veut agir (~3 pts au total) :
1. (1 pt) Page de guide « Omnibus avec Sylius 2.3 : ce que le core couvre, ce qu'il faut surcharger » (liste ci-dessus, avec avertissement « pas un conseil juridique »).
2. (1-2 pts) Éventuellement un snippet de surcharge `product/common/price.html.twig` + `unit_price.html.twig` dans le guide, ou dans la démo si un canal B2C apparaît.
3. (0,5 pt) Test dans erp-flatfile : un import de prix crée bien une entrée de journal.

Si « faire » quand même : plugin « Omnibus Compliance Pack » ≈ 8-13 pts (surcharge listes/panier, export CSV de preuve, prix par groupe, tests, doc FR/EN). Non recommandé.

## Limites
- Textes EUR-Lex/Légifrance non relus. À faire par Thibaut avant toute publication juridique.
