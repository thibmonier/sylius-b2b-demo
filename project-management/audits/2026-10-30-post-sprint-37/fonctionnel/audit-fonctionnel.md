# Audit fonctionnel : vitrine B2B après le sprint 37

- Date : 2026-10-30 · Base : `main` @ `b1ad1c6` · Production : https://sylius-b2b.thibaut-monier.fr
- Méthode : chaque `demo_route` du catalogue (`config/demo/plugins.yaml`, 29 entrées) est résolue en chemin réel par `bin/console debug:router`, puis demandée en production par un GET anonyme (`curl`, un appel par seconde, sans connexion ni formulaire). Les comptes conseillés sont vérifiés dans `config/demo/accounts.yaml`.
- Note de méthode : une première passe confiée à un sous-agent avait deviné les URL (par exemple `/fr_FR/products/epi-cask-001` ou `/fr_FR/demo/mcp-connections`) et conclu à sept routes en 404. Ces constats étaient faux : ils ont été écartés et la vérification refaite par l'orchestrateur à partir des routes réelles.

## Verdict

La vitrine tient ses promesses pour les parcours vérifiables sans connexion. Les routes de démo du catalogue existent toutes ; les pages de compte redirigent vers la connexion, comme attendu, et les cinq comptes conseillés existent dans le catalogue des comptes. Deux écarts réels sont relevés, plus un point à revérifier après la remise à zéro nocturne.

| Gravité | Nombre | Écarts |
|---|---|---|
| Moyenne | 1 | Scénario « Registration Approval » : sa route de démo répond 403 en production publique |
| Basse | 1 | Passeport produit : aucun passeport en production le 2026-10-30 (données chargées par la remise à zéro nocturne) |
| Information | 1 | Parcours guidés des comptes : `/demo/accounts` sans préfixe de langue, contrairement aux autres pages de la vitrine |

## Routes de démo du catalogue

| Plugin | Route | Chemin réel | Production (GET anonyme) | Lecture |
|---|---|---|---|---|
| B2B MCP | `tmonier_b2b_mcp_shop_connections` | `/{_locale}/account/ai-assistants` | 302 vers `/fr_FR/login` | attendu (compte requis) |
| Predictive Analytics | `sylius_shop_account_dashboard` | `/{_locale}/account/dashboard` | 302 vers `/fr_FR/login` | attendu |
| Company Accounts | `tmonier_company_accounts_shop_company_show` | `/{_locale}/account/company` | 302 vers `/fr_FR/login` | attendu |
| Company Insights | `tmonier_company_insights_shop_insights` | `/{_locale}/account/company/insights` | 302 vers `/fr_FR/login` | attendu |
| Customer Asset Management | `tmonier_equipment_shop_fleet_index` | `/{_locale}/account/equipment` | 302 vers `/fr_FR/login` | attendu |
| E-invoice Transmission | `app_demo_fictitious_platform` | `/{_locale}/pa-fictive` | 200 | conforme |
| ERP Flat-file (non listé) | `app_demo_erp_flatfile` | `/{_locale}/echange-fichier` | 200 | conforme |
| PunchOut cXML | `tmonier_punchout_inspect` | `/{_locale}/punchout/inspect` | 302 vers `/fr_FR/` | attendu hors session PunchOut |
| Purchase Approval | `tmonier_purchase_approval_shop_approvals` | `/{_locale}/account/company/approvals` | 302 vers `/fr_FR/login` | attendu |
| Quick Order | `tmonier_quick_order_shop_index` | `/{_locale}/quick-order` | 200 | conforme |
| Quote Request | `tmonier_quote_request_shop_new` | `/{_locale}/quote-request` | 302 vers `/fr_FR/login` | attendu (devis invité fermé en mode public) |
| Registration Approval | `sylius_shop_register` | `/{_locale}/register` | **403** | **écart** (voir ci-dessous) |
| Withdrawal | `tmonier_withdrawal_shop_form` | `/{_locale}/withdrawal` | 200 | conforme |

Les 16 autres plugins n'ont pas de route de démo (`demo_route: null`) : leur scénario passe par l'administration ou par une fiche produit.

Pages de la vitrine : `/fr_FR/plugins` 200, `/en_US/plugins` 200, `/demo/accounts` 200 (`/fr_FR/demo/accounts` 404, la route `app_demo_accounts` n'a pas de préfixe de langue).

## Écarts

### Registration Approval : route de démo en 403 (moyenne)

En production publique, `DEMO_REGISTRATION_OPEN=0` ferme l'inscription : `/fr_FR/register` et `/en_US/register` répondent 403 (`RegistrationGateListener`, décrit dans `docs/durcissement-demo.md`). Le catalogue pointe pourtant le bouton « Voir la démo » de ce plugin vers `sylius_shop_register`. Le visiteur tombe sur une page refusée.

Piste : en mode public, faire pointer la démo vers une page qui montre la validation côté administration (liste des inscriptions en attente, avec le compte de Sophie), ou afficher un scénario « inscriptions fermées sur la démo publique » sans bouton.

### Passeport produit : pas encore de données en production (basse, à revérifier)

Les fiches `/fr_FR/products/epi-cask-001-fr`, `-002-fr` et `-003-fr` répondent 200, mais aucun lien « Passeport produit » n'y apparaît et `/fr_FR/product-passport/{1..5, 30..33}` répond 404. La fixture `demo_digital_passports` est bien inscrite dans la suite `demo` (`config/packages/demo_fixtures.yaml:225`) : les passeports seront créés par la remise à zéro nocturne (`app:demo:reset`). À revérifier après la prochaine remise à zéro ; si le lien manque encore, c'est un écart d'installation d'US-241 en production.

## Nouveautés récentes

- Passeport produit : présent dans la vitrine (`/fr_FR/a-propos` cite le dépôt `sylius-digital-product-passport-plugin`) ; données attendues après la remise à zéro (voir plus haut).
- Copilote d'achat B2B (`sylius-b2b-sales-copilot-plugin`, v0.1.0) : non installé dans la démo et absent du catalogue, ce qui est cohérent. À ne pas confondre avec `sylius-ai-catalog-copilot-plugin`, présent au catalogue.

## Axes d'amélioration

| Axe | Priorité | Estimation | Cible |
|---|---|---|---|
| Démo de Registration Approval utilisable en mode public (page côté administration ou scénario sans bouton) | Moyenne | 2 pts | story de la démo |
| Contrôle automatique des routes de démo du catalogue en mode public (aucun 403/404 sur un bouton « Voir la démo ») | Moyenne | 2 pts | story de la démo |
| Revérifier le passeport en production après la remise à zéro ; test de fumée « lien passeport sur EPI-CASK-001 » | Basse | 1 pt | story de la démo (suite d'US-241) |
| Installer le copilote d'achat dans la démo, avec captures du widget | Moyenne | 3 pts | story de la démo (suite d'US-211) |
