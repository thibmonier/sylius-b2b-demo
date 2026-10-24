# Tâches : US-188 : Démonstration avec l'ERP fictif (périmètre réduit, sans US-187)

## Informations US

- **Epic** : EPIC-14
- **Persona** : Camille (recruteuse IT), Thibaut ; lead développeur d'une agence
- **Story Points** : 5 (Could, sacrifice : **n°1, validé par Thibaut**)
- **Sprint** : sprint-27-fiche-produit-et-erp-fictif
- **Worker proposé** : w188
- **Story** : [US-188](../../../backlog/user-stories/US-188.md) ; décision : [ADR 0017](../../../../docs/adr/0017-connecteur-erp-de-reference.md), section « Décision (2026-10-24, sprint 27) »

## Résumé de la US

**En tant que** visiteuse de la démo  
**Je veux** voir les commandes descendre vers un « ERP Démo (fictif) » et un accusé de réception simulé remonter par fichier  
**Afin de** comprendre l'échange avec un ERP sans aucun ERP réel

## Écart de périmètre

La fiche d'origine dépend d'US-187 (import CSV de prix et de stock), **hors sprint**. Cette décomposition ne livre donc **que** ce qui se démontre avec l'export déjà livré (US-185, US-190) : un simulateur qui lit le dernier CSV de commandes et dépose un **accusé de réception simulé**, un journal des échanges au back-office, la documentation. Aucun prix ni stock n'est importé ; les scénarios « prix mis à jour » et « lot rejeté à l'import » restent avec US-187. Le détail est dans [sprint-goal.md](../sprint-goal.md), section « Écart de périmètre ».

## Règles et contraintes

