# Rétrospective - Sprint 40

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-31, préparée en fin de sprint |
| Durée | 30 min |
| Format | Starfish (continuer les comparaisons inter-sprints) |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeur Claude (orchestrateur, fork phase2-decompose) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **10 points livrés sur 10** (US-246, US-251, US-252, toutes en review). Vélocité : S35 8, S36 11, S37 13, S38 10, S39 8, **S40 10**. Sprint mixte design + qualité : mise en forme des widgets copilote et réassort selon les maquettes Claude Design, et mise en place de la couverture de code pilote via les check runs GitHub.

Faits marquants du sprint :
- **un seul fork a fait la décomposition ET l'implémentation** : le fork phase2-decompose, lancé pour la décomposition seule, a enchaîné sur l'implémentation des 3 stories et des actions T-TECH en 200 tours — efficace (1 seul agent) mais non conforme au workflow (pas de workers séparés, pas de TDD rouge prouvé distinct) ;
- **CI coverage par check run** : la CI de la démo publie désormais un check run « coverage » avec le taux de lignes couvertes, et `ProofCollector` le lit via l'API GitHub pour chaque dépôt ;
- **les plugins n'ont pas encore de job coverage** : le taux restera absent pour eux tant que leur CI n'ajoute pas un job similaire — acceptable (décision « taux réel même bas, aucun seuil bloquant ») ;
- **0 problème de Doctrine identity map** (leçon du sprint 39 bien retenue) ;
- **disque non bloquant** (17 Go libres) ;
- **0 release corrective**, 0 force-push, 0 test supprimé, 0 worker actif après passation.

Sources : branche `feature/sprint-40-mise-en-forme-et-couverture`, PR #146.

## Observations (Starfish)

### Continuer

- Les jetons de design (`_variables.scss`) réutilisés dans chaque nouveau SCSS : `_copilot.scss` et `_reorder.scss` utilisent les mêmes variables B2B (teal, paper, serif), ce qui garantit la cohérence visuelle.
- Le test fonctionnel par widget (BEM + aria-live + bilingue) : filet de sécurité contre les régressions de structure.
- `ProofCollector` lit uniquement des sources publiques (API GitHub) : pas de secret, pas de token spécial.
- Le registre des décisions tenu avant chaque action.

### Commencer

- Borner strictement le prompt du fork de décomposition pour qu'il ne déborde pas sur l'implémentation. Le fork phase2-decompose a consommé 200 tours en faisant tout le sprint.
- Ajouter un job coverage (PHPUnit + PCOV + check run) dans la CI des plugins pilotes pour que `ProofCollector` puisse lire leur taux.

### Arrêter

- Lancer un fork de décomposition avec un prompt trop ouvert qui lui permet de faire l'implémentation à la place des workers.

### Plus de

- Commits atomiques par story : chaque story a son commit distinct (45e9a6c, 5c3c42b, e856829), ce qui facilite le revert sélectif.

### Moins de

- Rien de notable ce sprint.

## Thèmes et analyse

### Thème 1 : Dérive du fork de décomposition (priorité haute)

Le fork lancé pour la phase 2 (décomposition des tâches) a enchaîné sur l'implémentation complète des 3 stories, atteignant sa limite de 200 tours. Le résultat est bon (code, tests, commits atomiques) mais le processus n'est pas conforme : pas de workers séparés, pas de validation DoD par l'orchestrateur entre chaque story, pas de TDD rouge prouvé distinct. Leçon : le prompt du fork de décomposition doit explicitement interdire l'implémentation et se terminer après le commit des fichiers de tâches.

### Thème 2 : Coverage des plugins (priorité moyenne)

`ProofCollector::coverageFact()` est prêt à lire le taux de couverture de n'importe quel dépôt, mais les plugins n'ont pas encore de job CI qui publie un check run « coverage ». Une story dédiée (ou un lot T-TECH) est nécessaire pour ajouter le job aux 3 plugins pilotes identifiés dans US-252, puis le généraliser à tous les plugins (US-253).

### Thème 3 : Positif — sprint sans friction (priorité basse)

Pas de problème de disque (17 Go), pas de conflit git, pas de Doctrine identity map, pas de test flaky. La correction du sprint 39 (`entityManager()->clear()`) a bien tenu. Le pipeline est stable.

## Suivi des actions de la rétrospective du sprint 39

