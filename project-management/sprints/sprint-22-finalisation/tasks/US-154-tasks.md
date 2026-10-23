# Tâches : US-154 : Company-insights : conversion des devis et économies des prix dégressifs

## Informations US

- **Epic** : EPIC-15
- **Persona** : Isabelle (administratrice d'Acme Industries)
- **Story Points** : 3 (Could, n°2)
- **Sprint** : sprint-22-finalisation
- **Story** : [US-154](../../../backlog/user-stories/US-154.md)

## Résumé de la US

**En tant que** Isabelle
**Je veux** savoir combien de nos demandes de devis aboutissent et ce que nous ont fait économiser les prix dégressifs
**Afin de** mesurer la valeur de nos conditions négociées

## Règles et contraintes

- Deux dépôts : `tmonier/sylius-company-insights-plugin` (dépôt `../sylius-company-insights-plugin`, `main`, dernier tag `v0.2.1`) et la démo. Release **mineure 0.3.0** + montée de la démo. `git fetch` puis `feature/us-154-devis-et-economies` depuis `origin/main`, branche vérifiée avant tout commit.
- **Tag, release GitHub et Packagist seulement après le GO de Thibaut.** Ordre : GO, tag, indexation Packagist (paquet déjà soumis, confirmée par Thibaut), vérification de la version publiée, puis `composer.lock` de la démo et sa PR.
- Widgets sur `InsightsWidgetProviderInterface` ; plugins `quote-request` et `tier-pricing` optionnels (détection à l'exécution, widget absent sans erreur) ; chiffres issus des services publics, jamais des tables ; économie calculée sur le prix figé de la ligne de commande ; libellé factuel.
- **Écrivain unique du `composer.lock` de la démo** : la montée ne se fait qu'après les montées en attente d'autres plugins (purchase-approval v1.3.0, parc d'équipements 0.1.0) décidées par Thibaut, voir T-TECH-22-02.
- Worker de plugin : budget disque encadré (un worker à la fois, nettoyage en fin de tâche), `df -h .` avant et après. Tests : uniquement `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests` (refuse de démarrer sous 5 Go libres) ; jamais phpunit en direct ; si le disque est insuffisant, la CI valide.
- Règles du sprint et consigne de worker : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md) avant de lancer deux développeurs en parallèle.

- Plugin company-insights : `src/` (fournisseurs de widgets Devis et Économies), `templates/`, `translations/`, `tests/`, `README.md`, `CHANGELOG.md`, `composer.json`, `.github/workflows/` (relue, non modifiée sauf besoin)
- Démo : `composer.json` et `composer.lock` (**écrivain unique**), `config/demo/plugins.yaml`, `docs/vitrine-plugins.md`, `tests/` du scénario d'Isabelle, `config/demo/proof.json` si la version est affichée

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude** seul, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut** (action de sa part). Ordre TDD : les tâches [TEST] « rouges » précèdent le code qu'elles décrivent. Estimations de 0,25 h à 3 h (aucune tâche au-dessus de 8 h).

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-154-01 | [DOC] | Cadrage : `git fetch`, branche depuis `origin/main` (v0.2.1), branche vérifiée ; matrice CI et `composer.json` relus ; services publics de quote-request et tier-pricing, prix de base de la ligne de commande, interface des widgets lus ; rubriques « versions » et « ordre de publication » | 1 h | - | Claude | 🔲 |
| T-154-02 | [TEST] | Tests rouges : indicateurs des devis (4 demandes, 3 devis, 2 commandes, 67 %, délai médian, expirés), économie (120 casques à 9 € au lieu de 11 € = 240 € HT), grille modifiée après l'achat, un seul compagnon, aucun compagnon, période sans demande, service en erreur | 1,5 h | T-154-01 | Claude | 🔲 |
| T-154-03 | [BE] | Widget « Devis » (fournisseur de widget, permission, taux « non applicable » sans demande, repli « Données indisponibles ») | 2 h | T-154-02 | Claude | 🔲 |
| T-154-04 | [BE] | Widget « Économies » (différence prix de base et prix figé, sommée sur la période, rapportée à la dépense HT) | 2 h | T-154-03 | Claude | 🔲 |
| T-154-05 | [FE-WEB] | Rendu des deux widgets avec tableau équivalent ; libellés FR et EN, libellé factuel « Économies sur prix dégressifs » ; contraste AAA ; mobile | 1 h | T-154-04 | Claude | 🔲 |
| T-154-06 | [TEST] | Dans Docker : ECS, PHPStan max, Rector, suite avec et sans compagnons ; rendu réel FR et EN, pa11y WCAG2AAA ; lecture du code pour les cellules PHP 8.2 de la matrice | 1 h | T-154-05 | Claude | 🔲 |
| T-154-07 | [OPS] | Captures des deux widgets pour le README (application de test, données fictives) ; relevé du disque avant | 0,5 h | T-154-05 | Claude | 🔲 |
| T-154-08 | [REV] | Revue (économie sur prix figé, cloisonnement entreprise, journal sans donnée de commande) rapportée hors dépôt public, et revue de code | 0,5 h | T-154-06 | Claude | 🔲 |
| T-154-09 | [DOC] | README EN et FR (deux widgets, plugins optionnels, versions supportées), `CHANGELOG.md` de la 0.3.0, captures | 0,5 h | T-154-06, T-154-07 | Claude | 🔲 |
| T-154-10 | [OPS] | Thibaut donne le GO du tag et de la release `v0.3.0` de company-insights | 0,25 h | T-154-08, T-154-09 | Thibaut | 🔲 |
| T-154-11 | [OPS] | Push, PR, matrice complète verte (run terminé), merge, tag `v0.3.0` et release GitHub **après le GO** ; script de vérification Packagist remis à Thibaut | 0,75 h | T-154-10 | Claude | 🔲 |
| T-154-12 | [OPS] | Thibaut confirme que Packagist indexe la 0.3.0 (paquet déjà soumis) | 0,25 h | T-154-11 | Thibaut | 🔲 |
| T-154-13 | [OPS] | Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | T-154-12 | Claude | 🔲 |
| T-154-14 | [BE] | Démo : `php -d memory_limit=1G composer require tmonier/sylius-company-insights-plugin:^0.3` ; `composer.lock` écrit seul (après les montées en attente, T-TECH-22-02) | 0,5 h | T-154-13, T-TECH-22-02 | Claude | 🔲 |
| T-154-15 | [TEST] | Tests de la démo : widgets d'Acme (devis, économies), TechParts absent des chiffres d'Acme ; scénario d'Isabelle dans `config/demo/plugins.yaml` et `docs/vitrine-plugins.md` ; rendu réel et pa11y ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | T-154-14 | Claude | 🔲 |
| T-154-16 | [OPS] | Fin de worker : suppression de `vendor`, des caches et de l'image du plugin, puis des caches du worker de démo ; `df -h .` avant et après | 0,25 h | T-154-11, T-154-15 | Claude | 🔲 |

**Total estimé : 13,25 h** (16 tâches, dont 2 de Thibaut, soit 0,5 h ; 12,75 h pour Claude et l'orchestrateur).

## Dépendances

```mermaid
graph TD
    T15401["T-154-01 [DOC]"]
    T15402["T-154-02 [TEST]"]
    T15403["T-154-03 [BE]"]
    T15404["T-154-04 [BE]"]
    T15405["T-154-05 [FE-WEB]"]
    T15406["T-154-06 [TEST]"]
    T15407["T-154-07 [OPS]"]
    T15408["T-154-08 [REV]"]
    T15409["T-154-09 [DOC]"]
    T15410["T-154-10 [OPS]"]
    T15411["T-154-11 [OPS]"]
    T15412["T-154-12 [OPS]"]
    T15413["T-154-13 [OPS]"]
    T15414["T-154-14 [BE]"]
    T15415["T-154-15 [TEST]"]
    T15416["T-154-16 [OPS]"]
    T15401 --> T15402
    T15402 --> T15403
    T15403 --> T15404
    T15404 --> T15405
    T15405 --> T15406
    T15405 --> T15407
    T15406 --> T15408
    T15406 --> T15409
    T15407 --> T15409
    T15408 --> T15410
    T15409 --> T15410
    T15410 --> T15411
    T15411 --> T15412
    T15412 --> T15413
    T15413 --> T15414
    TTECH2202 --> T15414
    T15414 --> T15415
    T15411 --> T15416
    T15415 --> T15416
```

Dépendances hors fichier : T-TECH-22-02.
