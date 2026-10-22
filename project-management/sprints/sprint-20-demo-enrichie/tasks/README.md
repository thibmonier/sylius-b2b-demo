# Tâches : Sprint 020 (démo enrichie)

> Sprint Planning Part 2 du 2027-03-22 (décomposition préparée le 2026-10-22, après le planning du 2026-10-22 qui a validé le périmètre). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-20`). Sprint 19 : [../../sprint-19-pilotage-achats/tasks/README.md](../../sprint-19-pilotage-achats/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md). Tâches transverses : [technical-tasks.md](./technical-tasks.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-113](./US-113-tasks.md) | Accueil réorganisé : trois colonnes, pourquoi cette démo, qui l'a conçue | 3 | 7 | 6,5 h | 👀 (T-113-06 restante) |
| [US-152](./US-152-tasks.md) | Company-insights : ventilation par site et centre de coût, export CSV | 5 | 17 | 16,5 h | 👀 |
| [US-153](./US-153-tasks.md) | Company-insights : indicateurs des approbations et de l'encours | 5 | 12 | 14,25 h | 👀 |
| [US-156](./US-156-tasks.md) | Purchase-approval : approbations en attente signalées dans l'en-tête | 3 | 15 | 12,25 h | 👀 (T-156-06 restante) |
| [US-108](./US-108-tasks.md) | Page « Intégrations » : ERP, PIM, transporteurs, e-facture, grands comptes, assistants | 3 | 7 | 6,5 h | 👀 |
| [US-099](./US-099-tasks.md) | Simulateur PunchOut habillé et expliqué | 3 | 7 | 6,5 h | 👀 |
| [US-093](./US-093-tasks.md) | Catalogue crédible, tranche 2 : environ 150 références réalistes | 8 | 11 | 16,5 h | ↪️ sprint 21 (sacrifiée le 2026-10-22) |
| [Transverse](./technical-tasks.md) | Lock company-insights, actions de la rétro S19, vérification en production, clôture de l'orchestrateur (sans points) | 0 | 12 | 8,5 h | 🔄 (4 ✅, 1 🚫, 7 🔲) |

**Total décomposé** : 88 tâches | **87,5 h** pour 30 points (**après le sacrifice d'US-093 du 2026-10-22 : 77 tâches, 71 h, 22 points, soit 69,5 h de charge pour Claude**) (+ 12 tâches transverses sans points), dont 1,5 h d'actions de Thibaut (5 tâches), soit **86 h de charge pour Claude** (orchestrateur compris). Aucune tâche n'est bloquée au départ ; T-TECH-06 attend la reprise de Railway (calendaire).

> Les estimations des fiches (« ≈ 10 h », « ≈ 7 h »…) sont dépassées pour les trois stories de plugins : US-152 et US-153 portent chacune des tâches de dépôt de plugin, de revue de sécurité et de démo, US-152 porte en plus la release 0.2.0 (tag, Packagist, vérification), US-156 porte une release complète et la montée de version de la démo. Ces écarts expliquent l'excès de charge ci-dessous (même cause qu'au sprint 19).

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [DB] | 3 | 6 h | 7 % |
| [BE] | 13 | 15,75 h | 18 % |
| [FE-WEB] | 6 | 10,75 h | 12 % |
| [TEST] | 20 | 25,5 h | 29 % |
| [DOC] | 19 | 12,75 h | 15 % |
| [REV] | 7 | 4,25 h | 5 % |
| [OPS] | 20 | 12,5 h | 14 % |
| **Total** | **88** | **87,5 h** | 100 % |

**État à la clôture du 2026-10-22** : sur les 77 tâches du périmètre engagé, 67 ✅, 1 🚫 (T-TECH-06, vérification en production : Railway suspendu), 9 🔲 (T-113-06, T-156-06, T-TECH-02, 04, 05, 08, 09, 10, 11). Les 6 stories sont 👀 en review : vérification en production en attente (incident Railway). Voir [sprint-review.md](../sprint-review.md).

## Capacité

Méthode des sprints précédents : 30 points × 2,1 h ≈ **63 h** pour les stories (ratio du sprint 18) + 8 h de tâches transverses de Claude et de l'orchestrateur, soit **71 h**. La charge de Claude est de **86 h** : un **excès de 15 h** sur la capacité. Il est résorbé par le premier sacrifice, **US-093 (16,5 h)**, appliqué dès le planning du 2026-10-22 : il reste 69,5 h, sous la capacité (71 h), avec 1,5 h de marge et 22 points. US-152 et US-113 ne se sacrifient qu'en dernier recours.

**Reprises conditionnelles du sprint 19** (4 tâches, 3,25 h, voir [technical-tasks.md](./technical-tasks.md)) : si la rétrospective du 2027-03-19 les reporte, la charge après le sacrifice d'US-093 passe à 72,75 h, soit 1,75 h au-dessus de la capacité : **sacrifier aussi US-099** (6,5 h, charge restante 66,25 h).

Pistes de travail, une par thème pour éviter les fichiers communs : **outillage** (T-TECH-01 à T-TECH-04, T-TECH-07, T-TECH-08), **company-insights** (dépôt `sylius-company-insights-plugin` : US-152 puis US-153, release 0.2.0), **purchase-approval** (US-156), **démo** (bump de lock, puis US-113, US-108, US-099 dans cet ordre ; `composer.lock` à écrivain unique), **production** (T-TECH-05 et T-TECH-06 à la reprise de Railway). Un seul worker à la fois, sur tous les fichiers.

### Ordre de sacrifice (si les heures dépassent la capacité)

À appliquer dans cet ordre (validé par Thibaut le 2026-10-22), en s'arrêtant dès que la charge restante tient dans 71 h. **Non sacrifiables** : les actions de la rétro S19 (T-TECH), les tests d'accessibilité et de rendu réel, les revues. Toute tâche sacrifiée est consignée dans la revue comme dette datée. Si US-152 est sacrifiée en dernier recours alors qu'US-153 est maintenue, les tâches de release T-152-10 à T-152-14 passent dans US-153.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge de Claude décomposée) | n/a | 86 h | n/a |
| 1 | US-093 entière (catalogue de 150 références) — **APPLIQUÉ le 2026-10-22** | 16,5 h | 69,5 h | Catalogue à ≈ 80 références, aucune image ajoutée (le disque est épargné) ; 22 points engagés ; reprise au sprint 21 |
| 2 | US-099 entière (simulateur PunchOut) | 6,5 h | 63 h | Le simulateur reste en l'état (page brute) ; reprise au sprint 21 |
| 3 | US-108 entière (page Intégrations) | 6,5 h | 56,5 h | Pas de page « Intégrations » ; reprise au sprint 21 |
| 4 | US-156 entière (approbations dans l'en-tête) | 11,75 h | 44,75 h | Pas de nouvelle version de purchase-approval ; les tâches T-156-* sont retirées, `approver_roles` reste à sa valeur par défaut |
| 5 | US-153 entière (approbations et encours) | 14,25 h | 30,5 h | company-insights 0.2.0 sans widgets compagnons ; les tâches de release restent dans US-152 |

## Chemin critique et attentes

- **Début de sprint** : T-TECH-01 (lock company-insights 0.1.1), T-TECH-02 (budget disque), T-TECH-03 (gabarit de fiche) avant les tâches de code ; T-TECH-08 (gabarit de CI) avant la release 0.2.0.
- **Chemin critique de company-insights 0.2.0** : T-TECH-01 → T-152-01 → T-152-02 → T-152-03 → T-152-05 → T-152-06 → T-153-05 → T-153-06 → T-153-07 → T-153-09 → T-153-12 → T-152-10 → **T-152-11 (GO de Thibaut)** → T-152-12 → **T-152-13 (Packagist, Thibaut)** → T-152-14 → T-152-15 → T-152-16, T-153-10 → T-152-17, T-153-11.
- **Purchase-approval v1.2.0** : T-156-01 → T-156-04 → T-156-08 → **T-156-09 (GO)** → T-156-10 → **T-156-11 (Packagist, Thibaut)** → T-156-12 → T-156-13 (après T-152-17 : `composer.lock` à écrivain unique) → T-156-14.
- **Démo (fichiers d'accueil et de pages)** : US-113, puis US-108, puis US-099, jamais en parallèle ; T-TECH-11 (image) après T-099-05, T-153-11 et T-156-14.
- **Captures avant le jour 5** (action 1 de la rétro S19) : T-152-08, T-153-08 et T-156-06 se font dès l'écran terminé, avec `bin/check-disk` avant.
- **Production** : T-TECH-05 (déploiement de Thibaut, échéance 2027-03-26) → T-TECH-06 (vérification) ; calendaire, indépendant du reste.
- **Clôture** : T-TECH-11 (image) avant la revue de sprint, puis T-TECH-12 (bilan pour la rétro du 2027-04-02).

## Actions de Thibaut (tâches qui ne sont pas réalisables par Claude seul)

| ID | Action | Durée |
|----|--------|-------|
| T-TECH-05 | Déploiement groupé des PR #43 à #48 sur Railway à la reprise (échéance 2027-03-26) | 0,5 h |
| T-152-11 | GO du tag et de la release `v0.2.0` de company-insights | 0,25 h |
| T-152-13 | Confirme l'indexation de la 0.2.0 sur Packagist | 0,25 h |
| T-156-09 | GO du tag et de la release `v1.2.0` de purchase-approval | 0,25 h |
| T-156-11 | Confirme l'indexation de la v1.2.0 sur Packagist | 0,25 h |
| T-099-01 | Confirme (ou retire) le 5e scénario de la fiche d'US-099 (URL de retour hors domaines autorisés) | inclus |

Notes :
- **Packagist** : les deux paquets existent déjà ; Thibaut confirme l'indexation de la nouvelle version, Claude prépare le tag, la release et un script à OK ou FAIL par élément (T-TECH-10). Aucune soumission de nouveau paquet dans ce sprint.
- **GO** : un GO par release (T-152-11, T-156-09). Push, PR, merge et tag seulement après ce GO ; commit, push, PR, merge et tag de la démo sur demande explicite.
- **Ordre de publication** (action 2 de la rétro S19) : tag, Packagist, vérification de la version publiée, puis bump du `composer.lock` de la démo ; la PR de la démo ne s'ouvre qu'après la vérification.
- **Railway** : en incident ; aucune commande Railway par Claude.
- **Politique** : aucune comparaison à Sylius Plus dans les textes publiés ; aucun détail de sécurité dans les dépôts publics (revues rapportées hors dépôt).

## Prérequis d'environnement par story (action 5 de la rétro S17)

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-152, US-153 | Dépôt `sylius-company-insights-plugin` à jour sur `main` (v0.1.1), `git fetch` et branche vérifiés ; matrice CI et `composer.json` relus ; Docker des plugins ; lock de la démo sur 0.1.1 ; gabarit de CI vérifié | T-152-01, T-TECH-01, T-TECH-08 |
| US-156 | Dépôt `sylius-purchase-approval-plugin` : branche locale `fix/company-tabs-test` ancienne, repartir de `main` (v1.1.0) après `git fetch` ; matrice CI relue | T-156-01 |
| US-113, US-108 | Base de test par worker ; pa11y et `bin/measure-mobile` disponibles ; CTA d'US-158 en place | T-113-05, T-108-05 |
| US-099 | Idem ; décision de Thibaut sur le 5e scénario | T-099-01, T-099-05 |
| US-093 | (sacrifiée le 2026-10-22, reportée au sprint 21 : budget d'images et `bin/check-disk` à reprendre) | T-093-01, T-093-07 |
| Transverse | Docker (conteneur MySQL du projet) pour `bin/check-disk` ; Railway repris pour T-TECH-06 | T-TECH-02, T-TECH-05 |

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 100 mots au total).** Le Scrum Master refuse de clore une tâche sans lui, et le refuse tant que les preuves rejouables manquent.

```markdown
## Rapport de fin (en premier, ≤ 100 mots)

