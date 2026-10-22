# Sprint 21 : Parc d'équipements (cœur du plugin, aperçu des approbations, jauge, journal d'audit et devis)

> **Engagement final : 17 points (~61 h, 59 h exécutables hors T-TECH-07 bloquée).** Périmètre validé par Thibaut le 2026-10-22 (variante B, 25 points) ; US-154 (3 pts), US-088 (3 pts) et US-098 (2 pts) sont sacrifiées le 2026-10-22 (capacité) et reportées au sprint 22 (sacrifices 1 à 3 de l'ordre validé ; la décomposition donnait 85 h de charge Claude pour 60,5 h de capacité). Engagé : US-056, US-057, US-058 (MMF, 15 points) et US-157 (2). **Dépassement résiduel : 0,5 h** (61 h pour 60,5 h), absorbé par T-TECH-07 (2 h) bloquée tant que Railway est suspendu ; réserve : US-157 (sacrifice n°4).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 21 |
| Début | 2027-04-02 (revue et rétro du sprint 20 ; démarrage effectif le 2027-04-05) |
| Fin | 2027-04-16 |
| Durée | 10 jours ouvrés (2027-04-05 au 2027-04-16) |
| Capacité | **17 points engagés** (4 stories, 0 Must, 17 Should/Could) : 25 points validés sur 41 candidats, moins US-154, US-088 et US-098 (8 pts) sacrifiées le 2026-10-22. Capacité en heures : **60,5 h** (25 points × 2,1 h + 8 h de tâches transverses, méthode du sprint 20) ; charge décomposée de Claude 85 h, ramenée à **61 h** (dépassement résiduel de 0,5 h ; 59 h exécutables hors T-TECH-07 bloquée), voir [tasks/README.md](./tasks/README.md) |
| Branche | `feature/sprint-21-parc-equipements` (créée depuis `main`, a8c1ebd) |

Périmètre issu de la section « Sprint 21 » de la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md) (41 points) et des reports du [sprint 20](../sprint-20-demo-enrichie/sprint-retro.md), arbitré par Thibaut au planning du 2026-10-22.

## Sprint Goal

