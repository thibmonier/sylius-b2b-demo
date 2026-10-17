# Runbook : remise à zéro nocturne de la démo (US-033)

## Ce qui se passe à 03:00

`supercronic` (conteneur web) lit `docker/crontab` avec `CRON_TZ=Europe/Paris` : la tâche part à **03:00 heure de Paris**, heure d'été comprise (supercronic journalise « processes will be spawned using TZ: Europe/Paris »). Les tâches fréquentes (webhooks chaque minute, flux toutes les 15 minutes) continuent ; les tâches quotidiennes commencent à 04:15, bien après la fin du reset.

La commande `app:demo:reset` :

1. refuse de s'exécuter sans `DEMO_MODE=1` (code de sortie 1, base intacte) ; elle est voulue en `APP_ENV=prod` de démo et nulle part ailleurs ;
2. recharge la suite de fixtures `demo` dans **une seule transaction** : la purge (`DELETE`) et le rechargement sont validés ensemble ou pas du tout. Un échec à n'importe quel moment rend la démo **dans l'état précédent**, intacte ;
3. purge le cache d'images, puis vide et réchauffe le cache applicatif (après validation de la transaction). `cache:clear` et `cache:warmup` tournent chacun dans un **sous-processus PHP** (`App\Demo\Command\ConsoleSubprocess`) : le processus des fixtures garde sa mémoire pleine, le réchauffage repart d'une mémoire fraîche. Le sous-processus utilise le même binaire PHP (`PHP_BINARY`), le même utilisateur (`www-data` sur Railway, les fichiers de cache restent écrivables par le serveur), l'environnement hérité (`APP_ENV`, `DATABASE_URL`…) et la limite mémoire du parent : le `-d memory_limit=1G` du crontab s'applique donc aussi à lui. Un code de sortie non nul (255 après une erreur fatale) fait échouer le reset (`DEMO_RESET_FAILED`, marqueur, code 1) ; les arguments sont passés en liste, sans shell.

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

