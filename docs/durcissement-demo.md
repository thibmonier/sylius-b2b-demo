# Durcissement de la démo publique

Application des US-032 (porte privée) et US-036 (durcissement). Le code est dans `src/Demo/Http`, la configuration dans `config/packages/demo_hardening.yaml` et `config/packages/demo_framework.yaml`. Contexte et décision : [ADR-0008](adr/0008-securite-de-la-demo-publique.md).

## Porte privée (authentification HTTP basique)

- Active quand `DEMO_MODE=1`, sauf `DEMO_BASIC_AUTH=0`. Toute requête sans identifiants valides reçoit `401` avec `WWW-Authenticate`.
- Exemptés : `/health` et `/healthz`. Les fichiers statiques servis directement par Caddy (`/build`, `/bundles`, `/media`) ne passent pas par PHP et restent accessibles.
- Les échecs de mot de passe sont comptés par IP visiteur : au-delà de `DEMO_RATE_LIMIT_GATE` échecs par 10 minutes, `429`.
- Le simulateur PunchOut appelle la boutique en boucle locale : il prouve son origine avec l'en-tête `X-Demo-Internal` (HMAC de `APP_SECRET`), accepté par la porte et par l'endpoint PunchOut.
- Fail-closed : porte active sans `DEMO_BASIC_AUTH_USER` / `DEMO_BASIC_AUTH_PASSWORD_HASH` valides (le hash est celui de `password_hash`, jamais un mot de passe en clair) : toutes les pages répondent `503` et `/health` aussi. `php bin/console app:demo:preflight` fait la même vérification pour l'entrypoint du conteneur.

Générer le hash : `php -r 'echo password_hash("<mot de passe>", PASSWORD_ARGON2ID), PHP_EOL;'`. Dans un fichier `.env`, entourer la valeur de guillemets simples (le `$` y est interprété).

## Variables d'environnement

| Variable | Défaut | Valeur prévue sur Railway |
|----------|--------|---------------------------|
| `DEMO_BASIC_AUTH` | `1` | `1` tant que la démo est privée, `0` sur GO |
| `DEMO_BASIC_AUTH_USER` | vide | choisi par l'opérateur (secret) |
| `DEMO_BASIC_AUTH_PASSWORD_HASH` | vide | hash Argon2id (secret) |
| `TRUSTED_HOSTS` | `^.*$` | expression exacte du domaine Railway, ex. `^demo\.up\.railway\.app$` (`healthcheck.railway.app` est toujours accepté) |
| `TRUSTED_PROXIES` | `127.0.0.1,REMOTE_ADDR` | inchangé ; `127.0.0.1` doit rester dans la liste (simulateur) |
| `DEMO_RATE_LIMIT_LOGIN` | `30` (par 15 min) | `30` |
| `DEMO_RATE_LIMIT_PUBLIC` | `120` (par minute) | `120` |
| `DEMO_RATE_LIMIT_FORMS` | `10` (par heure) | `10` |
| `DEMO_RATE_LIMIT_PUNCHOUT_START` | `10` (par 10 min) | `10` |
| `DEMO_RATE_LIMIT_PUNCHOUT_ORDER` | `10` (par heure) | `10` |
| `DEMO_RATE_LIMIT_GATE` | `20` (échecs par 10 min) | `20` |
| `DEMO_RATE_LIMIT_HEALTH` | `30` (par minute) | `30` : `/health` et `/healthz`, par IP visiteur ; un moniteur toutes les 10 s fait 6 appels |
| `DEMO_REGISTRATION_OPEN` | `1` | `1` tant que la démo est privée (inscriptions validées par Sophie), **`0` avant l'ouverture publique** |
| `DEMO_PUNCHOUT_DAILY_ORDERS` | `50` | `50` |
| `DEMO_PUNCHOUT_PUBLIC_ENDPOINT` | `0` | `0` |
| `DEMO_MAILER_ALLOWED_HOSTS` | `mailpit,localhost,127.0.0.1` | nom d'hôte privé de Mailpit (`*.railway.internal` toujours accepté) |
| `DEMO_OUTBOUND_ALLOWED_HOSTS` | `mailpit,api.iconify.design` | inchangé |

## Constats traités

