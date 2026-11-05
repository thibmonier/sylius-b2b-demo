# Revue du sprint 50 — La démo sur le thème publié

**Date** : 2026-11-05
**Engagement** : 10 points · **Livré** : 10 points (3/3 stories, US-228 en périmètre réduit) · **Tâches techniques** : 1/2 (T-TECH-50-02 bloquée)

## Sprint Goal

> La boutique B2B de la démo tourne sur le thème Industrie & Négoce installé depuis Packagist, et les deux derniers plugins sous 80 % de couverture passent le seuil.

**Atteint pour la couverture, abandonné pour la bascule de la démo.** Activer le thème dans la démo a montré que les deux habillages se recouvrent. Thibaut a décidé de corriger d'abord le thème, puis de ne pas basculer la démo.

## Stories livrées

| Story | PR | Commit sur `main` | Résultat |
|-------|----|-------------------|----------|
| US-271 couverture predictive-analytics | [#13](https://github.com/thibmonier/sylius-b2b-predictive-analytics-plugin/pull/13) | `8458053` | 78,36 % → **92,61 %** des lignes sur `main`, 26 tests ajoutés |
| US-270 couverture sales-copilot | [#15](https://github.com/thibmonier/sylius-b2b-sales-copilot-plugin/pull/15) | `3641240` | 58,90 % → **98,07 %** des lignes sur `main` |
| US-228 démo et thème | démo [#167](https://github.com/thibmonier/sylius-b2b-demo/pull/167), thème [#20](https://github.com/thibmonier/sylius-industry-trade-theme/pull/20) | `13635d1`, `9fffd4c` | périmètre réduit, voir ci-dessous |

### US-270 et US-271

- Les tests ciblent les branches d'erreur des contrôleurs, la configuration et l'extension DI, les adaptateurs et les calculateurs. Chaque test vérifie un message ou une valeur ; `src/` n'est pas modifié et aucun code n'est exclu de la mesure.
- La page « Preuve technique » affiche les nouveaux taux (`bin/proof-refresh` dans la PR du sprint).

### US-228

Ce qui a été constaté en activant le thème v0.1.0 sur la boutique B2B de la démo (sans fusion) :

- sur un canal themé, les gabarits du thème passent avant `templates/bundles/` de l'application ; le README disait l'inverse ;
- l'en-tête du thème remplaçait le logo, le menu horizontal et le menu de compte de la démo (constat visuel de Thibaut sur les captures) ;
- la liste dense remplaçait la carte produit : plus de panier, de favoris ni de devis, et des prix affichés hors connexion ;
- la configuration des hooks du panier écrasait les réglages de panier de la démo, même sur un canal sans thème ;
- les variables CSS du thème étaient globales ;
- une quarantaine de tests de maquettes validées de la démo cassaient.

Livré :

- **Thème v0.1.1** (PR #20, CI verte sur Sylius 2.2 et 2.3) : en-tête, pied de page et messages rendus par le cœur ; panier et compte en surcharges de gabarits qui n'écrasent plus les réglages de l'application ; liste dense qui rend les hookables de la carte produit (prix masqués par l'application respectés, panier, favoris, devis) ; styles limités à `[data-theme="industry-trade"]` ; README corrigé (priorité des gabarits, thème enfant, intégration des styles) ; libellé du champ de recherche et vert de succès en AAA. Chaque correction a son rouge prouvé (commits `bcc159d`, `0baf1bc`).
- **Démo** (PR #167) : thème installé en `^0.1`, **inactif** ; test qui refuse un paquet `tmonier/*` en `dev-*` ; test qui signale un gabarit de la démo masqué par le thème (4 entrées justifiées, entrées périmées détectées) ; règle unique `TmonierPackages` qui exclut le thème des 30 plugins (catalogue, page « À propos », matrice de compatibilité).

## Décisions de Thibaut

- Critères visuels ajoutés à US-228 : captures avant/après, images produits, fusion après validation visuelle, correction du thème avant toute bascule.
- Correction du thème d'abord (v0.1.1), puis : **la démo ne bascule pas sur le thème**. Le paquet reste installé, inactif ; la décision pourra être revue.
- Liste dense : tableau conservé, cellules prix et actions alimentées par les hookables de la carte.

## Ce qui n'est pas fait

- **Tag v0.1.1 du thème** : à poser par Thibaut (notes de version préparées).
- **Projet vierge** (scénario 2 d'US-228) : repris en US-273 (2 points, backlog).
- **T-TECH-50-02** : Packagist sert toujours v0.3.0 du passeport sur `packages/<nom>.json` ; le worktree `demo-capture268` est toujours présent.
- **Audit pa11y du shop complet** : abandonné avec la bascule. Une erreur de contraste du texte de succès du plugin Container Fill a été vue dans le panier, thème actif ; à revérifier sur la démo sans thème.

## Incident

La CI de `main` de la démo est rouge depuis la fusion de #167 : le contrôle « Published proof is up to date » compte 349 fichiers de tests pour 346 publiés. La PR du sprint corrige le compte. Sur une PR, ce contrôle est informatif (écart signalé dans le résumé du job, sans échec) ; il n'est bloquant que sur `main`, d'où la découverte après fusion.
