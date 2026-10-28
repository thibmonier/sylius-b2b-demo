# Relevé de départ des dépôts de plugins (US-209, T-209-01)

Relevé du 2026-10-28 sur les branches par défaut **distantes** des 29 dépôts (27 plugins et 2 packs), par `gh api` et `bin/plugin-supply-chain report`. Réponses : oui (contrôle conforme), non, sans objet. Aucun constat détaillé : seul l’état conforme ou non est consigné.

## Constats d’ensemble

- Branche par défaut `main` partout ; **aucune branche protégée** ; CI verte sur `main` dans les 29 dépôts (un workflow planifié secondaire d’un dépôt est rouge depuis avant ce sprint, hors du workflow de build) ; aucun usage de `secrets.` ; aucune branche `dev-*` dans `require` ; aucun `pull_request_target`.
- **Aucun des 29 dépôts ne versionne `composer.lock`** (il est ignoré par Git) : l’audit Composer se fait après l’installation partout, la revue du verrou est « sans objet » partout. La fiche US-209 n’annonçait cette situation que pour trois dépôts.
- Dependabot : le fichier existe dans 24 dépôts, mais il ne couvre GitHub Actions que dans 3 d’entre eux.

## Contrôles par dépôt

| Dépôt | SHA | Perm. | Creds | PRT | Zizmor | Audit | Policy | Allow | Dep. | CI main |
|---|---|---|---|---|---|---|---|---|---|---|
| `sylius-quick-order-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-admin-order-creation-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-agentic-feeds-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-ai-catalog-copilot-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-audit-log-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-b2b-mcp-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-bulk-actions-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-channel-access-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-command-palette-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-company-accounts-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-company-insights-plugin` | oui | non | non | oui | non | non | non | oui | non | verte |
| `sylius-container-fill-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-customer-asset-management-plugin` | oui | non | non | oui | non | non | non | oui | non | verte |
| `sylius-einvoice-transmission-plugin` | oui | non | non | oui | non | non | non | oui | non | verte |
| `sylius-erp-flatfile-plugin` | oui | non | non | oui | non | non | non | oui | non | verte |
| `sylius-facturx-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-gpsr-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-order-hold-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-punchout-cxml-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-purchase-approval-plugin` | oui | non | non | oui | non | non | non | oui | non | verte |
| `sylius-quote-request-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-registration-approval-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-tier-pricing-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-timeline-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-trade-credit-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-webhook-outbox-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-withdrawal-plugin` | non | non | non | oui | non | non | non | oui | non | verte |
| `sylius-pack-ai-headless` | oui | non | non | oui | non | non | non | non | non | verte |
| `sylius-pack-compliance-tooling` | oui | non | non | oui | non | non | non | non | non | verte |

Légende : SHA = actions figées par SHA avec la version en commentaire ; Perm. = `permissions: {}` puis permissions par job ; Creds = `persist-credentials: false` ; PRT = aucun `pull_request_target` ; Zizmor = job zizmor figé et hors ligne ; Audit = `composer audit` bloquant ; Policy = `config.policy` explicite ; Allow = `config.allow-plugins` en liste explicite ; Dep. = Dependabot Composer et GitHub Actions avec délai de maturité.
