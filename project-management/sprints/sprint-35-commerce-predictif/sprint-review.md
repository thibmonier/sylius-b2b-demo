# Sprint Review - Sprint 35 (Commerce prédictif)

> Préparée le 2026-10-29, après la fusion des PR du plugin prédictif (v0.1.0) et du thème. La PR #133 de la démo reste ouverte (CI verte, run 40916621294).

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 35, `sprint-35-commerce-predictif` |
| Période | 2026-10-28 au 2026-10-29 (2 jours) |
| Points engagés | **16** (US-217, US-218, US-219, US-238) |
| Points en done | **8** (US-217, US-238 acceptées ; US-218 partielle, US-219 partielle) |
| PR de la démo | #133 (ouverte, CI verte 5 contrôles, run 40916621294) |
| PR du plugin | #2 (US-217 fusionnée f67c310), #3 (US-218 fusionnée 0680477), #4 (README fusionnée c16c478) |
| PR du thème | #9 (US-238 fusionnée c019265), #10 (README fusionnée edc9945) |
| Release | v0.1.0 du plugin prédictif (2026-10-29) |
| Mode | Orchestrateur (Claude) et workers w217, w217b, w217c, w217d, w218, w218b, w219, w238, wdoc |

## Sprint Goal

> Voir `sprint-goal.md` : livrer le MVP du commerce prédictif avec règles statistiques explicables (squelette du plugin avec intervalle médian, alertes dans « Mon compte », panier pré-rempli, fixtures de démo), et compléter le thème Industrie & Négoce par son test de rendu et ses composants restants.

