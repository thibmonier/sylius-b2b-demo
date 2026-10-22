# Sprint 20 : Démo enrichie (accueil, intégrations, pilotage des achats complété, catalogue et simulateur)

> **Engagement final : 22 points (~69,5 h).** US-093 (8 pts, 16,5 h) est sacrifiée le 2026-10-22 (capacité), reportée au sprint 21 (premier sacrifice de l'ordre validé par Thibaut ; la décomposition donnait 86 h de charge Claude pour 71 h de capacité).
>
> **Périmètre validé par Thibaut au planning du 2026-10-22 : 30 points** (US-113, US-152, US-153, US-156, US-108, US-099, US-093) sur 43 points candidats (35 points de la feuille de route + 8 points reportés du sprint 19). Les décisions du planning sont consignées dans la section « Décisions prises au planning ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 20 |
| Début | 2027-03-19 (revue et rétro du sprint 19 ; démarrage effectif le 2027-03-22) |
| Fin | 2027-04-02 |
| Durée | 10 jours ouvrés (2027-03-22 au 2027-04-02) |
| Capacité | **22 points engagés** (6 stories, 0 Must, 22 Should) : 30 points validés sur 43 candidats, moins US-093 (8 pts) sacrifiée le 2026-10-22. Capacité en heures : **71 h** (30 points × 2,1 h + 8 h de tâches transverses), charge décomposée de 86 h pour Claude, excès de 15 h résorbé par le sacrifice d'US-093, soit **69,5 h** (voir [tasks/README.md](./tasks/README.md)) |
| Branche | `feature/sprint-20-demo-enrichie` (créée depuis `main`, 3ceeb6b) |

Périmètre issu de la section « Sprint 20 » de la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md) et des reports du [sprint 19](../sprint-19-pilotage-achats/sprint-retro.md).

## Sprint Goal

