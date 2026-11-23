# Backlog — Démo B2B Sylius

> Généré en phase de planification (2026-10-15). Source : `prd.md`, `personas.md`, `analysis/`.

## Epics

| Epic | Titre | Stories | Points |
|---|---|---|---|
| [EPIC-01](epics/EPIC-01.md) | Socle applicatif et conteneurisation | 6 | 24 |
| [EPIC-02](epics/EPIC-02.md) | Données de démo B2B | 6 | 34 |
| [EPIC-03](epics/EPIC-03.md) | Thème B2B et expérience de démo | 6 | 26 |
| [EPIC-04](epics/EPIC-04.md) | Nouveau plugin : accès B2B | 2 | 10 |
| [EPIC-05](epics/EPIC-05.md) | Nouveau plugin : référence de commande | 2 | 10 |
| [EPIC-06](epics/EPIC-06.md) | Listes d'achat et adresses d'entreprise | 3 | 18 |
| [EPIC-07](epics/EPIC-07.md) | Intégration des plugins et scénarios | 6 | 19 |
| [EPIC-08](epics/EPIC-08.md) | Hébergement Railway et exploitation | 5 | 12 |
| [EPIC-09](epics/EPIC-09.md) | Sécurité de la démo publique | 4 | 15 |

**Total** : 37 stories, 160 points.

## Plan de sprints

| Sprint | Objectif | Stories | Points |
|---|---|---|---|
| 1 | Walking skeleton : Sylius 2.3 + 21 plugins dans l'image FrankenPHP, local, tâches, CI, canaux | US-001, US-002, US-003, US-004, US-005, US-007, US-031 | 26 |
| 2 | Données B2B : catalogue, entreprises et comptes, données des plugins cohérentes, remise à zéro | US-008, US-009, US-010, US-012 | 26 |
| 3 | Nouveaux plugins accès B2B et référence de commande, spike wishlist, correctifs d'interaction | US-019, US-020, US-021, US-022, US-023, US-026 | 27 |
| 4 | Thème B2B, accueil et page Plugins, bandeau de démo, badges shop et admin, pages légales | US-013, US-014, US-015, US-016, US-017, US-018 | 26 |
| 5 | Listes d'achat, adresses d'entreprise, historique, simulateur PunchOut, Mailpit | US-011, US-024, US-025, US-027, US-028 | 29 |
| 6 | Railway, remise à zéro nocturne, budget, sécurité de la démo publique, spike worker, webhooks, MCP | US-006, US-029, US-030, US-032, US-033, US-034, US-035, US-036, US-037 | 26 |
| 10 | Vitrine et reports : images des produits, encart Sylius Plus, récepteur de webhooks, reports techniques du sprint 9 | US-029, US-046, US-047, US-048 | 13 |
| 11 | Fiabilité et ouverture : remise à zéro fiable (erreur fatale vue par `/healthz`), préparation de l'ouverture publique | US-049, US-050 | 8 |

## MoSCoW

- **Must** : 29 stories, 127 points
- **Should** : 6 stories, 28 points
- **Could** : 2 stories, 5 points

## Feuille de route innovation (2026-10-28)

Voir [roadmap-innovation](roadmap-innovation.md) : 28 stories (US-210 à US-237), 117 points, sprints 34 à 41 proposés.

| Epic | Titre | Stories | Points |
|---|---|---|---|
| [EPIC-16](epics/EPIC-16.md) | Copilote d'achat B2B (plugin sales-copilot) | 7 | 34 |
| [EPIC-17](epics/EPIC-17.md) | Commerce prédictif (plugin predictive-analytics) | 4 | 18 |
| [EPIC-18](epics/EPIC-18.md) | Thème « Industrie & Négoce » | 8 | 34 |
| [EPIC-19](epics/EPIC-19.md) | Passeport numérique produit (DPP) | 7 | 23 |
| Transverse | Posts et GIF par plugin (EPIC-13), parcours guidé copilote (EPIC-12) | 2 | 8 |

## Nouveau plugin : santé de la plateforme (2026-11-22)

Demande de Thibaut du 2026-11-22. Paquet `tmonier/sylius-platform-health-plugin` : un écran d'administration qui
répond « est-ce que ma plateforme va bien ? », chaque alerte portant le lien qui la corrige. 27 stories (US-286 à
US-312), 113 points, sprints 56 à 61 proposés. Fiches écrites : US-286 à US-288 (sprint 56, livrées en v0.1.0) et US-289 à US-293 (sprint 57, 17 points sur 15 de capacité, `ready-for-dev`) ; les autres au cadrage de leur sprint.

| Epic | Titre | Stories | Points |
|---|---|---|---|
| [EPIC-20](epics/EPIC-20.md) | Socle, contrat de sonde et tableau de bord admin | 7 | 25 |
| [EPIC-21](epics/EPIC-21.md) | Sondes d'environnement, de données et de tâches planifiées | 7 | 31 |
| [EPIC-22](epics/EPIC-22.md) | Sondes métier et sondes des plugins de l'écosystème | 7 | 33 |
| [EPIC-23](epics/EPIC-23.md) | Exposition, historique et alertes | 6 | 24 |

**MoSCoW** : Must 19 stories (83 points), Should 6 stories (24 points), Could 2 stories (6 points).

**Décisions de cadrage du 2026-11-22** : périmètre complet (technique, métier et sondes des plugins) ; sondes de plugins par `suggest` et vérification de classe, sans dépendance obligatoire ; historique en base dès la v1 ; alertes par courriel et par `webhook-outbox`, à la transition d'état seulement.

## Socle de la démo : montée PHP 8.6 (2026-11-22)

[US-313](user-stories/US-313.md) — Montée de la démo vers PHP 8.6 sur FrankenPHP 1.14 · [EPIC-01](epics/EPIC-01.md) · Should · 5 points · à planifier (sprint 57+). Issue de la question 6 du spike US-286 ; réouverture de l'[ADR-0021](../../docs/adr/0021-montee-php-8-6-reportee.md) décidée par Thibaut le 2026-11-22.

[US-314](user-stories/US-314.md) — CI des plugins : la suite non unitaire s'exécute même si l'unitaire échoue · [EPIC-01](epics/EPIC-01.md) · Should · 2 points · à planifier. Suite d'A-56-1 : appliquée au plugin de santé par T-TECH-57-03, les 30 autres plugins restent à faire.

[US-315](user-stories/US-315.md) — Plugins : traiter les PR Dependabot ouvertes (phpunit, GitHub Actions) · [EPIC-01](epics/EPIC-01.md) · Should · 3 points · à planifier. 8 PR dans 7 plugins au 2026-11-23 ; la démo passe par T-TECH-57-06.
