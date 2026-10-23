# Performance web après le sprint 21

- Date : 2026-10-23 · Production `https://sylius-b2b.thibaut-monier.fr` (code de `4a4dcfc`, `9cc3ead` n'ajoutant que de la documentation), visiteur non connecté
- Méthode : mesure de repli de `/web-perf`, sans le MCP chrome-devtools. Chromium sans interface piloté par puppeteer-core ; métriques lues dans le navigateur (`PerformanceObserver` : LCP, CLS, tâches longues ; Navigation Timing : TTFB) et réseau lu par le protocole DevTools.
- Profils : bureau (1350 × 940, sans bridage) et mobile (412 × 823, réseau 150 ms de latence et 1,6 Mbit/s, processeur ralenti 4 fois, proche du profil mobile de Lighthouse). Chargement à froid dans un contexte vierge, puis second chargement à chaud sur bureau.
- Limites : une seule mesure par page et par profil (pas de médiane) ; pas de score Lighthouse ; INP non mesuré (aucune interaction) : le TBT en tient lieu. Requêtes GET uniquement.

## Signaux web essentiels

Seuils « bon » : TTFB < 800 ms, FCP < 1,8 s, LCP < 2,5 s, CLS < 0,1, TBT < 200 ms.

| Page | Profil | TTFB | FCP | LCP (élément) | CLS | TBT | Requêtes / poids transféré |
|---|---|---|---|---|---|---|---|
| Accueil `/fr_FR/` | bureau | 418 ms | 672 ms | 672 ms (titre) | 0 | 0 ms | 12 / 356 Ko |
| | mobile | 473 ms | 1 656 ms | 1 656 ms (chapô) | 0 | 37 ms | 12 / 356 Ko |
| Vitrine `/fr_FR/plugins` | bureau | 245 ms | 508 ms | 508 ms (chapô) | 0 | 0 ms | 12 / 361 Ko |
| | mobile | 236 ms | 1 556 ms | 1 556 ms (chapô) | 0 | 22 ms | 12 / 361 Ko |
| Catalogue `/fr_FR/taxons/outillage` | bureau | 204 ms | 384 ms | 424 ms (vignette) | 0 | 0 ms | 18 / 405 Ko |
| | mobile | 219 ms | 1 444 ms | 1 976 ms (vignette) | 0 | 8 ms | 20 / 492 Ko |
| Fiche produit `/fr_FR/products/outi-001-fr` | bureau | 434 ms | 568 ms | 568 ms (image) | 0 | 0 ms | 19 / 386 Ko |
| | mobile | 287 ms | 1 560 ms | 1 560 ms (titre) | 0 | 17 ms | 16 / 368 Ko |

Toutes les pages sont dans la zone « bon » sur les deux profils. À chaud, la page se recharge en 136 à 180 ms (LCP) avec 7 à 13 Ko transférés : seul le document HTML repart du serveur.

## Ce qui est déjà en place

- Compression zstd sur HTML, CSS et JS ; HTTP/2 ; aucune ressource texte servie sans compression.
- Police Inter variable en woff2 (65 Ko), préchargée.
- Images en WebP avec `width`/`height` (CLS nul partout), `srcset` et `decoding="async"`.
- JavaScript non bloquant : seules les trois feuilles de style (48 Ko compressés) bloquent le premier rendu ; TBT quasi nul même avec le processeur ralenti.
- Statistiques Umami chargées en asynchrone, sans effet mesurable.

## Pistes, par ordre d'impact

1. **Bas-moyen : vignettes du haut du catalogue en chargement différé.** Le composant `templates/bundles/SyliusShopBundle/shared/components/main_image.html.twig` met `loading="lazy"` sur toutes les vignettes, y compris celles qui sont visibles dès l'ouverture et qui font le LCP du catalogue. Sur mobile, le LCP arrive environ 530 ms après le FCP (1 976 ms contre 1 444 ms). Passer `loading: 'eager'` et `fetchpriority: 'high'` aux 3 ou 4 premières cartes ; le composant accepte déjà que l'appelant remplace ces attributs.
2. **Bas : cache des fichiers versionnés.** `docker/Caddyfile` sert `/build/*`, `/bundles/*` et `/media/*` en `public, max-age=604800` (7 jours). Les fichiers de `/build/*` portent une empreinte dans leur nom : `public, max-age=31536000, immutable` leur convient, ce qui évite les revalidations après une semaine. `/bundles/*` et `/media/*` restent à 7 jours (noms sans empreinte).
3. **Bas : poids du JavaScript de la boutique.** `app-shop-entry.*.js` pèse 199 Ko compressés (724 Ko bruts) et représente près des deux tiers du poids d'une page. Il ne bloque ni le rendu ni le fil principal aujourd'hui : à surveiller plutôt qu'à traiter, sauf si un plugin y ajoute du poids. Une analyse du contenu du paquet (`webpack-bundle-analyzer`) dira ce qui peut passer en chargement à la demande.

Rien ne justifie d'autre chantier de performance avant l'ouverture : les marges sont larges sur les cinq indicateurs.
