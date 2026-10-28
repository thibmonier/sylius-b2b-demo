# Tâches : US-207 : Indexer les pages vitrine : sitemap, hreflang, métadonnées et données structurées

## Informations US

- **Epic** : EPIC-10 (lien : EPIC-13)
- **Persona** : Camille (recruteuse IT) ; Hugo (intégrateur Sylius) ; Didier (DSI / CTO)
- **Story Points** : 5 (Should, **protégée**, registre du 2026-10-28)
- **Sprint** : sprint-33-indexation-et-depots
- **Worker proposé** : w207
- **Story** : [US-207](../../../backlog/user-stories/US-207.md)

## Résumé de la US

**En tant que** recruteuse ou développeur qui cherche des plugins Sylius B2B ou l'architecture d'une boutique Sylius 2  
**Je veux** trouver les pages vitrine de la démo dans un moteur de recherche avec un titre et une description pertinents  
**Afin de** découvrir son auteur et de vérifier son travail

## Règles et contraintes

- **Pas de maquette** : le rendu visuel des pages ne change pas (métadonnées, en-têtes, sitemap, données structurées). La seule porte de Thibaut pour le code est le **GO de la liste blanche et de l'activation** (T-207-03), noté au registre **avant d'activer** l'indexation ; le code et les tests s'écrivent avant, indexation désactivée par défaut. Les refontes US-204 à US-206 sont livrées (sprint 31 et 32) : la condition posée par Thibaut le 2026-10-27 est remplie, l'activation reste soumise à son GO.
- **Une seule liste blanche**, versionnée (classe ou fichier de configuration dans la démo), lue par `NoIndexSubscriber`, `RobotsController` et le générateur de sitemap ; aucune liste dupliquée. Pages : accueil (FR et EN), Plugins, Architecture, Preuve technique, guides d'intégration, À propos. **« Guides d'intégration »** : à trancher au cadrage entre les routes `app_demo_integrate` (`/integrer`), `app_demo_mcp_guide` (`/mcp`), `app_demo_override_guide` (`/surcharge`) et les pages du même type ; les parcours guidés interactifs (`/guides/{id}/{step}`, avec ses actions `advance` et `launch`) ne sont **pas** des guides d'intégration et restent `noindex` (états, GET sans effet de bord). La liste proposée est soumise à Thibaut (T-207-03).
- **Refus par défaut** : toute route absente de la liste répond `X-Robots-Tag: noindex, nofollow`, y compris une route ajoutée plus tard ; un test échoue si une route non listée apparaît dans le sitemap. Le `robots.txt` ne bloque que les zones sans intérêt à explorer (`/admin`, `/demo`, `/api`, compte, panier, commande, paramètres de tri) : ce qui doit sortir de l'index passe par l'en-tête, qu'un moteur doit pouvoir lire.
- **Mode privé** (porte Basic Auth) : inchangé, `noindex` et `Disallow: /` partout ; l'indexation n'existe qu'en mode public avec le réglage d'activation.
- **Hôte canonique** : sitemap, canonical, `hreflang` et Open Graph utilisent l'hôte canonique (`DEFAULT_URI`, `CANONICAL_IS_PUBLIC_HOST`), jamais l'hôte de la requête ; point « À corriger avant toute indexation » de `docs/seo-et-vitesse.md` (l'image de partage d'un produit suit l'hôte de la requête) : sans objet pour les pages vitrine, mais l'image par défaut est vérifiée au cadrage.
- **Canal B2C** : les pages vitrine sont aussi servies sur le canal B2C ; le cadrage décide si le sitemap ne liste que l'hôte public du canal B2B (proposition) et vérifie les `hreflang` sur les deux canaux.
- **Données structurées** (JSON-LD, bloc de données non exécutable : sans effet sur la CSP, à confirmer par `HardeningTest`) : `WebSite` (accueil), `Person` (accueil et « À propos »), `SoftwareSourceCode` (dépôts, sur Plugins et Architecture) ; **uniquement des faits vérifiables** (dépôts publics, profil LinkedIn de Thibaut déjà public) ; **plugins en recoupement et import ERP exclus** (source de la liste d'exclusion relevée au cadrage, jamais nommés dans un document public) ; aucune note, avis ni prix.
- **Ton neutre** : aucune mention de l'offre de l'éditeur Sylius ni comparaison dans les titres, descriptions et données structurées (test de contenu).
- **Sécurité** : le sitemap n'expose que les pages vitrine ; aucune route d'administration, aucun seuil ni secret dans les documents ou les tests publics.
- **Réglage d'activation** : un paramètre (nom fixé au cadrage) lu par les trois consommateurs, déclaré dans l'infrastructure avec `preserve()` (règle 16) ; **désactivé par défaut**, activé en production après le GO.
- **Tests existants** : la table ci-dessous liste ceux que la story touche (règle 28) ; chaque modification est décidée au registre **avant** l'action et citée dans la PR.
- **Disque** : aucun test ni rendu local avant 5 Go libres (6 Go pour la suite complète) ; avant, seuls le cadrage, les tests et le code sont écrits (règle 27 : sans exécution, la story n'est pas « rouge prouvé »).
- Fichiers communs (règle 21) : `translations/*.{fr,en}.yaml` des pages vitrine (clés de titre et de description seulement ; `translations/architecture.*` pour la phrase « ouverte au public sans être indexée ») : US-207 seule ; `docs/captures-vitrine.md` : sa section seulement ; `config/demo/proof.json` : orchestrateur (`bin/proof-rebase`).
- Tests uniquement par `WORKER_ID=w207 TEST_DB_NAME=b2b_w207 TEST_CACHE_DIR=var/cache-w207 bin/run-tests`, dans l'arbre partagé (règle 22).

## Table « invariants de test » (règle 28, préremplie au planning, à confirmer par T-207-01)

Aucune maquette ce sprint : la table croise la story avec les **tests existants touchés** par les en-têtes, `robots.txt`, le sitemap et les métadonnées. « Registre » = ligne à écrire **avant** la modification (auteur : orchestrateur ou worker, jamais « accepté par Thibaut » sans sa ligne).

| Test ou script existant | Ce qu'il fige aujourd'hui | Ce que la story change | Traitement | Registre avant l'action |
|-------------------------|---------------------------|------------------------|------------|-------------------------|
| `tests/Smoke/SeoMetadataTest::testNoIndexIsKeptOnEveryPage` (fournisseur `pages` : home, catégorie, plugins, login, mcp, à propos, intégrer, en FR et EN) | `X-Robots-Tag: noindex, nofollow` et aucune balise `robots` indexable sur **toutes** les pages du fournisseur | home, plugins, à propos, intégrer (et mcp si retenu) deviennent indexables | **Adapté** : le test garde catégorie et login (`noindex`) ; les pages listées passent à un nouveau test (pas de `noindex`) ; fournisseur relu (règle 27) | oui |
| `SeoMetadataTest::testEveryPageHasADescriptionAndShareMetadata`, `…DescriptionIsWrittenInTheLanguage…`, `…TwoLanguagesDoNotShare…`, `…CanonicalUrlKeepsThePage…` | description par page et par langue, trois descriptions distinctes (home, catégorie, plugins), canonical sans paramètres | titres et descriptions revus par page | **Inchangé** : doit rester vert ; les nouvelles descriptions restent distinctes | non |
| `tests/Functional/Demo/ArchitecturePageTest::testThePageStaysOutOfTheSearchEnginesAndHasItsOwnDescription` | `noindex, nofollow` sur `/fr_FR/architecture` et description contenant « architecture » | la page devient indexable | **Adapté** : la partie `noindex` est retirée et reprise par le nouveau test de liste blanche ; la partie description est gardée ; le nom de la méthode (« stays out ») est corrigé | oui |
| `ArchitecturePageTest::testTheTextsRespectTheEditorialCharter` et la traduction `architecture.*:85` (« ouverte au public sans être indexée ») | charte éditoriale des textes (ton, pas de comparaison) | la phrase est mise à jour (la démo est ouverte et ses pages vitrine sont indexées) | **Inchangé** : la nouvelle phrase respecte la charte ; texte relu contre `docs/ouverture-publique.md` | non |
| `tests/Functional/Demo/IntegratePageTest::testThePageStaysOutOfTheSearchEnginesAndHasItsOwnDescription` | idem, sur `/fr_FR/integrer` | la page devient indexable (si retenue comme guide d'intégration) | **Adapté** comme ci-dessus | oui |
| `tests/Functional/Demo/PublicOpeningTest::testPublicModeAsksSearchEnginesToStayAway` | `robots.txt` = `User-agent: *` puis `Disallow: /` seul ; accueil en `noindex` | `robots.txt` liste des zones exclues et `Sitemap:` ; accueil indexable | **Adapté** : nouveau nom et nouvelles assertions (zones exclues, ligne `Sitemap:`, accueil sans `noindex`, page non listée avec `noindex`) | oui |
| `PublicOpeningTest::testPrivateModeAlsoCarriesTheNoindexHeader` | mode privé : 401 et `noindex` | rien | **Inchangé** (invariant : le mode privé reste fermé) | non |
| `PublicOpeningTest::testRobotsTxtFollowsTheGateInPrivateMode` | mode privé : `robots.txt` derrière la porte, contient `Disallow: /` | `Disallow: /` reste la seule règle en mode privé | **Inchangé** ; vérifier que la sous-chaîne ne devient pas vraie par `Disallow: /admin` en mode public | non |
| `tests/Fixtures/bin/demo-mode-router.php` (production simulée : `/fr_FR/` en `noindex`, `/robots.txt` en `Disallow: /`) | réponses simulées de `bin/verify-public-mode` | accueil sans `noindex`, `robots.txt` et `sitemap.xml` nouveaux, une page non listée avec `noindex` | **Adapté** avec le test qui l'utilise (nom relevé au cadrage) | oui |
| `bin/verify-public-mode` contrôle 6 (« robots.txt disallows everything », « X-Robots-Tag noindex » sur `/fr_FR/`) | runbook de production : tout est exclu | contrôles remplacés : `robots.txt` n'est plus `Disallow: /` partout ; `noindex` présent hors liste blanche et absent sur les pages listées ; `sitemap.xml` répond | **Adapté** (runbook et script ensemble) | oui |
| `src/Demo/Vitrine/ReadmeChecker` (message « the site refuses robots ») et son test | liens du README non vérifiables parce que le site refuse les robots (avertissement) | pages listées désormais explorables | **À relever au cadrage** : probablement inchangé (avertissement pour les pages non listées) | à décider |
| `HardeningTest` (CSP, en-têtes de sécurité) et `PluginsPageRedesignTest`, `PluginsPageCompatibilityNoteTest`, `ProofPageRedesignTest`, `ArchitecturePageRedesignTest`, `HomeRedesignTest` | en-têtes de sécurité, structure des pages (cartes, sections, sélecteurs `data-*`) | un bloc `<script type="application/ld+json">` dans l'en-tête | **Inchangés** ; à relever au cadrage : aucun test ne compte les `<script>` ni ne les interdit | non |
| `templates/demo/mail.html.twig`, `mails.html.twig`, `punchout_simulator/simulator.html.twig` (balise `noindex` propre à l'outil de démo) | outils de démo en `noindex` | rien : hors liste | **Inchangés** (invariant : les outils de démo restent exclus) | non |

## Cadrage T-207-01 (2026-10-28, w207)

**Constats sur le code (lecture seule, production par `curl -sI`).**

- `NoIndexSubscriber` pose `noindex, nofollow` sur toute réponse principale du mode démo, sans regarder la route ; `RobotsController` répond `Disallow: /` dès que le mode démo est actif. Aucune distinction mode privé ou public : le mode privé est porté par la porte (`demo.basic_auth.enabled`), qui répond avant.
- En production (accueil, Plugins, Architecture, Preuve, À propos, Intégrer, MCP, Surcharge, Packs, Compatibilité) : toutes répondent 200 à un visiteur sans compte, avec `noindex, nofollow` ; `/` répond 302 vers `/fr_FR/` ; `sitemap.xml` répond 404.
- Canal : B2B par défaut, B2C seulement par le cookie `demo_channel` ; **mêmes URL** sur les deux canaux, donc pas de second hôte. Un robot sans cookie voit le canal B2B. Proposition : le cookie `demo_channel=B2C` force `noindex` (le canal Particuliers reste hors index, `docs/seo-et-vitesse.md` point (e)) ; sitemap et canonical sans changement.
- Hôte canonique : `CanonicalUrl` lit `DEFAULT_URI` ; `sitemap.xml`, `hreflang` et Open Graph l'utilisent aussi (jamais l'hôte de la requête). Image de partage par défaut : `share-preview.png` via `app_seo_canonical(asset(...))`, donc sur l'hôte canonique : correct pour les pages vitrine.
- Locales : `fr_FR` et `en_US` ; `x-default` = `fr_FR` (cible de `/`).
- Source de l'exclusion des données structurées : le catalogue `config/demo/plugins.yaml` (déjà source unique) ; il porte `listed: false` pour le seul plugin d'échange par fichiers, mais aucun drapeau pour les plugins en recoupement (leur classement est dans `docs/vitrine-plugins.md`, non lisible par le code). Proposition : un drapeau neutre `structured_data: false` par plugin dans `plugins.yaml` (défaut vrai), posé sur les plugins en recoupement et sur l'échange par fichiers ; aucun nom de plugin écrit dans le code ni dans un test public (le test lit le catalogue). La liste des plugins concernés est relevée dans `docs/vitrine-plugins.md` (classes « concurrent » et « à confirmer ») et n'est écrite que dans `plugins.yaml`.
- Titres : un `title` par page dans `translations/<page>.*.yaml` ; descriptions dans `translations/seo.*.yaml` (une clé par route, déjà présentes pour toutes les routes retenues). Aucun test ne fige un titre vitrine (seuls `<title>` de SVG sont testés).

**Table « invariants de test » confirmée (règle 28).** Elle reprend la table préremplie, avec ces changements :

| Test ou script | Constat du cadrage | Traitement confirmé | Ligne de registre demandée |
|----------------|--------------------|---------------------|----------------------------|
| `SeoMetadataTest::testNoIndexIsKeptOnEveryPage` | le fournisseur `pages` sert aussi les 4 autres tests de la classe (description, partage) : **ne pas le modifier** ; ajouter un second fournisseur pour la nouvelle assertion | méthode scindée : `noindex` gardé pour catégorie et login (nouveau fournisseur `privatePages`) ; pages listées reprises par le nouveau test de liste blanche | oui, avant |
| `ArchitecturePageTest::testThePageStaysOutOfTheSearchEnginesAndHasItsOwnDescription` | mélange `noindex` et description | partie `noindex` retirée (reprise par le test de liste blanche), description gardée, méthode renommée | oui, avant |
| `IntegratePageTest::testThePageStaysOutOfTheSearchEnginesAndHasItsOwnDescription` | idem | idem | oui, avant |
| `PublicOpeningTest::testPublicModeAsksSearchEnginesToStayAway` | `Disallow: /` seul et accueil `noindex` | remplacé par un test de `robots.txt` (zones exclues, `Sitemap:`) et un test d'en-tête (page listée sans `noindex` si activé ; tout `noindex` si désactivé) | oui, avant |
| `PublicOpeningTest::testRobotsTxtFollowsTheGateInPrivateMode` | sous-chaîne `Disallow: /` | inchangé ; le mode privé garde `Disallow: /` seul (test de non-régression ajouté : pas de ligne `Sitemap:` ni `Allow`) | non |
| `tests/Fixtures/bin/demo-mode-router.php` + `bin/verify-public-mode` | `VerifyPublicModeTest::testThePublicModeIsAcceptedWhenEveryCheckPasses` exige zéro `KO` ; le contrôle 6 actuel (`Disallow: /` partout) échouerait contre le nouveau routeur | fixture et script adaptés ensemble ; **`VerifyPublicModeTest` lui-même inchangé** ; le script gagne une option (`--indexing`) pour le cas réglage actif, le défaut gardant le contrôle actuel (tout exclu) | oui, avant |
| `RailwayPreservedVariablesTest::variablesSetByTheOwner` | liste des variables gardées par `preserve()` | ajout du nom du réglage d'activation (ajout d'une valeur, aucune assertion changée) | oui, avant |
| `ReadmeChecker` (message « the site refuses robots ») | le message vise les **autres** sites qui répondent 403, 429 ou 999 à un robot, pas la démo | **inchangé**, sans objet | non |
| `SeoMetadataTest::testTheCanonicalUrlKeepsThePage…`, `…TwoLanguagesDoNotShare…`, `…DescriptionIsWrittenInTheLanguage…` | restent vrais si les descriptions restent distinctes entre accueil, catégorie et Plugins | **inchangés** | non |
| Les autres lignes de la table préremplie | confirmées telles quelles (`HardeningTest`, tests de refonte : aucun ne compte ni n'interdit les `<script>`) | inchangés | non |

DoD de la table : 0 test existant modifié sans ligne de registre ; les adaptations ci-dessus sont les seules.

**Liste blanche proposée** (routes nommées, `fr_FR` et `en_US`, lues par les trois consommateurs depuis un seul paramètre versionné) : `sylius_shop_homepage`, `app_demo_plugins`, `app_demo_architecture`, `app_demo_proof`, `app_demo_about`, `app_demo_integrate`, `app_demo_mcp_guide`, `app_demo_override_guide`. Hors liste, restent `noindex` : compatibilité et packs (proposés en option, voir message), parcours guidés (`/guides…`), mentions légales, crédits des images, plateforme fictive, échange par fichiers, comptes, panier, commande, catalogue, fiches, outils de démo, santé.

**Réglage d'activation proposé** : variable d'environnement `DEMO_INDEXING` (`1` actif, absente ou `0` désactivée), paramètre `demo.indexing.enabled`, lu uniquement si le mode démo est actif **et** la porte privée fermée ; déclaré par `preserve()` dans `.railway/railway.ts`.

## Mesures locales (2026-10-28, w207)

Rendu du noyau en ligne de commande, `APP_ENV=prod`, base de test, `DEMO_INDEXING=1` sauf mention (`bin/render-page`, 9 pages) ; pa11y 9 (`WCAG2AAA`) et mesure de largeur (`puppeteer-core`, `isMobile` sous 500 px) sur le HTML servi avec `public/build` et `public/media` ; l'option `--targets` de `bin/measure-mobile` n'a pas été lancée (elle vise un serveur local complet).

- pa11y WCAG2AAA : « No issues found! » sur accueil, Plugins et Intégrer en FR et EN, et sur Architecture en FR (7 pages).
- Largeur : `scrollWidth` égal à la largeur du viewport à 320, 375 et 1280 px sur les 7 pages (21 mesures OK).
- `robots.txt` avec le réglage : zones exclues (administration, `/demo`, `/api`, panier, commande, compte, tri) et ligne `Sitemap:` sur l'hôte canonique ; sans : `Disallow: /` seul. `sitemap.xml` avec le réglage : 20 entrées avec `hreflang` FR, EN et `x-default` ; sans : 404.
- Données structurées (contrôle local, pas de validateur public) : JSON valide, `@context` `https://schema.org`, accueil `WebSite` + `Person`, Plugins 19 `SoftwareSourceCode` (27 plugins moins les 8 écartés par `structured_data: false`), Architecture 1 `SoftwareSourceCode` ; test `ShowcaseIndexingTest` : aucun plugin écarté, aucune mention d'ERP, ni note ni prix.
- Désactivé : aucun `hreflang` ni JSON-LD dans la page Plugins.

## Lieu de vérification

**Local** (production simulée : `bin/verify-public-mode` sur le routeur de fixtures), **image reconstruite** et **production le jour du déploiement** : pages publiques, sans compte, lecture seule (`curl -sI`, `robots.txt`, `sitemap.xml`). Validation des données structurées : locale (JSON-LD contrôlé par test) puis par un validateur public sur l'URL de production, résultat noté au registre.

## Fichiers principaux touchés

- `src/Demo/Http/NoIndexSubscriber.php`, `src/Demo/Controller/RobotsController.php`, nouveau `src/Demo/Seo/` (liste blanche, générateur de sitemap, contrôleur, données structurées), réglage d'activation (paramètre et déclaration d'infrastructure)
- `templates/shop/seo/metatags.html.twig` (titres, descriptions, canonical, Open Graph, `hreflang`, JSON-LD), `translations/*.{fr,en}.yaml` (clés de métadonnées des pages vitrine), `translations/architecture.{fr,en}.yaml` (une phrase)
- `bin/verify-public-mode`, `tests/Fixtures/bin/demo-mode-router.php`, `tests/` (nouveaux fichiers et tests existants listés ci-dessus)
- `docs/ouverture-publique.md`, `docs/durcissement-demo.md`, `docs/seo-et-vitesse.md`, `docs/dossier-de-go.md` (ligne « toujours noindex »), `docs/captures-vitrine.md` (section), `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-207-01 | [DOC] | Cadrage : `NoIndexSubscriber`, `RobotsController`, `metatags.html.twig`, routes vitrine relues ; **table « invariants de test » confirmée** (règle 28) et confrontée au périmètre par l'orchestrateur ; routes de la liste blanche proposées (guides d'intégration tranchés) ; canal B2C et hôte canonique ; source de la liste des plugins en recoupement et de l'import ERP à exclure ; nom du réglage d'activation ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-207-02 | [TEST] | État de référence « avant » : tests existants de la table lancés sur la branche (`SeoMetadataTest`, `PublicOpeningTest`, `IntegratePageTest`, `ArchitecturePageTest`, test de `bin/verify-public-mode`) dès 5 Go libres | 0,25 h | T-207-01 | Claude | 📅 disque |
| T-207-03 | [OPS] | 📅 Thibaut confirme la **liste blanche** (pages et guides d'intégration retenus) et l'**activation** de l'indexation ; GO noté au registre **avant** d'activer le réglage en production | 0,25 h | T-207-01 | Thibaut | ✅ (GO au registre, a3dc1f3) |
| T-207-04 | [TEST] | Tests rouges, **rouge prouvé (règle 27)**, commités seuls et cités dans la PR : page listée sans `noindex` avec titre, description, canonical et `hreflang` FR/EN ; sitemap limité à la liste (alternatives de langue, `x-default`, hôte canonique) ; données structurées valides sans plugin en recoupement ni import ERP ; page privée en `noindex` et hors sitemap ; **route non listée en `noindex` et test qui échoue si elle apparaît au sitemap** ; ton neutre ; mode privé fermé ; réglage désactivé = tout en `noindex` ; fournisseurs de données relus avant le commit | 1,25 h | T-207-01 | Claude | ✅ rouge prouvé (5f81ac9, 1bcec3e) |
| T-207-05 | [BE] | Liste blanche unique et versionnée ; `NoIndexSubscriber` (retire `noindex` sur la liste, refus par défaut), `RobotsController` (zones exclues, ligne `Sitemap:`, mode privé inchangé), réglage d'activation (désactivé par défaut) | 1,25 h | T-207-04 | Claude | ✅ |
| T-207-06 | [BE] | `sitemap.xml` servi par l'application : entrées de la liste, `hreflang` FR/EN et `x-default`, hôte canonique, référencé par `robots.txt` ; aucun fichier écrit hors base | 1 h | T-207-05 | Claude | ✅ |
| T-207-07 | [FE-WEB] | Titres et descriptions par page selon l'intention de recherche (« plugins Sylius B2B », « architecture Sylius 2 »), FR et EN, charte `PublicTextCharter` ; canonical, Open Graph et `hreflang` cohérents dans `metatags.html.twig` ; plugins en recoupement et import ERP absents des descriptions | 1 h | T-207-05 | Claude | ✅ |
| T-207-08 | [BE] | Données structurées JSON-LD : `WebSite`, `Person` (accueil, À propos), `SoftwareSourceCode` (Plugins, Architecture) ; faits vérifiables, exclusions ; aucun compteur écrit en dur | 0,75 h | T-207-07 | Claude | ✅ |
| T-207-09 | [TEST] | Tests existants de la table adaptés (une ligne de registre **avant** chaque modification, citée dans la PR), fixture `demo-mode-router.php` et `bin/verify-public-mode` ; `git diff --stat` des tests inchangés vide | 0,5 h | T-207-05 | Claude | ✅ |
| T-207-10 | [DOC] | `docs/ouverture-publique.md` (politique, section « robots.txt et noindex », étapes de déclaration du sitemap pour Thibaut), `docs/durcissement-demo.md`, `docs/seo-et-vitesse.md` (point « Indexation contrôlée » remplacé : pages vitrine, plus catalogue ni fiches), `docs/dossier-de-go.md` ; vérifications du runbook ; phrase de la page Architecture (FR et EN) | 0,75 h | T-207-06 | Claude | ✅ |
| T-207-11 | [TEST] | Suite ciblée par `bin/run-tests` (tests de la table, nouveaux tests), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 320 et 375 px sur les pages modifiées, JSON-LD validé localement ; commande exacte et dernière ligne citées | 0,75 h | T-207-08, T-207-09 | Claude | ✅ (mesures locales du 2026-10-28, voir « Mesures locales ») |
| T-207-12 | [DOC] | Captures : constat que le rendu des pages est inchangé (`bin/render-page`, une page listée FR) ; `docs/captures-vitrine.md` mis à jour seulement si une page change | 0,25 h | T-207-11 | Claude | ✅ rendu `bin/render-page` constaté (aucun changement visuel : métadonnées seules), `docs/captures-vitrine.md` inchangé |
| T-207-13 | [OPS] | Image reconstruite et conteneur neuf (règle 16 : réglage d'activation déclaré, `preserve()`, sitemap servi par l'application) ; **production le jour du déploiement**, lecture seule : `robots.txt`, en-têtes d'une page listée et d'une page non listée, `sitemap.xml`, `bin/verify-public-mode` (contrôles 1 à 12), validateur public des données structurées (résultat noté au registre), pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px ; mesures consignées | 0,75 h | merge de la PR, T-207-03 | Claude | 📅 déploiement |
| T-207-14 | [OPS] | 📅 Thibaut déclare l'URL du sitemap dans Google Search Console (étapes fournies dans `docs/ouverture-publique.md`) ; Claude ne touche à aucun compte | 0,25 h | T-207-13 | Thibaut | 📅 déploiement |
| T-207-15 | [REV] | Revue (ton neutre, plugins en recoupement et import ERP absents, aucun détail sensible, mode privé fermé, aucune liste dupliquée) et table « scénario → test ou preuve » remplie | 0,25 h | T-207-13 | Claude | 🔲 |

**Total estimé : 10 h** (15 tâches ; 9,5 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Page vitrine indexable (nominal) | T-207-04, T-207-07, T-207-13 | `ShowcaseIndexingTest::testAShowcasePageIsIndexableWithItsOwnMetadataWhenTheSettingIsOn` (10 pages × 2 langues : pas de `noindex`, canonical, `hreflang` FR/EN et `x-default`), `…TitleOfAShowcasePageIsShortAndNamesTheSite`, `…TitlesAndDescriptionsOfTheShowcaseDifferFromPageToPage` ; `curl -sI` en production |
| Sitemap limité aux pages vitrine (alternatif) | T-207-04, T-207-06 | `ShowcaseIndexingTest::testTheSitemapListsTheWhitelistWithTheAlternatesOfEachLanguage`, `…testTheWhitelistIsTheOneTheStoryFixes`, `PublicOpeningTest::testPublicModeWithTheIndexingSettingListsTheShowcaseOnly` ; `sitemap.xml` en production |
| Données structurées (alternatif) | T-207-04, T-207-08, T-207-13 | `ShowcaseIndexingTest::testTheHomeDescribesTheWebsiteAndItsAuthor`, `…testTheAboutPageDescribesItsAuthor`, `…testPluginsAndArchitectureDescribeVerifiableRepositories`, `…testThePluginsPageDescribesOnlyThePluginsTheCatalogueAllowsAndNoRating` ; validateur public, résultat au registre |
| Pages privées toujours exclues (erreur) | T-207-04, T-207-09 | `ShowcaseIndexingTest::testEverythingOutsideTheWhitelistStaysNoindexWhenTheSettingIsOn` (catégorie, connexion, panier, compte, parcours, outils de démo), `…testAProductPageStaysNoindexWhenTheSettingIsOn`, `…testTheConsumerChannelStaysNoindexWhenTheSettingIsOn`, `…testThePrivateModeStaysClosedWhateverTheSetting` ; `SeoMetadataTest` adapté (catégorie, login) |
| Nouvelle route non listée (erreur) | T-207-04 | `ShowcaseIndexingTest::testNoRouteOutsideTheWhitelistAppearsInTheSitemap` (chaque entrée du sitemap se résout vers une route de la liste) et `…testEverythingOutsideTheWhitelistStaysNoindexWhenTheSettingIsOn` |
| Ton neutre (erreur) | T-207-04, T-207-07 | `ShowcaseIndexingTest::testTheTextsOfAShowcasePageStayNeutral` (titre, description, JSON-LD) et `…testTheDescriptionsOfTheShowcaseNameNeither…` ; `testTheTextsRespectTheEditorialCharter` non modifié |
