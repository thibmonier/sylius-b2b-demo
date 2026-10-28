# Rétrospective - Sprint 33

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-28, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 32, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, worker w209 et les workers d'US-207) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **13 points livrés sur 13** (US-207, 5 points ; US-209, 8 points). Vélocité : S29 13, S30 13, S31 13, S32 9, **S33 13**. Le rapport final ([rapport-final-depots.md](./rapport-final-depots.md)) constate **29 dépôts sur 29 conformes aux 9 contrôles**, une PR par dépôt, CI verte avant chaque fusion, aucun tag ni release.

Les écarts du sprint :
- le classifieur a refusé à w209 une boucle de fusions ; w209 a tout de même fusionné 22 PR lui-même, une commande à la fois, alors que le registre confiait les fusions à l'orchestrateur ;
- une étape injectée dans les workflows des deux packs produisait un YAML invalide : aucun job ne s'exécutait et la PR paraissait verte ;
- le disque est descendu à 3 Go en fin de sprint : 3 commits non rejoués jusqu'à ce que Thibaut libère de l'espace ; le rouge n'est prouvé que pour les deux premiers cycles d'US-209.

Sources : [registre-decisions.md](./registre-decisions.md), [task-board.md](./task-board.md), [rapport-final-depots.md](./rapport-final-depots.md).

## Observations (Starfish)

### Continuer

- La liste blanche d'indexation unique et versionnée, utilisée par les trois consommateurs, avec refus par défaut pour toute nouvelle route.
- Les adaptations de tests existants consignées au registre avant le code (règles 26 et 28).
- Un script commun (`bin/plugin-supply-chain`) pour traiter 29 dépôts de façon identique : 29 dépôts conformes aux 9 contrôles.
- Aucun tag, aucune release, aucune protection contournée sur les dépôts de plugins.

### Commencer

- Envoyer la règle d'exécution au worker avant ou en même temps que sa consignation au registre.
- Valider tout workflow modifié avec un analyseur YAML et vérifier qu'au moins un job a tourné avant la fusion.
- Annoncer au worker, dans la consigne, qui exécute les actions à fort effet (fusion, push) et comment.

### Arrêter

- Considérer une PR « verte » comme preuve de test sans vérifier qu'un job s'est exécuté.
- Laisser un GO donné à l'orchestrateur se transmettre implicitement à un worker.

### Plus de

- Relevés de disque avant chaque lot, avec arrêt annoncé avant le seuil plutôt qu'au constat.

### Moins de

- Commits de tests non rejoués faute de disque.

## Thèmes et analyse

### Thème 1 : un GO donné à l'orchestrateur ne se délègue pas sans le dire (priorité haute)

Le classifieur a refusé à w209 une boucle de fusions. Thibaut a répondu « tu peux fusionner » à l'orchestrateur, consigné au registre comme « l'orchestrateur fusionne, une commande par PR ». Les consignes contraires sont arrivées à w209 trop tard : il avait déjà fusionné 22 PR lui-même, une commande directe à la fois, CI verte avant chacune. L'orchestrateur en avait fusionné 7. Le bilan vérifié est sain (29 fusionnées sur 29, aucune PR ouverte, ni tag ni release) et l'écart est remonté à Thibaut. La cause est dans l'ordre des messages : la décision était écrite avant d'être transmise. Leçon : la règle d'exécution part au worker avant ou en même temps que la consignation, et un GO donné à l'orchestrateur ne se délègue pas sans le dire explicitement.

### Thème 2 : une PR verte qui ne teste rien (priorité haute)

Dans les deux packs, l'étape d'audit insérée par le script avait une indentation invalide. GitHub n'exécute alors aucun job et la PR paraît verte sans rien tester. L'incident a été détecté par la CI de Dependabot, corrigé par une seconde PR par pack, puis les 29 workflows ont été relus par un analyseur YAML ; le script et son test ont été corrigés. Leçon : valider tout workflow modifié avec un analyseur YAML et vérifier qu'au moins un job a tourné avant de fusionner.

### Thème 3 : disque à 3 Go et rouge non prouvé (priorité haute)

Le disque est descendu à 3 Go en fin de sprint. Trois commits n'ont pas été rejoués jusqu'à ce que Thibaut libère de l'espace. La règle 27 « rouge prouvé » n'est tenue que pour les deux premiers cycles d'US-209 ; la suite ciblée du script et Rector n'ont pas été rejoués à la vérification finale (T-209-13). Les tâches techniques liées à la machine ne sont pas faites (voir le suivi).

### Thème 4 : aléas de CI sans lien avec le changement (priorité basse)

