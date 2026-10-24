# Tâches : US-177 : Recettes Symfony Flex des packs

## Informations US

- **Epic** : EPIC-14
- **Persona** : lead développeur d'une agence
- **Story Points** : 5 (Could, **sacrifice n°1**)
- **Sprint** : sprint-25-packs-et-transmission
- **Story** : [US-177](../../../backlog/user-stories/US-177.md)

## Résumé de la US

**En tant que** lead développeur
**Je veux** qu'installer un pack déclare aussi ses bundles, sa configuration et ses routes
**Afin que** le pack soit vraiment clé en main

## Règles et contraintes

- Une recette Flex par métapaquet : bundles, `config/packages`, `config/routes`. **Point non vérifié** (question ouverte 3 de l'[ADR 0015](../../../../docs/adr/0015-distributions-de-plugins.md)) : Flex applique-t-il une recette à un métapaquet ? L'essai (T-177-02) est la porte de décision de la story.
- **Si l'essai montre que la recette n'est pas appliquée** (scénario alternatif) : la story s'arrête après T-177-03 ; les tâches T-177-04 à T-177-13 sont écartées avec ce motif (🚫), les README gardent les gestes manuels et l'ADR 0015 est mis à jour.
- Essai dans un **projet jetable minimal** hors dépôt (squelette Symfony avec Flex, endpoint de recettes local), supprimé avec `/bin/rm` ; ni Sylius ni `vendor/` de plugins en local (environ 7 Go libres). Le démarrage réel avec Sylius est vérifié par la CI d'US-176, étendue sans les gestes du README (T-177-08).
- La recette n'écrase jamais un fichier déjà présent dans l'application.
- La recette retire à la désinstallation ce qu'elle a ajouté (à vérifier par l'essai ; limite écrite si Flex ne le permet pas).
- Les bundles de la recette viennent du même fichier de gestes qu'US-176 (source unique, un test les compare).
- **La pull request sur `symfony/recipes-contrib` est publique** : ouverte seulement après le **GO explicite de Thibaut** (T-177-12). Sans GO, aucune PR (scénario « Publication sans accord ») : la recette reste prête dans le dépôt du pack, la story se clôt sans T-177-13.
- **Aucun push, PR, tag ni release dans les dépôts de packs sans GO de Thibaut** (T-177-10).
- Même dépôts que US-176 : la story démarre après le merge d'US-176 (mêmes fichiers de workflow et de README).
- **Règle 10** : `git` dans l'arbre principal, pas de worktree isolé.
- Ton neutre, aucune comparaison avec une offre commerciale, aucun détail de sécurité.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 24 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de rendu. **Preuves : sortie de l'essai en projet jetable (commandes et dernières lignes), runs de CI des deux dépôts de packs** (variante sans gestes), état de la PR sur `recipes-contrib` si Thibaut donne son GO.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Dépôts des packs : `recipe/` ou dossier de recette (manifeste, `config/packages/`, `config/routes/`), `tests/`, `.github/workflows/`, README FR et EN
- `docs/adr/0015-distributions-de-plugins.md` (résultat de l'essai : écrit par l'orchestrateur)
- Aucun fichier de la démo

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-177-01 | [DOC] | Cadrage : fonctionnement des recettes Flex (manifeste, `copy-from-recipe`, `bundles`), endpoint de recettes local pour l'essai, contenu des recettes tiré des gestes d'US-176, règles de `recipes-contrib` (conventions, lecture seule) ; plan du projet jetable et du nettoyage | 0,75 h | US-176 mergée | Claude | ✅ |
| T-177-02 | [OPS] | Essai de la porte de décision : projet jetable minimal avec Flex, métapaquet de pack avec recette servie par l'endpoint local ; la recette est-elle appliquée à un métapaquet ? Commandes et sorties consignées, espace libre relevé, projet supprimé avec `/bin/rm` | 1,5 h | T-177-01 | Claude | ✅ |
| T-177-03 | [DOC] | Résultat de l'essai consigné dans l'ADR 0015 (question ouverte 3) et décision d'arrêt ou de poursuite ; si arrêt : T-177-04 à T-177-13 écartées avec le motif | 0,25 h | T-177-02 | Orchestrateur | ✅ |
| T-177-04 | [TEST] | Tests rouges, commités seuls et cités dans la PR de chaque dépôt : manifeste valide, fichiers de la recette présents, bundles de la recette égaux aux bundles du fichier de gestes, fichier d'application déjà présent non écrasé (dossier fictif) | 1 h | T-177-03 | Claude | ✅ |
| T-177-05 | [BE] | Recette du pack « IA et headless » : manifeste, `config/packages/`, `config/routes/` | 1,25 h | T-177-04 | Claude | ✅ |
| T-177-06 | [BE] | Recette du pack « Conformité et outillage » | 0,75 h | T-177-04 | Claude | ✅ |
| T-177-07 | [TEST] | Essai d'installation puis de retrait (`composer remove`) dans un projet jetable : la recette retire ce qu'elle a ajouté, un fichier préexistant n'est pas écrasé ; sorties citées ; projet supprimé | 1 h | T-177-05, T-177-06 | Claude | ✅ |
| T-177-08 | [OPS] | Variante de la CI d'US-176 sans les gestes du README (recette appliquée par l'endpoint local), en matrice Sylius 2.2 et 2.3 | 1 h | T-177-07 | Claude | ✅ |
| T-177-09 | [DOC] | README FR et EN : gestes remplacés par « appliqué par la recette » là où c'est vrai, gestes restants (classes de l'application) conservés ; texte relu contre la recette | 0,5 h | T-177-08 | Claude | ✅ |
| T-177-10 | [OPS] | 📅 Thibaut donne son GO pour pousser les branches, ouvrir les PR et les fusionner dans les deux dépôts de packs (sans tag ni release) | 0,25 h | T-177-09 | Thibaut | ✅ |
| T-177-11 | [OPS] | Push, PR et CI des deux dépôts après GO, corrections jusqu'au vert, puis merge (CI terminée) ; aucun tag | 0,75 h | T-177-10 | Orchestrateur | ✅ |
| T-177-12 | [OPS] | 📅 Thibaut donne (ou refuse) son GO explicite pour la PR publique sur `symfony/recipes-contrib` ; sans GO, aucune PR | 0,25 h | T-177-11 | Thibaut | ✅ |
| T-177-13 | [OPS] | Après GO seulement : fork, branche, PR sur `symfony/recipes-contrib` selon les conventions du dépôt, lien consigné ; texte de la PR relu contre la recette | 0,75 h | T-177-12 | Claude | ✅ |
| T-177-14 | [REV] | Revue (essai rejouable, aucun écrasement, ton neutre, parité FR et EN, aucune PR sans GO) et table « scénario → preuve » remplie | 0,5 h | T-177-11 | Claude | ✅ |

