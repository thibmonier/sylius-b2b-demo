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

## Addendum (sprint 55, 2026-11-13) : messages asynchrones consommés par la crontab

La démo a maintenant un transport Messenger `async` (Doctrine, table `messenger_messages` créée par migration, `auto_setup=0`). Seul le message `VerifyCompany` de `company-verification` y est routé : la vérification appelle deux services publics (jusqu'à 15 s chacun) et ne doit jamais bloquer une requête web ni un chargement de fixtures.

Il est consommé comme les webhooks, par la crontab et non par un processus permanent : `messenger:consume async --time-limit=55 --limit=10 --quiet` chaque minute (supercronic ne chevauche pas deux exécutions de la même ligne). Aucun changement de l'entrypoint ni de la topologie à un seul conteneur. Délai de traitement : au plus une minute. Dans l'environnement de test, le transport est `in-memory://` : rien n'est traité, aucun appel réseau.

La commande de reprise `tmonier:company-verification:verify-unverified` n'est pas planifiée : les identifiants fictifs de la démo y donneraient « introuvable » auprès des services réels, et écraseraient l'état de démonstration des fixtures.
