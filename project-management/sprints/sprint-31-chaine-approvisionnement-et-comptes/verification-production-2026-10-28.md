# Sprint 31 : contrôles de production du 2026-10-28

Après le déploiement de `main` (18c9038, PR #125), annoncé terminé par Thibaut. Lecture seule, sans connexion (aucun identifiant saisi en production). Chromium sans interface, protocole de la rétrospective du sprint 29 pour le LCP : 360 × 780 px, latence de 150 ms, 200 Ko/s, processeur ralenti 4 fois, médiane de 5 chargements.

| Contrôle | Tâche | Résultat |
|---|---|---|
| `/healthz` | T-208-10 | 200 |
| Page « Plugins » déployée | T-204-10 | nouveau gabarit servi, 26 cartes, 6 familles, plugin ERP à fichiers plats absent ✅ |
| pa11y WCAG2AAA `/fr_FR/plugins` et `/en_US/plugins` | T-204-08, T-204-10 | « No issues found! » dans les deux langues ✅ |
| Largeur de la page « Plugins » à 360 px | T-204-08 | `scrollWidth` 360 pour 360 ✅ |
| Devis, crédit sur compte, assistants IA et page de consentement, FR et EN, sans session | T-203-14 | 302 vers `/<locale>/login` pour les 8 adresses ✅ |
| Page Crédits des images à 360 px, FR et EN | T-196-09, T-197-09 | `scrollWidth` 360 pour 360, 8 vignettes bornées ; seuls débordent des éléments du menu replié, invisibles ✅ |
| LCP mobile de la home FR (seuil 1 456 ms) | T-TECH-31-11, T-TECH-29-03 | 1 560 ms au premier passage (1 300 à 2 212, un chargement à froid), puis **1 236 et 1 212 ms** sur deux séries de 5 ✅ (1 616 ms le 2026-10-27) |
| LCP mobile de la home EN | T-TECH-31-11 | 1 276 puis 1 228 ms ✅ (1 588 ms le 2026-10-27) |
| LCP mobile de la fiche produit `epi-cask-001-fr` | T-TECH-31-11 | 1 348 ms, CLS 0,000 |
| LCP mobile de la liste `/fr_FR/taxons/epi` | T-TECH-31-11 | 1 384 ms, CLS 0,011 |

## Captures

`docs/images/plugins-{fr,en}.png` et `plugins-cards-{fr,en}.png` (1280 px), `plugins-320-{fr,en}.png` et `plugins-cards-320-{fr,en}.png` (320 px), prises sur la production (page publique, sans session) plutôt que par `bin/render-page` : le rendu local était refusé par `bin/check-disk` (4 Go libres). Relues : aucun défaut de mise en page.

## Restent ouverts

- **T-203-14 et T-204-10, partie connectée** (les quatre pages de compte et la page de consentement pour Claire, Paul et Isabelle sur une image reconstruite) : à faire en local quand le disque le permet (reconstruire l'image demande de la place). L'image de production a été construite et testée par la CI (« Docker image », « Quality and smoke tests », vertes sur la PR #125).
- **Capture du panier** (définition de terminé de T-TECH-31-04) : `bin/render-page` refusé, disque à 4 Go et swap à 3,7 Go.
- ~~T-208-11~~ : fait par Thibaut le 2026-10-28 (registre).
