# Task Board : Sprint 023 (garde-fous et cadrages)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **40 tâches, 28,25 h** pour 11 points ; **27,5 h pour Claude**, 0,75 h d'actions de Thibaut. État au 2026-10-23 (fin de l'implémentation) : 31 ✅, 4 🔄, 5 🔲. US-170 et US-173 ont remplacé US-132 et US-130 le jour du lancement (critique d'un architecte Sylius et d'un client B2B, décision de Thibaut). Voir [tasks/README.md](./tasks/README.md).

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-169-05 | US-169 | REV | 📅 Thibaut lit le rapport et décide (story de montée ou fermeture de la PR #54) | 0,25 h | Thibaut |
| T-169-06 | US-169 | DOC | Décision consignée dans `.bmad/sprint-status.yaml` ; story de montée créée ou PR #54 fermée avec Dependabot réglé pour ignorer les versions majeures | 0,25 h | Orchestrateur |
| T-173-05 | US-173 | REV | 📅 Thibaut relit l'ADR et décide (forme, packs, stories à planifier) | 0,25 h | Thibaut |
| T-173-06 | US-173 | DOC | Décision consignée dans `.bmad/sprint-status.yaml` et stories de réalisation créées au backlog | 0,25 h | Orchestrateur |
| T-TECH-23-06 | Transverse | DOC | Revue et rétrospective du sprint 23 : indicateurs du sprint 22 (scénarios non livrés, commits rouges, plantages, espace minimal, tâches restées 🔲, con … | 1 h | Orchestrateur |

## 🔄 En Cours

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-170-06 | US-170 | TEST | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile` (360 px sans défilement horizontal) ; suite via `bin/run-tests`, ECS, PHPStan max, Rector | 0,75 h | Claude |
| T-TECH-23-03 | Transverse | OPS | PR du sprint : CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; si la CI de la PR ne démarre pas, `gh workflow r … | 0,5 h | Orchestrateur |
| T-TECH-23-04 | Transverse | REV | Avant chaque passage en done : table « scénario → test ou preuve » complète dans le fichier de tâches (action 1) et commit de tests rouges cité dans l … | 0,75 h | Orchestrateur |
| T-TECH-23-05 | Transverse | DOC | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge (action 5) | 0,25 h | Orchestrateur |

## 👀 En Review

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-168-01 | US-168 | DOC | Cadrage : règle de comptage de `ProofCollector`, clés publiées (fichiers de tests de la démo, nombre de plugins), point d'entrée retenu (option `--che … | 0,25 h | Claude |
| T-168-02 | US-168 | TEST | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (preuve à jour, écart affiché en PR, rafraîchissement puis contrôle vert, écart en … | 0,75 h | Claude |
| T-168-03 | US-168 | BE | Contrôle sans écriture : extraction de la règle de comptage partagée, comparaison, message par chiffre (publié, réel, écart), codes de sortie | 0,75 h | Claude |
| T-168-04 | US-168 | OPS | CI : étape de contrôle, écart dans le résumé du job avec la commande de rafraîchissement ; bloquante sur `main`, informative sur les PR (décision du c … | 0,5 h | Claude |
| T-168-05 | US-168 | TEST | Suite ciblée via `bin/run-tests` (si le disque le permet, sinon CI), ECS (`src tests`), PHPStan max, Rector ; `docs/tests-locaux.md` à jour | 0,5 h | Claude |
| T-168-06 | US-168 | REV | Revue et table « scénario → test » remplie ci-dessous (action 1 de la rétro du sprint 22) | 0,25 h | Claude |
| T-166-01 | US-166 | DOC | Cadrage : relevé confirmé (6 méthodes, 7 `usleep`), horloge disponible (`symfony/clock` ou attente bornée sur condition), choix de la garde (test d'ar … | 0,5 h | Claude |
| T-166-02 | US-166 | TEST | Gardes rouges, commitées seules et citées dans la PR : aucune méthode de `src` au-delà de 45 lignes, aucun `usleep` dans `tests/` | 1 h | Claude |
| T-166-03 | US-166 | BE | `ProductCsvFixture` : `createOrUpdateVariant` et `importProduct` découpées | 1,25 h | Claude |
| T-166-04 | US-166 | BE | `OrderPoReferenceTypeExtension::buildForm` et `CsvProductReader::parseHandle` découpées | 1 h | Claude |
| T-166-05 | US-166 | BE | `PunchoutDemoFixture::loadCxmlBuyer` et `DemoResetCommand::reset` découpées | 1 h | Claude |
| T-166-06 | US-166 | TEST | Les 7 `usleep` remplacés (horloge contrôlée ou attente sur condition), dont `RunTestsLockTest` ; 20 exécutions de suite de `RunTestsLockTest` sans éch … | 1,5 h | Claude |
| T-166-07 | US-166 | TEST | Suite complète via `bin/run-tests` (si le disque le permet, sinon CI), ECS, PHPStan max, Rector ; aucun attendu de test modifié (diff de `tests/` relu … | 0,75 h | Claude |
| T-166-08 | US-166 | REV | Revue (comportement inchangé, noms des méthodes extraites) et table « scénario → test » remplie | 0,25 h | Claude |
| T-169-01 | US-169 | OPS | Branche d'essai depuis la PR #54 ; `composer why-not phpunit/phpunit 13` et bornes des paquets de développement relevées | 0,5 h | Claude |
| T-169-02 | US-169 | TEST | PHPStan sur la branche : les 59 erreurs classées par cause (types de l'API de PHPUnit 13, extension PHPStan de PHPUnit, code de test) | 1 h | Claude |
| T-169-03 | US-169 | TEST | Suite sur la branche (CI) : échecs et dépréciations de PHPUnit 13 listés (attributs, mocks, `TestCase`) | 1 h | Claude |
| T-169-04 | US-169 | DOC | Rapport : blocages, erreurs par cause, changements de code, estimation en points et recommandation | 1 h | Claude |
| T-170-01 | US-170 | DOC | Cadrage : sources de chaque rubrique (`docs/*.md`, `Dockerfile`, `docker/Caddyfile`, configuration, workflows), route et liens d'accès, chiffres lus d … | 0,75 h | Claude |
| T-170-02 | US-170 | TEST | Tests rouges, commités seuls et cités dans la PR : page FR et EN avec ses six rubriques, liens depuis Intégrer et le pied de page, SVG avec titre et d … | 1 h | Claude |
| T-170-03 | US-170 | BE | Route localisée et contrôleur ; chiffres fournis par `ProofReader` | 0,75 h | Claude |
| T-170-04 | US-170 | FE-WEB | Gabarit et schéma SVG d'ensemble (thème clair et sombre), textes FR et EN des six rubriques avec leurs liens vers le dépôt | 2 h | Claude |
| T-170-05 | US-170 | FE-WEB | Liens : section « Architecture type » de la page Intégrer et pied de page de la boutique | 0,5 h | Claude |
| T-170-07 | US-170 | REV | Revue (aucun détail de sécurité, ton neutre, parité FR et EN) et table « scénario → test » remplie | 0,25 h | Claude |
| T-173-01 | US-173 | DOC | Relevé : classement US-063, `require` et `suggest` des 18 plugins sans recoupement, dépendances internes entre eux | 0,5 h | Claude |
| T-173-02 | US-173 | OPS | Essai d'un métapaquet « IA et headless » dans un projet jetable (dépôt de type `path`) : ce qui reste à faire à la main (bundles, configuration, route … | 1,25 h | Claude |
| T-173-03 | US-173 | DOC | Comparaison des trois formes (métapaquet, recettes Flex sur `recipes-contrib`, squelette de projet) : effet, maintenance, publication | 0,75 h | Claude |
| T-173-04 | US-173 | DOC | ADR : forme retenue, composition des packs, découpage de la réalisation en stories de 8 points au plus ; table « scénario → preuve » remplie | 1 h | Claude |
| T-TECH-23-01 | Transverse | OPS | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés ; un seul serveur lourd à la fois (démo locale ou MySQL de test), a … | 0,5 h | Orchestrateur |
| T-TECH-23-01b | Transverse | OPS | Thibaut libère de l'espace disque pour atteindre au moins 5 Go libres (3,4 Go au lancement ; 5,9 Go le 2026-10-23 après son action) | 0,25 h | Thibaut |
| T-TECH-23-02 | Transverse | OPS | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push ; dernière ligne de sortie citée | 0,75 h | Orchestrateur |
