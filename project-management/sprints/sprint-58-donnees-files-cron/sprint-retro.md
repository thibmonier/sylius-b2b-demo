# Rétrospective du sprint 58

**Directive fondamentale** : « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »

Entrée principale : [sprint-review.md](./sprint-review.md). Bilan du sprint :
- 15 points sur 15 « done » en deux jours (2026-11-25 → 2026-11-26) ;
- trois stories fusionnées dans le même dépôt, l'une après l'autre ;
- une seule release (v0.4.0), posée après la documentation des trois ;
- la démo en ^0.4 et la production déployée.

Format : ce qui a marché, ce qui a coûté, actions SMART (comme aux sprints 56 et 57).

## Suivi des actions du sprint 57

| ID | Appliquée ? | Preuve |
|----|-------------|--------|
| A-57-1 (release après toute la documentation, dernière tâche) | Oui | v0.4.0 taguée après la fusion de #15, #16 et #17, documentation comprise. T-TECH-58-03 était la dernière tâche du sprint. Aucune release de documentation seule |
| A-57-2 (PHPStan et ECS sans filtre, code de retour lu) | Oui | Règle 31 appliquée à chaque commit rouge. Aucun run rouge arrêté à PHPStan ou ECS. Un rouge est pourtant tombé en erreur fatale : US-296 sous Symfony ^8.0, collision de `runCommand`, corrigée par `ac4b132` |
| A-57-3 (`proof-refresh` relu après une release) | Oui, et il a payé | `proof.json` relu avant #228 (v0.4.0, couverture 94,87 %). Cette relecture a révélé que `main` était rouge : la CI de la démo y figurait en `failure` |
| A-57-4 (relevé Railway) | En attente | Échéance au 2026-12-01, à la charge de Thibaut |

## Ce qui a bien marché

- **Trois stories en série dans un seul dépôt, sans PR empilée** : chaque story est fusionnée avant que la suivante démarre (règle 27). Aucun conflit dans le plugin.
- **Le rouge sur les 10 combinaisons** a attrapé un écart que le poste local ne voyait pas (Symfony ^8.0 et `runCommand`). Une suite locale sur une seule version l'aurait laissé passer.
- **La CI allégée des PR de documentation (T-TECH-58-04)** : quatre PR de suivi fusionnables en quelques minutes au lieu de 18.
- **Des constats réels dès l'arrivée dans la démo** :
  - le transport `async` sans transport d'échec, corrigé ;
  - la dérive du schéma, qui a donné US-318 ;
  - le reset qui aurait effacé les battements chaque nuit, évité avant la production.
- **Écart de spécification remonté au lieu d'être tranché en silence** : le seuil d'US-296 a été soumis à Thibaut, qui l'a accepté.
- **Imprévus traités à la racine, sans empilement** : 20 avis de sécurité Symfony (#229, PR séparée). Le conflit de `composer.lock` avec #228 a été résolu par fusion de `main` et nouvelle mise à jour ciblée.

## Ce qui a coûté

- **`main` de la démo rouge pendant quatre fusions, sans que personne le voie.**
  - #224 a ajouté un fichier de tests sans rafraîchir `proof.json`.
  - Les avis Symfony sont arrivés le même jour.
  - Les PR de documentation suivantes, en CI allégée, ne lancent ni `proof-check` ni l'audit, et personne ne lisait la CI de `main` après une fusion.
- **Le poste de test local à remonter** : `vendor` périmé (plugin absent), assets de l'admin non construits, donc une 500 sur toutes les pages. Trois allers-retours avant le premier rouge lisible ; la procédure Docker des tests fonctionnels de la démo n'est pas écrite.
- **Une contradiction dans la fiche d'US-296** (marge de 50 % contre `warning` à 2 battements), découverte à l'implémentation et non à la planification.
- **Le rouge de T-TECH-58-03 vu en local seulement** : la règle 26 ne dit pas ce qui s'applique à une tâche de la démo.
- **Deux PR de dépendances en parallèle, un conflit de `composer.lock`**, comme au sprint 57 (#216 et #217).

## Actions (SMART)

| ID | Action | Responsable | Échéance | Mesure |
|----|--------|-------------|----------|--------|
| A-58-1 | Après chaque fusion dans la démo, la CI de `main` est suivie jusqu'au bout et un rouge est signalé à Thibaut avant toute autre PR | orchestrateur | dès le sprint 59 | aucun `main` rouge non signalé après plus d'une fusion |
| A-58-2 | `bin/proof-check` tourne aussi dans la CI allégée (sans base ni réseau), et une PR qui ajoute ou retire un fichier `*Test.php` embarque son `proof.json` rafraîchi | orchestrateur | planification du sprint 59 (tâche technique) | aucun écart du nombre de fichiers de tests sur `main` au sprint 59 |
| A-58-3 | Procédure Docker des tests fonctionnels de la démo écrite dans `docs/tests-locaux.md` : `composer install`, `yarn build` dans `node:24`, `bin/run-tests` dans `sylius-php:8.5-alpine` avec la base de l'hôte | orchestrateur | sprint 59 | un rouge fonctionnel lisible du premier coup sur une copie de travail neuve |
| A-58-4 | À la planification, chaque story à seuils numériques reçoit un tableau de cas aux bornes (valeur juste en dessous, sur la borne, juste au-dessus) ; une contradiction est tranchée par Thibaut avant le premier test | orchestrateur, Thibaut | planification du sprint 59 | aucun écart de seuil découvert à l'implémentation |
| A-58-5 | Règle 26 précisée pour les tâches de la démo : le rouge est poussé et vu en CI, comme pour les stories des plugins, sauf exception notée dans la fiche | orchestrateur | consigne du sprint 59 | aucun rouge vu en local seulement sans note au sprint 59 |

## ROTI

**5/5** (Thibaut, 2026-11-26). Actions A-58-1 à A-58-5 approuvées le même jour ; elles sont planifiées au sprint 59 (T-TECH-59-02 à 04).
