# C — Thème, manques B2B, plugins communautaires (rapport brut, agent research-assistant, 2026-10-15)

Faits [S] et recommandations [R] de l'agent. Les points marqués « non confirmé » sont à vérifier avant usage.

## Démo Sylius Plus
- [S] v2.demo.sylius.com ne marque pas les fonctionnalités Plus par un badge. Chaque page porte un lien « Get a live demo of Sylius Plus ». demo.sylius.com propose des démos B2B, marketplace et B2C ; les extensions Plus (Returns, Loyalty, RBAC) y sont citées.
- Non confirmé : une convention d'icônes Plus côté shop ou admin, un bandeau de connexion de démo.
- [R] Utiliser un badge propre (« Plugin » / « Extension ») plutôt qu'imiter un marquage Plus.

## Thème Sylius 2.x
- [S] Le shop repose sur Bootstrap 5, Twig, Symfony UX (Stimulus, Twig Components) et Webpack Encore, avec des Twig Hooks (`twig_hooks/**/*.yaml`).
- Non confirmé : support d'AssetMapper. Rester sur Encore.
- [R] Pas de SyliusThemeBundle : surcharger les hooks et quelques templates ciblés.
- [R] Catalogue fermé : un listener de firewall redirige les visiteurs anonymes vers la connexion (sauf accueil, connexion, inscription), et les prix sont masqués dans les hooks. Shopware et Shopify utilisent le même schéma.

## Manques B2B (référence Sylius Plus B2B Suite)
Fonctionnalités de la suite : organisations multi-acheteurs, adresses et historique partagés, listes de prix par organisation ou groupe, commande rapide, réachat, multi-listes d'achat, RFQ.

Ordre d'importance [R] :
1. Prix et catalogue par client
2. Multi-adresses et multi-utilisateurs
3. Réachat
4. Listes d'achat / favoris
5. Champ PO number au checkout
6. Factures PDF et historique
7. Commandes récurrentes (à montrer en slide)
8. CMS (optionnel)

## Plugins communautaires
| Plugin | Compatibilité | Remarque |
|--------|---------------|----------|
| sylius/invoicing-plugin | v2.2.0, Sylius 1.2 à 2.2 selon la fiche | 2.3 non confirmé (facturx l'exige en ^2.2) |
| Wishlist officiel Sylius | v1.2.0 (2026-04-30), Sylius 2.0 à 2.2 | Licence à vérifier |
| bitbag/wishlist-plugin | Sylius 1.x seulement | Écarté |
| malina141/sylius-wishlist-plugin | Sylius ^2.1 | Maintenance à vérifier |
| Sylius CMS plugin | Documentation officielle | 2.3 non vérifié |
| Refund plugin, Monsieur Biz, Elasticsearch | — | Non vérifiés |

## Données B2B
- Aucune fixture B2B ouverte trouvée pour Sylius 2.
- [R] Ce qui rend un catalogue crédible :
  - unités de vente (pièce, boîte, carton, palette) et conditionnements ;
  - quantité minimale et multiple de vente ;
  - références fabricant (MPN), EAN/GTIN et SKU ;
  - normes (EN 388, CE, catégorie EPI) en attributs ;
  - fiches techniques PDF, poids et dimensions (palettes) ;
  - paliers de prix ;
  - données générées depuis un CSV versionné, sans lorem ipsum.
