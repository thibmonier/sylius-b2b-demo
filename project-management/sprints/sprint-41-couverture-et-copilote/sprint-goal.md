# Sprint 41 : Couverture et copilote — couverture de code tous plugins et copilote de réassort

> **Planifié le 2026-10-31**, après la clôture du sprint 40 (PR à venir). Périmètre décidé par Thibaut le 2026-10-31 : **13 points** ([registre des décisions](./registre-decisions.md)). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-41`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 41 |
| Début | 2026-10-31 (planning) |
| Fin | **2026-11-14** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; tâches décomposées ci-dessous |
| Vélocité de référence | S35 8, S36 11, S37 13, S38 10, S39 8, S40 10 |
| Branche | `feature/sprint-41-couverture-et-copilote` (arbre principal) |

Le périmètre retient 13 points : couverture de code pour tous les plugins (US-253, 5 pts), copilote de réassort "comme le mois dernier" (US-212, 8 pts). Les actions techniques T-TECH-41 sont intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, disque ~17 Go libres le 2026-10-31 ; suite complète par la CI.

## Sprint Goal

> « Mesurer et publier la couverture de code de tous les plugins sur la page Preuve technique, et implémenter le scénario de réassort automatique "comme le mois dernier" dans le copilote d'achat. »

## Périmètre engagé

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-253](../../backlog/user-stories/US-253.md) | Preuve technique : couverture de code tous plugins | 5 | Should | ready-for-dev | Dépend d'US-252 (pilote livré S40) |
| [US-212](../../backlog/user-stories/US-212.md) | Copilote : réassort "comme le mois dernier" | 8 | Should | ready-for-dev | ADR 0018 décision S39 |

Total engagé : **13 points**.

- **US-253** ajoute un job PHPUnit + PCOV + check run « coverage » dans la CI de tous les 27 plugins. Pattern : le step « Publish coverage check run » du ci.yaml de la démo. Puis `bin/proof-refresh` dans la démo pour que les taux apparaissent sur /preuve. Taux réel affiché même bas, aucun seuil bloquant.
- **US-212** implémente dans le plugin `sylius-b2b-sales-copilot-plugin` un scénario de réassort : l'utilisateur dit « recommande comme le mois dernier » et le copilote reproduit la dernière commande (lookup dernière commande, items + quantités, ajout au panier, substitution si référence inactive). Tests unitaires et fonctionnels. Intégration dans la démo avec cassette enregistrée, fixtures, test. ADR 0018 décision : replay with scenario buttons, per-action confirmation.

**Hors sprint** : US-154 (conversion devis, 3 pts), US-246+ (features futures).

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote, prédictif et parc avec Sylius Plus. En cas de recoupement avéré, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité dans les dépôts publics. Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-31)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 40 à fusionner
- [x] Actions de la rétrospective du sprint 40 reprises : T-TECH-41-01 à 03
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-31
- [x] Stories estimées et « Ready »
- [ ] Capacité calculée : à calculer après décomposition
- [x] Branche `feature/sprint-41-couverture-et-copilote` créée

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : US-253 dépend d'US-252 (S40) ; US-212 indépendante
- [ ] Tags plugin demandés sur GO de Thibaut

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Commit de tests rouges cité dans la PR, **avec l'échec observé** (règle 27)
- [ ] US-253 : check run coverage dans tous les plugins, proof-refresh exécuté, page Preuve affiche les taux, CI verte
- [ ] US-212 : scénario réassort implémenté dans le plugin copilote, cassette enregistrée démo, tests unitaires + fonctionnels, CI verte

## Cérémonies

| Cérémonie | Date | Heure | Durée | Participants |
|-----------|------|-------|-------|--------------|
| Sprint Planning Part 1 (QUOI) | 2026-10-31 | 09:00 | 2h | PO, Scrum Master, équipe |
| Sprint Planning Part 2 (COMMENT) | 2026-10-31 | 14:00 | 2h | Équipe |
| Daily Scrum | Chaque jour | 09:30 | 15min | Équipe |
| Sprint Review | 2026-11-14 | 14:00 | 2h | PO, Scrum Master, équipe, parties prenantes |
| Rétrospective | 2026-11-14 | 16:30 | 1h30 | Scrum Master, équipe |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth
