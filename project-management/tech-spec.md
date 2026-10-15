# Spécification technique — Démo B2B Sylius

> Phase 3 (Conception), workflow claude-craft Enterprise, 2026-10-15. Statut : **brouillon à valider**.
> Entrées : `prd.md`, `personas.md`, `backlog/`, `analysis/*`. Décisions : `docs/adr/0001` à `0011`.
> Écarts constatés au sprint 1 : canal choisi par cookie (ADR-0011) ; Caddy en HTTP local (TLS seulement chez Railway) ; wkhtmltox obligatoire (invoicing-plugin 2.3 appelle KnpSnappy directement, l'adaptateur dompdf de Sylius ne s'applique pas aux factures). Architecture : `architecture/`.

## 1. Vue d'ensemble

Une application **Sylius-Standard 2.3** (Symfony 7.4 LTS, PHP 8.4) installe les 21 plugins tmonier depuis Packagist, ainsi que les nouveaux plugins créés pour la démo. Une couche applicative `src/Demo/` porte tout ce qui est propre à la démo : badges, bandeau, pages, remise à zéro, restrictions, récepteur de webhooks, simulateur. Le thème B2B **ajoute** des hookables (Twig Hooks) au thème officiel et apporte sa propre feuille Encore.

L'application tourne dans **une image FrankenPHP** (Caddy), qui sert le web et exécute les tâches planifiées (supercronic). Elle est déployée sur **Railway** avec MySQL 8.4 et Mailpit. Les données sont remises à zéro chaque nuit **dans le conteneur web** (ADR-0003).

## 2. Socle applicatif (US-001, US-031)

- `composer create-project sylius/sylius-standard:~2.3` épinglé, puis :
  - **retrait** des plugins de paiement en ligne (Stripe, Adyen, Mollie, PayPal), hors périmètre (PRD §3) ;
  - **conservation** de `sylius/refund-plugin` (remboursements et avoirs, utiles en back-office B2B), sous réserve de compatibilité au sprint 1 ;
  - **ajout** des 21 plugins tmonier (versions taguées), `sylius/invoicing-plugin` ^2.3, `sylius/mcp-server-plugin` ^0.2, `symfony/ai-bundle` ^0.14 avec un bridge (générateur `demo` par défaut), `knplabs/knp-snappy-bundle`.
- **Ordre des bundles** (`config/bundles.php`) :
  1. bundles Sylius ;
  2. SyliusInvoicing, KnpSnappy, KnpGaufrette, SyliusPdfGeneration ;
  3. McpBundle, SyliusMcpServerPlugin, AiBundle ;
  4. order-hold, company-accounts, puis les autres plugins tmonier ;
  5. nouveaux plugins.
- **Configuration** :
  - un fichier par plugin dans `config/packages/tmonier/*.yaml` (import de `@Plugin/config/config.yaml` et options de démo) ;
  - routes dans `config/routes/tmonier.yaml` ;
  - Symfony Workflow pour les graphes de commande (exigé par order-hold) ;
  - `framework.router.default_uri` = `%env(DEFAULT_URI)%` ;
  - `trusted_proxies` = `REMOTE_ADDR`, en-têtes `x-forwarded-*` (Railway).
