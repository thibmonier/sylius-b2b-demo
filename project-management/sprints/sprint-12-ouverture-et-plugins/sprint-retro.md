# Rétrospective - Sprint 12

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-12-11 (après-midi) ; préparée le 2026-10-17 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), workers Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code et documentation : 3 stories sur 4 (11 pts sur 14) en review, US-052 (3 pts) en cours car ses tâches OPS reviennent à l'utilisateur. 861 tests et 8 558 assertions sans échec (2 passages), ECS et PHPStan OK. Deux erreurs de processus : une affirmation de planning non vérifiée (Packagist) et une CI de plugin en échec au premier push.

## Observations (Starfish)

### Continuer
- Vérifier sur base neuve avec deux passages de la suite complète.
- La revue de sécurité indépendante (US-053 : aucun bloquant, risque accepté documenté).
- Demander l'accord de l'utilisateur avant toute action externe (création du dépôt public).
- Le script de vérification en lecture seule (`bin/verify-public-mode`).

### Commencer
- Vérifier toute affirmation sur un état externe (Packagist, CI) par une requête réelle avant de l'écrire dans le planning.
- Prévoir un script de nettoyage des caches que les agents peuvent lancer.
- Passer une checklist du squelette avant le premier push d'un nouveau plugin.

### Arrêter
- Faire confiance à un compte rendu d'agent pour un état externe sans le recouper.
- Laisser des `var/cache-*` derrière soi quand `rm -rf` est refusé aux agents.

### Plus de
- Revues qui rendent leur rapport du premier coup.
- Isolation de l'environnement de test (`bin/run-tests`, cache jetable).

### Moins de
- Faux échecs dus à des états partagés (compteurs des limiteurs).
- Tâches dépendant de l'utilisateur qui retardent la clôture d'une story.

## Thèmes et analyse

### Thème 1 : planning fondé sur un état externe non vérifié (priorité haute)
Faits : le planning affirmait agentic-feeds sur Packagist ; la v1.0.0 venait du dépôt VCS et Packagist répond 404. US-031 reste bloquée, découvert en cours de sprint.

5 Pourquoi :
1. Pourquoi US-031 a-t-elle été jugée débloquée ? → l'agent de planning a affirmé la présence sur Packagist.
2. Pourquoi l'affirmation était-elle fausse ? → la v1.0.0 résolvait depuis le dépôt VCS.
3. Pourquoi n'a-t-elle pas été vue ? → personne n'a interrogé Packagist.
Cause racine : un état externe est repris tel quel dans le planning, sans requête réelle.

### Thème 2 : premier push d'un nouveau plugin (priorité moyenne)
Faits : la première CI du plugin a échoué, le dossier `assets/` (entrypoints vides) manquant. Correctif poussé.
Cause racine : aucune CI locale ni checklist du squelette avant le premier push.

### Thème 3 : nettoyage des caches (priorité moyenne)
Faits : aucun arrêt de MySQL, mais des `var/cache-*` sont restés car `rm -rf` est refusé aux agents.
Cause racine : le nettoyage prévu repose sur une commande interdite aux agents.

### Thème 4 : tâches OPS en fin de chaîne (priorité basse)
Fait : US-052 reste en cours car la bascule, le moniteur, `X-Real-IP`, le MCP réel et le coût dépendent de l'utilisateur.
Cause racine : story calendaire mêlant travail de Claude et tâches de l'utilisateur.

## Actions (SMART)

### Action 1 : Vérifier les états externes par une requête réelle
| Attribut | Valeur |
|----------|--------|
| Description | Avant d'écrire dans un planning une affirmation sur un état externe (Packagist, CI), la vérifier par une requête réelle (`curl`, `gh`) et citer le résultat. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 13 |
| DoD | 100 % des affirmations sur un état externe du planning du sprint 13 portent la commande de vérification et son résultat ; 0 story débloquée à tort. |
| Priorité | Haute |

### Action 2 : Script de nettoyage des caches
| Attribut | Valeur |
|----------|--------|
| Description | Créer `bin/clean-test-caches`, que les agents peuvent lancer, pour supprimer les `var/cache-*` laissés par les workers. |
| Responsable | Worker devops, relu par le Scrum Master |
| Deadline | Fin de la première semaine du sprint 13 |
| DoD | Le script est ajouté au contrat de fin de worker (`tasks/README.md`) ; 0 dossier `var/cache-*` résiduel à la fin du sprint 13. |
| Priorité | Moyenne |

### Action 3 : Checklist du squelette avant le premier push
| Attribut | Valeur |
|----------|--------|
| Description | Pour un nouveau plugin, lancer une CI locale ou passer une checklist du squelette (dont le dossier `assets/`) avant le premier push. |
| Responsable | Scrum Master (Claude) |
| Deadline | Avant le premier push du prochain plugin (US-056 et suivantes) |
| DoD | La checklist est écrite ; la première CI du prochain nouveau plugin est verte au premier push. |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 11

| Action (S11) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Gérer l'espace disque | SM | ⚠️ Partielle | 0 arrêt de MySQL pendant le sprint 12, mais des `var/cache-*` sont restés car `rm -rf` est refusé aux agents (repris en action 2) |
| 2. Cache de test isolé et vidé | Worker devops | ✅ Fait | `bin/run-tests` avec cache jetable par exécution ; un faux échec dû aux compteurs des limiteurs partagés a été corrigé |
| 3. Relecteurs avec assez de tours | SM | ✅ Fait | Les 3 revues du sprint 12 ont rendu leur rapport du premier coup |

Bilan : 2 actions atteintes, 1 partielle.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
