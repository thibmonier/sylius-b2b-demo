# Audit UI/UX et accessibilité (WCAG 2.2 AAA)

- Date : 2026-10-18 · Commande : `/uiux:audit` (niveau AAA par défaut) · Base : `main` @ `3927587` (sprint 13 mergé)
- Périmètre : 5 pages de la boutique B2B en visiteur anonyme (accueil, Plugins, connexion, catégorie Quincaillerie, fiche OUTI-019), démo locale en prod, porte privée désactivée.
- Méthode : pa11y 8 (WCAG2AAA, `pa11y-wcag2aaa.txt`), Lighthouse 12.8.2 (`lighthouse.md`), mesure du débordement en émulation mobile (Puppeteer), puis trois revues expertes (accessibilité, UX, UI) sur le HTML rendu, les captures et les sources.

| Domaine | Score |
|---|---:|
| Accessibilité AAA | 58/100 |
| UX (heuristiques de Nielsen) | 68/100 |
| UI (design system) | 66/100 |

## Correction apportée aux revues

Les captures 375 px de Chrome headless étaient rendues à environ 500 px (largeur minimale de fenêtre), ce qui laissait croire à un débordement sur toutes les pages. Mesure en émulation mobile réelle (`scrollWidth` du document) :

| Page | 375 px | 320 px |
|---|---:|---:|
| Accueil | 375 | 320 |
| Plugins | 375 | 320 |
| Connexion | **418** (boutons des comptes de démo) | **418** |
| Catégorie | **396** (pagination) | **369** |
| Fiche produit | 375 | 320 |

Vérifié aussi dans le HTML rendu : aucune des 5 pages n'a de `<main>`, de `<header>`, de lien d'évitement ni d'`aria-live`.

## Accessibilité (58/100)

Erreurs pa11y : presque toutes des contrastes sous 7:1 (critère 1.4.6), plus un champ sans libellé. Les 33 avertissements « Abs » sur les `visually-hidden` sont des faux positifs ; le lien newsletter à 1:1 est probablement un faux positif (fond recoloré par `:has()`), le pied de page étant de toute façon à retirer.

| Gravité | Problème | WCAG | Pages | Origine | Remédiation |
|---|---|---|---|---|---|
| Critique | Débordement horizontal (voir tableau ci-dessus) | 1.4.10 | connexion, catégorie | démo + cœur | retirer `text-nowrap` des boutons de `templates/demo/_login_form.html.twig`, pagination repliable |
| Critique | Pas de `<main>`, `<header>`, lien d'évitement | 1.3.1, 2.4.1 | toutes | layout Sylius | surcharger le layout dans `templates/shop/` |
| Majeur | Noms accessibles en anglais (« Account button », « Toggle navigation », « breadcrumbs »…) | 3.1.2, 2.4.6 | toutes | cœur (traductions) | traductions fr dans `translations/` |
| Majeur | Passages anglais de la barre de démo sans `lang="en"` | 3.1.2 | toutes | démo | `<span lang="en">` dans `banner.html.twig`, `_login_form.html.twig` |
| Majeur | `<code>` des identifiants 4,5:1, `.text-muted` | 1.4.6 | connexion, accueil | démo | `--bs-code-color: #9a3f00`, `--bs-secondary-color: #4b5563` |
| Majeur | Boutons favoris `btn-outline-secondary` 4,69:1 | 1.4.6 | accueil, catégorie, produit | thème | surcharge dans `b2b.scss` (`#4b5563`, 7,6:1) |
| Majeur | Pagination 2,07:1 | 1.4.6, 1.4.11 | catégorie | thème | `.page-link` en navy `#1f3a5f` (11,1:1) |
| Majeur | Champ `#criteria_search_value` sans libellé (placeholder « Valeur ») | 1.3.1, 3.3.2 | catégorie | cœur (grille) | `<label class="visually-hidden">` |
| Majeur | Cibles sous 44 px probables (`btn-sm`, `btn-icon`) | 2.5.5 | toutes | démo + cœur | hauteur et largeur minimales 44 px |
| Mineur | Logo « Pro » 6,81:1 | 1.4.6 | toutes | démo | unifier sur `$b2b-accent` |
| Mineur | 4 liens « Essayer » identiques | 2.4.9 | accueil | démo | texte `visually-hidden` par parcours |
| Mineur | Liens `target=_blank` sans mention | 3.2.5 | accueil, connexion, Plugins | démo | « (nouvelle fenêtre) » en `visually-hidden` |
| Mineur | Liens `href="#"` dans le pied de page, pas d'`aria-live` sur les messages | 2.4.4, 4.1.3 | toutes | cœur | retirer ; `aria-live="polite"` sur les flashs |