**Total estimé : 10,5 h** (14 tâches, dont 0,5 h de Thibaut ; 10 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

PR fusionnées (squash) : Conformité #2 (`8277b69`), IA #2 (`8e1af4e`). Commits rouges, tests seuls : IA `5b5fded` `f7ecec2` ; Conformité `2a9654c` `e0db542`.

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI, état de la PR). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Pack installé sans geste manuel (nominal) | Jobs `recipe`, runs 40038113308 (IA) et 40038119600 (Conformité) : « Configuring … From local/recipes:main », `--verify-applied`, migrations, lint, schéma, Sylius 2.2.4 et 2.3.0 |
| Désinstallation propre (alternatif) | `bin/flex-trial` (installation, retrait, fichier préexistant), rejoué dans le job `flex-trial` : runs 40037450669 (IA, lignes PASS relues) et 40037454014 (Conformité, vert) |
| Recette non appliquée à un métapaquet (alternatif) | Sans objet : la porte T-177-02 est franchie, Flex applique la recette d'un métapaquet ; résultat consigné dans l'ADR 0015 (T-177-03) |
| Fichier de l'application déjà présent (erreur) | `bin/flex-trial` : fichier non écrasé à l'installation ; limite (supprimé à `composer remove`) écrite dans les README et l'ADR 0015 |
| Publication sans accord (erreur) | Aucune PR sur `symfony/recipes-contrib` avant le GO de Thibaut (2026-10-24) ; après GO : symfony/recipes-contrib#2094, recettes dans des dossiers `0.1` (version publiée des packs ; le dossier `1.0` préparé ne se serait jamais appliqué) |
