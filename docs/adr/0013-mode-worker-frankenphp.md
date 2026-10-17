# ADR-0013 — Mode worker FrankenPHP : classique retenu, threads bornés

- **Statut** : Accepté (2026-10-16)
- **Contexte projet** : sylius-b2b-demo, sprint 9 (US-006, spike)

## Contexte

La démo tourne en mode classique (ADR-0002) : FrankenPHP exécute `public/index.php` à chaque requête, avec `total_threads=16 worker_threads=0` sur Railway. Le `docker/Caddyfile` n'avait ni `num_threads` ni `max_threads` (risque d'OOM relevé au sprint 1, constat R5, jamais traité). Le mode worker garde le noyau Symfony en mémoire entre deux requêtes et promet un gain de latence, au prix d'un risque de fuite d'état et de services restés branchés sur l'ancien état de Doctrine.

Le spike répond à trois questions : le gain est-il significatif, le mode worker fuit-il de l'état entre visiteurs, la mémoire tient-elle avec supercronic dans le même conteneur.

## Décision

1. **La démo reste en mode classique.** Le mode worker n'est pas activé en production.
2. **Les threads sont bornés par défaut** dans le bloc global `frankenphp {}` du Caddyfile : `FRANKENPHP_NUM_THREADS=8` (démarrage) et `FRANKENPHP_MAX_THREADS=16` (plafond). Les deux sont pilotables par variable d'environnement, par exemple `FRANKENPHP_MAX_THREADS=8` si Railway impose un plafond mémoire plus bas.
3. **Le mode worker reste disponible derrière un drapeau, désactivé par défaut** : `FRANKENPHP_WORKER_MODE=1` (nombre de workers : `FRANKENPHP_WORKERS`, 4 par défaut). L'entrypoint renseigne alors `FRANKENPHP_WORKER_DIRECTIVE` (`worker /app/public/index.php N`), que le Caddyfile insère dans `php_server`. `symfony/runtime` 7.4 détecte seul le thread worker et boucle sur `frankenphp_handle_request()` : aucune dépendance ajoutée, aucun script de worker maison. Sans le drapeau, la ligne ne produit rien et le mode classique s'applique.

La raison du maintien en classique : **le mode worker casse l'ajout au panier par saisie rapide (quick-order) à chaque appel** (voir « Régression »), alors que le gain mesuré est réel mais ne compense pas ce défaut. Le scénario « Repli en mode classique » du spike s'applique.

## Méthode de mesure

