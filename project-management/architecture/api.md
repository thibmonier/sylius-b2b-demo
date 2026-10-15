# Routes et interfaces

## Routes de la démo (`src/Demo`)

| Méthode | Chemin | Accès | Rôle |
|---------|--------|-------|------|
| GET | `/healthz` | public | Healthcheck Railway : 200 `ok` ou `resetting`, 503 si la base est indisponible |
| GET | `/{_locale}/plugins` | public | Page Plugins (catalogue des plugins et scénarios) |
| GET | `/{_locale}/about-demo`, `/{_locale}/legal` | public | À propos, mentions légales |
| GET | `/{_locale}/login?demo={persona}` | public | Formulaire de connexion pré-rempli (persona dans une liste blanche) |
| GET/POST | `/{_locale}/demo/punchout` | public | Simulateur d'outil d'achat (setup cXML et OCI par sous-requête interne) |
| POST | `/demo/punchout/return` | public | Réception du panier transféré (cXML-base64 ou champs OCI), affichage |
| POST | `/{_locale}/demo/punchout/order-request` | public, CSRF | Envoi d'un OrderRequest pour le dernier panier reçu |
| POST | `/demo/webhooks/receive` | signature Standard Webhooks | Récepteur interne de l'outbox |
| GET | `/admin/demo/webhooks` | admin | Webhooks reçus |
| GET | `/{_locale}/demo/mcp` | public | Guide de connexion MCP |
| GET | `/mails/*` | public, GET seulement | Mailpit (reverse proxy Caddy) |

## Routes des nouveaux plugins (prévisionnel)

| Plugin | Routes |
|--------|--------|
| b2b-access | Admin : `/admin/b2b-access/registrations` (file, approuver ou refuser : POST + CSRF), réglage sur le formulaire canal. Shop : page « Compte en attente » |
| order-reference | Pas de route propre (champs du checkout, colonnes et fiche admin) ; API shop : `PATCH /api/v2/shop/orders/{token}/reference` |
| shopping-list | Shop : `/{_locale}/account/shopping-lists[/{id}]`, POST ajout et suppression d'article, POST « tout ajouter au panier » (CSRF) ; API shop correspondante |
| company-accounts 1.2 | Shop : `/{_locale}/account/company/addresses[...]` (administrateurs d'entreprise), fournisseur d'adresses au checkout |

## Interfaces externes servies par les plugins (rappel)
- cXML et OCI : `/punchout/*` (punchout).
- MCP et OAuth : `/_mcp`, `/.well-known/oauth-*`, `/mcp-oauth/*` (b2b-mcp et mcp-server).
- Flux : `/feeds/{code}/{role}` (agentic-feeds).
- API Sylius : `/api/v2/*`.
