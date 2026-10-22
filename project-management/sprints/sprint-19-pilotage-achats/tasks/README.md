# Tâches : Sprint 019 (pilotage des achats)

> Sprint Planning Part 2 du 2027-03-08 (décomposition préparée le 2026-10-21). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-19`). Sprint 18 : [../../sprint-18-repositionnement/tasks/README.md](../../sprint-18-repositionnement/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md). Tâches transverses : [technical-tasks.md](./technical-tasks.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-149](./US-149-tasks.md) | Company-insights : squelette du plugin et indicateurs de la période | 5 | 13 | 18,25 h | 🔲 |
| [US-150](./US-150-tasks.md) | Company-insights : achats par mois sur 12 mois, comparés à l'année précédente | 5 | 7 | 10 h | 🔲 |
| [US-151](./US-151-tasks.md) | Company-insights : tops acheteurs, produits et catégories | 5 | 7 | 10,25 h | 🔲 |
| [US-155](./US-155-tasks.md) | Company-insights : historique d'achats de 12 mois et intégration à la démo | 5 | 15 | 13,25 h | 🔲 |
| [US-146](./US-146-tasks.md) | Création de commande admin : choisir une adresse de l'entreprise ou du carnet du client | 5 | 13 | 14 h | 🔲 |
| [US-099](./US-099-tasks.md) | Simulateur PunchOut habillé et expliqué | 3 | 7 | 6,5 h | 🔲 |
| [US-121](./US-121-tasks.md) | Kit média (2/2) : approbation, MCP, container-fill et visite complète | 5 | 12 | 10,5 h | 🚫 partielle |
| [Transverse](./technical-tasks.md) | Actions de la rétro S18 et clôture de l'orchestrateur (sans points) | 0 | 9 | 6,5 h | 🔲 |

**Total décomposé** : 83 tâches | **89,25 h** pour 33 points (+ 9 tâches transverses sans points), dont 1,75 h d'actions de Thibaut (6 tâches), soit **87,5 h de charge pour Claude** (orchestrateur compris). 4 tâches (2,75 h) sont bloquées (🚫) par T-103-03 dans US-121.

> Les estimations de la fiche (« ≈ 12 h », « ≈ 10 h »…) sont dépassées pour trois stories : US-149 porte tout le squelette d'un nouveau dépôt (CI, README, ADR), US-155 le plan de commandes, la release et la montée de version, US-146 deux dépôts (admin-order-creation et company-accounts) plus la démo. Ces écarts expliquent l'excès de charge ci-dessous.

## Capacité

Capacité retenue : 33 points × 2,1 h ≈ **69,3 h** pour les stories (ratio du sprint 18) + 6,5 h de tâches transverses de Claude et de l'orchestrateur, soit **75,8 h**. La charge de Claude est de **87,5 h** : un excès de **11,7 h** sur la capacité. Il se résorbe par l'ordre de sacrifice au point d'étape du 2027-03-12 ; les quatre Must d'EPIC-15 (20 points) ne se sacrifient pas.

Pistes de travail, une par thème pour éviter les fichiers communs : **outillage** (T-TECH-01 à T-TECH-07, démo), **nouveau plugin** (dépôt `sylius-company-insights-plugin` : US-149, puis US-150, puis US-151), **plugins existants** (US-146 : admin-order-creation puis company-accounts), **démo** (US-099 puis US-155 puis T-146-11 et T-146-12 ; `composer.lock` à écrivain unique), **rédaction et média** (US-121, hors dépôt public). Un seul worker à la fois, sur tous les fichiers.

### Ordre de sacrifice (si les heures dépassent la capacité)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans 75,8 h. **Non sacrifiables** : US-149, US-150, US-151, US-155, les actions de la rétro S18 (T-TECH), les tests d'accessibilité et de rendu réel, les revues. Toute tâche sacrifiée est consignée dans la revue comme dette datée.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge de Claude décomposée) | n/a | 87,5 h | n/a |
| 1 | US-121 entière (kit média 2/2), de T-121-01 à T-121-11 | 10 h | 77,5 h | Pas de séquences approbation, MCP et container-fill ni de script de visite ; repli : les séquences d'US-120. Sacrifiée d'office si T-103-03 n'arrive pas avant le point d'étape du 2027-03-12 |
| 2 | US-099 entière (simulateur habillé) | 6,5 h | 71 h | Le simulateur reste en l'état (page brute) ; reprise au sprint 20 |
| 3 | US-146 entière (adresses en création de commande), T-146-01 à T-146-13 sauf le GO | 13,75 h | 57,25 h | Pas de nouvelle version d'admin-order-creation ni de company-accounts ; reprise au sprint 20 |

Les trois sacrifices ensemble retirent 30,25 h : seules les stories d'EPIC-15 et les tâches transverses resteraient (57,25 h).

## Chemin critique et attentes

- **Début de sprint** : T-TECH-02 (disque de la VM Docker) et T-TECH-03 (gabarit : lecture de la matrice CI au cadrage) passent avant les autres tâches de code.
- **Chemin critique d'EPIC-15** : T-149-01 → T-149-02 → T-149-03 (GO de Thibaut) → T-149-04 (dépôt public) ; T-149-05 → T-149-06 → T-149-09 → T-149-10 → T-149-12 → T-149-13 ; puis US-150 (T-150-01 à T-150-07), US-151 (T-151-01 à T-151-07), enfin US-155.
- **Plugin 0.1.0** : T-155-05 → T-155-06 (GO de Thibaut) → T-155-07 (tag) → **T-155-08 (soumission Packagist par Thibaut)** → T-155-09 (démo). Sans la soumission, la démo ne peut pas monter la version : la demander dès la fin d'US-151.
- **Démo** : T-099-07 (revue du simulateur) → T-155-09 → T-155-11 ; `composer.lock` à écrivain unique (T-155-09 puis T-146-11, ou l'inverse, jamais ensemble). PR #46 (PHP 8.5.12) : décision de Thibaut connue avant T-155-09.
- **Plugins existants** : T-146-01 → T-146-03 → T-146-04 ; T-146-05 → T-146-06 ; T-146-09 (GO) → T-146-10 (admin-order-creation puis company-accounts).
- **Kit média** : T-121-01 à T-121-04, T-121-06, T-121-08, T-121-10, T-121-11 ne dépendent pas de T-103-03. **Bloquées** : T-121-05, T-121-07, T-121-09, T-121-12.
- **Clôture** : T-TECH-08 (image) avant la revue de sprint, puis T-TECH-09 (bilan pour la rétro du 2027-03-19).

## Actions de Thibaut (tâches qui ne sont pas réalisables par Claude seul)

| ID | Action | Durée |
|----|--------|-------|
| T-149-03 | Thibaut donne le GO : création du dépôt public `sylius-company-insights-plugin` et premier push | 0,25 h |
| T-155-06 | Thibaut donne le GO du tag et de la release `v0.1.0` du plugin | 0,25 h |
| T-155-08 | Thibaut soumet le plugin à Packagist (nouveau paquet) et confirme l'indexation de la 0.1.0 | 0,25 h |
| T-155-14 | Thibaut relit la documentation technique de Sylius Plus (S9) s'il y a accès et confirme ou corrige la classe de company-insights (non bloquant) | 0,25 h |
| T-146-09 | Thibaut donne le GO unique : push des deux branches, deux PR, merge quand les CI sont vertes, tags v1.1.0 (admin-order-creation) et v1.3.0 (company-accounts) | 0,25 h |
| T-121-12 | Thibaut relit et valide les séquences et le script de la visite complète (sinon séquence refaite) — *bloquée : attend le segment et la séquence MCP* | 0,5 h |
| T-103-03 (sprint 18) | Thibaut réalise la conversation réelle avec son assistant sur le MCP de production : elle débloque US-121 | 1 h |

Notes :
- **Packagist** : Thibaut soumet lui-même company-insights (T-155-08) ; Claude prépare le dépôt, le tag, la release et un script à OK ou FAIL par élément (T-TECH-07).
- **GO** : un GO pour le dépôt public de company-insights (T-149-03), un pour le tag 0.1.0 (T-155-06), un GO unique pour les deux plugins d'US-146 (T-146-09). Commit, push, PR, merge et tag de la démo sur demande explicite.
- **Railway** : en incident ; redéploiement de la démo par Thibaut après la fin de l'incident, aucune commande Railway par Claude. **PR #46** : merge par Thibaut.
- **Documentation de Plus** : T-155-14, non bloquant.

## Prérequis d'environnement par story (action 5 de la rétro S17)

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-149 | Docker des plugins ; matrice CI et `composer.json` d'agentic-feeds et de company-accounts lus ; GO de Thibaut pour le dépôt public ; `gh` connecté | T-149-01, T-149-03 |
| US-150, US-151 | Dépôt de company-insights avec squelette et CI verts (US-149) ; apexcharts embarqué sans CDN | T-150-01, T-151-01 |
| US-155 | Plugin 0.1.0 indexé par Packagist (soumission de Thibaut) ; décision sur la PR #46 ; disque suffisant ; base de test (`bin/prepare-test-db`) | T-155-08, T-155-09 |
| US-146 | `git fetch` et branche vérifiée dans admin-order-creation et company-accounts ; matrice CI relue ; GO unique ; Packagist qui répond | T-146-01, T-146-09 |
| US-099 | Base de test par worker ; pa11y et `bin/measure-mobile` disponibles | T-099-05 |
| US-121 | Démo locale remise à zéro ; `ffmpeg` ; **T-103-03 (conversation réelle de Thibaut)** pour la séquence MCP ; retour HTTPS pour la séquence PunchOut (repli de Thibaut) | T-121-02, T-103-07 |
| Transverse | Docker (conteneur MySQL du projet) pour `bin/check-disk` | T-TECH-01 |

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

### Règles d'exécution (leçons des rétros des sprints 17 et 18)

1. **Disque** : `df -h .` et `bin/check-disk` au démarrage de chaque worker et **avant chaque lot de tests** ; l'orchestrateur le contrôle avant de lancer des workers (début et milieu de journée). Environ 7 Go libres : sous 15 Go, prévenir Thibaut sans attendre ; l'espace manquant se diagnostique d'abord (projet, VM Docker ou machine) avant tout nettoyage. Un seul build d'image à la fois. Le seuil `MIN_FREE_GB` de `bin/run-tests` (5 Go) n'est jamais abaissé.
2. **Isolation par worker** : un sous-dossier de scratchpad par worker, une base de test (`TEST_DB_NAME=b2b_<worker>`) et un cache (`TEST_CACHE_DIR=var/cache-<worker>`) par worker ; un worker ne nettoie que ses propres ressources (`WORKER_ID`), `--all` est réservé à l'orchestrateur hors lots. Un seul worker à la fois sur le sprint.
3. **Vérification croisée à l'intégration** : le rapport cite la commande et la dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite. **L'orchestrateur rejoue ECS (`src tests`) et PHPStan sur l'arbre exact avant chaque push** (action 3 de la rétro S18). Un « vert » sans preuve est refusé.
4. **Attente bornée** : `bin/run-tests --wait`, sortie après 30 minutes avec un rapport ; une tâche de fond est arrêtée explicitement, l'arrêt vérifié, puis relancée.
5. **Cadrage d'un plugin** : avant d'écrire la fiche de tâches d'un plugin touché, lire sa matrice CI (`.github/workflows/build.yaml`) et son `composer.json` (action 2 de la rétro S18) ; consigner les versions supportées et l'impact de version.
6. **Secrets fictifs** : jamais de faux jeton au format réel écrit en clair dans un test ; le recomposer à l'exécution (action 4).

**Rappels** : seul `bin/run-tests` est autorisé dans la démo (jamais `phpunit` en direct) ; dans les plugins, tests dans Docker ; second run refusé par le verrou (code 4) ; comptes de démo partagés isolés (trait `IsolatesSharedAccounts`, [docs/tests-locaux.md](../../../../docs/tests-locaux.md)).

**Règles propres au sprint** : ECS sur `src tests` et PHPStan max ; mobile mesuré avec `bin/measure-mobile`, pa11y WCAG2AAA ; libellés FR et EN ; seul l'orchestrateur reconstruit l'image Docker ; `git add` par chemins explicites ; dépôts de plugins : `git fetch` puis branche courante vérifiée avant tout commit ; aucun push, PR, merge ni tag sans GO ; branche principale de la démo protégée (PR et contrôles « Quality and smoke tests » et « Docker image », attendre la fin du run CI avant le merge) ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets ; aucun détail exploitable sur une faille dans un dépôt public ; company-insights : ton neutre, aucune mention de Sylius Plus dans les textes publiés ; livrables rédactionnels et média dans `/Users/tmonier/Projects/sylius_plugins/communication/sprint-19/` (hors dépôt public, référencés dans `communication/INDEX.md`).
