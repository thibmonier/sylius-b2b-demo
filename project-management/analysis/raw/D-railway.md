# D — Railway Hobby (rapport brut, agent research-assistant, 2026-10-15)

Sources officielles : docs.railway.com (pricing/plans, cron-jobs, serverless, volumes, healthchecks, pre-deploy, variables). Les autres sources (forum station.railway.com, blogs tiers) sont signalées comme telles.

## Plan Hobby
- 5 $/mois, avec 5 $ d'usage inclus (non reportables). Au-delà, l'usage est facturé.
- Maximums par service : 48 Go RAM, 48 vCPU, 6 répliques.
- Volumes : 5 Go chacun, 10 par projet, à 0,15 $/Go/mois. Egress : 0,05 $/Go.
- Veille (serverless) : un service est inactif après 10 minutes sans trafic sortant. Un pool de connexions DB ou de la télémétrie empêche la veille. Le premier appel après veille peut renvoyer 502.
- Non documenté : nombre de services et de projets, limites de domaines personnalisés.
- SMTP sortant bloqué sur Hobby (réponses du staff sur le forum) : utiliser une API HTTPS (Resend, Postmark…) ou Mailpit.

## Cron
- Réglage « Cron Schedule » du service, en UTC, toutes les 5 minutes au minimum.
- Le processus doit se terminer seul. Si l'exécution précédente tourne encore, la suivante est sautée.
- Horaire approximatif. Durée maximale et politique de retry non documentées.
- CLI : `railway cron run`.
- Reset : un service cron avec la même image (drop, create, migrate, fixtures). Références de variables du type `${{MySQL.MYSQL_URL}}`.
- Un volume ne semble pas partageable entre services : stocker les médias sur S3, ou déclencher le reset côté web.

## Déploiement
- Pre-deploy command : conteneur séparé, sans volumes, timeout de 1 à 3600 s. Adaptée aux migrations.
- Healthcheck : HTTP 2xx, timeout de 300 s, requêtes venant de healthcheck.railway.app. La variable `PORT` est injectée.
- Variables système : `RAILWAY_PUBLIC_DOMAIN`, `RAILWAY_PRIVATE_DOMAIN`.
- Un volume impose une courte coupure à chaque redéploiement.
- Le template Railway Sylius (railway.com/deploy/sylius) utilise nginx + php-fpm, un worker Messenger, Redis, MySQL et Mailpit. Pas de retour d'expérience FrankenPHP trouvé.

## Coût (non vérifié, blog tiers)
- Environ 20 $ par vCPU et par mois, et 10 $ par Go de RAM et par mois.
- Web et base allumés en permanence (0,5 vCPU et 0,5 Go chacun) : environ 30 $/mois. Avec mise en veille : 5 à 15 $/mois.
- Fixer une limite d'usage dans les réglages.

## Pièges
- Cache Symfony : préchauffer (`cache:warmup`) au build.
- Médias : volume limité à 5 Go, ou S3.
- Trusted proxies : nécessaires pour X-Forwarded-Proto (cookies secure, URLs https).
- 502 au réveil d'un service en veille.
- Une base de données active empêche la veille.
