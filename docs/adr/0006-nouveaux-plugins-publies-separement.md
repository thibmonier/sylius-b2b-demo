# ADR-0006 — Nouveaux plugins publiés séparément (MIT)

- **Statut** : Proposé (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

L'analyse relève quatre manques B2B sans équivalent pour Sylius 2 : catalogue fermé, référence de commande, listes d'achat partagées, adresses d'entreprise.

## Décision

Chaque manque devient un **plugin indépendant** publié en MIT sur Packagist, avec le squelette et les exigences de qualité des plugins tmonier : `sylius-b2b-access-plugin`, `sylius-order-reference-plugin`, `sylius-shopping-list-plugin` (si le spike de la wishlist officielle conclut qu'il le faut) et company-accounts 1.2 (adresses, sans BC break). La démo les installe par version taguée.

## Alternatives considérées

Code dans la démo : non réutilisable, et la démo cesserait de montrer seulement des plugins installables.

## Conséquences

Plus de travail par fonctionnalité (CI, revue de sécurité, release), mais les plugins enrichissent l'écosystème et la démo reste une pure intégration.
