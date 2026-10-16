# Runbook : déploiement Railway (US-032, US-034)

La démo tourne sur Railway (projet `sylius-b2b-demo`, environnement `production`, région `europe-west4`). L'infrastructure est décrite en code dans [`.railway/railway.ts`](../.railway/railway.ts) (Infrastructure as Code de Railway : `railway.json` n'est plus accepté pour un nouveau service et cesse d'être lu le 2026-12-01).

## Services

| Service | Source | Public | Rôle |
|---------|--------|--------|------|
| `web` | GitHub `thibmonier/sylius-b2b-demo`, `docker/Dockerfile` | domaine HTTPS Railway, **porte privée** | FrankenPHP (boutique, admin) + supercronic (tâches planifiées, reset 03:00 Paris) |
| `mysql` | base MySQL Railway | non | données de la démo |
| `mailpit` | image `axllent/mailpit:v1.27` | **non** (réseau privé uniquement) | reçoit tous les e-mails ; lu côté serveur par `/demo/mails` |

Déploiement : construction de l'image, puis `preDeploy` (migrations Doctrine), puis bascule après le healthcheck `/healthz` (timeout 600 s). Au premier démarrage sur une base vide, l'entrypoint charge les fixtures. L'entrypoint lance d'abord `app:demo:preflight` : le conteneur refuse de démarrer si la porte est active sans identifiants ou si le mailer n'est pas Mailpit (échec fermé).

## Variables

Les valeurs non secrètes sont dans `.railway/railway.ts`. Les secrets ne sont jamais dans le dépôt : ils sont posés dans Railway et conservés par `preserve()`.

| Variable | Où | Valeur |
|----------|----|--------|
| `APP_SECRET` | secret Railway | 64 caractères hexadécimaux aléatoires |
| `DEMO_BASIC_AUTH_USER` | secret Railway | identifiant de la porte privée |
| `DEMO_BASIC_AUTH_PASSWORD_HASH` | secret Railway | `password_hash(<mot de passe>, PASSWORD_ARGON2ID)` ; le mot de passe en clair n'est jamais stocké chez Railway |
| `DEMO_BASIC_AUTH` | `railway.ts` | `1` (démo privée) jusqu'au GO d'ouverture |
| `TRUSTED_HOSTS` | `railway.ts` | domaine public + `healthcheck.railway.app` (hôte du healthcheck Railway) |
| `DATABASE_URL`, `MAILER_DSN`, `DEMO_MAILPIT_API_URL` | `railway.ts` | références au réseau privé (`${{mysql.MYSQL_URL}}`, `${{mailpit.RAILWAY_PRIVATE_DOMAIN}}`) |
| `DEFAULT_URI` | `railway.ts` | `https://${{RAILWAY_PUBLIC_DOMAIN}}` (aussi domaine de retour PunchOut) |

Les autres réglages (plafonds de débit, hôtes sortants autorisés, simulateur) et leurs valeurs par défaut sont décrits dans [`durcissement-demo.md`](durcissement-demo.md).

Changer un secret :

```bash
railway variables --service web --set "DEMO_BASIC_AUTH_PASSWORD_HASH=$(php -r 'echo password_hash(getenv("P"), PASSWORD_ARGON2ID);')"   # avec P exporté
```

## Appliquer un changement d'infrastructure

```bash
cd .railway && npm ci --ignore-scripts && cd ..   # SDK railway épinglé (hors build de l'application)
railway link                                      # projet sylius-b2b-demo, environnement production
railway config plan                               # aperçu, ne modifie rien
railway config apply                              # applique après confirmation
```

`apply` supprime les ressources absentes du fichier : ne jamais retirer un service du fichier sans le vouloir.

## Vérifications après déploiement

Vérifiées le 2026-10-16 sur le domaine généré :

| Vérification | Attendu |
|--------------|---------|
| `GET /healthz` sans identifiants | 200 |
| `GET /fr_FR/` sans identifiants ou avec de mauvais identifiants | 401 (`WWW-Authenticate: Basic`) |
| `GET /fr_FR/` avec les identifiants | 200, « Fournitures Pro — B2B » |
| En-têtes | CSP, HSTS, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` |
| `X-Forwarded-Host` forgé | ignoré (réécrit par le proxy Railway, rien ne fuit dans la page) |
| Mailpit | aucun domaine public |
| `app:demo:reset` dans le conteneur | `DEMO_RESET_OK duration=25s`, démo de nouveau disponible |
| `app:demo:reset --simulate-failure` | sortie 1, `DEMO_RESET_FAILED cause=…`, données précédentes conservées |

```bash
railway ssh --service web -- su -s /bin/sh www-data -c 'php bin/console app:demo:reset --no-interaction'   # relance manuelle
railway logs --service web                                                      # journal (DEMO_RESET_OK / DEMO_RESET_FAILED)
```

**Toujours en `www-data`** : `railway ssh` ouvre une session root ; une commande qui reconstruit le cache (reset, `cache:clear`) lancée en root rend `var/cache/prod` inaccessible au serveur web (erreur 500 sur toutes les pages, constaté le 2026-10-16). Réparation : `railway ssh --service web -- chown -R www-data:www-data /app/var`.

Exploitation du reset nocturne : [`remise-a-zero-demo.md`](remise-a-zero-demo.md).

## Retour arrière

```bash
railway deployment list --service web     # repérer le dernier déploiement SUCCESS
railway redeploy --service web            # redéploie le dernier déploiement
```

Ou, dans le tableau de bord Railway, *Deployments > Redeploy* sur une version précédente. Les migrations sont additives : un retour arrière du code ne nécessite pas de migration inverse.

## Ouverture publique (après le GO de l'utilisateur uniquement)

1. Revue de sécurité US-037 sans constat critique ou majeur ouvert.
2. GO explicite de l'utilisateur.
3. Fermer les voies par lesquelles un visiteur crée un client avec ses propres données (constat M2 du sprint 6, N2 du sprint 8). Mettre `DEMO_REGISTRATION_OPEN: "0"`. Vérifier aussi la commande invité B2C et la demande de devis invité (`allow_guests`), qui ne sont pas encore couvertes.
4. `DEMO_BASIC_AUTH: "0"` dans `.railway/railway.ts`, commit, `railway config apply`.
5. Revérifier : 200 sans identifiants, plafonds de débit actifs, `/demo/mails` limité aux comptes de démo, inscription refusée (403).

## Budget (US-034)

- Plafond visé : 25 $/mois pour la démo.
- La limite Railway est **par workspace** (partagée avec les autres projets du compte) : alerte e-mail (*soft limit*) à **30 $** posée le 2026-10-16 (25 $ démo + ~5 $ des autres projets), **sans** limite dure (une limite dure arrêterait tous les services du workspace). Décision utilisateur.
- `railway usage` et `railway usage projects` donnent le coût courant.
- Relevé partiel du 2026-10-16 (T-041-12/13) : le projet `sylius-b2b-demo` a coûté 0,065 $ en ~5 h, une journée chargée en builds. La projection donne ≈ 9,5 $/mois, sans doute surestimée. Le workspace est estimé à 3,95 $ pour la période en cours. On reste sous le plafond de 25 $.
- Relevé complet à J+7 (T-041-15, sprint 9) à partir du 2026-10-23 : coût réel d'une semaine sans builds intensifs.
- Leviers si le coût dérive : réduire la mémoire allouée au service web, purger Mailpit (`MP_MAX_MESSAGES`), espacer les tâches planifiées toutes les minutes (`tmonier:webhook:dispatch`).
