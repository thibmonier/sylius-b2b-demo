# Rétrospective - Sprint 19

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-03-19 (après-midi) ; préparée le 2026-10-22 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal partiellement atteint : les 5 stories engagées (25 points) sont en review, dont le plugin company-insights 0.1.0 publié sur Packagist ; US-121 et US-099 (8 points) ont été sacrifiées au planning. Les difficultés viennent de l'environnement (disque sous 7 Go, captures non faites), de l'orchestration (arrêt erroné du sprint, gate mal calculé) et de l'ordre de publication entre plugins. Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer
- Le sacrifice décidé au planning, selon l'ordre annoncé : le sprint tient sa capacité (25 points sur 25) sans mauvaise surprise.
- La revue de sécurité avant le merge : aucun bloquant, et un test fragile (horloge réelle) corrigé à la racine.
- Un seul worker à la fois et la reprise après la mort d'un worker sans perte de travail.

### Commencer
- Calculer le gate à partir du fichier de statut et le recouper avec la somme des points des stories.
- Planifier les captures et les contrôles de rendu avant la fin du sprint, tant qu'il reste de l'espace disque.
- Écrire l'ordre de publication des plugins dépendants dans la fiche de la story.
- Inscrire PHP 8.5 et le durcissement du workflow (SHA, `permissions`) dans le gabarit de création d'un nouveau plugin.

### Arrêter
- Laisser l'orchestrateur interpréter un refus d'outil comme une demande d'arrêt.
- Reporter les captures en fin de sprint quand le disque est chroniquement bas.

### Plus de
- Vérification des dépendances entre dépôts (versions publiées avant la CI du dépôt dépendant).

### Moins de
- Temps perdu à attendre de l'espace disque : captures et suite locale bloquées.

## Thèmes et analyse

### Thème 1 : disque chroniquement bas (priorité haute)
Faits : espace libre sous 7 Go pendant tout le sprint ; captures T-155-12/13 d'abord reportées (faites après la revue), suite locale impossible sous 5 Go, rendu réel et pa11y non rejoués.
5 Pourquoi :
1. Pourquoi les captures ont-elles été reportées ? → la démo locale ne peut pas être remise à zéro avec si peu d'espace.
2. Pourquoi si peu d'espace ? → images Docker, volumes et caches s'accumulent sur le même disque.
3. Pourquoi le nettoyage n'a-t-il pas suffi ? → il se fait en réaction, pas selon un budget d'espace.
Cause racine : pas de budget d'espace disque ni de nettoyage planifié ; les contrôles dépendants du disque sont placés en fin de sprint.

### Thème 2 : ordre de publication entre plugins (priorité haute)
Faits : la CI de company-accounts est restée rouge tant qu'admin-order-creation 1.1.0 n'était pas publié ; conflit `composer.lock` au rebase après US-147.
Cause racine : la fiche d'US-146 ne décrivait pas l'ordre de publication entre les deux dépôts.

### Thème 3 : fiabilité de l'orchestration (priorité moyenne)
Faits : gate calculé à 23 points au lieu de 25 ; arrêt du sprint après un appel d'outil refusé (« Je n'ai pas demandé d'arrêt ») ; un worker mort sur une erreur réseau.
Cause racine : le calcul du gate est manuel ; la consigne de l'orchestrateur ne dit pas quoi faire après un refus d'outil.

### Thème 4 : qualité d'un plugin neuf (priorité moyenne)
Faits : company-insights est publié sans PHP 8.5 dans la matrice CI, avec des actions non épinglées par SHA et sans bloc `permissions`.
Cause racine : aucun gabarit de CI pour un nouveau plugin ; la revue a repéré les points sans pouvoir les corriger avant la publication.

### Thème 5 : déploiement bloqué (priorité basse, hors équipe)
Faits : builds Railway suspendus, la production n'a reçu aucune des PR #43 à #47.
Cause racine : incident de la plateforme ; aucun moyen de déploiement de remplacement documenté.

## Actions (SMART)

### Action 1 : Budget d'espace disque et captures tôt dans le sprint
| Attribut | Valeur |
|----------|--------|
| Description | Au planning, relever l'espace libre de la machine et de la VM Docker ; planifier les tâches de captures et de rendu réel dans la première semaine ; nettoyer images et volumes inutilisés avant chaque tâche de captures. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 20 |
| DoD | Relevé noté dans le sprint-goal ; captures planifiées avant le jour 5 ; T-155-12/13 faites tôt ; 0 tâche de captures reportée pour manque d'espace au sprint 20 (2 au sprint 19). |
| Priorité | Haute |

