# Tâches : US-178 : Page « Installer un pack » dans la démo

## Informations US

- **Epic** : EPIC-14
- **Persona** : lead développeur d'une agence ; Camille (recruteuse IT)
- **Story Points** : 2 (Should, non sacrifiable)
- **Sprint** : sprint-25-packs-et-transmission
- **Story** : [US-178](../../../backlog/user-stories/US-178.md)

## Résumé de la US

**En tant que** lead développeur
**Je veux** voir dans la démo quels packs existent et comment les installer
**Afin de** choisir entre un pack et des plugins un par un

## Règles et contraintes

- Page FR et EN, route `/{locale}/packs`, liée depuis le guide d'intégration (`/{locale}/integrer`) et la page Architecture (`templates/demo/architecture.html.twig`, libre depuis la fin d'US-179).
- Contenu **tiré des `composer.json` des métapaquets et de leurs README, jamais recopié à la main** : fichier de données généré (`config/demo/packs.json`, commande de rafraîchissement en lecture seule via `gh`, dernière valeur gardée et datée si un dépôt est illisible), même mécanisme de retard que la preuve (US-168) et la matrice (US-171), branché sur `bin/proof-check`.
- Chaque pack : ses plugins (depuis son `composer.json`), sa commande d'installation, les gestes restants ; un plugin qui n'est dans aucun pack renvoie vers l'installation un par un (guide Intégrer, page Plugins).
- Le test de la page échoue si la liste affichée diffère de celle du fichier de données, et le fichier est comparé aux `composer.json` des clones locaux quand ils sont présents.
- Ton neutre : `PublicTextCharter` (aucune comparaison, aucun prix, aucune mention d'offre commerciale) ; aucun plugin « concurrent » ou « à confirmer » ; aucun détail de sécurité.
- Les gestes restants affichés viennent des README **après** le merge d'US-176 (qui peut les corriger) : la génération réelle des données se fait après ce merge.
- Aucun nouveau dépôt, aucun push dans un dépôt de pack : la story ne touche que la démo.
- **Règle 10** : `git` (commits de la branche du sprint) dans l'arbre principal.
- Tests de la démo uniquement : `WORKER_ID=w178 TEST_DB_NAME=b2b_w178 TEST_CACHE_DIR=var/cache-w178 bin/run-tests`. Un seul serveur lourd à la fois.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 24 : voir [README.md](./README.md).

## Lieu de vérification du rendu (action 4 de la rétro du sprint 23)

**Production, après le déploiement automatique de `main` par Railway, en lecture seule** (environ 7 Go libres : l'image de la démo ne se construit pas en local, seuil de 10 Go). Pas de tâche de Thibaut : Railway déploie `main` à chaque merge, aucune commande Railway par Claude. La story reste en review jusqu'à la preuve : pa11y WCAG2AAA sur `/fr_FR/packs` et `/en_US/packs`, mesure à 360 px (sans défilement horizontal) et à 1280 px, liens depuis `/integrer` et `/architecture`, selon [docs/tests-locaux.md](../../../../docs/tests-locaux.md), « Vérifier le rendu en production ». Si Thibaut libère au moins 10 Go avant la fin du sprint, `bin/measure-mobile` peut remplacer la mesure de production avant le merge.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `src/Demo/Packs/` (collecte, lecture, contrôle de retard), `bin/packs-refresh` (nouveau), `bin/proof-check`
- `config/demo/packs.json` (nouveau), `config/packages/demo_packs.yaml` (nouveau)
- `src/Demo/Controller/PacksController.php` (nouveau), `templates/demo/packs.html.twig` (nouveau), `translations/packs.fr.yaml` et `.en.yaml` (nouveaux)
- `templates/demo/integrate.html.twig`, `templates/demo/architecture.html.twig` (un lien chacun), `translations/` des deux pages
- `tests/Unit/Demo/Packs/`, `tests/Functional/Demo/PacksPageTest.php`
- `config/demo/proof.json` (rafraîchi : nouveaux fichiers de tests)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-178-01 | [DOC] | Cadrage : forme du fichier de données (plugins, commande, gestes restants, source et date), mécanisme de retard repris de la matrice (US-171), route, textes d'accès depuis Intégrer et Architecture, renvoi des plugins hors pack, lieu de vérification | 0,5 h | - | Claude | 🔲 |
| T-178-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : page FR et EN (packs, plugins, commande, gestes restants), liste de la page égale à celle du fichier, fichier égal aux `composer.json` des clones locaux, dépôt illisible (dernière valeur datée), lien depuis Intégrer et Architecture, plugin hors pack renvoyé vers l'installation un par un, charte publique sans comparaison ni prix | 0,75 h | T-178-01 | Claude | 🔲 |
| T-178-03 | [BE] | Collecteur et lecteur des données, commande `bin/packs-refresh` (lecture seule via `gh`), contrôle de retard branché sur `bin/proof-check`, route localisée et contrôleur | 0,75 h | T-178-02 | Claude | 🔲 |
| T-178-04 | [FE-WEB] | Gabarit et textes FR et EN (packs, plugins, commande d'installation, gestes restants), liens depuis les pages Intégrer et Architecture | 0,75 h | T-178-03 | Claude | 🔲 |
| T-178-05 | [OPS] | Première génération réelle après le merge d'US-176 (`php bin/packs-refresh`, jeu de données commité) ; `php bin/proof-refresh` puis `php bin/proof-check` (nouveaux fichiers de tests) | 0,5 h | T-178-04, T-176-13 | Claude | 🔲 |
| T-178-06 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w178 …`), ECS (`src tests`), PHPStan max, Rector, lint Twig et YAML | 0,5 h | T-178-05 | Claude | 🔲 |
| T-178-07 | [TEST] | Rendu en production après le déploiement automatique de `main` : pa11y WCAG2AAA en FR et en EN, 360 px sans défilement horizontal, 1280 px, liens d'accès ; story en review jusque-là | 0,75 h | T-178-06, merge de la PR du sprint | Claude | 🔲 |
| T-178-08 | [REV] | Revue (ton neutre, liste lue et non recopiée, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-178-07 | Claude | 🔲 |

**Total estimé : 4,75 h** (8 tâches, dont 0 h de Thibaut ; 4,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Packs présentés (nominal) | |
| Accès depuis le guide d'intégration (alternatif) | |
| Plugin seul (alternatif) | |
| Composition en retard (erreur) | |
| Ton commercial (erreur) | |
