# Tâches : US-187 : Import CSV de prix et de stock (plugin, puis démonstration)

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut ; lead développeur d'une agence
- **Story Points** : 8 (Could), découpée en **tranche A (plugin, 5 points)** et **tranche B (démonstration, 3 points)** ; sacrifice : **n°1 pour la tranche B (validé par Thibaut le 2026-10-25)**
- **Sprint** : sprint-28-noms-photos-et-import-erp
- **Worker proposé** : w187
- **Story** : [US-187](../../../backlog/user-stories/US-187.md) ; décision : [ADR 0017](../../../../docs/adr/0017-connecteur-erp-de-reference.md), story B de l'ADR
- **Dépôt du plugin** : `thibmonier/sylius-erp-flatfile-plugin` (dépôt séparé `/Users/tmonier/Projects/sylius_plugins/sylius-erp-flatfile-plugin`, v0.1.0)

## Résumé de la US

**En tant que** lead développeur  
**Je veux** importer des prix et des stocks depuis un fichier plat validé, avec un rapport de rejet et un journal des lots  
**Afin qu'**un ERP mette la boutique à jour sans écriture partielle ni double traitement

## Estimation et découpage

La fiche annonce 8 points. La décomposition donne **16,75 h pour Claude** (8 × 2,1 h = 16,8 h) : **8 points tiennent, sans marge** (tranche A : 10 h pour 5 points, 10,5 h attendues ; tranche B : 6,75 h pour 3 points, 6,3 h attendues). Les inconnues sont le modèle de prix et de stock de Sylius (tranche A) et l'attente des feux verts de Thibaut (tranche B ne peut être committée qu'après le tag). D'où le découpage : la **tranche A** (plugin, tag `v0.2.0`) a de la valeur seule ; la **tranche B** (simulateur, import au chargement, page étendue) est sacrifiée en premier si la charge dérape.

## Règles et contraintes

- **Contrat de l'ADR 0017** : prix `variant_code;price_minor;currency;channel`, stock `variant_code;on_hand` ; UTF-8, séparateur `;`, entiers ; lot identifié par nom unique + total de contrôle, lot déjà traité ignoré ; prix en tout ou rien ; stock : lignes valides appliquées, autres rejetées.
- **Questions 3, 4 et 6 : tranchées par Thibaut le 2026-10-25** (T-187-02 faite) : prix par canal (code de variante + canal + prix, `ChannelPricing`), sans groupe de clients ; stock en valeur absolue `on_hand`, sans toucher à `on_hold` ; aucune rétention dans le plugin, remise à zéro nocturne dans la démo.
- **Plugin** : TDD (test rouge commité seul), matrice CI du dépôt lue avant d'écrire du code (PHP le plus bas de la matrice), ECS, PHPStan max, Rector, README FR et EN, CHANGELOG ; MySQL de la démo, base supprimée à la fin, jamais de `.env*`. **Aucun push, tag, release ni dépôt sans le GO de Thibaut (T-187-10)**, qui soumet lui-même à Packagist s'il le faut.
- **Politique Sylius Plus** : aucune promotion d'un plugin qui recouvre Plus. T-187-01 lit la documentation officielle de Plus (import de prix et de stock, inventaire multi-source) et note le résultat ; tant que le recouvrement n'est pas levé, ni README comparatif, ni annonce, ni fiche Marketplace.
- **Démonstration** : « ERP Démo (fictif) » seulement, statuts simulés dits tels à l'écran, aucun ERP réel nommé, aucun appel sortant, dépôt atomique, page du back-office en lecture seule. Le plugin d'export de commandes (v0.1.0) n'est pas modifié ; l'import est dans la même bibliothèque, publié en `v0.2.0`.
- **« Que devient cet état après un déploiement ? »** (règle 16) : les fichiers d'échange vivent dans le conteneur (neuf à chaque déploiement), le journal des lots en base (qui survit) ; T-187-16 écrit ce qui recrée l'un et l'autre au démarrage et T-187-19 le vérifie dans un conteneur neuf, avant le merge. Défaut #100 du sprint 27 : même cause.
- Tests de la démo uniquement par `WORKER_ID=w187 TEST_DB_NAME=b2b_w187 TEST_CACHE_DIR=var/cache-w187 bin/run-tests` ; preuve : `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Pages publiques en production** (lecture seule, sans connexion) : `/echange-fichier` et la documentation. **La page du back-office n'est pas ouverte en production** (aucun identifiant n'est saisi) : elle est vérifiée en local et sur l'image reconstruite (T-187-19, T-187-20). Mesures consignées le jour du déploiement.

## Budget disque du dépôt du plugin

`vendor/` du plugin (à relever à l'installation) et une base de test à son nom, supprimée à la fin du lot ; relevé de `df -h .` avant et après (T-TECH-28-06).

## Fichiers principaux touchés

- Plugin : `src/Import/` (nouveau), `src/Entity/` (journal des lots), `src/Migrations/`, `src/Command/` (commande d'import), `src/DependencyInjection/`, `tests/`, `README.md`, `README.fr.md`, `CHANGELOG.md`, `composer.json`
- Démo : `composer.json`, `composer.lock`, `src/Demo/Erp/` (simulateur, journal), `src/Demo/Command/DemoResetCommand.php`, `src/Demo/DataFixtures/` (partagé avec US-195 et US-196 : nouveaux fichiers seulement), `templates/admin/` ou `templates/demo/` (page des échanges), `translations/`, `docs/erp-demo.md`, `docs/adr/0017-*.md`, `tests/`, `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

