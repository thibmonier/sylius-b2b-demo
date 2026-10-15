# A — Audit des plugins, lot 1 (rapport brut, agent Explore, 2026-10-15)

Plugins audités : admin-order-creation, agentic-feeds, ai-catalog-copilot, audit-log, b2b-mcp, bulk-actions, command-palette, company-accounts, container-fill, facturx, gpsr.

Socle commun :
- `php ^8.2`, `sylius ^2.2`. Sylius 2.3 impose PHP 8.3 au minimum, donc PHP 8.4 convient.
- Installation : bundle, puis `config.yaml` et `routes.yaml` de chaque plugin. Les migrations sont prependées et portables MySQL et PostgreSQL.
- Pas de build JS. Pas de prepend `security`.

## Par plugin
- **admin-order-creation** :
  - Brouillons de commande.
  - Processeur de commande en priorité 15, décore `sylius.remover.expired_carts`, e-mail `payment_link`.
  - Cron : `admin-order:purge-drafts`.
  - Pas de fixture.
  - Hooks admin : actions de la fiche commande et de la fiche client.
- **agentic-feeds** :
  - Route publique `/feeds/{code}/{role}`.
  - Crons : `feeds:generate --stale` (toutes les 15 min) et une génération chaque nuit.
  - Stockage dans `var/agentic_feeds`, ou Flysystem.
  - Fixture `tmonier_agentic_feeds`. Il faut configurer `default_brand`.
  - JSON-LD sur la fiche produit. Listener `kernel.response` en priorité -10.
  - GTIN et MPN sur le produit et la variante.
- **ai-catalog-copilot** :
  - Requiert `symfony/ai-bundle ^0.14` et un bridge (mistral, openai, anthropic ou ollama).
  - Générateur `demo` sans clé d'API : `TMONIER_AI_GENERATOR=demo`.
  - Fixture `tmonier_ai_copilot_demo_suggestions`.
  - Mention IA dans l'accordéon de la fiche produit. Pages admin `/admin/ai-suggestions`.
- **audit-log** :
  - Listeners Doctrine.
  - Cron : `audit-log:purge`.
  - Pas de fixture livrée.
  - Hooks admin : fiche commande, fiche client, produit, promotion. Page `/admin/audit-log`.
- **b2b-mcp** :
  - Requiert `sylius/mcp-server-plugin ^0.2` (beta, apporte `symfony/mcp-bundle 0.3`) et `league/oauth2-server`.
  - Bundles `McpBundle` et `SyliusMcpServerPlugin`, à charger avant b2b-mcp.
  - Variables d'environnement : `TMONIER_B2B_MCP_ENCRYPTION_KEY`, `SYLIUS_MCP_SERVER_API_SHOP_BASE_URI`.
  - Exige HTTPS public et `trusted_proxies`.
  - Routes `/_mcp`, `/.well-known/oauth-*`, `/mcp-oauth/*` et `/account/ai-assistants`.
  - Cron : `b2b-mcp:purge`.
  - Pas de fixture.
  - Ponts vers quick-order, quote, trade-credit, company, approval et audit-log.
- **bulk-actions** :
  - Actions de masse sur les grilles admin.
  - Pas de migration, pas de fixture.
- **command-palette** :
  - Nécessite `assets:install`.
  - Bouton Ctrl+K dans la barre de navigation admin.
- **company-accounts** :
  - Fixture `tmonier_company_accounts` (code, name, vat, registration, customer_group, email_domain, orders_per_member, channel).
  - Routes `/account/company[/members|/orders]`.
  - Décore l'`account_owner` d'order-hold.
  - Hooks : tableau de bord du compte et onglets extensibles ; fiche client côté admin.
- **container-fill** :
  - Garde de checkout sur `complete` (`workflow_guard`) et `CheckoutEntrySubscriber` sur `kernel.request` en priorité 4, qui renvoie au panier.
  - Composant Live : jauge.
  - Fixtures `tmonier_container_fill_container_types` et `tmonier_container_fill_demo` (pallet@example.com).
  - Pont quick-order.
  - Hooks : `add_to_cart` sur la fiche produit, `#right` du panier, fiche commande admin, formulaire produit.
- **facturx** :
  - Requiert `sylius/invoicing-plugin ^2.2` (2.3.0 dans le lock) et `horstoeko/zugferd`.
  - Bundles KnpSnappy, KnpGaufrette, SyliusPdfGeneration et SyliusInvoicing.
  - Extensions PHP gd, zip et fileinfo. Binaire wkhtmltopdf (`WKHTMLTOPDF_PATH`). Dossier `private/invoices`.
  - Pas de fixture. Factur-X s'active par canal dans l'admin.
  - Hooks admin : canal, client, facture, factures de la fiche commande.
- **gpsr** :
  - Fixtures `tmonier_gpsr_responsible_person`, `tmonier_gpsr_manufacturer` et `tmonier_gpsr_product_safety` (données `custom`).
  - Bloc sécurité sur la fiche produit, sections du formulaire produit.

## Conflits
1. Le prepend `framework.lock` (flock) est répété par ai-copilot, b2b-mcp et command-palette (ainsi que punchout, quote et quick-order) : à vérifier avec `debug:config framework lock`.
2. `symfony/ai-bundle 0.14` et `symfony/mcp-bundle 0.3` viennent du même monorepo à des versions éloignées : **à valider par une résolution composer à blanc**.
3. container-fill bloque le checkout (guard et redirection) pour tous les paniers, y compris brouillons admin, MCP, quick-order et OrderRequest. Limiter ses règles à des produits ou un canal dédiés, ou passer `workflow_guard: false`.
4. Brouillons admin (paniers « invité ») et lecture du dernier panier par b2b-mcp : vérifier l'exclusion.
5. Listeners Doctrine onFlush multiples (audit, feeds, company, container) : surveiller le coût sur les imports.
6. Formulaire produit chargé (feeds, container-fill, gpsr), sans collision de noms.
7. Hooks partagés, priorités distinctes, sans écrasement.
8. Grilles core modifiées de façon additive.
9. Routes racine (`/feeds`, `/_mcp`, `/.well-known/oauth-*`) sans collision.
10. Menu du compte enrichi par company et mcp.
11. Prepends `api_platform.mapping.paths` cumulables.

## Pré-requis
- PHP 8.4 avec intl, gd, dom, zip, fileinfo et pdo_mysql ou pdo_pgsql. `memory_limit` de 1 à 2 Go pour compiler le conteneur.
- Bundles en plus des plugins : AiBundle, McpBundle, SyliusMcpServerPlugin, KnpSnappy, KnpGaufrette, SyliusPdfGeneration, SyliusInvoicing.
- wkhtmltopdf dans l'image. HTTPS public pour MCP.
- Variables d'environnement : `DATABASE_URL`, `MAILER_DSN`, `APP_SECRET`, `WKHTMLTOPDF_PATH`, `TMONIER_AI_GENERATOR`, `TMONIER_AI_MODEL`, clé IA facultative, `TMONIER_B2B_MCP_ENCRYPTION_KEY`, `SYLIUS_MCP_SERVER_API_SHOP_BASE_URI`.
- Crons : feeds:generate (toutes les 15 min et chaque nuit), audit-log:purge, b2b-mcp:purge, admin-order:purge-drafts, sylius:remove-expired-carts.
- Aucun worker obligatoire.
- Réglages après fixtures : `default_brand` d'agentic-feeds, activation de Factur-X par canal, shop users vérifiés.
