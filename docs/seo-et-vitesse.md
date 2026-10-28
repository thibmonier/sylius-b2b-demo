# SEO de base et vitesse d'affichage mobile (US-078)

Application de l'US-078 (sprint 15). La démo reste **hors des moteurs de recherche** (`noindex`, voir [ouverture-publique.md](ouverture-publique.md)) : on soigne ce qu'un visiteur voit quand il colle le lien dans un message, et le temps d'affichage sur un téléphone. Mesures de référence : `project-management/audits/2026-10-18-pre-ouverture/uiux/lighthouse.md`.

## Ce qui est en place

| Élément | Où | Comportement |
|---------|----|--------------|
| Description de page | `templates/shop/seo/metatags.html.twig`, `App\Demo\Seo\MetaDescription` | Produit et catégorie : leur propre texte (sans balises, 160 caractères au plus, coupé à un mot). Autres pages : une phrase par route dans `translations/seo.{fr,en}.yaml` (`description.<nom de route>`), `description.default` pour les routes absentes |
| Aperçu de lien | même gabarit | `og:title`, `og:description`, `og:url`, `og:image`, `og:locale`, `og:site_name`, `twitter:card` (`summary_large_image`) et `twitter:image`. Image : celle du produit sur sa fiche, sinon `assets/shop/images/share-preview.png` (1200 x 630) |
| Adresse canonique | `App\Demo\Seo\CanonicalUrl` | `<link rel="canonical">` et `og:url` sur l'hôte de `DEFAULT_URI`, chemin sans paramètres de requête, sauf le numéro de page d'une liste (à partir de la page 2) |
| `noindex` | `NoIndexSubscriber`, `RobotsController` | **Inchangé** : `X-Robots-Tag: noindex, nofollow` sur chaque réponse et `robots.txt` en `Disallow: /`. Aucune balise `meta robots` n'est ajoutée |
| Titre de la catégorie | `templates/bundles/SyliusShopBundle/product/index.html.twig` | « Visserie \| Fournitures Pro — B2B » (il n'y avait que le nom de la boutique) |

Ajouter une page décrite : une ligne `description.<nom de route>` dans les deux fichiers `seo.*.yaml` (`%name%` est le nom du produit ou de la catégorie quand il y en a un).

## Vitesse d'affichage

Ce que la mesure a montré (Lighthouse 12.8.2, profil mobile, démo locale) : l'élément du LCP est un texte (titre de la fiche, bandeau, accroche), affiché en moins d'une seconde en réel. Le LCP « simulé » de 5,7 à 7,4 s venait des octets demandés avant ce premier affichage : **723 Ko de polices** (deux fichiers Inter complets préchargés, dont l'italique que la boutique n'utilise pas) sur 1,0 à 1,2 Mo au total.

| Changement | Fichier | Effet |
|------------|---------|-------|
| Police : sous-ensemble latin d'Inter (66 Ko, axe de graisse conservé) à la place des deux fichiers du thème officiel (344 Ko et 379 Ko) ; police de repli aux mesures d'Inter (pas de décalage au changement) ; préchargement retiré ensuite (T-TECH-31-11, ligne suivante) | `assets/shop/fonts/InterVariable-latin.woff2`, `assets/shop/styles/fonts.scss`, `templates/bundles/SyliusShopBundle/shared/layout/base/metatags.html.twig` | -657 Ko sur chaque page |
| T-TECH-31-11. LCP mobile de la home : le h1 attend les trois feuilles de style ; la police Inter préchargée et la photo du hero en priorité haute se partageaient la bande passante avec elles (Slow 4G). Photo préchargée seulement à partir de 992 px (`media`), sans `fetchpriority` propre ; aucune police préchargée (repli aux mesures d'Inter : CLS 0,002) | `templates/shop/theme_b2b/home_hero_preload.html.twig`, `_home_photo.html.twig`, `templates/bundles/SyliusShopBundle/shared/layout/base/metatags.html.twig` | Mesuré sur la production avec le protocole de w186 (médiane de 5 passes, 360 px, Slow 4G, CPU x4, page réécrite en local) : 1 696 ms avant, 1 332 ms après (- 21 %) ; à 1 280 px, 1 940 ms avant, 1 664 ms après. Test : `HomeFirstViewPriorityTest` |
| Images des cartes : `width` et `height` (3:4), `srcset` 300 et 600 px, `sizes`, `loading="lazy"`, `decoding="async"` ; attribut parasite `product="..."` retiré | `templates/bundles/SyliusShopBundle/shared/components/main_image.html.twig` | les images sous l'écran ne sont pas demandées au chargement |
| Fiche produit : image principale en 600 px (au lieu de 1200 x 1600), `srcset` 300 / 600 / 1200, `fetchpriority="high"`, dimensions ; vignettes paresseuses, sans le `data-large-thumbnail` que rien ne lisait | `templates/bundles/SyliusShopBundle/product/show/content/info/overview/images*.twig` | |
| US-167. Catalogue : les quatre premières vignettes de la liste en `loading="eager"`, la première avec `fetchpriority="high"` (c'est elle qui fait le LCP) ; les suivantes restent paresseuses. La liste transmet la position de la carte, le bloc image la lit ; hors liste (accueil, avis), rien ne change | `templates/bundles/SyliusShopBundle/product/index/content/body/main/products.html.twig`, `.../shared/product/card/details/image.html.twig` | première image demandée sans attendre le défilement |
| US-167. Cache : `/build/*` (nom à empreinte) en `public, max-age=31536000, immutable` ; `/bundles/*` et `/media/*` restent à 7 jours (pas d'empreinte garantie) | `docker/Caddyfile` | les visites suivantes ne revalident plus les scripts ni les styles |
| Image absente : visuel de repli en SVG intégré à la page (aucune requête qui puisse échouer), mêmes dimensions | `main_image.html.twig` | |

Le sous-ensemble de police a été produit avec `pyftsubset` (fonttools) à partir du fichier du thème : plage Basic Latin, Latin-1, `œ`, ponctuation générale, `€`, `™`, flèches, `≤ ≥`. Un caractère hors plage s'affiche dans la police de repli. Inter est sous licence SIL OFL 1.1 (redistribution autorisée avec la police réduite). Pour le régénérer :

```bash
pip install fonttools brotli
pyftsubset vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/assets/fonts/InterVariable.woff2 \
  --unicodes="U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2190-2199,U+2212,U+2215,U+2264-2265,U+FEFF,U+FFFD" \
  --flavor=woff2 --layout-features='kern,liga,calt,ccmp,locl,mark,mkmk,tnum,case' \
  --output-file=assets/shop/fonts/InterVariable-latin.woff2
```

## Mesures

Lighthouse 12.8.2, profil mobile, démo locale (2026-10-18), avant puis après les changements :

| Page | Performance | LCP | Octets | SEO |
|------|-------------|-----|--------|-----|
| Accueil | 76 puis **98** | 7,5 s puis **2,2 s** | 1 210 Ko puis 208 Ko | 58 puis 66 |
| Catégorie Quincaillerie | 76 puis **97** | 7,2 s puis **2,2 s** | 1 175 Ko puis 526 Ko | 58 puis 69 |
| Fiche | 74 puis **99** | 6,9 s puis **2,1 s** | 1 049 Ko puis 405 Ko | 58 puis 69 |

Le décalage de mise en page (CLS) reste sous 0,02 (0,012 au plus). Le SEO ne dépasse pas 69 : la seule vérification en échec est « page explorable », volontairement (le `noindex` est conservé). Les trois pages sont celles mesurées par l'audit du 2026-10-18 (même méthode).

La mesure du LCP mobile du catalogue après US-167 (`bin/measure-mobile`) se rejoue sur le rendu local ; la production se vérifie après déploiement (la règle de cache ne se voit que dans l'image, validée par la CI).

## Mesurer

```bash
CHROME_PATH=/Applications/Chromium.app/Contents/MacOS/Chromium \
  npx --yes lighthouse@12.8.2 <url> --quiet --chrome-flags="--headless=new" \
  --output=json --output-path=<fichier>
```

Sur la démo locale (`http://localhost:8080`, image reconstruite après un changement de gabarit : `docker compose build web && docker compose up -d web`). Le LCP du profil mobile est simulé (4G lente, processeur ralenti 4 fois) : il se lit comme une charge d'octets avant le premier affichage, donc il baisse quand on retire des octets des premières requêtes.

## Au passage en public (non activé)

La démo reste `noindex` tant que son propriétaire n'a pas décidé de la promouvoir ; ce qui suit est prêt à décider, rien n'est activé.

1. **Hôte canonique.** `DEFAULT_URI` suit `CANONICAL_IS_PUBLIC_HOST` (`.railway/railway.ts`) : à la bascule du sous-domaine, `canonical`, `og:url` et `og:image` pointent sur `https://sylius-b2b.<domaine du site>` sans autre changement. Le domaine Railway continue de répondre mais désigne le sous-domaine comme adresse canonique.
2. **Indexation contrôlée** (US-207, réglage `DEMO_INDEXING`) : les **pages vitrine** seulement, plus le catalogue ni les fiches produit. (a) `RobotsController` exclut les zones sans intérêt (administration, `/demo`, `/api`, panier, commande, compte, tri) et publie `Sitemap:` ; (b) `NoIndexSubscriber` retire `noindex` sur la liste blanche unique (`config/demo/seo.yaml`) et le garde partout ailleurs (refus par défaut) ; (c) `sitemap.xml` est servi par l'application, une entrée par page et par langue avec `hreflang` et `x-default`, sur l'hôte canonique ; (d) données structurées `WebSite`, `Person` et `SoftwareSourceCode`, sans note ni prix ; (e) un seul canal indexé (B2B) : le canal Particuliers (cookie `demo_channel`) reste `noindex`.
3. **À corriger avant toute indexation** : l'image de partage d'un produit (`og:image`) est construite sur l'hôte de la requête (génération des vignettes) et non sur l'hôte canonique ; l'image par défaut, elle, suit `DEFAULT_URI`. Sans objet pour les pages vitrine (aucune fiche produit n'est indexée) ; à corriger avant d'indexer le catalogue.
4. **Contrôle** : `curl -sI <url> | grep -i x-robots-tag`, `curl -s <url>/robots.txt`, inspection d'URL de la Search Console du sous-domaine, Lighthouse SEO.

Recommandation actuelle : ne pas indexer. La démo contient des données fictives remises à zéro chaque nuit ; une page indexée qui change ou disparaît nuit plus qu'elle ne sert (voir la limite connue dans [ouverture-publique.md](ouverture-publique.md)).
