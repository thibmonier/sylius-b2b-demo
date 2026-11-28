# Revue du sprint 60 — Sondes des plugins B2B et santé en HTTP

**Date** : 2026-11-28. Sprint exécuté du 2026-11-27 au 2026-11-28 ; le créneau prévu était du 2027-01-21 au 2027-02-04.

**Objectif** : les trois plugins B2B les plus exposés remontent leur propre état sur la page de santé, et la démo est surveillée par le point d'entrée HTTP du plugin au lieu de son `/healthz` maison.

**Objectif atteint.**
- 14 points livrés sur 14 engagés (capacité 15, 93 %) ; 14 tâches sur 14 ; 33,5 h estimées.
- Les quatre stories sont « done », et toutes les PR ont été fusionnées par Thibaut.
- Quatre releases sur GO, après toute la documentation : plugin de santé **v0.6.0**, webhook-outbox **v1.1.0**, purchase-approval **v1.5.0**, trade-credit **v1.2.0**. Les quatre sont visibles sur Packagist.
- En production, après le déploiement de #246 (2026-11-28, 13:44) :
  - `/healthz` répond 200 avec un corps vide, sans en-tête ;
  - `tmonier:health:check` donne un statut global `warning`, **23 sondes, aucune `critical`**, et les cinq sondes des plugins B2B sont `ok`.

## Stories

