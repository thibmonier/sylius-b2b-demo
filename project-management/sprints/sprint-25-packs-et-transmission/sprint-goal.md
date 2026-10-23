# Sprint 25 : Packs installés pour de vrai et transmission des factures électroniques

> **Lancé le 2026-10-24**, le lendemain de la clôture du sprint 24. Périmètre décidé par Thibaut le 2026-10-24 : **17 points engagés**. La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-25`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 25 |
| Début | 2026-10-24 (lancement) |
| Fin | au plus tard le 2026-11-07 ; clôture dès que le périmètre est vérifié |
| Capacité | **17 points** ; 62 tâches, 42,5 h (41,25 h pour Claude, 1,25 h d'actions de Thibaut) pour 43,7 h de capacité (17 × 2,1 h + 8 h transverses), soit 2,45 h de marge |
| Vélocité de référence | S22 23, S23 11, S24 19 (moyenne 17,7) |
| Branche | `feature/sprint-25-packs-et-transmission` (PR du sprint dans la démo) ; branches par story dans les dépôts des packs ; dossier local du nouveau plugin |

Le périmètre (17 points) est égal à la vélocité moyenne des trois derniers sprints (17,7).

## Sprint Goal

> « Prouver que les packs de plugins s'installent vraiment et deviennent clé en main (installation réelle en CI, recettes Flex, page dans la démo), et poser la transmission des factures électroniques par un port remplaçable avec son journal de statuts. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-176](../../backlog/user-stories/US-176.md) | Installation réelle d'un pack en CI | 5 | Should | ready-for-dev | non sacrifiable |
| [US-178](../../backlog/user-stories/US-178.md) | Page « Installer un pack » dans la démo | 2 | Should | ready-for-dev | non sacrifiable |
| [US-177](../../backlog/user-stories/US-177.md) | Recettes Symfony Flex des packs | 5 | Could | ready-for-dev | **n°1** |
| [US-180](../../backlog/user-stories/US-180.md) | Transmission des factures électroniques, tranche A : port, journal et statuts | 5 | Should | ready-for-dev | **n°2** |

Total engagé : **17 points**.

**Ordre de sacrifice** (décidé par Thibaut) : US-177, puis US-180.
- US-176, US-177 et US-178 sont les stories 2, 3 et 4 de la réalisation de l'[ADR 0015](../../../docs/adr/0015-distributions-de-plugins.md) ; US-175 (les métapaquets, v0.1.0) est faite et publiée.
- US-177 est la plus incertaine : Flex applique-t-il une recette à un métapaquet ? L'essai est la porte de décision ; la pull request publique sur `symfony/recipes-contrib` attend le **GO explicite** de Thibaut.
- US-180 est la story A de l'[ADR 0016](../../../docs/adr/0016-facture-electronique-pa-pdp.md) : un nouveau plugin développé en local, dont le dépôt, le tag et la soumission à Packagist attendent le GO de Thibaut.

**Hors sprint** : ADR 0017 (connecteur ERP), aucune réalisation ce sprint (décision de Thibaut) ; stories B à E de l'ADR 0016, au backlog et non créées ; US-128 attend ses données (bilan J+30 du 2026-11-18, fin du calendrier éditorial le 2026-12-15) ; US-103 et US-121 restent conditionnées au transcript T-103-03 ; US-154 et US-062 sont au backlog.

## Checklist de démarrage (2026-10-24)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 24 clos le 2026-10-23 (19 points sur 19, revue et rétro écrites)
- [x] Actions de la rétrospective du sprint 24 reprises :
  - action 1 : ✅ ligne ajoutée à `checklist-integration.md` (T-TECH-25-01), exécution à chaque lot (T-TECH-25-05) ;
  - action 2 : ✅ règle 10 de `consigne-worker.md` (T-TECH-25-02), section « Règle 10 » de `tasks/README.md` ;
  - action 3 : ✅ ligne ajoutée à `checklist-integration.md` (T-TECH-25-03), exécution : T-TECH-25-11 ;
  - action 4 : ✅ règle 11 de `consigne-worker.md` (T-TECH-25-04), contrôle : T-TECH-25-08.
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-24
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 43,7 h pour 41,25 h de charge de Claude
- [x] Branche `feature/sprint-25-packs-et-transmission` créée ; `.bmad/sprint-status.yaml` mis à jour par l'orchestrateur
- [x] Disque : environ 7 Go libres le 2026-10-24, au-dessus du seuil de 5 Go de `bin/run-tests` ; sous les 10 Go qui permettraient de construire l'image en local ; aucune installation lourde en local pour les packs (la CI est le juge)

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [ ] Clones des dépôts des packs créés (T-176-01)
- [ ] GO de Thibaut pour le push des branches de packs (T-176-09), à demander dès les workflows prêts

## Definition of Ready (contrôle du 2026-10-24)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis d'environnement | État |
|----|-------|----------------------|----------------------------|------|
| US-176 | oui | oui | US-175 faite ; clones des deux dépôts ; GO de Thibaut avant tout push | prête |
| US-178 | oui | oui | métapaquets et README lisibles via `gh` ; lieu de vérification du rendu : production après déploiement automatique | prête |
| US-177 | oui | oui | US-176 mergée ; projet jetable minimal ; GO de Thibaut pour le push puis, séparément, pour la PR publique | prête |
| US-180 | oui | oui | squelette des plugins `tmonier/*` ; Docker ; 5 Go libres ; GO de Thibaut avant tout dépôt distant | prête |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR (y compris pour le code de l'orchestrateur)
- [ ] US-178 : rendu vérifié en production après le déploiement automatique de `main` (pa11y WCAG2AAA en FR et en EN, 360 px, 1280 px) ; la story reste en review jusque-là
- [ ] PR de la démo qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check`
- [ ] ECS, PHPStan max et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors`
- [ ] Aucun secret ni détail de sécurité exploitable dans un dépôt public
- [ ] Texte public relu contre sa source avant le commit
- [ ] Documentation à jour
- [ ] CI verte (démo : « Quality and smoke tests », « Docker image » ; dépôts de packs et du plugin : tous les jobs), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun push, tag, release, dépôt ni PR publique sans GO de Thibaut

## Actions de Thibaut

- GO pour pousser et fusionner les branches des deux dépôts de packs : installation réelle (T-176-09), puis recettes (T-177-10).
- GO explicite, ou refus, pour la PR publique sur `symfony/recipes-contrib` (T-177-12).
- GO pour créer le dépôt du plugin de transmission, pousser et poser le tag (T-180-11), puis soumission à Packagist (T-180-13).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Installation réelle en CI instable ou plus longue que prévu (cycles de CI) | Élevée | Moyen | Marge de 2,45 h ; scripts testés en local avant le push ; un échec est lu et corrigé avant de relancer ; T-176-10 est le poste d'aléa |
| Flex n'applique pas la recette à un métapaquet | Moyenne | Moyen | Porte de décision T-177-02 avant tout développement ; scénario prévu par la fiche : arrêt, README inchangés, ADR 0015 mis à jour |
| Recette erronée publiée sous le nom du pack | Faible | Élevé | Essai d'installation et de retrait en projet jetable, CI sans gestes du README ; PR publique seulement sur GO de Thibaut |
| Publication sans GO (dépôt, tag, PR publique) | Faible | Élevé | Tâches 📅 de Thibaut avant toute écriture distante (T-TECH-25-12) ; un GO couvre l'objet nommé seulement |
| Disque qui repasse sous 5 Go : `bin/run-tests` refuse de démarrer | Moyenne | Moyen | Relevé avant chaque lot, nettoyage des bases après chaque lot (action 1), un seul serveur lourd à la fois, aucune installation lourde pour les packs |
| Worker bloqué par un refus prévisible de `git` en worktree isolé | Faible | Moyen | Règle 10 : toute tâche avec `git` dans l'arbre principal, séquencée |
| Texte public inexact (README des packs, page, PR de recette, README du plugin) | Moyenne | Élevé | Relecture contre la source avant le commit (action 3, T-TECH-25-11) |
| Statuts de facturation présentés comme officiels | Moyenne | Élevé | Ensemble minimal et extensible, marqué « à vérifier » (ADR 0016) ; statut inconnu refusé ; aucun avis juridique |
| Texte qui compare avec une offre commerciale ou plugin classé « concurrent » dans un pack | Faible | Élevé | `PublicTextCharter`, `etc/composition.json` des packs, relecture à chaque revue |
| Page des packs en retard sur les `composer.json` des packs | Moyenne | Moyen | Données générées, test qui compare la liste affichée au fichier, contrôle de retard sur `bin/proof-check` |
| Écriture de la preuve en retard sur `main` (US-178) | Moyenne | Faible | T-TECH-25-10 ; build de `main` surveillé à chaque merge |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-07. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-25-13), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-24 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Au premier GO demandé (T-176-09) ou à l'essai de la porte de décision d'US-177 | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-07 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
