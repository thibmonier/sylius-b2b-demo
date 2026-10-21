# Runbook : déploiement Railway (US-032, US-034)

La démo tourne sur Railway (projet `sylius-b2b-demo`, environnement `production`, région `europe-west4`). L'infrastructure est décrite en code dans [`.railway/railway.ts`](../.railway/railway.ts) (Infrastructure as Code de Railway : `railway.json` n'est plus accepté pour un nouveau service et cesse d'être lu le 2026-12-01).

## Services

| Service | Source | Public | Rôle |
|---------|--------|--------|------|
| `web` | GitHub `thibmonier/sylius-b2b-demo`, `docker/Dockerfile` | domaine HTTPS Railway, **porte privée** | FrankenPHP (boutique, admin) + supercronic (tâches planifiées, reset 03:00 Paris) |
| `mysql` | base MySQL Railway | non | données de la démo |
| volume `media` | volume Railway monté sur `/app/public/media` (service `web`) | non | images produit et cache d'images, conservés d'un déploiement à l'autre (US-145) |
| `mailpit` | image `axllent/mailpit:v1.27` | **non** (réseau privé uniquement) | reçoit tous les e-mails ; lu côté serveur par `/demo/mails` |

Déploiement : construction de l'image, puis `preDeploy` (migrations Doctrine), puis bascule après le healthcheck `/healthz` (timeout 600 s). Au premier démarrage sur une base vide, l'entrypoint charge les fixtures. Il ne se fie plus à la table des canaux : `php bin/console app:demo:fixtures-loaded` lit un marqueur de fin de chargement (table `app_demo_load_marker`, écrite en dernier par la suite de fixtures). Code de sortie 0 : chargé ; 3 : à charger (base vide, ou chargement interrompu qui laisse des canaux sans marqueur) ; autre : erreur, l'entrypoint s'arrête sans rien purger. La migration pose le marqueur sur une base qui contient déjà des canaux, pour ne pas purger une démo en service. L'entrypoint lance d'abord `app:demo:preflight` : le conteneur refuse de démarrer si la porte est active sans identifiants ou si le mailer n'est pas Mailpit (échec fermé).

## Variables

Les valeurs non secrètes sont dans `.railway/railway.ts`. Les secrets ne sont jamais dans le dépôt : ils sont posés dans Railway et conservés par `preserve()`.

| Variable | Où | Valeur |
|----------|----|--------|
| `APP_SECRET` | secret Railway | 64 caractères hexadécimaux aléatoires |
| `DEMO_BASIC_AUTH_USER` | secret Railway | identifiant de la porte privée |
| `DEMO_BASIC_AUTH_PASSWORD_HASH` | secret Railway | `password_hash(<mot de passe>, PASSWORD_ARGON2ID)` ; le mot de passe en clair n'est jamais stocké chez Railway |
| `DEMO_BASIC_AUTH` | `railway.ts` | `1` (démo privée) jusqu'au GO d'ouverture |
| `TRUSTED_HOSTS` | `railway.ts` | domaine Railway d'origine du service `web` (écrit en dur, échappé et ancré) + `healthcheck.railway.app` (hôte du healthcheck Railway) ; ne lit plus `RAILWAY_PUBLIC_DOMAIN`, qui suit le domaine personnalisé (US-140) |
| `DEMO_PUBLIC_HOST` | variable Railway (non secrète, absente du dépôt) | sous-domaine de la démo, nom d'hôte seul, accepté en plus du domaine Railway ([sous-domaine-demo.md](sous-domaine-demo.md)) |
| `DEMO_PROFILE_URL`, `DEMO_CONTACT_URL`, `DEMO_SITE_URL` | variables Railway (non secrètes, posées par Thibaut, vides par défaut) | appel à l'action « Me contacter » (US-142) : profil LinkedIn et formulaire de contact, URL https sans identifiants ; vides ou invalides, les boutons correspondants sont masqués sans erreur |
| `DEMO_UMAMI_SCRIPT_URL`, `DEMO_UMAMI_WEBSITE_ID` | variables Railway (non secrètes, conservées par `preserve()`) | mesure d'audience Umami (US-143) : URL du script en https (ex. `https://cloud.umami.is/script.js`) et identifiant du site (UUID) de thibaut-monier.fr dans Umami. Vides ou invalides : aucun script, CSP inchangée. Voir [`durcissement-demo.md`](durcissement-demo.md) |
| `DATABASE_URL`, `MAILER_DSN`, `DEMO_MAILPIT_API_URL` | `railway.ts` | références au réseau privé (`${{mysql.MYSQL_URL}}`, `${{mailpit.RAILWAY_PRIVATE_DOMAIN}}`) |
| `DEFAULT_URI` | `railway.ts` | `https://${{RAILWAY_PUBLIC_DOMAIN}}` (aussi domaine de retour PunchOut) ; `https://${{DEMO_PUBLIC_HOST}}` une fois `CANONICAL_IS_PUBLIC_HOST` à `true` |

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

