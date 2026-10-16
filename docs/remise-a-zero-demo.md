# Runbook : remise à zéro nocturne de la démo (US-033)

## Ce qui se passe à 03:00

`supercronic` (conteneur web) lit `docker/crontab` avec `CRON_TZ=Europe/Paris` : la tâche part à **03:00 heure de Paris**, heure d'été comprise (supercronic journalise « processes will be spawned using TZ: Europe/Paris »). Les tâches fréquentes (webhooks chaque minute, flux toutes les 15 minutes) continuent ; les tâches quotidiennes commencent à 04:15, bien après la fin du reset.

La commande `app:demo:reset` :

1. refuse de s'exécuter sans `DEMO_MODE=1` (code de sortie 1, base intacte) ; elle est voulue en `APP_ENV=prod` de démo et nulle part ailleurs ;
2. recharge la suite de fixtures `demo` dans **une seule transaction** : la purge (`DELETE`) et le rechargement sont validés ensemble ou pas du tout. Un échec à n'importe quel moment rend la démo **dans l'état précédent**, intacte ;
3. purge le cache d'images, vide puis réchauffe le cache applicatif (après validation de la transaction).

La purge supprime aussi les commandes Acme du simulateur PunchOut et la consommation du crédit commercial ; le crédit retrouve sa valeur initiale (10 000 € pour Acme).

Pendant le rechargement (de l'ordre de la minute en local), les lectures voient l'état précédent ; une écriture d'un visiteur peut attendre le verrou de ligne. La coupure visible reste de quelques minutes au plus (cache).

## Lire le résultat

Les sorties de la tâche sont relayées par supercronic dans les journaux du conteneur web (Railway) : pas de fichier de journal.

| Ligne | Flux | Sens |
|---|---|---|
| `DEMO_RESET_OK duration=12.3s` | stdout | Succès |
| `DEMO_RESET_FAILED cause="…" duration=…s` | stderr | Échec : rollback, données précédentes conservées, code de sortie 1 |
| `DEMO_RESET_REFUSED cause="DEMO_MODE=1 is required…"` | stderr | Garde : `DEMO_MODE` absent ou à 0 |
| `level=error … error="exit status 1"` | supercronic | Tâche terminée en erreur |

Un échec de la dernière étape (cache) survient après la validation : les données sont alors celles de la remise à zéro, mais `DEMO_RESET_FAILED` et le code 1 le signalent.

## Alerte

Railway n'envoie pas d'e-mail pour une tâche planifiée en échec : ses e-mails ne couvrent que les déploiements en échec et les conteneurs « Crashed » (après épuisement des redémarrages), et il n'a pas d'alerte sur une ligne de journal ([guide des alertes](https://docs.railway.com/guides/alerts-crashes-failed-deploys)). Décision utilisateur du 2026-10-16 : l'alerte passe par `/healthz`.

- Un reset en échec écrit `DEMO_RESET_FAILED` (stderr, journal Railway) et pose le marqueur `var/demo-reset-failed` (`App\Demo\Command\ResetStatus`).
- Tant que ce marqueur existe, `/healthz` et `/health` répondent **503 `reset failed`** ; la démo continue de servir ses données précédentes (reset atomique). Le prochain reset réussi efface le marqueur ; un nouveau déploiement repart sans marqueur.
- Un **moniteur externe** (UptimeRobot, Better Stack…, compte de l'exploitant) interroge `https://<domaine>/healthz` toutes les 5 minutes et envoie un e-mail sur toute réponse différente de 200 : il couvre à la fois une démo en panne et un reset en échec. `/healthz` est hors de la porte privée, aucun identifiant n'est nécessaire.
- Les e-mails natifs de Railway (déploiement en échec, conteneur « Crashed ») restent actifs (réglage du compte, *Account Settings > Notifications*).

La démo n'émet aucun trafic sortant pour alerter : c'est le moniteur qui vient la lire.

## Relance manuelle

Dans le conteneur web, **en `www-data`** (le serveur et le cron tournent sous cet utilisateur) :

```bash
railway ssh --service web -- su -s /bin/sh www-data -c 'php bin/console app:demo:reset --no-interaction'
```

`railway ssh` ouvre une session root : lancée en root, la commande reconstruit `var/cache/prod` avec des fichiers que le serveur web ne peut plus écrire, et toutes les pages répondent 500. Si c'est arrivé : `railway ssh --service web -- chown -R www-data:www-data /app/var`.

Options, depuis un shell `www-data` du conteneur :

```bash
php bin/console app:demo:reset --no-interaction                 # reset complet
php bin/console app:demo:reset --dry-run                        # affiche les étapes sans rien faire
php bin/console app:demo:reset --no-media --no-cache            # uniquement base (tests)
php bin/console app:demo:reset --simulate-failure               # vérifie l'alerte : échoue après le rechargement, rollback complet
```

`--simulate-failure` sert au contrôle d'exploitation (T-033-10) : la sortie doit contenir `DEMO_RESET_FAILED`, le code de sortie doit être 1 et les données d'avant (produits, clients, commandes) doivent être toujours présentes.

## Si ça ne répond plus après le reset

1. `/healthz` répond-il ? Sinon redéployer le service web.
2. Chercher `DEMO_RESET_FAILED` : la cause est dans le message ; la démo est restée dans l'état de la veille, relancer la commande à la main après correction.
3. Cache : `php bin/console cache:clear && php bin/console cache:warmup`.

## Tests

`tests/Functional/Demo/DemoResetCommandTest.php` (garde, succès, échec atomique, purge Acme), `tests/Unit/Demo/Command/DemoResetCommandTest.php` (options, garde, rollback). La bannière « prochaine remise à zéro » (T-033-05) est glissée au sprint 7.
