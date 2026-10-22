# EPIC-14 — Feuille de route plugins B2B (preuve d'expertise)

> Exigences : Audit fonctionnel §4.2 et §5 · 14 stories · 55 points (dont 5 reprises de stories existantes)

## Description

**Recadrage du 2026-10-21 :** ces plugins ne nourrissent plus des offres de conseil ; ils démontrent l'expertise Sylius et e-commerce B2B de Thibaut. Cadrer puis réaliser, par ordre d'utilité de la preuve : parc d'équipements (P1, en cours), connecteur ERP (P5), assortiments par société (P3), facture électronique France (P7), contrats-cadres (P2), stock et délais (P4), budgets (P8). Les gros chantiers commencent par un spike.

## MMF (Minimum Marketable Feature)

**Plus petite version livrable** : Chaque plugin prioritaire a un ADR et un découpage en stories de 8 points au plus avant tout développement.
**US incluses** : US-056, US-057, US-058, US-059, US-060, US-130 (Should, aucun Must : chantier de cadrage) ; le reste en Should et Could.

## Stories

| Story | Titre | MoSCoW | Points | Sprint cible |
|---|---|---|---|---|
| [US-056](../user-stories/US-056.md) | Plugin équipements : modèles d'équipement liés aux variantes compatibles (admin) | Should | 5 | 21 |
| [US-057](../user-stories/US-057.md) | Plugin équipements : parc du client dans l'espace client (CRUD) | Should | 5 | 21 |
| [US-058](../user-stories/US-058.md) | Plugin équipements : filtre « pièces compatibles » au catalogue | Should | 5 | 21 |
| US-059 (fiche à créer au planning) | Plugin équipements : publication 0.1.0 (tag, Packagist, README) | Should | 2 | 22 |
| US-060 (fiche à créer au planning) | Plugin équipements : intégration à la démo | Should | 3 | 22 |
| [US-130](../user-stories/US-130.md) | Spike P5 : connecteur ERP de référence | Should | 3 | 22+ |
| [US-131](../user-stories/US-131.md) | Spike P3 : assortiments et prix par société | Should | 3 | 22 |
| [US-132](../user-stories/US-132.md) | Spike P7 : facture électronique France (PA/PDP) et e-reporting | Should | 3 | 22 |
| [US-133](../user-stories/US-133.md) | Spike P2 : contrats-cadres | Could | 3 | 22 |
| [US-134](../user-stories/US-134.md) | Spike P4 : stock multi-entrepôts et délais | Could | 3 | 23 |
| [US-135](../user-stories/US-135.md) | Spike P8 : budgets et centres de coût | Could | 2 | 23 |
| [US-136](../user-stories/US-136.md) | Connecteur ERP, tranche 1 : export des commandes et contrat d'événements | Should | 5 | non planifié (arbitrage de Thibaut) |
| [US-137](../user-stories/US-137.md) | Connecteur ERP, tranche 2 : import des prix et du stock | Could | 5 | non planifié (arbitrage de Thibaut) |
| [US-138](../user-stories/US-138.md) | Assortiments par société, tranche 1 | Could | 8 | non planifié (arbitrage de Thibaut) |

**Notes** : P1 reprend US-056 à US-060 (sans duplication). Les plugins P6, P9, P10, P11, P12, P13 sont consignés dans la feuille de route, non planifiés.
