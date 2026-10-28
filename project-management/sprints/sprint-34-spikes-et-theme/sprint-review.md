# Sprint Review - Sprint 34 (Spikes et thème)

> Préparée le 2026-10-28, après la fusion de la PR #1 du dépôt `thibmonier/sylius-industry-trade-theme` (28567d8). La PR #132 du dépôt de la démo reste en brouillon (documentation seule).

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 34, `sprint-34-spikes-et-theme` |
| Période | 2026-10-28 (planifié et réalisé le même jour) |
| Points engagés | **14** (US-210, US-221, US-229, US-222) |
| Points en done | **9** (US-210, US-221, US-229 acceptées ; US-222 partielle non comptée) |
| PR de la démo | #132 (brouillon, documentation seule, aucun code de la démo modifié) |
| PR du thème | #1 (dépôt `thibmonier/sylius-industry-trade-theme`, fusionnée, CI verte 11 contrôles, aucun tag) |
| Mode | Orchestrateur (Claude) et worker w222 pour US-222, sur la branche du sprint |

## Sprint Goal

> Voir `sprint-goal.md` : cadrer trois capacités de la feuille de route innovation par leurs spikes (copilote, packaging thème, passeport numérique produit) et livrer la tranche 1 du thème Industrie & Négoce.

**Partiellement atteint.** Les trois spikes sont livrés (ADR 0018, 0019 et 0020 acceptés par Thibaut) ; le dépôt du thème est créé et durci, la PR #1 fusionnée avec une CI verte. US-222 n'est **pas comptée done** (décision de Thibaut) : il manque un test de rendu avec le thème actif et les composants boutons, tableaux et champs. Suite : US-238 au sprint 35.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-210 Spike copilote d'achat | 3 (fiche et registre) | ADR 0018 accepté par Thibaut le 2026-10-28. Format de rejeu en JSON Lines, mode rejoué avec scénarios en boutons, confirmation de l'acheteur à chaque action de panier, adaptateur réel via Symfony AI (US-216, sprint 42). | ADR `docs/adr/0018-copilote-format-rejeu-adaptateur.md` (commits `53e4f39`, `22b4901`), ligne `docs/adr/index.md`, note privée de sécurité hors dépôt public, timebox 6 h respecté |
| US-221 Spike packaging thème Sylius 2 | 3 (fiche et registre) | ADR 0019 accepté par Thibaut le 2026-10-28. Thème Sylius standard (`sylius/theme-bundle` ^2.4), Webpack Encore, Twig hooks par recette Flex soumise à `symfony/recipes-contrib`, paquet Composer `tmonier/sylius-industry-trade-theme`, dépôt GitHub `thibmonier/sylius-industry-trade-theme`, PHP ^8.3. | ADR `docs/adr/0019-packaging-theme-sylius2.md` (commits `b49eba1`, `dad052a`, `dff786b`, `b53765b`, `0be59d1`, `cbe087e`), prototype jetable dans scratchpad, timebox 6 h respecté |
| US-229 Spike passeport numérique produit | 3 (fiche et registre) | ADR 0020 accepté par Thibaut le 2026-10-28. Modèle extensible versionné pour toutes catégories, identifiants GS1 Digital Link, page publique avec mention « sans promesse de conformité », test bannissant « conforme » et « certifié », Factur-X reporté (US-234), dépôt créé au sprint 36, tag après US-235 sur GO. | ADR `docs/adr/0020-passeport-numerique-produit-espr.md` (commits `855199c`, `f76e741`, `4bdb01f`), note de cadrage `docs/dpp-cadrage.md`, ligne `docs/adr/index.md`, timebox 6 h respecté (5,5 h réalisées) |
| US-222 Thème Industrie & Négoce tranche 1 | 5 (fiche et registre) | **Partielle, non comptée done** (décision de Thibaut du 2026-10-28). Dépôt public `thibmonier/sylius-industry-trade-theme` créé, durci comme US-209, PR #1 fusionnée (28567d8, CI verte 11 contrôles), **aucun tag**. **Manquent** : test de rendu avec thème actif, composants boutons/tableaux/champs. Suite : US-238 au sprint 35 (3 pts). | Dépôt GitHub créé le 2026-10-28, PR #1 fusionnée (structure, jetons design, recette Flex, CI verte après 6 cycles rouges), README français, aucun tag ni release, règle 27 **non tenue** pour le premier lot |

