# Tâches : Sprint 021 (parc d'équipements)

> Sprint Planning Part 2 (décomposition préparée le 2026-10-22, après le planning du 2026-10-22 qui a validé le périmètre B de 25 points). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-21`). Sprint 20 : [../../sprint-20-demo-enrichie/tasks/README.md](../../sprint-20-demo-enrichie/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md). Tâches transverses : [technical-tasks.md](./technical-tasks.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-056](./US-056-tasks.md) | Plugin équipements : modèles d'équipement liés aux variantes compatibles (admin) | 5 | 12 | 14,75 h | 👀 |
| [US-057](./US-057-tasks.md) | Plugin équipements : parc du client dans l'espace client (CRUD) | 5 | 11 | 14,25 h | 👀 |
| [US-058](./US-058-tasks.md) | Plugin équipements : filtre « pièces compatibles » au catalogue | 5 | 11 | 12,5 h | 👀 |
| [US-157](./US-157-tasks.md) | Purchase-approval : aperçu des approbations en attente depuis l'en-tête | 2 | 15 | 11 h | 👀 |
| [US-098](./US-098-tasks.md) | Jauge de remplissage condensée sur la fiche produit | 2 | 6 | 4 h | ↪️ sacrifiée (sprint 22) |
| [US-088](./US-088-tasks.md) | Journal d'audit lisible : reset masqué et événements métier | 3 | 7 | 7,25 h | ↪️ sacrifiée (sprint 22) |
| [US-154](./US-154-tasks.md) | Company-insights : conversion des devis et économies des prix dégressifs | 3 | 16 | 13,25 h | ↪️ sacrifiée (sprint 22) |
| [Transverse](./technical-tasks.md) | Dérogation disque, gabarits, PHP 8.5 de purchase-approval, reprises du sprint 20, vérification en production, clôture de l'orchestrateur (sans points) | 0 | 12 | 9,75 h | 🔲 (T-TECH-07 et 08 🚫) |

**Total décomposé** : 90 tâches | **86,75 h** pour 25 points. **Engagé après les sacrifices du 2026-10-22 (US-154, US-088, US-098 : 29 tâches, 24,5 h, hors total)** : 61 tâches | **62,25 h** pour 17 points, dont 12 tâches transverses sans points, dont 1,25 h d'actions de Thibaut (4 tâches), soit **61 h de charge pour Claude** (orchestrateur compris), dont 2 h bloquées (T-TECH-07) : 59 h exécutables. Hors total : 5 tâches conditionnelles de publication de la 0.1.0 (T-058-12 à T-058-16, 2,5 h, voir [US-058-tasks.md](./US-058-tasks.md)). Deux tâches sont 🚫 au départ (T-TECH-07 et T-TECH-08, Railway suspendu).

> Les estimations des fiches (« ≈ 4 h », « ≈ 5 h », « ≈ 6 h ») sont dépassées pour les stories de plugin : chacune porte des tâches de dépôt de plugin (branche, CI, revue de sécurité, README, captures) et, pour US-157 et US-154, une release complète et la montée de la démo. Le MMF du plugin neuf porte en plus une migration, un voter et une extension de requête : **41,5 h pour 15 points**, soit 2,8 h par point contre 2,1 h dans la méthode de capacité.

## Répartition par type (périmètre engagé, après sacrifices)

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [DB] | 2 | 4,5 h | 7 % |
| [BE] | 5 | 10 h | 16 % |
| [FE-WEB] | 4 | 8,75 h | 14 % |
| [TEST] | 10 | 15,75 h | 25 % |
| [DOC] | 13 | 8 h | 13 % |
| [REV] | 4 | 2,25 h | 4 % |
| [OPS] | 23 | 13 h | 21 % |
| **Total** | **61** | **62,25 h** | 100 % |

## Capacité

Méthode des sprints précédents : 25 points × 2,1 h ≈ **52,5 h** pour les stories + 8 h de tâches transverses de Claude et de l'orchestrateur, soit **60,5 h**. La charge de Claude décomposée était de **85 h** : un **dépassement de 24,5 h** sur la capacité, résorbé par trois sacrifices (ci-dessous) : **61 h** restantes.

**Sacrifices 1 à 3 appliqués le 2026-10-22** (ordre validé par Thibaut : US-154, US-088, US-098), comme US-093 au sprint 20 ; statut backlog, reportées au sprint 22, fiches de tâches conservées avec une bannière. Le sacrifice 4 (US-157) reste en réserve ; plancher = le MMF de 15 points :

| # | Sacrifice | Heures Claude | Charge restante | Conséquence |
|---|--------------------------|---------------|-----------------|-------------|
| 0 | (charge décomposée, 25 points) | n/a | 85 h | Dépassement de 24,5 h |
| 1 | **Appliqué** : US-154 entière (devis et économies, company-insights 0.3.0) | 12,75 h | 72,25 h | Pas de widgets « Devis » et « Économies » ; reprise sprint 22+ ; 22 points |
| 2 | **Appliqué** : US-088 entière (journal d'audit lisible) | 7,25 h | 65 h | Journal d'audit inchangé (plus de 1 000 lignes de reset) ; 19 points |
| 3 | **Appliqué** : US-098 entière (jauge condensée) | 4 h | 61 h | Jauge à 9 lignes inchangée ; 17 points |
| 4 | (réserve) US-157 entière (aperçu des approbations) | 10,5 h | 50,5 h | Pas de v1.3.0 de purchase-approval ; T-TECH-03 et T-TECH-04 (actions de la rétro) restent et passent en autonomes ; 15 points = le MMF seul |

Lecture : après les sacrifices 1 à 3 (appliqués) il reste **61 h**, soit **0,5 h au-dessus de 60,5 h (dépassement résiduel)** ; mais T-TECH-07 (2 h) est 🚫 tant que Railway est suspendu et n'est pas dépensée : la charge réellement exécutable est alors de **59 h**, sous la capacité. Le sacrifice 4 (US-157) n'est donc nécessaire que si un retard sur le MMF s'ajoute. **Non sacrifiables** : le MMF (US-056, US-057, US-058 ensemble), les actions de la rétro S20 (T-TECH), les tests d'accessibilité et de rendu réel, les revues, la dérogation disque. Toute tâche sacrifiée est consignée dans la revue comme dette datée.

Pistes de travail, **un seul worker à la fois sur tout le sprint** : **plugin parc d'équipements** (US-056, US-057, US-058 dans cet ordre, T-TECH-06 avec US-058), **purchase-approval** (US-157 avec T-TECH-03 et T-TECH-04), **démo** (T-TECH-05 ; US-098 et US-088 sacrifiées), **outillage** (T-TECH-01, 02, 09, 10), **production** (T-TECH-07 et T-TECH-08 à la reprise de Railway). `composer.lock` de la démo à écrivain unique : T-157-12 avant T-154-14.

## Chemin critique et attentes

- **Début de sprint** : T-TECH-01 (dérogation disque, relevé), puis les cadrages (T-056-01, T-157-01). T-TECH-05 (captures d'accueil) et toutes les captures de plugin **avant le jour 5** (T-057-07, T-058-06, T-TECH-04).
- **MMF du plugin** : T-056-01 → T-056-02 → T-056-03 → T-056-04 → T-056-05 → T-056-06 → T-056-07 → T-056-08 → **T-056-10 (GO de Thibaut)** → T-056-11 (merge) → T-056-12 → T-057-01 → … → T-057-10 → T-057-11 → T-058-01 → … → T-058-09 → T-058-10 → T-058-11. Trois PR fusionnées l'une après l'autre dans `main` du plugin, branche repartie de `origin/main` à chaque fois.
- **Purchase-approval v1.3.0** : T-157-01 → T-TECH-03 → T-157-02 → … → T-157-06 → **T-157-08 (GO)** → T-157-09 → **T-157-10 (Packagist, Thibaut)** → T-157-11 → T-157-12 → T-157-13 → T-157-14 → T-157-15.
- **Démo** : T-TECH-11 (image) après T-157-13 (US-098, US-088 et US-154 sacrifiées).
- **Company-insights 0.3.0** : US-154 sacrifiée (sprint 22), plus de chemin dans ce sprint.
- **Production** : T-TECH-08 (déploiement de Thibaut) → T-TECH-07 (vérification) ; calendaire, bloqué.
- **Clôture** : T-TECH-11 (image) avant la revue de sprint, puis T-TECH-12 (bilan pour la rétro du 2027-04-16).

## Actions de Thibaut (tâches qui ne sont pas réalisables par Claude seul)

| ID | Action | Durée |
|----|--------|-------|
| T-056-10 | GO de push, PR et merge des PR du plugin parc d'équipements (US-056 à US-058), sans tag | 0,25 h |
| T-157-08 | GO du tag et de la release `v1.3.0` de purchase-approval | 0,25 h |
| T-157-10 | Confirme l'indexation de la v1.3.0 sur Packagist | 0,25 h |
| T-TECH-08 | Déploiement groupé des PR en attente sur Railway à la reprise (bloquée) | 0,5 h |
| T-058-12, T-058-14 (conditionnelles) | GO du tag `v0.1.0`, soumission du **nouveau paquet** à Packagist | 0,75 h |

Notes :
- **Packagist** : purchase-approval et company-insights existent déjà (Thibaut confirme l'indexation) ; le plugin parc d'équipements est un **nouveau paquet** soumis par Thibaut seul, première version 0.1.0, après son GO (tâches conditionnelles, sprint 22 par défaut).
- **GO** : un GO par release. Push, PR, merge et tag seulement après ce GO ; commit, push, PR, merge et tag de la démo sur demande explicite.
- **Ordre de publication** : tag, Packagist, vérification de la version publiée, puis bump du `composer.lock` de la démo ; la PR de la démo ne s'ouvre qu'après la vérification.
- **Railway** : suspendu ; aucune commande Railway par Claude.
- **Politique** : aucune comparaison à Sylius Plus dans les textes publiés (promotion du plugin parc d'équipements levée le 2026-10-22) ; aucun détail de sécurité dans les dépôts publics (revues rapportées hors dépôt).

## Prérequis d'environnement par story (action 5 de la rétro S17)

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-056, US-057, US-058 | Dépôt `sylius-customer-asset-management-plugin` : `git fetch`, branche fusionnée `docs/readme-demo-profile` quittée, branche créée depuis `origin/main` ; matrice CI et `composer.json` relus ; Docker des plugins ; disque (dérogation) | T-056-01, T-057-01, T-058-01, T-TECH-01 |
| US-157 | Dépôt `sylius-purchase-approval-plugin` à jour sur `main` (v1.2.0) ; matrice CI relue ; gabarit de CI de T-TECH-03 | T-157-01, T-TECH-03 |
| Transverse | Docker (conteneur MySQL du projet) pour `bin/check-disk` ; Railway repris pour T-TECH-07 | T-TECH-01, T-TECH-08 |

## Consigne de disque des workers (dérogation encadrée, décision de Thibaut du 2026-10-22)

Écart assumé à l'action 1 de la rétro S20 (« pas de worker de plugin sous 10 Go libres ») : 5,6 Go libres au planning. Règles, à coller dans **chaque** consigne de worker :

1. **Un seul worker à la fois**, un seul build d'image à la fois.
2. **Budget ≤ 3 Go par worker** (`vendor`, caches, image Docker, base de test).
3. **Nettoyage de ses propres ressources en fin de tâche** (`vendor`, caches, image, base du worker ; jamais celles d'un autre) ; relevé `df -h .` avant et après dans le rapport. Tâches « Fin de worker » dans chaque story.
4. **Arrêt sous 4 Go libres** : le worker s'arrête, le signale dans son rapport et n'efface rien au-delà de ses propres ressources ; l'orchestrateur prévient Thibaut sans arrêter le sprint.
5. Le seuil `MIN_FREE_GB` de `bin/run-tests` (5 Go) n'est jamais abaissé : avec 5,6 Go libres, la marge pour la démo (T-157-13) est de 0,6 Go. Ces stories se placent après un nettoyage et un relevé, ou attendent une libération d'espace.

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
- **Nettoyage** : vendor, caches, image et base du worker supprimés, `df -h .` avant et après en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

### Règles d'exécution (leçons des rétros des sprints 17 à 20)

1. **Disque** : voir la consigne ci-dessus (dérogation encadrée). `df -h .` et `bin/check-disk` au démarrage de chaque worker et avant chaque lot de tests ; l'orchestrateur contrôle avant de lancer un worker. L'espace manquant se diagnostique d'abord (projet, VM Docker ou machine) avant tout nettoyage. Les captures sont faites tôt (avant le jour 5).
2. **Isolation par worker** : un sous-dossier de scratchpad, une base (`TEST_DB_NAME=b2b_<worker>`) et un cache (`TEST_CACHE_DIR=var/cache-<worker>`) par worker ; `--all` réservé à l'orchestrateur hors lots.
3. **Vérification croisée** : le rapport cite la commande et la dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite. **L'orchestrateur rejoue ECS (`src tests`) et PHPStan sur l'arbre exact avant chaque push** (T-TECH-09). Le gate est recoupé avec la somme des points du fichier de statut (T-TECH-02) ; **un refus d'outil n'arrête jamais le sprint** : il est signalé à Thibaut et le travail continue.
4. **Attente bornée** : `bin/run-tests --wait`, sortie après 30 minutes avec un rapport ; une tâche de fond est arrêtée explicitement.
5. **Cadrage d'un plugin** : lire la matrice CI (`.github/workflows/build.yaml`) et le `composer.json` avant d'écrire la fiche de tâches ; consigner les versions supportées et l'impact de version (fait pour les trois dépôts le 2026-10-22). **Dépôts de plugins : `git fetch`, branche courante vérifiée, branche de travail créée depuis `origin/main` avant tout commit** (le plugin parc d'équipements est sur une branche fusionnée).
6. **Secrets fictifs** : jamais de faux jeton au format réel écrit en clair dans un test ; le recomposer à l'exécution. Plaques et numéros de série de test : valeurs fictives, jamais journalisées.
7. **Ordre de publication** : tag du plugin, Packagist, vérification de la version publiée, puis seulement le bump du `composer.lock` de la démo et sa PR.
8. **Statuts** : la ligne de chaque tâche faite dans un autre dépôt est mise à jour dans son fichier de tâches (action 2 de la rétro S20).

**Rappels** : seul `bin/run-tests` est autorisé dans la démo (jamais `phpunit` en direct) ; dans les plugins, tests dans Docker ; second run refusé par le verrou (code 4) ; comptes de démo partagés isolés (trait `IsolatesSharedAccounts`, [docs/tests-locaux.md](../../../../docs/tests-locaux.md)).

**Règles propres au sprint** : ECS sur `src tests` et PHPStan max ; mobile mesuré avec `bin/measure-mobile`, pa11y WCAG2AAA ; libellés FR et EN ; seul l'orchestrateur reconstruit l'image Docker ; `git add` par chemins explicites ; aucun push, PR, merge ni tag sans GO ; branche principale de la démo protégée (PR et contrôles « Quality and smoke tests » et « Docker image », attendre la fin du run CI avant le merge) ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets ; aucun détail exploitable sur une faille dans un dépôt public ; aucune comparaison avec Sylius Plus dans les textes publiés ; revues de sécurité rapportées hors dépôt public.

## Conventions

- **ID** : T-[US]-[Numéro] (ex : T-056-05) ; transverses T-TECH-NN.
- **Taille** : 0,25 h à 3 h (aucune tâche au-delà de 8 h).
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 Conditionnelle (action de Thibaut ou GO).
