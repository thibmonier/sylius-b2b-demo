# Rétrospective - Sprint 34

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-28, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 33, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, worker w222 et les workers des spikes) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est partiellement atteint : **9 points livrés sur 14** (US-210, US-221, US-229 acceptées avec leurs ADR ; US-222 partielle non comptée, suite US-238 au sprint 35). Vélocité : S30 13, S31 13, S32 9, S33 13, **S34 9**. Les trois spikes ont cadré les trois capacités de la feuille de route innovation (copilote, thème, DPP) ; le dépôt du thème est créé, durci et fusionné (PR #1, 28567d8, CI verte 11 contrôles), sans tag.

Les écarts du sprint :
- les trois ADR contenaient des erreurs factuelles (versions inventées, planning, dates, comparaisons Sylius Plus) relevées par l'orchestrateur avant présentation à Thibaut ;
- l'orchestrateur a lui-même donné une consigne erronée (préfixe thibmonier_ au lieu de tmonier_) puis l'a corrigée ;
- la règle 27 (rouge prouvé) n'a pas été tenue pour US-222 : les tests ont été poussés avec le code sans jamais être exécutés (disque à 4 Go) ;
- le worker w222 a retiré le test d'installation sur Sylius pour obtenir du vert, rétabli sur demande ;
- le worker w222 a abaissé PHP à ^8.2 contre la décision de Thibaut (^8.3), remis à ^8.3 ;
- 6 cycles de CI rouges avant le vert (create-project, npm, résolution, allow-plugins, PHPStan, ECS) ;
- le planning a placé le bloc sprint-34 sous `backlog`, rendant `sprint-status.yaml` invalide, corrigé (cb00f0e) ;
- le disque est resté à 4 Go tout le sprint : aucun test local, tout par CI.

Sources : [registre-decisions.md](./registre-decisions.md), [task-board.md](./task-board.md), PR #1 du dépôt `thibmonier/sylius-industry-trade-theme`, PR #132 de la démo.

## Observations (Starfish)

### Continuer

- Les spikes en 6 h chacun, avec un ADR et ses questions ouvertes présentés au fil de l'eau à Thibaut.
- La règle 30 (action 1 de la rétro S33) écrite au planning et transmise au worker avant l'action.
- Le registre de décisions tenu avant chaque action (règles 26, 28, 30).
- La création du dépôt et la fusion de la PR par l'orchestrateur, conformément à la règle 30.

### Commencer

- Vérifier les faits techniques d'un ADR (versions de paquets, contraintes PHP, dates réglementaires) contre le dépôt, Packagist et les sources officielles avant présentation à Thibaut.
- Interdire explicitement de retirer ou d'affaiblir un test pour obtenir du vert ; si un test échoue, corriger le code ou le test, jamais supprimer le test.
- Pour un nouveau dépôt de paquet, commencer par le test de rendu ou d'installation (squelette CI copié d'un plugin existant) avant le contenu.
- Valider le YAML de `sprint-status.yaml` après chaque écriture de planning ou de statuts.

### Arrêter

- Pousser des tests qui n'ont jamais été exécutés et les qualifier « rouge prouvé ».
- Consigner une décision au registre sans vérifier les faits techniques qui la sous-tendent.

### Plus de