- Image construite depuis la branche `feature/sprint-9-reports` (un seul build, `sylius-b2b-demo-web:spike`), conteneur lancé avec `docker run` sur le réseau de `compose.yaml`, base MySQL dédiée `sylius_b2b_demo_spike_w` (fixtures `demo` chargées par l'entrypoint). Le même conteneur, supercronic compris, sert les deux modes ; un seul conteneur de mesure tourne à la fois.
- Machine : poste de développement (8 cœurs, Docker local), partagée avec d'autres tâches pendant la mesure : les valeurs absolues sont indicatives, la comparaison classique / worker (même jour, même base, mêmes pages) est la donnée utile.
- Outil : ApacheBench, `docker/bench/bench.sh` (60 requêtes d'échauffement exclues, puis 600 requêtes à 4 en parallèle par page), sessions préparées par le script (panier B2C de 2 lignes, compte Claire connecté). Mémoire : `memory.current` du cgroup (cache de pages inclus) et somme des `VmRSS` des processus, échantillonnées chaque seconde, supercronic actif (tâche `tmonier:webhook:dispatch` chaque minute pendant la mesure).
- Rejouable : `BASE=http://localhost:8090 CONTAINER=<nom> docker/bench/bench.sh`.

## Résultats

### Latence (ms), 600 requêtes à 4 en parallèle, cron actif

| Page | Classique p50 | Classique p95 | Worker p50 | Worker p95 | Gain p50 |
|------|---------------|---------------|------------|------------|----------|
| Accueil B2B (anonyme) | 44 | 58-61 | 25 | 36 | -43 % |
| Catégorie | 49-50 | 63-64 | 30 | 41 | -40 % |
| Fiche produit | 45-46 | 57-58 | 26 | 34 | -43 % |
| Panier (B2C, 2 lignes) | 40 | 49-54 | 25 | 39 | -38 % |
| Compte (Claire, B2B) | 91-92 | 106-108 | 43 | 54 | -53 % |
| Route d'API (prix dégressifs, 401) | 14 | 18 | 5 | 7 | -64 % |

Le mode classique a été mesuré deux fois (deux colonnes en intervalle, écart de 1 à 5 ms), le mode worker (4 workers) une fois. Débit sur l'accueil : 86 req/s en classique, 151 req/s en worker. Sous 32 connexions parallèles sur la catégorie, 1 500 requêtes : classique p50 347 ms / p95 445 ms (90 req/s), worker p50 210 ms / p95 270 ms (146 req/s).

Le gain est donc de 40 à 50 % sur les pages, soit environ 20 ms par page à charge légère. Il est réel, mais la démo est un usage de présentation et non de volume.

Limite (revue du sprint 9) : le mode worker n'a été mesuré qu'en une seule passe, sans écart-type, sur un poste partagé. L'ordre de grandeur est plausible, mais ce n'est pas une mesure reproductible. Si le mode worker est réévalué, il faudra au moins 3 passes par mode, avec l'écart entre les passes.

### Mémoire du conteneur (supercronic compris)

| Situation | Classique | Worker (4 workers) |
|-----------|-----------|--------------------|
| RSS du processus `frankenphp` après la charge de `bench.sh` | 247-288 Mio | 371 Mio |
| Pic cgroup pendant `bench.sh` | 332-374 Mio | 579 Mio |
| Pic cgroup à 32 connexions parallèles | 355-512 Mio (8/16 threads) | 475 Mio |
| `supercronic` | 7-11 Mio | 10-11 Mio |
| Après 5 000 requêtes en worker (rotation à 500 requêtes) | - | 403 Mio puis 393 Mio : stable |

Bornage des threads en classique, 1 500 requêtes à 32 en parallèle sur la catégorie :

| `num_threads` / `max_threads` | Mémoire au repos | Pic cgroup | Débit | p50 / p95 |
|-------------------------------|------------------|------------|-------|-----------|
| 16 / 16 (état de production avant ADR) | 209 Mio | 450 Mio | 86,8 req/s | 350 / 454 ms |
| 8 / 16 (retenu) | 145 Mio | 355 Mio | 88,6 req/s | 348 / 454 ms |
| 4 / 8 | 148 Mio | 324 Mio | 87,8 req/s | 345 / 450 ms |

Le débit est limité par le CPU, pas par le nombre de threads : réduire les threads ne dégrade pas la latence mesurée en local. Les valeurs retenues (8 / 16) gardent le plafond historique de 16 et baissent la mémoire au repos ; la base MySQL de Railway étant distante, des threads supplémentaires y absorbent mieux l'attente d'entrée-sortie, d'où le refus de descendre à 4 / 8 sans mesure sur Railway. En mode worker FrankenPHP exige `max_threads >= num_threads + workers` (démarrage refusé sinon) : 8 + 4 = 12 <= 16.

Le gros consommateur de mémoire n'est pas le serveur web mais la remise à zéro de 03:00 : `app:demo:reset` (limite PHP de 512 Mio dans `docker/php.ini`, terminé en 27 s ici) a fait monter le cgroup du conteneur à environ 1,08 Gio en pic (cache de pages inclus), serveur au repos. C'est le chiffre à comparer au plafond du service Railway, mode classique comme worker (le mode worker ajoute alors environ 150 Mio de workers résidents). `tmonier:feeds:generate --stale --profile` a levé une fois « Allowed memory size of 536870912 bytes exhausted » ; relancé sans `--profile` il passe (les tâches cron n'utilisent pas `--profile`), non reproduit depuis : à surveiller dans les logs de la nuit.

### Fuite d'état entre requêtes

Script : `docker/bench/leak-test.sh`, exécuté contre un conteneur worker à **un seul worker** (toutes les requêtes passent par le même processus PHP), porte de démo active, 15 tours : visiteur A (canal B2C, panier de 2 lignes, `demo_badges=off`, `fr_FR`) puis visiteur B anonyme ; client Claire connecté puis visiteur sans session ; Léa (B2C) après Claire (B2B) ; `fr_FR` puis `en_US` puis chemin nu ; identifiants valides puis requête sans identifiants ou avec un mauvais mot de passe.

| Cible | Vérifications | Fuites |
|-------|---------------|--------|
| Mode worker, porte de démo active | 190 | **0** |
| Mode classique (témoin de non-régression du script) | 173 | 0 |
| Worker avec témoin de fuite injecté (`docker/bench/witness-index.php`) | 41 | **9** (canal, formulaire d'achat, session) |

Le témoin est un décorateur de noyau qui retient le cookie de canal d'une requête pour la suivante, comme le ferait un service à état. Le script le détecte (code de sortie 1), ce qui prouve qu'il sait voir une fuite **de ce type**.

Portée réelle du résultat :
- **Ce qui est couvert.** Sur les parcours testés, aucun état de visiteur observable dans les réponses ne passe d'une requête à l'autre en mode worker : canal, client, panier, locale, badges, session, porte de démo.
- **Témoin limité.** Le témoin ne simule qu'une fuite de cookie. Il ne prouve pas que le script détecterait une fuite d'état Doctrine (EntityManager, identity map), qui est justement la classe de défaut rencontrée ci-dessous.
- **Ajout au panier non vérifié pendant la mesure.** Le script ne contrôlait pas le résultat de `quick-order/add`. La régression ci-dessous (500 en mode worker) n'est donc pas comptée dans les 190 vérifications. Le script la compte désormais comme un échec (revue du sprint 9).

Conclusion : pas de fuite de cookie ni de session observée, mais le mode worker n'est pas démontré sûr pour les écritures Doctrine. C'est une raison de plus de rester en mode classique.

### Régression en mode worker (bloquante)

`POST /en_US/quick-order/add` répond **HTTP 500 à chaque appel** en mode worker, y compris sur un worker neuf, et passe en mode classique :

```
Doctrine\ORM\ORMInvalidArgumentException: A new entity was found through the relationship
'App\Entity\Order\Order#channel' that was not configured to cascade persist operations for entity:
Fournitures Pro — Particuliers.
  at Doctrine\ORM\EntityManager->flush()
  at Tmonier\SyliusQuickOrderPlugin\Adder\BulkCartAdder->add()   (BulkCartAdder.php:144)
```

Le canal attaché au panier n'est pas géré par l'EntityManager qui flushe : un service gardé en mémoire entre les requêtes fournit une entité (ou un gestionnaire) issue d'avant la réinitialisation de Doctrine, ce que le mode classique masque puisque tout est reconstruit à chaque requête. Le service exact n'est pas identifié (hors périmètre du spike). Ce défaut est le même pour tout ce qui crée un panier côté serveur ; l'ajout au panier de la fiche produit (composant Live) n'a pas pu être exercé de bout en bout pendant le spike et reste donc à vérifier avant tout passage en worker.

## Inventaire des services à état

| Service | État entre requêtes | Verdict |
|---------|--------------------|---------|
| `DemoChannelContext`, `BadgePreference`, `ConsumerChannelVoter` | Aucun (lisent le cookie de la requête courante) | Sûr |
| `DemoGateListener`, `BasicAuthGate`, `DemoConfigurationGuard`, `InternalRequestToken`, `HostAllowList` | Propriétés `readonly` ou immuables | Sûr (test de fuite vert avec la porte active) |
| `RateLimitSubscriber` | Compteurs dans le pool de cache `cache.demo_rate_limiter`, pas en mémoire de processus | Sûr, mais partagé entre workers par le cache fichier |
| `OutboundGuardHttpClient` | `ResetInterface`, remet le client interne à zéro | Sûr |
| `CachedPerRequestChannelContext` (Sylius) | `SplObjectStorage` indexé par objet `Request` : conserve chaque requête et son canal jusqu'au recyclage du worker | Croissance mémoire bornée par `FRANKENPHP_LOOP_MAX` (500 par défaut dans `symfony/runtime`), observée stable |
| Plugin quick-order, chemin `BulkCartAdder` | Entité canal hors de l'EntityManager courant | **Défaut bloquant** |

## Observation hors périmètre

Dans le conteneur de production construit, les routes d'API Platform de la boutique (`/api/v2/shop/products`, `/api/v2/shop/locales`, `/api/v2/shop/customers/token`) répondent 404 alors que `router:match` les reconnaît ; la route des prix dégressifs répond 401 comme attendu. Le test `ChannelAccessSmokeTest` les exerce en environnement de test. À investiguer séparément : le spike a donc mesuré une route d'API (prix dégressifs, refus 401) à la place d'une liste JSON.

## Alternatives considérées

- **Passer en worker maintenant** : refusé, régression bloquante ci-dessus et gain absolu de l'ordre de 20 ms par page.
- **Ajouter `runtime/frankenphp-symfony`** : inutile, `symfony/runtime` 7.4 intègre `FrankenPhpWorkerRunner`.
- **Script de worker maison** : écarté après essai, il entrait en conflit avec le runner natif (« frankenphp_handle_request() called while not in worker mode »).
- **Descendre à 4 / 8 threads** : mesuré sans régression en local, non retenu sans mesure sur Railway (base distante).

## Conséquences

- La prod ne change que par le bornage 8 / 16 (plafond identique, mémoire au repos plus basse) ; aucun GO utilisateur n'est nécessaire pour le mode worker puisqu'il reste désactivé.
- Pour rouvrir le sujet : identifier le service qui garde le canal (ou l'EntityManager), corriger, rejouer `docker/bench/leak-test.sh` (et ajouter le passage de commande complet au script), puis refaire la mesure sur Railway.
- Le script de fuite lance aussi bien le mode classique que le mode worker : il sert de garde pour tout futur passage en worker.
- Revue de la méthode de mesure et du test de fuite : tâche T-006-05, par l'orchestrateur.
