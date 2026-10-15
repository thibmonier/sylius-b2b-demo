# ADR-0003 — Remise à zéro nocturne exécutée dans le conteneur web

- **Statut** : Proposé (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Les volumes Railway ne se partagent pas entre services. Les fixtures écrivent des médias (images, factures) qui doivent être servis par le web.

## Décision

La commande `app:demo:reset` tourne **dans le conteneur web** (supercronic, 03:00 Europe/Paris). Elle passe en mode maintenance (503, sauf `/healthz`), recrée la base, recharge la suite `demo`, régénère les médias, vide Mailpit, puis sort du mode maintenance. Au premier démarrage, une base vide déclenche la même commande. Pas de mise en veille Railway.

## Alternatives considérées

Service cron Railway lançant les fixtures : médias écrits hors du web. Service cron qui redéploie le web (remise à zéro au démarrage) : jeton Railway nécessaire et coupure plus longue. Stockage S3 : un service et un coût de plus.

## Conséquences

Coupure de quelques minutes à 03:00. Échec alerté (seule sortie autorisée). Le FS éphémère est cohérent avec la remise à zéro.
