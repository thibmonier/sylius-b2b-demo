# Tâches : Sprint 022 (finalisation)

> Sprint Planning Part 2 (décomposition faite au lancement du sprint, le 2026-10-23, branche `feature/sprint-22-finalisation`). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-22`). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md). Tâches transverses : [technical-tasks.md](./technical-tasks.md). Sprint précédent : [../../sprint-21-parc-equipements/tasks/README.md](../../sprint-21-parc-equipements/tasks/README.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-163](../../../backlog/user-stories/US-163.md) | Clôture des deux points hauts de l'audit et checklist US-079 | 2 | 0 | 0 h | sans objet | ✅ faite le 2026-10-23 (PR #57) |
| [US-164](./US-164-tasks.md) | Bornes du parc d'équipements des comptes partagés et limitation de débit des routes récentes | 3 | 8 | 8 h | non sacrifiable | 🔲 |
| [US-162](./US-162-tasks.md) | Anomalies mineures relevées en production | 1 | 5 | 3,25 h | non sacrifiable | 🔲 |
| [US-161](./US-161-tasks.md) | Solde de la rétro du sprint 17 | 2 | 4 | 2,75 h | non sacrifiable | 🔄 |
| [US-084](./US-084-tasks.md) | Qualité : Rector en CI, seuil de couverture, `bin/proof-refresh` | 5 | 10 | 11,75 h | non sacrifiable | 🔲 |
| [US-165](./US-165-tasks.md) | Dettes qualité de l'audit | 3 | 10 | 8,25 h | n°5 | 🔲 |
| [US-167](./US-167-tasks.md) | Performance à faible effort | 2 | 7 | 5 h | non sacrifiable | 🔲 |
| [US-088](./US-088-tasks.md) | Journal d'audit lisible : reset masqué et événements métier | 3 | 7 | 6,5 h | n°4 | 🔲 |
| [US-098](./US-098-tasks.md) | Jauge de remplissage condensée sur la fiche produit | 2 | 6 | 4 h | n°3 | 🔲 |
| [US-154](./US-154-tasks.md) | Company-insights : conversion des devis et économies | 3 | 16 | 13,25 h | n°2 | 🔲 |
| [US-062](./US-062-tasks.md) | Mineurs du script d'images | 1 | 4 | 2,75 h | n°1 | 🔲 |
| [Transverse](./technical-tasks.md) | Clôture des reviews (T-TECH-22-01) et gestion du sprint (sans points) | 0 | 13 | 11,5 h | non sacrifiable | 🔲 |

**Total décomposé : 27 points, 90 tâches, 77 h** (stories 65,5 h, transverses 11,5 h), dont 2,5 h d'actions de Thibaut (GO et déploiement, relectures, validation du registre d'US-161, GO et confirmation Packagist d'US-154) : **74,5 h pour Claude** (orchestrateur compris, 4,5 h). Les 2 points d'US-163 sont déjà livrés : les 25 points restants portent la charge.

## Capacité

Méthode des sprints précédents : 2,1 h par point restant + 8 h de tâches transverses = 25 × 2,1 + 8 = **60,5 h**. Charge décomposée pour Claude : 74,5 h, soit **14 h au-dessus**. Les sacrifices ordonnés absorbent l'écart (les tâches des stories sacrifiées restent décrites, elles ne sont simplement pas engagées).

| # | Sacrifice | Heures Claude | Charge restante | Conséquence |
|---|-----------|---------------|-----------------|-------------|
| 0 | (charge décomposée) | n/a | 74,5 h | Dépassement de 14 h |
| 1 | US-062 entière (script d'images) | 2,75 h | 71,75 h | Mineurs du script d'images restent au backlog ; 26 points |
| 2 | US-154 entière (devis et économies, company-insights 0.3.0) | 12,75 h | 59 h | Pas de release 0.3.0 ni de montée du lock pour company-insights ; 23 points |
| 3 | US-098 entière (jauge condensée) | 4 h | 55 h | Jauge à 9 lignes inchangée ; 21 points |
| 4 | US-088 entière (journal d'audit lisible) | 6,5 h | 48,5 h | Événements métier manquants non rejoués ; 18 points |
| 5 | US-165 entière (dettes qualité) | 8,25 h | 40,25 h | Trois dettes d'architecture restent ouvertes ; 15 points |

Lecture : **après les sacrifices 1 et 2, la charge (59 h) passe sous la capacité (60,5 h)**. Point d'étape le **2026-10-30** : décider alors des sacrifices selon l'avancement. Les Must (US-164, US-162, US-161, clôture des reviews), US-084 et US-167 ne se sacrifient pas : plancher de **40,25 h**. La vérification en production (T-TECH-22-01) dépend d'actions de Thibaut (GO, déploiement, relectures) : elle peut glisser sans bloquer le reste.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [TEST] | 25 | 26 h |
| [BE] | 15 | 20,75 h |
| [DOC] | 19 | 11,25 h |
| [OPS] | 16 | 9,5 h |
| [REV] | 11 | 5,25 h |
| [FE-WEB] | 4 | 4,25 h |
| [DB] / [FE-MOB] | 0 | 0 h |
| **Total** | **90** | **77 h** |

## Fichiers principaux et chevauchements entre stories

Matrice destinée à décider quelles stories peuvent être menées **en parallèle** (une branche et un développeur par story) sans conflit de fusion. `●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-164 | US-162 | US-084 | US-165 | US-167 | US-088 | US-098 | US-154 | US-062 |
|-----------------|:------:|:------:|:------:|:------:|:------:|:------:|:------:|:------:|:------:|
| `docker/Caddyfile` | | ● | | | ● | | | | |
| `src/Demo/Http/SecurityHeadersSubscriber.php` | | ● | | | | | | | |
| `src/Demo/Http/RateLimitSubscriber.php`, `config/packages/demo_hardening.yaml` | ● | | ○ | | | | | | |
| `docs/durcissement-demo.md` | ● | ● | | | | | | | |
| `.github/workflows/ci.yaml`, `rector.php`, `phpunit.xml.dist` | | | ● | | | | | | |
| `src/` et `tests/` en masse (application de Rector) | ● | ● | ● | ● | ● | ● | | | ● |
| `config/demo/proof.json`, `bin/proof-refresh`, `src/Demo/Proof/Refresh/` | | | ● | | | | | ○ | |
| `src/Controller/CompanyWishlistController.php`, `src/Wishlist/` | | | | ● | | | | | |
| `src/Demo/PunchOutSimulator/`, `PunchoutDemoFixture.php` | | | | ● | | | | | |
| `src/Demo/Vitrine/` (StandardBlock, applicateur, vérificateur) | | | | ● | | | | | |
| `templates/bundles/SyliusShopBundle/shared/components/main_image.html.twig` et `product/index.html.twig` | | | | | ● | | | | |
| `src/Demo/DataFixtures/AuditLogDemoFixture.php`, `tmonier_plugins_overrides.yaml` | | | | | | ● | | | |
| `templates/shop/container_fill/` | | | | | | | ● | | |
| `src/Demo/Images/Fetch/`, `bin/fetch-product-images` | | | | | | | | | ● |
| `composer.json`, `composer.lock` de la démo | | | | | | | | ● | |
| `config/demo/plugins.yaml`, `docs/vitrine-plugins.md` | | | | | | | | ● | |
| Dépôt `../sylius-company-insights-plugin` | | | | | | | | ● | |
| `docs/seo-et-vitesse.md` | | | | | ● | | | | |
| `docs/remise-a-zero-demo.md` | | | | | | ● | | | |
| `docs/tests-locaux.md` | | | ○ | | | | | | |

