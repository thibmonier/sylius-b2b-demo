# Rétrospective - Sprint 23

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-23, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 et 22, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers w166, w169, w173) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : 11 points sur 11 en done, le jour même du lancement. Les trois garde-fous tournent (preuve en retard, attentes dans les tests, méthodes longues), la page d'architecture est en production et vérifiée, et Thibaut a tranché les deux spikes.

Les écarts du sprint :
- une erreur 500 introduite puis trouvée par les tests (piège Twig sur une méthode statique) ;
- une commande de fond bloquée par un alias de shell ;
- des commentaires masqués à la lecture par le filtrage de RTK, qui ont obligé un worker à refaire ses découpages ;
- le rendu réel d'US-170 n'a pu être vérifié qu'en production, faute de disque pour construire l'image en local.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- Les tests rouges commités seuls avant le code (3 stories de code sur 3) : c'est le test rouge resté rouge qui a révélé l'erreur 500 de la page Architecture.
- La table « scénario → test ou preuve » dans chaque fichier de tâches : 25 scénarios, 0 sans preuve.
- Les spikes sans installation lourde (résolution Composer sans `vendor/`, logs de CI) : sous le budget, sans risque pour la machine.
- La vérification en production en lecture seule, sans connexion : pa11y et la mesure à 360 px ont tourné sur la page publique.
- Les workers séparés par fichiers, avec `git add` sur chemins exacts : aucun conflit malgré deux écrivains dans le même arbre.

### Commencer

- Lire les fichiers à modifier sans filtrage (outil Read, ou `rtk proxy cat`), surtout avant un refactoring.
- Écrire la sortie des tests dans un fichier, puis la filtrer, au lieu de la couper dans un tube.

### Arrêter

- Appeler `rm` dans une commande de fond : l'alias `rm -i` attend une confirmation qui ne viendra jamais.
- Couper la sortie de PHPUnit avec `sed -n 1,N` ou `head` : on perd justement le message d'erreur.

### Plus de

- Pièges de l'environnement consignés dans `docs/tests-locaux.md` dès qu'ils coûtent du temps.

### Moins de

- Diagnostic à l'aveugle : quand un test fonctionnel renvoie 500, faire remonter l'exception tout de suite (`catchExceptions(false)`) plutôt que relire le gabarit.

## Thèmes et analyse

### Thème 1 : lecture filtrée par RTK (priorité haute)

Le proxy RTK réécrit `cat` et `sed` et masque les commentaires et les docblocks. Un worker qui découpe une méthode à partir de ce texte risque de supprimer des commentaires sans le voir. w166 l'a repéré, a restauré 5 fichiers et refait le travail ; deux commentaires de section ont quand même disparu. Le risque touche toute modification faite à partir d'une lecture par le shell.

### Thème 2 : pièges du shell dans les commandes de fond (priorité moyenne)

`rm` aliasé en `rm -i`, `sed` de BSD qui ignore l'alternance `\|` sans `-E`, sortie de test coupée par un tube : trois pièges, une quarantaine de minutes perdues au total. Aucun n'a cassé de livrable, mais chacun a retardé un diagnostic.

### Thème 3 : contrôle de preuve bloquant sur `main` (priorité moyenne)

Le garde-fou d'US-168 marche : le build de `main` est vert avec une preuve à jour. En contrepartie, chaque PR qui ajoute ou supprime un test doit embarquer `php bin/proof-refresh`, sinon `main` passe au rouge. La règle est documentée dans `docs/tests-locaux.md` mais pas encore dans la consigne de worker ni dans la checklist d'intégration.

### Thème 4 : vérification du rendu sans image locale (priorité basse)

Avec 6 à 7 Go libres, construire l'image de la démo aurait fait passer le disque sous le seuil de 5 Go. La vérification en production a fonctionné, mais elle arrive après le merge : un défaut d'affichage serait découvert une fois publié. pa11y a dû être installé dans un dossier jetable.

## Suivi des actions de la rétrospective du sprint 22

