# Sprint 40 : Mise en forme et couverture — widgets copilote et réassort stylés, couverture de code pilote

> **Planifié le 2026-10-31**, après la clôture du sprint 39 (PR #145). Périmètre décidé par Thibaut le 2026-10-31 : **10 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches sera faite au planning. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-40`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 40 |
| Début | 2026-10-31 (planning) |
| Fin | **2026-11-14** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **10 points** ; tâches à décomposer |
| Vélocité de référence | S34 9, S35 8, S36 11, S37 13, S38 10, S39 8 |
| Branche | `feature/sprint-40-mise-en-forme-et-couverture` |

Le périmètre retient 10 points : mise en forme du widget copilote (US-246, 2 pts), mise en forme du bloc de réassort (US-251, 3 pts), couverture de code pilote sur 3 plugins et la démo (US-252, 5 pts). Les actions de rétro du sprint 39 (T-TECH-40-01 à 04) seront intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, disque ~17 Go libres le 2026-10-31 ; tests ciblés seulement ; suite complète de la démo par la CI.

## Sprint Goal

> « Appliquer les maquettes Claude Design au widget copilote et au bloc de réassort (mise en forme CSS/Twig), et mettre en place la couverture de code pilote (PHPUnit + Clover) sur 3 plugins et la démo pour établir une base de mesure. »

## Périmètre engagé

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-246](../../backlog/user-stories/US-246.md) | Copilote d'achat : mise en forme du widget | 2 | Should | ready-for-dev | Dépend d'US-248 (done S39) |
| [US-251](../../backlog/user-stories/US-251.md) | Commerce prédictif : mise en forme du bloc de réassort | 3 | Should | ready-for-dev | Dépend d'US-250 (done S39) |
| [US-252](../../backlog/user-stories/US-252.md) | Couverture de code pilote (3 plugins + démo) | 5 | Should | ready-for-dev | Indépendante |

Total engagé : **10 points**.

- **US-246** applique la maquette Claude Design du copilote (pages Copilote/CopiloteMobile du canevas `G2StRQigRKD6SqNRoyHpkW`) au widget Twig existant : styles CSS, mise en page responsive, états (vide, question, réponse, repli, erreur), contrastes AAA, test fonctionnel rouge prouvé.
- **US-251** applique la maquette Claude Design du réassort (pages Reassort/ReassortMobile) au bloc d'alertes existant : styles CSS, mise en page responsive, états (aucune alerte, une, plusieurs dont retard), contrastes AAA, retard non signalé par la seule couleur, test fonctionnel rouge prouvé.
- **US-252** met en place PHPUnit avec Clover sur 3 plugins pilotes (copilote, parc, passeport) et la démo : configuration phpunit.xml.dist, CI step informative, taux affiché sans seuil bloquant, rapport dans `config/demo/coverage.json`, page de preuve mise à jour.
- **Tâches techniques** (actions de rétro du sprint 39) :
  - T-TECH-40-01 : consigne de cohérence maquette/fixtures (le fork wdesign lit les fixtures)
  - T-TECH-40-02 : estimer le budget de tours avant chaque fork > 2 pts
  - T-TECH-40-03 : corriger le montant 1 017,35 → 963,50 € dans la maquette copilote
  - T-TECH-40-04 : grep « sprint N » dans traductions/templates publics

**Hors sprint** : US-212 (copilote réassort automatique, 8 pts) reportée ; US-253 (couverture tous les plugins, 5 pts) bloquée par US-252.

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote, commerce prédictif et parc avec Sylius Plus. En cas de recoupement avéré pendant le sprint, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugins). Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-31)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 39 fusionné (PR #145, 23962a6)
- [x] Actions de la rétrospective du sprint 39 reprises :
  - action 1 : T-TECH-40-01 (cohérence maquette/fixtures)
  - action 2 : T-TECH-40-02 (estimer budget tours forks)
  - action 3 : T-TECH-40-03 (corriger montant maquette copilote)
  - action 4 : T-TECH-40-04 (grep sprint N dans traductions)
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-31
- [x] Stories estimées et « Ready » : US-246 (2), US-251 (3), US-252 (5)
- [ ] Capacité calculée : à calculer après décomposition
- [x] Fiches US-246, US-251, US-252 à jour
- [x] Branche `feature/sprint-40-mise-en-forme-et-couverture` créée
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Graphe de dépendances entre tâches
- [ ] `sprint-status.yaml` mis à jour (`current_sprint: 40`)

## Cérémonies prévues

| Cérémonie | Moment | Format |
|-----------|--------|--------|
| Sprint Planning (Part 1) | Début de sprint | Périmètre ci-dessus |
| Sprint Planning (Part 2) | Après la décomposition | Tâches détaillées |
| Daily | Chaque lot de travail | Statut sprint-status.yaml |
| Sprint Review | Fin du périmètre | sprint-review.md |
| Sprint Retrospective | Après la review | sprint-retro.md |
| Affinage | Si besoin | Ajustement du backlog |