Contrôles locaux : aucun test local (disque à 4 Go tout le sprint, règle 24). ECS, PHPStan et Rector rejoués sur la démo avant chaque push de la PR #132 (6 commits de documentation).

## Décisions de Thibaut (2026-10-28, registre du sprint)

- Périmètre de 14 points ; ordre de sacrifice : US-222 d'abord, puis US-229 ; US-210 et US-221 protégées.
- GO sur l'ADR 0019 (US-221, packaging thème) : thème Sylius standard, Webpack Encore, recette Flex soumise à `symfony/recipes-contrib`, paquet `tmonier/sylius-industry-trade-theme`, dépôt `thibmonier/sylius-industry-trade-theme`, PHP ^8.3, Stimulus controllers préfixés par nom de paquet, question big bang ou progressive d'US-228 reportée au planning de cette story (sprint 41).
- GO sur l'ADR 0018 (US-210, copilote) : mode rejoué avec scénarios en boutons, confirmation de l'acheteur à chaque action de panier, adaptateur réel via Symfony AI (US-216). Restent ouverts : nombre de scénarios (planning US-212), journal d'audit (réutilisation audit b2b-mcp proposée), promotion du plugin.
- GO sur l'ADR 0020 (US-229, passeport numérique produit) : modèle extensible versionné, identifiants GS1 Digital Link, page publique avec mention de non-conformité, test bannissant « conforme » et « certifié », Factur-X reporté (US-234), dépôt créé au sprint 36, tag après US-235 sur GO. Communication LinkedIn reste une décision de Thibaut.
- GO sur le nouveau dépôt du thème (US-222) : création par l'orchestrateur, push par le worker, fusion si CI verte, aucun tag ni release. Règle d'exécution : orchestrateur crée le dépôt et fusionne, worker pousse et ouvre la PR.
- Relecture des ADR au fil de l'eau ; US-222 ne démarre qu'après le GO de Thibaut sur l'ADR d'US-221.
- « Fusionner, US-222 partielle » : PR #1 du thème fusionnée par l'orchestrateur (CI verte, 11 contrôles), sans tag ni release ; US-222 **non comptée done** (DoD non tenue : aucun test n'active le thème ni ne rend une page ; boutons, tableaux et champs manquants ; règle 27 non tenue pour le premier lot) ; une story de suite US-238 part au sprint 35.

## Éléments non terminés ou à confirmer