- Le marqueur `var/demo-reset-failed` (`App\Demo\Command\ResetStatus`) est posé **au début** du reset (état `running`) et retiré **au seul succès**. Une erreur fatale PHP (« Allowed memory size exhausted ») n'est pas une exception : rien ne peut la signaler après coup, d'où ce marqueur posé d'avance (incident du 2026-10-17 : le reset est sorti en 255 sans marqueur, `/healthz` est resté 200).
- Un reset en échec écrit `DEMO_RESET_FAILED` (stderr, journal Railway) et fait passer le marqueur à l'état `failed`. Un processus qui s'arrête sans rapport (erreur fatale, `exit`) est rattrapé par une fonction d'arrêt (`register_shutdown_function`) qui pose `failed` aussitôt. Un processus tué sans code PHP (SIGKILL, tueur de mémoire du noyau, arrêt du conteneur) laisse `running` : au-delà de 30 minutes (`ResetStatus::MAX_RUN_SECONDS`) il compte comme un échec.
- Tant que le marqueur est `failed` (ou `running` depuis plus de 30 minutes), `/healthz` et `/health` répondent **503 `reset failed`** ; un reset en cours (marqueur `running` récent, quelques minutes) ne déclenche **pas** l'alerte, pour que le moniteur n'alerte pas chaque nuit à 03:00. La démo continue de servir ses données précédentes (reset atomique). Le prochain reset réussi efface le marqueur (un reset qui démarre conserve un marqueur `failed` précédent jusqu'à son succès) ; un nouveau déploiement repart sans marqueur.
- Aucun marqueur n'est posé pour `--dry-run` ni pour un refus (`DEMO_MODE` absent, ou autre reset en cours).
- Deux resets (cron et manuel) ne tournent jamais ensemble : un verrou `flock` non bloquant (`var/demo-reset-failed.lock`, `ResetLock`) fait refuser le second (`DEMO_RESET_REFUSED … already running`, code 1, marqueur du premier intact). Le verrou disparaît avec le processus (SIGTERM compris) : un fichier de verrou resté sur le disque ne bloque rien.
- Chaque sous-processus de cache a un délai de 300 s ; `MAX_RUN_SECONDS` (1 800 s) reste au-dessus du pire cas d'un reset vivant (2 × 300 s + fixtures + marge, vérifié par `ResetStatusTest`). L'écriture du marqueur est atomique (fichier temporaire puis `rename`).
- Un **moniteur externe** (UptimeRobot, Better Stack…, compte de l'exploitant) interroge `https://<domaine>/healthz` toutes les 5 minutes et envoie un e-mail sur toute réponse différente de 200 : il couvre à la fois une démo en panne et un reset en échec. `/healthz` est hors de la porte privée, aucun identifiant n'est nécessaire.
- Les e-mails natifs de Railway (déploiement en échec, conteneur « Crashed ») restent actifs (réglage du compte, *Account Settings > Notifications*).

La démo n'émet aucun trafic sortant pour alerter : c'est le moniteur qui vient la lire.

## Plafond du corps du récepteur de webhooks (US-049)

`docker/Caddyfile` limite le corps de `POST /demo/webhooks/receive` (`request_body`, `max_size 262145` = `MAX_BODY_BYTES` du contrôleur + 1 octet). Sous FrankenPHP, Caddy ne répond pas 413 lui-même : il coupe le flux et PHP ne lit jamais plus de 262 145 octets, y compris en transfert chunked (sans `Content-Length`, le contrôleur lisait tout avant de mesurer). Le dernier octet fait déclencher le test `strlen > MAX_BODY_BYTES` du contrôleur, qui répond 413. Vérifications : `CaddyfileTest` (alignement des valeurs et portée du chemin), `frankenphp validate` et `bin/check-caddy-body-limit` (CI, job « Docker image » ; en local `CADDYFILE=docker/Caddyfile bin/check-caddy-body-limit <image>`).

## Relance manuelle

Dans le conteneur web, **en `www-data`** (le serveur et le cron tournent sous cet utilisateur) :

```bash
railway ssh --service web -- su -s /bin/sh www-data -c 'php -d memory_limit=1G bin/console app:demo:reset --no-interaction'
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

## Profilage du reset et objectifs (US-048 / T-048-04)

Principe : **aucun objectif chiffré sans mesure**. Le sprint 9 avait fixé « reset −50 % » sans profil (−13 % obtenu) ; ce tableau remplace cette cible par un objectif par poste.

Mesure reproductible, base de test migrée (voir [tests-locaux.md](./tests-locaux.md)) :

```bash
TEST_DB_NAME=ma_base_de_test bin/profile-reset 3    # 3 passes ; chaque passe recharge la suite « demo » en entier
```

Le script lance `sylius:fixtures:load demo` en `APP_ENV=test`. Le décorateur `tests/Support/FixtureProfiler.php` (inactif sans `PROFILE_RESET_OUT`) compte les requêtes SQL de chaque fixture grâce au compteur DBAL `tests/Support/QueryCounter.php` ; la mémoire est le pic RSS du processus (`/usr/bin/time`).

### Mesure initiale

Relevé du **2026-10-17**, base `ff1dd86` (branche `feature/sprint-10-vitrine-et-reports`, avant tout correctif de la US-048), 3 passes, MySQL 8.4 local. Les images de la US-047 ne sont **pas** encore associées par `demo_products_csv` : le profil changera quand elles le seront (à refaire alors, voir la fin de la section). L'écart de requêtes entre passes est nul (reset déterministe) ; seule la durée varie.

| Fixture | SELECT | INSERT | UPDATE | Total | Durée (s, min-max) |
|---|---:|---:|---:|---:|---|
| `demo_products_csv` | 308 | 3 890 | 9 | **4 207** | 0,88-0,92 |
| `demo_container_fill` | 1 211 | 993 | 1 | **2 205** | 0,45-0,46 |
| `demo_orders` | 822 | 997 | 86 | **1 963** | 1,59-1,93 |
| `demo_taxons` | 16 | 42 | 40 | 98 | 0,03-0,05 |
| `demo_companies` | 27 | 38 | 0 | 65 | 0,03-0,05 |
| `demo_tier_pricing` | 37 | 24 | 0 | 61 | 0,02 |
| `tmonier_container_fill_container_types` | 14 | 39 | 0 | 53 | 0,01-0,02 |
| `demo_quotes` | 24 | 7 | 0 | 31 | 0,01 |
| `demo_gpsr` | 12 | 17 | 0 | 29 | 0,02 |
| `channel` | 16 | 11 | 0 | 27 | 0,02-0,03 |
| Les 21 autres fixtures | 68 | 92 | 8 | 168 | < 0,3 chacune (`demo_punchout` 0,25) |
| Purge et écouteurs, hors fixtures | | | | 157 | |
| **Reset complet** | **2 555** | **6 150** | **144** | **9 064** | **4,04-4,48** |

Pic mémoire : **≈ 458 Mo** de RSS pour le processus entier (458 à 466 Mo selon la passe), `APP_ENV=test`, sans débogage. Ce n'est pas le « ≈ 1,08 Go » du sprint 9 : la source de cette valeur n'est pas reproductible ici (mesure faite sur un autre environnement ou avec une autre jauge) ; la comparaison à la limite Railway relève de T-048-13 (commande ci-dessous), jamais d'une estimation.

Où vont les requêtes (statements les plus fréquents, `bin/profile-reset` les affiche) :

- `demo_products_csv` : 420 variantes × (traductions, prix, journal de prix, canaux) + 1 160 valeurs d'attributs. Ce sont des `INSERT` ORM inévitables sans contourner l'ORM ; **hors cible** (la fixture est en lecture seule pour la US-048, elle appartient à la US-047).
- `demo_container_fill` : 776 `SELECT` de capacité + 210 `SELECT` de logistique + 210 `SELECT` de variante, chacun suivi d'un `INSERT` (776 capacités, 210 logistiques). Les `SELECT` retrouvent des lignes qui n'existent pas encore : la base vient d'être purgée.
- `demo_orders` : 12 commandes ; 725 `INSERT` d'unités (une ligne par unité, inévitable avec l'ORM) ; 264 `SELECT` de promotions et 72 de règles et d'actions (processeur de promotions de Sylius, une vingtaine par commande : cœur Sylius, non touché) ; le reste (≈ 350 `SELECT`) est le chargement unitaire de variantes, clients, membres, méthodes de livraison et de paiement, détection de doublon par référence.

### Objectifs par poste

Fixés **avant** toute optimisation, à partir du tableau ci-dessus. Un objectif non atteint est publié tel quel, avec son écart.

| Poste | Mesure initiale | Objectif | Raisonnement |
|---|---|---|---|
| `demo_container_fill`, `SELECT` | 1 211 | **≤ 40** | Un chargement par lot des variantes, des logistiques et des capacités existantes (3 requêtes + les types) remplace une recherche par ligne. |
| `demo_container_fill`, `INSERT` | 993 | **≤ 60** | Insertion multi-lignes (lots de 250) des lignes nouvelles ; mise à jour par l'ORM des lignes déjà présentes. Si le chemin multi-lignes se révèle risqué (données non identiques), l'objectif retombe à 993 (un `INSERT` ORM par ligne) et l'écart est publié. |
| `demo_container_fill`, total | 2 205 | **≤ 100** | Somme des deux lignes ci-dessus. |
| `demo_orders`, `SELECT` | 822 | **≤ 600** | Seul le chargement unitaire (≈ 350 `SELECT`) est à notre portée : variantes en un lot, méthodes et clients mémorisés. Les 336 `SELECT` de promotions sont dans le cœur de Sylius. |
| `demo_orders`, `INSERT` et `UPDATE` | 997 et 86 | inchangés | Pas de contournement de l'ORM pour les commandes : une commande doit passer par les processeurs et les machines à états de Sylius. |
| `demo_orders`, total | 1 963 | **≤ 1 750** (−11 %) | |
| **Reset complet** | 9 064 | **≤ 7 000** (−23 %) | Conséquence des deux postes ci-dessus (−2 100 pour `demo_container_fill`, −210 pour `demo_orders`) ; le reste (`demo_products_csv`, 4 207) est hors périmètre. |

Dans tous les cas, **les données ne changent pas** : empreinte (comptes et sommes de contrôle des tables concernées) comparée avant et après chaque optimisation.

### Mesures après optimisation

Relevé du **2026-10-17**, 3 passes, base `db01c13` plus les modifications non encore validées de la branche (US-029 et US-047 en cours : `demo_products_csv` compte maintenant 171 `INSERT` d'images et `demo_webhook_endpoint` existe). Les chiffres des deux fixtures de la US-048 ne dépendent pas de ces ajouts ; le total, si.

| Poste | Avant | Objectif | Après | Verdict |
|---|---:|---:|---:|---|
| `demo_container_fill`, `SELECT` | 1 211 | ≤ 40 | **18** | atteint |
| `demo_container_fill`, `INSERT` | 993 | ≤ 60 | **12** | atteint (lots de 250 lignes, SQL multi-lignes) |
| `demo_container_fill`, total | 2 205 | ≤ 100 | **31** | atteint ; 0,45 s devient 0,10 s |
| `demo_orders`, `SELECT` | 822 | ≤ 600 | **770** (−6 %) | **non atteint**, écart publié |
| `demo_orders`, `INSERT` et `UPDATE` | 997 et 86 | inchangés | 997 et 86 | conforme |
| `demo_orders`, total | 1 963 | ≤ 1 750 | **1 911** (−3 %) | **non atteint**, écart publié |
| Reset complet | 9 064 | ≤ 7 000 | **7 014** brut, **≈ 6 840** à périmètre égal | atteint à périmètre égal (le brut compte les 172 requêtes ajoutées par la US-047 aux produits) |

Pourquoi `demo_orders` s'arrête à 770 `SELECT` : l'objectif de 600 supposait environ 350 recherches unitaires à notre portée ; il n'y en avait qu'une cinquantaine. Ce que la fixture contrôlait (variantes des lignes, clients, méthodes de livraison et de paiement) est maintenant lu une fois (−52). Le reste est du cœur de Sylius et des plugins : 264 `SELECT` de zones et 72 de promotions (les processeurs de commande tournent à chaque transition du tunnel de commande : environ 8 passes par commande), 39 `SELECT` des membres d'entreprise (plugin des comptes d'entreprise), 34 de limites d'approbation, 30 de points d'accès de webhooks (US-029), 27 de factures. Les réduire demanderait de mémoïser ces processeurs ou de contourner le tunnel de commande, ce que le sprint n'a pas retenu (une commande de démonstration doit passer par la même machine à états que celle d'un client). Les `INSERT` d'unités (725) et d'adresses restent des `INSERT` ORM, un par ligne.

**Limite actée (US-049, sans code) :** `demo_orders` reste à **1 911 requêtes pour un objectif de 1 750** (770 `SELECT` pour 600 visés). L'écart vient du cœur de Sylius (processeurs de commande rejoués à chaque transition du tunnel) et des plugins ; l'objectif n'est pas atteint et n'est pas rouvert. Le reset complet tient son objectif à périmètre égal.

Données : empreinte des tables (nombre de lignes et somme de contrôle des colonnes stables, ids compris, base vidée par `TRUNCATE` avant chaque chargement pour que les ids repartent de 1) comparée avant et après : **identique** pour `tmonier_container_variant_capacity` (776 lignes), `tmonier_container_variant_logistics` (210) et toutes les tables de commandes (`sylius_order*`, `sylius_payment`, `sylius_shipment`, `tmonier_timeline_entry`, approbations, `tmonier_company_order`). Les seules différences sont les tables des autres travaux (`sylius_product_image`, `tmonier_webhook_endpoint`, `app_demo_load_marker`) et `sylius_invoicing_plugin_*` et `sylius_tax_category`, qui varient d'un chargement à l'autre même sans changement de code (numérotation et identifiants aléatoires).

Le chemin du reset nocturne (`app:demo:reset`) ajoute à `sylius:fixtures:load`, pour `demo_orders`, 126 `INSERT` du journal d'audit et 87 paires `SAVEPOINT` : le tableau ci-dessus mesure le chargement des fixtures, comme au sprint 9.

Tests de non-régression (rouges avant, verts après) : `tests/Functional/Demo/ContainerFillFixtureQueryCountTest.php` (≤ 40 `SELECT`, ≤ 60 `INSERT`, mêmes lignes) et `tests/Functional/Demo/OrderHistoryFixtureQueryCountTest.php` (≤ 770 `SELECT`, mêmes commandes). À refaire quand la US-047 associera définitivement les images : `bin/profile-reset 3`.

### Pic mémoire sur Railway (T-048-13, utilisateur)

L'image n'a pas `/usr/bin/time`. La commande lance le reset et relève le compteur mémoire du conteneur (cgroup v2) toutes les demi-secondes :

```bash
railway ssh --service web -- sh -c 'su -s /bin/sh www-data -c "php -d memory_limit=1G bin/console app:demo:reset --no-interaction" >/tmp/reset.log 2>&1 & p=$!; m=0; while kill -0 $p 2>/dev/null; do c=$(cat /sys/fs/cgroup/memory.current); [ "$c" -gt "$m" ] && m=$c; sleep 0.5; done; wait $p; echo "reset exit=$? ; pic conteneur: $((m/1048576)) Mo"'
```

**Relevé du 2026-10-17** (déploiement du sprint 10, `a4cd868`) : pic du conteneur **≈ 1,05 Go**, pour une limite de réplique de **8 Go** (8 vCPU). La marge est large. Les fixtures se sont chargées en 16,1 s.

Ce relevé a révélé un défaut. Le reset lance `cache:clear` puis `cache:warmup` dans le même processus que le chargement des fixtures. Avec la limite PHP de 512 Mo de `docker/php.ini`, le réchauffage s'est arrêté (« Allowed memory size of 536870912 bytes exhausted ») : le reset sortait en erreur alors que les données étaient rechargées. Le reset nocturne (`docker/crontab`) et les commandes manuelles passent donc `-d memory_limit=1G`, comme le chargement initial de l'entrypoint. Le pic du conteneur reste très en dessous de la limite.

## Tests

`tests/Functional/Demo/DemoResetCommandTest.php` (garde, succès, échec atomique, purge Acme), `tests/Unit/Demo/Command/DemoResetCommandTest.php` (options, garde, rollback). La bannière « prochaine remise à zéro » (T-033-05) est glissée au sprint 7.
