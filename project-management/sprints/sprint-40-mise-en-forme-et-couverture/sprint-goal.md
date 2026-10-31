# Sprint 40 : Mise en forme et couverture — widget copilote, bloc réassort et couverture de code pilote

> **Planifié le 2026-10-31**, après la clôture du sprint 39 (PR #145). Périmètre décidé par Thibaut le 2026-10-31 : **10 points** ([registre des décisions](./registre-decisions.md)). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-40`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 40 |
| Début | 2026-10-31 (planning) |
| Fin | **2026-11-14** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **10 points** ; tâches décomposées ci-dessous |
| Vélocité de référence | S34 9, S35 8, S36 11, S37 13, S38 10, S39 8 |
| Branche | `feature/sprint-40-mise-en-forme-et-couverture` (arbre principal) |

Le périmètre retient 10 points : mise en forme du widget copilote d'après la maquette (US-246, 2 pts), mise en forme du bloc de réassort d'après la maquette (US-251, 3 pts), couverture de code pilote sur 3 plugins et la démo (US-252, 5 pts). Les actions de rétro du sprint 39 (T-TECH-40-01 à 04) sont intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, disque ~17 Go libres le 2026-10-31 ; suite complète par la CI.

## Sprint Goal

> « Appliquer les maquettes validées au sprint 39 (copilote et réassort), mesurer la couverture de code sur 3 plugins pilotes et la démo, et l'afficher sur la page Preuve technique. »

## Périmètre engagé

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-246](../../backlog/user-stories/US-246.md) | Copilote d'achat : mise en forme du widget | 2 | Should | ready-for-dev | Dépend d'US-248 (maquette validée S39) |
| [US-251](../../backlog/user-stories/US-251.md) | Commerce prédictif : mise en forme du bloc de réassort | 3 | Should | ready-for-dev | Dépend d'US-250 (maquette validée S39) |
| [US-252](../../backlog/user-stories/US-252.md) | Couverture de code pilote (3 plugins + démo) | 5 | Should | ready-for-dev | Aucune dépendance |

Total engagé : **10 points**.

- **US-246** applique la maquette Copilote/CopiloteMobile du canevas Claude Design au widget du plugin copilote. Styles neutres dans le plugin (release sur GO), habillage thème dans la démo. Question alignée à droite, réponse à gauche, indicateur aria-live, pa11y AAA.
- **US-251** applique la maquette Reassort/ReassortMobile au bloc du plugin prédictif. Styles neutres dans le plugin (release sur GO), habillage thème dans la démo. Alerte en retard signalée par texte (pas seulement couleur), état vide, pa11y AAA.
- **US-252** mesure la couverture PHPUnit (lignes) sur la démo et 3 plugins pilotes (copilote, passeport, parc). La CI publie un check run « coverage » lisible par l'API REST publique. `ProofCollector` lit ce check run, `bin/proof-refresh` écrit la valeur dans `proof.json`, la page Preuve technique l'affiche. Taux réel affiché même bas, aucun seuil bloquant.

**Hors sprint** : US-253 (couverture tous les plugins, 5 pts — après US-252), US-212 (copilote réassort automatique, 8 pts). **Réserve** : US-154 (conversion devis, 3 pts).

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote, prédictif et parc avec Sylius Plus. En cas de recoupement avéré, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité dans les dépôts publics. Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-31)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 39 fusionné (PR #145)
- [x] Actions de la rétrospective du sprint 39 reprises : T-TECH-40-01 à 04
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-31
- [x] Stories estimées et « Ready »
- [ ] Capacité calculée : à calculer après décomposition
- [x] Branche `feature/sprint-40-mise-en-forme-et-couverture` créée

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : US-246 et US-251 indépendantes entre elles ; US-252 indépendante
- [ ] Tags plugin demandés sur GO de Thibaut

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Commit de tests rouges cité dans la PR, **avec l'échec observé** (règle 27)
- [ ] US-246 : styles dans le plugin copilote, release sur GO, démo mise à jour, captures README, pa11y AAA, CI verte
- [ ] US-251 : styles dans le plugin prédictif, release sur GO, démo mise à jour, captures README, pa11y AAA, CI verte
- [ ] US-252 : check run coverage publié par la CI, ProofCollector le lit, page Preuve l'affiche, tests unitaires rouge prouvé, pa11y AAA, CI verte

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
