# Consigne de worker : Sprint 036

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md). Les règles 1 à 34 de la [consigne du sprint 35](../sprint-35-commerce-predictif/consigne-worker.md) restent applicables telles quelles, sauf les adaptations du tableau « Sprint 36 » ci-dessous. Les règles 35 à 41 sont nouvelles (action T-TECH-36-03 de la rétro du sprint 35). L'orchestrateur colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Détails des outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).
>
> **Règle 1 en tête : les tests de la démo se lancent seulement par `bin/run-tests`, jamais par `phpunit` en direct.**

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Dépôt de travail | Base | Cache |
|--------|---------|-------------|------------------|------|-------|
| w240 | US-240 | `w240` | plugin prédictif (`sylius-b2b-predictive-analytics-plugin`) + démo (fixtures) | `b2b_w240` | `cache-w240` |
| w239 | US-239 | `w239` | démo (`.github/`, `bin/`) | `b2b_w239` | `cache-w239` |
| w230 | US-230 puis US-231 | `w230` | nouveau dépôt `sylius-digital-product-passport-plugin` | aucune (dépôt séparé) | aucun |

## Sprint 36 : adaptations des règles 1 à 34

| Règle | Sprint 36 |
|-------|-----------|
| 6 (branches) | Démo : `feature/sprint-36-predictif-et-passeport` (arbre principal), **pas de checkout dans l'arbre partagé**. Plugin prédictif : `feature/us-240-guaranteed-alerts` depuis `origin/main` (règle 29). Plugin passeport : `feature/dpp-skeleton` (US-230) puis `feature/dpp-public-page` (US-231) depuis `origin/main`. |
| 8 et 30 (GO) | Registre du 2026-10-29 (auteur : Thibaut) : **l'orchestrateur a créé le dépôt du passeport et fusionne** les PR des plugins à CI verte ; le worker pousse sa branche et ouvre la PR ; **aucun tag ni release** (v0.1.1 du prédictif et v0.1.0 du passeport sur GO de Thibaut). Démo : aucun push sans demande de l'orchestrateur. |
| 10 (plugins) | Seuls le plugin prédictif (US-240) et le plugin passeport (US-230, US-231) sont modifiés. `composer.json` / `composer.lock` de la démo inchangés (montée en v0.1.1 après le tag, par l'orchestrateur). |
| 13 et 27 (rouge) | Test rouge commité seul, ligne d'échec citée ; voir aussi la règle 36. |
| 23 (captures) | US-240 : captures du bloc d'alertes « Mon compte » pour le README du plugin prédictif (EN + FR). US-231 : capture de la page publique du passeport pour le README du plugin passeport. |
| 24 (parallélisme) | Relevé du 2026-10-29 : 10 Go libres, swap 3,6 Go : **au plus deux workers lancent des tests en même temps** (w240 et w230 ; w239 n'a que des tests de script légers). |
| 28 (invariants) | US-240 : invariants de l'historique de la démo **non négociables** : aucune commande ajoutée (≤ 55 commandes Acme), creux d'août, même jour du mois, Paul en retard. La garantie d'alertes se fait par le calcul (dates relatives), jamais par de nouvelles commandes ni en adaptant un test au bug. |
| 33 (nouveau paquet) | US-230 : le premier commit du dépôt passeport est le test d'installation (rouge), squelette copié intégralement de `sylius-company-insights-plugin`. |

## Règles 35 à 41 (rétro du sprint 35)

35. **Un push, puis on attend le run.** Une rafale de pushs annule les runs précédents et la preuve du rouge est perdue. Le worker pousse une fois, puis interroge GitHub **au plus une fois toutes les 3 minutes** avec `gh run view <id>` ou `bin/wait-for-ci` ; **jamais `gh run watch`** (limite secondaire de GitHub avec plusieurs workers).
36. **Un rouge valide échoue sur une assertion du comportement, après un setup qui passe.** Un rouge dû à PHPStan, ECS, la construction, le démarrage du noyau, un 404/302 ou un nom faux ne compte pas. Si tests et code ont été commités ensemble, la preuve se fait par un commit de preuve qui casse le code, poussé seul, puis annulé.
37. **Aucun « vert » sans preuve.** PHPStan, ECS et la suite : commande exacte et dernière ligne de sortie, relues **après** le dernier commit. Annoncer vert un contrôle rouge est une faute de rapport.
38. **Tout nom vérifié dans `vendor/` avant usage** : classe, méthode, service, hook, route (`grep -rn` dans `vendor/` ou dans le code du plugin). Aucune classe ni méthode inventée (incidents `TestClient`, `getShopUser()`).
39. **Aucun test vide ni affaibli** : pas de `assertGreaterThanOrEqual(0)`, pas de `markTestIncomplete`/`markTestSkipped` pour obtenir du vert, pas de données de test adaptées à un bug (règle 32 renforcée).
40. **Jamais de force-push** (`--force`, `--force-with-lease`, `+refspec`) : le hook `pre-push` de la démo le refuse (T-TECH-36-01) ; dans les dépôts de plugins, la règle s'applique sans hook. Une correction se fait par un nouveau commit.
41. **Lire les fichiers d'une autre branche par `git show origin/<branche>:<fichier>`**, jamais par `git checkout`. Avant tout lot de tests ou de rendus : `bin/check-resources` (T-TECH-36-05) ; sous le seuil, écrire le code sans rien lancer et le dire.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**, ou « non exécuté » avec la raison. Pour les tests rouges : la ligne d'échec de chaque test, l'URL du run CI rouge, ou « non exécuté, story non comptée rouge prouvé ». Rapport final à l'orchestrateur en moins de 150 mots.
