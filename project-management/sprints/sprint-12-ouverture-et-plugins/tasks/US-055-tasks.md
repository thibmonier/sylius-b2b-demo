# Tâches — US-055 : Actions de rétro et dette

## Informations US

- **Epic** : EPIC-08
- **Persona** : Thibaut
- **Story Points** : 3
- **Sprint** : sprint-12-ouverture-et-plugins
- **Dépendances** : aucune

## Résumé

En tant que Thibaut, je veux appliquer les actions des rétrospectives (disque, cache de test, relecteurs), vérifier l'action 4 du sprint 10 et débloquer US-031, afin que les sprints suivants ne perdent plus de temps sur l'environnement.

## Règles

- Les actions 1 à 3 de la rétro du sprint 11 sont déjà inscrites dans les fichiers du sprint 12 (`tasks/README.md`, `pre-sprint-checklist.md`) ; T-055-01 les reporte dans les modèles réutilisables.
- `bin/run-tests` ne touche jamais la base de développement ni le vrai `var/`.
- Tâche **sacrifiable** ⚠️ : T-055-04 ; ordre dans [README.md](./README.md).
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Constats de planification (2026-10-17)

- **Action 4 du sprint 10** (« reporter les mineurs au backlog ») : **non faite**. La revue du sprint 10 demande de créer « Crédits des images » (T-047-09) et « Mineurs du script d'images » ; la section `backlog:` de `.bmad/sprint-status.yaml` ne les contient pas. Ils sont créés à la planification : US-061 et US-062 (voir T-055-06).
- **US-031** : `composer show tmonier/sylius-agentic-feeds-plugin --available` donne `v1.0.0` (publiée le 2026-10-13) et `dev-main` : le paquet est sur Packagist. Le README de punchout-cxml documente `PUNCHOUT_SUPPLIER_DOMAIN` et `PUNCHOUT_SUPPLIER_IDENTITY`. Reste : la démo déclare encore un dépôt VCS GitHub pour agentic-feeds dans `composer.json`.

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-055-01 | [DOC] | Contrat de fin de worker (nettoyage `var/cache-*`) et ligne `df` : vérifiés dans les fichiers du sprint 12 et reportés dans les modèles de sprint | 0.5h | — | ✅ |
| T-055-02 | [BE] | `bin/run-tests` : cache de test dédié par exécution, vidé avant la suite et supprimé à la fin, contrôle `df` (arrêt sous 5 Go), appelle `bin/prepare-test-db` | 2.5h | — | ✅ |
| T-055-03 | [TEST] | Vérification : suite complète deux fois de suite sans erreur liée au cache, y compris après un changement de constructeur ; aucun fichier de test dans le vrai `var/` | 1.5h | T-055-02 | ✅ |
| T-055-04 | [DOC] | ⚠️ `docs/tests-locaux.md` : `bin/run-tests`, cache isolé, seuil disque | 0.5h | T-055-03 | ✅ |
| T-055-05 | [DOC] | Consigne d'orchestration des relecteurs (12 tours minimum, rapport exigé) : vérifiée dans `tasks/README.md` et reportée dans le modèle de consigne | 0.5h | — | ✅ |
| T-055-06 | [REV] | Action 4 du sprint 10 : mineurs au backlog (US-061, US-062), suivi consigné dans la rétro du sprint 12 | 1h | — | ✅ |
| T-055-07 | [BE] | US-031 : retirer le dépôt VCS d'agentic-feeds de `composer.json`, résolution depuis Packagist (`composer update` ciblé dans Docker), README punchout vérifié, US-031 `done` | 1.5h | — | ✅ |
| T-055-08 | [REV] | Revue | 0.5h | T-055-03, T-055-07 | 🔲 |

**Total estimé : 8.5h** (8 tâches), dont 0.5h sacrifiable (T-055-04).

## Résultats (worker w12-tools, 2026-10-17)