Points positifs : un seul h1 et hiérarchie sans saut, alternatives présentes, icônes SVG en `aria-hidden`, libellés du login associés, focus visible, palette documentée à 7:1.

## UX (68/100)

| Heuristique | Score | Observation |
|---|---:|---|
| Visibilité de l'état | 4 | canal et prochaine remise à zéro affichés ; rien n'indique qui est connecté |
| Correspondance monde réel | 3 | vocabulaire métier juste ; barre de démo bilingue |
| Contrôle utilisateur | 3 | badges masquables ; menu de connexion sans fermeture au clic extérieur |
| Cohérence | 3 | blocs Sylius génériques (newsletter, partenaires) hors thème |
| Prévention des erreurs | 4 | comptes désactivés grisés |
| Reconnaissance plutôt que rappel | 4 | rôle affiché à côté de chaque compte |
| Flexibilité | 3 | pas de chemin « scénario → compte → page » en un geste |
| Minimalisme | 2 | barre de démo, bandeau, newsletter, 4 bannières partenaires |
| Récupération d'erreurs | 3 | non testé |
| Aide | 4 | scénario et compte conseillé par plugin |

Problèmes majeurs : pied de page hors sujet ; barre de démo sur 2 lignes ; comptes de démo sous le formulaire standard de `/login`, avec double libellé ; aucun prix B2B visible pour le visiteur anonyme. Mineurs : « Dernières offres » sans prix, champ de recherche sans libellé, liens externes non signalés, défilement mobile long (4 + 3 cartes avant les produits).

Points positifs : hero avec une seule action, cartes « Parcours B2B », page Plugins (scénario, compte conseillé, GitHub, Packagist, badge « Non publié »), badges « propulsé par », connexion en un clic sans contournement, prochaine remise à zéro affichée.

## UI (66/100)

- Majeur : pagination non stylée (2,07:1) ; `<code>` en rose Bootstrap ; `btn-outline-secondary` sans surcharge ; deux bruns (`#963d00`, `#9a3f00`) et navy codé en dur dans le SVG du logo ; valeurs en dur au lieu des tokens (`rgba(31,58,95,…)` répété, couleurs de stock, `<style>` propres aux pages de démo) ; pas d'échelle d'espacements, de rayons ni d'ombres.
- Mineur : hiérarchie des titres inégale (« Dernières offres » sans `.b2b-section-title`), cartes plugins, produits et tuiles de styles différents, badges hors palette, état disabled peu distinct, densité mobile (3 barres d'en-tête ≈ 250 px), liens du catalogue distingués par la seule couleur.
- Tokens proposés : `--bs-code-color: #9a3f00`, `--bs-secondary-color: #4b5563`, `--bs-heading-color: #1f3a5f`, pagination en `#1f3a5f`, `$b2b-success` et `$b2b-warning` tokenisés.

Points positifs : palette sémantique documentée avec ses ratios, surcouche additive par variables `--bs-*`, boutons primaires complets, icônes Tabler homogènes, références en monospace, « Prix HT » explicite.

## Plan d'action

Repris dans le backlog (EPIC-10, voir `project-management/backlog/roadmap-ouverture-et-communication.md`).