| Story | Points | PR | Rouge prouvé en CI | Statut |
|-------|--------|----|--------------------|--------|
| US-308 point d'entrée HTTP JSON protégé par jeton | 5 | santé [#24](https://github.com/thibmonier/sylius-platform-health-plugin/pull/24), release [#25](https://github.com/thibmonier/sylius-platform-health-plugin/pull/25) | run 46025210370, vert run 46026712267 | **Done** |
| US-304 sondes des plugins B2B (lot 1) | 5 | webhook-outbox [#11](https://github.com/thibmonier/sylius-webhook-outbox-plugin/pull/11), purchase-approval [#17](https://github.com/thibmonier/sylius-purchase-approval-plugin/pull/17), trade-credit [#14](https://github.com/thibmonier/sylius-trade-credit-plugin/pull/14) ; releases #12, #18, #15 | webhook-outbox run 46025510370 ; purchase-approval run 46027275039 ; trade-credit run 46027869945 | **Done** |
| US-319 `AdminOrderBulkActionsTest` instable | 2 | bulk-actions [#11](https://github.com/thibmonier/sylius-bulk-actions-plugin/pull/11) | run 46126934590, échecs par assertion sur 8 combinaisons ; vert run 46127092639 | **Done** |
| US-321 `/healthz` de la démo servi par le plugin | 2 | démo [#245](https://github.com/thibmonier/sylius-b2b-demo/pull/245) | run 46129431007 (26 échecs d'assertion), vert run 46130997163 | **Done** |

**Livré : 14/14 points.** Vélocité : 14 (sprint 59 : 13 ; sprint 58 : 15 ; sprint 57 : 17).

## Démonstration (démo déployée)

1. **Page de santé, catégorie « Plugins »** (US-304, T-TECH-60-04) : en tant que Sophie, ouvrir Configuration > Santé de la plateforme. Les cinq sondes `webhook_outbox.backlog`, `webhook_outbox.dead_deliveries`, `purchase_approval.overdue`, `purchase_approval.closed_orders` et `trade_credit.stale_reservations` sont `ok`.
   - Chaque plugin déclare le plugin de santé en `suggest`, jamais en `require` ; la sonde n'est chargée que si l'interface existe.
   - Les requêtes de comptage s'arrêtent à 501 lignes.
2. **Point d'entrée HTTP** (US-308) :
   - Sans jeton configuré, la route n'existe pas (404).
   - Avec un jeton `Authorization: Bearer`, le niveau global ou le niveau détail renvoie, pour chaque sonde, son identifiant, sa catégorie et son statut, sans le résumé (décision de Thibaut).
   - Le niveau « vivant » renvoie 200 ou 503 avec un corps vide, sans en-tête.
   - La réponse est 503 seulement si une sonde est `critical`. Les résultats viennent du cache des relevés, et le débit est limité à 30 appels par minute et par IP (429 au-delà).
3. **`/healthz` de la démo** (US-321) : il est servi par le niveau « vivant » du plugin. Le `HealthController` de la démo et le limiteur `demo_health` sont retirés. Les deux contrôles de la démo deviennent les sondes `demo.configuration` et `demo.reset`. Les jetons `HEALTH_HTTP_TOKEN` et `HEALTH_HTTP_DETAIL_TOKEN` ont été créés par Thibaut dans Railway et sont gardés par `preserve()`.
4. **bulk-actions** (US-319) : la CI de `main` est verte sur toute la matrice, sans relance.

## Tâches techniques

| Tâche | PR | Statut |
|-------|----|--------|
| T-TECH-60-01 planification, sprint 59 clos | [#243](https://github.com/thibmonier/sylius-b2b-demo/pull/243) | Fait |
| T-TECH-60-02 (A-59-1 à A-59-3) règles 35, 36 et 37 | consigne des workers | Fait |
| T-TECH-60-03 releases santé v0.6.0, webhook-outbox v1.1.0, purchase-approval v1.5.0, trade-credit v1.2.0 | PR de release fusionnées, puis tags et releases GitHub sur GO de Thibaut | Fait ; les quatre sont sur Packagist |
| T-TECH-60-04 démo à jour des trois plugins, preuve relue, relevé après déploiement | [#246](https://github.com/thibmonier/sylius-b2b-demo/pull/246) | Fait. Rouge run 46137115600, vert run 46138822571 ; CI de `main` verte (run 46146127564) ; relevé de production à 23 sondes |

## Écarts à signaler

1. **US-321 change trois comportements de `/healthz`**, tous écrits dans #245 :
   - des migrations illisibles donnent `unknown`, donc 200 (l'ancien contrôleur répondait 503) ; une migration en attente reste `critical` ;
   - une base injoignable lève une erreur avant la route, comme avec l'ancien `/healthz`, et non un 503 propre ;
   - une sonde `critical` de n'importe quelle catégorie fait désormais échouer le contrôle de déploiement.
2. **`RailwayPreservedVariablesTest` est arrivé avec le vert d'US-321**, sans rouge prouvé avant (règle 26).
3. **US-319 touchait trois combinaisons et non une.** La cause : les fixtures de Sylius tirent au hasard la zone des méthodes de livraison, et certains tirages ne donnent aucune expédition. Trois autres tests appellent encore `markTestSkipped` dans ce cas : dette inscrite ([US-322](../../backlog/user-stories/US-322.md)). Durée : environ 2 h 30 sur les 4 h prévues.
4. **Des runs de CI ont été annulés par la concurrence** : le rouge d'US-319 (relancé sur le SHA rouge, tentative 2), puis deux runs du vert de #246 (la preuve est le run du dernier commit, qui contient tout le changement).
5. **Le test local du vert de #246 n'a pas tourné** : l'image `sylius-php:8.5-alpine` a planté (erreur de segmentation) pendant le chargement des fixtures. Seule la CI fait foi pour ce vert. Pendant US-319, Docker (OrbStack) s'était aussi bloqué, puis rétabli.
6. **Couverture marquée `stale` dans `proof.json` pour les trois plugins** : leurs commits de release ne touchaient que le CHANGELOG, et la CI ne lance pas le job de couverture sur ce type de commit. C'est le comportement prévu par `ProofCollector`, mais la page publie la couverture d'avant les sondes.
7. **Le catalogue annonçait « seize sondes » sur la page** alors qu'elle en montrait 18, puis 23. Corrigé dans #246.
8. **Outils hors Docker, comme aux sprints 57 à 59** : `bin/compat-refresh`, `bin/proof-refresh` et `bin/proof-check` ont tourné en PHP local, parce qu'ils ont besoin de `gh`. ECS local signale toujours `RunTestsLockTest` (`pcntl` absent de l'image) ; la CI est verte.

Progrès par rapport au sprint 59 :
- chaque déploiement de la démo a eu son relevé `tmonier:health:check` (A-59-3, deux relevés : après #245 et après #246) ;
- la CI de `main` a été suivie après chaque fusion, dans les cinq dépôts (règle 32) ;
- aucun schéma de la démo n'a été touché : A-59-1 et A-59-2 étaient sans objet.

## Retours du Product Owner (Thibaut, 2026-11-28)

- **Niveau détail du point d'entrée HTTP** : sans le résumé des sondes, qui peut contenir des versions et des noms de tables.
- **Trois tests encore ignorés selon les fixtures** : ouvrir une story ([US-322](../../backlog/user-stories/US-322.md)).
- **Durée de la CI de la démo** : le job « Quality and smoke tests » est trop long (20 min, dont 17 de PHPUnit). Story ouverte : [US-323](../../backlog/user-stories/US-323.md).

## Impact sur le backlog

| Action | Story | Description |
|--------|-------|-------------|
| Ajout | [US-322](../../backlog/user-stories/US-322.md) | Dette : trois tests de bulk-actions ignorés selon les fixtures (2 points, Should) |
| Ajout | [US-323](../../backlog/user-stories/US-323.md) | Dette : le job « Quality and smoke tests » en moins de 10 minutes (3 points, Should) |

## Hors sprint

- 2026-12-01 : relevé Railway par service (A-57-4), à la charge de Thibaut.
- Avertissements de production à suivre : `environment.symfony_version` (fin de support de Symfony 8.1 le 2027-01-31, dans 64 jours) et `environment.opcache` (inactif pour la SAPI CLI).

## Reste à faire

1. ROTI de Thibaut et approbation des actions A-60-1 à A-60-3 ([sprint-retro.md](./sprint-retro.md)).
2. Planifier le sprint 61. Candidates : US-323 (CI, 3 points), US-322 (2 points), US-320 (`--fail-on`, 2 points), et la montée de version de Symfony avant la fin de support de 8.1 (2027-01-31).
