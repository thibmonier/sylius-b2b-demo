# ADR-0007 — Données de démo depuis des CSV et une suite de fixtures dédiée

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Les fixtures des plugins sont incohérentes entre elles : crédit par client et non par entreprise, seuils d'approbation faussés par les paliers, priorités identiques.

## Décision

Une suite de fixtures `demo` (priorités explicites) lit des **CSV versionnés** (catalogue industriel FR/EN, entreprises, paliers…) et crée les données de chaque plugin de façon cohérente. Elle ne réutilise les fixtures des plugins que si elles sont cohérentes. Les illustrations produits sont générées (SVG), pour éviter les photos sous licence incertaine.

## Alternatives considérées

Fixtures par défaut de Sylius et des plugins : incohérentes, catalogue mode (B2C). Dump SQL : opaque, se périme à chaque migration.

## Conséquences

Les données s'éditent dans des CSV, se relisent en revue et suivent les migrations. Le temps de chargement est mesuré (moins de 10 minutes).
