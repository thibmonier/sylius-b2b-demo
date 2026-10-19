# Rétrospective - Sprint 16

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-02-05 (après-midi) ; préparée le 2026-10-19 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code : 7 stories (27 pts) en revue, sans sacrifice. 1 306 tests, 0 échec ; pa11y AAA à 0 erreur sur 7 pages. La revue indépendante a trouvé 2 majeurs (fuite de jeton vers Umami, mentions légales inexactes), corrigés. Les difficultés viennent de l'exécution concurrente des tests et des tâches de fond, pas du code.

## Observations (Starfish)

### Continuer
- La revue indépendante : elle a bloqué une fuite de jeton vers un tiers avant l'ouverture.
- Les vérifications au rendu réel (URL factices, `www-data`, pa11y) et la reconstruction du conteneur par l'orchestrateur, qui a détecté l'incident du `chown`.
- Les rapports de fin de moins de 100 mots (action du S15 tenue).
- Le gel disque (disque stable entre 5,6 et 7,5 Go).

### Commencer
- Un verrou de fichier dans `bin/run-tests` : un seul run à la fois.
- Arrêter explicitement toute tâche de fond avant d'en relancer une.
- Une liste blanche par défaut pour tout script tiers (principe retenu pour Umami).

### Arrêter
- Détecter un processus par `pgrep -f` sur sa propre commande.
- Les tests qui modifient un compte de démo partagé sans annuler.
- Lancer ses tests en parallèle des autres malgré la consigne du lancement unique.

### Plus de
- Tests de l'entrypoint et du démarrage du conteneur (le correctif de revue l'a cassé).
- Tests de données vivantes indépendants de l'ordre.

### Moins de
- Résultats invalides à écarter (deux suites simultanées : 23 échecs sans cause réelle).
- Processus `rm -i` bloqués laissés par les développeurs.

## Thèmes et analyse

### Thème 1 : exécutions concurrentes sur la même base (priorité haute)
Faits : deux suites complètes et deux remises à zéro simultanées, 23 échecs, résultats invalides ; un développeur a lancé ses tests en parallèle ; une collision de fixtures.
5 Pourquoi :
1. Pourquoi 23 échecs ? → deux suites ont écrit sur la même base.
2. Pourquoi deux suites ? → une séquence « arrêtée » attendait le conteneur et a repris à son retour.
3. Pourquoi reprise ? → l'attente n'a pas été arrêtée explicitement, seulement perdue de vue.
4. Pourquoi aucune barrière ? → `bin/run-tests` n'empêche pas un second run ; la consigne est orale.
Cause racine : règle du lancement unique sans verrou technique.

### Thème 2 : détection de processus fragile (priorité haute)
Fait : `pgrep -f "bin/run-tests"` s'est détecté lui-même, ~15 min perdues.
Cause racine : l'attente repose sur la recherche d'un nom de commande plutôt que sur un état (fichier verrou).

### Thème 3 : tests qui partagent un compte de démo (priorité moyenne)
Faits : deux tests de données vivantes dépendaient de l'ordre (panier de Claire) ; assouplis, mais la cause demeure.
Cause racine : un test modifie un compte partagé sans annuler ses effets.

### Thème 4 : correctif de revue sans test de démarrage (priorité moyenne)
Fait : le `chown` conditionnel a empêché le conteneur de démarrer ; détecté à la reconstruction, la CI l'aurait aussi bloqué.
Cause racine : un correctif de revue n'est pas revalidé par une reconstruction avant de conclure.

## Actions (SMART)

### Action 1 : Verrou dans `bin/run-tests`
| Attribut | Valeur |
|----------|--------|
| Description | Un fichier verrou (créé à l'entrée, retiré à la sortie, détection d'un verrou périmé par PID) empêche un second run ; message clair et code de sortie non nul. L'attente de fin repose sur ce fichier, jamais sur `pgrep`. |
| Responsable | Worker devops |
| Deadline | Planning du sprint 17 |
| DoD | Un second `bin/run-tests` lancé pendant un run échoue immédiatement ; test de ce comportement ; 0 suite simultanée au sprint 17 (2 au sprint 16). |
| Priorité | Haute |

### Action 2 : Isoler les tests qui modifient un compte de démo partagé
| Attribut | Valeur |
|----------|--------|
| Description | Tout test qui modifie un compte de démo partagé (panier de Claire, etc.) l'isole (copie ou transaction) ou annule ses effets. Ajouter la règle à `tasks/README.md` et corriger les deux tests assouplis. |
| Responsable | Scrum Master (règle) + développeur fixtures (correction) |
| Deadline | Sprint 17, avant la première story touchant des données de démo |
| DoD | Règle écrite ; les deux tests d'ordre retrouvent leurs assertions strictes ; suite verte dans un ordre aléatoire (`--order-by=random`) ; 0 test dépendant de l'ordre. |
| Priorité | Haute |

### Action 3 : Arrêt explicite des tâches de fond avant relance
| Attribut | Valeur |
|----------|--------|
| Description | L'orchestrateur arrête explicitement toute tâche de fond (TaskStop) avant d'en relancer une, et vérifie que l'arrêt a eu lieu avant de relancer. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 17 |
| DoD | Consigne dans le gabarit d'orchestration ; 0 reprise inattendue d'une tâche arrêtée au sprint 17 (1 au sprint 16). |
| Priorité | Haute |

### Action 4 : Reconstruire le conteneur après tout correctif de revue touchant l'image
| Attribut | Valeur |
|----------|--------|
| Description | Un correctif de revue touchant le Dockerfile, l'entrypoint ou les droits est validé par une reconstruction et un démarrage du conteneur avant la clôture de la revue. |
| Responsable | Scrum Master (Claude) |
| Deadline | Sprint 17 |
| DoD | Étape ajoutée à la checklist de revue ; 0 correctif de revue cassant le démarrage découvert tardivement. |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 15

| Action (S15) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Volume `public/media` et remise à zéro documentée | Worker devops + Thibaut | ⚠️ Partiel | Faite côté code (US-145) ; reste l'application par Thibaut et la vérification de deux redéploiements |
| 2. Rapport de fin de moins de 100 mots | Scrum Master | ✅ Fait | Tenu au sprint 16 ; 0 relance pour rapport tronqué |
| 3. Règle de gel disque sans contournement | Scrum Master | ✅ Fait | Disque stable de 5,6 à 7,5 Go, aucun phpunit direct signalé |
| 4. Relancer avant de signaler un échec partagé | Scrum Master | ⚠️ Partiel | Pas de conflit de résultat entre développeurs, mais des échecs invalides venus de suites simultanées (reprise en actions 1 et 3) |

Bilan : 2 actions atteintes, 2 partielles, 0 non tenue.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
