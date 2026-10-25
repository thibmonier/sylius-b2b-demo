# Images des produits de démo (US-047)

Les 210 produits de démo ont une image, versionnée dans `fixtures/images/` (WebP, CC0 ou licence Pexels), liée au SKU par
`fixtures/images/manifest.csv`. Le chargement des fixtures (`ProductImageAttacher`) lit uniquement ces fichiers locaux :
**aucun appel réseau** au runtime, au reset, au build ni en CI (garde : `ImageBudgetTest`).

## Décision (spike, 2026-10-17)

- Source : Openverse (`https://api.openverse.org/v1/images/`, sans clé), filtre `license=cc0`, revérifié sur chaque résultat
  (champ `license` et `license_url`). Quota anonyme : 20 requêtes/minute et 200/jour, d'où une recherche par type d'article
  (88 requêtes) et non par produit.
- Format : WebP, 800 px au plus sur le grand côté, 80 Ko au plus par image, 15 Mo au plus au total.
- Résultat de la récupération unique : 74 images (2,6 Mo) pour 210 produits : 23 produits ont une image propre, 187 partagent
  une image de leur famille (`scope = family`), aucun produit sans image.

## Pertinence : photo relue ou visuel neutre (US-092)

Les résultats CC0 d'Openverse pour du matériel industriel sont pauvres (musées, illustrations, photos de personnes ou de
marques). Une première relecture (US-047) avait écarté les images montrant une personne, une marque ou un contenu
sensible, mais elle laissait des photos hors sujet (une cheville nylon illustrée par une équerre en fonte, une scie à
métaux par une scie ancienne en bois, des gants par un outil et des briques).

Depuis le sprint 16, chaque ligne du manifeste porte un `status`, jugé à la main produit par produit :

| `status` | Sens | Image |
|----------|------|-------|
| `validated` | l'image montre le même type d'article que le produit (une photo de famille : la couleur ou la marque peuvent différer) | photo CC0 (`scope` = `own` ou `family`) |
| `fallback` | aucune photo libre ne convient : un visuel neutre de la catégorie remplace la photo | `category-<taxon>.webp`, dessiné pour la démo (`source` = `generated`, `scope` = `category`, CC0) |
| `unreviewed` | écrit par le script de récupération ; refusé tant que personne ne l'a relu | - |

Relecture du sprint 16 : 210 produits, 137 images validées (dont une nouvelle, un pied à coulisse) et 73 visuels neutres de
catégorie (gants jetables, chaussures de sécurité, vêtements de protection, fixations, quincaillerie sans photo fiable,
rubans et films, étiquettes). Les photos qui ne correspondaient à aucun produit ont été supprimées du dépôt. Les visuels
neutres sont des pictogrammes sans texte (valables en français et en anglais), aux couleurs du thème B2B.

Production des visuels neutres : pictogrammes dessinés à la main en SVG (formes géométriques, jetons de couleur du thème, aucun
texte), rendus en 800 x 600 par ImageMagick (`magick`) puis convertis en WebP par `cwebp` (qualité 80, moins de 15 Ko chacun).
Ce sont des oeuvres originales de Thibaut Monier, versées au domaine public (CC0), sans source externe. Leur titre affiché sur la
page Crédits est traduit (« Visuel neutre : gants »), et l'auteur « not specified » des photos Openverse devient « auteur non précisé ».

Le contrôle est `ImageManifestAuditor` (tests : `ImageBudgetTest`, `ImageManifestAuditorTest`) et la commande
`bin/console app:demo:audit-product-images` : licence autre que CC0, champ vide, image non relue ou visuel neutre qui n'en
est pas un échouent en nommant le SKU. La page publique **Crédits des images** (`/{locale}/credits-images`, lien dans la
colonne « Cette démo » du pied de page, US-061) est générée depuis le même manifeste : chaque fichier y figure une fois avec
son auteur, sa source, sa licence et le nombre de produits qu'il illustre.

## Photos par type d'article (US-196, sprint 28)

