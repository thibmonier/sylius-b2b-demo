# Rétrospective - Sprint 30

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-25, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 29, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers w199, w201, w202 et un fork pour les tâches transverses) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint dans le code : **13 points livrés sur 13**, sans sacrifice. Vélocité : S26 29, S27 13, S28 13, S29 13, **S30 13** (moyenne des quatre précédents : 17). Les vérifications de production restent ouvertes jusqu'à la remise à zéro de 03:00 du 2026-10-26, avec les contrôles reportés des sprints 28 et 29.

Les écarts du sprint :
- le disque sous 5 Go à plusieurs reprises, les trois workers à l'arrêt environ 40 minutes ;
- la suite complète impossible en local, et une CI rouge au premier passage ;
- un défaut de TVA (historique taxé à moitié) découvert par un test de performance, pas par un test fonctionnel ;
- une décision par défaut d'un worker notée au registre après coup ;
- un `phpunit` lancé en direct, une fois ;
- des notifications d'attente des workers arrivées après les réponses, source de relances.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- Les tâches transverses en tête de sprint : `bin/proof-rebase`, le cache de rendu unique et les vignettes réelles sont faits, après trois reports ; la preuve n'a plus été régénérée à la main.
- La maquette validée avant tout Twig, sur les vraies données relevées par les workers au cadrage (`maquette-data.json`).
- L'arrêt avant un contournement : w202 a montré que le plugin n'offrait aucun point d'extension, a proposé trois options, et Thibaut a tranché en une réponse.
- Les fichiers partagés déclarés au planning et commités par l'index (`git apply --cached`) : 0 commit embarquant le travail d'un autre worker.
- La capture à 1280 px relue avant la PR : le menu sans style du tableau de bord a été vu et corrigé avant la fusion.
- Le budget de requêtes comme garde-fou : il a révélé un mélange HT et TTC que les tests fonctionnels ne voyaient pas.

### Commencer

- Mesurer la mémoire et le swap avant de lancer quatre agents, et limiter le parallélisme à ce que la machine supporte (thème 1).
- Un test qui vérifie la cohérence HT et TTC de l'historique de commandes (thème 2).
- Corriger `bin/render-page` pour les URL d'images relatives (thème 4).

### Arrêter

- Lancer quatre agents en parallèle sur une machine à 5 Go de swap et 6 Go de disque.
- Accepter l'espace « disponible » du Finder comme mesure : seul `df` compte.

### Plus de

- Messages de workers qui portent l'état réel (ce qui est commité, ce qui ne l'est pas) plutôt que l'historique de leurs actions.

### Moins de

- Relances entre orchestrateur et workers dues aux notifications d'attente décalées.

## Thèmes et analyse

### Thème 1 : machine saturée par le parallélisme (priorité haute)

Avec trois workers, un fork et MySQL, le swap a atteint 4 Go et l'espace libre est descendu sous 5 Go : les workers se sont arrêtés comme prévu, pendant environ 40 minutes, jusqu'au nettoyage par Thibaut. Le Finder affichait ensuite 8 Go là où `df` en voyait 5,9 : l'espace « purgeable » n'est pas utilisable par les scripts, et la suite complète locale a été refusée. Les seuils ont tenu (aucun test lancé sous le seuil), mais le coût en temps et la dépendance au nettoyage manuel restent.

### Thème 2 : un défaut de TVA vu par un test de performance (priorité haute)

