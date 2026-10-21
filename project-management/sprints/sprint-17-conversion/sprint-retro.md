# Rétrospective - Sprint 17

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-02-19 (après-midi) ; préparée le 2026-10-21 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code et rédaction, avec réserves : 10 stories (38 pts) en review, sans sacrifice. Parcours guidés, page « Intégrer » et mesure par campagne sont livrés ; la séquence PunchOut du kit média n'est pas captée (retour HTTPS exigé, démo locale en HTTP). Les difficultés viennent de l'environnement d'exécution (disque, parallélisme des workers) et de la vérification croisée, pas du code livré. Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer
- Le verrou de `bin/run-tests` (action 1 du S16) : 0 suite simultanée invalide, contre 2 au sprint 16.
- La suite en ordre aléatoire, verte sur trois graines.
- La recherche de défauts réels au rendu : UTM supprimées par `data-exclude-search` d'Umami, débordement mobile de la barre de démo.
- Le garde-fou qui a refusé le proxy TLS local : la contrainte a été documentée (repli de Thibaut) au lieu d'être contournée.

### Commencer
- Un sous-dossier de scratchpad par worker.
- Une vérification croisée indépendante de PHPStan, ECS et lint avant de déclarer « vert » à l'intégration.
- Un contrôle de l'espace disque de la machine avant le lancement des workers, avec alerte à 15 Go.
- Un cadrage préalable du HTTPS pour PunchOut (démo locale), avant de promettre la captation.
- Un test explicite d'ordre (`--order-by=random`) sur les commandes qui touchent aux données de démo (`DemoResetCommandTest`).

### Arrêter
- Se fier au « vert » annoncé par un worker sans le rejouer à l'intégration.
- Lancer `bin/clean-test-caches` pendant que d'autres workers testent.
- Chasser des échecs signalés non reproduits avant d'avoir relancé la suite seule.
- Laisser un worker attendre la fin d'un run sans condition de sortie.

### Plus de
- Livrables de communication versionnés ou au moins référencés (chemin, empreinte) pour que la revue les retrouve.

### Moins de
- Heures d'attente bloquée par le disque.
- Fichiers de travail partagés entre workers.

## Thèmes et analyse

### Thème 1 : disque saturé, tests bloqués (priorité haute)
Faits : disque sous le seuil de 5 Go, tests bloqués plusieurs heures ; espace libéré par Thibaut.
5 Pourquoi :
1. Pourquoi bloqués ? → la règle de gel disque interdit de lancer sous 5 Go.
2. Pourquoi sous 5 Go ? → le disque de la machine était à 98 %.
3. Pourquoi 98 % ? → l'occupation venait de la machine (hors projet), pas des caches du projet.
4. Pourquoi découvert tard ? → le seuil n'est mesuré qu'à l'exécution, et les nettoyages du projet ont d'abord été tentés.
Cause racine : aucune alerte précoce sur l'espace de la machine, ni diagnostic distinguant « projet » et « machine ».

### Thème 2 : parallélisme des workers sans isolation (priorité haute)
Faits : scratchpad partagé (fichiers écrasés) ; `bin/clean-test-caches` supprime les bases de tous les workers, en conflit avec la règle « clean avant chaque lot » ; un worker bloqué sur un run déjà terminé, arrêté et remplacé.
Causes racines : ressources communes (dossier, bases) sans propriétaire par worker ; condition d'attente fondée sur l'observation d'un run plutôt que sur un état lisible (le verrou existe, l'attente de fin du worker ne l'utilisait pas) ; action 3 du S16 (arrêt explicite) toujours à l'état de consigne non intégrée au gabarit.

### Thème 3 : confiance sans vérification croisée (priorité haute)
Faits : PHPStan rouge à l'intégration (trait `RestoresDemoCarts`) alors que les workers le disaient vert ; deux smoke tests signalés en échec, non reproduits, alors que la vraie dépendance d'ordre était dans `DemoResetCommandTest`.
5 Pourquoi :
1. Pourquoi rouge à l'intégration ? → le trait était vérifié dans un état partiel de l'arbre.
2. Pourquoi cru vert ? → le rapport du worker n'indiquait ni la commande ni la sortie.
3. Pourquoi aucun rejeu ? → aucune étape d'intégration ne l'exige.
Cause racine : rapport de fin sans preuve rejouable et sans passe de contrôle obligatoire.

### Thème 4 : contraintes d'environnement et périmètre public (priorité moyenne)
Faits : PunchOut exige HTTPS, démo locale en HTTP, tentative de proxy TLS local refusée par le garde-fou ; livrables de communication hors dépôt public (choix voulu, mais non traçables par la revue).
Cause racine : la contrainte HTTPS n'a pas été vue au cadrage de US-120 ; le hors-dépôt n'a pas de registre de suivi.

## Actions (SMART)

