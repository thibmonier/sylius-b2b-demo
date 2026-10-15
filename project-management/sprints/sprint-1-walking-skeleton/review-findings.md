# Revue de code — sprint 1 (walking skeleton)

Date : 2026-10-15. Branche `feature/sprint-1-walking-skeleton`, PR #1 (brouillon).
Méthode : 3 revues indépendantes en parallèle (Symfony/code custom, sécurité OWASP Top 10:2025, DevOps Docker/CI), puis consolidation et vérification manuelle des constats majeurs.

CI au moment de la revue : **verte** (Quality & smoke tests ✅, Docker image ✅, GitGuardian ✅).

## Verdict global

Aucune vulnérabilité **critique ou haute**. Le point sensible « secrets / GitGuardian » est **réellement propre** (vérifié sur tout l'historique) : seules des valeurs publiques Sylius-Standard de dev/test et des comptes de démo assumés sont versionnés ; `APP_SECRET` et clés JWT de prod viennent des variables Railway ou sont générés au boot. Pour une démo publique non promue, l'état est bon. Le walking skeleton est **mergeable** après quelques quick wins d'hygiène (voir plus bas), les constats de conception étant reportés au sprint 2.

## Faux positif écarté (vérifié manuellement)

- **Import `order-hold` « manquant » dans `tmonier_plugins.yaml`** (Symfony #1) : **FAUX**. Le plugin est installé (21/21 dans `vendor/tmonier`), et `TmonierSyliusOrderHoldExtension::load()` charge lui-même `config/services.php` via `PhpFileLoader`. Il suit la convention Symfony idiomatique (Extension auto-chargée à l'enregistrement du bundle, `bundles.php:68`), contrairement aux 20 autres plugins qui exposent un `config.yaml` importé manuellement. Rien de cassé ; CI verte + 19 smoke tests le confirment. L'affirmation « vendor n'a que 20 paquets » était également inexacte.

## Quick wins recommandés AVANT merge (faible risque, hygiène sprint-1)

| # | Constat | Fichier | Source | Correctif |
|---|---------|---------|--------|-----------|
| Q1 | Hachage `argon2i` au lieu d'`argon2id` (règle 11 + OWASP A02) | `config/packages/security.yaml:13` | Symfony #5 + Sécurité #2 | Passer à `auto` (résout argon2id/sodium) |
| Q2 | En-tête anti-clickjacking absent (A05) | `docker/Caddyfile` | Sécurité #3 + Docker #12 | Ajouter `X-Frame-Options "DENY"` (ou CSP `frame-ancestors 'none'`) |
| Q3 | `.dockerignore` incomplet, `COPY . .` embarque `.env*`, `test.key`, `tests/`, `project-management/`, clés JWT locales | `.dockerignore`, `docker/Dockerfile:49` | Sécurité #5 + Docker #7 | Exclure `.env.*`, `tests/`, `project-management/`, `.github/`, `config/jwt/*.pem`, `config/encryption/*.key` |
| Q4 | Logs prod écrits dans un fichier éphémère, invisibles sur Railway | `config/packages/monolog.yaml` | Docker #4 | `path: php://stderr` pour `nested` et `deprecation` |
| Q5 | MySQL/Mailpit exposés sur `0.0.0.0` sans mot de passe (LAN) | `compose.yaml:32-39` | Docker #5 | Préfixer les ports par `127.0.0.1:` |
| Q6 | `.DS_Store` non suivi et non ignoré | `.gitignore` | observé | Ajouter `.DS_Store` |

## Reporté au sprint 2 (conception / backlog)

| # | Constat | Sévérité | Source | Ticket / note |
|---|---------|----------|--------|---------------|
| R1 | `DemoChannelContext` (priorité 200) gagne sur toute la chaîne, y compris API/admin stateless → toujours servi en B2B hors cookie | Majeur | Symfony #2 | Limiter au storefront (préfixe hors `/admin`, `/api`) ou documenter dans ADR-0011 |
| R2 | Fallback silencieux si le canal B2B absent de la base | Majeur | Symfony #3 | Échec bruyant (log/exception) ou check au boot |
| R3 | `entrypoint.sh` : migrations + fixtures au boot sans verrou ni atomicité, test `COUNT(*)` fragile, pas de `set -eu` | Moyen-élevé | Docker #2 | `flock`/`GET_LOCK`, marqueur `demo_seeded`, `set -eu` — à concevoir avec US-012 |
| R4 | `supercronic ... &` non supervisé (mort silencieuse, SIGTERM non relayé) | Moyen | Docker #3 | Superviseur `wait -n` ou service cron Railway dédié |
| R5 | FrankenPHP sans `num_threads`/`max_threads` → risque OOM sur Railway Hobby | Moyen | Docker #11 | Borner les threads + dimensionner le service |
| R6 | Compte admin de démo public dans les fixtures (`sophie` / `demo-sophie`) | Moyen (by-design) | Sécurité #1 | Acceptable si reset régulier (US-012) ; idéalement mot de passe via variable Railway |
| R7 | Images Docker et actions GitHub pinnées sur tag, pas sur SHA (règle 11 / SLSA) | Faible | Sécurité #6 + Docker #6, #9 | Pinner `@sha256`/SHA + Renovate/Dependabot |
| R8 | Cache Composer CI inopérant (`~/.composer/cache` ≠ `~/.cache/composer`), pas de `restore-keys` | Faible | Docker #8 | Corriger le chemin de cache |
| R9 | `composer install --no-scripts` masque la cause racine `DoctrineMetadataCacheWarmer` | Faible-moyen | Docker #10 | Ticket cause racine (déjà noté au handoff) |
| R10 | Smoke tests non hermétiques (fixtures supposées, admin `sophie` en dur, assertions de chaîne), `@dataProvider` en annotation | Mineur | Symfony #6 | `#[DataProvider]`, setUp fixtures, assertions via contexte |
| R11 | Redirection du switcher sans `_locale` (non vérifié) | Mineur | Symfony #7 | Passer `_locale` explicite ; à confirmer |
| R12 | `remember_me` d'un an (admin + shop) | Faible | Sécurité #8 | Réduire (ex. 30 j admin) |
| R13 | Caddyfile : HSTS `includeSubDomains` sur domaine partagé, `max-age` long sur media non hashé, `php_server` large | Faible | Docker #12 | À durcir au déploiement sur domaine dédié |
| R14 | Requête canal par appel (`findOneByCode` répété) | Mineur | Symfony #4 | Mémoïser par requête |

## Non-défauts (by-design, documentés)

- **Reset nocturne absent** (`docker/crontab`) : attendu — c'est **US-012**, prévu au sprint 2 (le commentaire du crontab le dit). Pas un défaut sprint-1.
- **Cookie de canal forgeable** (Symfony #8 + Sécurité #7) : by-design (ADR-0011). La whitelist `codeFrom()` retombe sur B2B pour toute valeur ≠ `B2C` ; le voter `DEMO_CONSUMER_CHANNEL` ne gouverne que l'affichage, pas l'autorisation. Cookie `HttpOnly` + `SameSite=Lax`. Bascule GET sans CSRF = impact cosmétique, non bloquant.
- **`HealthController`** : propre (réponses génériques, pas de stack trace, `Cache-Control: no-store`).
- **`.env.test` avec valeurs de hasher en clair** : valeurs publiques du squelette Sylius-Standard, test uniquement.

## Classement par confiance

- **Haute confiance** (≥2 reviewers ou vérifié + règle projet) : Q1 (argon2i), Q2/H (headers), Q3 (.dockerignore), R7 (pinning).
- **Vérifié manuellement** : faux positif order-hold (écarté), secrets propres (confirmé).
- **À confirmer** : R11 (`_locale`), R1 (comportement réel côté API/admin — non confirmé par le reviewer).
