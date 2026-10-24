# Tâches : US-189 : Refonte de la fiche produit dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : acheteur B2B ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, sacrifice : non sacrifiable, demande de Thibaut)
- **Sprint** : sprint-27-fiche-produit-et-erp-fictif
- **Worker proposé** : w189 (Orchestrateur pour la maquette)
- **Story** : [US-189](../../../backlog/user-stories/US-189.md)

## Résumé de la US

**En tant qu'** acheteur B2B  
**Je veux** une fiche produit aussi claire et chaleureuse que la nouvelle home  
**Afin de** trouver vite le prix, les paliers, la référence et l'action d'achat, comme sur un site réel

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (desktop et mobile) publiée par l'orchestrateur et **validée par Thibaut (T-189-04) avant toute intégration Twig**, comme pour US-186. Les tâches de code T-189-07 à T-189-13 sont 📅 tant que son accord n'est pas donné. Si la maquette demande des photos ou des éléments, Thibaut les fournit ou donne le GO sur une banque libre (T-189-05) avant le code.
- **Rendu réel d'abord** : la maquette est précédée de T-TECH-27-04 et T-TECH-27-05 (503 en mode prod local compris et documenté) ; les captures de la fiche sont faites sur un rendu du noyau en mode prod, sans HTML jetable.
- **Reprise d'US-186** : palette (marine, orange brûlé, sable, sarcelle), polices Fraunces et Inter **auto-hébergées** (CSP `font-src 'self'`, aucune police distante), jetons et composants de `b2b.scss`, AAA (WCAG2AAA), cibles de 44 px, `prefers-reduced-motion`.
- Canal B2B seulement : **la fiche B2C du cœur reste inchangée** (test) ; tous les nouveaux hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`. Blocs B2B conservés : référence, prix HT de la société, paliers de quantité, prix négocié, devis, liste d'atelier, badges de plugins.
- Visiteur anonyme : aucun prix B2B, appel à se connecter avec un compte de démo.
- **LCP mobile mesuré avant (T-189-02) et après (T-189-12), même protocole** (médiane de 5 passes, production en lecture seule) ; seuil : sous 2,5 s, ou au plus 10 % au-dessus de la mesure d'avant si celle-ci était déjà au-dessus. 360 et 1280 px sans défilement horizontal.
- Photos : banques libres seulement, fichier de crédits tenu à jour (un test échoue si une photo n'y figure pas).
- Ton neutre (`PublicTextCharter`), aucune comparaison avec Sylius Plus, aucun chiffre non vérifiable ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests de la démo uniquement par `WORKER_ID=w189 TEST_DB_NAME=b2b_w189 TEST_CACHE_DIR=var/cache-w189 bin/run-tests` ; preuve : `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Production, après le déploiement automatique de `main` par Railway** (lecture seule, sans connexion) : pa11y WCAG2AAA en FR et en EN sur trois fiches types (produit à paliers, produit sans palier, visiteur anonyme), 360 et 1280 px, LCP mobile « après ». Les vues connectées sont vérifiées en local et sur l'image reconstruite. **Mesures consignées dans ce fichier le jour du déploiement ; la story reste en review jusque-là.**

## Fichiers principaux touchés

