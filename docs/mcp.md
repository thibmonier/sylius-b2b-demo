# Connecter un assistant IA à la démo (MCP)

Application d'US-030. Les étapes pour l'utilisateur sont sur la page **`/{locale}/mcp`** de la démo (FR/EN, mêmes étapes, même bloc de configuration, mêmes liens), liée depuis la carte « B2B MCP » de la page des plugins. Cette page est la source : ce document ne la répète pas, il donne le contexte d'exploitation.

- Code : `src/Demo/Controller/McpGuideController.php`, `templates/demo/mcp.html.twig`, `translations/mcp_guide.{fr,en}.yaml`.
- Tests : `tests/Functional/Demo/McpGuideTest.php` (page, parité FR/EN, métadonnées OAuth, commandes de Claire seules, refus sans jeton ou avec jeton expiré, compte non approuvé), `McpOutboundGuardTest.php` (hôte non autorisé).

## Ce que le serveur fait

- Un seul point d'entrée : `https://<hôte>/_mcp`, où `<hôte>` est le domaine Railway ou le sous-domaine de la démo ([sous-domaine-demo.md](sous-domaine-demo.md)) ; la page du guide affiche l'hôte de la requête (serveur MCP officiel de Sylius + outils du plugin `tmonier/sylius-b2b-mcp-plugin`).
- Connexion OAuth 2.1 avec PKCE (S256) ; l'assistant s'enregistre lui-même (enregistrement dynamique, pas d'identifiant client) ; métadonnées : `/.well-known/oauth-authorization-server` et `/.well-known/oauth-protected-resource/_mcp`.
- Portées : `b2b:read` (commandes, devis, crédit), `b2b:cart` (réassort, commande rapide, demande de devis). `b2b:approve` n'existe pas dans la démo (`approvals.allow_decisions` est à faux).
- Aucune commande ni paiement dans l'assistant.

## Comportements décrits par le guide

| Cas | Réponse |
|-----|---------|
| Pas de jeton, jeton expiré, révoqué ou inconnu | `401` + `WWW-Authenticate: Bearer resource_metadata=…`, aucune commande dans le corps |
| Compte en attente ou rejeté (inscription B2B, US-020) | `403 access_denied` à la connexion (page de consentement) et à chaque appel `/_mcp` |
| Requête du serveur MCP vers un hôte hors liste | refusée avant toute connexion (garde sortant sur `http_client.transport`, US-040) |
| Reset nocturne (03:00 Paris) | connexions d'assistants effacées : se reconnecter |

Le refus des comptes non approuvés passe par le point d'extension du plugin (`AccountUsabilityCheckerInterface`, b2b-mcp ≥ 1.2.1) : `src/Demo/Mcp/ApprovedRegistrationChecker.php` lit le statut d'approbation du client (registration-approval) quand un canal valide les inscriptions. Le plugin l'interroge à la page de consentement et à chaque appel `/_mcp`. Le vérificateur ne reçoit que le client : la règle s'applique dès qu'un canal de la politique d'approbation existe (B2B), pas au canal propre de la connexion.

## Démo privée : limite importante

Tant que la porte HTTP (`DEMO_BASIC_AUTH=1`) est active, un assistant hébergé (Claude, ChatGPT) ne peut pas atteindre `/_mcp` ni le flux OAuth : seuls `/health` et `/healthz` sont exemptés. Le guide l'affiche (bandeau d'avertissement) tant que la porte est active. Le guide se suit donc de bout en bout à partir de l'ouverture publique (GO utilisateur distinct, [ouverture-publique.md](ouverture-publique.md) ; l'avertissement disparaît dès `DEMO_BASIC_AUTH=0`, vérifié par `PublicOpeningTest`) ; d'ici là, le comportement est couvert par `McpGuideTest`.

Le jeton `Authorization: Bearer` du MCP et l'authentification HTTP basique partagent le même en-tête : on ne peut pas franchir la porte et s'authentifier en même temps avec un client hébergé.

L'API REST Sylius (`/api/v2/*`) est désactivée en prod et répond `404`. Le MCP n'en dépend pas (voir [durcissement-demo.md](durcissement-demo.md#api-rest-désactivée-en-production)).

## Vérification manuelle avec un vrai assistant (T-030-03, à faire par l'utilisateur)

À faire après l'ouverture publique (aucune commande externe depuis ce sprint) :

1. Suivre la page `/fr_FR/mcp` à froid, de l'ajout du connecteur à « Liste mes dernières commandes ».
2. Vérifier que seules les commandes de `claire@acme.example` sont listées.
3. Se déconnecter (Mon compte > Assistants IA) et vérifier que l'assistant n'obtient plus rien.
