# Tâches : US-168 : Contrôle de retard de la preuve publiée (`proof.json`)

## Informations US

- **Epic** : EPIC-10
- **Persona** : Thibaut (mainteneur) ; Camille (recruteuse IT)
- **Story Points** : 1 (Should, non sacrifiable)
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-168](../../../backlog/user-stories/US-168.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** qu'un contrôle signale un `proof.json` en retard sur l'état réel du dépôt
**Afin de** que les chiffres publiés par la démo ne restent jamais faux en silence

## Règles et contraintes

- Le contrôle ne lit que le dépôt local et `config/demo/proof.json` ; aucun appel réseau (les chiffres des dépôts de plugins restent à `bin/proof-refresh`).
- Même règle de comptage des fichiers de tests que `ProofCollector::testsFact()` : une seule source de vérité (extraire la règle plutôt que la recopier).
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `bin/proof-refresh` (ou commande dédiée, décidé au cadrage)
- `src/Demo/Proof/Refresh/`
- `tests/Unit/Demo/Proof/`
- `.github/workflows/ci.yaml` (**partagé avec US-166**)
- `docs/tests-locaux.md`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-168-01 | [DOC] | Cadrage : règle de comptage de `ProofCollector`, clés publiées (fichiers de tests de la démo, nombre de plugins), point d'entrée retenu (option `--check` ou commande dédiée), comportement en PR et sur `main` | 0,25 h | - | Claude | 🔲 |
| T-168-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (preuve à jour, écart affiché en PR, rafraîchissement puis contrôle vert, écart en erreur avec chiffres publiés et réels, fichier absent ou mal formé sans trace de pile) | 0,75 h | T-168-01 | Claude | 🔲 |
| T-168-03 | [BE] | Contrôle sans écriture : extraction de la règle de comptage partagée, comparaison, message par chiffre (publié, réel, écart), codes de sortie | 0,75 h | T-168-02 | Claude | 🔲 |
| T-168-04 | [OPS] | CI : étape de contrôle, écart dans le résumé du job avec la commande de rafraîchissement ; bloquante sur `main`, informative sur les PR (décision du cadrage consignée) | 0,5 h | T-168-03 | Claude | 🔲 |
| T-168-05 | [TEST] | Suite ciblée via `bin/run-tests` (si le disque le permet, sinon CI), ECS (`src tests`), PHPStan max, Rector ; `docs/tests-locaux.md` à jour | 0,5 h | T-168-04 | Claude | 🔲 |
| T-168-06 | [REV] | Revue et table « scénario → test » remplie ci-dessous (action 1 de la rétro du sprint 22) | 0,25 h | T-168-05 | Claude | 🔲 |

**Total estimé : 3 h** (6 tâches, dont 0 h de Thibaut ; 3 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Preuve à jour (nominal) | |
| Tolérance documentée (alternatif) | |
| Rafraîchissement après écart (alternatif) | |
| Preuve en retard (erreur) | |
| Source illisible (erreur) | |
