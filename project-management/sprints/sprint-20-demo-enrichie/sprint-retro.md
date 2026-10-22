# Rétrospective - Sprint 20

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-04-02 (après-midi) ; préparée le 2026-10-22 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint sur le périmètre engagé : les 6 stories (22 points) sont en review, avec 4 publications de plugins le même jour (company-insights 0.1.1, 0.2.0, 0.2.1 ; purchase-approval 1.2.0) et 1 786 tests verts. La production n'a rien reçu (Railway suspendu). Les difficultés viennent de l'environnement : disque descendu à 2 Go puis 4,7 Go (deux arrêts), caches périmés, migration manquante. Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer
- Le sacrifice décidé au planning (US-093) : 22 points engagés, 22 livrés en review.
- La revue de code avant le tag : 5 points majeurs de company-insights corrigés avant la 0.2.0.
- L'ordre de publication écrit dans les fiches : plugin, Packagist, puis démo.
- Les contrôles d'accessibilité sur les pages touchées (0 violation axe-core).

### Commencer
- Un budget d'espace disque par worker de plugin, avec relevé avant et après.
- Un gabarit de CI de plugin appliqué à chaque dépôt avant sa release (PHP 8.5 manquait à purchase-approval).
- Mettre à jour le statut des tâches de plugin dans le fichier de tâches au fil de l'eau (il est resté à 🔲 alors que les releases étaient publiées).
- Vérifier le mode démo et les migrations avant de montrer un serveur local à Thibaut.

### Arrêter
- Lancer plusieurs workers de plugins qui installent chacun leurs dépendances et leurs images quand le disque est sous 10 Go.
- Reporter les captures de README en fin de sprint.

### Plus de
- Revue de code des plugins avant le tag : elle a rattrapé des défauts de fond (agrégats sans plafond, N+1, montants hors taxes).

### Moins de
- Interruptions pour nettoyer le disque : deux arrêts du sprint.

## Thèmes et analyse

### Thème 1 : disque des workers de plugins (priorité haute)
Faits : disque descendu à 2 Go puis 4,7 Go ; deux arrêts du sprint ; nettoyage par Thibaut ; deux suppressions de cache refusées par l'outil ; le même thème a coûté deux captures au sprint 19.
5 Pourquoi :
1. Pourquoi le disque est-il tombé à 2 Go ? → vendor, caches et image Docker de chaque plugin s'ajoutent à ceux de la démo, et macOS utilise le swap.
2. Pourquoi s'accumulent-ils ? → chaque worker de plugin recrée son environnement et ne le supprime pas à la fin.
3. Pourquoi personne ne l'a vu venir ? → le relevé se fait au planning, pas avant chaque worker.
4. Pourquoi le nettoyage n'a-t-il pas été fait par l'équipe ? → les suppressions de cache sont refusées par l'outil, donc il revient à Thibaut.
Cause racine : aucun budget d'espace par worker de plugin ni de nettoyage prévu dans la tâche ; le garde-fou de la démo (`bin/check-disk`) ne couvre pas les dépôts de plugins.

### Thème 2 : CI des plugins hétérogène (priorité moyenne)
Faits : company-insights a PHP 8.5 et des actions épinglées depuis la 0.1.1 ; la matrice de purchase-approval n'a pas PHP 8.5 (écart T-TECH-08) ; gabarit non documenté.
Cause racine : le gabarit de CI n'est pas écrit, chaque dépôt est traité à part.

### Thème 3 : suivi des statuts (priorité moyenne)
Faits : les tâches de plugin étaient restées 🔲 dans les fiches alors que les versions étaient publiées ; T-TECH-04, 09, 10 et 11 sans statut clair.
Cause racine : la mise à jour des fiches n'est pas dans la DoD des tâches faites dans un autre dépôt.

### Thème 4 : validation des écarts (priorité moyenne)
Faits : trois écarts de fiche (US-113, US-108, US-099) et T-113-06 attendent Thibaut ; le sprint 21 monte à 41 points avec US-093.
Cause racine : les écarts sont découverts en cours de réalisation, la décision attend la revue.

### Thème 5 : déploiement bloqué (priorité basse, hors équipe)
Faits : Railway toujours suspendu (plans Hobby) ; aucune des PR #43 à #49 n'est en production ; les six stories restent en review.

## Actions (SMART)

### Action 1 : Budget d'espace disque des workers de plugins
| Attribut | Valeur |
|----------|--------|
| Description | Étendre le relevé de `bin/check-disk` aux dépôts de plugins : un worker de plugin ne démarre pas sous 10 Go libres sur la machine ; sa tâche finit par la suppression de son `vendor`, de ses caches et de son image Docker (budget de 3 Go par worker), avec relevé avant et après dans son rapport. Si l'outil refuse une suppression, il le signale à Thibaut sans arrêter le sprint. |
| Responsable | Worker devops (script), Scrum Master (consigne de worker) |
| Deadline | Planning du sprint 21 |
| DoD | Script et consigne en place ; 0 arrêt du sprint dû au disque et 0 capture reportée pour manque d'espace au sprint 21 (2 arrêts au sprint 20) ; espace libre jamais sous 5 Go. |
| Priorité | Haute |

