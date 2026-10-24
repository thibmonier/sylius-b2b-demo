# Tâches : US-185 : Contrat de fichier plat et export CSV des commandes, dépôt atomique

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut ; lead développeur d'une agence qui relie la boutique à l'ERP d'un client
- **Story Points** : 5 (Could, sacrifice : **n°1**)
- **Sprint** : sprint-26-vitrine-et-flux
- **Worker proposé** : w185
- **Story** : [US-185](../../../backlog/user-stories/US-185.md)

## Résumé de la US

**En tant que** lead développeur  
**Je veux** que les commandes soient exportées en fichiers plats selon un contrat documenté, déposés sans fichier partiel visible  
**Afin de** brancher un ERP qui sait lire un répertoire d'échange

## Règles et contraintes

- **Nouveau plugin** `tmonier/sylius-erp-flatfile-plugin` (nom proposé, ADR 0006), développé **en local** dans `/Users/tmonier/Projects/sylius_plugins/sylius-erp-flatfile-plugin/` à partir du squelette des plugins `tmonier/*`. `webhook-outbox` et `facturx` : lecture seule.
- **Aucun dépôt GitHub, push, tag ni paquet sans le GO de Thibaut** (T-185-11) ; soumission à Packagist par Thibaut (T-185-13). La démo n'est pas modifiée (intégration et ERP fictif : US-188, backlog).
- **Règle 12** : aucun fichier `.env*` (variables dans `phpunit.xml.dist` et l'environnement de la CI), MySQL de la démo avec une base à son nom supprimée à la fin, **jamais de second serveur**.
- **Budget disque** : `vendor/` d'environ 400 Mo plus la base d'essai, supprimés en fin de lot ; au moins 6 Go libres avant le lot (5,2 Go au planning : nettoyage demandé à Thibaut d'abord).
- Plancher commun : PHP 8.2, Sylius 2.2 et 2.3, Symfony 6.4, 7.4 et 8.0 ; CI écrite et validée en local, jouée après le push.
- Fichier plat dérivé des événements de l'outbox, pas d'une seconde lecture de la base ; données personnelles : rétention du répertoire d'échange tranchée ou laissée ouverte au cadrage.
- Ton neutre, aucun ERP ni éditeur nommé, aucune comparaison avec Sylius Plus (recoupement vérifié au cadrage avant toute promotion) ; aucun détail de sécurité.
- Règle 10 : `git` (`git init`, commits, tag) dans l'arbre principal.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 25 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de rendu. Preuves : suite de tests et outils de qualité du plugin en local ; CI du plugin sur sa matrice après le push (si GO).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Nouveau dépôt local `sylius-erp-flatfile-plugin` : `composer.json`, `src/` (contrat, export, dépôt atomique, bundle, commande), `tests/`, `.github/workflows/`, `ecs.php`, `phpstan.neon`, `rector.php`, `README*.md`, `CHANGELOG.md`, `LICENSE`
- Lecture : `sylius-webhook-outbox-plugin` (événements, enveloppe `order` v1), `docs/adr/0017-*.md`
- Aucun fichier de la démo

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-185-01 | [DOC] | Cadrage : outbox (`OutboxEvent`, dépôt des événements, enveloppe `order` v1) lu sans modification, squelette du modèle, contrat de l'ADR 0017, nom du paquet et espace de noms, mécanisme de consommation, rétention ; recoupement éventuel avec Sylius Plus vérifié (avant toute promotion) ; plan des tests | 1 h | - | Claude | ✅ |
| T-185-02 | [OPS] | Squelette local du plugin **sans `.env*`** (variables dans `phpunit.xml.dist`), MySQL de la démo avec une base à son nom (règle 12), `git init`, CI sur la matrice commune, outils de qualité, LICENSE ; budget disque noté (`vendor/` environ 400 Mo) et espace libre relevé avant et après | 1 h | T-185-01 | Claude | ✅ |
| T-185-03 | [TEST] | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (contrat, lot de plusieurs commandes, dépôt atomique observé par un lecteur, événement rejoué, répertoire inaccessible) | 1,5 h | T-185-02 | Claude | ✅ |
| T-185-04 | [BE] | Contrat de fichier plat : colonnes, en-tête, UTF-8, séparateur `;`, entiers de l'unité mineure, nom de fichier unique par lot | 1 h | T-185-03 | Claude | ✅ |
| T-185-05 | [BE] | Export : consommation des événements de commande, une ligne par ligne de commande, idempotence par `webhook_id` | 1,25 h | T-185-04 | Claude | ✅ |
| T-185-06 | [BE] | Dépôt atomique : nom provisoire puis renommage, aucun fichier partiel si le répertoire est inaccessible, événements laissés à exporter | 1 h | T-185-04 | Claude | ✅ |
| T-185-07 | [BE] | Bundle, configuration (répertoire d'échange, lot) et commande console | 0,75 h | T-185-05, T-185-06 | Claude | ✅ |
| T-185-08 | [TEST] | Suite complète en local (Docker, MySQL de la démo), ECS, PHPStan max, Rector ; dernières lignes citées ; `vendor/` et base d'essai supprimés, espace libre relevé | 0,75 h | T-185-07 | Claude | ✅ |
| T-185-09 | [DOC] | README FR et EN (contrat, configuration, limites : aucun ERP réel), CHANGELOG ; texte relu contre le code et l'ADR 0017 | 1 h | T-185-08 | Claude | ✅ |
| T-185-10 | [REV] | Revue (ton neutre, aucun ERP ni éditeur nommé, `webhook-outbox` inchangé, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-185-09 | Claude | ✅ |
| T-185-11 | [OPS] | 📅 Thibaut donne son GO pour créer le dépôt GitHub `thibmonier/sylius-erp-flatfile-plugin`, pousser et poser le tag `v0.1.0` | 0,25 h | T-185-10 | Thibaut | ✅ |
| T-185-12 | [OPS] | Après GO : création du dépôt avec ses topics (ecommerce, sylius, sylius-plugin, symfony, erp, csv), push, CI verte sur toute la matrice (run terminé), concordance (CHANGELOG, tag, `composer.json`), tag et release `v0.1.0` | 0,75 h | T-185-11 | Claude | ✅ |
| T-185-13 | [OPS] | 📅 Thibaut soumet le paquet à Packagist | 0,25 h | T-185-12 | Thibaut | ✅ |

**Total estimé : 10,75 h** (13 tâches, dont 0,5 h de Thibaut (2 tâches) ; 10,25 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Commande exportée selon le contrat (nominal) | `tests/Unit/Contract/OrderLinesCsvTest.php`, `tests/Integration/ExportScenariosTest.php` du plugin `thibmonier/sylius-erp-flatfile-plugin` |
| Plusieurs commandes dans un lot (alternatif) | `tests/Unit/Export/OrderExporterTest.php`, `ExportScenariosTest.php` |
| Lecteur concurrent (alternatif) | `tests/Unit/Deposit/AtomicFileDepositorTest.php` (nom provisoire puis renommage) |
| Événement rejoué (erreur) | `OrderExporterTest.php`, `ExportScenariosTest.php` (idempotence par `webhook_id`) |
| Répertoire inaccessible (erreur) | `AtomicFileDepositorTest.php`, `ExportScenariosTest.php` (aucun fichier partiel, événements laissés à exporter) ; CI verte, release v0.1.0 (run 40088454801) |
