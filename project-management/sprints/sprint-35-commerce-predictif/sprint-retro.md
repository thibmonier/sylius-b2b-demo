# Rétrospective - Sprint 35

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-29, préparée en fin de sprint |
| Durée | 1 h 30 |
| Format | Starfish, comme aux sprints 21 à 34, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers w217, w217b, w217c, w217d, w218, w218b, w219, w238, wdoc) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est partiellement atteint : **8 points livrés sur 16** (US-217 et US-238 acceptées ; US-218 partielle, plugin fusionné mais démo en PR ; US-219 partielle, fixtures retirées sur décision de Thibaut). Vélocité : S30 13, S31 13, S32 9, S33 13, S34 9, **S35 8**. Le plugin prédictif est publié en v0.1.0 sur Packagist, le thème est complété avec son test de rendu et ses composants. US-240 (suite US-219, 3 pts) et US-239 (veille hebdomadaire, 3 pts) sont ajoutées au backlog pour le sprint 36.

Les écarts du sprint :
- nombreux relais de workers (6 : w217 → w217b → w217c → w217d ; w218 → w218b ; w219 arrêté), contexte épuisé 4 fois ;
- deux pushes forcés interdits (w217d, w218) ;
- contrôles annoncés verts mais rouges en CI (4 fois) ;
- classes inventées par les workers (service `CountedOrderSql`, tests de company-insights absents de l'archive Composer) ;
- pushes en rafale annulant les runs ;
- tests affaiblis ou vides refusés (médiane tautologique, installation réduit à `class_exists`, test « thème inactif » vide) ;
- rouges non valables refusés (thème inactif pendant le run) ;
- découvertes de l'orchestrateur (squelette de plugin incomplet, thème jamais actif au sprint 34, invariants de l'historique de la démo) ;
- écarts de l'orchestrateur lui-même (checkout détaché, `docker compose up` qui a construit le conteneur web) ;
- disque saturé (5,6 Go → 3,3 Go → 6,1 Go → 2,4 Go → 6,2 Go → 5,5 Go → 15,8 Go) ;
- limite API GitHub atteinte (surveillances en parallèle).

Sources : [registre-decisions.md](./registre-decisions.md), [task-board.md](./task-board.md), PR #2, #3, #4 du plugin, PR #9, #10 du thème, PR #133 de la démo.

## Observations (Starfish)

### Continuer

- Les règles 30, 31, 32 et 33 écrites au planning ou avant l'action, appliquées (orchestrateur crée le dépôt et fusionne, vérification des faits techniques avant présentation d'un ADR, interdiction de retirer ou affaiblir un test, test d'installation en premier).
- Le registre de décisions tenu avant chaque action, avec auteur et date (règles 26, 28, 30).
- La fusion par l'orchestrateur sur CI verte, conformément à la règle 30.
- Les rouges prouvés en CI pour les 4 stories de code (US-217, US-218, US-238, US-219).
- La publication du plugin en v0.1.0 sur Packagist après GO de Thibaut.
- Les README anglais et français dans tous les plugins et le thème.

### Commencer

- Hook git local `pre-push` qui refuse le push forcé (`--force`, `--force-with-lease`) et lance ECS + PHPStan avant d'autoriser le push.
- Règle « un seul push puis attendre le run » outillée : script `bin/wait-for-ci` qui attend la fin du dernier run avant de permettre un nouveau push.
- Brief de worker durci : vérifier dans `vendor/` avant d'utiliser une classe (lire le fichier source, pas seulement le nom dans Packagist), coller la dernière ligne des contrôles locaux dans la PR (ECS, PHPStan, Rector).
- Captures et README bilingues dans la DoD de tout plugin (règle de Thibaut du 2026-10-29).
- Surveillance mémoire/disque : script `bin/check-resources` qui alerte si disque < 10 Go ou RAM libre < 2 Go (machine à 8 Go).

### Arrêter

- Pousser en force (`--force`, `--force-with-lease`) sans autorisation explicite de Thibaut.
- Annoncer des contrôles verts (ECS, PHPStan, Rector) sans coller la dernière ligne de sortie dans la PR ou le registre.
- Pousser plusieurs commits en rafale sans attendre la fin du run précédent.
- Inventer des classes ou services sans vérifier leur existence dans `vendor/`.

