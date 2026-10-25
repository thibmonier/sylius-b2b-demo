# Tâches : US-198 : Refonte de la page de liste des produits dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **protégée : sacrifiée en dernier**)
- **Sprint** : sprint-29-liste-panier-et-photos
- **Worker proposé** : w198 (orchestrateur pour la maquette)
- **Story** : [US-198](../../../backlog/user-stories/US-198.md)

## Résumé de la US

**En tant qu'** acheteuse B2B  
**Je veux** une liste de produits aussi claire que la home et la fiche produit  
**Afin de** comparer vite les références, les prix HT de ma société et la disponibilité avant d'ouvrir une fiche ou d'ajouter au panier

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) publiée par l'orchestrateur et **validée par Thibaut (T-198-04) avant toute intégration Twig**, comme US-186 et US-189. Les tâches de code T-198-05 à T-198-11 sont 📅 tant que son GO n'est pas donné et noté au registre.
- **Rendu réel d'abord** : les captures de la maquette sont faites sur un rendu du noyau en mode prod (`bin/render-page`), avec **un seul cache de rendu** et `bin/check-disk` avant chaque rendu ; T-TECH-29-01 précède la première capture.
- **Reprise d'US-186 et d'US-189** : jetons de `b2b.scss`, Fraunces et Inter auto-hébergées (CSP `font-src 'self'`), AAA (WCAG2AAA), cibles de 44 px, `prefers-reduced-motion`.
- Canal B2B seulement : **la liste B2C reste inchangée** (test) ; tous les nouveaux hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`. Hooks existants conservés : `product_card_sku`, `product_card_from_price`, `product_card_price_note`, `product_card_meta`.
- Visiteur anonyme : aucun prix B2B, invitation à se connecter avec un compte de démo.
- **Vue liste** (pour comparer) : négociable, première partie sortie du périmètre si le budget de T-198-06 est dépassé (la grille refondue suffit à la story).
- **Cadrage des photos** : la carte réutilise le point focal d'US-197 (T-197-03) ; si T-197-03 n'est pas mergée, la carte garde le cadrage actuel et la dépendance est notée.
- **LCP mobile mesuré avant (T-198-02) et après (T-198-10), même protocole** (médiane de 5 passes, production en lecture seule) ; seuil d'US-189 : sous 2,5 s, ou au plus 10 % au-dessus de la mesure d'avant si celle-ci était déjà au-dessus. Le LCP « après » du catalogue de T-196-09 (sprint 28) peut servir de mesure d'avant s'il est fait au même protocole après la remise à zéro du 2026-10-26.
- Ton neutre (`PublicTextCharter`), aucune comparaison avec Sylius Plus, aucun chiffre non vérifiable ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w198 TEST_DB_NAME=b2b_w198 TEST_CACHE_DIR=var/cache-w198 bin/run-tests` ; preuve : `bin/proof-rebase` (T-TECH-29-02) ou `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Production, après le déploiement automatique de `main`** (lecture seule, sans connexion) : une catégorie de plus d'une page, une recherche, une recherche sans résultat, visiteur anonyme, FR et EN : pa11y WCAG2AAA, 360 et 1280 px, LCP mobile « après ». Les vues connectées (prix HT de la société, stock) sont vérifiées en local et sur l'image reconstruite. **Mesures consignées dans ce fichier le jour du déploiement ; la story reste en review jusque-là.**

## Fichiers principaux touchés

- `templates/shop/theme_b2b/` (gabarits de la liste : `product/index`, `product/_box`, filtres, pagination, état vide), `config/packages/demo_theme_b2b.yaml` (hooks)
- `assets/shop/styles/b2b.scss`, `translations/` (domaine dédié à la liste)
- `tests/Functional/Demo/`, `tests/Smoke/` (nouveaux fichiers ; tests existants du catalogue, des filtres et du tri relus)
- `config/demo/proof.json` (rafraîchi), `docs/theme-b2b.md`, `docs/images/product-list-*`, README

## Cadrage (T-198-01, relu contre le code le 2026-10-25)

**Chaîne de rendu de la liste** : route `sylius_shop_product_index` (`/{locale}/taxons/{slug}`, grille `sylius_shop_product`, tri par défaut `position`, limites 9/18/27, filtre de recherche `shop_string` sur `translation.name` seulement) ; gabarit `templates/bundles/SyliusShopBundle/product/index.html.twig` (titre avec le nom de la catégorie, US-078) ; hook `sylius_shop.product.index.content.body.main` : en-tête (priorité 300), filtres (200), grille (100), pagination (0) ; le gabarit de la grille est une copie surchargée (`product/index/content/body/main/products.html.twig`, US-167 : passe `position` à chaque carte pour les premières vignettes en `eager`) ; la carte est le composant `sylius_shop:product:card` (`product/common/card.html.twig`) qui ouvre le hook `sylius_shop.shared.product.card`.

**Hooks B2B existants (à conserver)**, tous avec `not is_granted('DEMO_CONSUMER_CHANNEL')`, dans `config/packages/demo_theme_b2b.yaml` :

| Hook | Bloc | Priorité | Gabarit | Contenu |
|------|------|----------|---------|---------|
| `…shared.product.card.details` | `b2b_sku` | -10 | `product_card_sku` | référence (`b2b-sku`) |
| `…shared.product.card.details` | `b2b_meta` | -20 | `product_card_meta` | conditionnement et stock **si la variante est suivie** |
| `…shared.product.card.prices` | `b2b_from_price` | -20 | `product_card_from_price` | ligne prix de la société (écart > 5 %) et « dès X » |
| `…shared.product.card.prices` | `b2b_price_note` | -10 | `product_card_price_note` | mention HT |
| `sylius_shop.product.index.content.body.main` | `b2b_preload` | 150 | `product_list_preload` | précharge variantes, prix, images (`ProductListPreloader`), sans rendu |

Autres blocs de la carte posés par les plugins : liste d'envies (`add_to_wishlist`, priorité -10 dans `tmonier_plugins_overrides.yaml`) ; visibilité des prix par le plugin d'accès au canal (voir `HiddenPriceHookablesTest`).

**Tests existants à relire/étendre** : `tests/Functional/Demo/CatalogThumbnailsTest` (premières vignettes `eager`, la première en priorité haute, au-delà `lazy`, catégorie sans résultat), `tests/Functional/ProductListQueryCountTest` (requêtes indépendantes du nombre de produits, prix « dès »), `tests/Unit/Theme/B2bThemeTest` et `PriceBreaksTemplatesTest` (rendu des quatre gabarits de carte), `tests/Functional/Demo/HiddenPriceHookablesTest` (anonyme B2B sans prix, B2C avec), `tests/Smoke/TaxonListingSmokeTest`, `tests/Functional/Demo/AccessibleLabelsTest`, `ThemeLandmarksTest`, `tests/Smoke/PagesTest` et `SeoMetadataTest`.

**Constats du cadrage (à arbitrer avant la maquette)** :
1. **Aucun état de stock n'existe en base** : les 210 variantes locales ont `tracked = 0` et `on_hand = 0`, et `product_card_meta` n'affiche le stock que pour une variante suivie. La carte actuelle ne montre donc jamais « En stock » ni « Sur commande », alors que le scénario nominal l'exige. Décision requise : suivre le stock de quelques variantes dans les fixtures (impact sur le panier, le devis, les paliers et la remise à zéro), ou décrire l'état du stock autrement.
2. **Le tri « moins cher / plus cher » porte sur le prix du canal** (`channelPricing.price`), pas sur le prix HT de la société : à dire dans la maquette ou à corriger.
3. **La recherche est un filtre de la grille d'une catégorie**, sur le nom seulement : il n'y a pas de page de recherche globale, et la référence (SKU) n'est pas cherchée. Les « trois pages types » sont donc une catégorie, la même avec un filtre, et la même sans résultat.
4. **État vide** : simple message du cœur (`sylius.ui.no_results_to_display`), sans lien de retour aux catégories ; le scénario exige un retour proposé.
5. **Pagination** : macro du cœur `pagination.simple` (Bootstrap 5) ; la conservation du tri et l'annonce de la page courante sont à tester (rouge) avant de toucher le gabarit.
6. Peu de paliers en base : seulement 12 produits sur 210 ont des règles de palier (2 sur la visserie) ; la maquette montre donc surtout des cartes sans « dès ».

**Trois pages types retenues** : A (catégorie de plus d'une page : Visserie & Boulonnerie, 30 produits, 4 pages), B (recherche : la même catégorie filtrée sur « vis »), C (recherche sans résultat). Anonyme, FR et EN ; les vues connectées (prix HT d'Acme, stock) en local.

**Plan des tests (T-198-05)** : ceux de la fiche, plus : tri conservé au changement de page (lien de pagination contient `sorting`), état vide avec lien de retour, absence de nouveau hook sur B2C (liste des hooks du canal B2C inchangée), unicité d'un seul `h1`.

**État hors base (règle 16)** : aucun fichier ni cache écrit hors base attendu pour cette story (gabarits, styles et traductions du dépôt, polices déjà auto-hébergées) ; à reconfirmer à l'intégration.

**Données de la maquette** : `<scratchpad>/w198/maquette-data.json` (12 produits de la visserie, prix HT d'Acme calculés d'après les règles de palier). Captures du rendu actuel (T-198-03) : non faites, disque sous le seuil (voir le rapport).

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-198-01 | [DOC] | Cadrage : inventaire de la liste relu contre le code (hooks `product_card_*`, gabarits de la grille, filtres, tri, pagination, recherche, état vide, canal B2C, tests existants du catalogue) ; trois pages types retenues (catégorie de plus d'une page, recherche, recherche sans résultat) ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-198-02 | [TEST] | Mesure « avant » en production (lecture seule) sur les trois pages types : LCP mobile (médiane de 5 passes), poids et requêtes, largeur à 360 px, pa11y, FR et EN ; chiffres notés dans ce fichier (reprise possible de T-196-09 s'il est fait au même protocole) | 0,75 h | T-198-01 | Claude | ✅ |
| T-198-03 | [FE-WEB] | Maquette Claude Design (bureau et téléphone) : en-tête de catégorie, filtres et tri, carte produit (photo, nom, référence, prix HT de la société, paliers ou « à partir de », stock, action rapide), grille et vue liste, pagination, état vide, visiteur anonyme, B2C inchangé ; jetons de `b2b.scss`, Fraunces et Inter ; publiée en artefact privé, captures sur le rendu du noyau en mode prod | 2 h | T-198-01, T-TECH-29-01 | Orchestrateur | 🔲 |
| T-198-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-198-03 | Thibaut | 📅 |
| T-198-05 | [TEST] | Tests rouges, commités seuls et cités dans la PR : cartes B2B (photo, nom, référence, prix HT, stock), anonyme sans prix avec invitation, tri conservé au changement de page et page courante annoncée, B2C inchangé, état vide, 360 px, polices auto-hébergées et CSP, charte, cibles de 44 px | 1 h | T-198-04 | Claude | ✅ |
| T-198-06 | [FE-WEB] | Gabarits et hooks de la liste, styles `b2b.scss` (jetons d'US-186 réutilisés), responsive 360 et 1280 px, AAA, `prefers-reduced-motion` ; hooks sur le canal B2B seulement ; vue liste en dernier (négociable) ; point focal d'US-197 si T-197-03 est mergée | 3 h | T-198-05 | Claude | ✅ |
| T-198-07 | [DOC] | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter`, `alt` des photos ; texte relu contre la maquette validée | 0,5 h | T-198-06 | Claude | ✅ |
| T-198-08 | [TEST] | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 360 px ; commande exacte et dernière ligne citées | 0,75 h | T-198-06, T-198-07 | Claude | ✅ |
| T-198-09 | [DOC] | Captures `docs/images/product-list-*` sur un rendu du noyau en mode prod, `docs/theme-b2b.md` et README mis à jour | 0,5 h | T-198-08 | Claude | ✅ |
| T-198-10 | [TEST] | Vérification en production après le déploiement (lecture seule, trois pages types) : pa11y WCAG2AAA FR et EN, 360 et 1280 px, LCP mobile « après » au même protocole que T-198-02 ; **comparaison avant/après consignée le jour du déploiement** (le catalogue n'affiche les nouvelles photos qu'après la remise à zéro de 03:00 suivante) | 0,75 h | merge de la PR | Claude | 📅 |
| T-198-11 | [REV] | Revue (ton neutre, parité FR et EN, B2C inchangé) et table « scénario → test ou preuve » remplie ; `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-198-10 | Claude | 📅 |
| T-198-12 | [DB] | Stock suivi dans les fixtures (décision de Thibaut, registre 242b30e) : `tracked = 1` sur toutes les variantes, quantités fictives déterministes d'après le SKU (`DemoStockLevel`, environ 5 % de ruptures, environ 10 % de stocks faibles), `on_hold = 0`, fixture `demo_catalog_stock` (priorité -85, après les commandes et avant l'échange ERP) ; test rouge commité seul, import ERP vérifié cohérent | 1 h | T-198-01 | Claude | 🔄 |

**Total estimé : 12 h** (12 tâches ; 11,5 h pour Claude et l'orchestrateur, 0,5 h de Thibaut ; T-198-12, 1 h, prise sur la marge).

## Scénarios et preuves

Chaque scénario Gherkin de la fiche est relié à une tâche de test. Une ligne vide laisse la story en review.

| Scénario | Tâche de test | Test ou preuve |
|----------|---------------|----------------|
| Liste lisible et cohérente avec la fiche produit (nominal) | T-198-05 (rouge), T-198-08 | |
| Visiteur anonyme (alternatif) | T-198-05, T-198-10 (production, visiteur) | |
| Filtres, tri et pagination (alternatif) | T-198-05, T-198-08 | |
| Canal B2C (alternatif) | T-198-05 | |
| Recherche sans résultat (erreur) | T-198-05, T-198-10 | |
| Débordement sur téléphone (erreur) | T-198-05, T-198-08 (`bin/measure-mobile`), T-198-10 (360 px) | |
| Régression d'accessibilité ou de performance (erreur) | T-198-08 (pa11y local), T-198-10 (pa11y AAA FR et EN, LCP) | |

## Réalisation (GO de Thibaut sur la maquette, registre 8028523)

Tests rouges commités seuls (0211103), puis le code (68b917d). Écarts assumés avec la maquette : la ligne de prix de la société garde le texte existant « dès N pièces » (l'unité de vente varie selon le produit, la maquette ne montre que des boîtes) ; « Stock faible : N unité(s) » au lieu de « boîtes » (même raison) ; tri et nombre par page sont des champs d'un formulaire GET avec un bouton « Appliquer » (pas de changement de contexte à la sélection, fonctionne sans JavaScript) ; l'affichage grille/liste est côté serveur (`?view=list`) ; catégories masquées sous 992 px (comme la maquette sous 900 px). Le bouton « Ajouter au panier » passe par `ListAddToCartController` et l'ajouteur du plugin de commande rapide.

pa11y WCAG2AAA local (rendu du noyau, Claire, `APP_ENV=test`) : « No issues found! » sur catégorie, vue liste, sans résultat (FR) et page 2 (EN). `scrollWidth` 360 et 1280 sans débordement. Restent T-198-08 (suite ciblée, PHPStan, ECS, Rector rejoués au dernier état), T-198-09 (captures et documentation) et T-198-10/11.

## Mesures de production (avant : T-198-02 ; après : jour du déploiement)

_Avant : consigné le 2026-10-25 (T-198-02)._ Protocole d'US-189 : Chromium mobile 360 × 740, ratio 2, processeur x4, Slow 4G, cache désactivé, 5 passes, médiane ; visiteur anonyme, lecture seule, sans connexion. _Après : à consigner le jour du déploiement._

**Réserve** : ces chiffres sont pris **avant la remise à zéro de 03:00 du 2026-10-26** ; les noms et photos du sprint 28 ne sont pas encore en production. Ils sont à refaire au même protocole après cette remise à zéro avant de servir de référence au seuil d'US-189 (ou à reprendre de T-196-09 s'il est fait au même protocole).

Pages types (production, `https://sylius-b2b.thibaut-monier.fr`) : A = `/{locale}/taxons/visserie` (30 produits, 4 pages à 9 par page) ; B = A avec `?criteria[search][value]=vis` ; C = A avec `?criteria[search][value]=zzzz` (aucun résultat).

