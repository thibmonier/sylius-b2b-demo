# ADR-0009 — Canal B2C secondaire

- **Statut** : Proposé (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

withdrawal (rétractation UE), GPSR et agentic-feeds visent le grand public. Leur présence sur un canal B2B serait incohérente.

## Décision

Un petit canal **B2C** (sous-ensemble du catalogue, prix TTC) montre ces plugins. Sur le canal B2B, le bouton de rétractation n'est pas affiché.

## Alternatives considérées

Tout sur le canal B2B : incohérent (rétractation entre professionnels).

## Conséquences

Un canal de plus à alimenter en données. Les deux canaux partagent le catalogue.
