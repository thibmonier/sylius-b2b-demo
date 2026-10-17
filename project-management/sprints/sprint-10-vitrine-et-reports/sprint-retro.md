# Rétrospective - Sprint 10

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-11-13 (après-midi) ; préparée le 2026-10-17 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), workers Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal partiellement atteint : 4 stories (13 pts) en review, 765 tests sans échec, ECS et PHPStan propres. Objectif `demo_orders` non atteint (1 911 pour 1 750), T-047-09 sacrifiée, T-048-11 partielle, T-048-13 après déploiement. CI de la PR #19 en cours.

## Observations (Starfish)

### Continuer
- Le contrat de fin de worker : 3 workers sur 3 ont rendu le bloc.
- La revue indépendante : aucun bloquant sur US-047, mais correctifs utiles ; limiteur à clé unique d'US-029 corrigé (clé par IP).
- Mesurer avant de conclure : le pic mémoire local est de 458 Mo (et non 1,08 Go), l'hypothèse « le 404 de `/api/v2` impacte le MCP » était fausse.
- Publier les chiffres tels quels (`demo_orders` 1 911, objectif non atteint).

### Commencer
- Isoler les workers parallèles (un worktree chacun, ou `git add` par chemins explicites).
- Vérifier les métadonnées (manifeste) avant de juger une image sur une planche.
- Contrôler l'espace disque avant un lot de travaux lourds.
- Fixer l'objectif de perf par poste sur un périmètre égal (6 840 contre 7 014 à périmètre différent).

### Arrêter
- Laisser plusieurs workers écrire dans le même arbre de travail.
- Accepter un rapport de fin tronqué sans le redemander explicitement dans la consigne.

### Plus de
- Tests de non-régression pour les défauts de revue (validation d'id, manifeste, limiteur).
- Décisions utilisateur consignées dans les docs (API désactivée, images partagées par famille).

### Moins de
- Relances faute de fin de rapport.
- Hypothèses non mesurées reportées d'un sprint à l'autre.

## Thèmes et analyse

### Thème 1 : arbre de travail partagé (priorité haute)
Faits : le commit US-029 a embarqué une ligne d'un autre worker (HEAD cassé entre deux commits) ; une revue a été manquée par un worker.

5 Pourquoi :
1. Pourquoi un commit mélangé ? → les workers modifient les mêmes fichiers au même moment.
2. Pourquoi ? → un seul arbre de travail est partagé.
3. Pourquoi ? → rien n'impose l'isolation ni l'ajout de fichiers par chemins explicites.
Cause racine : la consigne n'impose ni worktree ni staging ciblé.

### Thème 2 : qualité des rapports de fin (priorité moyenne)
Faits : bloc rendu 3/3, mais plusieurs rapports tronqués (relances pour la fin) et un message de revue manqué.
Cause racine : le contrat fixe le contenu, pas la longueur ni l'ordre ; le bloc arrive à la fin du rapport, là où il est coupé.

### Thème 3 : limites de l'environnement et des sources (priorité moyenne)
Faits : disque plein un instant (écriture échouée) ; quota Openverse de 200 requêtes par jour ; pertinence CC0 faible, d'où 23 images propres pour 187 partagées.
Cause racine : les ressources (disque, quota) n'étaient pas chiffrées à la planification.

### Thème 4 : objectifs de performance (priorité basse)
Faits : `container_fill` 2 205 → 31, reset 9 064 → 7 014, `demo_orders` bloqué à 1 911 par le cœur Sylius. La rétro 9 avait demandé un objectif par poste : l'objectif de 1 750 restait fixé avant d'avoir identifié la part du cœur.

## Actions (SMART)

### Action 1 : Isoler les workers parallèles
| Attribut | Valeur |
|----------|--------|
| Description | Imposer dans chaque consigne un worktree dédié, ou à défaut `git add` par chemins explicites et `git diff --cached` avant commit. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 11 |
| DoD | Règle ajoutée au `README.md` des tâches du sprint 11 ; 0 commit contenant des lignes d'un autre worker ; HEAD vert à chaque commit. |
| Priorité | Haute |

### Action 2 : Rapport de fin court et placé en premier
| Attribut | Valeur |
|----------|--------|
| Description | Modifier le contrat : le bloc de fin vient en première section du rapport, avec un plafond de longueur, et les messages de revue sont listés dans le bloc. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 11 |
| DoD | Modèle mis à jour dans `tasks/README.md` ; 100 % des workers du sprint 11 rendent le bloc complet ; 0 relance pour rapport tronqué. |
| Priorité | Haute |

### Action 3 : Solder les relevés Railway
| Attribut | Valeur |
|----------|--------|
| Description | Après déploiement du sprint 10, relever le pic mémoire du reset (T-048-13) et faire le relevé de coût complet (T-048-11). |
| Responsable | Thibaut (GO et accès), assisté du SM |
| Deadline | 7 jours après le déploiement, avant le planning du sprint 11 |
| DoD | Pic mémoire (Go) et coût (\$) consignés dans `docs/` ; ticket créé si la limite du service est dépassée. |
| Priorité | Haute |

### Action 4 : Reporter les mineurs et l'objectif ouvert au backlog
| Attribut | Valeur |
|----------|--------|
| Description | Créer les tâches estimées : limite de pixels et `Content-Length`, gestion du 429, image principale conservée si le manifeste change, crédits (T-047-09), et décision sur `demo_orders` (1 911 pour 1 750). |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 11 |
| DoD | 5 éléments au backlog avec estimation ; chaque correctif prévoit un test qui échoue avant correction. |
| Priorité | Moyenne |

### Action 5 : Chiffrer disque et quotas à la planification
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à la checklist de pré-sprint l'espace disque libre requis et les quotas externes (API, Railway). |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 11 |
| DoD | Ligne ajoutée à `pre-sprint-checklist.md` ; 0 échec d'écriture dû au disque au sprint 11. |
| Priorité | Basse |

## Suivi des actions de la rétrospective du sprint 9

| Action (S9) | Responsable | Statut | Constat |
|-------------|-------------|--------|---------|
| 1. Contrat de fin de worker | SM | ⚠️ Fait en partie | Bloc rendu par 3/3 workers (T-048-01), mais rapports tronqués avec relances ; repris en action 2 |
| 2. Base de test locale en une commande | Worker devops | ✅ Fait | `bin/prepare-test-db`, procédure dans `docs/` (T-048-02, T-048-03) ; base neuve et 765 tests verts |
| 3. Vérifier sur Railway | Thibaut + SM | ⚠️ En cours | `/api/v2` : 404 expliqué (API désactivée hors dev, maintenue) ; T-048-11 partielle ; T-048-13 après déploiement ; repris en action 3 |
| 4. Objectifs de performance après profilage | Worker perf | ✅ Fait | Profilage par fixture (T-048-04) ; `container_fill` et reset atteints, `demo_orders` non atteint |
| 5. Mineurs de revue et N+1 Wishlist | SM + workers | ✅ Fait | NFC, capacité périmée, entrypoint LoadMarker, Wishlist constante (T-048-07 à T-048-10) |

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
