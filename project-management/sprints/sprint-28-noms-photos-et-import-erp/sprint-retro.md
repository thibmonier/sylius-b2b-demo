# Rétrospective - Sprint 28

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-25, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 27, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers w195, w196, w187) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint dans le code : **13 points livrés sur 13**, sans sacrifice. Vélocité : S24 19, S25 17, S26 29, S27 13, **S28 13** (moyenne des quatre précédents : 19,5). Les contrôles de production d'US-195 et d'US-196 restent ouverts jusqu'à la remise à zéro de 03:00 du 2026-10-26.

Les écarts du sprint :
- **un incident majeur sur la machine de développement** : une copie récursive de la racine du Mac a rempli le disque (voir thème 1) ;
- une CI rouge sur le plugin, faute d'avoir rejoué en local sa préparation ;
- l'espace libre sous 5 Go plusieurs fois ;
- des décisions mal attribuées dans des documents ;
- une PR fusionnée automatiquement alors qu'une question à Thibaut était ouverte.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- La planche, puis la mini-planche pour sept types, puis la vérification en pleine résolution de chaque photo : c'est elle qui a écarté les photos pixellisées et la botte d'enfant avant qu'elles n'arrivent au catalogue.
- Les décisions rapides de Thibaut (`mpn`, choix des photos, pas de retouche, visuels neutres, GO du plugin).
- La publication propre du plugin : CI en matrice verte avant le tag, release GitHub, Packagist à jour, GO demandé avant chaque push, tag et release.
- Une PR par story, avec le commit de tests rouges cité.
- La question « que devient cet état après un déploiement ? » (règle 16) : elle a fait annoncer d'emblée que noms et photos n'apparaîtraient qu'après la remise à zéro.

### Commencer

- Une garde sur toute commande qui copie, déplace ou supprime à partir d'une variable ou d'un glob (thème 1).
- Rejouer la séquence exacte de la CI d'un dépôt avant de rendre la main (thème 2).
- Un registre des décisions qui nomme leur auteur (thème 4).
- Vérifier qu'aucune question à Thibaut n'est ouverte sur une PR avant de la fusionner (thème 5).

### Arrêter

- Donner un feu vert sur une commande destructive ou récursive sans exiger la garde.
- Réinstaller sans y penser le `vendor` du plugin (459 Mo) quand l'espace libre est bas.

### Plus de

- Sources vérifiées avant d'attribuer une réserve ou une décision à quelqu'un.

### Moins de

- Dépendance au nettoyage manuel du disque par Thibaut.

## Thèmes et analyse

### Thème 1 : l'incident du 2026-10-25, copie de la racine du Mac (priorité haute)

Vers 09:08, un worker a lancé à la main `cp -Rn "$D"/. public/media/image/` avec `$D` vide (un glob sans résultat). Le chemin est devenu `/.`, et la commande a copié la racine du Mac (`/Library`, `/usr`, …, **6,4 Go**) dans `public/media/image`, jusqu'au disque plein. OrbStack et MySQL se sont arrêtés. Les originaux sont intacts (vérifiés) ; rien n'a été commité (le dossier est ignoré par git) ; Thibaut a supprimé les copies.

