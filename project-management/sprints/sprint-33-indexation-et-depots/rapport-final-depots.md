# Rapport final : chaîne d’approvisionnement des dépôts de plugins (US-209, T-209-12)

État au 2026-10-28, mesuré par `bin/plugin-supply-chain report` sur les branches par défaut **distantes** des 29 dépôts (27 plugins, 2 packs), après fusion. Relevé de départ : [releve-depart-depots.md](./releve-depart-depots.md). Oui = contrôle conforme ; la revue du verrou est « sans objet » partout (aucun dépôt ne versionne `composer.lock`).

## Résultat

- **29 dépôts sur 29 conformes aux 9 contrôles**, une PR par dépôt, CI verte avant la fusion (squash), **aucun tag, aucune release, aucun dépôt créé, aucune protection contournée** (aucune branche n’est protégée).
- Seuls `.github/` et la section `config` de `composer.json` ont changé.

## Par dépôt

| Dépôt | PR | SHA | Perm. | Creds | PRT | Zizmor | Audit | Policy | Allow | Dep. | CI de `main` |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `sylius-quick-order-plugin` | #7 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-admin-order-creation-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-agentic-feeds-plugin` | #8 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-ai-catalog-copilot-plugin` | #5 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-audit-log-plugin` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-b2b-mcp-plugin` | #12 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-bulk-actions-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-channel-access-plugin` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-command-palette-plugin` | #7 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-company-accounts-plugin` | #7 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-company-insights-plugin` | #8 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-container-fill-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-customer-asset-management-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-einvoice-transmission-plugin` | #2 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-erp-flatfile-plugin` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-facturx-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-gpsr-plugin` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-order-hold-plugin` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-punchout-cxml-plugin` | #7 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-purchase-approval-plugin` | #11 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-quote-request-plugin` | #7 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-registration-approval-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-tier-pricing-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-timeline-plugin` | #8 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-trade-credit-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-webhook-outbox-plugin` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-withdrawal-plugin` | #6 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-pack-ai-headless` | #4 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |
| `sylius-pack-compliance-tooling` | #3 | oui | oui | oui | oui | oui | oui | oui | oui | oui | verte |

Conformes aux 9 contrôles : 29 sur 29.

## Écarts et incidents (sans détail sensible)

- **Aucun dépôt à CI rouge préexistante, sans CI ou à branche protégée** : rien à remonter à Thibaut (T-209-11), aucune exception au GO du registre.
- **Écart à la fiche** : aucun dépôt ne versionne `composer.lock` (la fiche en annonçait trois sans verrou) ; l’audit Composer se fait donc après l’installation partout et la revue du verrou est sans objet partout.
- **Incident corrigé** : dans les deux packs, l’étape d’audit insérée par le script avait une indentation invalide, le workflow ne s’exécutait plus et le contrôle de la PR n’avait donc rien testé. Détecté par la CI de Dependabot, corrigé par une seconde PR par pack (#5 de `sylius-pack-ai-headless`, #4 de `sylius-pack-compliance-tooling`), CI complète verte avant la fusion. Les 29 workflows ont ensuite été relus par un analyseur YAML. Le script et son test ont été corrigés (voir le fichier de tâches).
- **Alea de CI sans lien avec le changement** : trois exécutions de `main` après fusion ont échoué une fois (délai d’attente de l’API de Packagist pendant l’audit ; deux étapes de tests ou de construction), puis réussi au second passage. Le blocage de l’audit reste fermé en cas d’indisponibilité du service : à relancer, pas à contourner.
- Dependabot ouvre ses premières PR (mises à jour d’actions) dans plusieurs dépôts depuis l’ajout de l’écosystème GitHub Actions : à traiter au fil de l’eau.
- Le fork de la wishlist n’est pas touché.