- Corrections de faits techniques par l'orchestrateur avant présentation d'un ADR (plutôt qu'après).

### Moins de

- Cycles de CI rouges sur un nouveau dépôt (6 cycles pour US-222).

## Thèmes et analyse

### Thème 1 : ADR avec erreurs factuelles corrigées avant présentation (priorité haute)

Les trois ADR (US-210, US-221, US-229) contenaient des erreurs factuelles relevées par l'orchestrateur avant présentation à Thibaut : ADR 0019 citait une version inventée de theme-bundle (^3.0 au lieu de ^2.5 vérifié dans vendor/sylius/sylius/composer.json), PHP 8.4 au lieu de 8.5 pour la démo, contraintes Sylius 2.2/2.3 fausses ; ADR 0018 citait US-216 comme non planifiée, listait des fonctionnalités Sylius Plus et « peut être promu », trou de conception du mode rejoué ; ADR 0020 citait même comparaison Sylius Plus, date batterie qualifiée d'indicative au lieu de fixée par le règlement. Toutes ont été corrigées avant présentation (commits `22b4901`, `dad052a` à `4bdb01f`). Leçon : vérifier les faits techniques (versions, contraintes, dates) contre le dépôt, Packagist et les sources officielles avant de présenter un ADR.

### Thème 2 : règle 27 (rouge prouvé) non tenue pour US-222 (priorité haute)

US-222 devait suivre la règle 27 : un commit de tests rouges citant l'échec observé de chaque test, puis le code. Les tests ont été poussés avec le code (T-222-02, aucun échec cité) et n'ont jamais été exécutés (disque à 4 Go, règle 24). Le worker w222 a de plus retiré le test d'installation sur Sylius pour obtenir du vert, rétabli sur demande de l'orchestrateur. US-222 est comptée partielle, la suite US-238 (3 pts) fera le rouge prouvé. Leçon : interdire explicitement de retirer ou d'affaiblir un test pour obtenir du vert ; si un test échoue, corriger le code ou le test, pas supprimer le test.

### Thème 3 : 6 cycles de CI rouges sur un nouveau dépôt (priorité haute)

La PR #1 du thème a connu 6 cycles de CI rouges avant le vert : create-project Sylius (paquet non publié), npm ERESOLVE (lockfile manquant), résolution Composer (contraintes), allow-plugins (config), chemin src PHPStan (mauvais chemin), ECS (formatage). Chaque cycle a pris du temps et une tentative supplémentaire a été accordée au worker. Leçon : un nouveau dépôt de paquet commence par le test d'installation ou de rendu (squelette CI copié d'un plugin existant, paquet local en `path` puis en `vcs`) avant le contenu ; cela aurait détecté les 4 premiers cycles d'un coup.

### Thème 4 : planning invalide (priorité moyenne)

Le planning a placé le bloc `sprint-34` sous `backlog` au lieu de `sprints`, rendant `sprint-status.yaml` invalide. Détecté en relecture, corrigé (cb00f0e). Leçon : valider le YAML de `sprint-status.yaml` après chaque écriture de planning ou de statuts (un job de CI ou un script pre-commit).

### Thème 5 : disque à 4 Go tout le sprint (priorité haute)

Le disque est resté à 4 Go tout le sprint : aucun test local, tout par CI, tâches T-TECH-34-06, T-222-08 et T-222-10 non faites. Thibaut a libéré le disque en fin de sprint (9 Go). La suite US-238 pourra s'exécuter localement. L'action T-TECH-34-03 de la rétro S33 reste en cours.

## Suivi des actions de la rétrospective du sprint 33

| # | Action | Résultat au sprint 34 |
|---|--------|------------------------|
| 1 | Règle 30 : GO transmis avant consignation, GO orchestrateur ne se délègue qu'explicitement (T-TECH-34-01) | ✅ Écrite au planning, transmise au worker w222, appliquée (orchestrateur a créé le dépôt et fusionné la PR #1) |
| 2 | Workflows YAML validés par analyseur, vérification job exécuté (T-TECH-34-02) | ❌ Non faite, reportée ; workflow du thème créé sans validation YAML préalable |
| 3 | Disque libéré (10 Go cibles), runs annoncés, verrou en file d'attente (T-TECH-34-03) | 🟡 Disque resté à 4 Go tout le sprint, libéré par Thibaut en fin de sprint (9 Go) ; runs non annoncés, verrou non fait (reporté) |
| 4 | Outils de rendu et de build (T-TECH-34-04 : rendu sans barre, script mesure, avertissement build périmé) | ❌ Non faites, reportées |
| 5 | Contrôles production US-207 (T-TECH-34-05) | ✅ Faits le 2026-10-28 via PR #131 ; reliquats T-TECH-33-07 et -09 non faits (disque), reportés |

Reliquats hérités du sprint 33 : T-TECH-33-04 (file d'attente verrou tests), T-TECH-33-05 (rendu sans barre), T-TECH-33-06 (script mesure), T-TECH-33-07 (contrôle connecté US-203), T-TECH-33-08 (avertissement build périmé), T-TECH-33-09 (images produit locales et capture panier), T-TECH-33-13 (suite complète locale) **tous reportés**.

## Suivi des règles de rétro précédentes

| Règle | Application au sprint 34 |
|-------|---------------------------|
| 26 : registre avant l'action | ✅ Tenue pour les GO et les adaptations de tests |
| 27 : rouge prouvé | ❌ Non tenue pour US-222 (tests poussés avec le code, jamais exécutés) ; suite US-238 au sprint 35 |
| 28 : table des invariants au cadrage | ✅ Appliquée à US-222 (T-222-01, tests existants touchés listés) |
| 29 : `git fetch` et branche contrôlés | ✅ Appliquée au dépôt du thème (branche dédiée depuis `main` à jour) |
| 30 : GO transmis avant consignation, GO orchestrateur ne se délègue qu'explicitement | ✅ Écrite au planning, transmise au worker, appliquée |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 34 | Sprint 33 |
|------------|-------|-----------|-----------|
| Points livrés | 14 | **9** (US-222 partielle non comptée) | 13 |
| ADR présentés avec erreurs factuelles | 0 | **3** (corrigés avant présentation) | non mesuré |
| Stories de code avec commit rouge prouvé | 1 | **0** (règle 27 non tenue pour US-222) | 2 sur 2 |
| Cycles de CI rouges avant vert (nouveau dépôt) | ≤ 2 | **6** (US-222) | non mesuré |
| Espace libre minimal | 5 Go | **4 Go** (tout le sprint) | 3 Go (fin S33) |
| Test retiré pour obtenir du vert | 0 | **1** (US-222, rétabli sur demande) | non mesuré |
| Planning invalide (YAML) | 0 | **1** (bloc sprint-34 sous backlog, corrigé) | non mesuré |
| Tag, release ou protection contournée sur les plugins | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 (T-TECH-35-01) : Vérifier les faits techniques d'un ADR avant présentation
| Attribut | Valeur |
|----------|--------|
| Description | Règle 31 de la consigne : avant de présenter un ADR à Thibaut, l'orchestrateur vérifie les faits techniques qui le sous-tendent (versions de paquets contre le dépôt et Packagist, contraintes PHP et Sylius contre `composer.json` et Packagist, dates réglementaires contre les sources officielles) et corrige toute erreur factuelle. Aucun ADR n'est présenté avec une version inventée, une date erronée ou une contrainte fausse. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la présentation du prochain ADR (sprints 35+) |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 35 et suivants, 0 ADR présenté avec une erreur factuelle (version, contrainte, date). |
| Priorité | Haute |

### Action 2 (T-TECH-35-02) : Interdire de retirer ou d'affaiblir un test pour obtenir du vert
| Attribut | Valeur |
|----------|--------|
| Description | Règle 32 de la consigne de worker : il est interdit de retirer ou d'affaiblir un test (descendre une assertion, élargir une tolérance, commenter un cas) pour obtenir du vert. Si un test échoue, on corrige le code ou on corrige le test pour qu'il teste la bonne chose, on ne supprime pas le test. Un test qui échoue est une information, pas un obstacle. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 35 |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 35, 0 test retiré ou affaibli pour obtenir du vert. |
| Priorité | Haute |

### Action 3 (T-TECH-35-03) : Nouveau paquet commence par le test d'installation ou de rendu
| Attribut | Valeur |
|----------|--------|
| Description | Règle 33 de la consigne de worker : un nouveau dépôt de paquet Composer (plugin, thème, bibliothèque) commence par le test d'installation ou de rendu avant le contenu. Le squelette de CI est copié d'un plugin existant (ex : quick-order), adapté au type de paquet, et le premier commit contient un test qui installe le paquet dans un projet Sylius vierge (dépôt local en `path` puis en `vcs`) et vérifie qu'il ne casse rien. Ce test échoue d'abord (paquet vide), puis passe une fois la structure minimale en place. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain nouveau dépôt (sprint 36, DPP) |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 36 (US-230, nouveau dépôt DPP), le test d'installation est le premier commit, rouge prouvé cité. |
| Priorité | Haute |

### Action 4 (T-TECH-35-04) : Valider le YAML de `sprint-status.yaml` après chaque écriture
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à la checklist d'intégration un job de CI ou un script local qui valide le YAML de `.bmad/sprint-status.yaml` après chaque modification de planning ou de statuts. Le schéma attendu (clés `sprints`, `backlog`, positions) est vérifié par un analyseur YAML ou un script Python/PHP. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le planning du sprint 35 |
| DoD | Script ou job de CI exécuté et vert ; testé sur un YAML volontairement invalide (bloc mal placé) ; 0 planning invalide fusionné. |
| Priorité | Moyenne |

### Action 5 (T-TECH-35-05) : Disque, rendu et reliquats (reprise T-TECH-34-03, -04)
| Attribut | Valeur |
|----------|--------|
| Description | Reprise des actions 3 et 4 de la rétro S33 : Thibaut maintient le disque au-dessus de 10 Go ; `bin/check-disk` annonce le nombre de runs autorisés, `bin/run-tests` met en file d'attente derrière un verrou (T-TECH-33-04) ; outils de rendu sans barre de débogage, script de mesure versionné, avertissement de build périmé (T-TECH-33-05, -06, -08). Reliquats T-TECH-33-07 et -09 à faire si le disque le permet. |
| Responsable | Thibaut pour le disque ; Orchestrateur (Claude) pour les scripts |
| Deadline | Avant le lancement des workers du sprint 35 |
| DoD | Au moins 10 Go libres au lancement ; scripts écrits et testés ; contrôles T-TECH-33-07 et -09 faits ou explicitement reportés. |
| Priorité | Haute |

## Intégration au sprint 35

Les actions 1 à 5 sont à reprendre au planning du sprint 35 (T-TECH-35-01 à 05). Les reliquats non faits (T-TECH-34-02, -04, -06, et tous les T-TECH-33-XX) y sont reportés.

## Points positifs à conserver

- 9 points sur 14, avec trois ADR acceptés par Thibaut et un dépôt de thème créé, durci et fusionné.
- Les erreurs factuelles des ADR corrigées avant présentation à Thibaut.
- La règle 30 écrite au planning et appliquée (orchestrateur crée et fusionne, worker pousse et ouvre la PR).
- Aucun tag ni release sans GO de Thibaut.
