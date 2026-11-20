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

## Builds suspendus par la plateforme

Constaté du 2026-10-21 au 2026-10-22 : un incident Railway suspend les builds, les PR fusionnées sur `main` ne partent plus en production (le déploiement automatique reste activé mais rien n'est construit).

1. **Constater** : page d'état de Railway (incident ouvert, plans concernés) et `railway deployment list --service web` (aucun déploiement après le dernier merge). Ne pas relancer de build en boucle.
2. **Continuer à fusionner** : `main` reste protégée et testée par la CI, la production garde la dernière version saine. Noter dans le fichier de statut du sprint les PR fusionnées non déployées.
3. **À la reprise** : un seul déploiement du dernier `main` (le déploiement automatique, ou `railway redeploy --service web` lancé par Thibaut), pas un déploiement par PR : les migrations sont additives et s'enchaînent dans l'ordre.
4. **Vérifier** : les vérifications après déploiement ci-dessus, puis un contrôle par story livrée pendant la suspension ; les stories restent en `review` tant que ce contrôle n'est pas fait.

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
- Relevé du 2026-11-19 (période de facturation du 13 novembre au 13 décembre, 6 jours écoulés) : workspace à 5,36 $, dont **3,18 $ pour `sylius-b2b-demo`** (0,50 $/jour, soit environ 15 $/mois : le calcul ci-dessus est confirmé) et **2,18 $ pour les sept autres projets** (0,35 $/jour, environ 10 $/mois), là où l'alerte de 30 $ les estimait à ~5 $/mois. Le crédit du plan Hobby étant **par workspace**, ces sept projets ont consommé 40 % du crédit : les auditer et supprimer ceux qui ne servent plus est le premier levier, sans aucun risque pour la démo. Projection de Railway : 15,22 $, alors qu'une extrapolation linéaire des 6 jours donnerait 26 $ ; l'écart suggère une charge non récurrente en début de période (builds du sprint, ouverture publique), à trancher en comparant l'incrément quotidien de `railway usage`.
- Journal du service `web` au 2026-11-19 : aucune boucle de redémarrage (supercronic à l'itération 6862, conteneur démarré vers le 14 novembre), aucun dépassement mémoire de `tmonier:feeds:generate`. Le journal confirme le diagnostic des tâches planifiées : `messenger:consume` démarrait à hh:mm:00 et ne rendait la main qu'à hh:mm:55, soit 55 s résidents par minute, tandis que `tmonier:webhook:dispatch` démarre et réussit dans la même seconde.
- Tarifs (docs.railway.com/reference/pricing, relus le 2026-11-19) : RAM **10 $/Go/mois**, CPU **20 $/vCPU/mois**, egress 0,05 $/Go, volume 0,15 $/Go/mois. Plan Hobby : 5 $/mois d'abonnement, **5 $ d'usage inclus**.
- Ce que coûte la démo au repos, à partir du relevé du 2026-10-17 (1,4 Go de RAM, moins de 0,1 vCPU) : 14 $ de RAM + 2 $ de CPU + 0,15 $ de volume, soit **environ 16 $/mois (0,53 $/jour)**, hors builds et egress. Le crédit du plan Hobby (5 $ inclus + 5 $ ajoutés) couvre donc une vingtaine de jours : le dépassement constaté le 2026-11-19 est le régime permanent de l'architecture, pas un incident, et le plafond visé de 25 $/mois reste tenu.
- Répartition **mesurée le 2026-11-20** (onglet *Usage*, « Cost by Service »), qui remplace l'hypothèse précédente : ce n'est pas MySQL qui porte la facture, c'est le `web`. Voir le relevé de clôture ci-dessous.
- **Relevé de clôture du 2026-11-20** (7 jours écoulés ; workspace 6,31 $, démo 3,74 $). La décomposition par poste lève toutes les hypothèses qui précèdent : **la mémoire fait 97 % de la facture** (6,11 $ sur 6,31 $), le CPU 1,8 % (0,11 $), le reste se partage entre volume, sauvegardes et egress. Tarif confirmé à la source : 0,000231 $/Go/minute, soit 9,98 $/Go/mois. Par service sur la démo : `web` **8 859,66 Go-minute (0,88 Go de moyenne, ~9,0 $/mois)**, `mysql` 4 598,06 (0,46 Go, ~4,8 $/mois), `mailpit` 2 258,92 (0,22 Go, ~2,2 $/mois). Le `web` porte donc **56 % de la mémoire du projet**, contre 29 % à MySQL et 14 % à Mailpit.
- **Railway facture la moyenne minute par minute, pas la jauge instantanée.** C'est la leçon du 2026-11-20, et elle invalide la méthode employée jusque-là : la jauge de `mysql` affichait 1,1 Go, mais sa moyenne facturée n'a jamais dépassé 0,46 Go, le pool InnoDB ne se remplissant qu'au fil des pages réellement touchées. Toute estimation de gain doit partir des Go-minute de l'onglet *Usage*, jamais d'une lecture ponctuelle de l'onglet *Metrics* : les deux diffèrent ici d'un facteur 2,4.
- Leviers, par gain décroissant :
  1. **Pool InnoDB de MySQL** (estimé 7 à 8 $/mois, le plus gros levier du workspace) : le service `mysql` porte déjà une commande de démarrage personnalisée dans le tableau de bord, `docker-entrypoint.sh mysqld --innodb-use-native-aio=0 --disable-log-bin --performance_schema=0 --innodb-buffer-pool-size=1G`. `performance_schema` est donc déjà désactivé, mais **le pool InnoDB est dimensionné à 1 Go** et MySQL 8 le préalloue au démarrage : c'est lui qui explique le gigaoctet attribué à MySQL dans les 1,4 Go du relevé du 2026-10-17. La base de la démo (fixtures Sylius) tient dans quelques dizaines de mégaoctets. Le ramener à `--innodb-buffer-pool-size=256M` libère environ 750 Mio, soit 7,50 $/mois, en gardant de la marge pour la remise à zéro nocturne, qui purge et recharge en une transaction ; `128M` économiserait 100 Mio de plus mais sans marge, d'autant que `--innodb-use-native-aio=0` rend les entrées-sorties plus coûteuses. Contrôle après le changement : la durée annoncée par `DEMO_RESET_OK duration=` dans le journal de 03:00 ne doit pas s'envoler. **Fait le 2026-11-19, mesuré le 2026-11-20 : le gain réel est d'environ 1 $/mois, pas 7,50 $.** La jauge est bien tombée de 1,1 Go à 383 Mo, mais la moyenne facturée n'a baissé que de ~0,46 à ~0,38 Go (voir la leçon de méthode ci-dessus). Le réglage est conservé : il ne coûte rien et borne le pic. Le reset est passé de 27 s à 36,9 s, très en deçà du seuil de 90 s.
  2. **Déclaration de ce réglage** : le SDK IaC ne sait pas l'exprimer. `database()` ne lit que `image`, `output`, `defaultMountPath` et `region`, et un `startCommand` passé à `mysql()` est ignoré sans erreur (vérifié sur le SDK 3.16.0). La commande de démarrage ne vit donc que dans le tableau de bord : vérifier avec `railway config plan` qu'un `apply` ne la retire pas, les réglages non déclarés étant supprimés comme les variables. Si le plan veut la retirer, le seul chemin IaC est de déclarer MySQL en `service()` avec son image, sa commande et son volume de données : cela recrée la base, ce qui est acceptable puisque l'entrypoint recharge les fixtures sur une base vide, mais c'est une décision d'architecture (perte de la base gérée et de `${{mysql.MYSQL_URL}}`).
  3. **Builds** (2 à 4 $/mois en sprint actif) : chaque merge sur `main` construit l'image (composer, yarn, warmup) et est facturé. Déployer une fois par jour plutôt qu'une fois par merge.
  4. **Tâches planifiées** : fait le 2026-11-19. `messenger:consume --time-limit=55` lancé chaque minute restait résident environ 92 % du temps (un worker interroge le transport chaque seconde jusqu'à sa limite au lieu de sortir sur file vide), soit un noyau Sylius en mémoire en continu et quelque 86 000 requêtes de scrutation par jour sur MySQL ; passé en `*/5` avec `--time-limit=20`, environ 7 %. En revanche `tmonier:webhook:dispatch` n'est **pas** résident : sans `--loop` il sort dès que l'outbox est vide (`DispatchWebhooksCommand`), contrairement à ce qu'indiquait la version précédente de cette liste.
  5. **Egress** depuis l'ouverture publique du 2026-11-18 : relever la ligne egress de `railway usage` et durcir le `robots.txt` si des robots tirent les images produit.
- Leviers inopérants ici, mesures à l'appui : baisser les threads FrankenPHP (ADR-0013 : 145 Mio en 8/16 contre 148 Mio en 4/8, aucun gain), le mode worker (il échange environ 2 $/mois de CPU contre 150 Mio résidents, soit +1,5 $/mois, et casse le quick-order), la remise à zéro de 03:00 (30 à 60 s à 1,08 Gio, moins d'un centime malgré l'heure des dépassements). La mention de `MP_MAX_MESSAGES` figurait ici : elle était fausse, Mailpit coûte ~2,2 $/mois et non « moins de 0,50 $ ».
- **Leviers révisés au 2026-11-20, classés sur la mesure et non sur l'estimation :**
  1. **Le conteneur `web`** (~9 $/mois, 56 % de la mémoire du projet). L'ADR-0013 le mesure à 145 Mio *au repos* ; sa moyenne facturée est de 0,88 Go, soit 730 Mo d'écart permanent. Le suspect est le noyau Sylius maintenu résident 92 % du temps par `messenger:consume` (`memory_limit` à 512 Mo), auquel s'ajoutent `tmonier:feeds:generate` tous les quarts d'heure et le pic du reset. Le passage en `*/5` avec `--time-limit=20` devrait retirer plusieurs centaines de mégaoctets de la moyenne, de l'ordre de **3 à 4 $/mois** si le noyau pèse 300 à 400 Mo. Prédiction à vérifier sur les Go-minute 48 h après le déploiement.
  2. **Mailpit** (~2,2 $/mois pour 0,22 Go de moyenne, quatre à cinq fois ce qu'un attrape-mail devrait peser) : `MP_MAX_MESSAGES` passe de 500 à 50 dans `railway.ts`. Une démo remise à zéro chaque nuit n'a pas besoin d'en conserver 500. Si des visiteurs signalent des messages manquants, 100 reste un compromis.
  3. **Les sept autres projets du workspace** (~2,5 $/mois) : le crédit étant mutualisé, les auditer et supprimer ceux qui ne servent plus reste sans aucun risque pour la démo.
  4. **Pool InnoDB** : fait, ~1 $/mois.
- Plancher réaliste après le réglage de MySQL : **8 à 11 $/mois**. Passer sous 5 $ n'est pas atteignable avec MySQL, Sylius et un volume en continu ; il faudrait quitter la facturation à l'usage pour un hébergement à prix fixe.
- Alerte souple : portée à **40 $** le 2026-11-20. À ce niveau elle ne prévient de rien ; l'abaisser à environ 12 $ pour être prévenu avant la fin du crédit. Toujours pas de limite dure (elle arrêterait tous les services du workspace).