Après la TVA d'US-202, 15 commandes de l'historique, construites par le tunnel de commande, étaient taxées, et les 47 écrites en masse ne l'étaient pas : l'historique mélangeait HT et TTC et les montants du tableau de bord changeaient sans qu'on le voie. Aucun test fonctionnel ne l'a signalé ; c'est le budget de requêtes de `OrderHistoryFixtureQueryCountTest` (1120 pour 1050) qui l'a révélé en CI. Corrigé en TDD (`OrderHistoryWithoutTaxTest`, processeur de taxe mis de côté pendant la construction de l'historique), budget non relevé.

### Thème 3 : suite complète locale non jouée (priorité moyenne)

Faute de 6 Go selon `df`, la suite complète n'a pas tourné avant le push ; la CI l'a jouée et a trouvé l'échec du thème 2. Rien n'a été fusionné rouge, mais la règle « CI rejouée en local avant le push » n'a été suivie que pour la qualité (ECS, PHPStan, Rector, lint), pas pour les tests.

### Thème 4 : outillage de rendu et de console (priorité basse)

`bin/render-page` ne génère les vignettes que pour les URL absolues ; le panier émet des URL relatives, et w202 a dû utiliser une copie de travail du script. `bin/console` plante en local à 128 Mo de mémoire quand il recompile le cache, ce qui a retardé le lint Twig de deux workers.

### Thème 5 : registre et décisions par défaut (priorité basse)

w202 a laissé l'historique sans TVA par défaut et l'a annoncé ; l'orchestrateur l'a noté au registre ensuite, avec la livraison taxée. Les deux décisions sont justes et tracées, mais pas « avant l'action ».

### Thème 6 : communication avec les workers (priorité basse)

Plusieurs notifications d'attente des workers sont arrivées après les réponses de l'orchestrateur (« j'attends le GO » alors que le GO était envoyé), ce qui a provoqué des relances. Un worker a aussi lancé `phpunit` en direct une fois avant de relire la règle 1, et l'a signalé.

## Suivi des actions de la rétrospective du sprint 29

| # | Action | Résultat au sprint 30 |
|---|--------|------------------------|
| 1 | `bin/proof-rebase` et cache de rendu unique en tête | ✅ Faits en premier (`7a64c80`, `25ad343`) ; 0 régénération manuelle de la preuve |
| 2 | Vignettes `b2b_photo` dans `bin/render-page` | ✅ Faites ; 0 capture simulée ; écart pour les URL relatives (thème 4) |
| 3 | Fichiers partagés déclarés au planning | ✅ Matrice dans `tasks/README.md` ; 0 commit embarquant le travail d'un autre worker |
| 4 | Rendu bureau des pages touchées avant la PR | ✅ Captures à 1280 px relues ; menu sans style corrigé avant la fusion |
| 5 | Environnement de test fiable | ✅ `ErpDemoExchangeFixtureTest` isolé ; règle 22 (aucun test depuis un worktree au `vendor` en lien symbolique) respectée |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 30 | Sprint 29 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 | 0 |
| Espace libre minimal | 5 Go | **4,9 Go** (workers à l'arrêt, aucun test lancé sous le seuil) | sous 5 Go une fois |
| Suite complète lancée sous le seuil | 0 | 0 (aucune suite complète locale) | 0 |
| CI rouges après contrôles locaux verts | 0 | **1** (suite non jouée en local) | 0 |
| Commit embarquant le travail d'un autre worker | 0 | 0 | 1 |
| Défauts trouvés après le déploiement | 0 | 0 relevé à la clôture | 1 |
| Capture simulée | 0 | 0 | 1 lot |
| Régénération manuelle de la preuve | 0 | 0 | plusieurs |
| Commande récursive sur un chemin non vérifié | 0 | 0 | 0 |
| Push, tag ou release sans GO | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 : Parallélisme réglé sur la machine
| Attribut | Valeur |
|----------|--------|
| Description | Avant de lancer les workers, l'orchestrateur relève `df -h /`, la mémoire libre et le swap ; au-delà de 2 Go de swap ou sous 7 Go de disque, au plus deux workers lancent des tests en même temps, les autres préparent du code. Le seuil se mesure avec `df`, jamais avec le Finder. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 31 (règle 24 de la consigne) |
| DoD | 0 arrêt de worker pour cause de disque au sprint 31. |
| Priorité | Haute |

### Action 2 : Suite complète locale avant le push, ou attente explicite
| Attribut | Valeur |
|----------|--------|
| Description | La suite complète tourne en local avant le push de la PR de sprint ; si le disque l'empêche, l'orchestrateur le dit dans la PR et demande à Thibaut de libérer de l'espace avant de pousser, plutôt que de laisser la CI découvrir l'échec. |
| Responsable | Orchestrateur (Claude) |
| Deadline | PR du sprint 31 |
| DoD | 0 CI rouge sur un test après les contrôles locaux au sprint 31. |
| Priorité | Haute |

### Action 3 : Cohérence HT et TTC des montants
| Attribut | Valeur |
|----------|--------|
| Description | Un test vérifie que les commandes d'historique sont toutes sans TVA et que les commandes récentes portent leur TVA ; les montants du tableau de bord restent HT. |
| Responsable | Orchestrateur (Claude), avec le worker de la story concernée |
| Deadline | Sprint 31 |
| DoD | Le test existe et passe ; il échoue si une commande d'historique est taxée. |
| Priorité | Moyenne |

### Action 4 : Outils de rendu et de console
| Attribut | Valeur |
|----------|--------|
| Description | `bin/render-page` génère les vignettes pour les URL d'images relatives comme absolues (avec test) ; `docs/tests-locaux.md` indique `-d memory_limit=1G` pour `bin/console` en local. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la première capture du sprint 31 |
| DoD | Une capture du panier par `bin/render-page` montre ses vignettes ; aucune copie de travail du script. |
| Priorité | Basse |

### Action 5 : Échanges avec les workers
| Attribut | Valeur |
|----------|--------|
| Description | Chaque réponse de l'orchestrateur à un worker cite l'identifiant de la décision (commit du registre) ; une notification d'attente antérieure à cette réponse est ignorée sans relance. La consigne rappelle la règle 1 (`bin/run-tests` seulement) en tête. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 31 |
| DoD | 0 relance pour une décision déjà envoyée ; 0 `phpunit` lancé en direct. |
| Priorité | Basse |

## Intégration au sprint 31

Les actions 1 à 5 sont à reprendre au planning du sprint 31, après les vérifications de production du 2026-10-26 (T-199-10, T-201-10, T-202-08, revues T-199-11, T-201-11, T-202-09, et contrôles reportés des sprints 28 et 29).

## Points positifs à conserver

- 13 points sur 13, sans sacrifice, avec deux pages refondues fidèles à des maquettes validées : toutes les refontes demandées le 2026-10-25 sont livrées.
- Les cinq actions de la rétrospective du sprint 29 tenues, dont les deux tâches transverses reportées trois fois.
- Un plugin publié proprement (v1.4.0, CI verte sur 21 contrôles, défaut inchangé pour les autres installations) sur GO explicite.
- Deux défauts de TVA trouvés et corrigés avant la production (zone de taxe, historique).
- 0 commit croisé, 0 capture simulée, 0 PR fusionnée avec une question ouverte.