### Action 2 : Ordre de publication dans les fiches de story
| Attribut | Valeur |
|----------|--------|
| Description | Toute story qui touche deux dépôts dépendants indique l'ordre de publication (tag puis Packagist puis bump du dépôt dépendant) et sa vérification avant d'ouvrir la PR du dépôt dépendant. |
| Responsable | Scrum Master (Claude) |
| Deadline | Affinage du sprint 20 |
| DoD | Rubrique ajoutée au gabarit de story et appliquée aux stories du sprint 20 ; 0 CI rouge due à un ordre de publication au sprint 20 (1 au sprint 19). |
| Priorité | Haute |

### Action 3 : Contrôle du gate et consigne après refus d'outil
| Attribut | Valeur |
|----------|--------|
| Description | Le gate recoupe les points avec la somme des stories du fichier de statut et affiche l'écart ; le gabarit de l'orchestrateur précise qu'un refus d'outil n'arrête jamais le sprint : il est signalé à Thibaut et le travail continue sur le reste. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Début du sprint 20 |
| DoD | Gabarit mis à jour ; 0 écart de calcul du gate et 0 arrêt non demandé au sprint 20 (1 et 1 au sprint 19). |
| Priorité | Moyenne |

### Action 4 : Gabarit de CI pour un nouveau plugin
| Attribut | Valeur |
|----------|--------|
| Description | Créer un gabarit de workflow : matrice PHP 8.4 et 8.5, actions épinglées par SHA, bloc `permissions` minimal ; l'appliquer à company-insights. |
| Responsable | Worker devops |
| Deadline | Fin du sprint 20 |
| DoD | PR sur company-insights avec CI verte sur PHP 8.5 ; gabarit documenté ; 0 point de ce type relevé à la revue du prochain plugin neuf. |
| Priorité | Moyenne |

### Action 5 : Plan de déploiement de repli
| Attribut | Valeur |
|----------|--------|
| Description | Documenter ce que Thibaut fait quand les builds Railway sont suspendus (vérification de l'état, ordre de déploiement des PR en attente) et déployer d'un coup les PR #43 à #47 à la reprise. |
| Responsable | Thibaut (déploiement), Scrum Master (note) |
| Deadline | 2027-03-26 (fin de la première semaine du sprint 20) |
| DoD | Note dans le runbook ; production à jour avec les PR #43 à #47 ; vérifications du runbook rejouées. |
| Priorité | Basse |

## Suivi des actions de la rétrospective du sprint 18

| Action (S18) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Surveiller le disque de la VM Docker | Worker devops | ⚠️ À vérifier | Le disque est resté chroniquement bas (sous 7 Go) ; DoD « 0 run invalidé par le disque » non confirmée, le run local a été bloqué sous 5 Go |
| 2. Lire la matrice CI au cadrage | Scrum Master | ⚠️ Partiel | Appliquée à US-146 ; mais la CI de company-accounts est restée rouge pour un ordre de publication (action 2) |
| 3. ECS et PHPStan avant chaque push | Orchestrateur | ✅ Fait | CI de la PR #47 verte ; aucun run rouge signalé sur ECS ou PHPStan |
| 4. Secrets fictifs non détectables | Scrum Master | ✅ Fait | Aucune alerte signalée sur les données de test |
| 5. Commandes pour Thibaut en script | Orchestrateur | ✅ Fait | Aucune commande collée en échec signalée |

Bilan : 3 actions atteintes, 2 partielles ou à vérifier.

### Bilan des indicateurs

Données partielles : valeurs tirées des rapports du sprint, aucun compteur tenu en continu.

| Indicateur | Cible | Sprint 19 | Source |
|------------|-------|-----------|--------|
| Tâches de captures ou de rendu reportées pour cause de disque | 0 | 2 (T-155-12 et T-155-13, faites après la revue) | task-board |
| Écarts « vert annoncé / rouge à l'intégration » | 0 | 1 (company-accounts, ordre de publication) | CI des PR |
| Erreurs de calcul du gate | 0 | 1 (23 au lieu de 25) | gate de planning |
| Arrêts du sprint non demandés | 0 | 1 | journal de session |
| Workers morts sur incident externe | 0 | 1 (reprise sans perte) | journal de session |

## Points positifs à conserver

- 25 points sur 25 engagés livrés en review, grâce au sacrifice décidé au planning.
- Un nouveau plugin public publié sur Packagist dans le sprint, avec revue de sécurité sans bloquant.
- Test d'historique rendu déterministe plutôt que relancé, et coût de la remise à zéro mesuré (6,09 s à 6,50 s).

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