- **T-055-01 / T-055-05** : contrat de fin de worker, ligne `df` (5 Go) et consigne des relecteurs vérifiés dans `tasks/README.md` et `pre-sprint-checklist.md`. Le dépôt ne contient aucun modèle réutilisable de consigne à mettre à jour (les modèles de `/project:decompose-tasks` et `/workflow:start` sont hors dépôt) : rien à reporter ici.
- **T-055-02** : `bin/run-tests` (cache `mktemp` vide par exécution, supprimé à la fin, arrêt sous 5 Go, appelle `bin/prepare-test-db`). Test rouge puis vert : constructeur de `HealthController` modifié sur un cache chaud, `PagesTest` donne un 500 (cache périmé), puis passe avec `bin/run-tests`. Cause d'un faux échec de la première passe (`PublicOpeningHardeningTest`) : les compteurs des limiteurs étaient dans le vrai `var/demo-limiter-test`, vidé par le démarrage d'une autre exécution en parallèle. Compteurs, images de test, journaux et pools partagés vivent maintenant dans le cache de l'exécution.
- **T-055-03** : suite complète deux fois de suite avec `bin/run-tests` : 861 tests, 8558 assertions, 0 échec les deux fois (1 test ignoré, 207 dépréciations, déjà là). Aucun fichier de test écrit dans le vrai `var/` (seul `var/share/dev` a bougé, écrit par un autre worker en `dev`).
- **T-055-04** : `docs/tests-locaux.md`, section « Une commande pour tout ».
- **T-055-06** : `US-061` et `US-062` sont dans `backlog:` de `.bmad/sprint-status.yaml` (vérifié) ; le suivi dans la rétro du sprint 12 se consigne en séance (la rétro n'existe pas encore).
- **T-055-07 🚫 bloquée** : le paquet n'est **pas résolvable depuis Packagist**. Retiré le dépôt VCS, `composer update tmonier/sylius-agentic-feeds-plugin` répond « not found in remote repositories » ; `https://repo.packagist.org/p2/tmonier/sylius-agentic-feeds-plugin.json` répond `404`, alors que `composer search` liste le nom. La version `v1.0.0` vue par `composer show --available` venait du dépôt VCS GitHub, pas de Packagist (même constat dans un dossier vide sans dépôt). `composer.json` et `composer.lock` sont restés inchangés. Action de l'utilisateur : sur packagist.org, page du paquet, forcer la mise à jour (ou vérifier le webhook GitHub), puis relancer cette tâche.

---

## Détail des tâches

### T-055-01 — Contrat de fin de worker et ligne `df`

- **Type** : [DOC]
- **Estimation** : 0.5h

**Description** : l'étape de nettoyage (`rm -rf var/cache-<nom>` et `df -h .` dans le rapport) est dans `tasks/README.md` du sprint 12 ; la ligne `df` (arrêt sous 5 Go) est dans `pre-sprint-checklist.md`. Vérifier leur présence et les reporter dans les modèles utilisés par `/project:decompose-tasks` et `/workflow:start` du projet s'il y en a.

### T-055-02 — `bin/run-tests`

- **Type** : [BE]
- **Estimation** : 2.5h
- **Fichiers (indicatifs)** : `bin/run-tests`, `bin/prepare-test-db`

**Description** : script POSIX sur le modèle de `bin/prepare-test-db` : crée un dossier de cache dédié sous un chemin temporaire (`TEST_CACHE_DIR`, variable déjà prise en charge par `bin/prepare-test-db`), le vide avant la suite, l'efface à la fin (`trap`), refuse de démarrer sous 5 Go libres, passe les arguments à PHPUnit. Test rouge d'abord : exécuter deux fois la suite ciblée après un changement de constructeur sans le script (échec de cache périmé), puis avec.

### T-055-03 — Deux passes

- **Type** : [TEST]
- **Estimation** : 1.5h

**Description** : suite complète deux fois de suite sans erreur liée au cache, après un changement de constructeur factice (annulé ensuite) ; contrôle qu'aucun fichier de test n'est écrit dans le vrai `var/` (`git status --ignored var/` avant et après).

### T-055-04 — `docs/tests-locaux.md` ⚠️

- **Type** : [DOC]
- **Estimation** : 0.5h

**Description** : décrire `bin/run-tests`, le cache isolé et le seuil de 5 Go ; renvoi depuis `bin/prepare-test-db`.

### T-055-05 — Consigne des relecteurs

- **Type** : [DOC]
- **Estimation** : 0.5h

**Description** : la consigne (12 tours minimum ou agent `general-purpose`, rapport exigé dans les derniers tours) est dans `tasks/README.md` du sprint 12. Vérifier qu'elle est reportée dans le modèle de consigne de revue.

### T-055-06 — Action 4 du sprint 10

- **Type** : [REV]
- **Estimation** : 1h

**Description** : confirmer en séance le constat (non faite), vérifier que US-061 « Crédits des images » (T-047-09) et US-062 « Mineurs du script d'images » (limite de pixels et `Content-Length`, gestion du 429, image principale conservée si le manifeste change) sont dans `backlog:` avec les mineurs acceptés de la revue du sprint 10 (`127.0.0.1` dans `trusted_hosts`, corps chunked sans `Content-Length`). Mettre à jour le suivi des actions dans la rétro du sprint 12.

### T-055-07 — US-031

- **Type** : [BE]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `composer.json`, `composer.lock`

**Description** : confirmer que `composer show tmonier/sylius-agentic-feeds-plugin --available` liste `v1.0.0`. Retirer le dépôt VCS de `composer.json` (ligne `https://github.com/thibmonier/sylius-agentic-feeds-plugin`), relancer la résolution ciblée dans Docker, vérifier que `composer.lock` pointe sur Packagist et que l'installation reste identique, puis passer US-031 à `done`. Pas de rebuild d'image.

### T-055-08 — Revue

- **Type** : [REV]
- **Estimation** : 0.5h

**Description** : relecture du script (aucune suppression hors de son propre cache, aucun accès à la base de développement), du diff `composer.json` / `composer.lock`, absence de secret.