| Constat | Statut | Comment |
|---------|--------|---------|
| S2-R1 rate limiting, mailer en puits | Corrigé | Limiteurs par IP visiteur (`RateLimitSubscriber`, 429 + `Retry-After`) sur connexions boutique et admin, `/demo/accounts`, `/demo/mails`, inscription, mot de passe oublié, contact, demande de devis ; `login_throttling` par identifiant ; `MAILER_DSN` vérifié (Mailpit interne ou transport nul), client HTTP et transport des webhooks bornés aux hôtes internes (`OutboundGuardHttpClient`). |
| S4-R2 données réelles | Corrigé (avertissement, inscription conservée) | Encart FR/EN sur l'inscription, le carnet d'adresses et les étapes adresse et validation du checkout. |
| S4-R4 session anonyme du bandeau | Corrigé | Jetons CSRF des connexions `shop_authenticate` et `admin_authenticate` sans état (contrôle de l'origine) ; `Vary: Cookie` sur toutes les réponses. |
| S4-R5 locales des pages légales | Corrigé | 404 hors des locales du canal courant (`LegalPagesLocaleListener`). |
| S4-R6 cookie `demo_badges` | Corrigé | `Secure` dès que la requête effective est HTTPS (proxy de confiance). |
| S5-R1 simulateur public | Corrigé | État de session à usage unique (rejeu refusé), plafond quotidien de commandes anonymes, limiteurs par IP, `POST /punchout/cxml` réservé au simulateur. |
| S5-R2 hôtes et proxys | Corrigé | `TRUSTED_HOSTS` (400 immédiat pour un hôte inconnu), `TRUSTED_PROXIES`. |
| S5-R3 limiteur du plugin | Corrigé côté démo | Le simulateur transmet l'IP du visiteur (`X-Forwarded-For`, la boucle locale est un proxy de confiance). Le correctif du plugin lui-même reste au sprint 7. |
| S5-R4 boîte de démo | Corrigé | Seuls les e-mails adressés aux comptes de démo sont listés ou lisibles ; les autres répondent 404. |
| S2-R3 à R10, S4-R3 | Reportés | S2-R3 à R6 : sprint 8 ; S2-R7 à R10 et S4-R3 : sprint 9. |
| M2 clients saisis par les visiteurs (US-040) | Corrigé (interrupteur) | `DEMO_REGISTRATION_OPEN=0` : le formulaire d'inscription et `POST /api/v2/shop/customers` répondent 403 (`RegistrationGateListener`). Ouvert par défaut tant que la démo est privée. |
| M4 images orphelines (US-040) | Corrigé | Étape 4 du reset (`OrphanImagePurger`) : tout fichier de `public/media/image` sans ligne d'image est supprimé ; un échec est journalisé et fait échouer le reset (`/healthz` 503 `reset failed`). |
| M5 clients HTTP du serveur MCP (US-040) | Corrigé | Le garde sortant décore aussi `http_client.transport` ; les redirections ne sont plus suivies. |
| M6 `/healthz` (US-040) | Corrigé | Limiteur par IP visiteur et verdict base + migrations gardé 5 s (aucune requête SQL par appel dans la fenêtre). |
| M8 retour du simulateur PunchOut (US-040) | Corrigé | Le panier n'est cru que s'il est, octet pour octet, un `PunchOutOrderMessage` du journal du plugin ; usage unique, dans l'heure (`ReturnAuthenticator`). |

## API REST désactivée en production

`config/packages/sylius_api.yaml` n'active l'API Sylius (`/api/v2/*`) qu'en `dev`, comme Sylius-Standard : en prod, tout `/api/v2/*` répond `404` (`KernelRequestEventSubscriber` du cœur). Constaté sur Railway le 2026-10-17 (T-048-12). Décision de l'utilisateur, le même jour : **l'API reste désactivée**. L'activer exposerait, entre autres, les prix des variantes sur le canal B2B à prix masqués. Les contrôles cités ci-dessous pour `/api/v2/*` restent en place en défense en profondeur. Le serveur MCP n'en dépend pas : ses outils appellent les services directement. Les ressources d'API des plugins (commande rapide en lot, plan de chargement container-fill, vues entreprise) ne sont donc pas démontrées en ligne.

## Risques acceptés

- **`TRUSTED_PROXIES` contient `REMOTE_ADDR`** : tout appelant direct du conteneur serait cru sur `X-Forwarded-*`. Acceptable parce que seul le proxy Railway atteint le conteneur (aucun port TCP public).
- **CSP compatible** (`'unsafe-inline'`, `'unsafe-eval'` pour les scripts) : l'admin Sylius et les contrôleurs Stimulus en ont besoin. La CSP bloque tout de même les ressources d'une autre origine, les objets, les cadres et les cibles de formulaire externes.
- **Compteurs de débit dans le cache du conteneur** (système de fichiers) : remis à zéro à chaque déploiement et par instance. Une seule instance est déployée.
- **Fichiers statiques hors de la porte** : les images et assets du thème sont servis par Caddy sans authentification.
- **Compteur quotidien et jeton d'usage unique non atomiques** : deux envois simultanés d'une même session pourraient créer deux commandes ; le plafond quotidien reste le garde-fou.
- **`api.iconify.design` autorisé en sortie** : Symfony UX Icons y récupère les icônes absentes du cache (requête GET sans donnée utilisateur). Réchauffer le cache à la construction de l'image supprime cette exception.
- **Inscriptions ouvertes tant que la démo est privée** : leurs données sont lisibles par tout porteur du mot de passe de la porte (liste des clients de Sophie) ; l'encart « aucune donnée réelle » et la purge nocturne limitent l'exposition. `DEMO_REGISTRATION_OPEN=0` est à positionner avant l'ouverture publique.
- **Retour du simulateur rejoué après actualisation** : un retour de panier ne sert qu'une fois ; actualiser la page de retour affiche « déjà reçu », sans commande (relancer une session).
- **`/health` rouge pendant la remise à zéro** (base recréée, migrations en cours) : la sonde Railway ne s'exécute qu'au déploiement, le `HEALTHCHECK` Docker n'est qu'informatif.
