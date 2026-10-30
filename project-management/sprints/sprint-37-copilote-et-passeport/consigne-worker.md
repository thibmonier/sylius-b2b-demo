# Consigne de worker : Sprint 037

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md). Les règles 1 à 41 des consignes des [sprint 35](../sprint-35-commerce-predictif/consigne-worker.md) et [sprint 36](../sprint-36-predictif-et-passeport/consigne-worker.md) restent applicables, sauf les adaptations du tableau « Sprint 37 » ci-dessous. Les règles 42 à 45 sont nouvelles (rétro du sprint 36, T-TECH-37-05). L'orchestrateur colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).
>
> **Règle 1 en tête : les tests de la démo se lancent seulement par `bin/run-tests`, jamais par `phpunit` en direct.**

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Dépôt de travail | Base | Cache |
|--------|---------|-------------|------------------|------|-------|
| w241 | US-241 puis T-TECH-37-01, 03, 04 | `w241` | démo (`sylius-b2b-demo`) | `b2b_w241` | `cache-w241` |
| w211 | US-211 | `w211` | nouveau dépôt `sylius-b2b-sales-copilot-plugin` | aucune (dépôt séparé) | aucun |
| wdpp | US-232 puis US-233 | `wdpp` | plugin passeport (`sylius-digital-product-passport-plugin`) | aucune (dépôt séparé) | aucun |

T-TECH-37-02 (squelette de plugin) et T-TECH-37-05 (règle 42) sont faites par l'orchestrateur.

## Sprint 37 : adaptations des règles 1 à 41

| Règle | Sprint 37 |
|-------|-----------|
| 6 (branches) | Démo : `feature/sprint-37-copilote-et-passeport` (arbre principal), **pas de checkout dans l'arbre partagé**. Passeport : `feature/dpp-gs1-link` (US-232) puis `feature/dpp-spare-parts` (US-233), chacune depuis `origin/main` après fusion de la précédente. Copilote : `feature/copilot-skeleton` depuis le dépôt vide. |
| 8 et 30 (GO) | Registre du 2026-10-30 (auteur : Thibaut) : **l'orchestrateur a créé le dépôt du copilote et fusionne** les PR des plugins à CI verte ; le worker pousse sa branche et ouvre la PR ; **aucun tag ni release** sans GO de Thibaut. Démo : aucun push sans demande de l'orchestrateur. |
| 10 (plugins) | Seuls le plugin passeport (US-232, US-233) et le plugin copilote (US-211) sont modifiés. Démo : `composer require tmonier/sylius-digital-product-passport-plugin` pour US-241, par w241 ; `bin/check-lock-diff --base=main` cité dans le rapport. |
| 13 et 27 (rouge) | Test rouge commité seul, ligne d'échec citée ; voir aussi les règles 36 et 43. |
| 23 (captures) | US-241 : capture de la page publique du passeport (EN + FR) pour les README du plugin passeport, par Chromium sans interface (`/Applications/Chromium.app/Contents/MacOS/Chromium --headless=new --hide-scrollbars --force-device-scale-factor=1 --virtual-time-budget=5000 --window-size=1280,1500 --screenshot=…`), PNG de 1280 px, recadrage par `sips -c`. Les chemins absolus des PNG sont envoyés à l'orchestrateur **avant** tout commit. |
| 24 (parallélisme) | 8 Go de RAM : **au plus deux workers lancent des tests en même temps**. w211 et wdpp testent par la CI de leur dépôt ; w241 seul lance `bin/run-tests`. |
| 33 (nouveau paquet) | US-211 : premier commit du dépôt copilote = test d'installation (rouge), squelette copié intégralement de `sylius-company-insights-plugin` **plus** les corrections du sprint 36 : règles 42 à 44. |

## Règles 42 à 45 (rétro du sprint 36)

42. **Nouveau dépôt : `.gitignore` vérifié avant le premier commit.** `vendor/`, `node_modules/`, `var/`, `tests/TestApplication/var/`, `public/bundles/`, `composer.lock` (dépôt de plugin), `.env.local`, `.env.*.local`, `.phpunit.cache/` et `.phpunit.result.cache` y figurent (liste de la [checklist d'intégration](../../templates/checklist-integration.md), « Nouveau dépôt de plugin ») ; `git status --short` est cité dans le rapport avant le premier `git add`. Jamais de `git add -A` ni de `git add .` dans un dépôt neuf : les fichiers sont ajoutés par chemin.
43. **Une CI verte ne prouve rien tant que le log ne montre pas les tests de la story.** Le rapport cite la ligne « Tests: N » ou « OK (N tests… ) » d'un job, et N doit compter les méthodes de test de la story. `phpunit.xml.dist` déclare une suite `all` qui couvre `tests/Integration` **et** `tests/Functional`, et la CI lance `--testsuite=all`.
44. **Aucune infrastructure factice.** La CI d'un plugin construit les vrais assets (`e2e_js: "yes"` dans `SyliusLabs/BuildTestAppAction`) ; pas de faux `entrypoints.json`, de template sans layout ni de route de test pour contourner un échec. En cas de blocage : log relu, cause citée, question à l'orchestrateur.
45. **Diagnostic sur le log, pas sur l'intuition.** Avant de rapporter une cause, citer l'étape qui échoue (nom du step) et la ligne d'erreur exacte du log CI (`gh run view <id> --log-failed`). Au sprint 36, « échec Webpack » était en fait un échec de test.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Pour les tests rouges : la ligne d'échec de chaque test, l'URL du run CI rouge, ou « non exécuté, story non comptée rouge prouvé ». SHA copiés de `git log -1`, ID de run copiés de `gh run list` (règle 37). Rapport final à l'orchestrateur en moins de 150 mots.