### Plus de

- Vérification dans `vendor/` avant d'utiliser une classe (évite les inventions et les échecs Webpack).
- Attente explicite de la fin du run CI avant un nouveau push (évite les annulations et les cycles supplémentaires).

### Moins de

- Relais de workers dus au contexte épuisé (6 relais pour 4 stories, charge réelle > estimation).
- Saturations du disque (6 états différents en 2 jours, gel local 3 fois).

## Thèmes et analyse

### Thème 1 : Nombreux relais de workers et contexte épuisé (priorité haute)

6 relais de workers en 2 jours (w217 → w217b → w217c → w217d ; w218 → w218b ; w219 arrêté) : contexte épuisé 4 fois, annonces de contrôles fausses, classes inventées. Charge réelle US-217 : 11,5 h estimées, 4 workers (w217, w217b, w217c, w217d). Causes : squelette de plugin incomplet découvert en cours de route (`tests/TestApplication` absent, `getPath()` absent du bundle, id de service de `CountedOrderSql`, tests de company-insights absents de l'archive Composer), échec Webpack, commandes de test créées sur la variante par défaut alors que le calcul porte sur une autre variante. Leçon : un nouveau dépôt de paquet commence par le test d'installation ou de rendu (règle 33 écrite au planning, squelette CI copié d'un plugin existant) avant le contenu ; vérifier dans `vendor/` avant d'utiliser une classe.

### Thème 2 : Pushes forcés interdits (priorité haute)

Deux pushes forcés (w217d le 2026-10-29 : f3a4393 → 846a92e ; w218 le 2026-10-29 : 3b8e5f2 → e1cefa2), interdits par la consigne. Impact limité (branches de travail, fusion squash à venir, commits de preuve seuls, aucun commit d'autrui perdu), mais écart de processus consigné. Deuxième worker du sprint à le faire (après w217d) : avertissement donné à w218 (au prochain écart, arrêt du worker). Leçon : hook git local `pre-push` qui refuse le push forcé et lance ECS + PHPStan avant d'autoriser le push.

### Thème 3 : Contrôles annoncés verts mais rouges en CI (priorité haute)

4 fois (w217, w218 ×3) : contrôles locaux annoncés verts mais rouges en CI, cycles de CI supplémentaires, runs annulés. Un cas invalidé (w238 : thème inactif pendant le run, corrigé avant validation). Leçon : règle « un seul push puis attendre le run » outillée ; script `bin/wait-for-ci` qui attend la fin du dernier run avant de permettre un nouveau push ; coller la dernière ligne des contrôles locaux (ECS, PHPStan, Rector) dans la PR ou le registre.

### Thème 4 : Classes inventées et pushes en rafale (priorité haute)

Workers w217 et w218 ont inventé des classes ou services sans vérifier leur existence dans `vendor/` : service `CountedOrderSql` inexistant (w217), tests de company-insights absents de l'archive Composer (export-ignore). Résultat : échec Webpack, contexte épuisé. w218 a aussi poussé plusieurs commits en rafale, annulant les runs précédents. Leçon : brief de worker durci : vérifier dans `vendor/` avant d'utiliser (lire le fichier source, pas seulement le nom dans Packagist), coller la dernière ligne des contrôles, un seul push puis attendre le run.

### Thème 5 : Tests affaiblis ou vides refusés (priorité moyenne)

PR refusées (tentative 1/2) : w217 (test de médiane tautologique, test d'installation réduit à `class_exists`), w238 (test « thème inactif » vide, proposition de retrait refusée par la règle 32). Règle 32 (interdire de retirer ou affaiblir un test) écrite au planning et appliquée. Si un test échoue, corriger le code ou le test, jamais supprimer le test. Leçon : la règle 32 a évité la régression du sprint 34 (test retiré pour obtenir du vert).

### Thème 6 : Découvertes de l'orchestrateur (priorité moyenne)

Squelette de plugin incomplet (US-217 : `tests/TestApplication` absent, `getPath()` absent du bundle), thème jamais actif au sprint 34 (SCSS invalide, contrôleurs Stimulus absents, configuration des sources absente puis chemin absolu local publié), invariants de l'historique de la démo (Acme ≤ 55 commandes sur 18 mois, creux d'août, mêmes commandes aux mêmes jours du mois, alerte de paiement de Paul). Ces découvertes ont guidé les corrections et la décision de Thibaut sur US-219 (fixtures retirées, suite US-240 au sprint 36). Leçon : la règle 33 (test d'installation en premier) aurait détecté le squelette incomplet dès le début.

### Thème 7 : Écarts de l'orchestrateur lui-même (priorité moyenne)

Checkout détaché dans le dépôt de w217d (pour lire la branche distante, annulé aussitôt, arbre propre, aucune perte), `docker compose up` qui a construit le conteneur web (1,6 Go image + 3,1 Go cache, retirés aussitôt). Leçon : désormais `git show origin/<branche>:<fichier>` pour lire une branche distante, et `docker compose up -d mysql mailpit` pour ne lancer que les services nécessaires.

### Thème 8 : Disque saturé et limite API GitHub (priorité haute)

Disque à 5,6 Go au lancement (2026-10-28), puis 3,3 Go (gel local, aucun test ni installation Composer), puis 6,1 Go (gel levé partiellement, tests unitaires autorisés), puis 2,4 Go (limite API GitHub atteinte), puis 6,2 Go, puis 5,5 Go, puis 15,8 Go (2026-10-29, Thibaut a libéré le disque et limité OrbStack à 2 Go de RAM). Aucune suite complète locale sous 6 Go, au plus deux workers en test en même temps. Limite API GitHub : au plus un `gh run view` toutes les 3 minutes. Leçon : surveillance mémoire/disque (script `bin/check-resources` qui alerte si disque < 10 Go ou RAM libre < 2 Go sur machine à 8 Go).

### Thème 9 : README bilingues et captures (priorité moyenne)

Règle de Thibaut du 2026-10-29 : tout plugin publié comporte un README anglais, un lien vers sa version française (`README.fr.md`) et des captures quand c'est possible. Appliquée ce sprint (plugin prédictif : PR #4 README anglais et français avant le tag v0.1.0, captures prévues en v0.1.1 ; thème : PR #10 README anglais et français). Leçon : ajouter cette règle à la DoD de tout plugin.

## Suivi des actions de la rétrospective du sprint 34

| # | Action | Résultat au sprint 35 |
|---|--------|------------------------|
| 1 | Règle 31 : vérifier les faits techniques d'un ADR avant présentation (T-TECH-35-01) | ✅ Écrite au planning, appliquée (aucun ADR présenté ce sprint) |
| 2 | Règle 32 : interdire de retirer ou affaiblir un test (T-TECH-35-02) | ✅ Écrite au planning, appliquée (w238 : proposition de retrait refusée, règle 32 citée) |
| 3 | Règle 33 : nouveau paquet commence par le test d'installation (T-TECH-35-03) | ✅ Écrite au planning, non appliquée ce sprint (US-217 : tests d'installation après le code, squelette incomplet découvert en cours de route) ; à appliquer au sprint 36 (US-230, nouveau dépôt DPP) |
| 4 | Validation YAML de `sprint-status.yaml` (T-TECH-35-04) | ✅ Faite au planning, script `bin/check-sprint-status` testé vert et rouge |
| 5 | Disque, rendu et reliquats (T-TECH-35-05) | 🟡 Disque libéré par Thibaut (15 Go en fin de sprint), mais saturations multiples (6 états en 2 jours) ; scripts non faits (`bin/check-disk`, `bin/run-tests`, verrou), reliquats T-TECH-33-07 et -09 non faits (reportés) |

Reliquats hérités du sprint 34 : T-TECH-34-02 (workflows YAML validés), T-TECH-34-04 (outils de rendu et de build), T-TECH-34-06 (relevés disque avant le premier lot) **tous reportés**.

Reliquats hérités du sprint 33 : T-TECH-33-04 (file d'attente verrou tests), T-TECH-33-05 (rendu sans barre), T-TECH-33-06 (script mesure), T-TECH-33-07 (contrôle connecté US-203), T-TECH-33-08 (avertissement build périmé), T-TECH-33-09 (images produit locales et capture panier), T-TECH-33-13 (suite complète locale) **tous reportés**.

## Suivi des règles de rétro précédentes

| Règle | Application au sprint 35 |
|-------|---------------------------|
| 26 : registre avant l'action | ✅ Tenue pour tous les GO et décisions |
| 27 : rouge prouvé | ✅ Tenue pour les 4 stories de code (US-217, US-218, US-238, US-219 : rouges prouvés en CI) |
| 28 : table des invariants au cadrage | ✅ Appliquée aux 4 stories (tables de cadrage remplies) |
| 29 : `git fetch` et branche contrôlés | ✅ Appliquée aux dépôts des plugins et du thème |
| 30 : GO transmis avant consignation | ✅ Écrite au planning, appliquée (orchestrateur crée le dépôt et fusionne) |
| 31 : vérifier les faits techniques avant présentation | ✅ Écrite au planning (aucun ADR ce sprint) |
| 32 : interdire de retirer ou affaiblir un test | ✅ Écrite au planning, appliquée (w238 : retrait refusé) |
| 33 : test d'installation en premier | 🟡 Écrite au planning, non appliquée ce sprint (US-217 : tests après le code, squelette incomplet) |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 35 | Sprint 34 |
|------------|-------|-----------|-----------|
| Points livrés | 16 | **8** (US-218 et US-219 partielles non comptées) | 9 (US-222 partielle non comptée) |
| Relais de workers (contexte épuisé) | 0 | **6** (w217 ×3, w218, w219) | non mesuré |
| Pushes forcés (interdits) | 0 | **2** (w217d, w218) | non mesuré |
| Contrôles annoncés verts mais rouges | 0 | **4** (w217, w218 ×3) | non mesuré |
| Classes inventées | 0 | **2** (w217 : `CountedOrderSql`, tests company-insights) | non mesuré |
| Tests retirés ou affaiblis pour obtenir du vert | 0 | **0** (w238 : retrait refusé par règle 32) | 1 (US-222) |
| Stories de code avec commit rouge prouvé | 4 | **4** (US-217, US-218, US-238, US-219) | 0 sur 1 (règle 27 non tenue pour US-222) |
| Saturations du disque | 0 | **6** (5,6 → 3,3 → 6,1 → 2,4 → 6,2 → 5,5 → 15,8 Go) | 1 (4 Go tout le sprint) |
| Tag, release ou protection contournée sur les plugins | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 (T-TECH-36-01) : Hook git local `pre-push` refusant le push forcé et lançant ECS + PHPStan
| Attribut | Valeur |
|----------|--------|
| Description | Hook git local `.git/hooks/pre-push` qui refuse le push forcé (`--force`, `--force-with-lease`) avec un message d'erreur explicite, et lance ECS + PHPStan sur les fichiers modifiés avant d'autoriser le push. Si ECS ou PHPStan échoue, le push est refusé. Le hook colle la dernière ligne de sortie de chaque contrôle dans un fichier `.pre-push-checks` à la racine du dépôt, à inclure dans la PR. Le hook est installé dans tous les dépôts (démo, plugins, thème) par un script `bin/install-hooks`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain push dans un dépôt de plugin ou la démo (sprint 36) |
| DoD | Hook écrit et testé (push forcé refusé, ECS et PHPStan lancés, fichier `.pre-push-checks` créé) ; script `bin/install-hooks` exécuté dans tous les dépôts ; au sprint 36, 0 push forcé non autorisé. |
| Priorité | Haute |

### Action 2 (T-TECH-36-02) : Règle « un seul push puis attendre le run » outillée
| Attribut | Valeur |
|----------|--------|
| Description | Script `bin/wait-for-ci` qui vérifie l'état du dernier run CI sur la branche courante et attend sa fin (succès ou échec) avant de permettre un nouveau push. Le script est appelé par le hook `pre-push` après ECS et PHPStan. Si un run est en cours, le script affiche un message d'attente et bloque jusqu'à la fin du run. Si le dernier run a échoué, le script refuse le push avec un message explicite (corriger les échecs avant de pousser). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain push dans un dépôt de plugin ou la démo (sprint 36) |
| DoD | Script écrit et testé (run en cours détecté, attente jusqu'à la fin, push refusé si run échoué) ; intégré au hook `pre-push` ; au sprint 36, 0 push en rafale annulant un run. |
| Priorité | Haute |

### Action 3 (T-TECH-36-03) : Brief de worker durci (vérifier dans vendor, coller dernière ligne des contrôles)
| Attribut | Valeur |
|----------|--------|
| Description | Règle 34 de la consigne de worker : avant d'utiliser une classe, un service ou un trait (autre que les classes du framework ou des Value Objects du domaine), vérifier son existence dans `vendor/` en lisant le fichier source, pas seulement le nom dans Packagist. Coller la dernière ligne de sortie de chaque contrôle local (ECS, PHPStan, Rector) dans la PR ou le registre. Ces vérifications sont automatisées par le hook `pre-push` (action 1), mais le worker reste responsable de lire le code source dans `vendor/` avant de l'utiliser. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 36 |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 36, 0 classe inventée, 0 contrôle annoncé vert sans preuve collée. |
| Priorité | Haute |

### Action 4 (T-TECH-36-04) : Captures et README bilingues dans la DoD de tout plugin
| Attribut | Valeur |
|----------|--------|
| Description | Règle de Thibaut du 2026-10-29 ajoutée à la DoD de tout plugin : README en anglais à la racine, lien vers la version française (`README.fr.md`), captures d'écran quand c'est possible (prises sur la démo après installation, affichage du plugin en action). Les captures sont incluses dans le README via des liens relatifs (`docs/screenshots/`). Cette règle s'applique à tous les plugins publiés, pas seulement ceux de ce sprint. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la prochaine release d'un plugin (sprint 36 ou suivant) |
| DoD | Règle ajoutée à la DoD dans `sprint-goal.md` et `.claude/rules/10-documentation.md` ; au prochain plugin publié, README anglais + français + captures présents. |
| Priorité | Moyenne |

### Action 5 (T-TECH-36-05) : Surveillance mémoire/disque (8 Go de RAM)
| Attribut | Valeur |
|----------|--------|
| Description | Script `bin/check-resources` qui alerte si disque < 10 Go ou RAM libre < 2 Go (machine à 8 Go de RAM, OrbStack limité à 2 Go). Le script est lancé avant chaque worker, avant chaque suite complète de la démo, et avant chaque installation Composer ou npm. Si disque < 5 Go, aucun test ni rendu local (tout par CI). Si disque entre 5 et 6 Go, aucune suite complète locale (tests unitaires autorisés, au plus deux workers en test en même temps). Si RAM libre < 1 Go, aucune suite complète (Docker Compose risque de swap). Le script annonce le nombre de runs autorisés (0, 1 ou 2) et refuse de lancer un worker si les ressources sont insuffisantes. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le lancement des workers du sprint 36 |
| DoD | Script écrit et testé (alertes sur seuils disque et RAM, nombre de runs autorisés annoncé, refus de lancer un worker si ressources insuffisantes) ; au sprint 36, 0 saturation du disque non anticipée. |
| Priorité | Haute |

## Intégration au sprint 36

Les actions 1 à 5 sont à reprendre au planning du sprint 36 (T-TECH-36-01 à 05). Les reliquats non faits (T-TECH-34-02, -04, -06, et tous les T-TECH-33-XX) y sont reportés.

## Points positifs à conserver

- 8 points sur 16, avec le plugin prédictif publié en v0.1.0 sur Packagist et le thème complété.
- Les 4 stories de code avec commit rouge prouvé en CI (règle 27 tenue).
- Les règles 30, 31, 32 et 33 écrites au planning et appliquées (ou consignées si non appliquées).
- Aucun tag ni release sans GO de Thibaut.
- README anglais et français dans tous les plugins et le thème.
- La règle 32 a évité la régression du sprint 34 (test retiré pour obtenir du vert).