Cause directe : une variable non vérifiée dans une commande récursive. Cause de fond : l'orchestrateur avait donné le feu vert à cette commande sans exiger de garde, et rien dans la consigne des workers n'interdisait ce geste. Le code livré en a tiré la leçon (le script d'import d'US-196 refuse une source vide ou la racine du disque avant toute écriture, et un test statique interdit copie, renommage et `exec` dans le code d'import), mais les commandes lancées à la main restaient hors de cette protection.

**Nouvelle règle** : jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans avoir vérifié qu'il est non vide et situé sous le dossier attendu (`set -u`, `[ -n "$D" ] && [ -d "$D" ]`). Les commandes de nettoyage données à Thibaut sont écrites en chemins absolus, précédées d'un `du -sh`.

### Thème 2 : CI du plugin rouge faute de reproduire sa préparation (priorité haute)

Le premier push de la PR #2 du plugin a donné une CI rouge : les tests d'intégration n'étaient pas isolés des fixtures Sylius que la CI charge, ce que la préparation locale ne reproduisait pas. Corrigé par `3485a05`, matrice verte avant le tag. Même famille que les « CI rouges après tests ciblés verts » des sprints 25 et 26.

### Thème 3 : disque (priorité haute)

L'espace libre est repassé sous 5 Go plusieurs fois : `vendor` du plugin (459 Mo) réinstallé, caches. L'incident du thème 1 a fini par remplir le disque. L'action 1 de la rétro du sprint 27 (un seul cache de rendu, seuil disque dans `bin/render-page`) n'a pas été faite (T-TECH-28-01 reportée).

### Thème 4 : décisions mal attribuées (priorité moyenne)

Un agent a attribué à Thibaut une réserve qu'il n'avait pas formulée (le ruban), et un worker a écrit « accepté par Thibaut » pour une décision de l'orchestrateur. Les documents publics et les journaux de sprint s'appuient sur ces mentions : une attribution fausse est un faux dans l'historique.

### Thème 5 : fusion automatique avec une question ouverte (priorité moyenne)

La PR #106 a été fusionnée par l'enchaînement automatique avant la réponse de Thibaut à une question ouverte (botte en caoutchouc). La réponse a été traitée dans #107, sans dommage, mais l'ordre aurait pu coûter une photo non conforme au catalogue.

### Thème 6 : actions de la rétro du sprint 27 (priorité moyenne)

Trois actions sur cinq ne sont pas faites (1, 2 et 5). L'action 2 (`bin/proof-rebase`) n'existe pas : aucun conflit de `proof.json` n'a été signalé dans les sources consultées, mais le comptage n'a pas été fait. Le sprint à 13 points aurait laissé la place de les faire ; elles ont été repoussées derrière les stories.

## Suivi des actions de la rétrospective du sprint 27

| # | Action | Résultat au sprint 28 |
|---|--------|------------------------|
| 1 | Un seul cache de rendu et seuil disque pour les rendus | 🔲 Non faite (T-TECH-28-01), reportée au sprint 29 ; espace libre sous 5 Go plusieurs fois |
| 2 | `bin/proof-rebase` | 🔲 Non faite (T-TECH-28-02), reportée au sprint 29 ; aucun conflit de `proof.json` relevé sur les PR fusionnées en série |
| 3 | « Que devient cet état après un déploiement ? » dans la checklist | ✅ Règle 16 écrite au planning et appliquée : le contrôle de production d'US-195 et d'US-196 est planifié après la remise à zéro ; 0 défaut visible seulement en production à ce stade |
| 4 | Numéro de story relu avant chaque commit | ✅ Règle 13 complétée au planning ; aucun commit au mauvais numéro signalé dans les sources consultées (non recompté) |
| 5 | LCP de la home sous contrôle | 🔲 Mesure non consignée (T-TECH-28-05), reportée ; à refaire aussi après US-196 |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 28 | Sprint 27 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | **1 incident** (disque plein, OrbStack et MySQL arrêtés) | 0 |
| Espace libre minimal | 5 Go | **sous 5 Go plusieurs fois, disque plein le 2026-10-25** | 3 à 4 Go |
| Suite complète lancée sous le seuil | 0 | 0 relevé | 0 relevé |
| CI rouges après tests ciblés verts | 0 | **1** (PR #2 du plugin) | 0 |
| Commit au mauvais numéro de story | 0 | 0 relevé | 2 |
| Tâches de contrôle en production ouvertes à la clôture | 0 | **3** (T-195-06, T-196-09, T-196-10), imposées par la remise à zéro du 2026-10-26 | 0 |
| Défauts trouvés en production après merge | 0 | 0 relevé à ce stade | 1 |
| Commande récursive sur un chemin non vérifié | 0 | **1** | n/a |
| Push, tag ou dépôt sans confirmation | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 : Garde sur les commandes récursives et destructives
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à `consigne-worker.md` et à `checklist-integration.md` : aucune commande `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans `set -u` et sans `[ -n "$D" ] && [ -d "$D" ]` ; le chemin est aussi vérifié sous le dossier attendu ; l'orchestrateur n'approuve pas une telle commande sans cette garde ; les commandes de nettoyage destinées à Thibaut sont en chemins absolus, précédées d'un `du -sh`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le premier lot du sprint 29 |
| DoD | La règle est dans la consigne et la checklist ; 0 commande récursive non gardée au sprint 29. |
| Priorité | Haute |

### Action 2 : Rejouer la séquence exacte de la CI avant de rendre la main
| Attribut | Valeur |
|----------|--------|
| Description | Pour tout dépôt qui a une CI (le plugin d'abord), documenter dans la consigne la séquence de préparation de la CI (installation, fixtures, base) et la rejouer telle quelle en local avant le push ; citer la dernière ligne de sortie. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain push d'un plugin |
| DoD | 0 CI rouge après des contrôles locaux verts au sprint 29. |
| Priorité | Haute |

### Action 3 : Registre des décisions avec leur auteur
| Attribut | Valeur |
|----------|--------|
| Description | Tenir dans le dossier du sprint un registre des décisions (date, décision, auteur : Thibaut, orchestrateur ou worker) ; une phrase « accepté par Thibaut » n'est écrite que si elle y figure. Reprend T-TECH-28-09 (registre des GO). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 29 |
| DoD | 0 décision mal attribuée relevée à la revue du sprint 29. |
| Priorité | Moyenne |

### Action 4 : Pas de fusion automatique avec une question ouverte
| Attribut | Valeur |
|----------|--------|
| Description | Avant tout enchaînement automatique de fusion, vérifier qu'aucune question à Thibaut n'est en suspens sur la PR (commentaire ou description) ; sinon la PR reste ouverte. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 29 |
| DoD | 0 PR fusionnée avec une question ouverte au sprint 29. |
| Priorité | Moyenne |

### Action 5 : Reprendre les actions 1, 2 et 5 de la rétro du sprint 27 en tête du sprint 29
| Attribut | Valeur |
|----------|--------|
| Description | T-TECH-28-01 (un seul cache de rendu et seuil disque dans `bin/render-page`), T-TECH-28-02 (`bin/proof-rebase`) et T-TECH-28-05 (LCP de la home et du catalogue, avant et après US-196) passent au sprint 29 avant les stories ; vérifier le `vendor` du plugin avant de le réinstaller quand l'espace libre est sous 6 Go. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Premier lot du sprint 29 |
| DoD | Espace libre minimal d'au moins 5 Go au sprint 29 ; LCP de la home à 1456 ms au plus, ou cause chiffrée. |
| Priorité | Haute |

## Intégration au sprint 29

Les actions 1 à 5 sont à reprendre au planning du sprint 29 avec [US-197](../../backlog/user-stories/US-197.md) (photos du catalogue : cadrage des cartes et variantes de couleur, 3 points, à confirmer), candidate sortie des réserves d'US-196.

## Points positifs à conserver

- 13 points sur 13, sans sacrifice, dont un plugin publié proprement (`v0.2.0`) et branché sur la démo le même jour.
- Un catalogue sans marque réelle, gardé par un test, et des photos libres vérifiées une à une en pleine résolution.
- Des décisions de Thibaut rapides, qui n'ont jamais bloqué le sprint.
- Aucun original perdu et rien de commité lors de l'incident du disque : le dossier ignoré par git et les vérifications ont limité les dégâts.
- Aucune publication sans GO.
