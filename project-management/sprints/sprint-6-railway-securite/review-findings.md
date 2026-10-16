# Revue de sécurité — sprint 6 (US-037)

Date : 2026-10-16. Revue indépendante du code (agent au contexte frais, lecture seule, OWASP Top 10:2025) sur `git diff main...HEAD`, puis vérification dynamique sur la démo privée déployée (`web-production-3c17c.up.railway.app`).

## Recommandation

**GO pour l'ouverture publique, sous réserve du GO explicite de l'utilisateur** : aucun constat critique ou majeur ouvert. Le seul défaut majeur (V1) a été trouvé par la vérification dynamique, corrigé et revérifié en réel. Les mineurs bloquants (M1, M3, M7) sont corrigés ; les autres sont reportés au sprint 7 et ne bloquent pas l'ouverture. La démo reste privée (`DEMO_BASIC_AUTH=1`) tant que l'utilisateur n'a pas donné son GO.

## Matrice constats reportés → preuves

| Constat | Statut | Preuve (code / test) |
|---------|--------|----------------------|
| S4-R1 admin de Sophie | Traité | `DemoAdminWriteGuard`, `DemoAdminWritePolicy` / `DemoAdminBypassTest`, `DemoAdminRestrictionTest` |
| S4-R2 données réelles | Partiel | encart « aucune donnée réelle » / `HardeningTest` ; l'inscription reste ouverte (voir M2) |
| S4-R4 session du bandeau | Traité | jeton CSRF sans état / `HardeningTest` (aucun cookie de session pour un anonyme) |
| S4-R5 locales des pages légales | Traité | `LegalPagesLocaleListener` / `HardeningTest` |
| S4-R6 cookie Secure | Traité | `BadgeToggleController` / `TrustedProxiesTest` |
| S5-R1 simulateur public | Traité | `SimulatorOrderGate`, `PunchOutEndpointGuard` (usage unique, plafond quotidien) / `HardeningTest` |
| S5-R2 hôtes et proxys | Traité | `UntrustedHostListener`, `RailwayClientIpListener` / `TrustedProxiesTest`, `RailwayClientIpListenerTest` + vérification réelle (V1) |
| S5-R3 limiteur du plugin PunchOut | Traité | `EndpointClient` transmet l'IP visiteur / `HardeningTest` |
| S5-R4 boîte de démo | Traité | `DemoMailbox` (liste blanche des comptes de démo) / `DemoMailsControllerTest` |
| S2-R1 débit et mailer | Traité | `RateLimitSubscriber` (dont M3), `DemoConfigurationGuard` / `HardeningTest`, `DemoGuardsTest` |
| S2-R2 reset | Traité | `DemoResetCommand` (transaction unique, `ResetStatus`) / `DemoResetCommandTest` + reset et échec simulé sur Railway |

S2-R3 à S2-R10 et S4-R3 : reportés au sprint 7 (décision de capacité du 2026-10-16).

## Trouvé par la vérification dynamique

| # | Gravité | Constat | Statut |
|---|---------|---------|--------|
| V1 | **Majeur** | Derrière l'edge Railway, un `X-Forwarded-For` envoyé par le visiteur restait à droite de la liste, là où Symfony cherche le client : avec une valeur différente à chaque requête, aucune limite par IP (porte, logins, formulaires, simulateur) ne se déclenchait. Mesuré : 25 essais sans 429 avec l'en-tête forgé, 429 après ~14 essais sans. | **Corrigé** (`b14201b`, `RailwayClientIpListener`) : `X-Real-IP`, écrasé par l'edge, devient la seule adresse transmise. Revérifié en réel : `X-Forwarded-For` et `X-Real-IP` forgés et variables → 429 après 20 essais. |
| V2 | — | Proxy TCP public sur MySQL (Railway l'active sur certains modèles). | **Levé** : aucun proxy TCP ni domaine sur `mysql` et `mailpit`. |

Autres vérifications réelles (voir `docs/deploiement-railway.md`) : 401 sans identifiants, en-têtes de sécurité, `X-Forwarded-Host` forgé ignoré, Mailpit non exposé, reset en 25 s, échec simulé → `/healthz` 503 `reset failed` puis 200 après un reset réussi.

## Constats de la revue de code

| # | Gravité | Constat | Statut |
|---|---------|---------|--------|
| M1 | Mineur | La restriction de Sophie ne s'appliquait qu'avec `DEMO_MODE=1` (mot de passe public). | **Corrigé** (`fdade76`) : appliquée dès `ROLE_DEMO_ADMIN`. |
| M3 | Mineur | Tokens API (admin, shop, OAuth MCP), inscription et commandes par l'API, enregistrement OAuth et `/_mcp` sans limite de débit. | **Corrigé** (`3aa66c8`) : `DEMO_RATE_LIMIT_API_AUTH`, `_API_WRITE`, `_MCP`. |
| M7 | Mineur | Le preflight ne vérifiait ni la longueur d'`APP_SECRET` ni un `TRUSTED_HOSTS` ouvert. | **Corrigé** (`3aa66c8`) : échec fermé (preflight, pages et `/healthz` en 503). |
| M2 | Mineur | Les clients saisis par des visiteurs sont lisibles par tous via la liste des clients de Sophie (seul un avertissement existe). | Sprint 7 (désactiver l'inscription ou acter le risque par écrit). |
| M4 | Mineur | Les images téléversées par Sophie (`public/media/image`) ne sont pas purgées par le reset ; Caddy les sert hors porte. | Sprint 7 (purge des fichiers orphelins au reset). |
| M5 | Mineur | Les `scoped_clients` du serveur MCP passent par `http_client.transport` et échappent au garde sortant (hôte fixé par l'environnement, non contrôlé par le visiteur). | Sprint 7 (décorer `http_client.transport`). |
| M6 | Mineur | `/healthz` hors porte et sans limite, avec une requête SQL et un calcul de migrations à chaque appel. | Sprint 7 (limiteur dédié ou cache court). |
| M8 | Mineur | `/demo/punchout-simulator/return` signe le panier envoyé par le visiteur sans en authentifier l'origine ; le plugin revalide prix et stock, plafond de 50 commandes par jour. | Sprint 7. |

## Informations

- `remember_me` d'un an, sans `secure` / `samesite` explicites (`security.yaml`).
- CSP avec `unsafe-inline` et `unsafe-eval` (risque accepté) ; en-têtes émis à la fois par Caddy et PHP (HSTS en double, sans effet).
- Images Mailpit et actions CI non épinglées par digest / SHA.
- Les dépréciations PHP sont journalisées en production (bruit dans les logs Railway).
- `.railway/railway.ts` suit la branche du sprint pendant la vérification : à repointer sur `main` au merge.
- Effet de bord de M1 : la réinitialisation du mot de passe admin est fermée même hors mode démo (application de démo uniquement).
- Aucun secret dans le dépôt, la CI ou `railway.ts` (`preserve()`, mot de passe CI jetable).

## Actions avant l'ouverture publique (GO utilisateur)

1. Moniteur externe sur `/healthz` (alerte du reset nocturne) et notifications e-mail Railway vérifiées par l'utilisateur.
2. Merge de la PR, `railway.ts` repointé sur `main`, `railway config apply`.
3. `DEMO_BASIC_AUTH: "0"`, commit, `railway config apply`, puis revérifier : 200 sans identifiants, plafonds actifs, `/demo/mails` limité aux comptes de démo.