### Action 2 : Gabarit de l'orchestrateur et statuts à jour
| Attribut | Valeur |
|----------|--------|
| Description | Finir T-TECH-04 : le gate recoupe les points avec la somme des stories du fichier de statut, un refus d'outil n'arrête jamais le sprint ; ajouter à la DoD d'une tâche faite dans un autre dépôt la mise à jour de sa ligne dans le fichier de tâches. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Début du sprint 21 |
| DoD | Gabarit mis à jour ; 0 écart de gate, 0 arrêt non demandé, 0 tâche livrée restée 🔲 à la clôture du sprint 21 (plus de 40 lignes à corriger au sprint 20). |
| Priorité | Moyenne |

### Action 3 : Gabarit de CI de plugin et PHP 8.5 pour purchase-approval
| Attribut | Valeur |
|----------|--------|
| Description | Documenter le gabarit (PHP 8.4 et 8.5, actions épinglées par SHA, `permissions` minimal) à partir de company-insights, l'appliquer à purchase-approval et publier ses captures de README (T-156-06). |
| Responsable | Worker devops |
| Deadline | Milieu du sprint 21 (point d'étape) |
| DoD | Gabarit dans `docs/` ; PR de purchase-approval avec CI verte sur PHP 8.5 ; captures dans le README ; 0 écart de matrice à la revue du prochain plugin. |
| Priorité | Moyenne |

### Action 4 : Valider les écarts et arbitrer le sprint 21
| Attribut | Valeur |
|----------|--------|
| Description | Thibaut valide ou corrige les écarts d'US-113, US-108 et US-099, décide du sort de T-113-06 et arbitre les 41 points du sprint 21 (US-093 incluse). |
| Responsable | Thibaut (décision), Scrum Master (préparation) |
| Deadline | Avant le planning du sprint 21 |
| DoD | Décisions consignées dans `sprint-status.yaml` et le sprint-goal du sprint 21 ; engagement du sprint 21 ne dépassant pas la vélocité de référence de 25 points sans sacrifice prévu d'avance. |
| Priorité | Haute |

### Action 5 : Déploiement et vérification en production
| Attribut | Valeur |
|----------|--------|
| Description | Dès la reprise de Railway, déployer d'un coup les PR #43 à #49 puis rejouer les contrôles du runbook et un contrôle par story des sprints 18 à 20. |
| Responsable | Thibaut (déploiement), Scrum Master (contrôles) |
| Deadline | 7 jours ouvrés après la reprise de la plateforme |
| DoD | Production à jour ; checks du runbook OK ; stories passées de `review` à `done`. |
| Priorité | Basse (hors équipe) |

## Suivi des actions de la rétrospective du sprint 19

| Action (S19) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Budget d'espace disque et captures tôt | Scrum Master | ❌ Non tenue | Disque à 2 Go puis 4,7 Go, 2 arrêts ; T-TECH-02 partielle ; captures de plugins faites pour company-insights (v0.2.1) mais pas pour purchase-approval (T-156-06) |
| 2. Ordre de publication dans les fiches | Scrum Master | ✅ Fait | Ordre plugin, Packagist, démo appliqué et vérifié par script ; aucune CI rouge due à l'ordre signalée |
| 3. Contrôle du gate et refus d'outil | Orchestrateur | ⚠️ Partielle | Gate de 22 points sans écart ; gabarit (T-TECH-04) non mis à jour ; deux refus d'outil signalés sans arrêt du sprint (les 2 arrêts viennent du disque) |
| 4. Gabarit de CI d'un nouveau plugin | Worker devops | ⚠️ Partielle | company-insights à jour en 0.1.1 ; gabarit non documenté ; purchase-approval sans PHP 8.5 |
| 5. Plan de déploiement de repli | Thibaut, Scrum Master | ⚠️ Partielle | Note au runbook faite (T-TECH-07) ; déploiement groupé impossible, Railway toujours suspendu |

Bilan : 1 action atteinte, 3 partielles, 1 non tenue.

### Bilan des indicateurs

Données partielles : valeurs tirées des rapports du sprint, aucun compteur tenu en continu.

| Indicateur | Cible | Sprint 20 | Source |
|------------|-------|-----------|--------|
| Tâches de captures reportées | 0 | 2 non faites (T-113-06, T-156-06) ; cause disque non confirmée | task-board |
| Arrêts du sprint dus au disque | 0 | 2 | journal de session |
| Écarts « vert annoncé / rouge à l'intégration » | 0 | 0 signalé | CI des PR |
| Erreurs de calcul du gate | 0 | 0 | gate de planning |
| Arrêts du sprint non demandés | 0 | 0 liés à un refus d'outil | journal de session |
| Workers morts sur incident externe | 0 | 0 signalé | journal de session |

## Points positifs à conserver

- 22 points sur 22 engagés en review, avec 4 versions de plugins publiées dans le sprint.
- Revue de code avant le tag : 5 points majeurs corrigés avant la 0.2.0.
- Suite complète de 1 786 tests sans échec, ECS et PHPStan verts, 0 violation axe-core sur les pages touchées.
- Écarts de fiche signalés plutôt que cachés, avec une décision demandée à Thibaut.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
