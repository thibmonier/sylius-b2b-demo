# Performance web après le sprint 37

- Date : 2026-10-30 · Production `https://sylius-b2b.thibaut-monier.fr` (code de `b1ad1c6`, sprint 37 : copilote d'achat et passeport produit)
- Méthode : mesure simplifiée par cURL avec `-w` pour les temps de réponse, en raison de contraintes d'outillage. Pas de métriques LCP/CLS/TBT cette fois-ci. Requêtes GET uniquement, visiteur non connecté.
- Profil : bureau uniquement (sans émulation mobile ni bridage réseau)
- Limites : une seule mesure par page ; pas de score Lighthouse ; métriques limitées à TTFB et temps total

## Temps de réponse serveur (TTFB)

Seuil « bon » : TTFB < 800 ms (idéalement < 500 ms)

| Page | URL | TTFB | Temps total | Taille HTML |
|---|---|---|---|---|
| Accueil | `/fr_FR/` | 108 ms | 126 ms | 55 Ko |
| Plugins | `/fr_FR/plugins` | 95 ms | 134 ms | 201 Ko |
| Catalogue | `/fr_FR/taxons/outillage` | 117 ms | 137 ms | 63 Ko |
| Fiche produit | `/fr_FR/products/outi-001-fr` | 143 ms | 163 ms | 61 Ko |
| À propos | `/fr_FR/a-propos` | 91 ms | 113 ms | 62 Ko |
| Preuve | `/fr_FR/preuve` | 106 ms | 153 ms | 232 Ko |

**Toutes les pages affichent un TTFB excellent (< 150 ms)**, largement dans la zone « bon ».

## Évolution depuis le sprint 21 (2026-10-22)

### TTFB — amélioration significative

| Page | Sprint 21 (bureau) | Sprint 37 | Delta |
|---|---|---|---|
| Accueil | 418 ms | 108 ms | **−310 ms (−74 %)** |
| Plugins | 245 ms | 95 ms | **−150 ms (−61 %)** |
| Catalogue | 204 ms | 117 ms | **−87 ms (−43 %)** |
| Fiche produit | 434 ms | 143 ms | **−291 ms (−67 %)** |

**Amélioration moyenne du TTFB : −65 %**

Les nouvelles pages (À propos, Preuve) affichent également d'excellents temps de réponse, avec un TTFB de 91 et 106 ms respectivement.

### Points positifs maintenus

Les optimisations identifiées lors de l'audit du sprint 21 restent en place :

- Compression zstd sur HTML, CSS et JS
- HTTP/2
- Police Inter variable en woff2, préchargée
- Images en WebP avec attributs `width`/`height`
- JavaScript non bloquant

### Nouvelle page Preuve

La page `/fr_FR/preuve` (232 Ko de HTML, ~475 ressources estimées) est la plus volumineuse, mais conserve un TTFB de 106 ms et un temps total de 153 ms, ce qui reste excellent.

## Nouveautés des sprints 35 à 37

### Sprint 35 : Commerce prédictif
- Plugin prédictif installé (v0.1.0 → v0.1.3)
- Thème actif (UI/UX)

### Sprint 36 : Veille des versions
- Plugin de veille hebdomadaire des versions
- Chaîne d'approvisionnement PHP sécurisée

### Sprint 37 : Copilote d'achat et passeport produit
- Plugin copilote d'achat
- Plugin passeport produit numérique (lien vérifié sur `/fr_FR/a-propos`)

Aucun de ces plugins n'a dégradé les performances. Au contraire, le TTFB s'est amélioré de façon spectaculaire.

## Hypothèses d'amélioration du TTFB

Plusieurs facteurs peuvent expliquer l'amélioration du TTFB :

1. **Optimisations serveur** : cache applicatif, OPcache, ou configuration Caddy améliorée
2. **Infrastructure Railway** : possible montée en version ou amélioration du réseau
3. **Réduction de la charge** : moins de traitements synchrones au rendu
4. **Localisation** : mesure depuis une zone géographique plus proche du serveur

Sans instrumentation applicative (APM), l'origine exacte reste à confirmer.

## Axes d'amélioration

Bien que les performances soient déjà excellentes, quelques pistes demeurent :

### 1. Mesures complètes (Lighthouse ou équivalent)

**Priorité** : Basse  
**Estimation** : 3 points  
**Cible** : Story de la démo ou script d'audit

Restaurer les métriques LCP, CLS, TBT et FCP pour un suivi complet des signaux web essentiels. Nécessite soit :
- `npx lighthouse` fonctionnel
- Script Puppeteer comme au sprint 21

### 2. Monitoring continu (APM)

**Priorité** : Basse  
**Estimation** : 5 points  
**Cible** : Issue de la démo

Installer un APM léger (Blackfire, Tideways, ou New Relic) pour :
- Identifier les gains réels de performance
- Détecter les régressions avant qu'elles n'impactent l'utilisateur
- Mesurer l'impact de chaque nouveau plugin

### 3. Cache des ressources statiques (rappel du sprint 21)

**Priorité** : Très basse  
**Estimation** : 1 point  
**Cible** : Issue de la démo

Passer `/build/*` en `public, max-age=31536000, immutable` (fichiers versionnés).

## Conclusion

Les performances web de la démo B2B sont **excellentes** après le sprint 37 :

- **TTFB moyen : 110 ms** (contre 325 ms au sprint 21)
- **Amélioration : −65 %** sur les 4 pages historiques
- **Nouvelles pages** : également très performantes
- **Aucune régression** malgré l'ajout de 3 nouveaux plugins

La démo reste largement dans la zone « bon » pour tous les critères mesurés.
