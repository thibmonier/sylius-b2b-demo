# Task Board : Sprint 023 (garde-fous et cadrages)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **39 tâches, 29 h** pour 12 points ; **28 h pour Claude**, 1 h d'actions de Thibaut. État au lancement (2026-10-23) : toutes les tâches 🔲. Voir [tasks/README.md](./tasks/README.md).

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-168-01 | US-168 | DOC | Cadrage : règle de comptage de `ProofCollector`, clés publiées (fichiers de tests de la démo, nombre de plugins), point d'entrée retenu (option `--check` ou … | 0,25 h | Claude |
| T-168-02 | US-168 | TEST | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (preuve à jour, écart affiché en PR, rafraîchissement puis contrôle vert, écart en erreur … | 0,75 h | Claude |
| T-168-03 | US-168 | BE | Contrôle sans écriture : extraction de la règle de comptage partagée, comparaison, message par chiffre (publié, réel, écart), codes de sortie | 0,75 h | Claude |
| T-168-04 | US-168 | OPS | CI : étape de contrôle, écart dans le résumé du job avec la commande de rafraîchissement ; bloquante sur `main`, informative sur les PR (décision du cadrage … | 0,5 h | Claude |
| T-168-05 | US-168 | TEST | Suite ciblée via `bin/run-tests` (si le disque le permet, sinon CI), ECS (`src tests`), PHPStan max, Rector ; `docs/tests-locaux.md` à jour | 0,5 h | Claude |
| T-168-06 | US-168 | REV | Revue et table « scénario → test » remplie ci-dessous (action 1 de la rétro du sprint 22) | 0,25 h | Claude |
| T-166-01 | US-166 | DOC | Cadrage : relevé confirmé (6 méthodes, 7 `usleep`), horloge disponible (`symfony/clock` ou attente bornée sur condition), choix de la garde (test d'architect … | 0,5 h | Claude |
| T-166-02 | US-166 | TEST | Gardes rouges, commitées seules et citées dans la PR : aucune méthode de `src` au-delà de 45 lignes, aucun `usleep` dans `tests/` | 1 h | Claude |
| T-166-03 | US-166 | BE | `ProductCsvFixture` : `createOrUpdateVariant` et `importProduct` découpées | 1,25 h | Claude |
| T-166-04 | US-166 | BE | `OrderPoReferenceTypeExtension::buildForm` et `CsvProductReader::parseHandle` découpées | 1 h | Claude |
| T-166-05 | US-166 | BE | `PunchoutDemoFixture::loadCxmlBuyer` et `DemoResetCommand::reset` découpées | 1 h | Claude |
| T-166-06 | US-166 | TEST | Les 7 `usleep` remplacés (horloge contrôlée ou attente sur condition), dont `RunTestsLockTest` ; 20 exécutions de suite de `RunTestsLockTest` sans échec | 1,5 h | Claude |
| T-166-07 | US-166 | TEST | Suite complète via `bin/run-tests` (si le disque le permet, sinon CI), ECS, PHPStan max, Rector ; aucun attendu de test modifié (diff de `tests/` relu) | 0,75 h | Claude |
| T-166-08 | US-166 | REV | Revue (comportement inchangé, noms des méthodes extraites) et table « scénario → test » remplie | 0,25 h | Claude |
| T-169-01 | US-169 | OPS | Branche d'essai depuis la PR #54 ; `composer why-not phpunit/phpunit 13` et bornes des paquets de développement relevées | 0,5 h | Claude |
| T-169-02 | US-169 | TEST | PHPStan sur la branche : les 59 erreurs classées par cause (types de l'API de PHPUnit 13, extension PHPStan de PHPUnit, code de test) | 1 h | Claude |
| T-169-03 | US-169 | TEST | Suite sur la branche (CI) : échecs et dépréciations de PHPUnit 13 listés (attributs, mocks, `TestCase`) | 1 h | Claude |
| T-169-04 | US-169 | DOC | Rapport : blocages, erreurs par cause, changements de code, estimation en points et recommandation | 1 h | Claude |
| T-169-05 | US-169 | REV | 📅 Thibaut lit le rapport et décide (story de montée ou fermeture de la PR #54) | 0,25 h | Thibaut |
| T-169-06 | US-169 | DOC | Décision consignée dans `.bmad/sprint-status.yaml` ; story de montée créée ou PR #54 fermée avec Dependabot réglé pour ignorer les versions majeures | 0,25 h | Orchestrateur |
| T-132-01 | US-132 | DOC | Sources officielles relevées (calendrier, formats, annuaire des plateformes, e-reporting), avec URL et date de consultation | 1,5 h | Claude |
| T-132-02 | US-132 | DOC | Couverture du plugin facturx relevée dans son dépôt (formats produits, profils) et manques pour la transmission et l'e-reporting | 0,75 h | Claude |
| T-132-03 | US-132 | DOC | Note datée : flux de transmission, e-reporting, démonstration vers une plateforme fictive ; règles non confirmées marquées « à vérifier » | 1,5 h | Claude |
| T-132-04 | US-132 | DOC | ADR et découpage en stories de 8 points au plus | 1 h | Claude |
| T-132-05 | US-132 | REV | Relecture : chaque affirmation sourcée ou marquée, pas d'avis juridique, ton neutre ; table « scénario → preuve » remplie | 0,5 h | Claude |
| T-132-06 | US-132 | REV | 📅 Thibaut relit la note et décide de la suite (stories à planifier ou non) | 0,25 h | Thibaut |
| T-130-01 | US-130 | DOC | Contrat d'événements existant du plugin webhook-outbox relevé (événements, charge utile, rejeu, journal) | 0,75 h | Claude |
| T-130-02 | US-130 | DOC | Documentation publique des 5 ERP cibles : formats d'import et d'export, API, avec URL et date de consultation | 1,5 h | Claude |
| T-130-03 | US-130 | DOC | ADR : format de fichier plat, contrats, API, journal des échanges, périmètre (ou no-go avec périmètre réduit), schéma Mermaid | 1,5 h | Claude |
| T-130-04 | US-130 | DOC | Découpage en stories de 8 points au plus et démonstration avec un ERP fictif nommé comme tel | 0,75 h | Claude |
| T-130-05 | US-130 | REV | Relecture : sources citées ou « à vérifier », temps du spike consigné ; table « scénario → preuve » remplie | 0,5 h | Claude |
| T-130-06 | US-130 | REV | 📅 Thibaut relit l'ADR et décide (stories à planifier ou non) | 0,25 h | Thibaut |
| T-TECH-23-01 | Transverse | OPS | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés ; un seul serveur lourd à la fois (démo locale ou MySQL de test), arrêté d … | 0,5 h | Orchestrateur |
| T-TECH-23-01b | Transverse | OPS | 📅 Thibaut libère de l'espace disque pour atteindre au moins 5 Go libres (3,4 Go au lancement du 2026-10-23) | 0,25 h | Thibaut |
| T-TECH-23-02 | Transverse | OPS | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push ; dernière ligne de sortie citée | 0,75 h | Orchestrateur |
| T-TECH-23-03 | Transverse | OPS | PR du sprint : CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; si la CI de la PR ne démarre pas, `gh workflow run ci.y … | 0,5 h | Orchestrateur |
| T-TECH-23-04 | Transverse | REV | Avant chaque passage en done : table « scénario → test ou preuve » complète dans le fichier de tâches (action 1) et commit de tests rouges cité dans la PR po … | 0,75 h | Orchestrateur |
| T-TECH-23-05 | Transverse | DOC | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge (action 5) | 0,25 h | Orchestrateur |
| T-TECH-23-06 | Transverse | DOC | Revue et rétrospective du sprint 23 : indicateurs du sprint 22 (scénarios non livrés, commits rouges, plantages, espace minimal, tâches restées 🔲, contournem … | 1 h | Orchestrateur |

## 🔄 En Cours

_Aucune._

## 👀 En Review

_Aucune._

## ✅ Terminé

_Aucune._