> « Livrer le cœur fonctionnel du plugin parc d'équipements (modèles d'équipement compatibles en administration, parc du client dans son espace, filtre « pièces compatibles » au catalogue), préparé pour une première release 0.1.0 soumise par Thibaut, et compléter la démo de finitions de lisibilité (aperçu des approbations, jauge de remplissage, journal d'audit, devis et économies). »

(La montée de la démo sur le plugin parc d'équipements reste au sprint 22 : US-059 et US-060, après le GO de Thibaut, la soumission du nouveau paquet à Packagist et la vérification de la version publiée. Le sprint 21 livre du code testé et documenté dans le dépôt du plugin, pas une démo visible du filtre.)

## Calcul de capacité et vélocité

| Sprint | Points planifiés | Points livrés (en review) |
|--------|------------------|---------------------------|
| 18 | 36 | 25 (69 %) |
| 19 | 25 | 25 (100 %) |
| 20 | 22 | 22 (100 %) |
| Moyenne | 27,7 | 24 |

Lecture : 25 points est la vélocité de référence (S18 et S19 livrés). La variante B l'atteint sans marge ; le plugin neuf (entités, migrations, voter, extension de requête) coûte 2,8 h par point contre 2,1 h dans la méthode, d'où le dépassement de 24,5 h de la décomposition. Plancher : **15 points** (le MMF). Aucun Must dans le sprint : tout est sacrifiable, sauf la cohérence du MMF. Ordre de sacrifice et charge restante : [tasks/README.md](./tasks/README.md), section Capacité.

| # | Sacrifice | Charge Claude restante |
|---|--------------------------|------------------------|
| 0 | (charge décomposée) | 85 h |
| 1 | **Appliqué** : US-154 (3 pts) | 72,25 h |
| 2 | **Appliqué** : US-088 (3 pts) | 65 h |
| 3 | **Appliqué** : US-098 (2 pts) | 61 h (59 h hors T-TECH-07, bloquée tant que Railway est suspendu) |
| 4 | (réserve) US-157 (2 pts) | 50,5 h : le MMF seul (15 pts) |

## Périmètre engagé

Gherkin : minimum 1 nominal + 2 alternatifs + 2 erreurs. « Fiche » = fichier `backlog/user-stories/US-XXX.md`. Statuts au 2026-10-22 : toutes les stories sont `ready-for-dev`.

| ID | Titre | Pts | Gherkin (nominal / alt. / erreur) | Statut | Sacrifice |
|----|-------|-----|-----------------------------------|--------|-----------|
| [US-056](../../backlog/user-stories/US-056.md) | Plugin équipements : modèles d'équipement liés aux variantes (admin) | 5 | 1 / 3 / 3 (fiche écrite au planning) | ready-for-dev | MMF |
| [US-057](../../backlog/user-stories/US-057.md) | Plugin équipements : parc du client dans l'espace client (CRUD) | 5 | 1 / 4 / 4 (fiche écrite au planning) | ready-for-dev | MMF |
| [US-058](../../backlog/user-stories/US-058.md) | Plugin équipements : filtre « pièces compatibles » | 5 | 1 / 3 / 4 (fiche écrite au planning) | ready-for-dev | MMF |
| [US-157](../../backlog/user-stories/US-157.md) | Purchase-approval : aperçu des approbations en attente depuis l'en-tête | 2 | 1 / 2 / 2 | ready-for-dev | n°4 |
| [US-098](../../backlog/user-stories/US-098.md) | Jauge de remplissage condensée sur la fiche produit | 2 | 1 / 2 / 2 (complété au planning) | backlog (sacrifiée le 2026-10-22) | **Sacrifiée, reportée au sprint 22** |
| [US-088](../../backlog/user-stories/US-088.md) | Journal d'audit lisible : reset masqué et événements métier | 3 | 1 / 2 / 2 (complété au planning) | backlog (sacrifiée le 2026-10-22) | **Sacrifiée, reportée au sprint 22** |
| [US-154](../../backlog/user-stories/US-154.md) | Company-insights : conversion des devis et économies des prix dégressifs | 3 | 2 / 2 / 3 (complété au planning) | backlog (sacrifiée le 2026-10-22) | **Sacrifiée, reportée au sprint 22** |

Total validé : 25 points, **engagé : 17 points** après les sacrifices du 2026-10-22 (MMF 15 + US-157 2 ; US-098 2, US-088 3 et US-154 3 sacrifiées, reportées au sprint 22).

**Hors périmètre, retournées au backlog** (bloc `backlog:` de `.bmad/sprint-status.yaml`, mention « reportée, sprint 22+ ») : US-093 (8 pts, catalogue de 150 références, dépend du disque), US-101 (5 pts, factures PDF et relevé de compte), US-130 (3 pts, spike connecteur ERP). Total reporté : 16 points.

**MMF du plugin** : US-056 + US-057 + US-058 forment ensemble la plus petite fonction livrable (administrer les modèles compatibles, déclarer son parc, filtrer le catalogue). Aucune des trois ne vaut seule : US-057 et US-058 dépendent d'US-056 ; US-058 dépend d'US-057 (parc non vide). Elles se sacrifient ensemble ou pas du tout.

## Sprint Backlog

Voir la section `sprint-21` de `.bmad/sprint-status.yaml` (source unique des statuts) et [tasks/README.md](./tasks/README.md) (90 tâches décomposées, 61 engagées pour 62,25 h après les sacrifices). Stories engagées (17 points) : US-056 (5), US-057 (5), US-058 (5), US-157 (2). US-098 (2), US-088 (3) et US-154 (3) sacrifiées le 2026-10-22, reportées au sprint 22.

## Décisions de Thibaut du planning (2026-10-22)

1. **Périmètre B, 25 points, ramenés à 17 par les sacrifices du 2026-10-22 (US-154, US-088, US-098)** : US-056, US-057, US-058 (MMF), US-157, US-098, US-088, US-154. Ordre de sacrifice : US-154, US-088, US-098, US-157 ; plancher = le MMF (15 points). US-093, US-101 et US-130 retournent au backlog (« reportée, sprint 22+ »).
2. **Disque : dérogation encadrée** à l'action 1 de la rétro S20 (10 Go libres) : **un seul worker à la fois, budget ≤ 3 Go par worker, nettoyage de ses propres caches, `vendor` et image en fin de tâche, arrêt sous 4 Go libres**. Consignée dans les risques ci-dessous et dans la consigne des workers ([tasks/README.md](./tasks/README.md), T-TECH-01).
3. **Fiches d'US-056, US-057 et US-058 écrites** au planning (gabarit `templates/user-story.md`, d'après les cartes de la feuille de route, `SPEC.md` et l'ADR-0001 du plugin, lus sans modification) : Gherkin 1+2+2 minimum, rubrique « Versions et publication », DoD. Nouveau paquet : première release **0.1.0**, soumission à Packagist par Thibaut seul, tags après son GO.
4. **Gherkin complétés** pour US-098, US-088 et US-154 (minimum 1+2+2, SMART, cohérents avec les fiches), puis passées `ready-for-dev`.
5. **T-113-06** (captures d'accueil du README de la démo) reprise en tâche technique (T-TECH-05).
6. **Promotion du plugin parc d'équipements : suspension levée** (aucun recoupement avec Sylius Plus documenté). Consignée dans `docs/vitrine-plugins.md` et la feuille de route ; **aucune comparaison avec Sylius Plus dans les textes publics** ; la neutralisation de `docs/plus-overlap.md` et de la mention du README du plugin est la tâche T-TECH-06 (le dépôt du plugin n'a pas été modifié au planning).
7. **Tâches techniques à prévoir** : PHP 8.5 dans la CI de purchase-approval (écart T-TECH-08 du S20, T-TECH-03), vérification en production des sprints 18 à 20 à la reprise de Railway (bloquée, T-TECH-07), actions SMART de la rétro S20 (T-TECH-01 à T-TECH-03, T-TECH-07 et T-TECH-08).

## Contraintes consignées

- **Railway toujours suspendu** : la production n'a reçu aucune des PR #43 à #49. Tout reste vérifié en démo locale ; la vérification en production (T-TECH-07 et contrôle par story des sprints 18 à 20) est reportée à la reprise de la plateforme ; le déploiement groupé reste une commande de Thibaut (T-TECH-08).
- **Disque local : 5,6 Go libres** (`df -h .`, relevé du 2026-10-22), sous le seuil de 10 Go de l'action 1 du sprint 20 : **dérogation encadrée** (décision 2). Un seul worker et un seul build d'image à la fois, `bin/check-disk` avant et après chaque worker de plugin, relevé dans chaque rapport, refus d'une suppression par l'outil signalé à Thibaut sans arrêter le sprint. `bin/run-tests` refuse sous 5 Go (seuil non abaissé) : la marge des stories de démo est de 0,6 Go (voir le risque ci-dessous).
- **Plugin parc d'équipements** : dépôt `~/Projects/sylius_plugins/sylius-customer-asset-management-plugin` existant (squelette seul : SPEC.md, ADR-0001, `docs/plus-overlap.md`, CI sur PHP 8.3 et 8.4, aucune entité ni écran, aucun tag, jamais soumis à Packagist). Il est sur la branche fusionnée `docs/readme-demo-profile` : `git fetch`, puis branche de travail créée depuis `origin/main` avant tout commit. **Nouveau paquet : première release 0.1.0, soumission à Packagist par Thibaut seul** ; Claude pose les tags uniquement après son GO ; aucun push, PR, merge ni tag sans confirmation.
- **Sylius Plus** : promotion du plugin parc d'équipements **levée par Thibaut le 2026-10-22** (aucun recoupement documenté). Politique inchangée pour les textes publiés : aucune comparaison avec Sylius Plus.
- **Sécurité** : aucun détail de faille dans un dépôt public ; revue des voters et de l'isolement des parcs rapportée hors dépôt. Les plaques et numéros de série sont des données personnelles (SPEC.md §7) : jamais journalisés ni dans les URLs.
- US-157 vit dans `sylius-purchase-approval-plugin` (US-154, company-insights, sacrifiée) : chaque dépôt de plugin coûte un worker et un vendor ; ordre de publication plugin, Packagist, puis démo.

## Tâches techniques à reprendre (sans points)

Reprises dans [tasks/technical-tasks.md](./tasks/technical-tasks.md), 12 tâches, 9,75 h (inchangées par les sacrifices).

| Origine | Tâche | Note |
|---------|-------|------|
| T-113-06 | T-TECH-05 | Décision de Thibaut du 2026-10-22 : reprise en tâche technique ; à faire avant le jour 5 |
| T-156-06 / T-TECH-08 (S20) | T-TECH-03, T-TECH-04 | PHP 8.5 dans la CI de purchase-approval, gabarit de CI documenté, captures du README ; groupées avec US-157 |
| T-TECH-02 (S20) | T-TECH-01 | Dérogation disque encadrée, relevé avant chaque worker |
| T-TECH-04 (S20) | T-TECH-02 | Gabarit de l'orchestrateur : gate recoupé, refus d'outil sans arrêt |
| T-TECH-05 / T-TECH-06 (S20) | T-TECH-08, T-TECH-07 | Déploiement groupé et vérification en production : bloquées, Railway suspendu |
| Promotion levée | T-TECH-06 | Neutralisation de `docs/plus-overlap.md` et du README du plugin (sans comparaison avec Plus) |
| Statuts | T-TECH-02 | Mettre à jour la ligne de chaque tâche faite dans un autre dépôt |

## Actions de la rétrospective du sprint 20

| Action (S20) | Responsable | Échéance | Prise en charge |
|--------------|-------------|----------|-----------------|
| 1. Budget d'espace disque des workers de plugins (refus sous 10 Go, 3 Go par worker, relevé avant et après) | Worker devops, Scrum Master | Planning du sprint 21 | **Écart constaté : 5,6 Go < 10 Go.** Dérogation encadrée de Thibaut (décision 2) ; T-TECH-01 ; DoD : 0 arrêt dû au disque, espace libre jamais sous 4 Go |
| 2. Gabarit de l'orchestrateur et statuts à jour | Orchestrateur | Début du sprint 21 | T-TECH-02 ; DoD : 0 écart de gate, 0 arrêt non demandé, 0 tâche livrée restée 🔲 |
| 3. Gabarit de CI de plugin et PHP 8.5 pour purchase-approval | Worker devops | Milieu du sprint 21 (point d'étape 2027-04-09) | T-TECH-03 et T-TECH-04, avec US-157 ; DoD : CI verte sur PHP 8.5, captures dans le README |
| 4. Valider les écarts et arbitrer le sprint 21 | Thibaut, Scrum Master | Avant le planning | **Fait le 2026-10-22** : décisions ci-dessus ; engagement de 25 points, ramené à 17 par les sacrifices du 2026-10-22 |
| 5. Déploiement et vérification en production | Thibaut | 7 jours ouvrés après la reprise | T-TECH-07 et T-TECH-08 ; hors équipe, bloquées par Railway |

## Jalons

- **Plugin parc d'équipements, tranche fonctionnelle** : modèles et compatibilité (admin), parc du client, filtre ; dans le dépôt du plugin, release 0.1.0 préparée (README, captures, CHANGELOG, CI verte sur PHP 8.2 à 8.5) ; tag, soumission Packagist et montée de la démo après le GO de Thibaut (US-059 et US-060, sprint 22 par défaut).
- **Purchase-approval** : aperçu des approbations en attente (US-157, v1.3.0) et PHP 8.5 dans la CI ; release après GO, puis bump de la démo (ordre : tag, Packagist, vérification, démo).
- **Company-insights** : widgets « Devis » et « Économies » (US-154, 0.3.0) : sacrifiée le 2026-10-22, reportée au sprint 22.
- **Démo** : jauge condensée (US-098) et journal d'audit lisible (US-088) : sacrifiées le 2026-10-22, reportées au sprint 22.

## Dépendances

Voir [sprint-dependencies.md](./sprint-dependencies.md).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| **Disque 5,6 Go sous le seuil de 10 Go** (action 1 de la rétro S20) | Élevée | Élevé | **Dérogation encadrée de Thibaut (2026-10-22)** : un seul worker à la fois, budget ≤ 3 Go par worker, nettoyage de ses propres caches, `vendor` et image en fin de tâche (tâche « Fin de worker » par story), arrêt sous 4 Go libres, relevé `df -h .` avant et après dans chaque rapport ; refus d'un outil signalé à Thibaut sans arrêter le sprint |
| `bin/run-tests` refuse sous 5 Go alors que l'arrêt de la dérogation est à 4 Go : 0,6 Go de marge pour les stories de démo (tests de démo d'US-157 ; US-098, US-088 et US-154 sacrifiées) | Élevée | Moyen | Nettoyer et relever avant chaque lot ; placer les stories de démo après un nettoyage ; le seuil de 5 Go n'est jamais abaissé ; en cas de refus, signaler à Thibaut et passer à une tâche de plugin (tests dans Docker) |
| **Dépassement de capacité** : 85 h de charge de Claude pour 60,5 h (+ 24,5 h) | Certaine | Élevé | Sacrifices 1 à 3 appliqués le 2026-10-22 (US-154, US-088, US-098) : reste 61 h (dépassement résiduel de 0,5 h), 59 h exécutables car T-TECH-07 (2 h) est bloquée ; réserve US-157 ; point d'étape du 2027-04-09 |
| Plugin neuf : nouvelle base de code (entités, migrations, voters, requête de listing) en un sprint | Moyenne | Moyen | Plancher de 15 points, TDD, ADR-0001 déjà accepté ; US-058 (extension de requête Doctrine) en dernier ; trois PR séquentielles |
| Cloisonnement des parcs entre clients (SPEC.md §4, §7) | Moyenne | Élevé | Tests « parc d'un autre client » en 404 ; revue de sécurité sans détail dans le dépôt public |
| Branche du plugin parc d'équipements restée sur une branche fusionnée | Moyenne | Moyen | `git fetch` et branche de travail depuis `origin/main` avant tout commit (T-056-01) |
| Matrice CI du plugin neuf élargie (PHP 8.2 à 8.5) : cellule rouge imprévue (Sylius 2.3 sur 8.5, PHPStan sur Symfony 6.4) | Moyenne | Moyen | T-056-02 en tête de sprint ; lecture du code pour les cellules non exécutées localement |
| Nouveau paquet : soumission à Packagist par Thibaut seul, après son GO | Faible | Moyen | Tâches conditionnelles T-058-12 à T-058-16 hors total (US-059 et US-060, sprint 22) ; script à OK ou FAIL (T-TECH-10) |
| Railway suspendu : rien en production depuis les PR #43 à #49 | Certaine | Moyen | Vérification en démo locale ; T-TECH-07 et T-TECH-08 bloquées jusqu'à la reprise |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation. Soumission à Packagist, publication LinkedIn et commandes Railway par Thibaut. `main` protégée : PR et contrôles requis, attendre la fin du run CI. Tests via `bin/run-tests` seulement. Un seul worker à la fois. Seul l'orchestrateur reconstruit l'image Docker. Aucune précision sur une faille dans un dépôt public. Rapports de fin ≤ 100 mots.

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts (stories de code)
- [ ] ECS (`src tests`) et PHPStan max verts, rejoués sur l'arbre exact avant le push (citer la dernière ligne de sortie)
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié, `bin/measure-mobile`, pa11y WCAG2AAA
- [ ] Aucun secret ni détail exploitable dans le dépôt ; jetons fictifs recomposés à l'exécution
- [ ] Documentation à jour (README EN et FR, CHANGELOG des plugins touchés)
- [ ] Statut de chaque tâche mis à jour dans son fichier de tâches, y compris dans un autre dépôt
- [ ] CI verte (« Quality and smoke tests », « Docker image » pour la démo ; matrice complète pour les plugins), run terminé avant le merge

## Checklist pré-sprint

- [x] Sprint 20 : revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`) ; rétro à animer le 2027-04-02
- [ ] Branche `feature/sprint-21-parc-equipements` active ; `git fetch` et branche vérifiés avant tout commit, dans la démo comme dans chaque plugin (le plugin parc d'équipements est sur une branche fusionnée : repartir de `origin/main`)
- [x] `df -h .` : relevé 5,6 Go libres (2026-10-22), **sous le seuil de 10 Go** ; dérogation encadrée prise (décision 2) ; `bin/check-disk` et `bin/clean-test-caches` à lancer avant le premier worker (T-TECH-01)
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [x] Périmètre validé par Thibaut (variante B, 25 points) et ordre de sacrifice accepté et appliqué (17 points engagés)
- [x] Fiches d'US-056, US-057 et US-058 écrites (Gherkin 1+2+2 minimum), passées `ready-for-dev`
- [x] Gherkin d'US-098, d'US-088 et d'US-154 complétés (1+2+2 minimum), passées `ready-for-dev`
- [x] Matrices CI et `composer.json` des plugins touchés lus au cadrage (plugin parc d'équipements : PHP `^8.3`, CI 8.3 et 8.4, cible 8.2 à 8.5 ; purchase-approval : PHP `^8.2`, sans 8.5 ; company-insights v0.2.1)
- [x] Rubrique « ordre de publication » présente dans les fiches de tâches des stories de plugin
- [ ] Incident Railway : état connu avant tout redéploiement
- [x] Actions 1 à 5 de la rétro du sprint 20 prises en charge (T-TECH-01 à T-TECH-03, T-TECH-07, T-TECH-08)

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-04-05 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-04-05 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices, CI purchase-approval) | 2027-04-09 | 30 min |
| Affinage Backlog (sprint 22) | 2027-04-12 | 1 h |
| Sprint Review | 2027-04-16 (matin) | 1 h |
| Rétrospective | 2027-04-16 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
