# Revue du sprint 51 — Valeur visible dans la démo

**Date** : 2026-11-10
**Engagement** : 9 points · **Livré** : 9 points (3/3 stories) · **Tâches techniques** : 1/1

## Sprint Goal

> Le back-office de la démo montre les entreprises qui ralentissent leurs commandes, avec la raison en clair, et le QR des passeports produits ; l'installation du thème est prouvée par sa CI.

**Atteint.**

## Stories livrées

| Story | PR | Commit sur `main` | Résultat |
|-------|----|-------------------|----------|
| US-220 score d'attrition | démo [#171](https://github.com/thibmonier/sylius-b2b-demo/pull/171), plugin predictive v0.2.0 | `b5bcf96` | IndusPro « À risque » avec sa raison, filtre de la liste des entreprises |
| US-272 GTIN des passeports | démo [#170](https://github.com/thibmonier/sylius-b2b-demo/pull/170) | `3aeb674` | 5 GTIN fictifs valides, onglet « Étiquettes » avec lien GS1 et QR |
| US-273 thème dans un projet vierge | thème [#21](https://github.com/thibmonier/sylius-industry-trade-theme/pull/21) | `dad352b` | job « projet vierge » vert sur Sylius 2.2 et 2.3, rouge prouvé |

### US-220

- Plugin predictive-analytics v0.2.0 : score par entreprise (intervalle médian sur 12 mois, seuils 2× « à risque » et 3× « critique »), raison en clair FR/EN, mention « indicatif », filtre dans la liste des entreprises.
- Démo : le plugin passe en `^0.2`. IndusPro commande tous les 30 jours et rien depuis 70 jours ; ses dates sont relatives au jour du reset, le statut ne dépend donc pas de la date du jour (testé sur 5 dates, dont le 29 février).

### US-272

- Les passeports de la démo portaient le code de la variante : « GTIN invalide » et pas de QR. Ils portent maintenant des GTIN-14 au préfixe GS1 réservé aux exemples (`0950600`), clé de contrôle calculée ; une clé fausse fait échouer le chargement en nommant le produit.
- La page publique du passeport affiche le GTIN à la place du code de la variante.

### US-273

- Le job crée un Sylius-Standard neuf (2.2 et 2.3), y exécute les étapes du README **telles qu'écrites** (blocs marqués `blank-project`, lus par `bin/blank-project-steps`), puis vérifie le rendu : accueil et liste produits du canal avec thème, accueil d'un canal sans thème, styles compilés.
- Un test vérifie que le README français exécute les mêmes commandes que l'anglais.
- Rouge prouvé : sans l'étape de copie de la configuration, le job échoue sur une assertion nommée (« themed channel, product list: HTTP 500 »).
- Le README documente maintenant l'import des styles depuis `assets/shop/entrypoint.js` : un Sylius-Standard neuf n'a pas de feuille de style boutique, la procédure était incomplète.

## Hors sprint, livré pendant le sprint

- Démo [#177](https://github.com/thibmonier/sylius-b2b-demo/pull/177) : `paragonie/halite` 5.1.5, après un avis de sécurité publié sur cette dépendance ; l'audit des dépendances bloquait toutes les PR.
- Démo [#176](https://github.com/thibmonier/sylius-b2b-demo/pull/176) (remplace la PR Dependabot #174) : Rector 2.8.4, PHPStan 2.3.5, phpstan-doctrine 2.1. Rector 2.7 plantait avec PHPStan 2.3.4.
- Plugin ai-catalog-copilot : le job hebdomadaire « Symfony AI next minor » était rouge parce qu'il ne montait pas `symfony/ai-anthropic-platform` ; correctif sur la branche `ci/symfony-ai-next-all-packages`.

## Ce qui n'est pas fait

- Fusion de #176, #173 (mailpit) et de la PR du sprint #175 : GO de Thibaut.
- Veille de la semaine 46 (#178) : montées majeures de la démo (mcp-server-plugin 0.3, ai-bundle 0.15, Encore 7, apexcharts 8) et PHPUnit 12/13 sur une quinzaine de plugins, à transformer en stories.
