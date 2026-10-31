# Sprint 42 : Copilote et parcours — ajout par référence, mode rejoué et parcours guidé

> **Planifié le 2026-10-31**, après la clôture du sprint 41 (PR #148 mergée e1ff59f). Périmètre décidé par Thibaut le 2026-10-31 : **11 points**. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-42`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 42 |
| Début | 2026-10-31 (planning) |
| Fin | **2026-11-14** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **11 points** ; tâches décomposées ci-dessous |
| Vélocité de référence | S36 11, S37 13, S38 10, S39 8, S40 10, S41 13 |
| Branche | `feature/sprint-42-copilote-et-parcours` (arbre principal) |

Le périmètre retient 11 points : copilote ajout par référence et état du panier (US-213, 5 pts), mode rejoué et scénarios enregistrés (US-215, 3 pts), parcours guidé copilote (US-237, 3 pts). Les actions techniques T-TECH-42 sont intégrées comme tâches transverses (0 points). Contraintes machine : 8 Go RAM ; suite complète par la CI.

## Sprint Goal

> « Permettre au copilote d'ajouter un produit au panier par sa référence et d'afficher l'état du panier, enrichir les scénarios enregistrés pour la démo publique, et ajouter un parcours guidé "copilote d'achat" dans les parcours de la démo. »

## Périmètre engagé

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-213](../../backlog/user-stories/US-213.md) | Copilote : ajout par référence et état du panier | 5 | Must | ready-for-dev | EPIC-16 |
| [US-215](../../backlog/user-stories/US-215.md) | Copilote : mode rejoué de la démo publique et scénarios enregistrés | 3 | Must | ready-for-dev | EPIC-16 |
| [US-237](../../backlog/user-stories/US-237.md) | Parcours guidé « copilote d'achat » dans les parcours de la démo | 3 | Should | ready-for-dev | EPIC-12, dépend d'US-212 (done S41) |

Total engagé : **11 points**.

- **US-213** ajoute dans le plugin `sylius-b2b-sales-copilot-plugin` les scénarios « ajoute [référence] au panier » et « quel est l'état de mon panier » : lookup du produit par code, ajout au panier via l'API Sylius, réponse structurée avec le contenu du panier. Tests unitaires et fonctionnels. Cassettes démo (FR/EN).
- **US-215** enrichit le mode rejoué (replay adapter) pour la démo publique : scénarios enregistrés supplémentaires, gestion des variantes de formulation, page d'aide décrivant les scénarios disponibles. Tests fonctionnels.
- **US-237** ajoute un parcours guidé « copilote d'achat » dans le système de parcours guidés de la démo (`config/demo/tours.yaml`), avec étapes pointant vers le widget copilote et les scénarios disponibles.

**Tâches transverses (0 pts) :**
- **T-TECH-42-01** : Garde-fou sélecteurs CSS plugin ↔ démo (action rétro S41)
- **T-TECH-42-02** : Script `bin/plugin-push-all` pour les 28 plugins (action rétro S41)
- **T-TECH-42-03** : Corriger le montant 1 017,35 → 963,50 € HT dans la maquette Copilote du canevas Claude Design (action rétro S40/S41)
- **T-TECH-42-04** : Bump copilote ^1.1 dans la démo et retirer le template override BEM

**Hors sprint** : US-214 (garde-fous et journal d'audit, 5 pts), US-216 (adaptateur LLM réel, 5 pts).

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote, prédictif et parc avec Sylius Plus. En cas de recoupement avéré, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité dans les dépôts publics. Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-31)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 41 mergé (PR #148, e1ff59f)
- [x] Actions de la rétrospective du sprint 41 reprises : T-TECH-42-01 à 04
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-31
- [x] Stories estimées et « Ready »
- [x] Capacité calculée : 16,25 h sur 24,85 h (35 % de marge)
- [x] Branche `feature/sprint-42-copilote-et-parcours` créée

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : US-237 dépend d'US-212 (done S41) ; US-213 et US-215 indépendantes
- [ ] Tags plugin demandés sur GO de Thibaut

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Commit de tests rouges cité dans la PR, **avec l'échec observé** (règle 27)
- [ ] PHPStan max, ECS, Rector, lint:container, lint:twig, lint:yaml passent
- [ ] CI verte (tests + qualité) avant la review
- [ ] Pas de secret, pas de détail de sécurité dans le code public
- [ ] Descriptions factuelles et neutres
