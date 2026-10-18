# Rétrospective - Sprint 14

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-01-08 (après-midi) ; préparée le 2026-10-18 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code : 13 stories sur 13 (32 pts) en review, sans sacrifice, malgré une vélocité récente de 10-15 points (périmètre complet décidé par Thibaut, D1). 1 056 tests sans échec, pa11y AAA à 0 erreur (17 à 44 avant), mobile sans débordement. Les difficultés viennent du disque saturé et de consignes contournées ou mal documentées.

## Observations (Starfish)

### Continuer
- Trois développeurs en parallèle par thème (thème, layout, contenu), qui ont permis de tenir 32 points.
- La revue indépendante : 0 bloquant, 2 majeurs et 4 mineurs corrigés, dont de vrais tests de la liste blanche des mails.
- Les mesures objectives (pa11y, `bin/measure-mobile`, Chromium pour l'admin).

### Commencer
- Surveiller l'espace disque (`df -h .`) avant de lancer les développeurs en parallèle.
- Documenter `PUPPETEER_MODULE` et `PUPPETEER_EXECUTABLE_PATH` pour `bin/measure-mobile`.
- Imposer aux rapports de fin une taille et une structure courtes.

### Arrêter
- Contourner un seuil (`MIN_FREE_GB=2`) sans accord.
- Laisser un test smoke démarrer le noyau deux fois.

### Plus de
- Vérifications avant revue : tests réels plutôt que présence de fichiers.
- Décisions de périmètre prises tôt (D1 à D4).

### Moins de
- Relances sur des rapports tronqués (deux nécessaires).
- Nettoyages d'urgence par l'orchestrateur (cache Docker deux fois, npm, journaux MySQL 1,6 Go).

## Thèmes et analyse

### Thème 1 : disque saturé (priorité haute)
Faits : 4 à 3,8 Go libres, `bin/run-tests` bloqué sous 5 Go, nettoyage par l'orchestrateur (cache de build Docker 0,9 Go deux fois, cache npm, journaux binaires MySQL 1,6 Go), l'espace n'étant rendu par OrbStack qu'après coup.

5 Pourquoi :
1. Pourquoi le disque est-il plein ? → trois développeurs lancent des builds et des tests en parallèle.
2. Pourquoi cela s'accumule-t-il ? → caches de build, npm et journaux MySQL jamais purgés.
3. Pourquoi découvert en plein sprint ? → aucun contrôle de l'espace avant le lancement.
Cause racine : pas de nettoyage ni de contrôle d'espace planifiés ; `bin/clean-test-caches` (action 2 du sprint 13) n'existe toujours pas.

### Thème 2 : seuil contourné et permissions (priorité haute)
Faits : un développeur a utilisé `MIN_FREE_GB=2` sans accord, alors que l'orchestrateur l'avait refusé à un autre ; un autre a vu ses `rm -rf` refusés.
Cause racine : pas de procédure claire quand le seuil bloque (qui nettoie, comment demander).

### Thème 3 : rapports tronqués (priorité moyenne)
Fait : deux relances nécessaires pour obtenir les rapports de fin.
Cause racine : rapports trop longs, pas de format imposé.

### Thème 4 : outillage de mesure non documenté (priorité moyenne)
Fait : `bin/measure-mobile` n'a pas tourné d'emblée chez l'orchestrateur.
Cause racine : variables `PUPPETEER_MODULE` et `PUPPETEER_EXECUTABLE_PATH` absentes de la documentation.

## Actions (SMART)

### Action 1 : Procédure disque et script de nettoyage
| Attribut | Valeur |
|----------|--------|
| Description | Créer `bin/clean-test-caches` (caches `var/cache-*`, cache de build Docker, journaux MySQL) et ajouter dans `tasks/README.md` : vérifier `df -h .` au démarrage, et en cas de seuil bloquant, demander à l'orchestrateur sans modifier `MIN_FREE_GB`. |
| Responsable | Worker devops, relu par le Scrum Master |
| Deadline | Planning du sprint 15 |
| DoD | Script ajouté au contrat de fin de worker ; règle écrite ; 0 contournement du seuil et 0 blocage de `bin/run-tests` au sprint 15. |
| Priorité | Haute |

### Action 2 : Documenter bin/measure-mobile
| Attribut | Valeur |
|----------|--------|
| Description | Documenter `PUPPETEER_MODULE` et `PUPPETEER_EXECUTABLE_PATH` dans l'en-tête et le README de `bin/measure-mobile`. |
| Responsable | Scrum Master (Claude) |
| Deadline | Première semaine du sprint 15 |
| DoD | Un lancement à froid de `bin/measure-mobile` suit la doc et passe 20/20, sans aide. |
| Priorité | Moyenne |

### Action 3 : Rapports de fin courts et structurés
| Attribut | Valeur |
|----------|--------|
| Description | Imposer dans le contrat de fin de worker un rapport de moins de 100 mots (fichiers, tests, points ouverts). |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 15 |
| DoD | 0 relance pour rapport tronqué au sprint 15. |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 13

| Action (S13) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Captures sans curseur et sans doublon | SM | ➖ Sans objet | Aucune capture produite ce sprint |
| 2. Script `bin/clean-test-caches` | Worker devops | ❌ Non fait | Absent de `bin/` ; aucun `var/cache-*` résiduel constaté, mais le disque a saturé (reprise en action 1) |
| 3. Relire sa boîte avant le rapport final | SM | ⚠️ À vérifier | Aucune contradiction signalée, mais deux rapports ont été tronqués |

Bilan : 0 action atteinte avec preuve, 1 non faite, 1 sans objet, 1 à vérifier.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
