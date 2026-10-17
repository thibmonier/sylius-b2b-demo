# Rétrospective - Sprint 13

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-12-24 (après-midi) ; préparée le 2026-10-17 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), workers Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code et documentation : 5 stories sur 5 (15 pts) en review. Les 24 PR de plugins attendent le GO unique de l'utilisateur et le CNAME attend le choix du domaine. 893 tests sans échec, ECS et PHPStan OK. Les défauts du sprint viennent des captures (doublon, curseur) et de messages qui se croisent entre orchestrateur et workers.

## Observations (Starfish)

### Continuer
- Les revues indépendantes (US-066 et README FR : aucun bloquant) et la relecture des captures par l'orchestrateur.
- Vérifier un état externe par une vraie requête (Packagist, CI).
- Un GO unique pour les 24 PR, une PR par dépôt.

### Commencer
- Produire les captures sans curseur et vérifier les doublons (md5) avant le rapport du worker.
- Un script de nettoyage des caches que les agents peuvent lancer.
- Relire sa boîte avant de rendre le rapport final.

### Arrêter
- Rendre un lot de captures sans contrôle des doublons ni du curseur.
- Laisser des `var/cache-*` derrière soi (`var/cache-w13d` reste).

### Plus de
- Relectures de l'orchestrateur sur les livrables visuels.
- Décisions de ton prises tôt (charte D2 appliquée aux 6 README).

### Moins de
- Rapports de workers qui se croisent avec les messages de l'orchestrateur.
- Dépendances à des décisions de l'utilisateur en fin de sprint (domaine, GO).

## Thèmes et analyse

### Thème 1 : qualité des captures (priorité haute)
Faits : 8 captures faites, 7 retenues. L'une doublait, octet pour octet, une autre et montrait une option du cœur Sylius ; le curseur était visible sur 3 captures ; le gestionnaire de mots de passe gênait les formulaires.

5 Pourquoi :
1. Pourquoi une capture en double ? → le worker n'a pas comparé ses fichiers.
2. Pourquoi le curseur visible ? → aucune consigne de le masquer.
3. Pourquoi découvert à la relecture ? → pas de contrôle avant le rapport.
Cause racine : pas de contrôle de sortie (curseur, doublons) dans la consigne du worker.

### Thème 2 : messages qui se croisent (priorité moyenne)
Faits : des messages de l'orchestrateur et des rapports de workers se sont croisés, laissant des rapports finaux en retard sur les décisions.
Cause racine : le worker rend son rapport sans relire sa boîte.

### Thème 3 : nettoyage des caches (priorité moyenne)
Fait : `var/cache-w13d` reste, `rm -rf` étant toujours refusé aux agents.
Cause racine : l'action 2 du sprint 12 (script de nettoyage) n'a pas été faite.

### Thème 4 : décisions de l'utilisateur en fin de chaîne (priorité basse)
Faits : GO des 24 PR, domaine (`thibaut-monier.fr` recommandé, `agentic-agency.fr` sans société juridique derrière) et US-052 reportée.
Cause racine : livraison conditionnée à des décisions externes au sprint.

## Actions (SMART)

### Action 1 : Captures sans curseur et sans doublon
| Attribut | Valeur |
|----------|--------|
| Description | Demander à chaque worker de produire ses captures sans curseur et de vérifier les doublons (md5) avant son rapport. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 14 |
| DoD | La consigne figure dans `tasks/README.md` ; 0 doublon et 0 curseur visible relevés à la revue du sprint 14. |
| Priorité | Haute |

### Action 2 : Script de nettoyage des caches
| Attribut | Valeur |
|----------|--------|
| Description | Créer enfin `bin/clean-test-caches`, que les agents peuvent lancer, pour supprimer les `var/cache-*`. |
| Responsable | Worker devops, relu par le Scrum Master |
| Deadline | Fin de la première semaine du sprint 14 |
| DoD | Le script est ajouté au contrat de fin de worker ; 0 dossier `var/cache-*` résiduel (dont `var/cache-w13d`) à la fin du sprint 14. |
| Priorité | Moyenne |

### Action 3 : Relire sa boîte avant le rapport final
| Attribut | Valeur |
|----------|--------|
| Description | Les messages de l'orchestrateur et les rapports des workers se croisent : un worker relit sa boîte avant de rendre son rapport final. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 14 |
| DoD | La règle figure dans le contrat de fin de worker ; 0 rapport final en contradiction avec un message reçu au sprint 14. |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 12

| Action (S12) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Vérifier un état externe par une vraie requête | SM | ✅ Fait | Appliquée : Packagist et CI vérifiés par requête réelle |
| 2. Script `bin/clean-test-caches` | Worker devops | ❌ Non fait | `rm -rf` est toujours refusé aux agents et `var/cache-w13d` reste (reprise en action 2) |
| 3. Checklist du squelette de plugin | SM | ➖ Sans objet | Aucun nouveau plugin ce sprint |

Bilan : 1 action atteinte, 1 non faite, 1 sans objet.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
