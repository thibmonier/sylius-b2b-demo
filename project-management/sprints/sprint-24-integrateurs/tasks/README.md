# Tâches : Sprint 024 (intégrateurs)

> Sprint Planning Part 2 : décomposition faite au lancement du sprint, le 2026-10-23, sur la branche `feature/sprint-24-integrateurs`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-24`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 23](../../sprint-23-garde-fous/sprint-retro.md), [revue du sprint 23](../../sprint-23-garde-fous/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-174](./US-174-tasks.md) | Montée de la démo à PHPUnit 13 | 3 | 11 | 8 h | non sacrifiable | 🔲 |
| [US-171](./US-171-tasks.md) | Matrice de compatibilité et graphe des dépendances | 3 | 9 | 7,25 h | non sacrifiable | 🔲 |
| [US-172](./US-172-tasks.md) | Guide de surcharge des plugins | 3 | 10 | 7,75 h | non sacrifiable | 🔲 |
| [US-179](./US-179-tasks.md) | Lisibilité mobile du schéma d'architecture et de la barre de démo | 1 | 6 | 2,5 h | non sacrifiable | 🔲 |
| [US-132](./US-132-tasks.md) | Spike : facture électronique France (PA/PDP) et e-reporting | 3 | 7 | 4,5 h | n°2 | 🔲 |
| [US-130](./US-130-tasks.md) | Spike : connecteur ERP de référence | 3 | 7 | 4,5 h | n°1 | 🔲 |
| [Transverse](./technical-tasks.md) | Ressources, contrôles avant push et avant done, preuve, statuts, revue et rétro (sans points) | 0 | 10 | 5 h | non sacrifiable | 🔲 (2 ✅) |
| **Engagé** | | **16** | **60** | **39,5 h** | | |
| [US-175](./US-175-tasks.md) (conditionnelle) | Métapaquets des packs « IA et headless » et « Conformité et outillage » | 3 | 10 | 6,25 h | n°3 | 📅 GO de Thibaut |
| **Avec US-175** | | **19** | **70** | **45,75 h** | | |

**Total décomposé : 16 points engagés, 60 tâches, 39,5 h**, dont 1,25 h d'actions de Thibaut (une décision par spike, un accord de repli éventuel, un GO de README, un déploiement) : **38,25 h pour Claude**, orchestrateur compris. Avec US-175 : 19 points, 70 tâches, 45,75 h, dont 1,75 h de Thibaut (**44 h pour Claude**).

> **Périmètre décidé par le PO le 2026-10-23.** Les spikes US-132 et US-130 (sortis du sprint 23 le jour de son lancement) reviennent. US-171 et US-172 répondent à la critique de l'architecte Sylius et du client B2B. US-174 est la montée de PHPUnit 13 décidée à la fin du spike US-169. US-179 reprend les constats de la vérification en production d'US-170. US-175 (ADR 0015) n'entre que si Thibaut donne son GO pour les deux dépôts et valide les noms des paquets.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 16 points engagés | 16 × 2,1 + 8 = **41,6 h** | 38,25 h | **3,35 h** |
| 19 points (US-175 incluse) | 19 × 2,1 + 8 = **47,9 h** | 44 h | **3,9 h** |

Aucun sacrifice n'est prévu. Si la charge dépasse la capacité, ordre de sacrifice décidé par le PO : **US-130, puis US-132, puis US-175**.

| # | Sacrifice | Heures Claude libérées | Charge restante (avec US-175) | Charge restante (sans US-175) | Conséquence |
|---|-----------|------------------------|-------------------------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 44 h (19 points) | 38,25 h (16 points) | Marge de 3,9 h ou de 3,35 h |
| 1 | US-130 entière (spike ERP) | 4,25 h | 39,75 h (16 points) | 34 h (13 points) | Pas d'ADR 0017 ; le connecteur ERP reste au backlog |
| 2 | US-132 entière (spike facture électronique) | 4,25 h | 35,5 h (13 points) | 29,75 h (10 points) | Pas d'ADR 0016 |
| 3 | US-175 entière (métapaquets) | 5,75 h | 29,75 h (10 points) | n/a (déjà au backlog) | Les deux packs restent au backlog |

Un spike sacrifié au milieu de son budget est arrêté et son état consigné (scénario « Dépassement » ou « Spike hors budget »), pas abandonné en silence.

## Répartition par type

| Type | Tâches (engagé) | Heures (engagé) | dont US-175 (tâches / heures) |
|------|-----------------|-----------------|-------------------------------|
| [TEST] | 16 | 12,5 h | 2 / 1,5 h |
| [DOC] | 20 | 13,25 h | 2 / 1,75 h |
| [OPS] | 9 | 4,25 h | 3 / 1 h |
| [FE-WEB] | 3 | 4,25 h | 0 / 0 h |
| [BE] | 3 | 2,5 h | 2 / 1,75 h |
| [REV] | 9 | 2,75 h | 1 / 0,25 h |
| [DB] / [FE-MOB] | 0 | 0 h | 0 / 0 h |
| **Total** | **60** | **39,5 h** | **10 / 6,25 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-174 | US-171 | US-172 | US-179 | US-132 | US-130 | US-175 |
|-----------------|:------:|:------:|:------:|:------:|:------:|:------:|:------:|
| `tests/` (une quinzaine de fichiers existants) | ● (typage) | ● (nouveaux) | ● (nouveaux) | ● (deux existants) | | | |
| `tests/Functional/Demo/ArchitecturePageTest.php` | ● (si typé) | | | ● | | | |
| `config/demo/proof.json` | ● (rafraîchi) | ● (rafraîchi) | ● (rafraîchi) | | | | |
| `src/Demo/Proof/`, `bin/proof-refresh`, `bin/proof-check` | | ● | | | | | |
| `config/demo/compatibility.json`, `config/packages/demo_compatibility.yaml` | | ● | | | | | |
| `src/Demo/Controller/`, `translations/` | | ● (nouveaux) | ● (nouveaux) | ● (`theme_demo.fr.yaml`) | | | |
| `templates/demo/architecture.html.twig` | | | | ● | | | |
| `templates/demo/banner.html.twig` | | | | ● | | | |
| `templates/demo/integrate.html.twig` | | | ● | | | | |
| `templates/Shop/plugins/` | | ● | | | | | |
| `docs/theme-b2b.md` | | ● | | | | | |
| `composer.json`, `composer.lock`, `phpunit.xml.dist` | ● | | | | | | |
| `docs/adr/0016-*.md` | | | | | ● | | |
| `docs/adr/0017-*.md` | | | | | | ● | |
| `docs/adr/index.md` | | | | | ● | ● | |
| `docs/vitrine-plugins.md`, `docs/adr/0015-*.md` | | | | | | | ○ |
| README des plugins (une PR par dépôt, après GO) | | | ● | | | | |
| Deux nouveaux dépôts de packs | | | | | | | ● |
| `docs/tests-locaux.md`, `project-management/templates/`, `.bmad/sprint-status.yaml` | | | | | | | |

La dernière ligne n'est modifiée que par l'orchestrateur : les workers n'y touchent pas.

**Conflits à prévoir** :

1. **`config/demo/proof.json` : US-174, US-171 et US-172.** Chaque PR qui ajoute des fichiers de tests change les mêmes compteurs. Après chaque rebase, rejouer `php bin/proof-refresh` puis `php bin/proof-check`, ne jamais fusionner le fichier à la main (T-TECH-24-08).
2. **`tests/` : US-174 contre tout le sprint.** US-174 réécrit une quinzaine de fichiers de tests pour les types ; les autres stories en ajoutent ou en modifient. **Ordre retenu : US-174 est mergée en dernier.** Raisons : son diff est mécanique et se rejoue sur l'arbre final (PHPStan et la CI désignent les erreurs restantes) ; l'inverse ferait réécrire trois branches contre PHPUnit 13 en cours de sprint ; son repli (fermeture de la PR #54) ne bloque alors aucune autre story ; la preuve est rafraîchie une dernière fois sur l'arbre complet. Condition : les nouveaux tests d'US-171, US-172 et US-179 évitent déjà l'accès par clé après `assertIsArray`, pour ne pas ajouter d'erreurs au lot de typage.
3. **`ArchitecturePageTest.php` : US-179 (modifie) et US-174 (typage éventuel).** Couvert par l'ordre précédent ; US-174 se rebase sur la version d'US-179.
4. **`templates/demo/architecture.html.twig` : seulement US-179.** US-171 relie sa page depuis la page Plugins et US-172 depuis la page Intégrer, pas depuis l'Architecture.
5. **`docs/adr/index.md` : US-132 et US-130.** L'orchestrateur est le seul écrivain (T-132-07, T-130-07) ; les workers écrivent chacun leur ADR (0016, 0017).
6. **`src/Demo/Proof/` : US-171 modifie, US-174 touche `ProofDataFileTest` et `ProofReaderTest`.** Couvert par l'ordre du point 2.
7. **`.bmad/sprint-status.yaml`, `docs/tests-locaux.md`, `project-management/templates/`** : un seul écrivain, l'orchestrateur.

**Parallélisme possible** (un seul serveur lourd à la fois) :
- voie A (code de la démo, `bin/run-tests` et démo locale) : US-179, puis US-171, puis US-172 ; l'ordre place d'abord la plus petite pour éprouver la vérification en production ;
- voie B (lecture et rédaction, sans serveur) : US-132 puis US-130, avec accès réseau en lecture seule ;
- voie C : US-174 sur la branche de la PR #54, mesurée par la CI seulement (pas de second `vendor/` local) ;
- voie D, si le GO arrive : US-175 dans des dossiers hors dépôt ; les vérifications Composer n'installent rien.

**Ordre de merge proposé** : la PR du sprint (US-179, US-171, US-172, ADR 0016 et 0017) d'abord, puis le déploiement de `main` par Thibaut (T-TECH-24-09) pour la vérification du rendu, puis la PR #54 (US-174) en dernier, rebasée sur `main` avec `proof-refresh` rejoué.

## Lieu de vérification du rendu des stories d'interface (action 4 de la rétro du sprint 23)

| US | Lieu | Procédure | État de la story avant la preuve |
|----|------|-----------|----------------------------------|
| US-171 | Production après déploiement, lecture seule | pa11y WCAG2AAA FR et EN, 360 px (`docs/tests-locaux.md`, « Vérifier le rendu en production ») | review |
| US-172 | Production après déploiement, lecture seule | idem | review |
| US-179 | Production après déploiement, lecture seule | idem, sur `/fr_FR/architecture` et `/fr_FR/preuve`, plus 1280 px | review |

Raison : environ 6 Go libres, l'image ne se construit pas en local (seuil de 10 Go). Si Thibaut libère au moins 10 Go avant la fin du sprint, la vérification peut se faire en local avant le merge (`bin/measure-mobile`).

## Actions de la rétrospective du sprint 23 appliquées

| Action | Application dans ce sprint |
|--------|----------------------------|
| 1. Lire sans filtrage avant de modifier | ✅ Faite au planning : règle 9 de `consigne-worker.md` (T-TECH-24-01) ; indicateur mesuré à la rétro : 0 commentaire supprimé par erreur |
| 2. Pièges du shell documentés | ✅ Faite au planning : section « Pièges connus » de `docs/tests-locaux.md` (T-TECH-24-02) ; indicateur : 0 commande de fond bloquée |
| 3. Rafraîchir la preuve dans la PR | T-TECH-24-08 ; ligne déjà dans `checklist-integration.md` ; indicateur : 0 build de `main` en échec à l'étape « Published proof is up to date » |
| 4. Rendu vérifiable avant le merge | Tableau ci-dessus ; procédure de production dans `docs/tests-locaux.md` ; T-TECH-24-06 et T-TECH-24-09 |

Actions du sprint 22 maintenues : table « scénario → test ou preuve » dans chaque fichier de tâches (T-TECH-24-06), commit de tests rouges cité dans la PR pour les stories de code, budget de ressources (T-TECH-24-03), statuts cochés dans le commit qui livre (T-TECH-24-07), clôture dès le périmètre vérifié.

## Actions de Thibaut

| ID | Action | Durée |
|----|--------|-------|
| T-175-01 | GO pour créer les deux dépôts de packs et validation des noms des paquets (condition d'entrée d'US-175) | 0,25 h |
| T-132-06 | Relire l'ADR 0016 et décider (périmètre, stories) | 0,25 h |
| T-130-06 | Relire l'ADR 0017 et décider (périmètre, stories) | 0,25 h |
| T-172-08 | Relire les diffs des README de plugins et donner le GO des PR | 0,25 h |
| T-174-11 | Accord de repli (fermeture de la PR #54, Dependabot), seulement si la montée échoue | 0,25 h |
| T-TECH-24-09 | Déployer `main` sur Railway après le merge du sprint | 0,25 h |
| T-175-10 | Soumettre les deux paquets à Packagist (si US-175 entre) | 0,25 h |

Notes :
- **Aucun push, tag, release ni création de dépôt sans GO de Thibaut** ; commit, push et PR de la démo sur demande ou GO permanent (« fusionner les PR dès qu'elles sont vertes »). Le push de la branche de la PR #54 pour la mesure par la CI est annoncé avant d'être fait.
- **Railway** : aucune commande par Claude ; vérification en production en lecture seule, sans connexion.
- **Politique** : ton neutre, aucune comparaison avec une offre commerciale (Sylius Plus), y compris dans les ADR, les README et les pages ; aucun plugin classé « concurrent » ou « à confirmer » dans un pack.
- **Sécurité** : aucun détail exploitable dans le dépôt public (routes, valeurs, seuils) ; les pages restent en termes généraux.

## Consigne de disque et de tests des workers

1. `df -h .`, `bin/check-disk` et mémoire libre avant chaque lot (environ 6 Go libres ; `bin/run-tests` refuse sous 5 Go, seuil jamais abaissé).
2. Tests de la démo : **uniquement** `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`. **Jamais phpunit en direct.** Identités proposées : w179, w171, w172 (voie A, une à la fois), w132 et w130 (voie B, sans serveur), w174 (voie C, CI seulement), w175 (voie D).
3. Un seul serveur lourd à la fois (démo locale ou MySQL de test), arrêté dès la fin de la vérification.
4. ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push (T-TECH-24-04).
5. Toute PR qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check` (T-TECH-24-08).
6. Refus d'outil : ne jamais le contourner ; le noter dans le rapport et continuer (règle 8 de `project-management/templates/consigne-worker.md`).
7. Lecture d'un fichier à modifier : outil Read ou `rtk proxy cat`, jamais `cat`, `sed` ou `head` filtrés (règle 9) ; diff d'un refactoring relu pour les commentaires supprimés.
8. Pièges du shell (`rm` aliasé, `sed -E`, sortie de test écrite dans un fichier, `catchExceptions(false)`) : section « Pièges connus » de `docs/tests-locaux.md`. Suppressions avec `/bin/rm`.
9. Chaque worker place le « Rapport de fin » du gabarit `consigne-worker.md` en tête de sa réponse finale.

## Prérequis d'environnement par story

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-174 | Branche de la PR #54 récupérée (`gh pr checkout 54`) et rebasée ; CI comme instrument de mesure ; GO de push annoncé | T-174-01 |
| US-171 | Accès `gh` en lecture aux 25 dépôts (comme `bin/proof-refresh`) ; conteneur MySQL du projet pour `bin/run-tests` ; démo à jour | T-171-01, T-TECH-24-03 |
| US-172 | Démo à jour ; clones locaux des dépôts de plugins à jour pour les README | T-172-01 |
| US-179 | Démo à jour ; Chromium et pa11y en dossier jetable pour la production | T-179-01, T-179-05 |
| US-132 | Accès web en lecture aux sources officielles ; dépôt du plugin `facturx` à jour | T-132-01 |
| US-130 | Accès web en lecture à la documentation publique des éditeurs ; dépôt `webhook-outbox` à jour | T-130-01 |
| US-175 | GO de Thibaut et noms des paquets validés ; dossier de travail hors dépôt | T-175-01 |
