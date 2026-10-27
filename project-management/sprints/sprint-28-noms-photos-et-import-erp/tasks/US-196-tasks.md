# Tâches : US-196 : Photos du catalogue cohérentes et sans marque

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) ; lead développeur d'une agence
- **Story Points** : 3 (Should, sacrifice : **n°2**)
- **Sprint** : sprint-28-noms-photos-et-import-erp
- **Worker proposé** : w196
- **Story** : [US-196](../../../backlog/user-stories/US-196.md)

## Résumé de la US

**En tant que** prospect ou évaluateur de la démo  
**Je veux** voir pour chaque produit une photo qui représente le bon type d'article et ne montre aucune marque  
**Afin que** le catalogue paraisse crédible

## Règles et contraintes (comme US-186)

- **Étapes obligatoires** : (1) recherche sur Pexels et Unsplash, **licence relue sur le site** pour chaque candidat ; (2) **PLANCHE** présentée à Thibaut (tâche 📅 de choix, T-196-04) ; (3) **GO explicite de Thibaut AVANT tout téléchargement** : les téléchargements demandent sa permission ; (4) puis optimisation WebP, `manifest.csv` et page Crédits.
- Avant le GO, **aucune image n'est enregistrée dans le dépôt** : la planche est une page locale de liens et d'aperçus servis par la banque.
- Objectif : une photo cohérente par famille (environ 25 à 30), sans marque ni logo lisible, sans produit montrant un autre type d'article ; WebP, dans le budget de poids de `ImageBudgetTest` ; aucun appel réseau au chargement des fixtures ni au runtime.
- `bin/fetch-product-images` ne gère qu'Openverse : T-196-05 l'adapte ou le remplace ; **aucune clé d'API ni fichier `.env*`** ; cache hors dépôt.
- **Dépend d'US-195** (noms affichés sur la planche, T-195-03). LCP du catalogue mesuré avant (T-196-01) et après (T-196-09), au même protocole.
- Tests uniquement par `WORKER_ID=w196 TEST_DB_NAME=b2b_w196 TEST_CACHE_DIR=var/cache-w196 bin/run-tests` ; **un seul cache de rendu**, `bin/check-disk` avant chaque rendu ; preuve : `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Pages publiques en production** (lecture seule, sans connexion) : catalogue, une fiche par famille échantillonnée, page Crédits. Mesures consignées le jour du déploiement.

## Fichiers principaux touchés

- `fixtures/images/*.webp`, `fixtures/images/manifest.csv` (colonnes `sku,file,source,creator,title,origin_url,license,license_url,fetched_at,scope,status`), `bin/fetch-product-images` (ou son remplaçant)
- `src/Demo/DataFixtures/ProductImageAttacher.php`, `src/Demo/Images/ImageManifest.php`, `src/Demo/Images/ImageCredits.php`, `src/Demo/Images/Fetch/`
- `tests/Unit/Demo/Images/` (budget, manifeste), `tests/Functional/Demo/ImageCreditsPageTest.php`, `docs/images-produits.md`, `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-196-01 | [DOC] | Cadrage : audit des images du 2026-10-24 relu (64 fichiers pour 210 produits, 37 partagés), liste des familles à couvrir (environ 25 à 30), `ImageManifest`, `ProductImageAttacher`, `ImageCredits`, `ImageBudgetTest`, `bin/fetch-product-images` (Openverse seulement) ; LCP mobile du catalogue mesuré **avant** en production (5 passes, protocole de w186) | 0,5 h | - | Claude | ✅ |
| T-196-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : manifeste (ligne sans licence libre sans attribution, produit sans image : échec nommant le SKU), chaque produit rattaché à la photo de sa famille, crédits identiques au manifeste, budget WebP et poids, aucun appel réseau au chargement des fixtures | 0,75 h | T-196-01 | Claude | ✅ |
| T-196-03 | [DOC] | Recherche sur Pexels et Unsplash : 2 à 3 candidats par famille, **licence relue sur le site pour chacun** (usage sans attribution obligatoire, pas de marque ni de logo lisible, pas d'article d'un autre type), noms de produits d'US-195 ; **PLANCHE** (page HTML locale de liens et d'aperçus de la banque, **aucune image enregistrée dans le dépôt**) présentée à Thibaut | 1,25 h | T-195-03 | Claude | ✅ |
| T-196-04 | [OPS] | 📅 Thibaut choisit une photo par famille sur la planche (une photo avec marque est écartée et remplacée) et donne le **GO explicite de téléchargement** ; GO noté au registre (T-TECH-28-09) | 0,5 h | T-196-03 | Thibaut | ✅ |
| T-196-05 | [BE] | Import à partir de la sélection : adapter ou remplacer `bin/fetch-product-images` (Openverse seulement) par un script qui lit un fichier de sélection (SKU ou famille, URL de la banque, auteur, licence), cache hors dépôt, aucun téléchargement sans l'option explicite ; réutilise `ImageProcessor` (WebP, 800 px) ; aucune clé d'API dans le dépôt | 1 h | T-196-02 | Claude | ✅ |
| T-196-06 | [DOC] | 📅 **Après le GO (T-196-04) seulement** : téléchargement des seules photos choisies, optimisation WebP, `fixtures/images/manifest.csv`, anciens fichiers orphelins retirés par `git rm`, `docs/images-produits.md` ; texte relu contre le manifeste | 0,75 h | T-196-04, T-196-05 | Claude | ✅ |
| T-196-07 | [BE] | Chargeur et crédits : rattachement par famille (`ProductImageAttacher`), lecture du manifeste (`ImageManifest`), page Crédits (`ImageCredits`) avec auteur, source et licence de chaque photo | 0,5 h | T-196-06 | Claude | ✅ |
| T-196-08 | [TEST] | Revue visuelle famille par famille (rendu du noyau en mode prod par `bin/render-page`, **un seul cache de rendu**, `bin/check-disk` avant chaque rendu) : aucune marque, aucun article hors sujet ; suite ciblée, PHPStan max sur tout le dépôt, ECS, Rector, lint ; commande exacte et dernière ligne citées | 0,5 h | T-196-07 | Claude | ✅ |
| T-196-09 | [TEST] | Vérification après déploiement : pa11y et largeurs (catalogue, une fiche, page Crédits), LCP mobile du catalogue **après** comparé à T-196-01 ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 🚫 (2026-10-27, production : la page Crédits des images déborde à 360 px, `scrollWidth` 1046 pour 360 en FR et en EN ; le reste est conforme, voir « Mesures de production ») |
| T-196-10 | [REV] | Revue (licences, ton neutre, aucune marque visible) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,25 h | T-196-09 | Claude | 🚫 (2026-10-27, bloquée par T-196-09 ; table remplie, `php bin/proof-check` vert, `proof-refresh` non relancé : il réécrit `config/demo/proof.json` sans lien avec ce contrôle) |

**Total estimé : 6,5 h** (10 tâches ; 6 h pour Claude, 0,5 h de Thibaut).

## Scénarios et preuves

Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Une photo cohérente par famille (nominal) | `ProductPhotosPerTypeTest::testEverySkuPointsToThePhotoOfItsType`, `testNoPhotoIsSharedBetweenTwoDifferentTypes` ; production 2026-10-27 : 7 fiches (casques de 4 couleurs, gilet, botte, casque vert) en 200 avec une image WebP servie (11 à 53 Ko), images distinctes |
| Crédits tenus (alternatif) | `ImageCreditsTest`, `ImageCreditsPageTest` ; production 2026-10-27 : la page Crédits (FR et EN) cite les six auteurs des photos du sprint 29 ; pa11y « No issues found! » ; **débordement horizontal à 360 px** (voir T-196-09) |
| Poids et format respectés (alternatif) | `ImageBudgetTest::testTheImagesStayUnderTheBudgetOfTheDirectoryAndOfEachImage`, `testEveryManifestRowIsCc0AndFullyFilled` |
| Photo avec marque visible (erreur) | revue visuelle de T-196-08 ; production 2026-10-27 : aucune occurrence de la liste noire sur 438 pages (voir US-195) |
| Licence non conforme ou famille sans photo (erreur) | `ImageManifestAuditorTest::testANonFreeLicenceIsRefusedAndNamed`, `ImageBudgetTest::testEveryProductOfTheCatalogueHasAnImage` |

## Registre de la planche et du GO

- 2026-10-25 : planche réalisée avec Claude Design (https://claude.ai/artifact/9egcEkMJDxkTPJTj9re8zf) et présentée à Thibaut ; choix de Thibaut et GO de téléchargement donnés avant tout enregistrement d'image (T-196-04). Mini-planche pour 7 types, puis vérification en pleine résolution de chaque candidate.
- 2026-10-25 : décision de Thibaut de ne pas retoucher les photos (les photos pixellisées sont remplacées) ; la veste de pluie puis la botte en caoutchouc passent en visuel neutre (#106, #107).

## Mesures de production (jour du déploiement)

_Contrôle du 2026-10-27 (T-196-09), production, lecture seule, sans connexion, après les remises à zéro de 03:00 des 2026-10-26 et 2026-10-27._

| Page | pa11y WCAG2AAA | 360 px | 1280 px |
|------|----------------|--------|---------|
| Catalogue `visserie` FR et EN, catégorie `epi` FR | « No issues found! » | 360 / 360 | 1280 / 1280 |
| Fiches `EPI-CASK-001`, `FIXA-001` (FR), `EPI-CASK-001` (EN) | « No issues found! » | 360 / 360 | 1280 / 1280 |
| Page Crédits des images, FR et EN | « No issues found! » | **1046 / 360 : déborde** | 1280 / 1280 |

Cause relevée (sans correction) : à 360 px, 16 éléments `li.border.rounded.p-3` de la liste des crédits mesurent 1034 px de large (bord droit à 1046 px).

**LCP mobile du catalogue** (`/taxons/visserie`, FR et EN). Deux mesures, car les deux scripts du dépôt de travail donnent des ordres de grandeur différents :
- script de la mesure « avant » d'US-198 (`scripts/lcp.cjs`, Chromium mobile 360 × 740, ratio 2, processeur x4, 1,6 Mbit/s, 150 ms, 5 passes) : FR **584 ms** (516/548/584/620/792), EN 704 ms (512/520/704/780/1284), 14 requêtes, 425 Ko, CLS 0 ; référence d'US-198 avant : FR 532 ms, EN 496 ms, soit +52 ms et +208 ms, dans la dispersion des passes ;
- protocole de w186 avec le cache désactivé : FR **1368 ms** (1344/1344/1368/1368/1372), EN 1368 ms (1352/1360/1368/1368/1372), 16 requêtes, 424 Ko, CLS 0 ; pas de mesure « avant » à ce protocole pour le catalogue.

Dans les deux cas le LCP reste sous le seuil de 2,5 s d'US-189. Le sprint 28 n'avait consigné aucune mesure « avant » de T-196-01 : la comparaison se fait avec la mesure « avant » d'US-198 (production du 2026-10-25, avant la remise à zéro).

`php bin/proof-check` (revue T-196-10) : « Proof up to date: 299 test files, 27 plugins. », « Compatibility up to date: 27 plugins. », « Packs up to date: 2 packs. »
