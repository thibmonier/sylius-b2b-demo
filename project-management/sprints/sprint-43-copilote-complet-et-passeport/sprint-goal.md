# Sprint 43 : Copilote complet et passeport — garde-fous, LLM réel et lien facture

> **Planifié le 2026-11-01**, après la clôture du sprint 42 (PR #149 mergée 25c670a). Périmètre décidé par Thibaut le 2026-11-01 : **13 points**. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-43`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 43 |
| Début | 2026-11-01 (planning) |
| Fin | **2026-11-15** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 43 tâches, 21 h estimées (23 % de marge) |
| Vélocité de référence | S37 13, S38 10, S39 8, S40 10, S41 13, S42 11 |
| Branche | `feature/sprint-43-copilote-complet-et-passeport` (arbre principal) |

Le périmètre retient 13 points : garde-fous et journal d'audit du copilote (US-214, 5 pts), adaptateur LLM réel via Symfony AI (US-216, 5 pts), lien du passeport numérique dans la facture Factur-X (US-234, 3 pts). Contraintes machine : 8 Go RAM ; suite complète par la CI.

## Sprint Goal

> « Sécuriser le copilote d'achat avec des garde-fous fonctionnels et un journal d'audit, offrir un adaptateur LLM réel pour les intégrateurs via Symfony AI, et relier le passeport numérique produit à la facture Factur-X. »

## Périmètre engagé

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-214](../../backlog/user-stories/US-214.md) | Copilote : garde-fous et journal d'audit | 5 | Must | ready-for-dev | EPIC-16 |
| [US-216](../../backlog/user-stories/US-216.md) | Copilote : adaptateur LLM réel pour les intégrateurs | 5 | Could | ready-for-dev | EPIC-16, dépend d'US-214 |
| [US-234](../../backlog/user-stories/US-234.md) | DPP : lien optionnel du passeport dans la facture Factur-X | 3 | Could | ready-for-dev | EPIC-19, indépendante |

Total engagé : **13 points**.

- **US-214** ajoute dans le plugin `sylius-b2b-sales-copilot-plugin` les garde-fous fonctionnels : vérification des droits du compte connecté, liste blanche d'outils, traçage de chaque action dans le journal d'audit, limite de débit par compte. Tests unitaires et fonctionnels.
- **US-216** implémente l'adaptateur LLM réel derrière le port `CopilotModel` via Symfony AI : configuration par variables d'environnement, délai d'attente, bascule vers le rejoué en cas d'erreur, plafond de coût journalier. Tests unitaires avec mocks.
- **US-234** ajoute au plugin `sylius-facturx-plugin` un champ de référence documentaire contenant le lien du passeport numérique pour chaque ligne de facture concernée, désactivé par défaut. Validation du PDF/A-3. Tests.

**Hors sprint** : US-217 (tableau de bord copilote, 3 pts), US-218 (évaluation hors connexion, 5 pts).

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote et passeport avec Sylius Plus. En cas de recoupement avéré, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité dans les dépôts publics. Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-11-01)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 42 mergé (PR #149, 25c670a)
- [x] Actions de la rétrospective du sprint 42 reprises (à vérifier en rétro S42)
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-11-01
- [x] Stories estimées et « Ready »
- [x] Branche `feature/sprint-43-copilote-complet-et-passeport` créée

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : US-216 dépend d'US-214 (garde-fous avant LLM réel) ; US-234 indépendante
- [ ] Tags plugin demandés sur GO de Thibaut

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Commit de tests rouges cité dans la PR, **avec l'échec observé** (règle 27)
- [ ] PHPStan max, ECS, Rector, lint:container, lint:twig, lint:yaml passent
- [ ] CI verte (tests + qualité) avant la review
- [ ] Pas de secret, pas de détail de sécurité dans le code public
- [ ] Descriptions factuelles et neutres