Depuis le sprint 28, 201 des 210 produits montrent la **photo de leur type d'article** (casque, gant en cuir, carton, tournevis...),
une photo par type, jamais partagée entre deux types différents, sans marque lisible. Les 9 autres (types `neutral` de la sélection : veste de pluie, botte en caoutchouc) montrent un visuel neutre de leur catégorie.

- Source : Pexels (23 types) et Unsplash (5 types), licence relue sur https://www.pexels.com/license/ le 2026-10-25 (usage libre, attribution non obligatoire mais gardée,
  modification autorisée ; interdit : suggérer l'aval d'une personne ou d'une marque, revendre les copies non modifiées).
  Les photos ont été choisies par Thibaut Monier sur une planche, puis téléchargées avec son accord ; les originaux (6 000 px)
  ne sont pas versionnés.
- Choix : `fixtures/images/selection.json` (versionné) liste, par type, les SKU couverts, le fichier d'origine, l'auteur, la page Pexels,
  le cadrage (`focus`, point à garder dans le cadre 4:3). `neutral` liste les types montrés par un visuel neutre de catégorie, `pending` ceux qui attendent une photo.
- Aucune retouche : une photo où une marque reste lisible est remplacée, jamais floutée ni pixellisée (décision de Thibaut, 2026-10-25).
- Format : cadre 4:3 comme la fiche produit (800 x 600), WebP, 80 Ko au plus.
- Page publique « Crédits des images » : chaque photo y figure avec son auteur, sa page d'origine, sa licence (CC0, Pexels ou Unsplash) et le
  nombre de produits illustrés.

Importer ou rejouer l'import (aucun réseau, mêmes originaux = mêmes fichiers) :

```bash
php bin/import-product-images --source-dir=/chemin/des/originaux [--date=AAAA-MM-JJ]
```

Le script découpe, floute, convertit en WebP, réécrit les lignes du manifeste (`source` et `license` = `pexels`, `status` = `validated`)
et retire les fichiers qui ne sont plus au manifeste. Garde : `ProductPhotosPerTypeTest` (chaque SKU sur la photo de son type, aucune photo
partagée entre types, cadre 4:3), `PhotoSelectionTest` (sélection complète, licence et auteur), `SelectionImporterTest`.

Pour ajouter un type : déposer l'original dans le dossier, ajouter l'entrée à `selection.json` (et la retirer de `pending`), relire la
photo en entier à 800 px (marque, texte, personne), puis relancer l'import.

## Récupération Openverse (sprint 15, historique)

```bash
php bin/fetch-product-images --cache-dir=/chemin/hors/depot [--only=SKU1,SKU2]
```

Script du premier chargement (photos CC0 d'Openverse, une par famille), conservé pour mémoire : les lignes qu'il ajoute sont `unreviewed`
et doivent être relues avant d'être livrées. Il n'est jamais appelé par le reset, le build ni la CI. User-Agent identifiant, 4 s entre deux
recherches, 1,2 s entre deux téléchargements, pas de parallélisme.

Ajouter une image à la main : déposer un WebP (800 px, <= 80 Ko, licence CC0, Pexels ou Unsplash vérifiée) dans `fixtures/images/` et ajouter
une ligne au manifeste (sku, file, source, creator, title, origin_url, license = `cc0` ou `pexels`, license_url, fetched_at, scope, status = `validated` après relecture).
`ImageBudgetTest` échoue si un fichier n'est pas au manifeste, si une licence n'est ni CC0 ni Pexels, si un SKU n'a pas d'image, si une image n'est pas relue ou si
un budget est dépassé.

## Reset

Les images des produits sont rechargées par les fixtures à chaque reset (nouveaux fichiers dans le répertoire d'images, une
copie par produit) ; la purge des images orphelines (étape 4 du reset) supprime les anciennes et celles téléversées par les
visiteurs. Le cache LiipImagine est vidé par l'étape 2 : les vignettes sont régénérées à la première visite.
Chargement des fixtures mesuré : 9,4 s et 317 Mo sans images, 6,7 s et 331 Mo avec les 210 images (écart de bruit).

## Affichage (US-078)

Tailles, `srcset`, chargement différé, image principale prioritaire et visuel de repli : voir [seo-et-vitesse.md](seo-et-vitesse.md) (mesures avant et après).
