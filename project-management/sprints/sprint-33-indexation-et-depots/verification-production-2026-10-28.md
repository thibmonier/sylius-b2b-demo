# Sprint 33 : vérifications de production du 2026-10-28

Contrôles en lecture seule sur https://sylius-b2b.thibaut-monier.fr, après le déploiement de `main` (f3e95ba) et la pose de `DEMO_INDEXING=1` par Thibaut. Aucune connexion ni identifiant utilisé.

| Contrôle | Résultat |
|----------|----------|
| `bin/verify-public-mode --indexing` (contrôles 1 à 9) | **All checks OK** ; le contrôle 12 a été ignoré (aucun produit donné) |
| `robots.txt` | n'interdit plus tout ; il annonce le sitemap et garde exclues les zones privées (compte, panier, commande, outils de démo) |
| En-tête noindex | absent sur les pages vitrine, présent hors de la liste blanche |
| `sitemap.xml` | 200 ; 20 adresses (10 pages × FR et EN), chacune avec hreflang `fr-FR`, `en-US` et `x-default` |
| Données structurées de l'accueil | `WebSite` et `Person` |
| Données structurées de la page Plugins | 19 `SoftwareSourceCode` ; import ERP absent, plugins en recoupement exclus |
| pa11y WCAG2AAA | sans problème sur les 20 pages du sitemap |

T-207-13 faite. T-207-14 (déclaration du sitemap dans Search Console) : faite par Thibaut, hors dépôt.
