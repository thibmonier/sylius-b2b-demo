# Rétrospective - Sprint 26

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-24, préparée en fin de sprint |
| Durée | 1 h 30 |
| Format | Starfish, comme aux sprints 21 à 25, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers w186, w183, w185) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **29 points livrés** (23 engagés au planning, plus US-190, US-191 et US-192 ajoutées en cours de sprint). Vélocité : S22 23, S23 11, S24 19, S25 17, **S26 29** (moyenne des quatre précédents : 17,5). Le sprint le plus chargé de la série a tenu parce qu'il s'appuyait sur des socles publiés (plugin de transmission, ADR 0016 et 0017) et que Thibaut a tranché vite (maquette, photos, retours).

Les écarts du sprint :
- l'arbre de travail partagé entre workers : un commit a emporté les suppressions d'un autre worker, une configuration non commitée a cassé le noyau pour tous ;
- trois CI rouges après des tests ciblés verts (#87, #88, #91) ;
- l'espace libre tombé à 2-3 Go, des workers arrêtés sous 5 Go ;
- un rendu local en mode prod impossible.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- La maquette Claude Design validée par Thibaut avant le premier gabarit Twig : aucune reprise de fond sur la home.
- Le choix des photos fait avec Thibaut, licence relue, crédits publiés.
- Les retours de Thibaut traités dans la journée (en-tête #89, connexion #90, menu de compte #91), chacun avec test rouge puis vert.
- pa11y WCAG2AAA systématique, FR et EN, avant chaque PR de gabarit.
- Les commits de tests rouges seuls, y compris par cherry-pick après l'incident de l'arbre partagé.

### Commencer

- Lancer PHPStan sur tout le dépôt, tests compris, avant de rendre la main (la CI le fait, les contrôles locaux ciblés non).
- Déduire les compteurs (plugins, fichiers de tests) du catalogue ou de la preuve, jamais des nombres écrits dans un test.
- Consigner dans le fichier de tâches les mesures de production de chaque story (LCP après, largeur, pa11y) le jour du déploiement.

### Arrêter

- Commiter avec `git commit -a` ou depuis un index partagé dans l'arbre commun.
- Laisser une configuration non commitée dans l'arbre partagé.
- Lancer une suite complète quand l'espace libre est à 5,0 Go.

### Plus de

- Stories de retour de Thibaut de 1 à 3 points, planifiées au fil de l'eau : elles ont donné 4 points de valeur visible en une journée.

### Moins de

- Tours de CI rouges évitables : trois sur un sprint.

## Thèmes et analyse

### Thème 1 : l'arbre partagé entre workers (priorité haute)

Le commit `7d08546` (tests rouges d'US-184) a embarqué trois suppressions de gabarits de la home indexées par w186. Plus tard, une configuration non commitée dans l'arbre commun a produit des erreurs 500 pour tous les workers. La règle `git commit -m … -- <chemins>` (qui ne commite que les chemins nommés) a été adoptée en cours de sprint et n'a plus posé de problème, mais elle n'est écrite ni dans `consigne-worker.md` ni dans une garde.

### Thème 2 : tests ciblés verts, CI rouge (priorité haute)

Trois causes distinctes, un même défaut de méthode : le contrôle local ne reproduit pas la CI. #87 : la locale `en` du test contre `xx_YY` et une variable d'environnement qui fuit d'un test à l'autre. #88 : le nombre de plugins écrit à 26 dans un test, alors que le catalogue passait à 27. #91 : PHPStan sur un fichier de test que le contrôle local n'analysait pas. Chaque tour coûte un cycle de CI et attire l'attention sur un détail que le worker aurait pu voir.

### Thème 3 : disque et mémoire (priorité haute)

La cible de 5 Go n'est tenue sur aucun des deux derniers sprints (3,9 Go au sprint 25, 2-3 Go ici). Les workers se sont arrêtés sous 5 Go comme prévu, mais un worker a lancé une suite complète à 5,0 Go. Quitter l'application Dia a libéré environ 5 Go de swap : la mémoire compte autant que l'espace disque.

### Thème 4 : rendu local en mode prod (priorité moyenne)

Le noyau répond 503 « the demo is not configured safely » en mode prod local : les captures de la home ont été faites sur du HTML rendu par un test jetable puis servi en statique. Cela marche, mais ce n'est pas le rendu réel, et la mesure de production reste la seule preuve de LCP.

### Thème 5 : preuves de production non consignées (priorité moyenne)

Les contrôles du 2026-10-24 (largeurs, pa11y) ont été faits par l'orchestrateur après coup et ont trouvé un défaut que la garde d'US-182 n'attrape pas : `/demo/accounts` débordait à 360 px (`scrollWidth` 412, tables à 4 colonnes sans `code` long), corrigé par #92 et remesuré à `scrollWidth` 360. La mesure « après » du LCP n'a pas été refaite après #89 et #91. La garde porte sur les `code` ; elle ne couvre pas toute table large.

## Suivi des actions de la rétrospective du sprint 25

| # | Action | Résultat au sprint 26 |
|---|--------|------------------------|
| 1 | Budget disque d'un nouveau dépôt et restes listés | 🟡 Faite (ligne de checklist, budgets écrits) mais DoD non atteinte : 2-3 Go au minimum pour une cible de 5 Go |
| 2 | Garde sur les `code` dans les tableaux | 🟡 Garde en place (#85, 13 tests, deux gabarits corrigés), mais DoD non atteinte à la lettre : `/demo/accounts` débordait à 360 px en production (tables à 4 colonnes, hors périmètre de la garde), trouvé par le contrôle et corrigé dans le sprint (#92) |
| 3 | Concordance des versions avant publication | ✅ Atteinte : 0 artefact publié à une version qui ne concorde pas (v1.2.3, v0.2.0, v0.1.0, borne `^1.2.3` du pack IA) |
| 4 | Consigne pour un nouveau dépôt de plugin | ✅ Atteinte pour le plugin de fichier plat : aucun second serveur de base signalé ; le refus des `.env*` n'est pas revenu |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 26 | Sprint 25 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 | 0 |
| Espace libre minimal | 5 Go | **environ 2 à 3 Go** | 3,9 Go |
| Tâches livrées restées 🔲 à la clôture | 0 | 6 (vérifications en production et transverses, voir la revue) | 0 (1 📅 Thibaut) |
| Push, tag ou dépôt sans confirmation | 0 | 0 | 0 |
| Stories en done avec un scénario non livré | 0 | 0 (tables de preuves d'US-186 à remplir) | 0 |
| CI rouges après tests ciblés verts | 0 | **3** | 1 |
| Textes publics inexacts publiés | 0 | 0 | 0 |
| Défauts d'affichage trouvés en production | 0 | 3 retours de Thibaut corrigés le jour même, plus `/demo/accounts` à 360 px (corrigé dans le sprint, #92) | 1 |

## Actions (SMART)

### Action 1 : Commits limités aux chemins nommés
| Attribut | Valeur |
|----------|--------|
| Description | Écrire dans `consigne-worker.md` la règle `git commit -m "…" -- <chemins>` (git add d'abord pour les nouveaux fichiers), l'interdiction de `git commit -a` et de toute configuration non commitée dans l'arbre commun ; vérification `git show --stat` avant chaque push. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 27 |
| DoD | 0 commit qui embarque des fichiers d'un autre worker et 0 erreur 500 due à une configuration non commitée au sprint 27 (1 et 1 au sprint 26). |
| Priorité | Haute |

### Action 2 : Contrôle complet avant de rendre la main
| Attribut | Valeur |
|----------|--------|
| Description | Avant de déclarer une PR prête, le worker lance PHPStan sur tout le dépôt (`src` et `tests`) en plus des tests ciblés, et remplace tout compteur écrit en dur (plugins, fichiers de tests) par une valeur déduite du catalogue ou de la preuve ; la ligne est ajoutée à `checklist-integration.md` et à T-TECH-26-06 du sprint 27. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 27 |
| DoD | 0 CI rouge après des contrôles locaux verts au sprint 27 (3 au sprint 26). |
| Priorité | Haute |

### Action 3 : Seuils disque et mémoire appliqués
| Attribut | Valeur |
|----------|--------|
| Description | `bin/check-disk` refuse aussi une suite complète sous 6 Go (seuil relevé de 5 Go) ; l'orchestrateur relève mémoire libre et swap avant chaque lot, et demande à Thibaut de quitter les applications lourdes (Dia) quand le swap dépasse la moitié de l'espace libre. |
| Responsable | Orchestrateur (Claude) pour le script et le relevé, Thibaut pour les applications |
| Deadline | Avant le premier lot du sprint 27 |
| DoD | Espace libre minimal d'au moins 5 Go au sprint 27 (2-3 Go au sprint 26) et 0 suite complète lancée sous le seuil. |
| Priorité | Haute |

### Action 4 : Rendu local en mode prod
| Attribut | Valeur |
|----------|--------|
| Description | Investiguer le 503 « the demo is not configured safely » en mode prod local : lister les variables exigées, documenter dans `docs/tests-locaux.md` le jeu minimal qui permet de rendre la home, ou un script de captures qui n'en a pas besoin. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la maquette de la fiche produit (US-189), sprint 27 |
| DoD | Les captures d'US-189 sont faites sur un rendu du noyau en mode prod, sans HTML jetable. |
| Priorité | Moyenne |

### Action 5 : Mesures de production consignées le jour du déploiement
| Attribut | Valeur |
|----------|--------|
| Description | Consigner dans le fichier de tâches de la story, le jour du déploiement, les mesures de production (LCP après, largeur à 360 et 1280 px, pa11y) ; étendre la garde de largeur aux tables larges sans `code` ; remesurer le LCP « après » d'US-186. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Rattrapage avant le planning du sprint 27, puis chaque story |
| DoD | 0 tâche de contrôle en production restée ouverte à la clôture du sprint 27 (T-186-14 au sprint 26) et 0 page de la démo avec défilement horizontal à 360 px. |
| Priorité | Moyenne |

## Intégration au sprint 27

Les actions 1 à 5 sont reprises au planning du sprint 27 ([sprint-goal.md](../sprint-27-fiche-produit-et-erp-fictif/sprint-goal.md), tâches T-TECH-27-01 à 07 de [technical-tasks.md](../sprint-27-fiche-produit-et-erp-fictif/tasks/technical-tasks.md)) : les actions 1 et 2 sont écrites dans `consigne-worker.md` et `checklist-integration.md` dès le planning.

## Points positifs à conserver

- 29 points sur 29 le jour du lancement, avec trois dépôts de plugins publiés ou mis à jour (b2b-mcp v1.2.3, transmission v0.2.0, fichier plat v0.1.0).
- Une maquette validée avant le code, des photos choisies avec Thibaut : aucune reprise de fond.
- Trois retours en production traités le jour même, avec tests.
- Aucun refus contourné, aucune publication sans GO.