> « Rendre la démo plus riche et plus lisible pour un recruteur (accueil réorganisé, page Intégrations, simulateur PunchOut expliqué) et compléter le pilotage des achats (ventilation par site, approbations et encours, approbations en attente dans l'en-tête). »

(Le catalogue de 150 références, initialement dans l'objectif, est reporté au sprint 21 avec US-093.)

## Calcul de capacité et vélocité

| Sprint | Points planifiés | Points livrés (en review) |
|--------|------------------|---------------------------|
| 17 | 38 | 38 |
| 18 | 36 | 25 (69 %) |
| 19 | 25 | 25 (100 %) |
| Moyenne | 33 | 29,3 |

Lecture : 25 points est la valeur sûre (deux sprints sur trois) ; 36 à 38 points n'ont tenu qu'au sprint 17. Les 43 points candidats ne tiennent pas. Décision : 30 points, avec un premier sacrifice de 8 points prévu d'avance (plancher 22). Les heures des fiches (≈ 61,5 h pour les 7 stories) plus ≈ 7 h de tâches techniques donnaient ≈ 68,5 h. **La décomposition en tâches du 2026-10-22 donne 86 h de charge pour Claude** (88 tâches, 87,5 h avec 1,5 h d'actions de Thibaut) contre une capacité de **71 h** (30 points × 2,1 h + 8 h de tâches transverses, méthode du sprint 19) : **excès de 15 h**. Les trois stories de plugins dépassent leur fiche (release, bump de la démo, revue de sécurité, captures). Le premier sacrifice, **US-093 (16,5 h)**, ramène la charge à 69,5 h (marge de 1,5 h, 22 points). Si la rétrospective du sprint 19 reporte les quatre tâches techniques conditionnelles (3,25 h), US-099 est sacrifiée aussi.

## Candidats et périmètre validé

| ID | Titre | Pts | Heures (fiche) | Gherkin (nominal / alternatif / erreur) | Statut proposé | Périmètre |
|----|-------|-----|----------------|------------------------------------------|----------------|-----------|
| US-113 | Accueil réorganisé : trois colonnes, pourquoi cette démo, qui l'a conçue | 3 | 6 | 1 / 2 / 2 | ready-for-dev | Validé |
| US-152 | Company-insights : ventilation par site et centre de coût, export CSV | 5 | 10 | 2 / 2 / 2 | ready-for-dev | Validé |
| US-153 | Company-insights : indicateurs des approbations et de l'encours | 5 | 10 | 2 / 2 / 2 | ready-for-dev (2e erreur ajoutée au planning) | Validé |
| US-156 | Purchase-approval : approbations en attente dans l'en-tête | 3 | 7 | 1 / 3 / 2 | ready-for-dev (administratrice approbatrice décidée) | Validé |
| US-108 | Page « Intégrations » | 3 | 6 | 1 / 2 / 2 | ready-for-dev | Validé |
| US-099 | Simulateur PunchOut habillé et expliqué (report S19) | 3 | 6,5 | 1 / 2 / 2 (dont une erreur proposée à valider) | ready-for-dev (à valider au planning) | Validé |
| US-093 | Catalogue crédible, tranche 2 : environ 150 références | 8 | 16 | 1 / 2 / 2 | backlog (sacrifiée le 2026-10-22) | Sacrifiée, reportée au sprint 21 |
| US-094 | Stock et délais indicatifs sur la fiche produit | 5 | 10 | 1 / 1 / 1 | backlog (Gherkin sous le minimum 1 + 2 + 2) | Hors périmètre |
| US-100 | Les 403 expliqués et les menus inaccessibles masqués | 3 | 6 | 1 / 1 / 1 | backlog (Gherkin sous le minimum 1 + 2 + 2) | Hors périmètre |
| US-121 | Kit média (2/2) (report S19) | 5 | 10,5 | 1 / 3 / 2 | backlog (bloquée par T-103-03) | Hors périmètre |

Total validé : 30 points, **engagé : 22 points** après le sacrifice d'US-093 (US-113 3, US-152 5, US-153 5, US-156 3, US-108 3, US-099 3, US-093 8). Hors périmètre : 13 points (US-094 5, US-100 3, US-121 5, statut backlog), reportables au sprint 21 ou à l'affinage.

**Ordre de sacrifice validé** (du premier au dernier) : ~~US-093 (8 pts, gros, dépend du disque) : appliqué le 2026-10-22~~, US-099 (3), US-108 (3), US-156 (3), US-153 (5). US-152 et US-113 ne se sacrifient qu'en dernier recours. Après le sacrifice suivant (US-099) : 19 points.

**Statuts à surveiller** :
- US-153 : Gherkin complété au planning du 2026-10-22 (2e scénario d'erreur : plugin compagnon en erreur), fiche passée `ready-for-dev`.
- US-094 et US-100 : 3 scénarios (1 + 1 + 1) contre le minimum 1 + 2 + 2. À compléter à l'affinage avant tout engagement.
- US-121 : Gherkin à 1 + 3 + 2, conforme, mais bloquée par T-103-03 (conversation réelle de Thibaut) via US-103 ; reste `backlog` tant que T-103-03 n'est pas livrée.
- US-099 : le cinquième scénario (URL de retour hors des domaines autorisés) est une proposition du cadrage du sprint 19, à confirmer par Thibaut (non tranché le 2026-10-22 ; la story est engagée telle quelle).
- US-108 : dépend nominalement d'US-130 (spike P5, sprint 21) ; la page décrit des états « disponible, en cours, non fourni » et n'a pas besoin du spike pour être livrée. À confirmer au planning.
- Le titre et le texte d'US-113 et d'US-108 sont déjà réécrits (repositionnement « recherche d'emploi ») : aucune mention de prestation ni d'appel commercial.

## Sprint Backlog

Voir la section `sprint-20` de `.bmad/sprint-status.yaml` (source unique des statuts). Stories engagées (22 points) : US-113 (3), US-152 (5), US-153 (5), US-156 (3), US-108 (3), US-099 (3). US-093 (8) sacrifiée le 2026-10-22, reportée au sprint 21. Décomposition : [tasks/README.md](./tasks/README.md) et [task-board.md](./task-board.md).

## Décisions prises au planning (Thibaut, 2026-10-22)

1. **Périmètre** : 30 points validés, ramenés à 22 par le sacrifice d'US-093 (2026-10-22) = US-113 (3), US-152 (5), US-153 (5), US-156 (3), US-108 (3), US-099 (3), US-093 (8, sacrifiée). Ordre de sacrifice : US-093 (appliqué), US-099, US-108, US-156, US-153 ; US-152 et US-113 en dernier recours.
2. **US-156 : administratrice approbatrice** : dans la démo, `approver_roles` = `[approver, admin]` (Isabelle approbatrice, sans dépasser sa limite d'approbation) ; aucun changement de règle dans le plugin. Consigné dans la fiche US-156.
3. **US-153** : Gherkin complété (2e scénario d'erreur, SMART) et fiche passée `ready-for-dev`. US-094 et US-100 restent `backlog` (hors périmètre).
4. **US-121** : reste hors sprint (T-103-03 non fournie), statut `backlog`. Le doublon US-121/US-099 de la section sprint-19 du fichier de statut est historique (reportées au sprint 20) et n'est pas modifié.

## Jalons

- **Pilotage des achats complet** : company-insights 0.2.0 (ventilation par site et centre de coût, export CSV, approbations et encours) ; tag et release préparés par Claude, publication Packagist par Thibaut.
- **Purchase-approval** : release mineure avec l'entrée d'en-tête (US-156), puis bump de la démo.
- **Démo plus lisible** : accueil réorganisé, page Intégrations, simulateur expliqué.
- **Ordre de publication (action 2 de la rétro S19)** : pour US-152, US-153 et US-156 (deux dépôts dépendants de la démo) : tag du plugin, puis Packagist, puis vérification de la version publiée, puis bump du `composer.lock` de la démo ; ne pas ouvrir la PR de la démo avant la vérification.
- **Politique** : aucune comparaison à Sylius Plus dans les textes publiés ; aucun détail de sécurité dans le dépôt public (la revue des voters et de l'export CSV se rapporte hors dépôt).

## Dépendances

- US-152 et US-153 dépendent d'US-149 (livrée) et se font dans le dépôt séparé `sylius-company-insights-plugin` (prochaine version 0.2.0) ; US-153 lit les plugins **optionnels** `sylius-purchase-approval-plugin` et `sylius-trade-credit-plugin` (aucune dépendance obligatoire ajoutée).
- US-156 se fait dans le dépôt `sylius-purchase-approval-plugin` ; la décision « administratrice approbatrice » précède le code de la démo.
- Tâche technique de début de sprint : monter le lock de la démo sur company-insights 0.1.1 (`composer update tmonier/sylius-company-insights-plugin`, publié sur Packagist). Les contraintes `^0.1` du `composer.json` couvrent déjà 0.1.1 ; la 0.2.0 exigera `^0.2`.
- US-093 (sacrifiée, sprint 21) touchait le catalogue et les images de la démo ; plus de contrainte de parallélisme dans ce sprint.
- Voir [sprint-dependencies.md](./sprint-dependencies.md).

## Risques

| Risque | Mitigation |
|--------|------------|
| Capacité dépassée (43 points candidats pour ≈ 25 à 30) | Périmètre validé (30 points) ; ordre de sacrifice écrit d'avance ; point d'étape le 2027-03-26 |
| Railway : builds suspendus par un incident, production sans les PR #43 à #48 | Vérifier en démo locale ; le déploiement groupé reste une commande de Thibaut à la reprise (action 5 de la rétro S19, échéance 2027-03-26) ; vérification en production des stories du sprint 19 toujours en attente |
| Disque local d'environ 5,4 Go libres (`bin/run-tests` refuse sous 5 Go) | Un seul worker à la fois, un seul build d'image à la fois ; `bin/check-disk` et `bin/clean-test-caches` en début et milieu de journée ; captures et rendu réel planifiés avant le jour 5 (action 1 de la rétro S19) ; US-093 (150 références et images) sacrifiée le 2026-10-22 |
| Lock de la démo en retard sur company-insights 0.1.1 | Tâche technique de début de sprint (`composer update tmonier/sylius-company-insights-plugin`, Composer avec `php -d memory_limit=1G`) |
| Ordre de publication entre plugin et démo (CI rouge au sprint 19) | Rubrique « ordre de publication » dans chaque fiche de tâches ; vérification de la version publiée avant la PR de la démo |
| Passer `admin` dans `approver_roles` change les parcours de la démo d'Isabelle (e-mails, commandes sur compte, tests) | Décision prise le 2026-10-22 ; tests de démo et parcours revérifiés dans US-156 ; US-156 sacrifiable en 4e position |
| Cloisonnement des données entre entreprises (ventilation, export CSV) | Scénarios « autre entreprise » en tests d'intégration ; neutralisation de l'injection de formule dans l'export ; revue de sécurité avant le merge, sans détail dans le dépôt public |
| CI d'un plugin neuf sans PHP 8.5 ni actions épinglées (company-insights) | Action 4 de la rétro S19 : gabarit de workflow appliqué à company-insights avant la 0.2.0 |
| Politique de communication | Aucune mention ni comparaison à Sylius Plus ; ton sobre et factuel ; aucune annonce sur les canaux Sylius |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation. Soumission à Packagist, publication LinkedIn et commandes Railway par Thibaut. `main` protégée : PR et contrôles requis, attendre la fin du run CI. Tests via `bin/run-tests` seulement. Un seul worker à la fois. Seul l'orchestrateur reconstruit l'image Docker. Aucune précision sur une faille dans un dépôt public. Rapports de fin ≤ 100 mots.

## Actions de la rétrospective du sprint 19

| Action (S19) | Responsable | Échéance | Prise en charge |
|--------------|-------------|----------|-----------------|
| 1. Budget d'espace disque et captures tôt dans le sprint | Scrum Master | Planning du sprint 20 | Relevé au planning : ≈ 5,4 Go libres (machine) ; captures planifiées avant le jour 5 ; DoD : 0 tâche de captures reportée pour manque d'espace |
| 2. Ordre de publication dans les fiches de story | Scrum Master | Affinage du sprint 20 | Rubrique dans le gabarit de story, appliquée à US-152, US-153 et US-156 ; DoD : 0 CI rouge due à un ordre de publication |
| 3. Contrôle du gate et consigne après refus d'outil | Orchestrateur | Début du sprint 20 | Gate recoupé avec la somme des points du fichier de statut ; un refus d'outil n'arrête jamais le sprint ; DoD : 0 écart de calcul, 0 arrêt non demandé |
| 4. Gabarit de CI pour un nouveau plugin | Worker devops | Fin du sprint 20 | PHP 8.4 et 8.5, actions épinglées par SHA, `permissions` minimal ; appliqué à company-insights (CI verte sur PHP 8.5) |
| 5. Plan de déploiement de repli | Thibaut (déploiement), Scrum Master (note) | 2027-03-26 | Note au runbook ; déploiement groupé des PR #43 à #48 à la reprise de Railway |

Reports techniques du sprint 19 (sans points) : T-TECH-01, 02, 03, 05, 06 et 08 non livrées, T-155-14 (relecture de Thibaut) ; à reprendre dans les tâches techniques du sprint 20.

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts (stories de code)
- [ ] ECS (`src tests`) et PHPStan max verts, rejoués sur l'arbre exact avant le push (citer la dernière ligne de sortie)
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié, `bin/measure-mobile`, pa11y WCAG2AAA
- [ ] Aucun secret ni détail exploitable dans le dépôt ; jetons fictifs recomposés à l'exécution
- [ ] Documentation à jour (README EN et FR, CHANGELOG des plugins touchés)
- [ ] CI verte (« Quality and smoke tests », « Docker image » pour la démo ; matrice complète pour les plugins), run terminé avant le merge

## Checklist pré-sprint

- [ ] Sprint 19 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`) ; rétro à animer le 2027-03-19
- [ ] Branche `feature/sprint-20-demo-enrichie` active ; `git fetch` et branche vérifiés avant tout commit
- [ ] `df -h .` : au moins 5 Go libres (≈ 5,4 Go constatés, marge faible) ; `bin/check-disk` et `bin/clean-test-caches` lancés
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [x] Périmètre validé par Thibaut (30 points) et ordre de sacrifice accepté (2026-10-22)
- [x] Décision « administratrice approbatrice » (US-156) prise (`[approver, admin]`)
- [x] Gherkin complété : US-153 (2e erreur) ; US-099 (erreur proposée) à confirmer ; US-094 et US-100 hors périmètre
- [ ] Lock de la démo monté sur company-insights 0.1.1
- [ ] Rubrique « ordre de publication » présente dans les fiches de tâches d'US-152, US-153 et US-156 ; matrices CI et `composer.json` des deux plugins lus au cadrage
- [ ] Incident Railway : état connu avant tout redéploiement
- [ ] Actions 1 à 5 de la rétro du sprint 19 prises en charge

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-03-22 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-03-22 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-03-26 | 30 min |
| Affinage Backlog (sprint 21) | 2027-03-29 | 1 h |
| Sprint Review | 2027-04-02 (matin) | 1 h |
| Rétrospective | 2027-04-02 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
