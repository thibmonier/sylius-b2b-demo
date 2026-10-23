# Rétrospective - Sprint 24

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-23, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 23, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers wA, wB, w175, w132, w130, w174) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : 19 points sur 19, dont US-175 entrée en cours de sprint après le GO de Thibaut. Trois pages pour les intégrateurs sont en production et vérifiées, la démo tourne sous PHPUnit 13 sans aucune erreur PHPStan ignorée, et les deux premiers packs sont sur Packagist.

Les écarts du sprint :
- le disque est passé deux fois sous 5 Go ;
- `git` est refusé dans un worktree isolé, ce qui a empêché de déléguer US-174 ;
- deux phrases de README préparées par un worker étaient inexactes, relevées avant publication ;
- l'orchestrateur a lancé une fois `phpunit` directement et commité un test avec son code.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- Les commits rouges seuls avant le code : 5 stories de code sur 5 ; le test rouge du workflow a fixé la cause de l'échec de la CI de la PR #76.
- La vérification en production en lecture seule : pa11y et les mesures à 360 et 1280 px ont tourné sur les pages publiques.
- La relecture par l'orchestrateur de ce qu'un worker publie : les références du guide (texte témoin) et les phrases des README ont été corrigées avant la sortie.
- Les décisions de Thibaut prises dans la journée, par questions groupées.

### Commencer

- Nettoyer les bases de test des workers après chaque lot, pas seulement quand `bin/run-tests` refuse.
- Vérifier, avant de planifier une délégation, qu'un worker isolé peut lancer les commandes dont il a besoin.

### Arrêter

- Lancer `phpunit` directement, même pour un test unitaire sans base.

### Plus de

- Comparaisons chiffrées avant et après une montée de version (nombre de tests, couverture) : elles ont montré qu'aucun cas de test n'était perdu.

### Moins de

- Phrases publiques rédigées à l'avance sans relire la source qu'elles décrivent.

## Thèmes et analyse

### Thème 1 : le disque, encore (priorité haute)

Chaque worker crée sa base de test dans le volume MySQL ; cinq bases cumulées et les journaux binaires ont fait passer le disque de 5,9 à 4,0 Go. La suppression des bases ne rend pas l'espace tout de suite à macOS. Le seuil de 5 Go a fait son travail (aucun plantage), mais il a bloqué la mesure locale.

### Thème 2 : délégation impossible dans un worktree isolé (priorité moyenne)

Le worker w174 n'a pu lancer aucune commande `git` dans son worktree. Il n'a rien contourné, et l'orchestrateur a repris la story avec l'accord de Thibaut. Le plan reposait sur une capacité non vérifiée.

### Thème 3 : textes publics préparés par un worker (priorité moyenne)

Les patchs de README annonçaient, pour deux plugins, un exemple que le guide ne montre pas. L'erreur venait d'une rédaction faite avant de relire la page cible.

### Thème 4 : discipline de test de l'orchestrateur (priorité basse)

Les workers ont suivi la règle des commits rouges seuls ; l'orchestrateur, en reprenant US-174 dans l'urgence, a lancé une fois `phpunit` directement et commité le test de `DataSets` avec son code.

## Suivi des actions de la rétrospective du sprint 23

| # | Action | Résultat au sprint 24 |
|---|--------|------------------------|
| 1 | Lire sans filtrage avant de modifier (règle 9) | 🟡 Non mesurable en totalité : aucun commentaire supprimé par erreur n'a été signalé, mais les diffs du sprint n'ont pas été relus systématiquement sur ce point |
| 2 | Pièges du shell documentés | ✅ Atteinte : 0 commande de fond bloquée |
| 3 | Rafraîchir la preuve dans la PR | ✅ Atteinte : 0 build de `main` en échec à l'étape de la preuve (#75 et #76 ont embarqué leur rafraîchissement) |
| 4 | Lieu de vérification du rendu écrit au planning | ✅ Atteinte : les trois stories d'interface l'avaient dans leurs tâches, vérifiées en production |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 24 | Sprint 23 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 | 0 |
| Espace libre minimal | 5 Go | **4,0 Go** | 6,0 Go |
| Tâches livrées restées 🔲 à la clôture | 0 | 0 (1 🚫 justifiée) | 0 |
| Contournements de refus d'outil | 0 | 0 (3 refus) | 0 (1 refus) |
| Push, tag ou dépôt sans confirmation | 0 | 0 | 0 |
| Stories en done avec un scénario non livré | 0 | 0 | 0 |
| Stories de code avec commit rouge cité | 100 % | 5 sur 5 | 3 sur 3 |
| Textes publics inexacts publiés | 0 | 0 (2 corrigés avant publication) | non mesuré |

## Actions (SMART)

### Action 1 : Nettoyer les bases de test après chaque lot
| Attribut | Valeur |
|----------|--------|
| Description | Après chaque lot de workers, l'orchestrateur lance `bin/clean-test-caches --all` (aucun run actif) et relève `df -h .` ; au-dessous de 6 Go libres, il prévient Thibaut avant le lot suivant. Ajouter la ligne à `checklist-integration.md`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 25 (checklist), puis chaque lot |
| DoD | Espace libre minimal au moins 5 Go au sprint 25 (4,0 Go au sprint 24). |
| Priorité | Haute |

### Action 2 : Vérifier les capacités d'un worker isolé avant de lui confier une story
| Attribut | Valeur |
|----------|--------|
| Description | Une story qui change de branche ou pousse (`git`) ne passe pas par un worktree isolé ; elle est séquencée dans l'arbre principal. La règle est écrite dans `tasks/README.md` du sprint et dans `consigne-worker.md`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 25 |
| DoD | 0 worker bloqué par un refus prévisible au sprint 25 (1 au sprint 24). |
| Priorité | Moyenne |

### Action 3 : Relire la source avant tout texte public
| Attribut | Valeur |
|----------|--------|
| Description | Un texte destiné à un dépôt public (README, release, page) est relu par l'orchestrateur contre la source qu'il décrit (fichier, page, registre) avant le commit ; la ligne est ajoutée à `checklist-integration.md`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 25 |
| DoD | 0 texte public inexact au sprint 25, publié ou corrigé en revue. |
| Priorité | Moyenne |

### Action 4 : Mêmes règles de test pour l'orchestrateur
| Attribut | Valeur |
|----------|--------|
| Description | L'orchestrateur applique la consigne de worker quand il code : tests uniquement par `bin/run-tests`, test rouge commité seul avant le code, y compris pour un assistant de test. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 25 |
| DoD | 0 exécution directe de `phpunit` et 100 % de commits rouges seuls au sprint 25 (1 écart de chaque au sprint 24). |
| Priorité | Basse |

## Points positifs à conserver

- 19 points sur 19 en done le jour du lancement, avec une story entrée en cours de route.
- Trois actions de la rétro du sprint 23 atteintes, la quatrième non mesurable en totalité.
- La montée à PHPUnit 13 sans aucune erreur ignorée, avec une preuve chiffrée qu'aucun test n'a été perdu.
- Aucun refus contourné, aucune publication sans GO.