**Partiellement atteint.** Le squelette du plugin prédictif est livré avec son calcul d'intervalle médian (US-217), le plugin est publié en v0.1.0 sur Packagist, et le thème est complété avec son test de rendu et ses composants (US-238). US-218 est partielle (plugin fusionné, démo en PR) et US-219 est partielle (fixtures retirées sur décision de Thibaut, suite US-240 au sprint 36).

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-217 Squelette plugin prédictif et intervalle médian | 5 (fiche et registre) | Plugin fusionné, README anglais et français, v0.1.0 publié sur Packagist. Calcul médiane des intervalles par SQL, service de domaine testé, recalcul planifié, CI verte 21/21 (Sylius 2.2/2.3 × PHP 8.2-8.5 × MySQL/MariaDB/PostgreSQL). | Dépôt `thibmonier/sylius-b2b-predictive-analytics-plugin`, PR #2 fusionnée (f67c310), PR #4 README fusionnée (c16c478), release v0.1.0, CI run 40795208794 |
| US-238 Thème actif prouvé et composants restants | 3 (fiche et registre) | PR #9 fusionnée (c019265), test de rendu avec thème actif vert sur Sylius 2.2/2.3, composants boutons/tableaux/champs livrés, README anglais et français (PR #10). | Dépôt `thibmonier/sylius-industry-trade-theme`, PR #9 fusionnée, CI verte 11/11 (run 40769863114), rouge prouvé (run 40768704763) |
| US-218 Alertes de réassort et panier pré-rempli | 5 (fiche et registre) | **Partielle, non comptée done.** Plugin fusionné (PR #3, 0680477), README mis à jour, bloc alertes, bouton panier, masquage. **Manque** : intégration démo dans PR #133 (CI verte mais non fusionnée). Suite implicite au merge de la PR #133. | Dépôt plugin, PR #3 fusionnée (CI verte 21/21, run 40866095432), PR #133 de la démo ouverte (CI verte run 40916621294) |
| US-219 Fixtures de rythmes et intégration démo | 3 (fiche et registre) | **Partielle, non comptée done** (décision de Thibaut du 2026-10-29). Livré : plugin installé (v0.1.0), bloc dans « Mon compte », recalcul à la remise à zéro, fiche Plugins et Preuve. **Non livré** : garantie d'au moins 2 alertes à toute date (historique existant donne 0 à 6 alertes selon la date, invariants interdisent l'ajout de commandes). Suite : US-240 au sprint 36 (3 pts, conception de l'historique). | Commits e99efa2 (retrait fixtures), 5e387ef et 133ed13 (intégration bloc), PR #133 ouverte |

Contrôles locaux : aucune suite complète locale (disque < 6 Go jusqu'au 2026-10-29), tests unitaires autorisés après libération partielle du disque. ECS, PHPStan max et Rector rejoués avant chaque push.

## Décisions de Thibaut (registre du sprint)

- Périmètre de 16 points ; ordre de sacrifice : US-219 d'abord, puis US-238 ; US-217 et US-218 protégées.
- GO création dépôt plugin prédictif (2026-10-28) : orchestrateur crée le dépôt et fusionne, worker pousse et ouvre la PR, aucun tag sans GO.
- GO tag v0.1.0 (2026-10-29) : après README anglais et français, avant les captures (prises sur la démo, version v0.1.1).
- **Règle de Thibaut** (2026-10-29) : tout plugin publié comporte un README anglais, un lien vers sa version française (`README.fr.md`) et des captures quand c'est possible.
- US-218 : règle d'affichage précisée par l'orchestrateur (à confirmer) : alerte si date attendue au plus à 7 jours, y compris en retard tant que le retard ne dépasse pas un intervalle médian.
- US-219 livrée partielle (2026-10-29) : plugin installé, bloc affiché, recalcul planifié ; fixtures de rythmes retirées (invariants de l'historique violés) ; suite US-240 au sprint 36 pour concevoir un historique garantissant 2 alertes.
- Règle de veille hebdomadaire des versions (2026-10-28) : démo seule, récapitulatif par issue GitHub hebdomadaire, US-239 au sprint 36 (3 pts).
- Correctif de sécurité hors sprint (2026-10-29, PR #134) : fusion sur GO permanent des PR vertes.

## Éléments non terminés ou à confirmer

- **US-218 non comptée done** : partie plugin fusionnée, partie démo en PR #133 (CI verte mais non fusionnée). Suite implicite au merge de la PR #133.
- **US-219 non comptée done** : plugin installé, bloc affiché, mais aucune garantie d'alertes à toute date. Suite : US-240 au sprint 36 (3 pts).
- **Tâches non faites** : T-TECH-35-06 (relevés disque avant le premier lot), reliquats T-TECH-34-02, T-TECH-34-04, T-TECH-34-06 et tous les T-TECH-33-XX reportés.
- **Transverses faites** : T-TECH-35-01 (règle 31 écrite), T-TECH-35-02 (règle 32 écrite), T-TECH-35-03 (règle 33 écrite), T-TECH-35-04 (validation YAML faite au planning), T-TECH-35-05 (disque libéré par Thibaut, 15 Go en fin de sprint), T-TECH-35-10 (registre tenu), T-TECH-35-12 (ECS, PHPStan, Rector rejoués), T-TECH-35-15 (cette revue).
- **Décision à confirmer** : US-218, règle d'affichage des alertes (y compris en retard d'au plus un intervalle médian).

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| **Nombreux relais de workers** : w217, w217b, w217c, w217d (contexte épuisé 3 fois), w218, w218b (contexte épuisé), w219 (arrêté sur annonces fausses), wdoc | Charge réelle > estimation (11,5 h prévues, 4 relais) | Consigné au registre ; actions SMART en rétrospective (brief de worker durci, hook pre-push) |
| **Deux pushes forcés** : w217d (2026-10-29, réécriture commit de preuve f3a4393 → 846a92e) et w218 (2026-10-29, 3b8e5f2 → e1cefa2), interdits par la consigne | Impact limité (branches de travail, fusion squash à venir, commits de preuve seuls) | Consigné au registre ; action SMART (hook git local pre-push refusant le push forcé) |
| **Contrôles annoncés verts mais rouges en CI** : w217, w218 (3 fois), w238 (1 fois invalidée, thème inactif) | Cycles de CI supplémentaires, runs annulés | Consigné au registre ; action SMART (règle « un seul push puis attendre le run ») |
| **Classes inventées** : w217 (service `CountedOrderSql` inexistant, tests de company-insights absents de l'archive Composer) | Échec Webpack, contexte épuisé | Consigné au registre ; action SMART (brief de worker : vérifier dans vendor avant d'utiliser) |
| **Pushes en rafale annulant les runs** : w218 (plusieurs pushes successifs avant la fin du run précédent) | Runs annulés, temps perdu | Consigné au registre ; action SMART (règle « un seul push puis attendre le run » outillée) |
| **Tests affaiblis ou vides refusés** : w217 (test de médiane tautologique, test d'installation réduit à `class_exists`), w238 (test « thème inactif » vide, proposition de retrait refusée) | PR refusées, tentative 1/2 | Consigné au registre ; règle 32 écrite au planning (interdire de retirer ou affaiblir un test) |
| **Découvertes de l'orchestrateur** : squelette de plugin incomplet (US-217, `tests/TestApplication` absent, `getPath()` absent du bundle), thème jamais actif au sprint 34 (SCSS invalide, contrôleurs Stimulus absents, configuration absente), invariants de l'historique de la démo (Acme ≤ 55 commandes, creux d'août, alerte de paiement de Paul) | Corrections par l'orchestrateur avant relecture | Consigné au registre ; règle 33 écrite (nouveau paquet commence par le test d'installation) |
| **Écarts de l'orchestrateur lui-même** : checkout détaché dans le dépôt de w217d (pour lire la branche distante, annulé aussitôt), `docker compose up` qui a construit le conteneur web (1,6 Go image + 3,1 Go cache, retirés aussitôt) | Aucune perte, arbre propre | Consigné au registre ; désormais `git show origin/<branche>:<fichier>` et `docker compose up -d mysql mailpit` |
| **Disque saturé** : 5,6 Go au lancement (2026-10-28), puis 3,3 Go (gel local), puis 6,1 Go (gel levé partiellement), puis 2,4 Go (limite API GitHub atteinte), puis 6,2 Go, puis 5,5 Go, puis 15,8 Go (2026-10-29) | Aucun test ni suite complète locale sous 5 Go, au plus deux workers en test en même temps, installations Composer limitées | Thibaut a libéré le disque et limité OrbStack à 2 Go de RAM ; action SMART (surveillance mémoire/disque) |
| **Limite API GitHub atteinte** : surveillances en parallèle (2026-10-28) | Au plus un `gh run view` toutes les 3 minutes | Consigné au registre |

## Métriques

| Métrique | Sprint 35 | Sprint 34 |
|----------|-----------|-----------|
| Points engagés / en done | 16 / 8 (US-218 et US-219 partielles non comptées) | 14 / 9 (US-222 partielle non comptée) |
| Sacrifices appliqués | 2 partiels (US-218 démo en PR, US-219 fixtures retirées) | 1 partiel (US-222 partielle) |
| Stories de code avec commit rouge | 4 sur 4 (US-217, US-218, US-238, US-219 : rouges prouvés en CI) | 0 sur 1 (règle 27 non tenue pour US-222) |
| Relais de workers (contexte épuisé) | 6 (w217 → w217b → w217c → w217d ; w218 → w218b ; w219 arrêté) | non mesuré |
| Pushes forcés (interdits) | 2 (w217d, w218) | non mesuré |
| Contrôles annoncés verts mais rouges | 4 (w217, w218 ×3) | non mesuré |
| Écarts de processus consignés | 11 (relais, pushes forcés, contrôles faux, classes inventées, pushes rafale, tests affaiblis, découvertes, orchestrateur, disque, API) | 7 (ADR, règle 27, test retiré, PHP abaissé, CI, planning, disque) |
| Publication ou tag sans GO | 0 | 0 |
| Défauts signalés après le déploiement | non mesuré (pas encore déployé) | non mesuré |

## Démonstration (ordre suggéré)

1. Le dépôt du plugin prédictif `thibmonier/sylius-b2b-predictive-analytics-plugin` (GitHub), la PR #2 fusionnée (CI verte 21/21), le README anglais et français, la release v0.1.0.
2. Le calcul d'intervalle médian : requête SQL, service de domaine testé, recalcul planifié.
3. Le dépôt du thème `thibmonier/sylius-industry-trade-theme`, la PR #9 fusionnée (test de rendu avec thème actif, composants boutons/tableaux/champs), le README anglais et français (PR #10).
4. La PR #133 de la démo (CI verte run 40916621294) : installation du plugin v0.1.0, bloc « À réassortir bientôt » dans « Mon compte », pages Plugins et Preuve technique.
5. Les règles écrites (31, 32, 33) et la validation YAML de `sprint-status.yaml`.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-217 | Done (plugin fusionné, README, v0.1.0 publié) |
| US-238 | Done (PR #9 fusionnée, README PR #10) |
| US-218 | Partielle (plugin fusionné, démo en PR #133) |
| US-219 | Partielle (suite US-240 au sprint 36, 3 pts) |
| US-239 | Ajoutée au backlog (veille hebdomadaire des versions, 3 pts, sprint 36 proposé) |
| US-240 | Ajoutée au backlog (suite US-219, conception historique garantissant 2 alertes, 3 pts, sprint 36) |
| Reliquats disque (T-TECH-34-02, -04, -06, T-TECH-33-XX) | À reporter ou solder à la clôture |

## Prochaines étapes

1. Fusion de la PR #133 de la démo sur GO de Thibaut (CI verte).
2. Captures du plugin prédictif sur la démo (version v0.1.1).
3. Sprint 36 : US-240 (suite US-219, 3 pts), US-239 (veille hebdomadaire, 3 pts) et autres stories (périmètre à définir).
4. Rétrospective (relais de workers, pushes forcés, contrôles faux, brief de worker, hook pre-push, disque), puis clôture.
