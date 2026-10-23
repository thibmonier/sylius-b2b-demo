# Tâches : US-169 : Spike : passage de la démo à PHPUnit 13

## Informations US

- **Epic** : EPIC-10
- **Persona** : Thibaut (mainteneur)
- **Story Points** : 2 (Could, n°1)
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-169](../../../backlog/user-stories/US-169.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** savoir si la démo peut passer à PHPUnit 13 et à quel coût
**Afin de** décider de la montée sans casser la compatibilité avec Sylius

## Règles et contraintes

- Spike limité à 4 h. Aucun merge de la PR #54 pendant le spike ; travail sur une branche jetable.
- Point de départ (2026-10-23) : Composer résout PHPUnit 13.4.3 ; la CI de la PR #54 échoue sur 59 erreurs PHPStan dans les tests.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Branche de la PR #54 (`composer.json`, `composer.lock`)
- `tests/` (lecture, essais sur la branche)
- `phpstan.neon`
- Rapport : `project-management/audits/`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-169-01 | [OPS] | Branche d'essai depuis la PR #54 ; `composer why-not phpunit/phpunit 13` et bornes des paquets de développement relevées | 0,5 h | - | Claude | 🔲 |
| T-169-02 | [TEST] | PHPStan sur la branche : les 59 erreurs classées par cause (types de l'API de PHPUnit 13, extension PHPStan de PHPUnit, code de test) | 1 h | T-169-01 | Claude | 🔲 |
| T-169-03 | [TEST] | Suite sur la branche (CI) : échecs et dépréciations de PHPUnit 13 listés (attributs, mocks, `TestCase`) | 1 h | T-169-01 | Claude | 🔲 |
| T-169-04 | [DOC] | Rapport : blocages, erreurs par cause, changements de code, estimation en points et recommandation | 1 h | T-169-02, T-169-03 | Claude | 🔲 |
| T-169-05 | [REV] | 📅 Thibaut lit le rapport et décide (story de montée ou fermeture de la PR #54) | 0,25 h | T-169-04 | Thibaut | 🔲 |
| T-169-06 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml` ; story de montée créée ou PR #54 fermée avec Dependabot réglé pour ignorer les versions majeures | 0,25 h | T-169-05 | Orchestrateur | 🔲 |

**Total estimé : 4 h** (6 tâches, dont 0,25 h de Thibaut ; 3,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Rapport de faisabilité (nominal) | |
| Montée possible à faible coût (alternatif) | |
| Montée coûteuse (alternatif) | |
| Blocage de dépendance (erreur) | |
| Spike hors budget (erreur) | |
