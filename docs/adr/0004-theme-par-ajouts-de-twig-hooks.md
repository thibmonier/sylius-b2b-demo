# ADR-0004 — Thème B2B par ajouts de Twig Hooks

- **Statut** : Proposé (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Les plugins ajoutent ou remplacent des hookables du thème officiel (panier, fiche produit, compte). Un thème qui remplacerait ces hookables casserait les plugins.

## Décision

Le thème B2B **ajoute** des hookables et une feuille de style Encore. Il ne surcharge que les templates du thème officiel qu'aucun plugin n'utilise (en-tête, pied de page). Une vérification en CI compare la liste des hookables des plugins à une référence. Pas de SyliusThemeBundle.

## Alternatives considérées

Fork complet du shop ou SyliusThemeBundle : maintenance lourde, risque élevé de régression. Thème publié en paquet : possible plus tard.

## Conséquences

Le thème reste proche du thème officiel (Bootstrap 5) avec une identité B2B. Les mises à jour Sylius restent simples.