- **Tâches** : T-XXX-YY
- **Worker** : nom (sous-dossier de scratchpad, base `b2b_<worker>`, cache `var/cache-<worker>`)
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Preuves rejouables** : pour PHPStan, ECS, lint Twig/YAML et suite, la commande exacte et la dernière ligne de sortie, ou « non exécutés » + raison
- **Rendu réel** : pages vues (URL, FR et EN) ou « sans objet »
- **Nettoyage** : cache et base du worker supprimés (`bin/clean-test-caches` ciblé), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

### Règles d'exécution (leçons des rétros des sprints 17, 18 et 19)

1. **Disque** : `df -h .` et `bin/check-disk` au démarrage de chaque worker et **avant chaque lot de tests** ; l'orchestrateur le contrôle avant de lancer des workers (début et milieu de journée). Environ 5,4 Go libres : sous 15 Go, prévenir Thibaut sans attendre ; l'espace manquant se diagnostique d'abord (projet, VM Docker ou machine) avant tout nettoyage. Un seul build d'image à la fois. Le seuil `MIN_FREE_GB` de `bin/run-tests` (5 Go) n'est jamais abaissé. Les captures sont faites tôt (avant le jour 5), pas en fin de sprint.
2. **Isolation par worker** : un sous-dossier de scratchpad par worker, une base de test (`TEST_DB_NAME=b2b_<worker>`) et un cache (`TEST_CACHE_DIR=var/cache-<worker>`) par worker ; un worker ne nettoie que ses propres ressources (`WORKER_ID`), `--all` est réservé à l'orchestrateur hors lots. Un seul worker à la fois sur le sprint.
3. **Vérification croisée à l'intégration** : le rapport cite la commande et la dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite. **L'orchestrateur rejoue ECS (`src tests`) et PHPStan sur l'arbre exact avant chaque push** (T-TECH-09). Un « vert » sans preuve est refusé. Le gate est recoupé avec la somme des points du fichier de statut (T-TECH-04) ; **un refus d'outil n'arrête jamais le sprint** : il est signalé à Thibaut et le travail continue.
4. **Attente bornée** : `bin/run-tests --wait`, sortie après 30 minutes avec un rapport ; une tâche de fond est arrêtée explicitement, l'arrêt vérifié, puis relancée.
5. **Cadrage d'un plugin** : avant d'écrire la fiche de tâches d'un plugin touché, lire sa matrice CI (`.github/workflows/build.yaml`) et son `composer.json` ; consigner les versions supportées et l'impact de version (fait pour company-insights et purchase-approval le 2026-10-22).
6. **Secrets fictifs** : jamais de faux jeton au format réel écrit en clair dans un test ; le recomposer à l'exécution (valeurs piégées de l'export CSV comprises).
7. **Ordre de publication** : tag du plugin, Packagist, vérification de la version publiée, puis seulement le bump du `composer.lock` de la démo et sa PR.

**Rappels** : seul `bin/run-tests` est autorisé dans la démo (jamais `phpunit` en direct) ; dans les plugins, tests dans Docker ; second run refusé par le verrou (code 4) ; comptes de démo partagés isolés (trait `IsolatesSharedAccounts`, [docs/tests-locaux.md](../../../../docs/tests-locaux.md)).

**Règles propres au sprint** : ECS sur `src tests` et PHPStan max ; mobile mesuré avec `bin/measure-mobile`, pa11y WCAG2AAA ; libellés FR et EN ; seul l'orchestrateur reconstruit l'image Docker ; `git add` par chemins explicites ; dépôts de plugins : `git fetch` puis branche courante vérifiée avant tout commit ; aucun push, PR, merge ni tag sans GO ; branche principale de la démo protégée (PR et contrôles « Quality and smoke tests » et « Docker image », attendre la fin du run CI avant le merge) ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets ; aucun détail exploitable sur une faille dans un dépôt public ; aucune mention ni comparaison à Sylius Plus dans les textes publiés ; revues de sécurité rapportées hors dépôt public.
