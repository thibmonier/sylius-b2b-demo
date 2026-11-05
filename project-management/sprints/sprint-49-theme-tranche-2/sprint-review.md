# Revue du sprint 49 — Thème Industrie & Négoce, tranche 2

**Date** : 2026-11-05
**Engagement** : 13 points · **Livré** : 13 points (3/3 stories) · **Tâches techniques** : 1/2 (T-TECH-49-02 bloquée)

## Sprint Goal

> Le thème Industrie & Négoce couvre le parcours d'achat principal (accueil, catalogue, fiche produit, panier, compte, pages vitrine) et prévient quand Sylius change un gabarit qu'il surcharge.

**Atteint.** Les pages du parcours sont livrées dans le thème, et le test de dérive surveille les 12 gabarits du cœur surchargés, sur Sylius 2.2 et 2.3.

## Stories livrées

| Story | PR du thème | Commit sur `main` | CI de la PR (Sylius 2.2/2.3, PHP 8.3/8.5) |
|-------|-------------|-------------------|--------------------------------------------|
| US-223 accueil, liste et fiche produit | [#19](https://github.com/thibmonier/sylius-industry-trade-theme/pull/19) | `d145629` | verte, 11 tests fonctionnels |
| US-224 panier, compte et pages vitrine | [#18](https://github.com/thibmonier/sylius-industry-trade-theme/pull/18) | `5fee8bf` | verte, 25 tests fonctionnels après fusion de `main` |
| US-225 test de dérive des surcharges | [#17](https://github.com/thibmonier/sylius-industry-trade-theme/pull/17) | `b0d794b` | verte, 47 tests unitaires (Sylius 2.2.12 et 2.3.2) |

### US-223

- 8 gabarits du cœur surchargés à leur chemin d'origine : bannière d'accueil, liste de produits, tri et nombre par page, en-tête, prix et attributs de la fiche.
- Liste dense (référence, désignation, stock, prix HT), repliée en cartes sous 768 px, visuel de substitution sans image, fiche avec références, fiche technique, documents et disponibilité, sans tier-pricing sans erreur.
- Les surcharges ne s'appliquent qu'aux canaux qui portent le thème : un test vérifie qu'un canal sans thème garde les pages standard.

### US-224

- Panier dense (référence, quantité modifiable, prix et total HT), panier vide avec lien vers la saisie rapide si quick-order est installé, navigation du compte au style du thème, mise en page des pages de contenu.
- Rouge prouvé en cours de sprint : sur un canal sans le thème, les hooks (globaux à l'application) visaient des gabarits introuvables, d'où une 500 sur le panier et le compte. Correction : gabarits des hooks dans le namespace `@TmonierSyliusIndustryTradeTheme`, toujours résolu, qui délèguent au gabarit du cœur quand le thème n'est pas celui du canal. Le nom du thème est déclaré une fois (`industry_theme_name`).

### US-225

- `tests/TemplateDrift/fingerprints.json` : empreinte sha256 normalisée de l'original de chaque surcharge, par version mineure de Sylius ; surcharges découvertes automatiquement.
- Échecs nommés : gabarit modifié, gabarit supprimé en amont, surcharge non enregistrée. `php bin/template-fingerprints --update | --check`.
- Exécuté dans la CI et le lundi par un déclencheur planifié ; procédure de revue dans les deux README.
- Preuve sur de vrais fichiers : après fusion d'US-223, le test a nommé les 8 nouvelles surcharges avant leur enregistrement.

## Ce qui n'est pas fait

- **Audit et captures sur le vrai shop.** pa11y (WCAG2AAA) a tourné sur un rendu de l'application de test, sans la feuille de style Sylius complète : aucune erreur sur les pages des stories, mais 5 erreurs sur l'en-tête et le pied de page de la tranche 1 et un champ de recherche des filtres sans libellé. Lighthouse n'a pas tourné. Les captures du README montrent la boutique de test (« Fashion Web Store », prix en dollars). Reporté dans la DoD d'US-228, quand la démo consommera le thème.
- **T-TECH-49-02.** La version du passeport reste v0.3.0 dans `config/demo/proof.json` : l'API `packages/<nom>.json` de Packagist sert encore v0.3.0 alors que l'index `p2` donne v0.3.1. La branche `proof/us-268-qr-capture` est liée à un worktree à supprimer par Thibaut.

## Incrément

Le thème n'est pas encore tagué : la version suivante (pages du parcours + test de dérive) attend le GO de Thibaut. La démo ne le consomme pas encore (US-228).
