# ADR-0002 — FrankenPHP (Caddy) et supercronic dans un seul conteneur

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Le plan Hobby facture chaque service. Les plugins demandent une dizaine de tâches planifiées et un envoi de webhooks chaque minute. L'utilisateur a choisi FrankenPHP pour les performances.

## Décision

Une seule image `dunglas/frankenphp:1-php8.4-bookworm` : Caddy et FrankenPHP servent HTTP (compression, en-têtes), **supercronic** lance les tâches dans le même conteneur. Le mode worker sera décidé après spike (US-006), en mode classique par défaut.

## Alternatives considérées

nginx + php-fpm + supervisord : deux processus, plus lourd. Service cron Railway séparé : un service facturé de plus, sans accès au FS du web.

## Conséquences

Coût et topologie minimaux. Une tâche qui plante n'arrête pas le serveur (supercronic journalise). Les tâches longues partagent la mémoire du web, d'où un dimensionnement à 1 Go.
