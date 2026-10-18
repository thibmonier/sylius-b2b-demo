# Images des produits de démo (US-047)

Les 210 produits de démo ont une image, versionnée dans `fixtures/images/` (WebP, CC0), liée au SKU par
`fixtures/images/manifest.csv`. Le chargement des fixtures (`ProductImageAttacher`) lit uniquement ces fichiers locaux :
**aucun appel réseau** au runtime, au reset, au build ni en CI (garde : `ImageBudgetTest`).

## Décision (spike, 2026-10-17)

- Source : Openverse (`https://api.openverse.org/v1/images/`, sans clé), filtre `license=cc0`, revérifié sur chaque résultat
  (champ `license` et `license_url`). Quota anonyme : 20 requêtes/minute et 200/jour, d'où une recherche par type d'article
  (88 requêtes) et non par produit.
- Format : WebP, 800 px au plus sur le grand côté, 80 Ko au plus par image, 15 Mo au plus au total.
- Résultat de la récupération unique : 74 images (2,6 Mo) pour 210 produits : 23 produits ont une image propre, 187 partagent
  une image de leur famille (`scope = family`), aucun produit sans image.

## Pertinence : limite connue

Les résultats CC0 d'Openverse pour du matériel industriel sont pauvres (musées, illustrations, photos de personnes ou de
marques). Un contrôle visuel de toutes les images a écarté celles qui montraient une personne, une marque, un texte
gênant, un sujet sans rapport ou un contenu sensible. Les produits dont l'image récupérée a été écartée reçoivent une
image conservée de leur catégorie (casques, gants, chaussures, vêtements, visserie, outillage, fixations, emballage,
signalétique, papeterie). L'image illustre donc une famille d'articles, pas toujours l'article exact : acceptable pour une
démo, à ne pas présenter comme la photo du produit.

## Relancer ou compléter

```bash
php bin/fetch-product-images --cache-dir=/chemin/hors/depot [--only=SKU1,SKU2]
```

Script manuel, jamais appelé par le reset, le build ni la CI. User-Agent identifiant, 4 s entre deux recherches, 1,2 s entre
deux téléchargements, pas de parallélisme. Reprise : un SKU déjà au manifeste avec son fichier n'est pas retraité. Ne relancer
que pour un nouveau produit (le quota est de 200 requêtes par jour) et relire visuellement le résultat.

Ajouter une image à la main : déposer un WebP (800 px, <= 80 Ko, licence CC0 vérifiée) dans `fixtures/images/` et ajouter
une ligne au manifeste (sku, file, source, creator, title, origin_url, license = `cc0`, license_url, fetched_at, scope).
`ImageBudgetTest` échoue si un fichier n'est pas au manifeste, si une licence n'est pas CC0, si un SKU n'a pas d'image ou si
un budget est dépassé.

## Reset

Les images des produits sont rechargées par les fixtures à chaque reset (nouveaux fichiers dans le répertoire d'images, une
copie par produit) ; la purge des images orphelines (étape 4 du reset) supprime les anciennes et celles téléversées par les
visiteurs. Le cache LiipImagine est vidé par l'étape 2 : les vignettes sont régénérées à la première visite.
Chargement des fixtures mesuré : 9,4 s et 317 Mo sans images, 6,7 s et 331 Mo avec les 210 images (écart de bruit).

## Affichage (US-078)

Tailles, `srcset`, chargement différé, image principale prioritaire et visuel de repli : voir [seo-et-vitesse.md](seo-et-vitesse.md) (mesures avant et après).