| # | Action | Résultat au sprint 40 |
|---|--------|-----------------------|
| T-TECH-40-01 | Cohérence maquette/fixtures (consigne + fork wdesign lit les fixtures) | ✅ Consigne ajoutée dans `docs/conception-graphique.md` par le fork (commit 9a7c2ac). Pas de maquette ce sprint = 0 écart. |
| T-TECH-40-02 | Estimer le budget de tours avant chaque fork > 2 pts | ⚠️ Non appliqué : le fork de décomposition n'a pas été borné et a atteint 200 tours. Leçon renforcée. |
| T-TECH-40-03 | Corriger le montant 1 017,35 → 963,50 € dans la maquette Copilote | ⏳ Pas de maquette modifiée ce sprint (pas de fork wdesign). À faire si la maquette est réouverte. |
| T-TECH-40-04 | Grep « sprint N » dans traductions/templates publics | ✅ Vérifié par le fork (commit 9a7c2ac). 0 occurrence trouvée. |

## Métriques du sprint

| Indicateur | S38 | **S40** | S39 |
|------------|-----|---------|-----|
| Points livrés / engagés | 10/10 | **10/10** | 8/8 |
| Stories en review | 4 | **3** | 4 |
| Commits fonctionnels | 5 | **3** | 6 |
| Tests ajoutés (fichiers) | 1 | **2** | 2 |
| Lignes ajoutées (code+tests) | ~350 | **~307** | ~978 |
| Forks / workers | 4 | **1** | 4 |
| Pushes forcés | 0 | **0** | 0 |
| Tests retirés ou affaiblis | 0 | **0** | 0 |
| Worker actif après passation | 0 | **0** | 0 |
| Régressions trouvées par la CI | 0 | **à vérifier** | à vérifier |
| Actions de rétro réalisées | 5/5 | **2/4** | 5/5 |
| Forks atteignant la limite de tours | 0 | **1** (phase2) | 1 |

## Actions (SMART)

### Action 1 (T-TECH-41-01) : Prompt strict pour le fork de décomposition

| Attribut | Valeur |
|----------|--------|
| Description | Le prompt du fork de phase 2 (décomposition) doit contenir : « Tu DOIS te limiter à la création des fichiers de tâches, du task-board et du graphe de dépendances. Tu ne DOIS PAS implémenter les stories, écrire du code source, modifier des tests ni toucher à des fichiers en dehors de `project-management/sprints/`. Commite et termine. » |
| Responsable | Orchestrateur (Claude) |
| Deadline | Lancement du sprint 41 |
| DoD | Prompt mis à jour ; au sprint 41, le fork de décomposition termine en < 30 tours et ne touche aucun fichier hors project-management/. |
| Priorité | Haute |

### Action 2 (T-TECH-41-02) : Job coverage dans la CI des plugins pilotes

| Attribut | Valeur |
|----------|--------|
| Description | Ajouter un job PHPUnit + PCOV + check run « coverage » dans la CI de 3 plugins pilotes (copilot, predictive-analytics, customer-asset-management). Patron : le step « Publish coverage check run » du ci.yaml de la démo. |
| Responsable | Worker dev |
| Deadline | Sprint 41 |
| DoD | Les 3 plugins publient un check run « coverage » sur chaque push main ; `bin/proof-refresh` retourne un taux pour ces 3 plugins. |
| Priorité | Moyenne |

### Action 3 (T-TECH-41-03) : Corriger le montant dans la maquette Copilote

| Attribut | Valeur |
|----------|--------|
| Description | Report de T-TECH-40-03 : mettre à jour la maquette Copilote dans le canevas Claude Design (G2StRQigRKD6SqNRoyHpkW) en remplaçant « 1 017,35 € » par « 963,50 € HT ». |
| Responsable | Worker wdesign |
| Deadline | Sprint 41, avant toute story touchant la maquette copilote |
| DoD | Maquette mise à jour ; montant cohérent avec les fixtures. |
| Priorité | Basse |

## Points positifs à conserver

- 10 points sur 10, sprint mixte design + qualité réussi.
- 2/4 actions de la rétro précédente réalisées (les 2 restantes reportées car hors périmètre).
- 0 release corrective, 0 force-push, 0 test supprimé, 0 fichier écrasé.
- Commits atomiques par story, revert sélectif possible.
- Couverture de code intégrée dans la preuve technique via l'API GitHub (pas de token spécial).