### Action 1 : Contrôle et diagnostic de l'espace disque avant les lots
| Attribut | Valeur |
|----------|--------|
| Description | `bin/run-tests` (ou un script `bin/check-disk`) mesure l'espace libre de la machine, affiche si le manque vient du projet (caches, volumes Docker) ou de la machine, et alerte à 15 Go. Consigne : prévenir Thibaut dès l'alerte. |
| Responsable | Worker devops |
| Deadline | Planning du sprint 18 |
| DoD | Script livré avec test ; message distinguant projet et machine ; 0 blocage de tests de plus de 30 min pour cause de disque au sprint 18 (plusieurs heures au sprint 17). |
| Priorité | Haute |

### Action 2 : Isoler les workers parallèles (scratchpad, bases, nettoyage)
| Attribut | Valeur |
|----------|--------|
| Description | Gabarit de consigne : un sous-dossier de scratchpad par worker. `bin/clean-test-caches` ne supprime que les bases du worker appelant (préfixe par worker) ; le nettoyage global est réservé à l'orchestrateur, hors lots. Règle « clean avant chaque lot » reformulée en conséquence. |
| Responsable | Worker devops (script) + Scrum Master (gabarit) |
| Deadline | Planning du sprint 18 |
| DoD | Test : un clean lancé par le worker A ne supprime pas la base du worker B ; gabarit mis à jour ; 0 fichier de travail écrasé et 0 base supprimée par un autre worker au sprint 18 (2 incidents au sprint 17). |
| Priorité | Haute |

### Action 3 : Vérification croisée obligatoire à l'intégration
| Attribut | Valeur |
|----------|--------|
| Description | Le rapport de fin cite la commande et la dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite. L'orchestrateur rejoue PHPStan et ECS sur l'arbre intégré avant de déclarer « vert », et rejoue seule toute suite signalée en échec avant d'ouvrir une enquête. |
| Responsable | Scrum Master (Claude) |
| Deadline | Début du sprint 18 |
| DoD | Étape ajoutée à la checklist d'intégration et au gabarit de rapport ; 0 écart « vert annoncé / rouge à l'intégration » au sprint 18 (1 au sprint 17). |
| Priorité | Haute |

### Action 4 : Attente de fin fondée sur le verrou, avec délai maximal
| Attribut | Valeur |
|----------|--------|
| Description | Fusionne le reste de l'action 3 du S16 (T-TECH-06). Tout worker qui attend un run lit le verrou de `bin/run-tests` et sort après un délai fixé (30 min) avec un rapport ; l'orchestrateur arrête (TaskStop) puis vérifie l'arrêt avant toute relance. |
| Responsable | Orchestrateur (consigne) + Worker devops (option `--wait` avec délai) |
| Deadline | Planning du sprint 18 |
| DoD | T-TECH-06 fermée ; test de `--wait` avec délai dépassé ; 0 worker bloqué sur un run terminé au sprint 18 (1 au sprint 17). |
| Priorité | Moyenne |

### Action 5 : Cadrer les contraintes d'environnement et tracer le hors-dépôt
| Attribut | Valeur |
|----------|--------|
| Description | Au cadrage de toute story de captation ou d'intégration externe, lister les prérequis d'environnement (HTTPS, comptes, outils) avant l'engagement. Tenir dans le dépôt un registre minimal (`communication/INDEX` : nom, date, statut, sans contenu) des livrables hors dépôt public. |
| Responsable | Scrum Master (Claude) ; Thibaut valide le format du registre |
| Deadline | Planning du sprint 18 (points d'entrée des sprints 18 et 19) |
| DoD | Rubrique « prérequis d'environnement » dans le gabarit de story ; registre présent avec les livrables du sprint 17 ; 0 story dont un prérequis d'environnement est découvert en cours de sprint (1 au sprint 17 : HTTPS de PunchOut). |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 16

| Action (S16) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Verrou dans `bin/run-tests` | Worker devops | ✅ Fait | Livré (T-TECH-01 et 02) ; 0 suite simultanée invalide ; l'attente de fin n'en tire pas encore parti (action 4) |
| 2. Isoler les tests sur comptes de démo partagés | Scrum Master + développeur fixtures | ⚠️ Partiel | Règle écrite, tests isolés, suite verte sur trois graines aléatoires ; une dépendance d'ordre subsistait dans `DemoResetCommandTest` |
| 3. Arrêt explicite des tâches de fond avant relance | Scrum Master | ❌ Non fait | T-TECH-06 toujours à faire ; un worker est resté bloqué (reprise en action 4) |
| 4. Reconstruire l'image après correctif de revue | Scrum Master | ❓ À confirmer | T-TECH-07 et 08 marquées à faire dans `technical-tasks.md` ; à vérifier avant la séance |

Bilan : 1 action atteinte, 1 partielle, 1 non tenue, 1 à confirmer.

## Points positifs à conserver

- Verrou de `bin/run-tests` livré et efficace.
- Suite aléatoire verte sur trois graines.
- Deux défauts réels trouvés avant publication : UTM supprimées par Umami, débordement mobile.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