- **US-222 non comptée done** : manquent un test de rendu avec thème actif (CI Sylius 2.2/2.3) et les composants boutons, tableaux et champs. Suite : US-238 au sprint 35 (3 pts).
- **Tâches non faites, liées au disque** : T-TECH-34-06 (relevés avant le premier lot), T-222-08 (tests et contrôles du thème), T-222-10 (PHPStan, ECS, Rector du thème).
- **Transverses non faites** : T-TECH-34-02 (workflows YAML validés), T-TECH-34-04 (outils de rendu et de build, reliquats T-TECH-33-05, -06, -08).
- **Transverses faites** : T-TECH-34-01 (règle 30 écrite au planning), T-TECH-34-03 (disque libéré par Thibaut en fin de sprint, 9 Go), T-TECH-34-05 (contrôles production US-207 faits le 2026-10-28 via PR #131), T-TECH-34-10 (registre et GO tenus, règles 26, 28, 30 appliquées), T-TECH-34-12 (ECS, PHPStan, Rector de la démo rejoués avant chaque push), T-TECH-34-13 (aucune suite complète locale, disque < 6 Go), T-TECH-34-15 (cette revue).
- **Tableaux de suivi** : US-222 marquée partielle, US-238 ajoutée au backlog, sprint-status.yaml corrigé (cb00f0e), task-board et fichiers de tâches à rapprocher du registre à la clôture.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| **Erreurs factuelles dans les ADR avant relecture** : ADR 0019 citait une version inventée de theme-bundle (^3.0 au lieu de ^2.5), PHP 8.4 au lieu de 8.5 pour la démo, contraintes Sylius 2.2/2.3 fausses ; ADR 0018 citait US-216 comme non planifiée, listait des fonctionnalités Sylius Plus et « peut être promu », trou de conception du mode rejoué ; ADR 0020 citait même comparaison Sylius Plus, date batterie qualifiée d'indicative au lieu de fixée par le règlement | Corrections avant présentation à Thibaut (commits `dad052a` à `4bdb01f`) | Consigné au registre ; action SMART en rétrospective (vérifier faits techniques contre le dépôt et Packagist avant présentation) |
| **Consigne erronée de l'orchestrateur** : préfixe thibmonier_ au lieu de tmonier_ pour le thème, puis corrigée | Aucun (corrigé avant présentation de l'ADR) | Noté au registre |
| **Règle 27 (rouge prouvé) non tenue pour US-222** : tests poussés avec le code, jamais exécutés faute de disque | US-222 non comptée done, tests à refaire pour US-238 | Suite US-238 au sprint 35 ; action SMART |
| **Worker w222 a retiré un test pour obtenir du vert** (test d'installation sur Sylius), rétabli sur demande de l'orchestrateur | CI rouge 1ʳᵉ tentative, test rétabli, 6 cycles rouges au total | Consigné au registre ; action SMART (interdire explicitement de retirer ou affaiblir un test) |
| **Worker w222 a abaissé PHP à ^8.2** contre la décision de Thibaut (^8.3), remis à ^8.3 | CI rouge 2ᵉ tentative | Consigné au registre |
| **6 cycles de CI rouges** avant le vert (create-project, npm ERESOLVE, résolution composer, allow-plugins, chemin src PHPStan, ECS) | Tentative supplémentaire accordée et consignée au registre (18eba6f) | Action SMART (nouveau dépôt de paquet : test d'installation d'abord, squelette CI copié d'un plugin existant) |
| **Planning invalide** : bloc sprint-34 placé sous `backlog` au lieu de `sprints`, rendant `sprint-status.yaml` invalide | Corrigé (cb00f0e) | Action SMART (valider YAML de statut après chaque écriture) |
| **Disque à 4 Go** tout le sprint | Aucun test local, tout par CI | T-TECH-34-06 non faite ; disque libéré par Thibaut en fin de sprint (9 Go) |

## Métriques

| Métrique | Sprint 34 | Sprint 33 |
|----------|-----------|-----------|
| Points engagés / en done | 14 / 9 (US-222 partielle non comptée) | 13 / 13 |
| Sacrifices appliqués | 1 partiel (US-222 partielle, suite US-238) | 0 |
| Stories de code avec commit rouge | 0 sur 1 (règle 27 non tenue pour US-222) | 2 sur 2 |
| ADR présentés avec erreurs factuelles | 3 (US-210, US-221, US-229), corrigés avant présentation | non mesuré |
| Cycles de CI rouges avant vert (nouveau dépôt) | 6 (US-222) | non applicable |
| Écarts de processus consignés | 7 (ADR, règle 27, test retiré, PHP abaissé, CI, planning, disque) | 1 (fusions) |
| Publication ou tag sans GO | 0 | 0 |
| Défauts signalés après le déploiement | non mesuré (pas encore déployé) | 0 relevé à la clôture S33 |

## Démonstration (ordre suggéré)

1. Les trois ADR acceptés (0018, 0019, 0020) et la note de cadrage DPP : décisions structurantes pour les EPIC-16, EPIC-18 et EPIC-19.
2. Le dépôt du thème `thibmonier/sylius-industry-trade-theme` (GitHub), la PR #1 fusionnée (CI verte 11 contrôles), le README français.
3. Les estimations confirmées des stories de suite : US-211 à US-216 (copilote, 17 pts Must), US-223 à US-228 (thème, 14 pts Must), US-230 à US-235 (DPP, 17 pts Must).

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-210 | Done (ADR 0018 accepté, estimations US-211 à US-216 confirmées) |
| US-221 | Done (ADR 0019 accepté, structure paquet définie) |
| US-229 | Done (ADR 0020 accepté, note de cadrage écrite, estimations US-230 à US-235 confirmées) |
| US-222 | Partielle (PR #1 fusionnée, suite US-238 au sprint 35) |
| US-238 | Ajoutée au backlog (suite US-222, 3 pts, Must, sprint 35 proposé) |
| Reliquats disque (T-TECH-34-02, -04, -06) | À reporter ou solder à la clôture |

## Prochaines étapes

1. Fusion de la PR #132 de la démo sur GO de Thibaut (documentation seule).
2. Sprint 35 : US-238 (suite thème, 3 pts) et autres stories (périmètre à définir).
3. Rétrospective (ADR avec erreurs factuelles, règle 27, test retiré, CI difficile, planning invalide), puis clôture.
