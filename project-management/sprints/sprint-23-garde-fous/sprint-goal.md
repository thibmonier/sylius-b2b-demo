# Sprint 23 : Garde-fous et lisibilité de l'architecture

> **Lancé le 2026-10-23**, juste après la clôture anticipée du sprint 22 (décision de Thibaut). Périmètre validé par Thibaut le même jour, puis remanié le même jour après la critique d'un architecte Sylius et d'un client B2B : **11 points**. La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-23`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 23 |
| Début | 2026-10-23 (lancement) |
| Fin | au plus tard le 2026-11-06 ; clôture dès que le périmètre est vérifié (action 6 de la rétro du sprint 22) |
| Capacité | **11 points** ; 40 tâches, 28,25 h (27,5 h pour Claude, 0,75 h d'actions de Thibaut) pour 31,1 h de capacité (11 × 2,1 h + 8 h transverses) |
| Vélocité de référence | S18 25, S19 25, S20 22, S21 17, S22 23 (moyenne 22,4) |
| Branche | `feature/sprint-23-garde-fous` (créée depuis `main`, commit `0af486a`) |

Le périmètre est sous la vélocité de référence pour deux raisons :
- **US-128** (bilan de communication) attend des données qui n'existent pas encore : le bilan J+30 du 2026-11-18 et la fin du calendrier éditorial, le 2026-12-15. Thibaut a validé sa sortie du sprint le 2026-10-23.
- **US-103 (3 points) et US-121 (5 points)** restent conditionnées au transcript T-103-03. S'il arrive pendant le sprint, elles peuvent entrer (19 points au total).

**Remaniement du 2026-10-23.** Thibaut a fait relire la démo par un architecte Sylius et un client B2B. Il en retient deux demandes : une page qui décrit l'architecture de la démo, et des distributions qui regroupent des plugins. Ses décisions :
- US-170 (page d'architecture) et US-173 (spike distributions) entrent ; US-132 et US-130 (spikes facture électronique et ERP) en sortent, vers le sprint 24 ;
- US-171 (matrice de compatibilité et graphe des dépendances) et US-172 (guide de surcharge) visent le sprint 24 ;
- packs sans recoupement avec Sylius Plus seulement ;
- volet commercial de la critique écarté (licences, SLA, Packagist privé, place de marché payante).

## Sprint Goal

> « Rendre automatiques les garde-fous qui ont manqué au sprint 22 (preuve en retard, tests sans attente, méthodes courtes), montrer l'architecture de la démo sur une page dédiée, puis décider de PHPUnit 13 et de la forme des distributions de plugins. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-168](../../backlog/user-stories/US-168.md) | Contrôle de retard de la preuve publiée (`proof.json`) | 1 | Should | backlog | non sacrifiable |
| [US-166](../../backlog/user-stories/US-166.md) | Méthodes longues découpées et suppression des `usleep` des tests | 3 | Could | backlog | non sacrifiable |
| [US-169](../../backlog/user-stories/US-169.md) | Spike : passage de la démo à PHPUnit 13 | 2 | Could | backlog | n°1 |
| [US-170](../../backlog/user-stories/US-170.md) | Page « Architecture de la démo » | 3 | Should | backlog | non sacrifiable |
| [US-173](../../backlog/user-stories/US-173.md) | Spike : distributions de plugins (packs clé en main) | 2 | Should | backlog | n°2 |

Total : **11 points**. **Ordre de sacrifice** : US-169, puis US-173.
- US-168 rattrape le scénario d'US-084 non livré au sprint 22.
- US-166 supprime la cause du test instable `RunTestsLockTest`.
- US-169 répond à une décision de Thibaut.
- US-170 et US-173 répondent à la critique du 2026-10-23.

**Hors sprint** : US-132, US-130, US-171 et US-172 visent le sprint 24 ; US-128 attend ses données (2026-11-18, 2026-12-15) ; US-103 et US-121 restent conditionnées au transcript T-103-03 ; US-154 et US-062 sont revenues au backlog après leur sacrifice du sprint 22.

## Checklist de démarrage (2026-10-23)

### Avant le Sprint Planning

- [x] Sprint précédent officiellement terminé : sprint 22 clos le 2026-10-23 (PR #67 et #68 mergées, revue et rétro écrites, 0 story en review)
- [x] Actions de la rétrospective du sprint 22 reprises :
  - actions 1, 2, 3 et 5 : tâches T-TECH-23-01 à 05 ;
  - action 4 : faite (PR #68) ;
  - action 6 : décidée (clôture dès le périmètre vérifié).
- [x] Backlog priorisé par le PO : périmètre validé par Thibaut le 2026-10-23
- [x] Stories candidates estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 31,1 h pour 27,5 h de charge de Claude
- [x] Branche `feature/sprint-23-garde-fous` créée ; `.bmad/sprint-status.yaml` : `current_sprint: 23`, sprint 23 `in-progress`
- [x] Disque : au moins 5 Go libres. 3,4 Go au lancement, **5,9 Go le 2026-10-23** après l'action de Thibaut (T-TECH-23-01b).

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [ ] Point d'étape avec Thibaut à caler sur ses créneaux de relecture (action 6 de la rétro du sprint 22)

## Definition of Ready (contrôle du 2026-10-23)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis d'environnement | État |
|----|-------|----------------------|----------------------------|------|
| US-168 | oui (créée le 2026-10-23, alignée au lancement sur le contenu réel de `proof.json` : fichiers de tests et plugins, pas d'assertions) | oui | aucun | prête |
| US-166 | oui | oui | aucun ; relevé du 2026-10-23 : 6 méthodes, 7 `usleep` | prête |
| US-169 | oui (créée le 2026-10-23) | oui | branche de la PR #54 | prête |
| US-170 | oui (créée le 2026-10-23) | oui | aucun | prête |
| US-173 | oui (créée le 2026-10-23) | oui | aucun ; `docs/vitrine-plugins.md` (classement US-063) | prête |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches (action 1 de la rétro du sprint 22)
- [ ] Stories de code : commit de tests rouges cité dans la PR (action 2)
- [ ] ECS, PHPStan max et Rector verts, rejoués avant le push
- [ ] Aucun secret ni détail exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Spikes : sources citées avec date de consultation, temps consigné, décision de Thibaut notée

## Actions de Thibaut

- Espace disque : fait (5,9 Go libres, T-TECH-23-01b).
- Lire les rapports et décider : PHPUnit 13 (T-169-05), distributions de plugins (T-173-05).
- Transcript T-103-03, s'il veut faire entrer US-103 et US-121.
- Déployer `main` sur Railway (v0.1.1 du parc d'équipements, en attente depuis le sprint 22).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Disque qui repasse sous 5 Go : `bin/run-tests` refuse de démarrer | Moyenne | Moyen | 5,9 Go le 2026-10-23 ; la CI valide ; un seul serveur lourd à la fois |
| Plantage de la machine (comme au sprint 22) | Moyenne | Moyen | Relevé du disque et de la mémoire avant chaque lot, arrêt des serveurs après usage |
| Pack ou page qui met en avant un plugin recoupant Sylius Plus | Faible | Élevé | Composition filtrée par le classement US-063 ; relecture du ton neutre |
| Chiffre faux sur la page d'architecture | Moyenne | Moyen | Chiffres lus dans `proof.json`, test qui refuse un chiffre écrit en dur |
| Conflit sur `.github/workflows/ci.yaml` entre US-168 et US-166 | Moyenne | Faible | Séquencer : US-168 puis US-166 |
| CI d'une PR qui ne démarre pas | Moyenne | Faible | `gh workflow run ci.yaml --ref <branche>` (constaté sur la PR #67) |

## Cérémonies

Calées sur le travail réel (action 6 de la rétro du sprint 22) : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-06.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-23 (périmètre validé par Thibaut, décomposition faite) | fait |
| Point d'étape | À la première relecture de Thibaut (rapport PHPUnit 13 ou ADR des distributions) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-06 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
