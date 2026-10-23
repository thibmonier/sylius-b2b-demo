# Task Board : Sprint 024 (intégrateurs)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **60 tâches, 39,5 h** pour 16 points engagés ; **38,25 h pour Claude**, 1,25 h d'actions de Thibaut. US-175 (conditionnelle, 3 points) ajoute 10 tâches et 6,25 h si Thibaut donne son GO (70 tâches, 45,75 h au total). État au 2026-10-23 (lancement) : 2 ✅ (actions 1 et 2 de la rétro du sprint 23, faites au planning), 0 🔄, 58 🔲 ou 📅 hors US-175. Voir [tasks/README.md](./tasks/README.md).

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-179-01 | US-179 | [DOC] | Cadrage : choix du conteneur du schéma (région à défilement focusable, nom accessible du schéma, largeur minimale du SVG) et de la … | 0,25 h | Claude |
| T-179-02 | US-179 | [TEST] | Tests rouges, commités seuls et cités dans la PR : conteneur focusable au nom du schéma, police rendue d'au moins 11 px CSS calculée à … | 0,5 h | Claude |
| T-179-03 | US-179 | [FE-WEB] | Schéma dans son conteneur à défilement propre ; barre de la démo sans dépassement en français | 0,75 h | Claude |
| T-179-04 | US-179 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w179 …`), ECS (`src tests`), PHPStan max, Rector ; `php bin/proof-check` (aucun fichier de … | 0,25 h | Claude |
| T-179-05 | US-179 | [TEST] | Rendu en production après déploiement : pa11y WCAG2AAA en FR et en EN, 360 px (libellés du schéma et éléments de la barre), 1280 px ; … | 0,5 h | Claude |
| T-179-06 | US-179 | [REV] | Revue (aucune régression sur grand écran) et table « scénario → test » remplie | 0,25 h | Claude |
| T-171-01 | US-171 | [DOC] | Cadrage : format des `composer.json` et des workflows de CI des 25 dépôts, clés publiées, fichier de données, mécanisme de retard … | 0,75 h | Claude |
| T-171-02 | US-171 | [TEST] | Tests rouges, commités seuls et cités dans la PR : collecte (`require` contre `suggest`, versions de la matrice de CI), dépôt illisible … | 1 h | Claude |
| T-171-03 | US-171 | [BE] | Collecteur : lecture de `require`, `suggest` et de la matrice de CI de chaque dépôt (API publique via `gh`), écriture de … | 1,25 h | Claude |
| T-171-04 | US-171 | [BE] | Contrôle de retard branché sur `bin/proof-check` (même mécanisme qu'US-168) ; lecteur des données, route localisée et contrôleur | 0,75 h | Claude |
| T-171-05 | US-171 | [FE-WEB] | Gabarit : tableau (PHP, Sylius, Symfony, base de données ; « testé par la CI du plugin » et « testé ensemble dans la démo »), … | 1,5 h | Claude |
| T-171-06 | US-171 | [OPS] | Première génération réelle (`php bin/compat-refresh`, lecture seule, réseau via `gh`), jeu de données commité ; `php … | 0,5 h | Claude |
| T-171-07 | US-171 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w171 …`), ECS (`src tests`), PHPStan max, Rector, lint Twig et YAML | 0,5 h | Claude |
| T-171-08 | US-171 | [TEST] | Rendu en production après déploiement : pa11y WCAG2AAA en FR et en EN, 360 px sans défilement horizontal, graphe lisible ; story en … | 0,75 h | Claude |
| T-171-09 | US-171 | [REV] | Revue (ton neutre, aucune formule « validés ensemble » hors démo, parité FR et EN) et table « scénario → test » remplie | 0,25 h | Claude |
| T-172-01 | US-172 | [DOC] | Cadrage : les quatre exemples réels confirmés (fichier, lignes, commande de vérification essayée : `debug:container`, `debug:form`, … | 1 h | Claude |
| T-172-02 | US-172 | [TEST] | Tests rouges, commités seuls et cités dans la PR : page FR et EN avec quatre cas (exemple et commande chacun), tout chemin cité existe … | 0,75 h | Claude |
| T-172-03 | US-172 | [BE] | Registre des exemples en configuration, route localisée et contrôleur | 0,5 h | Claude |
| T-172-04 | US-172 | [FE-WEB] | Gabarit et textes FR et EN : quatre cas, extrait, lien vers le fichier exact, commande de vérification, encadré « dernier recours » ; … | 2 h | Claude |
| T-172-05 | US-172 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w172 …`), ECS (`src tests`), PHPStan max, Rector ; `php bin/proof-refresh` puis `php … | 0,75 h | Claude |
| T-172-06 | US-172 | [TEST] | Rendu en production après déploiement : pa11y WCAG2AAA en FR et en EN, 360 px ; story en review jusque-là | 0,75 h | Claude |
| T-172-07 | US-172 | [DOC] | Texte du lien vers le guide pour chaque README concerné (FR et EN selon le dépôt) et diff préparé en local, **sans push** | 1 h | Claude |
| T-172-08 | US-172 | [REV] | 📅 Thibaut relit les diffs des README et donne son GO pour les PR (une par dépôt, sans tag) | 0,25 h | Thibaut |
| T-172-09 | US-172 | [OPS] | PR des README après GO, CI de chaque dépôt verte avant le merge | 0,5 h | Orchestrateur |
| T-172-10 | US-172 | [REV] | Revue (exemples réels et à jour, ton neutre, parité FR et EN) et table « scénario → test » remplie | 0,25 h | Claude |
| T-174-01 | US-174 | [OPS] | Branche de la PR #54 récupérée dans une branche d'essai (`gh pr checkout 54`) et rebasée sur `main` (`composer.lock` régénéré, pas … | 0,5 h | Claude |
| T-174-02 | US-174 | [TEST] | Garde rouge, commitée seule et citée dans la PR : `NoDocblockAnnotationsInTestsTest` (aucun `@dataProvider` en docblock, 16 occurrences … | 0,75 h | Claude |
| T-174-03 | US-174 | [TEST] | Assistant `tests/Support/TestArrays` (`map()`, `list()`) et son test d'abord (types réels, échec explicite sur une valeur non conforme), … | 0,75 h | Claude |
| T-174-04 | US-174 | [TEST] | Typage, lot 1 (tests fonctionnels et de fumée : trait `McpAccess`, `ContainerFillFixturesTest`, `PluginsAndHomepageTest`, …) : accès … | 1,25 h | Claude |
| T-174-05 | US-174 | [TEST] | Typage, lot 2 (tests unitaires : `IntegrationMapTest`, `DemoPluginCatalogTest`, `ProofDataFileTest`, `ThemeHooksTest`, … | 1,25 h | Claude |
| T-174-06 | US-174 | [TEST] | API de PHPUnit 13 : `OrderPoReferenceTest` (`with()` sans `expects()` devient un stub avec `willReturnMap`), 16 `@dataProvider` passés en … | 1,25 h | Claude |
| T-174-07 | US-174 | [TEST] | Passe de CI sur la branche : suite complète sous PHPUnit 13 avec `failOnNotice` ; mocks sans attente convertis en `createStub()` … | 1 h | Claude |
| T-174-08 | US-174 | [TEST] | `php bin/proof-refresh` puis `php bin/proof-check` (nouveaux fichiers de tests), ECS (`src tests`), PHPStan max et Rector rejoués sur … | 0,5 h | Claude |
| T-174-09 | US-174 | [REV] | Revue (aucun attendu de test modifié hors typage, aucun commentaire supprimé par erreur, règle 9) et table « scénario → test » … | 0,25 h | Claude |
| T-174-10 | US-174 | [OPS] | CI verte de la PR #54 (« Quality and smoke tests », « Docker image »), run terminé, puis merge **en dernier** de la liste des PR du … | 0,25 h | Orchestrateur |
| T-174-11 | US-174 | [REV] | 📅 Thibaut donne son accord seulement en cas de repli (fermeture de la PR #54 et Dependabot réglé pour ignorer les majeures de … | 0,25 h | Thibaut |
| T-132-01 | ⚠️ US-132 | [DOC] | Cadrage : questions du spike, liste des sources officielles à lire (calendrier, formats, annuaire, spécifications externes), grille de … | 0,5 h | Claude |
| T-132-02 | ⚠️ US-132 | [DOC] | Lecture des sources officielles et note datée : échéances, formats, flux de facturation contre e-reporting, rôle des plateformes … | 1,25 h | Claude |
| T-132-03 | ⚠️ US-132 | [DOC] | Écart avec le plugin `facturx` : ce qu'il couvre (génération Factur-X) et ce qui manque (transmission, statuts, e-reporting), lecture de … | 0,75 h | Claude |
| T-132-04 | ⚠️ US-132 | [DOC] | Démonstration sans plateforme réelle : flux de la commande à la facture Factur-X puis dépôt vers une « plateforme fictive » nommée … | 0,75 h | Claude |
| T-132-05 | ⚠️ US-132 | [DOC] | ADR 0016 : décision, périmètre, découpage en stories de 8 points au plus, questions ouvertes ; table « scénario → preuve » remplie … | 0,75 h | Claude |
| T-132-06 | ⚠️ US-132 | [REV] | 📅 Thibaut relit l'ADR et décide (périmètre, stories à planifier) | 0,25 h | Thibaut |
| T-132-07 | ⚠️ US-132 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml`, ligne de l'ADR dans `docs/adr/index.md`, stories de réalisation créées au backlog | 0,25 h | Orchestrateur |
| T-130-01 | ⚠️ US-130 | [DOC] | Cadrage : contrat d'événements de `webhook-outbox` relu, questions du spike, liste des ERP cibles et des capacités à sourcer, plan de … | 0,5 h | Claude |
| T-130-02 | ⚠️ US-130 | [DOC] | Relevé sourcé des capacités d'import et d'export de chaque ERP cible (documentation publique de l'éditeur, date de consultation) ; … | 1,25 h | Claude |
| T-130-03 | ⚠️ US-130 | [DOC] | Contrats : export de commandes (événements puis fichier plat), import de prix et de stock (format, API), journal des échanges, reprise … | 0,75 h | Claude |
| T-130-04 | ⚠️ US-130 | [DOC] | Démonstration sans ERP réel : fichier plat et `webhook-outbox`, ERP fictif nommé comme tel, schéma des échanges | 0,75 h | Claude |
| T-130-05 | ⚠️ US-130 | [DOC] | ADR 0017 : format, contrats, périmètre (avec périmètre réduit si trop large), découpage en stories de 8 points au plus ; table « … | 0,75 h | Claude |
| T-130-06 | ⚠️ US-130 | [REV] | 📅 Thibaut relit l'ADR et décide (périmètre, stories à planifier) | 0,25 h | Thibaut |
| T-130-07 | ⚠️ US-130 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml`, ligne de l'ADR dans `docs/adr/index.md`, stories de réalisation créées au backlog | 0,25 h | Orchestrateur |
| T-TECH-24-03 | Transverse | [OPS] | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés (environ 6 Go libres, seuil de 5 Go) ; un seul serveur … | 0,5 h | Orchestrateur |
| T-TECH-24-04 | Transverse | [OPS] | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push ; dernière ligne de sortie citée | 0,75 h | Orchestrateur |
| T-TECH-24-05 | Transverse | [OPS] | CI verte avant chaque merge, pour la PR du sprint et pour la PR #54 (« Quality and smoke tests », « Docker image »), run terminé ; si … | 0,5 h | Orchestrateur |
| T-TECH-24-06 | Transverse | [REV] | Avant chaque passage en done : table « scénario → test ou preuve » complète (action 1 du sprint 22), commit de tests rouges cité … | 0,75 h | Orchestrateur |
| T-TECH-24-07 | Transverse | [DOC] | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge (action 5 du sprint 22) | 0,25 h | Orchestrateur |
| T-TECH-24-08 | Transverse | [OPS] | Preuve publiée : toute PR qui ajoute ou supprime un fichier de tests embarque `php bin/proof-refresh` puis `php bin/proof-check` (US-171, … | 0,5 h | Orchestrateur |
| T-TECH-24-09 | Transverse | [OPS] | 📅 Thibaut déploie `main` sur Railway après le merge de la PR du sprint, pour la vérification du rendu en production (US-171, US-172, … | 0,25 h | Thibaut |
| T-TECH-24-10 | Transverse | [DOC] | Revue et rétrospective du sprint 24 : indicateurs de la rétro du sprint 23 (commentaires supprimés par erreur, commandes de fond … | 1 h | Orchestrateur |

### Sous condition : US-175 (GO de Thibaut pour les deux dépôts et noms des paquets validés, T-175-01)

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-175-01 | ⚠️ US-175 | [OPS] | 📅 Thibaut donne son GO pour créer les deux dépôts et valide les noms des paquets ; condition d'entrée de la story | 0,25 h | Thibaut |
| T-175-02 | ⚠️ US-175 | [DOC] | Cadrage : noms validés, bornes `^` des 10 plugins lues sur Packagist (`config/demo/proof.json`), `suggest`, gabarit de README, chaîne de … | 0,5 h | Claude |
| T-175-03 | ⚠️ US-175 | [TEST] | Tests rouges, commités seuls et cités dans la PR de chaque dépôt : `bin/check-composition` (membres exacts du pack, plugin classé « … | 1 h | Claude |
| T-175-04 | ⚠️ US-175 | [BE] | Dépôt « IA et headless » en local : `composer.json` métapaquet, vérificateur de composition, CI, LICENSE | 1 h | Claude |
| T-175-05 | ⚠️ US-175 | [BE] | Dépôt « Conformité et outillage » en local, mêmes fichiers | 0,75 h | Claude |
| T-175-06 | ⚠️ US-175 | [DOC] | README FR et EN de chaque pack : pour chaque plugin le bundle, la configuration, les routes et les migrations ; ton neutre | 1,25 h | Claude |
| T-175-07 | ⚠️ US-175 | [TEST] | `composer validate --strict` et résolution Composer dans un projet jetable hors dépôt (`composer update --no-install --no-scripts … | 0,5 h | Claude |
| T-175-08 | ⚠️ US-175 | [REV] | Revue (composition conforme à l'ADR, aucune mention d'offre commerciale, parité FR et EN) et table « scénario → preuve » remplie | 0,25 h | Claude |
| T-175-09 | ⚠️ US-175 | [OPS] | Après GO explicite : création des deux dépôts GitHub avec leurs topics (ecommerce, sylius, sylius-plugin, symfony et topics du pack), … | 0,5 h | Claude |
| T-175-10 | ⚠️ US-175 | [OPS] | 📅 Thibaut soumet les deux paquets à Packagist | 0,25 h | Thibaut |

## 🔄 En Cours

_Aucune tâche._

## 👀 En Review

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-24-01 | Transverse | [DOC] | Règle 9 de `project-management/templates/consigne-worker.md` : lecture sans filtrage (outil Read ou `rtk proxy cat`) avant de modifier un … | 0,25 h | Orchestrateur |
| T-TECH-24-02 | Transverse | [DOC] | Section « Pièges connus » de `docs/tests-locaux.md` complétée : `rm` aliasé, `sed -E` sur macOS, sortie de test écrite dans un … | 0,25 h | Orchestrateur |
