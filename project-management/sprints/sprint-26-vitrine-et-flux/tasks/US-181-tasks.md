# Tâches : US-181 : Plugin b2b-mcp : dépendance `symfony/lock` et import des routes du serveur MCP

## Informations US

- **Epic** : EPIC-14
- **Persona** : Lead développeur d'une agence
- **Story Points** : 1 (Should, sacrifice : non sacrifiable)
- **Sprint** : sprint-26-vitrine-et-flux
- **Worker proposé** : w185 (après US-185 ou en cas de sacrifice d'US-185)
- **Story** : [US-181](../../../backlog/user-stories/US-181.md)

## Résumé de la US

**En tant que** lead développeur  
**Je veux** installer le plugin b2b-mcp en suivant son README sans dépendance manquante ni erreur du routeur  
**Afin de** ne pas déboguer l'installation d'un plugin publié

## Règles et contraintes

- Travail dans le dépôt `thibmonier/sylius-b2b-mcp-plugin` (cloné à côté de la démo), branche et PR ; **tag et release correctifs seulement sur GO de Thibaut** (T-181-04).
- Lire la matrice de CI du dépôt avant d'écrire du code ; plancher commun PHP 8.2, Sylius 2.2 et 2.3, Symfony 6.4, 7.4 et 8.0.
- CI du plugin comme juge ; aucune installation lourde en local sous 6 Go libres.
- Concordance des versions contrôlée avant le tag (CHANGELOG, tag, contrainte du pack) ; geste `symfony/lock` du pack « IA et headless » retiré seulement si la borne du pack couvre la version corrigée.
- Règle 10 : `git` dans l'arbre principal, pas de worktree isolé ; `git fetch` et branche courante contrôlées avant tout commit.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 25 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de rendu. Preuves : runs de CI du plugin (matrice complète) et de la CI d'installation du pack « IA et headless ».

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Dépôt `sylius-b2b-mcp-plugin` : `composer.json`, `README.md`, `README.fr.md`, `CHANGELOG.md`, `tests/`
- Dépôt `sylius-pack-ai-headless` : fichier de gestes et README (conditionnel, T-181-06)
- Aucun fichier de la démo

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-181-01 | [DOC] | Cadrage : dépôt `sylius-b2b-mcp-plugin` (branche, matrice de CI, `composer.json`, README), recette Flex de `sylius/mcp-server-plugin`, gestes actuels du pack « IA et headless » | 0,25 h | - | Claude | ✅ |
| T-181-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : `symfony/lock` dans `require`, README sans import des routes en double, geste du README cohérent avec la recette | 0,5 h | T-181-01 | Claude | ✅ |
| T-181-03 | [BE] | `symfony/lock` ajouté au `require` ; README FR et EN corrigés (import des routes une fois, cas avec et sans Flex) ; CHANGELOG | 0,5 h | T-181-02 | Claude | ✅ |
| T-181-04 | [OPS] | 📅 Thibaut donne son GO pour pousser la branche, ouvrir la PR du plugin, la fusionner puis poser le tag et la release corrective | 0,25 h | T-181-03 | Thibaut | ✅ |
| T-181-05 | [OPS] | Après GO : push, CI verte sur toute la matrice (run terminé), concordance de la version avant le tag, tag et release | 0,5 h | T-181-04 | Claude | ✅ |
| T-181-06 | [OPS] | Pack « IA et headless » : geste `symfony/lock` retiré si la borne couvre la version corrigée, CI d'installation verte (PR du dépôt du pack, GO couvert par T-181-04 pour ce seul objet, sinon redemandé) | 0,5 h | T-181-05 | Claude | ✅ |

**Total estimé : 2,5 h** (6 tâches, dont 0,25 h de Thibaut (1 tâche) ; 2,25 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Dépendance déclarée (nominal) | `tests/Unit/ComposerRequirementsTest.php` du plugin ; PR thibmonier/sylius-b2b-mcp-plugin#10, release v1.2.3 |
| Projet avec Flex (alternatif) | `tests/Unit/InstallationDocumentationTest.php` (README FR et EN) ; PR #10 |
| Projet sans Flex (alternatif) | `tests/Unit/InstallationDocumentationTest.php` ; PR #10 |
| Import en double (erreur) | `tests/Unit/InstallationDocumentationTest.php` (import des routes une seule fois, sans Flex seulement) ; PR #10 |
| Pack inchangé (erreur) | PR thibmonier/sylius-pack-ai-headless#3 (borne `^1.2.3`, geste `symfony/lock` retiré), fusionnée ; la borne est reprise dans la démo par #88 (`packs.json`) |
