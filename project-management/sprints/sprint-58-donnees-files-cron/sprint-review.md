# Revue du sprint 58 — Données, files et tâches planifiées

**Date** : 2026-11-26. Sprint exécuté du 2026-11-25 au 2026-11-26 ; le créneau prévu était du 2026-12-24 au 2027-01-07.

**Objectif** : la page de santé montre l'état des données, des files et des tâches planifiées. Une migration oubliée, un transport d'échec qui se remplit ou un cron qui ne réussit plus se voient sans ouvrir de terminal.

**Objectif atteint.**
- 15 points livrés sur 15 engagés (capacité 15, 100 %) ; 16 tâches sur 16 ; 40,5 h sur 40,5 h.
- Les trois stories d'EPIC-21 sont « done », toutes les PR ont été fusionnées par Thibaut.
- Le plugin est publié en **v0.4.0**, la démo est passée en ^0.4 et la production est déployée (`/healthz` 200 : la migration du battement est passée).
- La page de la démo montre seize sondes, dont un constat réel : la dérive du schéma est en avertissement, ce qui a donné US-318.

## Stories

| Story | Points | PR | Rouge prouvé en CI (10 combinaisons) | Statut |
|-------|--------|----|--------------------------------------|--------|
| US-294 sondes de données | 5 | [health #15](https://github.com/thibmonier/sylius-platform-health-plugin/pull/15) | rouge `6a6a401`, vert `c397b55`, PostgreSQL et MariaDB compris | **Done** |
| US-295 files et cache | 5 | [health #16](https://github.com/thibmonier/sylius-platform-health-plugin/pull/16) | rouge `71bc8be` (25 échecs unitaires, 1 non unitaire), vert `2e7d584` | **Done** |
| US-296 battement des tâches planifiées | 5 | [health #17](https://github.com/thibmonier/sylius-platform-health-plugin/pull/17) | rouge `5a74e40` + `ac4b132` (16 échecs unitaires, 3 non unitaires, run 45786497806), vert run 45787040151 | **Done** ; écart de seuil accepté par Thibaut |

**Livré : 15/15 points.** Vélocité : 15 (sprint 57 : 17 ; sprint 56 : 13).

## Démonstration (démo déployée, compte Sophie)

Ouvrir Configuration > Santé de la plateforme.

1. **Données** :
   - connexion (temps de réponse) ;
   - migrations en attente : aucune ;
   - **dérive du schéma : avertissement**, avec le nombre d'instructions, jamais le SQL ;
   - croissance des trois tables purgées chaque nuit : journal d'audit, envois de webhooks, journal PunchOut.
2. **Files Messenger** :
   - messages en attente et âge du plus ancien sur `async` ;
   - transport d'échec : `async` a désormais le sien (`failed`). Avant la v0.4, un message en échec après ses tentatives disparaissait sans trace.
3. **Environnement** : deux sondes de plus, le cache de l'application (aller-retour d'une clé témoin) et les dossiers inscriptibles.
4. **Tâches planifiées** :
   - une ligne par commande du cron (16), avec son dernier succès ;
   - `tmonier:webhook:dispatch` et `messenger:consume` passent `ok` dans les minutes qui suivent le déploiement ;
   - les commandes quotidiennes restent `unknown` (« pas encore de battement ») jusqu'à leur premier passage nocturne ;
   - le reset de 03:00 garde la table des battements.
5. **Encart du tableau de bord** : trois feux (environnement, données, tâches planifiées), lus dans le cache partagé avec la page.

## Tâches techniques

| Tâche | PR | Statut |
|-------|----|--------|
| T-TECH-58-01 planification | suivi du sprint | Fait |
| T-TECH-58-02 (A-57-1, A-57-2) règles 30 et 31 | consigne des workers | Fait |
| T-TECH-58-03 release v0.4.0 et démo en ^0.4 | [health #18](https://github.com/thibmonier/sylius-platform-health-plugin/pull/18), tag et release sur GO ; démo [#228](https://github.com/thibmonier/sylius-b2b-demo/pull/228) | Fait. Rouge local (Docker) : 5 échecs unitaires, 2 fonctionnels ; CI verte. Table des fins de support relue (PHP 8.6 ; date publiée de Sylius 2.3) |
| T-TECH-58-04 CI allégée pour les PR de documentation | [#224](https://github.com/thibmonier/sylius-b2b-demo/pull/224) | Fait ; les PR de suivi #225, #226, #227 et #231 n'ont lancé que « Changed files » et zizmor |
| Imprévu : 20 avis de sécurité Symfony du 2026-11-25 | [#229](https://github.com/thibmonier/sylius-b2b-demo/pull/229) (Symfony 8.1.12) | Fait (non planifiée) |

## Écarts à signaler

1. **`main` de la démo rouge du 2026-11-26 07:22 à la fusion de #228**, sans que personne le voie pendant quatre PR. Deux causes :
   - #224 a ajouté un fichier de tests sans rafraîchir `proof.json`, et `proof-check` bloque sur `main` ;
   - les avis Symfony ont fait échouer « Dependency audit ».
   Le rouge n'est apparu qu'à la lecture de `proof.json` pendant T-TECH-58-03 : la CI allégée des PR de documentation ne lance ni l'un ni l'autre de ces contrôles.
2. **Écart de spécification d'US-296**, accepté par Thibaut le 2026-11-26 : la marge de 50 % et le `warning` à 2 battements ne tenaient pas ensemble. Livré : `ok` jusqu'à 2 battements, `warning` au-delà ou sur un échec, `critical` au-delà de 4.
3. **US-296, un premier rouge en erreur fatale sous Symfony ^8.0** : le helper `runCommand` du test entrait en collision avec la méthode de `KernelTestCase`. Il a été renommé dans un second commit rouge (`ac4b132`), puis le rouge a été vu sur les 10 combinaisons.
4. **Rouge de T-TECH-58-03 vu en local seulement**, dans Docker, puis CI verte. La règle 26 est écrite pour les stories des plugins ; pour cette tâche de la démo, le rouge n'a pas été poussé en CI.
5. **Le reset de 03:00 vide toutes les tables du mapping**, ce qui aurait effacé les battements chaque nuit. Repéré à la lecture de `DemoResetCommand`, corrigé par l'exclusion du purger et couvert par un test.
6. **Dérive du schéma de la démo** : 16 instructions sur une base migrée depuis zéro, aucune due au plugin. Story [US-318](../../backlog/user-stories/US-318.md), sprint 59 en tête.
7. **Outils hors Docker, comme au sprint 57** : `bin/compat-refresh`, `bin/proof-refresh` et `bin/proof-check` ont tourné en PHP local, parce qu'ils ont besoin de `gh`. En Docker, deux surprises : un `vendor` local périmé (plugin absent, `composer install` refait) et des assets non construits (`yarn build` dans `node:24`) avant que les tests fonctionnels répondent autrement que par une 500.
8. **ECS sur l'image `sylius-php:8.5-alpine`** signale `tests/Unit/Bin/RunTestsLockTest.php` (`\SIGTERM`), parce que l'extension `pcntl` manque à l'image. La CI a cette extension et reste verte.

## Hors sprint

- 2026-12-01 : relevé Railway par service (A-57-4), à la charge de Thibaut.

## Reste à faire

1. Fusionner #230 (suivi de T-TECH-58-03, `proof.json` à jour) et #231 (US-318).
2. Vérifier la page de santé en production le 2026-11-27 au matin : les 16 commandes en `ok`.
3. Planifier le sprint 59 : US-318 en tête, puis US-303 (sondes facultatives des plugins).