## Images produit persistantes (US-145)

Les images produit (`public/media/image`) et le cache LiipImagine (`public/media/cache`) vivent sur un **volume Railway** déclaré dans `.railway/railway.ts` (`volume("media")`, monté sur `/app/public/media`). Sans lui, chaque redéploiement efface les images jusqu'à la prochaine remise à zéro.

- **Droits** : Railway monte le volume avec root pour propriétaire. L'image démarre donc en root ; `docker/prepare-media.sh` crée `image/` et `cache/`, rend le dossier à `www-data`, puis l'entrypoint abandonne les privilèges (`setpriv`) avant de lancer l'application, le planificateur et le serveur. Si le dossier n'est pas inscriptible, le conteneur s'arrête avec un message explicite dans les journaux (`The media directory … is not writable`).
- **Premier démarrage** : le volume est vide. `/healthz` répond normalement et les fiches affichent le visuel de repli (US-078) jusqu'à la première remise à zéro, qui crée les images.
- **Images orphelines** : l'étape 4 de la remise à zéro supprime les fichiers de `public/media/image` qu'aucune ligne ne référence (`--dry-run` n'en supprime aucun) ; le volume ne grossit donc pas d'une nuit à l'autre. Le cache est purgé à l'étape 2, comme avant.

Mise en place (par Thibaut, jamais par Claude) :

```bash
cd .railway && npm ci --ignore-scripts && cd ..
railway config plan     # doit annoncer la création du volume et son montage sur le service web
railway config apply
```

Puis lancer une **remise à zéro** après le premier montage (commande « relance manuelle » ci-dessous) pour remplir le volume.

Vérification : redéployer deux fois le service `web` **sans** remise à zéro (`railway redeploy --service web`), puis ouvrir une fiche produit en FR et en EN : les images doivent s'afficher.

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
railway ssh --service web -- su -s /bin/sh www-data -c 'php -d memory_limit=1G bin/console app:demo:reset --no-interaction'   # relance manuelle
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

Pas-à-pas, vérifications et retour arrière : [ouverture-publique.md](ouverture-publique.md). En résumé : revue de sécurité sans constat critique ou majeur, moniteur `/healthz`, GO explicite, puis `OPEN_TO_PUBLIC = true` dans `.railway/railway.ts` (`DEMO_BASIC_AUTH=0` et `DEMO_REGISTRATION_OPEN=0`, qui ferme aussi la commande invité et le devis invité) et `railway config apply`. Le preflight du conteneur refuse une démo publique à inscriptions ouvertes.

## Budget (US-034)

- Plafond visé : 25 $/mois pour la démo.
- La limite Railway est **par workspace** (partagée avec les autres projets du compte) : alerte e-mail (*soft limit*) à **30 $** posée le 2026-10-16 (25 $ démo + ~5 $ des autres projets), **sans** limite dure (une limite dure arrêterait tous les services du workspace). Décision utilisateur.
- `railway usage` et `railway usage projects` donnent le coût courant.
- Relevé partiel du 2026-10-16 (T-041-12/13) : le projet `sylius-b2b-demo` a coûté 0,065 $ en ~5 h, une journée chargée en builds. La projection donne ≈ 9,5 $/mois, sans doute surestimée. Le workspace est estimé à 3,95 $ pour la période en cours. On reste sous le plafond de 25 $.
- Relevé du 2026-10-17 (T-048-11, encore partiel) : 1,62 $ consommés sur la période, facture estimée à 4,75 $. Projet `sylius-b2b-demo` : environ 0,35 $ pour la journée. La RAM cumulée des services tourne autour de 1,4 Go au repos, avec un pic à 2,77 Go. Le CPU reste sous 0,1 vCPU.
- Relevé complet à J+7 (T-048-11, ex-T-041-15) à partir du 2026-10-23 : coût réel d'une semaine sans builds intensifs.
- Leviers si le coût dérive : réduire la mémoire allouée au service web, purger Mailpit (`MP_MAX_MESSAGES`), espacer les tâches planifiées toutes les minutes (`tmonier:webhook:dispatch`).
