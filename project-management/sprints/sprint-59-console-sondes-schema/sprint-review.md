# Revue du sprint 59 — Console, sondes des plugins et schéma de la démo

**Date** : 2026-11-27. Sprint exécuté du 2026-11-26 au 2026-11-27 ; le créneau prévu était du 2027-01-07 au 2027-01-21.

**Objectif** : le diagnostic de santé se lit aussi en console avec un code de sortie utile, un plugin peut fournir sa sonde sans imposer le plugin de santé, et la démo n'a plus de dérive de schéma.

**Objectif atteint.**
- 13 points livrés sur 13 engagés (capacité 15, 87 %) ; 20 tâches sur 20 ; 36,5 h sur 36,5 h.
- Les quatre stories sont « done », et toutes les PR ont été fusionnées par Thibaut.
- Deux releases sur GO : plugin de santé **v0.5.0** et plugin prédictif **v0.2.1**. La démo est passée en santé ^0.5 et prédictif ^0.2.1.
- En production, « Dérive du schéma » est **`ok`** depuis le 2026-11-27 à 13:49, et les 7 sondes de données sont `ok`.

## Stories

| Story | Points | PR | Rouge prouvé en CI | Statut |
|-------|--------|----|--------------------|--------|
| US-318 dérive du schéma de la démo | 3 | prédictif [#16](https://github.com/thibmonier/sylius-b2b-predictive-analytics-plugin/pull/16), santé [#22](https://github.com/thibmonier/sylius-platform-health-plugin/pull/22), démo [#234](https://github.com/thibmonier/sylius-b2b-demo/pull/234) et [#238](https://github.com/thibmonier/sylius-b2b-demo/pull/238) | prédictif run 45863297636 ; santé run 45877419668 (10 combinaisons) ; démo runs 45866427853 (17 instructions), 45868284675 (12) et 45996438034 (`messenger_messages`) | **Done** |
| US-303 sondes facultatives des plugins | 5 | santé [#19](https://github.com/thibmonier/sylius-platform-health-plugin/pull/19) | rouge `1932c2f`, run 45861370966 : 3 échecs unitaires, 4 non unitaires, sur les 10 combinaisons | **Done** |
| US-307 commande `tmonier:health:check` | 3 | santé [#20](https://github.com/thibmonier/sylius-platform-health-plugin/pull/20), vocabulaire [#21](https://github.com/thibmonier/sylius-platform-health-plugin/pull/21) | #20 : rouge `0b30d91`, run 45868187123, 19 + 8 échecs ; #21 : rouge `02d63e6`, run 45876623491 | **Done** |
| US-314 suite non unitaire même si l'unitaire échoue | 2 | 30 PR `ci:` (liste dans la [fiche](../../backlog/user-stories/US-314.md)) | preuve sur un dépôt (commit rouge jetable), les 29 autres vérifiés par lecture du diff ; CI de `main` verte partout | **Done** |

**Livré : 13/13 points.** Vélocité : 13 (sprint 58 : 15 ; sprint 57 : 17 ; sprint 56 : 13).

## Démonstration (démo déployée)

1. **Console de Railway** (US-307), en `www-data` :
   ```
   php bin/console tmonier:health:check --category=data --format=json
   ```
   - Sortie JSON lisible par une machine : statut global et une ligne par sonde, avec leur résumé.
   - Code de sortie `0` si tout est `ok`, `1` sur un `warning` ou un `unknown`, `2` sur un `critical` et `3` sur une option invalide. La commande sert donc de porte de CI après un déploiement ou dans un cron.
   - Sans `--format`, la sortie est en texte. `--category` filtre la catégorie, et les messages parlent de « contrôle » / « check » (#21).
2. **Page de santé, catégorie Données** (US-318) : « Dérive du schéma » est `ok` (« The database schema matches the Doctrine mapping. »). Avant le sprint, elle était en avertissement avec 16 instructions.
3. **Sondes fournies par les plugins** (US-303) : un plugin déclare `tmonier/sylius-platform-health-plugin` en `suggest` et fournit sa sonde. Elle n'est enregistrée que si l'interface existe à la compilation. Sans le plugin de santé, le plugin s'installe et fonctionne sans erreur, comme le prouvent les tests du plugin de démonstration. Aucune catégorie vide n'apparaît sur la page. Les premières sondes de plugins B2B arriveront avec US-304 (sprint 60).
4. **CI des 30 plugins** (US-314) : quand l'unitaire échoue, le pas « Run Non-unit PHPUnit tests » s'exécute quand même. Un commit rouge montre donc ses deux suites.

## Tâches techniques

| Tâche | PR | Statut |
|-------|----|--------|
| T-TECH-59-01 planification, sprint 58 clos | [#232](https://github.com/thibmonier/sylius-b2b-demo/pull/232), [#233](https://github.com/thibmonier/sylius-b2b-demo/pull/233) | Fait |
| T-TECH-59-02 (A-58-2) `bin/proof-check` sur les PR de documentation | [#235](https://github.com/thibmonier/sylius-b2b-demo/pull/235) | Fait. Le job est informatif (non bloquant, décision de Thibaut) ; son premier passage réel, sur #239, est vert |
| T-TECH-59-03 (A-58-3) procédure Docker des tests de la démo | [#236](https://github.com/thibmonier/sylius-b2b-demo/pull/236) | Fait ; durées mesurées sur une copie neuve |
| T-TECH-59-04 (A-58-1, 2, 4, 5) règles 26, 32, 33 et 34 | consigne des workers | Fait |
| T-TECH-59-05 releases, démo à jour, sonde vue `ok` en production | santé [#23](https://github.com/thibmonier/sylius-platform-health-plugin/pull/23) (v0.5.0), prédictif v0.2.1, démo #234, #238 et [#239](https://github.com/thibmonier/sylius-b2b-demo/pull/239) | Fait ; `compat-refresh` puis `proof-refresh` relus (A-57-3) |

## Écarts à signaler

1. **La production montrait encore 5 instructions après le déploiement de v0.5.0**, toutes sur `messenger_messages`.
   - La table existait avant `Version20261113100000`, dont le `CREATE TABLE IF NOT EXISTS` n'avait rien fait. Elle gardait un index par colonne et les commentaires DBAL 3.
   - En test, le `schema_filter` cache cette table : la CI ne pouvait pas le voir.
   - Thibaut a choisi l'option A, une migration de la démo (#238), avec un test qui recrée la table de production.
   - **Leçon** : une table exclue de la comparaison en test doit avoir son propre test, sur sa forme réelle de production.
2. **La sonde du plugin de santé ne pouvait jamais être `ok`**, même en production. Hors des commandes `doctrine:schema:*`, elle comptait la table des migrations exécutées. Le défaut a été trouvé par le test de la démo, qui lit la sonde elle-même, puis corrigé en v0.5.0 (#22).
3. **La première version de #22 a échoué sur PHPStan sous Sylius 2.2** : sous DBAL 3, `getSchemaAssetsFilter()` peut renvoyer `null`. Le correctif filtre l'instruction `DROP TABLE` au lieu d'échanger le filtre de la connexion, qui reste ainsi intact (test dédié).
4. **Avertissement « lock file is not up to date » en CI de la démo.** Composer 2.11 dans l'image et 2.10.3 en CI ne calculent pas le même `content-hash`. Le lock a été recalculé avec 2.10.3, et le piège est documenté dans `docs/tests-locaux.md`.
5. **Copie neuve de la démo dans Docker**, deux faux rouges :
   - « Unknown database » sans `?serverVersion=8.4` ;
   - 4 échecs de `CartPageTest` sans `icu-data-full` (prix formatés `€35.76`).
   Les deux sont documentés (#236).
6. **`AdminOrderBulkActionsTest` instable** dans `sylius-bulk-actions-plugin`, sur la combinaison Sylius ~2.3.0 / PHP 8.4 / Symfony ^8.0 / MySQL 8.4. Le test a été vu en échec sur la PR #10 puis vert à la relance, sans changement de code. Dette inscrite au backlog : [US-319](../../backlog/user-stories/US-319.md).
7. **Run de `main` après #237 annulé, pas rouge** : le run de la fusion de #238, qui teste le même `main`, l'a remplacé. Ce run de #238 est vert (run 46001713770).
8. **Outils hors Docker, comme aux sprints 57 et 58** : `bin/compat-refresh`, `bin/proof-refresh` et `bin/proof-check` ont tourné en PHP local, parce qu'ils ont besoin de `gh`. ECS sur `sylius-php:8.5-alpine` signale toujours `RunTestsLockTest`, parce que `pcntl` manque à l'image. La CI reste verte.

Progrès par rapport au sprint 58 : le rouge de la démo a cette fois été vu en CI (#234 et #238, règle 26). La CI de `main` a été suivie après chaque fusion (règle 32), et le `main` de la démo n’a pas été rouge pendant le sprint (celui de bulk-actions l’a été une fois, voir le point 6).

## Impact sur le backlog

| Action | Story | Description |
|--------|-------|-------------|
| Ajout | [US-319](../../backlog/user-stories/US-319.md) | Dette : `AdminOrderBulkActionsTest` instable (2 points, Should) |
| Avancée | US-307 | Avancée du sprint 60 au sprint 59, livrée |

## Hors sprint

- 2026-12-01 : relevé Railway par service (A-57-4), à la charge de Thibaut.

## Reste à faire

1. Rétrospective du sprint 59.
2. Planifier le sprint 60 : US-304 (sondes des plugins B2B, lot 1) et US-308 (point d'entrée HTTP JSON) sont proposées ; US-319 est à placer.