- `templates/shop/theme_b2b/` (gabarits de la fiche : `product_*`), `config/packages/demo_theme_b2b.yaml` (hooks de la fiche)
- `assets/shop/styles/b2b.scss`, `assets/shop/images/` (photos éventuelles, crédits)
- `translations/` (domaine dédié à la fiche)
- `tests/Functional/Demo/`, `tests/Smoke/` (nouveaux tests ; tests de prix et de paliers existants relus)
- `config/demo/proof.json` (rafraîchi), `docs/theme-b2b.md`, `docs/images/product-*`, README

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-189-01 | [DOC] | Cadrage : inventaire de la fiche relu contre le code (hooks `product.show`, gabarits `product_*`, blocs B2B, canal B2C, tests existants de prix et de paliers) ; trois fiches types retenues ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-189-02 | [TEST] | Mesure « avant » en production (lecture seule) sur les trois fiches types : LCP mobile (médiane de 5 passes), poids et requêtes, largeur à 360 px, pa11y, FR et EN ; chiffres notés dans ce fichier | 0,75 h | T-189-01 | Claude | ✅ |
| T-189-03 | [FE-WEB] | Maquette Claude Design (desktop et mobile) : en-tête de fiche, galerie, référence, prix HT, paliers, action d'achat, états connecté, anonyme, rupture, B2C inchangé ; palette, Fraunces et Inter ; publiée en artefact privé, captures sur le rendu du noyau en mode prod | 2 h | T-189-01, T-TECH-27-04, T-TECH-27-05 | Orchestrateur | ✅ |
| T-189-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son accord** | 0,5 h | T-189-03 | Thibaut | ✅ |
| T-189-05 | [OPS] | 📅 Thibaut fournit les photos ou éléments que la maquette demande, ou donne le GO sur une banque libre (sans objet si la maquette n'en demande pas) | 0,25 h | T-189-04 | Thibaut | ⊘ sans objet (la maquette n\'ajoute aucune photo : les photos produit sont celles des fixtures) |
| T-189-06 | [DOC] | Photos ou éléments reçus : licence relue, optimisation, fichier de crédits (source, auteur, licence) ; conditionnelle | 0,5 h | T-189-05 | Claude | ⊘ sans objet (la maquette n\'ajoute aucune photo : les photos produit sont celles des fixtures) |
| T-189-07 | [TEST] | Tests rouges, commités seuls et cités dans la PR : fiche B2B (nom, référence, prix HT, paliers, action), anonyme sans prix, B2C inchangé, prix négocié et rupture, 360 px, polices auto-hébergées et CSP `font-src 'self'`, budget des images, charte | 1 h | T-189-04 | Claude | ✅ |
| T-189-08 | [FE-WEB] | Gabarits et hooks de la fiche, styles `b2b.scss` (jetons d'US-186 réutilisés), responsive 360 et 1280 px, AAA, cibles de 44 px, `prefers-reduced-motion` ; hooks sur le canal B2B seulement | 2,75 h | T-189-07, T-189-06 | Claude | ✅ |
| T-189-09 | [DOC] | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter`, `alt` des photos ; texte relu contre la maquette validée | 0,5 h | T-189-08 | Claude | ✅ |
| T-189-10 | [TEST] | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 360 px ; commande exacte et dernière ligne citées | 0,75 h | T-189-08, T-189-09 | Claude | ✅ |
| T-189-11 | [DOC] | Captures `docs/images/product-*` sur un rendu du noyau en mode prod, `docs/theme-b2b.md` et README mis à jour | 0,5 h | T-189-10 | Claude | ✅ |
| T-189-12 | [TEST] | Vérification en production après le déploiement (lecture seule, trois fiches types) : pa11y WCAG2AAA FR et EN, 360 et 1280 px, LCP mobile « après » au même protocole que T-189-02 ; **comparaison avant/après consignée le jour du déploiement** | 0,75 h | merge de la PR | Claude | ✅ |
| T-189-13 | [REV] | Revue (ton neutre, parité FR et EN, crédits complets, B2C inchangé) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-189-12 | Claude | ✅ |

**Total estimé : 11,5 h** (13 tâches ; 10,75 h pour Claude et l'orchestrateur, 0,75 h d'actions de Thibaut).

## Scénarios et preuves

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Fiche lisible et cohérente avec la home (nominal) | `ProductPageRedesignTest::testASignedInBuyerSeesTheEditorialLayoutInOrder` (3 fiches types, FR et EN), `::testTheBodySectionsAreThereOnlyWhenTheyHaveContent`, `::testTheNormPillComesFromTheAttributeOnly` ; captures `docs/images/product-*` |
| Visiteur anonyme (alternatif) | `ProductPageRedesignTest::testAVisitorSeesNoPriceAndIsAskedToSignIn` (3 fiches, FR et EN), `::testTheQuoteRequestIsCompactForABuyerAndTitledForAVisitor` ; `ChannelAccessSmokeTest::testTheProductPageInvitesAnAnonymousVisitorToSignIn` |
| Canal B2C (alternatif) | `ProductPageRedesignTest::testTheConsumerChannelKeepsTheStockProductPage` (aucun `data-pdp`, accordéon du cœur présent) |
| Prix négocié, paliers et rupture (alternatif) | `ProductPageRedesignTest::testTheTiersAreBeforeTheQuantityAndTheNegotiatedPriceIsSaid`, `::testAPageWithoutTiersHasNoEmptyTierBlock`, `::testAPageWithoutTiersHasNoTierBadge` ; `PriceBreaksAndHomeSmokeTest` ; la rupture : la disponibilité n'est dite que si le stock est suivi (`pdp/stock.html.twig`, aucun produit des fixtures n'est suivi : écart consigné) |
| Polices auto-hébergées et CSP (alternatif) | `HomeFontsTest` (polices et CSP `font-src 'self'`, partagées avec la home) ; la fiche n'ajoute ni police ni script en ligne (`ProductPageStylesTest`) |
| Débordement sur téléphone (erreur) | `ProductPageStylesTest::testGridsMayShrinkAndNothingIsWiderThanAPhone`, `::testALongNameWrapsInsteadOfWideningTheColumn` ; mesure navigateur 360, 768 et 1280 px sur 36 rendus et, en production le 2026-10-24, `scrollWidth` 360/360 et 1280/1280 sur la home et les trois fiches |
| Régression d'accessibilité ou de performance (erreur) | pa11y WCAG2AAA « No issues found! » en production (2026-10-24) sur les trois fiches FR et EN et sur la home FR et EN ; LCP mobile avant/après ci-dessous, sous 2,5 s |
| Photo hors crédits ou trop lourde (erreur) | sans objet : la fiche n'ajoute aucune photo (celles du catalogue sont créditées par `ImageCreditsPageTest` et seront refaites au sprint 28) |

## Inventaire de cadrage (T-189-01, 2026-10-24 ; BD locale, rien modifié)

- **Données** : 210 produits, tous à 1 variante, 1 image et 4 attributs (norme EPI, matière, certifications, conditionnement ; valeurs de 22 caractères au plus) ; stock non suivi ; 0 avis. Nom de 39 caractères en moyenne, 61 au plus ; description identique à la description courte (46 à 149 caractères). Paliers sur 12 produits seulement (les -001 de chaque famille), GPSR sur 5. Sections à masquer proprement sans donnée : attributs, avis, GPSR, paliers, vignettes, « souvent commandés avec ».
- **Marques réelles** : 77 des 210 noms (EPI-CASK 19, EPI-CHAU 17, EPI-GANT 24, EPI-VEST 14, OUTI-017/018, PAPE-013) ; liste dans le rapport à l'orchestrateur ; **aucune fixture modifiée sans GO de Thibaut**.
- **Photos** : 64 fichiers pour 210 SKU, 37 partagés par plusieurs SKU (17 au plus), 73 visuels neutres générés.
- **Trois fiches types** : OUTI-001 (court, paliers et prix négocié), EPI-VEST-001 (nom de 61 caractères, GPSR, paliers), FIXA-002 (sans palier, photo partagée). Visiteur et connecté, FR et EN, 360 et 1280 px.

**Maquette (T-189-03, T-189-04).** Maquette Claude Design validée par Thibaut le 2026-10-24 (https://claude.ai/artifact/9egcEkMJDxkTPJTj9re8zf) avant le premier gabarit ; la fiche a ensuite été resserrée sur sa demande après des captures avec les vraies données (#99, tests rouges `57ce592`, `4d6942a`, `5d80a9d`).

**T-189-13 : écart (soldé).** `php bin/proof-refresh` puis `php bin/proof-check` : `proof-check` signale +12 fichiers de tests (250 publiés, 262 réels) ; le rafraîchissement demande le réseau (`gh`) : fait par l'orchestrateur avant le merge (commits `chore(proof)` de #99).

## Preuves locales (T-189-10, 2026-10-24)

- **Rendu** : pages rendues par le noyau en ligne de commande, 12 combinaisons (3 fiches types, connecté et visiteur, FR et EN). Largeur : `scrollWidth` égale à la largeur d'écran et aucun élément plus large que l'écran dans `main`, à 360, 768 et 1280 px, sur les 36 rendus.
- **pa11y WCAG2AAA** : « No issues found! » sur les 12 pages (un titre `h3` sans `h2` avant lui a été corrigé en cours de route).
- **Qualité** : ECS `src tests` « [OK] No errors found », Rector « [OK] Rector is done! », PHPStan sur tout le dépôt sans erreur.
- **Suite ciblée** (Product, PriceBreaks, ChannelAccess, ThemeLandmarks, ClosedChannel, ThemeHooks, Gpsr, QuickOrder, ContainerFill, PluginBadge, Quote, AccessibleLabels, Catalog, Wishlist, Pages, Smoke, B2bTheme, Positioning) : 723 tests, un échec (`ChannelAccessSmokeTest`, la phrase « Connectez-vous pour voir vos prix » du plugin) corrigé ensuite ; relance verte.

## Mesures de production (avant : T-189-02 ; après : jour du déploiement)

Protocole : Chromium mobile 360 × 740, ratio 2, processeur x4, Slow 4G, cache désactivé, 5 passes, médiane ; lecture seule, visiteur anonyme (aucune connexion en production), 2026-10-24. CLS 0 sur les trois fiches, avant et après. **Avant / après** (LCP médian) : OUTI-001 1388 → 1444 ms (+4 %), EPI-VEST-001 1388 → 1408 ms (+1 %), FIXA-002 1776 → 1416 ms (-20 %) ; poids 375 à 378 Ko → 377 à 380 Ko, 15 → 13 requêtes. Seuil (sous 2,5 s) respecté sur les trois fiches. Mesure « après » faite avec le même protocole (script `lcp.cjs` recréé d'après la description ci-dessus ; ses valeurs ne sont pas comparables à celles d'un autre script).

| Fiche | Moment | LCP mobile (médiane de 5) | Poids / requêtes | 360 px | 1280 px | pa11y FR / EN |
|-------|--------|---------------------------|------------------|--------|---------|---------------|
| Produit à paliers (OUTI-001, visiteur) | avant | 1388 ms (1388, 1400, 1388, 1384, 2316) | 378 Ko / 15 | 360 / 360 | non mesuré | « No issues found! » FR et EN |
| Produit à paliers (OUTI-001, visiteur) | après (2026-10-24, après #99) | 1444 ms (1596, 1484, 1444, 1408, 1432) ; EN 1432 ms | 380 Ko / 13 | 360 / 360 | 1280 / 1280 | « No issues found! » FR et EN |
| Produit sans palier (FIXA-002, visiteur) | avant | 1776 ms (1776, 1796, 1904, 1208, 1368) | 375 Ko / 15 | 360 / 360 | non mesuré | « No issues found! » FR et EN |
| Produit sans palier (FIXA-002, visiteur) | après | 1416 ms (1540, 1436, 1408, 1416, 1380) | 377 Ko / 13 | 360 / 360 | 1280 / 1280 | « No issues found! » FR et EN |
| Visiteur anonyme, nom long (EPI-VEST-001) | avant | 1388 ms (1592, 1380, 1396, 1348, 1388) | 376 Ko / 15 | 360 / 360 | non mesuré | « No issues found! » FR et EN |
| Visiteur anonyme, nom long (EPI-VEST-001) | après | 1408 ms (1488, 1408, 1396, 1424, 1404) | 379 Ko / 13 | 360 / 360 | 1280 / 1280 | « No issues found! » FR et EN |