- L'ERP fictif s'appelle **« ERP Démo (fictif) »** dans l'interface, la documentation et les fichiers ; il ne représente aucun éditeur. Statuts et chiffres simulés, et dits tels à l'écran. Test de charte : aucun éditeur d'ERP nommé.
- **Aucun appel sortant** : le simulateur lit et écrit des fichiers du répertoire d'échange (`var/erp-exchange`), rien d'autre ; le client HTTP de la démo reste borné au récepteur local (`ReceiverOnlyHttpClient`, [webhooks-demo.md](../../../../docs/webhooks-demo.md)).
- Dépôt atomique des fichiers du simulateur (fichier temporaire puis renommage), comme le plugin d'export.
- Données personnelles des CSV fictives ; rétention du répertoire d'échange = remise à zéro nocturne.
- Page du back-office en lecture seule, à la façon de « Webhooks reçus » et de « Transmissions de factures » (même squelette, menu, garde d'écriture de démonstration).
- Le plugin `tmonier/sylius-erp-flatfile-plugin` n'est **pas modifié** (aucun tag ni dépôt) ; tout le code vit dans la démo.
- Tests de la démo uniquement par `WORKER_ID=w188 TEST_DB_NAME=b2b_w188 TEST_CACHE_DIR=var/cache-w188 bin/run-tests` ; preuve : `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Pages publiques en production** (lecture seule, sans connexion) : la page `/echange-fichier` et la documentation. **La page du back-office n'est pas ouverte en production** (aucun identifiant n'est saisi) : elle est vérifiée en local et sur l'image reconstruite (T-188-10). Mesures consignées le jour du déploiement.

## Fichiers principaux touchés

- `src/Demo/Erp/` (simulateur, lecteur de journal), `src/Demo/Command/` (commande du simulateur), `src/Demo/Controller/` (page des échanges), `src/Demo/Admin/` ou `src/Demo/Webhook/ReceivedWebhooksMenuListener.php` (entrée de menu, par analogie)
- `templates/admin/` ou `templates/demo/` (page des échanges), `translations/` (nouveau domaine)
- `src/Demo/Command/DemoResetCommand.php`, fixtures (partagés avec US-193 : T-193-04 d'abord)
- `docs/erp-demo.md` (nouveau), `docs/webhooks-demo.md` (lien), `docs/adr/0017-*.md`, README
- `tests/Functional/Demo/`, `tests/Unit/` (nouveaux tests), `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-188-01 | [DOC] | Cadrage : ADR 0017, `webhooks-demo.md`, récepteur (`WebhookReceiverController`, `ReceivedWebhooksController`), journal d'export du plugin (`ExportedEvent`, `ExportJournal`), répertoire `var/erp-exchange`, `DemoResetCommand` ; contrat de l'accusé de réception simulé (colonnes) | 0,75 h | - | Claude | 🔲 |
| T-188-02 | [DOC] | Affinage de la fiche : scénarios réécrits sans import (1 nominal, 2 alternatifs, 2 erreurs), renvoi explicite à US-187 ; section de l'ADR 0017 | 0,5 h | T-188-01 | Claude | 🔲 |
| T-188-03 | [TEST] | Tests rouges, commités seuls et cités dans la PR : simulateur (commande traitée, accusé déposé atomiquement), fichier invalide rejeté sans accusé, lecteur de journal (lots sortants et entrants), page des échanges (lignes lues, acceptées, rejetées), remise à zéro, aucun appel sortant, charte (aucun ERP réel nommé) | 1,25 h | T-188-02 | Claude | 🔲 |
| T-188-04 | [BE] | Simulateur « ERP Démo (fictif) » : commande console qui lit le dernier CSV de commandes du répertoire d'échange, valide l'en-tête et les lignes, dépose un accusé de réception simulé (CSV de statuts) ou rejette le fichier et le consigne ; aucun accès réseau | 1,75 h | T-188-03 | Claude | 🔲 |
| T-188-05 | [BE] | Journal des échanges : lecteur en lecture seule qui regroupe, par fichier, les événements du journal d'export du plugin (lots sortants) et les accusés ou rejets du simulateur (lots entrants) : sens, nom, date, lignes lues, acceptées, rejetées | 1,25 h | T-188-03 | Claude | 🔲 |
| T-188-06 | [FE-WEB] | Page « Échanges ERP (fictif) » au back-office : liste des lots, bandeau « statuts simulés », entrée de menu, état vide, FR et EN ; accessible (AAA) et sans défilement horizontal à 360 px | 1,5 h | T-188-05 | Claude | 🔲 |
| T-188-07 | [BE] | Remise à zéro nocturne : répertoire d'échange et journal du simulateur vidés, export puis simulation relancés après le chargement des fixtures (reprend le résultat de T-193-04) | 0,5 h | T-188-04, T-193-04 | Claude | 🔲 |
| T-188-08 | [DOC] | Documentation `docs/erp-demo.md` (schéma mermaid, fichiers, ce qui est simulé, ce qui reste à US-187), lien depuis `/echange-fichier` et `webhooks-demo.md`, ADR 0017 et README ; texte relu contre le code | 0,75 h | T-188-06, T-188-07 | Claude | 🔲 |
| T-188-09 | [TEST] | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; commande exacte et dernière ligne citées | 0,5 h | T-188-08 | Claude | 🔲 |
| T-188-10 | [TEST] | Vérification après déploiement : pages publiques en production (pa11y, 360 px, documentation) ; page du back-office en local et sur l'image reconstruite (aucun identifiant saisi en production) ; mesures consignées le jour du déploiement | 0,25 h | merge de la PR | Claude | 🔲 |
| T-188-11 | [REV] | Revue (ton neutre, « ERP Démo (fictif) » partout, aucun éditeur nommé) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-188-10 | Claude | 🔲 |

**Total estimé : 9,5 h** (11 tâches ; 9,5 h pour Claude).

## Scénarios et preuves

Scénarios réécrits pour le périmètre réduit (voir la fiche). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Commande exportée puis accusé de réception reçu (nominal) | |
| Page des échanges (alternatif) | |
| Remise à zéro nocturne (alternatif) | |
| Fichier de commandes invalide rejeté (erreur) | |
| Aucun appel sortant, aucun ERP réel nommé (erreur) | |
