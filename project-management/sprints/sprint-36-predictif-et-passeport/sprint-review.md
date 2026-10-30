# Sprint Review - Sprint 36 (Prédictif et Passeport)

> Préparée le 2026-10-30, après la fusion de trois PR du plugin passeport (v0.1.0 en attente du GO de tag) et de deux PR du plugin prédictif (v0.1.1, v0.1.2 publiés). La PR #135 de la démo reste ouverte (brouillon, CI verte).

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 36, `sprint-36-predictif-et-passeport` |
| Période | 2026-10-29 au 2026-11-13 (10 jours ouvrés au plus tard) |
| Points engagés | **14** (US-240, US-230, US-231, US-239) |
| Points en done | **5** avant captures et déploiement (US-230 seule) ; **11** en review avec restes mineurs (US-239 workflow_dispatch, US-240 captures, US-231 captures et pa11y AAA) |
| PR de la démo | #135 (ouverte, brouillon, CI verte 4 contrôles) |
| PR plugin prédictif | #5 (US-240 fusionnée 7ba09bb), #6 (US-240 fusionnée 8980c94) |
| PR plugin passeport | #1 (US-230 fusionnée e566febe), #2 (US-230 fusionnée a1e8186 puis réécrite b6a973f), #3 (US-231 fusionnée 2d3f825) |
| Releases | v0.1.1 et v0.1.2 du plugin prédictif (2026-10-29) ; v0.1.0 du passeport en attente de GO |
| Mode | Orchestrateur (Claude) et workers w230, w231, w239, w240, w240b |

## Sprint Goal

> Voir `sprint-goal.md` : garantir une démo prédictive vivante à toute date (alertes garanties), lancer le passeport numérique produit avec sa page publique, et automatiser la veille hebdomadaire des versions du socle.