**Conflits à prévoir** :

1. **`docker/Caddyfile` : US-162 (HSTS) et US-167 (cache `/build/*`)** : deux blocs différents du même fichier ; à séquencer (US-162 puis US-167) ou à fusionner sans conflit si les blocs sont disjoints ; un seul développeur à la fois.
2. **Application de Rector (US-084)** : modifie potentiellement de nombreux fichiers de `src/` et `tests/` (78 à l'audit) et recouvre donc toutes les stories de code. À faire **en premier, par lots, mergé avant** les autres branches de code, ou **en dernier** après leurs merges ; jamais en parallèle d'une story qui touche les mêmes fichiers. L'étape CI Rector bloquante n'est ajoutée qu'une fois le dry-run à 0.
3. **`docs/durcissement-demo.md` : US-164 et US-162** : ajouts disjoints (une phrase chacun), conflit trivial.
4. **`composer.lock` de la démo** : écrivain unique (US-154 seulement dans le sprint ; les montées de purchase-approval et du parc d'équipements en attente du sprint 21 passent avant, T-TECH-22-02).
5. **`.bmad/sprint-status.yaml`** : écrivain unique, l'orchestrateur (jamais les workers).
6. Sans recoupement : US-098, US-062, US-088 et US-165 sont indépendantes entre elles et vis-à-vis d'US-164, US-167.

**Parallélisme possible** (disque limité : **un seul worker de plugin à la fois**, un worker de démo en plus au maximum) : voie A `US-164`, voie B `US-165` puis `US-088`, voie C `US-162` puis `US-167`, voie D (plugin company-insights) `US-154` en dernier, `US-084` seule avec Rector séquencé comme ci-dessus.

## Actions de Thibaut

| ID | Action | Durée |
|----|--------|-------|
| T-TECH-22-01b | GO et opérations en attente : merge des PR de plugins, tags, Packagist, déploiement groupé sur Railway | 0,5 h |
| T-TECH-22-01g | Relectures et validations (kit média, profil, calendrier, posts, captures) | 1 h |
| T-161-12 | Validation du format du registre `communication/INDEX.md` | 0,5 h |
| T-154-10 | GO du tag et de la release `v0.3.0` de company-insights (si US-154 est engagée) | 0,25 h |
| T-154-12 | Confirmation de l'indexation de la 0.3.0 sur Packagist (si US-154 est engagée) | 0,25 h |

Notes :
- **Aucun tag, release ni push sans GO de Thibaut** ; commit, push et PR de la démo sur demande explicite.
- **Railway** : aucune commande par Claude ; vérification en production en lecture seule seulement.
- **Politique** : aucune comparaison avec une offre commerciale concurrente dans les textes publiés ; aucun détail de sécurité dans les dépôts publics (US-164 en termes généraux).

## Consigne de disque et de tests des workers

1. `df -h .` et `bin/check-disk` avant chaque lot (6,5 Go libres au démarrage du sprint ; `bin/run-tests` refuse sous 5 Go).
2. Tests de la démo : **uniquement** `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`. **Jamais phpunit en direct.** Si l'espace est insuffisant, laisser la CI valider.
3. Un seul worker de plugin à la fois ; nettoyage de ses propres ressources (`vendor`, caches, image, base) en fin de tâche ; arrêt sous 4 Go libres.
4. ECS (`src tests`) et PHPStan max rejoués sur l'arbre exact avant chaque push (T-TECH-22-03).
5. Chaque worker place le « Rapport de fin » du gabarit `project-management/templates/consigne-worker.md` en tête de sa réponse finale.

## Prérequis d'environnement par story

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-164, US-162, US-084, US-165, US-167, US-088, US-098, US-062 | Démo à jour sur `feature/sprint-22-finalisation` ; disque (`bin/check-disk`) ; conteneur MySQL du projet pour `bin/run-tests` | T-TECH-22-04 |
| US-154 | Dépôt `../sylius-company-insights-plugin` : `git fetch`, branche `main` à jour (v0.2.1), branche créée depuis `origin/main` et vérifiée ; matrice CI relue ; Docker des plugins ; GO de Thibaut avant tout tag | T-154-01 |
| Clôture des reviews | Production déployée avec les PR en attente (action de Thibaut) ; Railway repris | T-TECH-22-01b |
| US-161 | Gabarits du dépôt (`project-management/templates/`) ; registre `communication/INDEX.md` hors dépôt | T-161-06 |
