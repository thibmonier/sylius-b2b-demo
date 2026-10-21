# Tâches : Sprint 018 (repositionnement)

> Sprint Planning Part 2 du 2027-02-22 (décomposition préparée le 2026-10-21). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-18`). Sprint 17 : [../../sprint-17-conversion/tasks/README.md](../../sprint-17-conversion/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md). Tâches transverses : [technical-tasks.md](./technical-tasks.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-158](./US-158-tasks.md) | Repositionnement de la démo : profil LinkedIn, libellés neutres, guide technique | 3 | 10 | 12 h | 👀 |
| [US-147](./US-147-tasks.md) | Démo en PHP 8.5 (image, CI et plateforme Composer) | 3 | 9 | 8 h | ↪️ Reportée |
| [US-148](./US-148-tasks.md) | Plugins : limiteurs de débit typés `RateLimiterFactoryInterface` (Symfony 8) | 3 | 12 | 9 h | 👀 |
| [US-160](./US-160-tasks.md) | Dettes du sprint 17 : verrou atomique, GET sans écriture de session, menu compte nommé | 5 | 8 | 10 h | 👀 |
| [US-161](./US-161-tasks.md) | Actions de la rétrospective du sprint 17 | 5 | 13 | 11 h | 🔄 |
| [US-159](./US-159-tasks.md) | Contenu LinkedIn réaligné sur la recherche d'emploi : calendrier et posts de suite | 3 | 11 | 9 h | 👀 |
| [US-124](./US-124-tasks.md) | Posts LinkedIn rédigés, lot 2 : semaines 3 à 8 | 5 | 11 | 10,75 h | 👀 |
| [US-111](./US-111-tasks.md) | Bloc de preuve technique : qualité, compatibilité, versions | 3 | 8 | 7,5 h | 👀 |
| [US-103](./US-103-tasks.md) | Assistants IA : page utile avec transcript d'une vraie conversation | 3 | 9 | 7,25 h | 🔄 |
| [US-068](./US-068-tasks.md) | Liens vers la démo et le profil de l'auteur dans les 24 README | 3 | 8 | 6,5 h | 👀 |
| [Transverse](./technical-tasks.md) | Clôture et bilan de l'orchestrateur (sans points) | 0 | 2 | 1,5 h | 🔄 |

**Total décomposé** : 101 tâches | **92,5 h** pour 36 points (+ 2 tâches transverses sans points), dont 8,5 h déjà réalisées (✅ : code d'US-158 et fiche d'US-103), 6,5 h d'actions de Thibaut (14 tâches), soit **77,5 h de charge restante pour Claude** (orchestrateur compris).

> **Clôture du 2026-10-21** (PR #43 fusionnée, 9a4c461) : 64 tâches ✅, 5 🔄, 32 🔲. 👀 = livré sur `main`, vérifications ou validations de Thibaut restantes. US-147 sort du sprint : le correctif de sécurité PHP 8.5 n'était pas publié ; elle sera livrée dans une PR séparée dès que l'image FrankenPHP corrigée sort (sinon au sprint 19). Détail : [../sprint-review.md](../sprint-review.md).

## Capacité

Capacité retenue : 36 points × 2,1 h ≈ **76 h** pour les stories (ratio du sprint 17 : 80 h pour 38 points) + 1,5 h de tâches transverses, soit **77,5 h**. La charge restante de Claude est de **77,5 h** : une charge **égale** à la capacité, sans marge. Les 8,5 h déjà réalisées ne sont pas comptées (code d'US-158 écrit avant le planning). Tout retard s'absorbe par l'ordre de sacrifice au point d'étape du 2027-02-26 ; les Must (22 points) ne se sacrifient pas.

Pistes de travail, une par thème pour éviter les fichiers communs : **outillage** (US-161 puis US-160, `bin/run-tests`, `bin/clean-test-caches`, gabarits), **démo** (US-158 puis US-160 et US-111, thème et traductions ; US-103 ensuite), **plateforme** (US-147 puis US-148, `composer.lock`, plugins), **rédaction** (US-159, US-124, hors dépôt public), **README** (US-068, 24 dépôts). Un seul worker à la fois sur chaque fichier commun.

### Ordre de sacrifice (si les heures dépassent la capacité)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans 77,5 h. **Non sacrifiables** : US-158, US-147, US-148, US-160, US-161, US-159, la tranche 1 d'US-124, les tests d'accessibilité et de rendu réel, les revues. Toute tâche sacrifiée est consignée dans la revue comme dette datée.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge de Claude décomposée) | n/a | 77,5 h | n/a |
| 1 | US-068 entière (24 PR de README), T-068-01 à T-068-08 sauf le GO de Thibaut | 6,25 h | 71,25 h | Les 24 README gardent leurs liens actuels ; reprise au sprint 19 (URL du profil déjà fournie) |
| 2 | US-103 entière (assistants IA), reste après la fiche | 5,75 h | 65,5 h | La page Assistants IA reste en l'état ; US-121 (sprint 19) n'est pas bloquée, le transcript est repris plus tard |
| 3 | US-111 entière (bloc de preuve technique) | 7,5 h | 58 h | Pas de tableau de qualité sur la démo ; la preuve passe par les README et la CI publique |
| 4 | Seconde tranche d'US-124 (semaines 5 à 8, T-124-06 à T-124-09), semaines 7 et 8 d'abord | 5,5 h | 52,5 h | Posts des semaines 5 à 8 non rédigés ; repris au sprint 19 avec les retours des semaines 1 à 4 |

Si la charge dépasse encore 77,5 h après le sacrifice n° 4, la décision revient à Thibaut. US-147 passe en tête si la veille programmée ne l'a pas livrée (faille critique de PHP).

## Chemin critique et attentes

- **Échéance du 2026-10-26 (en premier)** : T-159-01 → T-159-03 → T-159-04 (relecture) → T-159-05 (publication du post 1), profil v2 appliqué avant (US-129). Elle précède le début calendaire du sprint (2027-02-22) : démarrage effectif dès la décision de Thibaut.
- **Faille critique de PHP (2026-10-21)** : T-147-01 (veille) → T-147-03 → T-147-05 (image, orchestrateur) → T-147-08 (redéploiement par Thibaut). La veille programmée de l'orchestrateur réalise T-147-01 à T-147-04 dès que l'image FrankenPHP 8.5 corrigée est publiée ; la story est alors livrée avant le sprint.
- **Outillage d'abord** : T-160-01 → T-160-02 (verrou atomique) → T-161-02 (`check-disk`) → T-161-08 (`--wait`) ; T-161-03 → T-161-04 (nettoyage par worker) → T-161-05 (gabarit). Ces tâches passent avant les autres stories de code, car elles conditionnent la fiabilité des tests.
- **Piste démo** : T-158-05 → T-158-06 et T-158-07 → T-158-08 (revue, libère les fichiers partagés) → T-160-03 à T-160-07 et T-111-03 ; T-158-09 (URL du profil, Thibaut) → T-068-02.
- **Piste plateforme** : T-147-03 (`composer.lock`) → T-148-11 (montée des plugins) ; T-148-01 à T-148-05 en parallèle (un dépôt par worker) → T-148-06 → T-148-08 (GO) → T-148-10 (tags).
- **Rédaction** : T-159 → T-124 (tranche 1 puis tranche 2 après les chiffres des semaines 1 et 2).
- **Clôture** : T-TECH-01 (image) avant la revue de sprint, puis T-TECH-02 (bilan des cinq indicateurs pour la rétrospective).

## Actions de Thibaut (tâches qui ne sont pas réalisables par Claude seul)

| ID | Action | Durée |
|----|--------|-------|
| T-158-09 | Thibaut fournit l'URL de son profil LinkedIn (`DEMO_PROFILE_URL`, https) par message ; elle ne va pas dans le dépôt | 0,25 h |
| T-158-10 | Thibaut valide les libellés exacts et pose `DEMO_PROFILE_URL` sur Railway après le merge (commande Railway par Thibaut) | 0,25 h |
| T-147-08 | Thibaut redéploie sur Railway après le merge et vérifie `/healthz` et la version de PHP | 0,5 h |
| T-148-08 | Thibaut donne le GO unique : push des 5 branches, 5 PR, merge quand les CI sont vertes, tag des 5 versions patch | 0,25 h |
| T-161-12 | Thibaut valide le format du registre | 0,5 h |
| T-159-04 | Thibaut relit et ajuste le post 1 | 0,5 h |
| T-159-05 | Thibaut publie le post 1 avant le 2026-10-26 et épingle le commentaire | 0,5 h |
| T-159-10 | Thibaut relit le calendrier et les posts 2 et 3 | 0,5 h |
| T-159-11 | Thibaut publie les posts 2 et 3 aux dates du calendrier | 0,5 h |
| T-124-04 | Thibaut relit la tranche 1 | 0,5 h |
| T-124-05 | Thibaut relève les chiffres des semaines 1 et 2 (vues du profil, messages reçus) | 0,5 h |
| T-124-10 | Thibaut relit la tranche 2 | 0,5 h |
| T-103-03 | Thibaut réalise la conversation réelle avec son assistant sur le MCP de production, enregistre le transcript brut et un GIF ou une vidéo courte | 1 h |
| T-068-05 | Thibaut donne le GO unique : push des 24 branches, 24 PR, merge quand les CI sont vertes | 0,25 h |

Notes :
- **URL du profil LinkedIn** (T-158-09) : entrée requise du sprint, nécessaire à `DEMO_PROFILE_URL`, aux 24 README (US-068) et aux publications ; elle n'entre jamais dans le dépôt.
- **Publications** (T-159-05, T-159-11) : Claude ne publie rien sur LinkedIn ; le post 1 avant le **2026-10-26**.
- **Relectures** : post 1 (T-159-04), calendrier et posts 2 et 3 (T-159-10), tranches d'US-124 (T-124-04, T-124-10), format du registre (T-161-12).
- **Railway** : `DEMO_PROFILE_URL` (T-158-10) et redéploiement PHP 8.5 (T-147-08) ; aucune commande Railway par Claude.
- **GO** : un GO unique pour les 5 plugins (T-148-08) et un GO unique pour les 24 README (T-068-05) ; commit, push, PR, merge et tag de la démo (dont le code d'US-158 non commité) sur demande explicite.
- **Conversation réelle avec un assistant** (T-103-03), seulement si US-103 n'est pas sacrifiée.

## Prérequis d'environnement par story (action 5 de la rétro S17)

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-158 | Valeur de test factice pour `DEMO_PROFILE_URL` ; URL réelle de Thibaut (T-158-09) pour Railway seulement | T-158-06 |
| US-147 | Image FrankenPHP 8.5 corrigée publiée (digest) ; disque suffisant pour un build ; Docker ; `composer` en PHP 8.5 en local | T-147-01, T-147-05 |
| US-148 | Cinq dépôts à jour (`git fetch`, branche vérifiée) ; Docker des plugins ; GO pour push, PR, tags ; Packagist qui répond | T-148-01, T-148-10 |
| US-160 | Base de test par worker ; pa11y et `bin/measure-mobile` disponibles | T-160-07 |
| US-161 | Docker (conteneur MySQL du projet) pour tester le nettoyage des bases | T-161-03 |
| US-159, US-124 | Charte v2 et profil v2 de `communication/sprint-17/` ; `charter_check.py` ; relecture de Thibaut | T-159-01 |
| US-111 | Accès réseau à Packagist et à l'API GitHub (jeton éventuel dans l'environnement, jamais dans un fichier) | T-111-01 |
| US-103 | **MCP de production joignable en HTTPS**, compte OAuth de démonstration, assistant de Thibaut | T-103-03 |
| US-068 | URL du profil ; les 24 dépôts clonés sous `/Users/tmonier/Projects/sylius_plugins/` ; GO unique | T-068-04, T-068-05 |

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 100 mots au total).** Le Scrum Master refuse de clore une tâche sans lui, et le refuse tant que les preuves rejouables manquent (action 3 de la rétro S17).

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

### Règles d'exécution (leçons de la rétro du sprint 17)

1. **Disque** : `df -h .` au démarrage de chaque worker et **avant chaque lot de tests** ; l'orchestrateur le contrôle avant de lancer des workers. Sous 15 Go libres : prévenir Thibaut sans attendre (alerte de `bin/check-disk` dès T-161-02) ; l'espace manquant se diagnostique d'abord (projet : caches, bases, volumes Docker ; machine : hors projet) avant tout nettoyage. Un seul build d'image à la fois. Le seuil `MIN_FREE_GB` de `bin/run-tests` (5 Go) n'est jamais abaissé.
2. **Isolation par worker** : un sous-dossier de scratchpad par worker (`<scratchpad>/<nom-du-worker>/`, jamais de fichier de travail à la racine du scratchpad), une base de test (`TEST_DB_NAME=b2b_<worker>`) et un cache (`TEST_CACHE_DIR=var/cache-<worker>`) par worker. **`bin/clean-test-caches` est interdit tant qu'un autre worker a une base active** ; avant T-161-04 il est réservé à l'orchestrateur, hors lots ; après, un worker ne nettoie que ses propres caches et bases (`WORKER_ID`), et `--all` est réservé à l'orchestrateur. La règle « nettoyer avant chaque lot » vise donc le worker lui-même, jamais les ressources des autres.
3. **Vérification croisée à l'intégration** : le rapport cite la commande et la dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite. **L'orchestrateur rejoue ECS (`src tests`) et PHPStan sur l'arbre intégré avant de déclarer « vert »**, et rejoue seule toute suite signalée en échec avant d'ouvrir une enquête. Un « vert » sans preuve est refusé.
4. **Attente bornée** : un worker qui attend un run lit le verrou de `bin/run-tests` (`var/run-tests.lock`), jamais `pgrep`, et **sort après 30 minutes** avec un rapport (`--wait` dès T-161-08). L'orchestrateur arrête explicitement (TaskStop) une tâche de fond, vérifie l'arrêt, puis seulement relance.
5. **Prérequis d'environnement** : toute story de captation ou d'intégration externe liste ses prérequis (tableau ci-dessus) avant d'être engagée.

**Rappels** : seul `bin/run-tests` est autorisé (jamais `phpunit` en direct) ; second run refusé par le verrou (code 4) ; comptes de démo partagés isolés (trait `IsolatesSharedAccounts`, [docs/tests-locaux.md](../../../../docs/tests-locaux.md)) ; échec sur un test partagé : mettre la branche à jour et relancer ce test avant de le signaler.

**Règles propres au sprint** : tests via `TEST_DB_NAME=<db> bin/run-tests …`, ECS sur `src tests`, PHPStan ; mobile mesuré avec `bin/measure-mobile`, pa11y WCAG2AAA ; libellés FR et EN ; seul l'orchestrateur reconstruit l'image Docker (et la valide avant la clôture d'une revue) ; `git add` par chemins explicites ; **le code d'US-158 non commité n'est modifié que par ses tâches** ; dépôts de plugins (US-148, US-068) : `git fetch` puis branche courante vérifiée avant tout commit ; aucun push, PR, merge ni tag sans GO ; branche principale protégée (PR et contrôles « Quality and smoke tests » et « Docker image », attendre la fin du run CI avant le merge) ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets ; aucun détail exploitable sur une faille dans un dépôt public ; livrables rédactionnels dans `/Users/tmonier/Projects/sylius_plugins/communication/sprint-18/` (hors dépôt public, référencés dans `communication/INDEX.md`), aucun nom ni contenu de conversation de prospect ou de recruteur dans le dépôt.
