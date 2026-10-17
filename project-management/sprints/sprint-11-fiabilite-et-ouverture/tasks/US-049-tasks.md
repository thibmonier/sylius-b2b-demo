# Tâches — US-049 : Fiabilité de la remise à zéro

## Informations US

- **Epic** : EPIC-08
- **Persona** : Thibaut
- **Story Points** : 3
- **Sprint** : sprint-11-fiabilite-et-ouverture
- **Dépendances** : US-033, US-041, US-048 (livrées)

## Résumé

En tant que Thibaut, je veux que toute défaillance du reset, y compris une erreur PHP fatale, soit signalée par `/healthz`, afin que le moniteur me prévienne.

## Règles

- Chaque correctif (T-049-02, T-049-04, T-049-06) est précédé d'un **test qui échoue avant correction** (T-049-01, T-049-03, T-049-05).
- Tâches **sacrifiables** marquées ⚠️ ; ordre de sacrifice dans [README.md](./README.md).
- T-049-08 est calendaire : GO et accès Railway de l'utilisateur, pas avant 2026-10-23, jamais close sur une estimation.
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-049-01 | [TEST] | Test rouge : une erreur fatale pendant le reset laisse le marqueur d'échec et `/healthz` répond 503 (reset réussi : marqueur absent, 200) | 1.5h | — | 🔲 |
| T-049-02 | [BE] | `ResetStatus` : marqueur « en cours » posé au début, retiré au seul succès (ou `register_shutdown_function`) ; échec suivi d'un succès retire le marqueur | 2.5h | T-049-01 | 🔲 |
| T-049-03 | [TEST] | Test rouge : `cache:clear` / `cache:warmup` tournent dans un sous-processus ; code de sortie non nul = reset en échec | 1h | — | 🔲 |
| T-049-04 | [BE] | `DemoResetCommand::stepClearWarmUpCache` / `runSubCommand` en sous-processus à mémoire fraîche ; `-d memory_limit=1G` du crontab conservé | 2.5h | T-049-03, T-049-02 | 🔲 |
| T-049-05 | [TEST] | ⚠️ Test rouge : corps de `POST /demo/webhooks/receive` au-dessus du plafond, y compris chunked, refusé en 413 par Caddy | 1.5h | — | 🔲 |
| T-049-06 | [BE] | ⚠️ `docker/Caddyfile` : `request_body max_size` pour `/demo/webhooks/receive` | 1h | T-049-05 | 🔲 |
| T-049-07 | [DOC] | Acter la limite de `demo_orders` (1 911 requêtes pour un objectif de 1 750, cœur Sylius) dans `docs/remise-a-zero-demo.md` ; documenter marqueur et sous-processus | 0.5h | T-049-04 | 🔲 |
| T-049-08 | [OPS] | 📅 Relevé de coût Railway complet (ex T-048-11 / T-041-15), pas avant 2026-10-23, par l'utilisateur | 0.5h | — | 📅 |
| T-049-09 | [REV] | Revue | 1h | T-049-04, T-049-07 | 🔲 |

**Total estimé : 12h** (9 tâches), dont 0.5h calendaire (T-049-08) et 2.5h sacrifiables (T-049-05, T-049-06).

---

## Détail des tâches

### T-049-01 — Test rouge du marqueur sur erreur fatale

- **Type** : [TEST]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `tests/Functional/Demo/` (reset et `/healthz`), `src/Demo/Command/ResetStatus.php`

**Description** : reproduire l'échec du 2026-10-17 sans épuiser la mémoire de la CI (sous-processus qui meurt par erreur fatale, ou simulation à l'arrêt). Le test échoue avant correction : le marqueur n'est pas écrit et `/healthz` répond 200.

**Critères de validation** :
- [ ] Test rouge avant correction, vert après T-049-02
- [ ] Cas reset réussi (marqueur absent) et échec suivi d'un succès couverts

### T-049-02 — Marqueur « en cours »

- **Type** : [BE]
- **Estimation** : 2.5h
- **Dépend de** : T-049-01

**Description** : poser le marqueur au début de `app:demo:reset`, ne le retirer qu'au succès ; alternative documentée : `register_shutdown_function`. Choisir la plus simple qui passe le test (KISS). `/healthz` lit le marqueur sans changer de contrat.

**Critères de validation** :
- [ ] Erreur fatale : marqueur présent, `/healthz` 503
- [ ] Succès : marqueur retiré, 200 ; ECS et PHPStan verts

### T-049-03 — Test rouge du sous-processus de cache

- **Type** : [TEST]
- **Estimation** : 1h

**Description** : vérifier que l'étape de cache lance un processus distinct (commande construite, code de sortie propagé) et que la limite `-d memory_limit=1G` du crontab reste appliquée.

### T-049-04 — Cache en sous-processus

- **Type** : [BE]
- **Estimation** : 2.5h
- **Dépend de** : T-049-03, T-049-02

**Description** : `stepClearWarmUpCache` n'appelle plus `cache:clear` / `cache:warmup` dans le processus des fixtures ; `runSubCommand` lance `php bin/console` via `Symfony\Component\Process`. Un code non nul fait échouer le reset (marqueur posé).

**Critères de validation** :
- [ ] Reset local complet vert ; pic mémoire du processus des fixtures non augmenté
- [ ] Crontab inchangé (`memory_limit=1G`)

### T-049-05 / T-049-06 — Plafond du corps de requête ⚠️

- **Type** : [TEST] puis [BE]
- **Estimation** : 1.5h puis 1h
- **Fichiers (indicatifs)** : `docker/Caddyfile`, test de fumée ou d'intégration Caddy

**Description** : constat mineur de revue du sprint 10 : un corps chunked est lu avant le contrôle de taille. Ajouter `request_body { max_size … }` limité à `/demo/webhooks/receive`, valeur alignée sur la taille maximale acceptée par le récepteur. Test d'abord : 413 attendu, y compris en chunked.

### T-049-07 — Documentation

- **Type** : [DOC]
- **Estimation** : 0.5h

**Description** : limite de `demo_orders` actée (1 911 requêtes, cœur Sylius, objectif 1 750 non atteint, pas de code), marqueur « en cours » et sous-processus décrits.

### T-049-08 — Relevé de coût Railway complet

- **Type** : [OPS] 📅
- **Estimation** : 0.5h
- **Condition** : pas avant 2026-10-23, GO et accès de l'utilisateur

**Description** : relevé complet (le partiel du 2026-10-17 indique 4,75 $ estimés), consigné dans `docs/deploiement-railway.md`. Aucune valeur estimée à la place du relevé.

### T-049-09 — Revue

- **Type** : [REV]
- **Estimation** : 1h

**Description** : revue du marqueur (courses entre reset et `/healthz`), du sous-processus (échappement des arguments, variables d'environnement transmises) et du plafond Caddy.