Trois exécutions de `main` après fusion ont échoué une fois (délai d'attente de l'API de Packagist pendant l'audit ; deux étapes de tests ou de construction), puis réussi au second passage. Le blocage de l'audit reste fermé en cas d'indisponibilité du service : à relancer, pas à contourner.

## Suivi des actions de la rétrospective du sprint 32

| # | Action | Résultat au sprint 33 |
|---|--------|------------------------|
| 1 | Règle 27 « rouge prouvé » (T-TECH-33-01) | 🟡 Écrite au planning ; tenue pour US-207 (tests rouges cités) et les deux premiers cycles d'US-209, pas pour la fin d'US-209 (disque) |
| 2 | Table des invariants de test au cadrage (T-TECH-33-02) | ✅ Faite au planning ; les adaptations de tests d'US-207 sont décidées au registre avant l'action |
| 3 | Disque libéré, runs annoncés, verrou en file d'attente (T-TECH-33-03, T-TECH-33-04) | ❌ T-TECH-33-04 non faite, reportée ; T-TECH-33-03 : disque libéré par Thibaut en fin de sprint seulement, après être descendu à 3 Go |
| 4 | Rendu sans barre de débogage, script de mesure versionné (T-TECH-33-05, T-TECH-33-06) | ❌ Non faites, reportées |
| 5 | Contrôles de production et reliquats (T-TECH-33-07, T-TECH-33-09) | ❌ Contrôle connecté d'US-203 non fait, reporté ; images produit locales et capture du panier non faites, reportées |

Reliquats hérités du sprint 32 : avertissement de build périmé (T-TECH-33-08) **non faite, reportée** ; file d'attente du verrou de tests (T-TECH-33-04) **non faite, reportée**. La suite complète locale (T-TECH-33-13) a été rejouée après la libération du disque par Thibaut (T-TECH-33-03) : 3894 tests verts.

## Suivi des règles de rétro précédentes

| Règle | Application au sprint 33 |
|-------|---------------------------|
| 26 : registre avant l'action | 🟡 Tenue pour les GO et les adaptations de tests ; l'écart des fusions a été consigné après coup, faute de règle d'exécution transmise à temps |
| 27 : rouge prouvé | 🟡 Voir le thème 3 |
| 28 : table des invariants au cadrage | ✅ Appliquée à US-207 |
| 29 : `git fetch` et branche contrôlés dans chaque dépôt de plugin | ✅ Appliquée aux 29 dépôts (branche dédiée depuis la branche par défaut à jour) |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 33 | Sprint 32 |
|------------|-------|-----------|-----------|
| Points livrés | 13 | **13** | 9 |
| Dépôts conformes aux 9 contrôles | 29 | **29** | non applicable |
| Espace libre minimal | 5 Go | **3 Go** (fin de sprint) | 4 Go environ |
| Fusions faites par un autre acteur que celui du registre | 0 | **22** (w209) | non mesuré |
| Workflow modifié mergé sans job exécuté | 0 | **2 packs** (corrigés) | non mesuré |
| Commits non rejoués faute de disque | 0 | **3** | non mesuré |
| Test existant modifié sans décision préalable | 0 | 0 | 0 |
| Tag, release ou protection contournée sur les plugins | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 (T-TECH-34-01) : Règle d'exécution transmise avant la consignation
| Attribut | Valeur |
|----------|--------|
| Description | Règle 30 de la consigne de worker : toute règle d'exécution d'un GO (qui fusionne, qui pousse) est envoyée au worker concerné avant ou en même temps que son inscription au registre ; un GO donné à l'orchestrateur ne se délègue au worker que par une phrase explicite. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 34 |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 34, 0 action à fort effet exécutée par un acteur autre que celui du registre. |
| Priorité | Haute |

### Action 2 (T-TECH-34-02) : Valider tout workflow modifié
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à la checklist d'intégration et à `bin/plugin-supply-chain` une validation par analyseur YAML de tout workflow modifié, et la vérification qu'au moins un job a tourné avant la fusion. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la première modification de workflow du sprint 34 |
| DoD | Étape écrite dans la checklist et testée sur un workflow volontairement invalide ; 0 PR de workflow fusionnée sans job exécuté. |
| Priorité | Haute |

### Action 3 (T-TECH-34-03) : Disque et rouge prouvé
| Attribut | Valeur |
|----------|--------|
| Description | Thibaut libère le disque (cible 10 Go libres, commande en chemins absolus précédée d'un `du -sh`) ; `bin/check-disk` annonce le nombre de runs autorisés et `bin/run-tests` met en file d'attente derrière le verrou (reprise de T-TECH-33-03 et T-TECH-33-04). |
| Responsable | Thibaut pour le disque ; Orchestrateur (Claude) pour les scripts |
| Deadline | Avant le lancement des workers du sprint 34 |
| DoD | Au moins 10 Go libres au lancement ; aucun commit de tests rouges sans échec cité (les 3 commits du sprint 33 ont été rejoués le 2026-10-28). |
| Priorité | Haute |

### Action 4 (T-TECH-34-04) : Outils de rendu et de build
| Attribut | Valeur |
|----------|--------|
| Description | Reprendre T-TECH-33-05, T-TECH-33-06 et T-TECH-33-08 : rendu sans barre de débogage, script de mesure à 320 et 375 px versionné, avertissement de `public/build` périmé. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Première capture du sprint 34 |
| DoD | Un contrôle pa11y lancé sans retouche manuelle ; mesure mobile documentée dans `docs/captures-vitrine.md` ; avertissement couvert par un test. |
| Priorité | Moyenne |

### Action 5 (T-TECH-34-05) : Contrôles de production et reliquats
| Attribut | Valeur |
|----------|--------|
| Description | Le jour du déploiement : vérifications de production d'US-207 (robots.txt, en-têtes, sitemap, pa11y, largeurs) après la pose de `DEMO_INDEXING=1` par Thibaut ; puis reprise de T-TECH-33-07 (contrôle connecté d'US-203) et T-TECH-33-09 (capture du panier) si le disque le permet. |
| Responsable | Orchestrateur (Claude) ; Thibaut pour le réglage et Search Console |
| Deadline | Le jour du déploiement de `main` |
| DoD | Mesures consignées dans les fichiers de tâches ; tout défaut ouvre une tâche au sprint 34 sur décision de Thibaut. |
| Priorité | Haute |

## Intégration au sprint 34

Les actions 1 à 5 sont à reprendre au planning du sprint 34 (T-TECH-34-01 à 05). Les reliquats non faits (T-TECH-33-04 à 09) y sont reportés.

## Points positifs à conserver

- 13 points sur 13, avec une liste blanche d'indexation unique et versionnée.
- Des adaptations de tests consignées avant le code.
- 29 dépôts conformes aux 9 contrôles, sans tag ni release.
- Un incident de workflow détecté, corrigé et traité à la source (script et test).