### Tranche A : plugin (5 points)

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-187-01 | [DOC] | [A] Cadrage : ADR 0017 (contrat, questions 3, 4 et 6), plugin `sylius-erp-flatfile-plugin` v0.1.0 (`OrderLinesCsv`, `AtomicFileDepositor`, `ExportJournal`, `ExportedEvent`), simulateur et journal de la démo, `DemoResetCommand`, modèle `ChannelPricing` et stock de Sylius ; **politique Sylius Plus** : documentation officielle de Plus lue sur l'import de prix et de stock, sources notées ; propositions de réponse aux questions 3, 4 et 6 | 0,5 h | - | Claude | 🔲 |
| T-187-02 | [OPS] | [A] Thibaut a tranché les questions 3, 4 et 6 de l'ADR le 2026-10-25 : prix par canal (code de variante + canal + prix, `ChannelPricing`), sans groupe de clients ; stock en valeur absolue `on_hand`, sans toucher à `on_hold` ; aucune rétention dans le plugin, remise à zéro nocturne dans la démo | 0,25 h | T-187-01 | Thibaut | ✅ |
| T-187-03 | [DOC] | [A] Affinage de la fiche : scénarios (1 nominal, 2 alternatifs, 2 erreurs pour le plugin ; 1 nominal et 1 erreur pour la démonstration), découpage A (plugin) / B (démo), ADR 0017 section « Décision (2026-10-24, sprint 28) » | 0,5 h | T-187-02 | Claude | 🔲 |
| T-187-04 | [TEST] | [A] Tests rouges du plugin, commités seuls et cités dans la PR du plugin : lot de prix valide, lot de stock partiellement valide, lot déjà traité, lot de prix invalide (aucune écriture), fichier tronqué ou d'un autre encodage | 1,75 h | T-187-03 | Claude | 🔲 |
| T-187-05 | [BE] | [A] Plugin, lecture et validation : encodage UTF-8, séparateur `;`, en-tête, total de contrôle ; prix (code de variante connu, entier positif, devise du canal) ; stock (code connu, entier) ; rapport de rejet ligne par ligne | 2 h | T-187-04 | Claude | 🔲 |
| T-187-06 | [DB] | [A] Plugin, journal des lots : entité et migration (nom, total de contrôle, date, lignes lues, appliquées, rejetées, rapport), clé d'idempotence nom + total de contrôle, lot déjà traité ignoré et signalé | 1,5 h | T-187-04 | Claude | 🔲 |
| T-187-07 | [BE] | [A] Plugin, application et commande : prix en tout ou rien dans une transaction (aucune écriture partielle), stock : lignes valides appliquées et autres rejetées ; commande console d'import ; dépôt d'un fichier traité dans un sous-répertoire (atomique) | 2 h | T-187-05, T-187-06 | Claude | 🔲 |
| T-187-08 | [DOC] | [A] Plugin : README FR et EN (contrat des deux fichiers, journal, idempotence, ce qui n'est pas fait), CHANGELOG, mention de la politique Plus relue (aucune comparaison tant que le recouvrement n'est pas levé) | 0,75 h | T-187-07 | Claude | 🔲 |
| T-187-09 | [TEST] | [A] Plugin : ECS, PHPStan max, Rector, tests sur la matrice CI du dépôt (Docker, MySQL de la démo, base supprimée à la fin) ; commande exacte et dernière ligne citées ; budget disque relevé | 0,5 h | T-187-08 | Claude | 🔲 |
| T-187-10 | [OPS] | [A] 📅 **GO de Thibaut pour push, tag et release** du plugin (branche, PR, tag `v0.2.0`, release) ; aucun push, tag ni release avant ce GO ; GO noté au registre | 0,1 h | T-187-09 | Thibaut | 📅 |
| T-187-11 | [OPS] | [A] 📅 Après le GO : push de la branche, PR du plugin, CI verte (run terminé), merge sur GO, tag `v0.2.0` et release ; concordance de la version avec Packagist vérifiée | 0,5 h | T-187-10 | Claude | 📅 |
| T-187-12 | [OPS] | [A] 📅 Thibaut soumet ou met à jour le paquet sur Packagist s'il le faut (Claude ne soumet jamais) et confirme que `v0.2.0` y est visible | 0,1 h | T-187-11 | Thibaut | 📅 |

