# Conception graphique dans Claude Design

La démo utilise un canevas unique Claude Design pour maquetter chaque écran avant de le coder.

## Principe

1. **Canevas unique** : toutes les maquettes vivent dans le même projet Claude Design (`G2StRQigRKD6SqNRoyHpkW`). Chaque page du canevas représente un écran (desktop et mobile).
2. **Validation avant Twig** : aucun gabarit Twig n'est ouvert tant que Thibaut n'a pas validé la maquette. La validation est consignée au registre des décisions du sprint.
3. **Export des images** : les images sont exportées depuis le canevas, optimisées (< 300 Ko, WebP ou PNG), et versionnées dans `docs/mockups/`.

## Écrans refondus depuis le sprint 29

| Sprint | Écran | Page du canevas |
|--------|-------|-----------------|
| 29 | Home et vitrine (US-186) | Home, HomeMobile |
| 30 | Catalogue (US-198) | Catalogue, Catalogue2 |
| 30 | Fiche produit (US-200) | Fiche, FicheMobile |
| 30 | Panier (US-200) | Panier, PanierMobile |
| 31 | Mon compte (US-203) | Compte, CompteMobile |
| 31 | Pages des plugins (US-203) | Insights, InsightsMobile, Releve, ReleveMobile |
| 32 | Preuve technique (US-205) | Preuve, PreuveMobile |
| 32 | Architecture (US-206) | Architecture, ArchitectureMobile |
| 33 | Liste / recherche (EPIC-18) | Liste, ListeMobile |
| 34 | Plugins (EPIC-18) | Plugins, PluginsMobile |
| 39 | Copilote d'achat (US-248) | Copilote, CopiloteMobile |
| 39 | Réassort (US-250) | Reassort, ReassortMobile |

## Méthode d'export des images

1. Ouvrir la page du canevas dans Claude Design.
2. Capturer la page en PNG ou WebP.
3. Optimiser : largeur maximale 1280 px (desktop) ou 360 px (mobile), poids < 300 Ko.
4. Sauvegarder dans `docs/mockups/` avec le nom de la page en kebab-case : `home-desktop.png`, `home-mobile.png`.
5. Ajouter un texte alternatif descriptif dans le gabarit Twig qui affiche l'image.

## Jetons de design

Le thème « Industrie & Négoce » définit les jetons réutilisés dans chaque maquette :

| Jeton | Valeur |
|-------|--------|
| Fond principal | `#faf8f5` (crème) |
| Texte principal | `#1a2332` (navy) |
| Accent | `#2d7d6f` (teal) |
| Titres | Fraunces (serif) |
| Corps | Inter (sans-serif) |
| Espacement de bloc | 1.5 rem |
| Rayon de carte | 0.5 rem |

## Accessibilité

Chaque texte doit atteindre le niveau AAA (WCAG 2.2) :
- Texte normal (< 18 pt) : ratio de contraste ≥ 7:1
- Texte large (≥ 18 pt ou ≥ 14 pt gras) : ratio de contraste ≥ 4,5:1
- Les informations ne sont jamais transmises par la seule couleur (icône + mention textuelle).
