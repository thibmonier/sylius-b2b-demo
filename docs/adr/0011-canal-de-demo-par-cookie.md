# ADR-0011 — Canal de la démo choisi par cookie

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, sprint 1 (US-007)

## Contexte

Sylius résout le canal par nom d'hôte, et Railway ne sert la démo que sur un domaine. Avec deux canaux (B2B et B2C, ADR-0009) sans nom d'hôte, le contexte de canal unique de Sylius échoue.

## Décision

Un contexte de canal de la démo (`DemoChannelContext`, priorité 200) lit le cookie `demo_channel` posé par le sélecteur `/demo/channel/{B2B|B2C}`. Sans cookie, ou avec une valeur inconnue, c'est le canal **B2B**. Les fonctionnalités grand public des plugins (withdrawal) ne s'affichent que sur le canal B2C, par une condition de twig hook (`is_granted('DEMO_CONSUMER_CHANNEL')`).

## Alternatives considérées

- Deux domaines Railway (un par canal) : possible avec un domaine personnalisé, refusé (domaine par défaut, Q1).
- Préfixe d'URL par canal : non pris en charge nativement par Sylius.

## Conséquences

- Un seul domaine, et un changement de canal en un clic depuis le bandeau de démo.
- Le panier est propre au canal. Changer de canal en cours de commande repart d'un autre panier.
