# ADR-0003 — Remise à zéro nocturne exécutée dans le conteneur web

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Les volumes Railway ne se partagent pas entre services. Les fixtures écrivent des médias (images, factures) qui doivent être servis par le web.

## Décision

La commande `app:demo:reset` tourne **dans le conteneur web** (supercronic, 03:00 Europe/Paris). Elle passe en mode maintenance (503, sauf `/healthz`), recrée la base, recharge la suite `demo`, régénère les médias, vide Mailpit, puis sort du mode maintenance. Au premier démarrage, une base vide déclenche la même commande. Pas de mise en veille Railway.

## Alternatives considérées

Service cron Railway lançant les fixtures : médias écrits hors du web. Service cron qui redéploie le web (remise à zéro au démarrage) : jeton Railway nécessaire et coupure plus longue. Stockage S3 : un service et un coût de plus.

## Conséquences

Coupure de quelques minutes à 03:00. Échec alerté (seule sortie autorisée). Le FS éphémère est cohérent avec la remise à zéro.

## Amendement (2026-10-16, US-033)

- **Garde** : `app:demo:reset` s'exécute si `DEMO_MODE=1` (y compris en `APP_ENV=prod`) et est refusée sinon ; aucune garde sur `APP_ENV`.
- **Atomicité** : le rechargement de la suite `demo` se fait dans **une transaction de base de données unique** (la purge `orm_purger` est en mode `delete`, donc transactionnelle) ; l'échec la rétracte et la démo garde ses données précédentes. Choix retenu contre la base tampon (bascule de schéma, plus de pièces) et la sauvegarde/restauration (plus lente) ; limite : verrous de ligne pendant la minute du rechargement. Le mode maintenance et la recréation de la base décrits plus haut ne sont pas retenus.
- **Fuseau** : `CRON_TZ=Europe/Paris` dans `docker/crontab` (supercronic 0.2.33), 03:00 heure de Paris, heure d'été comprise.
- **Alerte** : voir ADR-0008 (journal Railway + e-mails Railway, code de sortie non nul).