**Sous-total A : 10,45 h** (12 tâches ; 10 h pour Claude, 0,45 h de Thibaut).

### Tranche B : démonstration (3 points)

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-187-13 | [TEST] | [B] Tests rouges de la démo, commités seuls et cités dans la PR : simulateur qui dépose un fichier de prix et de stock d'exemple (atomique) et un lot volontairement invalide, import au chargement des fixtures, page « Échanges ERP (fictif) » avec lots entrants d'import (lus, appliqués, rejetés, rapport), remise à zéro, aucun appel sortant, charte (aucun ERP réel nommé) | 0,75 h | T-187-03 | Claude | 🔲 |
| T-187-14 | [BE] | [B] 📅 Montée de version : `tmonier/sylius-erp-flatfile-plugin` en `^0.2`, `composer.lock`, migration du plugin, configuration ; développement avant le tag sur une copie locale du plugin **non commitée** ; commit de `composer.json` et du verrou seulement après le tag visible sur Packagist | 0,25 h | T-187-12 | Claude | 📅 |
| T-187-15 | [BE] | [B] Simulateur « ERP Démo (fictif) » : produit un fichier de prix et de stock d'exemple à partir du catalogue de la démo (codes de variante réels, valeurs fictives) et un fichier volontairement invalide (montant négatif, devise étrangère au canal) ; dépôt atomique ; aucun accès réseau | 1,25 h | T-187-13 | Claude | 🔲 |
| T-187-16 | [BE] | [B] Import dans la démo : commande du plugin lancée après la simulation, au chargement des fixtures et à la remise à zéro nocturne (répertoire d'échange, journal des lots d'import vidés puis régénérés) ; **« que devient cet état après un déploiement ? »** : répertoire dans le conteneur, journal en base, ce qui recrée l'un et l'autre au démarrage, vérifié dans un conteneur neuf | 1 h | T-187-14, T-187-15 | Claude | 🔲 |
| T-187-17 | [FE-WEB] | [B] Page « Échanges ERP (fictif) » étendue : lots entrants d'import (nom, total de contrôle, lignes lues, appliquées, rejetées), rapport de rejet lisible, lot ignoré signalé, bandeau « simulé », FR et EN ; accessible (AAA), sans défilement horizontal à 360 px | 1,25 h | T-187-16 | Claude | 🔲 |
| T-187-18 | [DOC] | [B] `docs/erp-demo.md` (schéma, fichiers de prix et de stock, ce qui est simulé), lien depuis `/echange-fichier`, ADR 0017, README ; texte relu contre le code | 0,5 h | T-187-17 | Claude | 🔲 |
| T-187-19 | [TEST] | [B] Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; image reconstruite démarrée en conteneur neuf (échange et lots d'import présents) ; commande exacte et dernière ligne citées | 0,75 h | T-187-18 | Claude | 🔲 |
| T-187-20 | [TEST] | [B] Vérification après déploiement : pages publiques en production (`/echange-fichier`, documentation : pa11y, 360 px) ; page du back-office en local et sur l'image reconstruite (aucun identifiant saisi en production) ; échange présent après le déploiement ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 🔲 |
| T-187-21 | [REV] | [B] Revue (ton neutre, « ERP Démo (fictif) » partout, aucun éditeur nommé, aucune comparaison avec Sylius Plus) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-187-20 | Claude | 🔲 |

**Sous-total B : 6,75 h** (9 tâches ; 6,75 h pour Claude).

**Total estimé : 17,2 h** (21 tâches ; 16,75 h pour Claude, 0,45 h de Thibaut).

## Scénarios et preuves

Une ligne vide laisse la story en review. Plugin : 1 nominal, 2 alternatifs, 2 erreurs (fiche) ; démonstration : 1 nominal et 1 erreur.

| Scénario | Test ou preuve |
|----------|----------------|
| Lot de prix valide (nominal, plugin) | |
| Lot de stock partiellement valide (alternatif, plugin) | |
| Lot déjà traité (alternatif, plugin) | |
| Lot de prix invalide (erreur, plugin) | |
| Fichier illisible (erreur, plugin) | |
| Fichier de prix et de stock d'exemple importé et visible à la page des échanges (nominal, démonstration) | |
| Lot volontairement invalide rejeté, rapport lisible, aucune écriture de prix (erreur, démonstration) | |
| Échange et lots d'import présents après un déploiement (conteneur neuf) | |

## Mesures de production (jour du déploiement)

_À consigner par T-187-20 : pages publiques (pa11y, 360 px), échange présent après le déploiement._

## Registre des GO

- 2026-10-25 : questions 3, 4 et 6 tranchées par Thibaut (T-187-02) ; ordre de sacrifice validé ; le GO du tag `v0.2.0` sera demandé quand la tranche A sera prête.
- _À remplir : GO push/tag/release du plugin (T-187-10), confirmation Packagist (T-187-12)._
