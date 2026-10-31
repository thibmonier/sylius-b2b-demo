# Consigne de worker : Sprint 038

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md). Les règles 1 à 45 des consignes des [sprint 35](../sprint-35-commerce-predictif/consigne-worker.md), [sprint 36](../sprint-36-predictif-et-passeport/consigne-worker.md) et [sprint 37](../sprint-37-copilote-et-passeport/consigne-worker.md) restent applicables, sauf les adaptations du tableau « Sprint 38 ». Les règles 46 à 48 sont nouvelles (rétro du sprint 37). L'orchestrateur colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Gabarits de tests : [docs/gabarits-tests.md](../../../docs/gabarits-tests.md) ; outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).
>
> **Règle 1 en tête : les tests de la démo se lancent seulement par `bin/run-tests`, jamais par `phpunit` en direct.**

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Dépôt de travail | Base | Cache |
|--------|---------|-------------|------------------|------|-------|
| w245 | US-245 | `w245` | démo (arbre principal, un seul worker de la démo à la fois) | `b2b_w245` | `cache-w245` |
| w244 | US-244 | `w244` | démo (arbre principal, après w245), puis dépôt copilote (captures) | `b2b_w244` | `cache-w244` |
| w243 | US-243 | `w243` | démo (arbre principal, après w244) | `b2b_w243` | `cache-w243` |
| w242 | US-242 | `w242` | `sylius-customer-asset-management-plugin` puis `sylius-digital-product-passport-plugin` | aucune (dépôts séparés) | aucun |

T-TECH-38-01 à 05 sont faites par l'orchestrateur.

## Sprint 38 : adaptations des règles 1 à 45

| Règle | Sprint 38 |
|-------|-----------|
| 6 (branches) | Démo : branche `feature/sprint-38-consolidation-vitrine` dans l'arbre principal, **un seul worker de la démo à la fois** (w245, puis w244, puis w243 ; pas de worktree, faute de disque pour un second `vendor/`) ; pas de checkout. Plugins : `feature/<sujet>` depuis `origin/main`, un seul worker par dépôt. |
| 8 et 30 (GO) | Plugins : l'orchestrateur fusionne à CI verte ; aucun tag ni release sans GO de Thibaut. Démo : aucun push sans demande de l'orchestrateur (la branche du sprint part en une PR brouillon de fin de sprint ; un `proof/*` peut être demandé pour prouver un rouge en CI). |
| 10 (plugins) | US-244 : `composer require tmonier/sylius-b2b-sales-copilot-plugin:^0.1`, `bin/check-lock-diff --base=main` cité ; un nouveau plugin installé entre aussi dans `config/demo/plugins.yaml`, puis `bin/compat-refresh` et `bin/proof-refresh`. Canal B2B de la démo : code `B2B`. |
| 23 (captures) | Chromium sans interface, 1280 px, recadrage par ImageMagick (`magick in.png -crop 1280xH+0+0 +repage out.png`), pas par `sips` ; PNG relus avant tout commit ; pa11y WCAG2AAA sur la page rendue avec son CSS. |
| 24 (parallélisme) | Disque à 7,1 Go libres : tests ciblés seulement, jamais la suite complète en local ; au plus deux workers lancent `bin/run-tests` en même temps. |

## Règles 46 à 48 (rétro du sprint 37)

46. **Tests en local avant tout push** (T-TECH-38-02). Dès qu'une base de test existe (démo : `bin/run-tests` ; plugin : `vendor/bin/phpunit` avec la base de la TestApplication), le worker lance en local les tests qu'il a écrits ou modifiés et cite la ligne « OK (N tests… ) » ou « Tests: N, Failures: M » dans son rapport **avant** le push. Un rouge poussé doit avoir échoué en local sur la même assertion. Avant chaque push : PHPStan, ECS et lints, commande et dernière ligne citées. La mise en place des tests suit [docs/gabarits-tests.md](../../../docs/gabarits-tests.md).
47. **Arrêt après passation** (T-TECH-38-03). Le worker qui écrit sa passation ne fait plus aucune commande git ni écriture ; l'orchestrateur l'arrête par TaskStop dès la passation reçue, avant de lancer le relais.
48. **Contrôle avant la PR de fin de sprint** (T-TECH-38-04). Avant d'ouvrir la PR de la démo, l'orchestrateur lance les tests du catalogue et des fixtures (`tests/Unit/Demo/Plugin`, `tests/Smoke/LegalPagesTest.php`, `tests/Functional/FixturesConsistencyTest.php`), `php bin/proof-check` et `php bin/verify-test-coverage` ; la suite complète tourne en CI.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**. Pour les tests rouges : la ligne d'échec locale **et** l'URL ou l'ID du run CI rouge. SHA copiés de `git log --oneline`, ID de run copiés de `gh run list` (règle 37). Rapport final à l'orchestrateur en moins de 150 mots. Interdits rappelés : force-push, `--force-with-lease`, `--amend` ou `reset` d'un commit poussé ; `git add -A` ou `git add .` ; tout fichier `.env*` ; supprimer ou affaiblir un test ; infrastructure factice ; serveur local ; tag, release, fusion ; contourner un refus d'outil.
