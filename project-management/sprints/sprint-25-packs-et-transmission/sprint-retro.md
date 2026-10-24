# Rétrospective - Sprint 25

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-24, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 24, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers wP, wD, wE) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : 17 points sur 17. Les packs s'installent réellement en CI, avec et sans recette Flex ; la page « Installer un pack » est en production ; le plugin de transmission est publié en v0.1.0 ; la PR sur `symfony/recipes-contrib` est ouverte.

Les écarts du sprint :
- un débordement de la page des packs publié, trouvé par la vérification en production et corrigé le jour même ;
- un dossier de recette à la mauvaise version, relevé avant la publication ;
- l'espace libre tombé à 3,9 Go, sous la cible de 5 Go ;
- un worker bloqué par le refus d'écrire les `.env`, et un second MySQL démarré.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- La vérification en production en lecture seule : elle a trouvé le seul défaut publié du sprint, que ni les tests ni pa11y ne voyaient.
- La relecture par l'orchestrateur de ce qu'un worker prépare pour un dépôt public : version du dossier de recette et texte de la PR corrigés avant publication.
- Les preuves négatives sur branches jetables : chaque échec nomme le plugin et l'étape.
- Les commits rouges seuls, y compris pour le correctif de l'orchestrateur, vu rouge en CI quand le disque interdisait le run local.

### Commencer

- Tester les pages à tableau avec les valeurs réelles les plus longues, pas seulement avec des données de test courtes.
- Vérifier la concordance des versions (dossier de recette, tag, version publiée) avant toute publication.
- Lister à Thibaut, à la fin de chaque lot, les restes qu'un refus d'outil a empêché de supprimer.

### Arrêter

- Laisser un worker monter son propre serveur de base de données dans un nouveau dépôt.

### Plus de

- Installations réelles en CI : elles ont trouvé deux défauts de b2b-mcp qu'aucun test du plugin ne voyait.

### Moins de

- Pushes successifs sur une PR dont la CI tourne : plusieurs runs annulés sur #79.

## Thèmes et analyse

### Thème 1 : l'espace disque, toujours (priorité haute)

La cible de 5 Go n'est pas tenue (3,9 Go, 4,0 Go au sprint 24). Un nouveau dépôt de plugin coûte environ 400 Mo de `vendor/` plus un MySQL ; les refus d'outil empêchent les workers de nettoyer eux-mêmes. Le nettoyage des bases de test après chaque lot (action 1 du sprint 24) a été fait, mais il ne couvre ni les `vendor/` ni les essais hors de la démo.

### Thème 2 : un défaut d'affichage passé jusqu'en production (priorité moyenne)

Les tests fonctionnels lisaient le HTML et pa11y ne mesure pas la largeur. Le débordement ne venait que des noms de classes réels, longs, absents des données de test. La vérification en production a fonctionné comme prévu par l'action 4 du sprint 23, mais après le merge.

### Thème 3 : versions des artefacts publics (priorité moyenne)

La recette préparée visait `1.0` alors que les packs sont publiés en 0.1.0. Elle aurait été acceptée et ne se serait jamais appliquée. La relecture contre la source (action 3 du sprint 24) l'a trouvé, mais aucune ligne de checklist ne demandait cette concordance.

### Thème 4 : squelette d'un nouveau plugin (priorité basse)

Le squelette modèle contient des fichiers `.env*`, que le hook du projet refuse d'écrire, et un `compose` avec son propre MySQL. wE a dû improviser une configuration sans `.env` et a démarré un second serveur.

## Suivi des actions de la rétrospective du sprint 24