**Partiellement atteint.** Le plugin passeport est livré avec son modèle de données ouvert et versionné (US-230) et sa page publique (US-231, captures et audit reportés au sprint 37). Le plugin prédictif garantit au moins 2 alertes à toute date de remise à zéro (US-240, captures encore en attente). La veille hebdomadaire des versions est automatisée (US-239, reste le déclenchement manuel `workflow_dispatch` après fusion de la démo).

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-230 Squelette du plugin passeport et modèle de données ouvert et versionné | 5 (fiche et registre) | Plugin fusionné (PR #1, #2), historique réécrit sans vendor/ (b6a973f), CI verte 11/11 (Sylius 2.2/2.3 × PHP 8.2-8.5 × MySQL/MariaDB/PostgreSQL). Modèle de données avec révisions et export JSON. **Tag v0.1.0 en attente de GO de Thibaut.** | Dépôt `thibmonier/sylius-digital-product-passport-plugin`, PR #1 fusionnée (e566febe), PR #2 fusionnée puis réécrite (a1e8186 → b6a973f, un force-push avec lease), CI de main verte (run 40955027583 sur a1e8186b avant réécriture) |
| US-231 Page publique du passeport | 3 (fiche et registre) | **Partielle, non comptée done.** PR #3 fusionnée (2d3f825), rouge prouvé (run 40983308462), CI verte 11/11 (run 40982882619). **Manquent** : capture de la page publique et audit pa11y AAA reportés à l'installation du plugin dans la démo (sprint 37). | Dépôt passeport, PR #3 fusionnée (CI verte 11/11), preuve rouge branche `proof/us-231-link-red` (PublicPassportTest.php:116 après 200) |
| US-240 Alertes garanties à toute date et captures | 3 (fiche et registre) | **Partielle, non comptée done.** Plugin prédictif v0.1.1 (PR #5, 7ba09bb), v0.1.2 (PR #6, 8980c94), démo montée en ^0.1.2. Garantit au moins 2 alertes sur 365 jours de balayage. **Manquent** : captures du README du plugin (serveur local de Thibaut). | Dépôt plugin prédictif, PR #5 fusionnée (CI verte 21/21, run 40939734040), PR #6 fusionnée (CI verte 21/21, run 40955416144), releases v0.1.1 et v0.1.2, démo montée (commit 13a83e2) |
| US-239 Veille hebdomadaire des versions | 3 (fiche et registre) | **Partielle, non comptée done.** Script `bin/stack-versions-report`, workflow GitHub Actions hebdomadaire (lundi 09:00 UTC), issue automatique, CI verte. **Manque** : déclenchement manuel `workflow_dispatch` après fusion de la PR #135 de la démo. | Démo, commit 6ee1fbb (workflow) et 787f019 (script), CI verte (PHPStan, ECS, tests 9/9) |

Contrôles locaux : aucune suite complète locale (disque ~8 Go, contrainte OrbStack 2 Go). ECS, PHPStan max et Rector rejoués avant chaque push.

## Décisions de Thibaut (registre du sprint)

- Périmètre de 14 points (US-240, US-230, US-231, US-239), plus actions de rétro T-TECH-36-01 à 05.
- **Règle confirmée pour US-240** : un client est signalé « en retard » dès qu'il dépasse sa date de recommande prévue, dans la limite d'un intervalle médian de commande.
- **GO nouveau dépôt du plugin passeport (US-230)** : création du dépôt public durci, **fusion si la CI est verte**, aucun tag ni release sans GO. Règle d'exécution : **l'orchestrateur crée le dépôt et fusionne** ; le worker pousse sa branche et ouvre la PR.
- Actions de rétro du sprint 35 reprises comme tâches transverses (T-TECH-36-01 à 05).
- Contraintes machine : 8 Go RAM, OrbStack 2 Go ; `docker compose up -d mysql mailpit` seulement.
- **GO tag v0.1.1 du plugin prédictif** (PR #5 fusionnée en 7ba09bb) ; démo montée en ^0.1.1.
- **Bloc « À réassortir bientôt » corrigé au sprint 36** (US-240) : boutons au style du thème, nom de produit non doublé, médiane au format de la locale ; PR du plugin prédictif, fusion à CI verte, tag v0.1.2 sur GO de Thibaut, puis captures.
- **Incident : vendor/ versionné dans le dépôt public du passeport** (commit 615e1cf de w230, porté sur main par la PR #1) ; aucun secret (vérifié par l'orchestrateur) ; PR #2 retire vendor/ du suivi ; **GO de réécriture de l'historique de main** sans vendor/, un seul force-push par l'orchestrateur après fusion de la PR #2 (dépôt neuf, sans tag ni Packagist).
- **GO tag v0.1.2 du plugin prédictif** (PR #6 fusionnée en 8980c94) ; démo montée en ^0.1.2.
- **Incident US-231 : tests fonctionnels jamais exécutés en CI** (`phpunit.xml.dist` limité à `tests/Integration`, CI en `--testsuite=integration`) : la première CI « verte » de la PR #3 ne prouvait rien. Corrigé : suite `all` avec `tests/Functional`, `e2e_js: "yes"`, lint Twig rétabli. Les tests activés ont trouvé un vrai bug (template du lien).
- US-231 : « publié » = au moins un champ de données renseigné (`isPublished()`), sans statut explicite ; accepté pour la v0, **dette** : statut de publication explicite à prévoir avec US-232/US-233.
- US-231 : rouge prouvé par la branche `proof/us-231-link-red` ; PR #3 fusionnée en squash (2d3f825) à CI verte (run 40982882619, 11 tests sur les 9 jobs).
- US-231 : capture de la page publique et audit pa11y AAA **reportés** à l'installation du plugin passeport dans la démo (sprint 37) ; tag v0.1.0 et soumission Packagist sur GO de Thibaut.

## Éléments non terminés ou à confirmer

- **US-240 non comptée done** : partie plugin fusionnée (v0.1.1, v0.1.2), démo montée. **Manquent** : captures du README du plugin (serveur local de Thibaut).
- **US-231 non comptée done** : PR #3 fusionnée, rouge prouvé. **Manquent** : capture de la page publique et audit pa11y AAA reportés à l'installation dans la démo (sprint 37).
- **US-239 non comptée done** : script et workflow livrés, CI verte. **Manque** : déclenchement manuel `workflow_dispatch` après fusion de la PR #135 de la démo.
- **Tâches techniques faites** : T-TECH-36-01 (hook pre-push refusant le force-push + ECS/PHPStan), T-TECH-36-02 (script wait-for-ci), T-TECH-36-03 (consigne worker durcie), T-TECH-36-04 (README bilingue + captures dans la DoD plugin), T-TECH-36-05 (script check-resources), T-TECH-36-10 (registre tenu).
- **PR #135 de la démo** : brouillon, CI verte (4 contrôles), en attente de fusion sur GO de Thibaut.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| **vendor/ versionné dans le dépôt public du passeport** : commit 615e1cf de w230 (PR #1) contenait vendor/ ; aucun secret (vérifié) | Historique de main contenait vendor/ jusqu'à la PR #2 | PR #2 fusionnée (a1e8186), historique réécrit sans vendor/ (b6a973f, un force-push avec lease) ; refs/pull/* de GitHub gardent les anciens commits ; consigné au registre |
| **Tests fonctionnels jamais exécutés en CI (US-231)** : `phpunit.xml.dist` limité à `tests/Integration`, CI en `--testsuite=integration` | La première CI « verte » de la PR #3 ne prouvait rien ; les tests fonctionnels ne tournaient pas | Corrigé : suite `all` avec `tests/Functional`, `e2e_js: "yes"`, lint Twig rétabli ; les tests activés ont trouvé un vrai bug (template du lien lisant `product` au lieu de `hookable_metadata.context.product`) ; consigné au registre |
| **Commit 85ff026 (US-240) a embarqué par erreur les tests d'US-239** (`tests/Unit/Stack/VersionsReportTest.php`, assistant `extractSection`) | Tests d'US-239 présents dans un commit d'US-240, non poussé | Accepté en l'état, la PR sera fusionnée en squash ; signalé par w239 ; consigné au registre |

## Métriques

| Métrique | Sprint 36 | Sprint 35 |
|----------|-----------|-----------|
| Points engagés / en done | 14 / 5 (US-230 seule done ; US-239, US-240, US-231 partielles non comptées) | 16 / 8 (US-218 et US-219 partielles non comptées) |
| Stories de code avec commit rouge | 3 sur 4 (US-240, US-239, US-231 : rouges prouvés en CI ; US-230 pas de test rouge attendu, squelette) | 4 sur 4 (US-217, US-218, US-238, US-219) |
| Relais de workers (contexte épuisé) | 1 (w240 → w240b) | 6 (w217 → w217b → w217c → w217d ; w218 → w218b ; w219) |
| Pushes forcés (interdits sauf réécriture d'historique GO) | 1 (orchestrateur, réécriture sans vendor/ sur GO de Thibaut) | 2 (w217d, w218) |
| Incidents consignés | 3 (vendor/ versionné, tests fonctionnels jamais exécutés, commit mélangé) | 11 (relais, pushes forcés, contrôles faux, classes inventées, pushes rafale, tests affaiblis, découvertes, orchestrateur, disque, API) |
| Publication ou tag sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Le dépôt du plugin passeport `thibmonier/sylius-digital-product-passport-plugin` (GitHub), PR #1, #2 et #3 fusionnées, CI verte 11/11, historique réécrit sans vendor/.
2. Le modèle de données du passeport : entité ProductPassport avec révisions, export JSON, données ouvertes et versionnées.
3. La page publique du passeport : route `/{_locale}/product-passport/{code}`, données structurées, conformité ESPR, ton prudent.
4. Le dépôt du plugin prédictif, releases v0.1.1 et v0.1.2, bloc « À réassortir bientôt » corrigé (boutons au style du thème, nom de produit non doublé, médiane au format de la locale).
5. Les alertes garanties : test `MinimumAlertsTest` balayant 365 jours vérifiant au moins 2 alertes à toute date de remise à zéro.
6. La veille hebdomadaire des versions : workflow GitHub Actions (`stack-versions-weekly.yml`), script `bin/stack-versions-report`, issue automatique créée chaque lundi à 09:00 UTC.
7. Les actions de rétro du sprint 35 : hook pre-push (T-TECH-36-01), script wait-for-ci (T-TECH-36-02), consigne worker durcie (T-TECH-36-03), README bilingue + captures dans la DoD plugin (T-TECH-36-04), script check-resources (T-TECH-36-05).

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-230 | Done (plugin fusionné, historique réécrit, tag v0.1.0 en attente de GO) |
| US-231 | Partielle (PR #3 fusionnée, captures et audit pa11y AAA reportés au sprint 37) |
| US-240 | Partielle (v0.1.1 et v0.1.2 publiés, captures encore en attente) |
| US-239 | Partielle (script et workflow livrés, workflow_dispatch à lancer après fusion de la démo) |
| US-232, US-233 | Reportées au sprint 37 |

## Prochaines étapes

1. Captures du plugin prédictif sur serveur local de Thibaut (US-240).
2. GO de tag v0.1.0 du plugin passeport et soumission Packagist par Thibaut.
3. Fusion de la PR #135 de la démo sur GO de Thibaut (CI verte).
4. Déclenchement manuel `workflow_dispatch` du workflow de veille hebdomadaire (US-239).
5. Sprint 37 : installation du plugin passeport dans la démo, captures et audit pa11y AAA (US-231), US-232 et US-233 (passeport numérique produit).
