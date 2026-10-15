# Sécurité de la démo publique (OWASP Top 10:2025)

## Modèle de menace

| Actif | Menace | Mesure |
|-------|--------|--------|
| Infrastructure et budget Railway | Abus de ressources (crawl massif, scripts sur les formulaires et VALIDATE) | Rate limiting applicatif (limiteurs des plugins et de la démo), plafond d'usage Railway, ressources fixes (pas d'autoscaling) |
| Réputation (domaine, IP) | Spam par e-mail via les formulaires (inscription, devis, mot de passe oublié) | Mailer **uniquement** vers Mailpit (SMTP interne), aucun relais |
| Réseau | SSRF via des URLs saisies (endpoints webhook, logos, imports) | Garde des sorties : HttpClient scopé, validateur d'URL webhook limité au récepteur interne, aucune fonctionnalité d'import par URL exposée |
| Intégrité de la démo | Admin public qui casse la configuration (supprime les canaux, change les admins, les passerelles) | `DemoAdminGuard` : écritures refusées sur les routes sensibles ; remise à zéro nocturne |
| Contenus | Textes injurieux ou malveillants publiés (avis, noms de listes, devis) | Échappement Twig, purge nocturne ; avis produits désactivés sur le canal B2B |
| Comptes de démo | Prise de contrôle, changement du mot de passe | Changement de mot de passe et d'e-mail refusé pour les comptes de démo (garde shop), remise à zéro nocturne |
| Secrets | Fuite d'`APP_SECRET`, de la clé MCP ou de `DATABASE_URL` | Variables Railway, rien dans le dépôt (GitGuardian), `.env` sans valeur de production |
| Assistants IA (MCP) | Usage de l'API pour des actions non souhaitées | Scopes OAuth des plugins, comptes de démo seulement, rate limiting b2b-mcp |

## Correspondance OWASP 2025

| # | Catégorie | Disposition |
|---|-----------|-------------|
| A01 | Broken Access Control | Voters et pare-feu Sylius ; garde admin de démo ; vérifications de propriété des listes d'achat et des adresses (par entreprise) dans les nouveaux plugins ; aucune redirection ouverte (connexion pré-remplie par persona en liste blanche) |
| A02 | Cryptographic Failures | TLS Railway et HSTS ; secrets PunchOut en Argon2id (plugin) ; mots de passe Sylius |
| A03 | Injection | Doctrine paramétré ; Twig auto-échappé ; CSV de fixtures lus par l'équipe (non saisis par les visiteurs) |
| A04 | Insecure Design | Rate limiting ; aucune sortie réelle ; plafonds des plugins |
| A05 | Security Misconfiguration | `APP_ENV=prod`, `APP_DEBUG=0`, profiler absent de l'image, en-têtes de sécurité dans Caddy, `trusted_proxies` restreints |
| A06 | Supply Chain | Versions épinglées (composer.lock et yarn.lock), Renovate, `composer audit` en CI, image de base épinglée par digest |
| A07 | Exceptional Conditions | Pages d'erreur génériques ; le mode maintenance renvoie 503 propre ; échec de la remise à zéro alerté |
| A08 | Authentication Failures | Limiteur de connexion ; comptes de démo non modifiables ; OAuth MCP (PKCE S256) |
| A09 | Logging & Monitoring | Journaux Railway, journal de la remise à zéro, audit-log et journaux PunchOut des plugins |
| A10 | Data Integrity | Image construite en CI, déploiement depuis main seulement |

## En-têtes (Caddy)

```
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'none'; form-action 'self'
```

- `style-src 'unsafe-inline'` reste nécessaire pour Bootstrap et Live Components, à durcir si possible.
- Les pages de retour PunchOut gardent leur CSP propre (nonce), posée par le plugin.

## Routes sensibles refusées à l'admin de démo (écriture)

Administrateurs, canaux (édition et suppression), moyens de paiement et passerelles, zones et pays (suppression), endpoints webhook (création et édition hors récepteur interne), secrets des acheteurs PunchOut (génération affichée seulement dans le simulateur), paramètres Factur-X et IA, suppression en masse des clients et des commandes, export de données personnelles.

## Revue

Revue de sécurité indépendante avant ouverture publique (US-037), et pour chaque nouveau plugin avant sa première release.