| # | Action | Résultat au sprint 25 |
|---|--------|------------------------|
| 1 | Nettoyer les bases de test après chaque lot | 🟡 Faite (nettoyage après le lot, relevé du disque) mais DoD non atteinte : 3,9 Go au minimum pour une cible de 5 Go |
| 2 | Pas de `git` dans un worktree isolé | ✅ Atteinte : 0 worker bloqué par `git` ; 1 worker bloqué par un autre refus prévisible (`.env`), voir action 4 |
| 3 | Relire la source avant tout texte public | ✅ Atteinte : 0 texte public inexact publié ; 2 corrections avant publication (version de recette, texte de la PR) |
| 4 | Mêmes règles de test pour l'orchestrateur | ✅ Atteinte : 0 exécution directe de `phpunit`, commit rouge seul pour #80 |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 25 | Sprint 24 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 | 0 |
| Espace libre minimal | 5 Go | **3,9 Go** | 4,0 Go |
| Tâches livrées restées 🔲 à la clôture | 0 | 0 (1 📅 Thibaut) | 0 |
| Contournements de refus d'outil | 0 | 0 (5 refus) | 0 (3 refus) |
| Push, tag ou dépôt sans confirmation | 0 | 0 | 0 |
| Stories en done avec un scénario non livré | 0 | 0 | 0 |
| Stories de code avec commit rouge cité | 100 % | 4 sur 4 | 5 sur 5 |
| Textes publics inexacts publiés | 0 | 0 (2 corrigés avant publication) | 0 |
| Défauts d'affichage trouvés en production | 0 | 1 | 0 |

## Actions (SMART)

### Action 1 : Budget disque d'un nouveau dépôt et restes listés
| Attribut | Valeur |
|----------|--------|
| Description | Au planning, toute story qui crée ou installe un dépôt hors de la démo porte une ligne de budget disque (vendor, base, essais). À la fin de chaque lot, l'orchestrateur relève `df` et envoie à Thibaut la liste des restes qu'un refus a empêché de supprimer, avec la commande à lancer ; au-dessous de 6 Go, aucun nouveau lot avant ce nettoyage. Ligne ajoutée à `checklist-integration.md`. |
| Responsable | Orchestrateur (Claude), Thibaut (suppressions) |
| Deadline | Planning du sprint 26, puis chaque lot |
| DoD | Espace libre minimal d'au moins 5 Go au sprint 26 (3,9 Go au sprint 25). |
| Priorité | Haute |

### Action 2 : Garde sur les `code` dans les tableaux
| Attribut | Valeur |
|----------|--------|
| Description | Test d'architecture qui parcourt `templates/demo/` : toute page dont un tableau contient du `code` doit déclarer `overflow-wrap: anywhere` pour ces `code`, hors media query. Story de 1 point au backlog. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 26 |
| DoD | Garde en place ; 0 défaut d'affichage trouvé en production au sprint 26 (1 au sprint 25). |
| Priorité | Moyenne |

### Action 3 : Concordance des versions avant publication
| Attribut | Valeur |
|----------|--------|
| Description | Ligne ajoutée à `checklist-integration.md` : avant un tag, une release ou une PR publique, comparer la version visée (dossier de recette, tag, contrainte) aux versions publiées sur Packagist. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 26 |
| DoD | 0 artefact publié à une version qui ne concorde pas au sprint 26. |
| Priorité | Moyenne |

### Action 4 : Consigne pour un nouveau dépôt de plugin
| Attribut | Valeur |
|----------|--------|
| Description | Règle 12 de `consigne-worker.md` : un nouveau plugin se configure sans fichier `.env*` (variables dans `phpunit.xml.dist` et dans l'environnement de la CI) et utilise le MySQL de la démo, avec une base à son nom supprimée à la fin ; jamais de second serveur. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 26 |
| DoD | 0 worker bloqué par un refus prévisible et 0 second serveur de base au sprint 26 (1 et 1 au sprint 25). |
| Priorité | Basse |

## Points positifs à conserver

- 17 points sur 17 le jour du lancement, avec trois dépôts publics touchés et une PR sur un dépôt de Symfony.
- La seule erreur publiée trouvée par nos propres contrôles et corrigée le jour même, test rouge compris.
- Trois actions de la rétro du sprint 24 atteintes, la quatrième faite mais sans atteindre sa cible.
- Aucun refus contourné, aucune publication sans GO.
