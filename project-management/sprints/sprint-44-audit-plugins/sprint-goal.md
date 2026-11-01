# Sprint 44 — Audit des plugins

## Sprint Goal

Auditer l'architecture, la qualité et la sécurité de l'ensemble des 33 dépôts de plugins, thème et packs, pour garantir un niveau homogène avant toute nouvelle fonctionnalité.

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 44 |
| Slug | audit-plugins |
| Début | 2026-11-01 |
| Fin | 2026-11-15 |
| Capacité | 13 points |
| Stories | 2 (US-254, US-255) |

## Périmètre

### US-254 — Lot 1 : 15 plugins critiques (8 pts)

Audit `/team:audit` (architecture, qualité, sécurité) sur :
1. sylius-punchout-cxml-plugin
2. sylius-company-accounts-plugin
3. sylius-b2b-sales-copilot-plugin
4. sylius-facturx-plugin
5. sylius-quick-order-plugin
6. sylius-quote-request-plugin
7. sylius-purchase-approval-plugin
8. sylius-company-insights-plugin
9. sylius-customer-asset-management-plugin
10. sylius-digital-product-passport-plugin
11. sylius-einvoice-transmission-plugin
12. sylius-erp-flatfile-plugin
13. sylius-b2b-predictive-analytics-plugin
14. sylius-container-fill-plugin
15. sylius-channel-access-plugin

### US-255 — Lot 2 : 16 dépôts restants (5 pts)

Audit `/team:audit` sur :
1. sylius-admin-order-creation-plugin
2. sylius-agentic-feeds-plugin
3. sylius-ai-catalog-copilot-plugin
4. sylius-audit-log-plugin
5. sylius-b2b-mcp-plugin
6. sylius-bulk-actions-plugin
7. sylius-command-palette-plugin
8. sylius-gpsr-plugin
9. sylius-order-hold-plugin
10. sylius-registration-approval-plugin
11. sylius-tier-pricing-plugin
12. sylius-timeline-plugin
13. sylius-trade-credit-plugin
14. sylius-webhook-outbox-plugin
15. sylius-withdrawal-plugin
16. sylius-industry-trade-theme
17. sylius-pack-ai-headless (pack)
18. sylius-pack-compliance-tooling (pack)

### Tâches techniques

- T-TECH-44-01 : Marquer sprint 43 comme done (done)
- T-TECH-44-02 : Report T-TECH-43-04 (montant maquette Copilote)

## Livrables

- Rapport d'audit par plugin dans `project-management/audits/2026-11-01-audit-plugins/`
- Rapport consolidé avec les axes d'amélioration et les stories backlog proposées
- Aucun correctif dans ce sprint : le sprint identifie les problèmes, un sprint ultérieur les corrige

## Critères de succès

- [ ] Chaque dépôt a été audité (architecture, qualité, sécurité)
- [ ] Les constats sont classés par sévérité (critique, majeur, mineur, info)
- [ ] Les stories de correction sont proposées au backlog
- [ ] Aucun constat de sécurité détaillé dans le dépôt public de la démo