| # | Action | Résultat au sprint 23 |
|---|--------|------------------------|
| 1 | Contrôle des scénarios avant le done | ✅ Atteinte : 0 story en done avec un scénario non livré (1 au sprint 22) ; US-170 est restée en review jusqu'à sa preuve en production |
| 2 | TDD vérifiable malgré le squash | ✅ Atteinte : 3 stories de code sur 3 avec un commit rouge cité dans la PR #71 (0 au sprint 22) |
| 3 | Budget de ressources de la machine | ✅ Atteinte : 0 plantage ; minimum de 6,0 Go libres (environ 2,6 Go au sprint 22) ; MySQL de test arrêté après le dernier run, aucune image construite en local |
| 4 | Règle des refus dans la consigne de worker | ✅ Atteinte : 1 refus au sprint (suppression du projet jetable de w173), 0 contournement |
| 5 | Statuts des tâches | ✅ Atteinte : 40 tâches sur 40 en ✅ à la clôture (9 livrées restées 🔲 au sprint 22) ; tableau régénéré à partir des fichiers de tâches |
| 6 | Calendrier sur la disponibilité de Thibaut | ✅ Atteinte : sprint clos dès le périmètre vérifié, décisions des spikes prises le jour même |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 23 | Sprint 22 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 | 1 |
| Espace libre minimal | 5 Go | 6,0 Go | environ 2,6 Go |
| Tâches livrées restées 🔲 à la clôture | 0 | 0 | 9 |
| Contournements de refus d'outil | 0 | 0 (1 refus) | 0 (4 refus) |
| Push sans confirmation | 0 | 0 | 0 |
| Stories en done avec un scénario non livré | 0 | 0 | 1 |
| Stories de code avec commit rouge cité | 100 % | 3 sur 3 | 0 |
| Écarts « vert annoncé / rouge à l'intégration » | 0 | 0 (les échecs du run complet de w166 étaient la page d'US-170 en cours, signalés comme tels) | 0 |

## Actions (SMART)

### Action 1 : Lire sans filtrage avant de modifier
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à `project-management/templates/consigne-worker.md` une règle 9 : un fichier à modifier se lit avec l'outil Read ou `rtk proxy cat`, jamais avec `cat`, `sed` ou `head` filtrés par RTK ; le diff d'un refactoring est relu pour les commentaires supprimés. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 24 |
| DoD | Règle ajoutée ; 0 commentaire supprimé par erreur dans les refactorings du sprint 24 (2 au sprint 23). |
| Statut | ✅ Règle 9 ajoutée à `consigne-worker.md` au planning du sprint 24 (2026-10-23) ; l'indicateur se mesure au sprint 24. |
| Priorité | Haute |

### Action 2 : Pièges du shell documentés
| Attribut | Valeur |
|----------|--------|
| Description | Compléter la section « Pièges connus » de `docs/tests-locaux.md` : `rm` aliasé en `rm -i` (utiliser `/bin/rm`), `sed -E` sur macOS, sortie de test écrite dans un fichier puis filtrée, `catchExceptions(false)` pour faire remonter une erreur 500 dans un test. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 24 |
| DoD | Section complétée ; 0 commande de fond bloquée au sprint 24 (1 au sprint 23). |
| Statut | ✅ Section « Pièges connus » de `docs/tests-locaux.md` complétée au planning du sprint 24 (2026-10-23) ; l'indicateur se mesure au sprint 24. |
| Priorité | Moyenne |

### Action 3 : Rafraîchir la preuve dans la PR
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à `project-management/templates/checklist-integration.md` : « Si la PR ajoute ou supprime un fichier de tests, `php bin/proof-refresh` puis `php bin/proof-check` avant le push. » |
| Responsable | Orchestrateur (Claude) |
| Deadline | Première PR du sprint 24 qui touche aux tests |
| DoD | 0 build de `main` en échec à l'étape « Published proof is up to date » au sprint 24. |
| Statut | ✅ Ligne ajoutée à `checklist-integration.md` au planning du sprint 24 (2026-10-23) ; l'indicateur se mesure au sprint 24. |
| Priorité | Moyenne |

### Action 4 : Rendu vérifiable avant le merge
| Attribut | Valeur |
|----------|--------|
| Description | Pour une story d'interface, décider au planning où se vérifie le rendu (démo locale si au moins 10 Go libres, sinon production après déploiement, la story restant en review), et consigner la procédure pa11y et 360 px sur la production dans `docs/tests-locaux.md`. |
| Responsable | Orchestrateur (Claude), Thibaut (espace disque) |
| Deadline | Planning du sprint 24 |
| DoD | Chaque story d'interface du sprint 24 a son lieu de vérification écrit dans son fichier de tâches avant le développement. |
| Statut | ✅ Procédure « Vérifier le rendu en production » ajoutée à `docs/tests-locaux.md` au planning du sprint 24 (2026-10-23) ; le lieu de vérification de chaque story d'interface est écrit dans ses tâches. |
| Priorité | Basse |

## Points positifs à conserver

- 11 points sur 11 en done le jour du lancement, avec les deux décisions de Thibaut dans la foulée.
- Les six actions de la rétro du sprint 22 sont atteintes.
- Les tests rouges ont trouvé un vrai défaut avant le push.
- Aucun refus contourné, aucune publication sans GO, aucune connexion à la production.