| Page | Langue | LCP médian (5 passes, ms) | Passes (ms) | Poids (Ko) | Requêtes | CLS |
|------|--------|---------------------------|-------------|------------|----------|-----|
| A catégorie | FR | 532 | 504/528/532/648/656 | 798 | 19 | 0,000 |
| A catégorie | EN | 496 | 476/496/496/504/524 | 728 | 18 | 0,000 |
| B recherche « vis » | FR | 520 | 476/512/520/544/548 | 686 | 19 | 0,000 |
| B recherche « vis » | EN | 504 | 468/500/504/512/684 | 379 | 14 | 0,000 |
| C sans résultat | FR | 492 | 472/472/492/500/500 | 380 | 14 | 0,000 |
| C sans résultat | EN | 488 | 472/480/488/504/508 | 379 | 14 | 0,000 |

- **pa11y WCAG2AAA** : « No issues found! » sur les six pages (A, B, C en FR et en EN).
- **1280 px** : `scrollWidth` 1280, aucun élément ne dépasse. **360 px** : `scrollWidth` 360 sur A, B et C, aucun défilement horizontal ; le script signale cinq éléments dont le bord droit dépasse 360 px (`DIV`, `STRONG`, `UL.list-unstyled`, `LI`, `A.text-white`), cause non identifiée (probablement un panneau hors-champ, la page ne défile pas) : à confirmer avec `bin/measure-mobile` en local au cadrage de T-198-08.
- **Cartes** : les photos sont celles d'avant la remise à zéro ; le LCP se joue sur du texte et les premières vignettes.