- **Correctifs des plugins publiés (US-031)** : soumettre agentic-feeds sur Packagist (action de l'utilisateur) ; README de punchout (variables `PUNCHOUT_SUPPLIER_DOMAIN` et `PUNCHOUT_SUPPLIER_IDENTITY`), en patch release.

## 3. Arborescence du dépôt

```
sylius-b2b-demo/
├── config/
│   ├── packages/tmonier/*.yaml      # un fichier par plugin
│   ├── packages/demo.yaml           # options de la démo (accounts, badges, reset)
│   ├── demo/badges.yaml             # registre hook → plugin (ADR-0005)
│   ├── demo/plugins.yaml            # catalogue des plugins (page Plugins)
│   └── routes/{tmonier,demo}.yaml
├── src/Demo/
│   ├── Badge/                       # registre, extension DI (hooks générés), composant Twig
│   ├── Banner/                      # bandeau, comptes de démo
│   ├── Controller/                  # healthz, plugins, about, legal, simulateur, webhooks, mcp
│   ├── Reset/                       # app:demo:reset, mode maintenance
│   ├── Security/                    # restrictions de l'admin de démo, garde des sorties
│   ├── Webhook/                     # récepteur et entité WebhookReceipt
│   └── Fixture/                     # fixtures de la démo (CSV → Sylius + plugins)
├── fixtures/data/*.csv              # catalogue, entreprises, paliers… (versionnés)
├── fixtures/images/                 # illustrations générées (SVG → PNG au build)
├── assets/shop/demo/                # SCSS B2B, contrôleurs Stimulus (bandeau, badges)
├── templates/demo/ et templates/bundles/   # surcharges ciblées
├── docker/{Dockerfile,Caddyfile,crontab,entrypoint.sh,php.ini}
├── compose.yaml, compose.override.dist.yaml
├── railway.json
└── tests/{Smoke,Scenario,Unit}/
```

## 4. Conteneur et exécution (US-002 à US-004, ADR-0002)

- **Dockerfile multi-stage** :
  - `composer` : dépendances sans dev, autoloader optimisé ;
  - `assets` : Node LTS, `yarn install` et `yarn build` (Encore shop et admin des plugins) ;
  - `final` : `dunglas/frankenphp:1-php8.4-bookworm`, avec les extensions intl, gd, zip, pdo_mysql, opcache et apcu, wkhtmltox 0.12.6.1 (patched qt), supercronic, `cache:warmup`, `assets:install`, utilisateur non root.
- **Caddyfile** :
  - `php_server`, compression zstd et gzip, en-têtes de sécurité (§9) ;
  - port `{$PORT:8080}` pour Railway ;
  - TLS terminé par Railway en production, Caddy en HTTPS local.
- **Entrypoint** :
  1. attente de la base ;
  2. si la base est vide, `app:demo:reset` (premier démarrage) ;
  3. lancement de supercronic en arrière-plan, puis de FrankenPHP au premier plan.
- **Crontab** (`TZ=Europe/Paris`) :
  - chaque minute : `tmonier:webhook:dispatch` (borné dans le temps) ;
  - toutes les 15 minutes : `tmonier:feeds:generate --stale` ;
  - chaque nuit : expirations (approbations, devis), relances et recalcul trade-credit, purges (punchout, webhook, audit-log, b2b-mcp, brouillons admin), `sylius:remove-expired-carts` ;
  - **03:00** : `app:demo:reset`.
- **Mode worker** de FrankenPHP : spike US-006 (sprint 6). Mode classique par défaut.
- **Mémoire** : `memory_limit` à 512M pour le web, 1G pour la CLI (reset, fixtures). Service Railway calibré à 1 Go de RAM.

## 5. Données de démo (US-007 à US-012, ADR-0007)

- **Suite de fixtures `demo`** (remplace `default`), priorités explicites :
  1. géographie (pays, zones, devises), canaux B2B et B2C, locales, taxes, expédition, paiement ;
  2. taxons, attributs et options ;
  3. catalogue (CSV) et illustrations ;
  4. clients et utilisateurs ;
  5. entreprises et membres (company-accounts) ;
  6. limites (purchase-approval) ;
  7. crédit (trade-credit), au niveau de l'entreprise ;
  8. paliers (tier-pricing) ;
  9. types de palettes et produits palettisables (container-fill) ;
  10. GPSR ;
  11. acheteurs PunchOut et secrets connus de la démo (affichés dans le simulateur) ;
  12. suggestions IA ;
  13. devis ;
  14. commandes historiques (via les transitions) et factures ;
  15. listes d'achat ; adresses d'entreprise ; références PO.
- **CSV versionnés** dans `fixtures/data/` : `taxons.csv`, `products.csv` (SKU, nom fr/en, taxon, MPN, EAN, conditionnement, poids, dimensions, prix, normes), `tiers.csv`, `companies.csv`, `members.csv`, `pallet_products.csv`. Un lecteur CSV commun et des fixtures Sylius (`AbstractFixture`) par domaine.
- **Illustrations** : SVG générés par un script (pictogramme par taxon, avec le libellé et la couleur de la gamme), convertis en PNG au build. Pas de photo sous licence.
- **Mots de passe des comptes de démo** : valeur publique documentée (« demo » + rôle), hachée par Sylius. Ce ne sont pas des secrets (comptes publics, remis à zéro chaque nuit).
- **Remise à zéro `app:demo:reset`** :
  1. mode maintenance (fichier drapeau, réponse 503 « Remise à zéro en cours », sauf `/healthz` qui renvoie 200 avec `resetting`) ;
  2. `doctrine:database:drop`, `create`, `migrations:migrate` ;
  3. `sylius:fixtures:load demo` ;
  4. purge de `public/media`, du cache liip et de `private/invoices`, puis régénération ;
  5. purge de Mailpit (API) ;
  6. sortie du mode maintenance ;
  7. journal de la durée.
  Commande idempotente, code non nul en cas d'échec (US-012).

## 6. Thème B2B et expérience de démo (US-013 à US-018, ADR-0004, ADR-0005, ADR-0010)

- **Thème** :
  - feuille `assets/shop/demo/b2b.scss` importée dans l'entrée shop : palette industrielle, densité, tableaux ;
  - **hookables ajoutés** sur les listes (SKU, conditionnement, stock, « dès X € HT » à partir du premier palier) et sur la fiche produit (références, documents, caractéristiques normées) ;
  - surcharges limitées aux templates du thème officiel non utilisés par les plugins (en-tête et pied de page) ;
  - test de non-régression : la liste des hookables des plugins (`debug:twig-hooks`) est comparée à une référence en CI.
- **Accueil** : héros B2B, accès rapides (quick-order, devis, listes, PunchOut), gammes, encart « Démo des plugins ».
- **Page Plugins** (`/{_locale}/plugins`) : cartes générées depuis `config/demo/plugins.yaml` (nom, paquet, description fr/en, scénario, compte conseillé, liens GitHub et Packagist, icône).
- **Bandeau de démo** (hook `sylius_shop.base.header` ajouté) :
  - comptes avec lien « Se connecter en tant que… » vers `/login?demo=<persona>`, où un contrôleur Stimulus pré-remplit le formulaire (ADR-0010) ;
  - lien vers Mailpit ;
  - heure de la prochaine remise à zéro ;
  - interrupteur des badges (cookie `demo_badges`).
- **Badges** (ADR-0005) :
  - registre `config/demo/badges.yaml` (`hook`, `plugin`, `priority`, `area: shop|admin`) ;
  - une extension DI le lit et **prepend** la config `sylius_twig_hooks` (un hookable `demo_badge_<plugin>` par entrée) ;
  - le composant Twig `DemoPluginBadge` rend une icône et un lien accessible (`aria-label`, infobulle, focus visible) vers la carte de la page Plugins et vers GitHub.
- **Pages légales** : `/{_locale}/legal` et `/{_locale}/about-demo`.

## 7. Nouveaux plugins (EPIC-04 à 06, ADR-0006)

Chaque plugin est un dépôt `thibmonier/sylius-<nom>-plugin` créé avec le squelette habituel des plugins tmonier (test application, CI 8 jobs, ECS, PHPStan max, Rector, 7 langues). Il est publié en MIT sur Packagist et installé dans la démo par version taguée. Revue de sécurité indépendante avant sa première release.

| Plugin | Modèle (tables du plugin) | Points d'intégration |
|--------|---------------------------|----------------------|
| **b2b-access** | `tmonier_b2b_access_channel` (channel, mode `open\|prices_hidden\|closed`, approbation à l'inscription) ; `tmonier_b2b_access_customer` (customer, statut `pending\|approved\|rejected`, dates) | Listener `kernel.request` (après le firewall) avec routes exemptées configurables (préfixes : login, register, reset, `/punchout`, `/_mcp`, `/.well-known`, `/feeds`, `/healthz`). Hooks de masquage prix et achat ; Twig `b2b_can_see_prices()`. Admin : réglage par canal, file des inscriptions. E-mails d'approbation et de refus |
| **order-reference** | `tmonier_order_reference` (order unique, `buyer_reference` 35, `cost_center` 35) ; `tmonier_order_reference_channel` (channel, exigences de référence et de centre de coût, libellés) | Étape checkout (hook formulaire `complete`, validation), grille et fiche admin, e-mails, API shop. Ponts : facturx (BT-10 BuyerReference), punchout (`orderID` → référence), company-accounts (exigence par entreprise) |
| **shopping-list** (si l'ADR de US-023 l'exige) | `tmonier_shopping_list` (owner customer, company nullable, channel, nom) ; `tmonier_shopping_list_item` (list, variant, quantité) | Pages du compte, bouton « Ajouter à une liste » (fiche produit, panier), ajout au panier en un clic (pont quick-order), voters par entreprise |
| **company-accounts 1.2** | `tmonier_company_address` (company, address Sylius, libellé, défaut) | Onglet Adresses (administrateurs d'entreprise), sélection des adresses de l'entreprise au checkout (fournisseur d'adresses), API. Sans BC break |

## 8. Intégration et scénarios (EPIC-07)

- **Correctifs d'interaction (US-026)** :
  - container-fill : règles limitées aux produits palettisables, la garde ne bloque pas un panier sans tels produits (vérifié, sinon correctif) ;
  - punchout : bloquer `/quotes/{token}/checkout` et les commandes passées par quick-order pendant une session (correctif dans punchout v1.1.x, liste de routes configurable) ;
  - OrderRequest : test de scénario (réservation trade-credit, approbation, garde quote).
- **Simulateur PunchOut (US-027)** : contrôleur `src/Demo/Controller/PunchoutSimulator` adapté de la TestApplication de punchout. Formulaires cXML et OCI, page de réception (base64 décodé, affichage du panier), bouton « Envoyer l'OrderRequest ». Il appelle l'endpoint par sous-requête interne : aucun appel sortant.
- **Mailpit (US-028)** : service séparé. L'interface est exposée en lecture seule par Caddy (reverse proxy `/mails/*`, méthodes GET seulement, API de suppression bloquée). L'application envoie par SMTP interne (réseau privé Railway).
- **Récepteur de webhooks (US-029)** : `POST /demo/webhooks/receive` (signature Standard Webhooks vérifiée), entité `app_demo_webhook_receipt` et page de consultation. Seul l'endpoint interne est autorisé (§9).
- **MCP (US-030)** : page de guide. Le serveur MCP est servi par les plugins (HTTPS du domaine Railway).

## 9. Sécurité de la démo publique (EPIC-09, ADR-0008, `architecture/security.md`)

- **Admin restreint** :
  - Sophie a un compte admin normal (Sylius n'a pas de RBAC), et un listener `DemoAdminGuard` refuse les écritures sur une liste de routes sensibles : administrateurs, canaux, moyens de paiement et passerelles, endpoints webhook, secrets des acheteurs PunchOut, paramètres IA et Factur-X, suppression en masse des clients et des commandes ;
  - le refus se fait par message flash et redirection ;
  - liste dans `config/packages/demo.yaml`.
- **Aucune sortie réelle** :
  - mailer uniquement vers Mailpit ;
  - webhook-outbox : validateur qui n'accepte que l'URL du récepteur interne ;
  - IA : générateur `demo` ;
  - `HttpClient` limité aux hôtes internes (scoped client et `NoPrivateNetworkHttpClient` inversé) ;
  - PunchOut : simulateur interne seulement.
- **Rate limiting** : connexion, inscription, devis, formulaires publics (limiteurs des plugins et de la démo).
- **En-têtes** : CSP (nonces pour le script du bandeau), HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `frame-ancestors 'none'` (PunchOut en fenêtre).
- **Secrets** :
  - `APP_SECRET`, `TMONIER_B2B_MCP_ENCRYPTION_KEY`, `DATABASE_URL` dans les variables Railway ;
  - aucun secret dans le dépôt ;
  - les secrets PunchOut de démo sont publics par nature (simulateur) et régénérés à chaque remise à zéro.

## 10. Hébergement Railway (EPIC-08, ADR-0003)

- Projet Railway « sylius-b2b-demo » avec trois services :
  - **web** : Dockerfile du dépôt, déploiement depuis main après CI verte, `preDeployCommand` = migrations, healthcheck `/healthz`, 1 Go de RAM ;
  - **MySQL** : 8.4 managé ;
  - **Mailpit** : image officielle, réseau privé.
- `railway.json` versionné (builder, healthcheck, restart policy). Variables : `APP_ENV=prod`, `APP_SECRET`, `DATABASE_URL=${{MySQL.MYSQL_URL}}?serverVersion=8.4`, `MAILER_DSN=smtp://mailpit.railway.internal:1025`, `DEFAULT_URI=https://${{RAILWAY_PUBLIC_DOMAIN}}`, `PUNCHOUT_SUPPLIER_*`, `TMONIER_AI_GENERATOR=demo`, `TMONIER_B2B_MCP_ENCRYPTION_KEY`, `SYLIUS_MCP_SERVER_API_SHOP_BASE_URI`.
- **Pas de mise en veille** (la remise à zéro au réveil effacerait une session en cours). Limite d'usage Railway à 25 $.
- **Supervision** : la remise à zéro écrit un journal structuré. En cas d'échec, une notification par webhook sortant **autorisé** vers une URL de notification de l'utilisateur (ntfy ou similaire, variable `DEMO_ALERT_URL`), seule exception à la règle « pas de sortie ».

## 11. Stratégie de test

| Niveau | Contenu | Outil |
|--------|---------|-------|
| Unitaires | Registre de badges, lecteur CSV, garde admin, guard des sorties | PHPUnit |
| Fumée | Pages clés shop et admin en 200, pour chaque persona ; aucun hookable de plugin manquant | PHPUnit WebTestCase (BrowserKit) |
| Scénarios | Les 9 parcours du PRD §5 (approbation, crédit, devis, palettes, PunchOut aller-retour et OrderRequest, B2C rétractation…) | PHPUnit WebTestCase, base de test et fixtures demo |
| Plugins nouveaux | TDD complet dans chaque dépôt (functional et unit, 8 jobs CI) | PHPUnit |
| Image | Build, démarrage, `/healthz`, remise à zéro en moins de 10 minutes | GitHub Actions (service MySQL) |
| Accessibilité | Axe-core sur l'accueil, le catalogue, la fiche produit et le bandeau | Playwright (CI, non bloquant au début) |

## 12. Couverture des exigences

| Exigence | Section | Stories |
|----------|---------|---------|
| FR-1 | §2 | US-001 |
| FR-2, FR-3 | §4 | US-002 à US-004 |
| FR-4 à FR-8 | §5 | US-007 à US-012 |
| FR-9 à FR-13 | §6 | US-013 à US-018 |
| FR-14 à FR-17 | §7 | US-019 à US-025 |
| FR-18 à FR-23 | §2, §8 | US-026 à US-031 |
| FR-24, FR-25 | §10 | US-032 à US-034 |
| FR-26, FR-27 | §9 | US-035 à US-037 |
| NFR-1 | §4 (cache, opcache, worker en spike) | US-006 |
| NFR-2 | §5 | US-012, US-033 |
| NFR-3 | §10 | US-034 |
| NFR-4 | §9 | US-035 à US-037 |
| NFR-5 | §11 | US-005 |
| NFR-6 | §6, §11 | US-015, US-016 |
| NFR-7 | §5, §6 | US-007, US-008 |
| NFR-8 | §2, §6 | US-001, US-013 |
