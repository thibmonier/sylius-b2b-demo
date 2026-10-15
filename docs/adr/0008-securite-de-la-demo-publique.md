# ADR-0008 — Sécurité de la démo publique

- **Statut** : Proposé (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Des comptes publics, admin compris, sont exposés sur Internet.

## Décision

Garde de l'admin de démo (écritures refusées sur les routes sensibles), comptes de démo non modifiables, **aucune sortie réelle** (Mailpit, récepteur de webhooks interne, IA démo, HttpClient scopé), rate limiting, en-têtes de sécurité, remise à zéro nocturne. Seule l'alerte d'échec de remise à zéro sort, vers une URL de l'opérateur.

## Alternatives considérées

Admin en lecture seule complète : rend la démo back-office sans intérêt. Pas d'admin public : contraire à l'objectif (Hugo veut voir l'admin).

## Conséquences

La démo back-office reste interactive, et les dégâts possibles disparaissent au plus tard à la remise à zéro suivante.
