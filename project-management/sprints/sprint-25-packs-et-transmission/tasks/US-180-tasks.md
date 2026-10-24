# Tâches : US-180 : Transmission des factures électroniques, tranche A : port, journal et statuts

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut ; lead développeur d'une agence
- **Story Points** : 5 (Should, **sacrifice n°2**)
- **Sprint** : sprint-25-packs-et-transmission
- **Story** : [US-180](../../../backlog/user-stories/US-180.md)

## Résumé de la US

**En tant que** lead développeur
**Je veux** qu'une facture Factur-X générée soit transmise par un port remplaçable et que chaque envoi garde son statut
**Afin de** brancher plus tard une plateforme agréée sans toucher au plugin de génération

## Règles et contraintes

- **Nouveau plugin** `tmonier/sylius-einvoice-transmission-plugin`, développé **en local** dans `/Users/tmonier/Projects/sylius_plugins/sylius-einvoice-transmission-plugin/` à partir du squelette des autres plugins `tmonier/*` (modèle : `sylius-webhook-outbox-plugin`). Il ne modifie pas `facturx` (lecture seule du dépôt `sylius-facturx-plugin`).
- Port `InvoiceTransmitterInterface` ; écouteur de l'événement `tmonier_facturx.invoice_generated` (`FacturXInvoiceGenerated`) ; journal des envois (table du plugin, une ligne par facture) ; adaptateur nul `NullInvoiceTransmitter` (statut « non transmise ») et double de test.
- Écouteur **idempotent** : une même facture n'est transmise qu'une fois, même si l'événement est rejoué (clé : identifiant de la facture, contrainte d'unicité en base).
- Statuts : ensemble minimal et extensible (déposée, rejetée, en erreur, non transmise), écrit comme tel dans le README ; **les statuts officiels ne sont pas confirmés par une source lue** (ADR 0016, « à vérifier ») : aucun statut inventé, un statut inconnu est refusé.
- Un adaptateur en échec n'empêche jamais la génération de la facture : exception attrapée, statut « en erreur » et message gardés.
- Plancher commun : PHP 8.2, Sylius 2.2 et 2.3, Symfony 6.4, 7.4 et 8.0 ; CI du plugin sur ces matrices (écrite et validée en local, jouée seulement après le push).
- **Aucun dépôt GitHub, push, tag ni paquet sans le GO de Thibaut** (T-180-11) ; la soumission à Packagist est faite par Thibaut (T-180-13). L'intégration à la démo (catalogue `config/demo/plugins.yaml`, plateforme fictive) est la story B de l'ADR 0016 : **hors sprint**, la démo n'est pas modifiée.
- Ressources : une seule installation lourde à la fois (vendor et base du plugin), au moins 5 Go libres, nettoyage après le lot ; commandes de qualité dans Docker (consigne du projet).
- **Règle 10** : `git` (`git init`, commits, tag) dans l'arbre principal, pas de worktree isolé.
- Ton neutre, aucune comparaison avec une offre commerciale (Sylius Plus), aucune plateforme réelle nommée, aucun détail de sécurité.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 24 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de rendu (pas d'interface). **Preuves : suite de tests et outils de qualité du plugin en local ; CI du plugin sur sa matrice après le push (si GO).**

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Nouveau dépôt local `sylius-einvoice-transmission-plugin` : `composer.json`, `src/` (port, adaptateur nul, statuts, écouteur, entité du journal, bundle), `migrations/`, `tests/`, `.github/workflows/build.yaml`, `ecs.php`, `phpstan.neon`, `rector.php`, `README.md`, `README.fr.md`, `CHANGELOG.md`, `LICENSE`
- Lecture : `sylius-facturx-plugin` (événement et données), `docs/adr/0016-facture-electronique-pa-pdp.md`, `sylius-webhook-outbox-plugin` (squelette)
- Aucun fichier de la démo

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-180-01 | [DOC] | Cadrage : squelette du plugin modèle (composer.json, matrice de CI, outils de qualité, application de test) lu ; événement et classe `FacturXInvoiceGenerated` de `facturx` lus (version de dépôt notée) ; nom du bundle, espace de noms, table, statuts, clé d'idempotence, forme du port ; plan des tests | 1 h | - | Claude | ✅ |
| T-180-02 | [OPS] | Squelette du plugin en local (copie nettoyée du modèle, `git init`, `composer.json`, CI sur la matrice commune, outils de qualité, LICENSE) ; ressources relevées avant l'installation ; une seule installation lourde | 1 h | T-180-01 | Claude | ✅ |
| T-180-03 | [TEST] | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (facture transmise et journalisée, événement rejoué, adaptateur nul par défaut, adaptateur en échec sans échec de la génération, statut inconnu refusé) | 1,5 h | T-180-02 | Claude | ✅ |
| T-180-04 | [BE] | Port `InvoiceTransmitterInterface`, résultat de transmission, statuts (ensemble minimal, extensible), `NullInvoiceTransmitter` | 1 h | T-180-03 | Claude | ✅ |
| T-180-05 | [DB] | Entité du journal des envois (facture, statut, message, dates), mapping, migration avec unicité par identifiant de facture, dépôt | 1 h | T-180-03 | Claude | ✅ |
| T-180-06 | [BE] | Écouteur de `tmonier_facturx.invoice_generated` : appel de l'adaptateur, journalisation, idempotence, exception attrapée, statut inconnu refusé | 1 h | T-180-04, T-180-05 | Claude | ✅ |
| T-180-07 | [BE] | Bundle et configuration (adaptateur choisi par service, défaut nul), enregistrement de l'écouteur, double de test `RecordingInvoiceTransmitter` | 0,75 h | T-180-06 | Claude | ✅ |
| T-180-08 | [TEST] | Suite complète en local (commande du Makefile du plugin, dans Docker), ECS, PHPStan max, Rector ; dernières lignes citées ; base et vendor de l'essai nettoyés, espace libre relevé | 0,75 h | T-180-07 | Claude | ✅ |
| T-180-09 | [DOC] | README FR et EN (installation, port, statuts minimaux « à vérifier », limites : aucune plateforme réelle), CHANGELOG ; texte relu contre le code et l'ADR 0016 | 1 h | T-180-08 | Claude | ✅ |
| T-180-10 | [REV] | Revue (ton neutre, aucune plateforme réelle, `facturx` inchangé, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-180-09 | Claude | ✅ |
| T-180-11 | [OPS] | 📅 Thibaut donne son GO pour créer le dépôt GitHub `thibmonier/sylius-einvoice-transmission-plugin`, pousser et poser le tag `v0.1.0` | 0,25 h | T-180-10 | Thibaut | ✅ |
| T-180-12 | [OPS] | Après GO : création du dépôt avec ses topics (ecommerce, sylius, sylius-plugin, symfony, einvoice, factur-x), push, CI verte sur toute la matrice (run terminé), tag et release `v0.1.0` | 0,75 h | T-180-11 | Claude | ✅ |
| T-180-13 | [OPS] | 📅 Thibaut soumet le paquet à Packagist | 0,25 h | T-180-12 | Thibaut | 📅 |

**Total estimé : 10,5 h** (13 tâches, dont 0,5 h de Thibaut ; 10 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

Dépôt public `thibmonier/sylius-einvoice-transmission-plugin`, `main` `b645262` : CI verte sur ses 10 jobs (Sylius 2.2 et 2.3, PHP 8.2 à 8.5, Symfony 6.4 à 8.0, MySQL, MariaDB 11.4, PostgreSQL 17). Commit rouge seul : `d817be6` (15 tests, 14 erreurs, 1 échec). Local : `OK (20 tests, 42 assertions)`, ECS, PHPStan max et Rector verts. Écart au squelette : aucun fichier `.env*` (refusé par le hook du projet), application de test configurée par `phpunit.xml.dist` et l'environnement de la CI, expliqué dans le README. Tag et release `v0.1.0` posés après le GO de Thibaut (2026-10-24), sur `d2da15f` (CHANGELOG daté, code identique au commit vert) ; soumission à Packagist par Thibaut (T-180-13).

| Scénario | Test ou preuve |
|----------|----------------|
| Facture transmise et journalisée (nominal) | `tests/Integration/TransmissionScenariosTest.php` : `testAnInvoiceIsTransmittedOnceAndTheSendIsLogged` |
| Événement rejoué (alternatif) | `tests/Integration/TransmissionScenariosTest.php` : `testAReplayedEventDoesNotSendTheInvoiceAgain` |
| Aucun adaptateur configuré (alternatif) | `tests/Integration/TransmissionScenariosTest.php` : `testTheDefaultConfigurationLogsTheInvoiceAsNotTransmittedWithTheNullAdapter` |
| Adaptateur en échec (erreur) | `tests/Integration/TransmissionScenariosTest.php` : `testAFailingAdapterIsLoggedAsAnErrorAndDoesNotBreakTheGeneration` ; `Unit\EventListener\InvoiceGeneratedListenerTest` |
| Statut inconnu (erreur) | `tests/Integration/TransmissionScenariosTest.php` : `testAnUnknownStatusIsRefusedWithAClearMessageAndNeverStored` ; `TransmissionStatusCatalogTest`, `TransmissionJournalTest` |
