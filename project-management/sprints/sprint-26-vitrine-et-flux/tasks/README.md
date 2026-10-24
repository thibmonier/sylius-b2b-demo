# Tâches : Sprint 026 (vitrine et flux)

> Sprint Planning Part 2 : décomposition faite au lancement du sprint, le 2026-10-24, sur la branche `feature/sprint-26-vitrine-et-flux`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-26`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 25](../../sprint-25-packs-et-transmission/sprint-retro.md), [revue du sprint 25](../../sprint-25-packs-et-transmission/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-186](./US-186-tasks.md) | Refonte du design de la home | 8 | 15 | 16,5 h | non sacrifiable | ✅ (T-186-14 partielle : LCP non remesuré ; T-186-15 à confirmer) |
| [US-181](./US-181-tasks.md) | Plugin b2b-mcp : `symfony/lock` et routes MCP en double | 1 | 6 | 2,5 h | non sacrifiable | ✅ |
| [US-182](./US-182-tasks.md) | Garde : `code` des tableaux qui se coupent | 1 | 5 | 2 h | non sacrifiable | ✅ (T-182-05 ouverte : `/demo/accounts` déborde à 360 px) |
| [US-183](./US-183-tasks.md) | PA fictive de démonstration (ADR 0016, story B) | 5 | 10 | 10,25 h | non sacrifiable | ✅ |
| [US-184](./US-184-tasks.md) | Statuts de transmission au back-office et relance (ADR 0016, story C) | 3 | 8 | 6 h | **n°2** | ✅ |
| [US-185](./US-185-tasks.md) | Contrat de fichier plat et export CSV des commandes (ADR 0017, story A) | 5 | 13 | 10,75 h | **n°1** | ✅ |
| [Transverse](./technical-tasks.md) | Ressources, contrôles avant push et avant done, preuve, GO, rétro (sans points) | 0 | 13 | 6,5 h | non sacrifiable | 9 ✅, 4 🔲 |
| **Total** | | **23** | **70** | **54,5 h** | | |

**Total décomposé : 23 points, 70 tâches, 54,5 h**, dont 1,5 h d'actions de Thibaut (5 tâches : validation de la maquette, trois GO de publication, une soumission à Packagist) : **53 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-24.** Refonte de la home (demande du jour), deux corrections héritées du sprint 25 (US-181, US-182), suite de l'ADR 0016 (PA fictive, statuts et relance au back-office) et début de l'ADR 0017 (story A, export CSV). Stories B à E de l'ADR 0017 et D, E de l'ADR 0016 : backlog.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 23 points | 23 × 2,1 + 8 = **56,3 h** | 53 h | **3,3 h** |

La marge (3,3 h) est supérieure à celle du sprint 25 (2,45 h) mais US-186 pèse 8 points : la maquette validée par Thibaut est un point d'attente (T-186-05) et la vérification en production vient après le merge. Si la charge dépasse la capacité, ordre de sacrifice décidé par Thibaut : **US-185, puis US-184**.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 53 h (23 points) | 56,3 h | Marge de 3,3 h |
| 1 | US-185 entière (export CSV, nouveau plugin) | 10,25 h | 42,75 h (18 points) | 45,8 h | Pas de nouveau plugin ; l'ADR 0017 garde la story A au backlog ; le worker w185 enchaîne US-181 et US-182 plus tôt |
| 2 | US-184 entière (statuts au back-office et relance) | 5,75 h | 37 h (15 points) | 39,5 h | Le journal reste consultable en base ; la relance repart au backlog ; pas de version 0.2.0 du plugin |

Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. Un blocage d'US-183 faute de paquet sur Packagist n'est **pas** un sacrifice : la story passe 🚫 et US-184 attend avec elle.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DOC] | 17 | 11 h |
| [TEST] | 15 | 12,75 h |
| [FE-WEB] | 8 | 10,75 h |
| [OPS] | 16 | 8,75 h |
| [BE] | 8 | 7,75 h |
| [REV] | 5 | 2,25 h |
| [DB] | 1 | 1,25 h |
| [FE-MOB] | 0 | 0 h |
| **Total** | **70** | **54,5 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-186 | US-181 | US-182 | US-183 | US-184 | US-185 |
|-----------------|:------:|:------:|:------:|:------:|:------:|:------:|
| `templates/shop/theme_b2b/`, `assets/shop/styles/b2b.scss`, `assets/shop/images/home/` | ● | | | | | |
| `config/packages/demo_theme_b2b.yaml`, `demo_cta.yaml` | ● | | | | | |
| `translations/` (un nouveau domaine par story) | ● (home) | | | ● (PA fictive) | ● (écran) | |
| `tests/` de la démo (nouveaux fichiers ; `HomeAuthorTest`, `PriceBreaksAndHomeSmokeTest`) | ● | | ● | ● | ● | |
| `config/demo/proof.json` | ● (rafraîchi) | | ● (rafraîchi) | ● (rafraîchi) | ● (rafraîchi) | |
| `templates/demo/*.html.twig` (tableaux et `code`) | | | ● | | | |
| `templates/demo/integrate.html.twig` (un lien vers la page de la PA fictive) | | | ○ ou ● (si non conforme) | ● | | |
| `composer.json`, `composer.lock`, `config/bundles.php`, `migrations/` | | | | ● | ● (`^0.2`) | |
| `config/demo/plugins.yaml`, `compatibility.json` | | | | ● | | |
| `src/Demo/Einvoice/`, `src/Demo/Controller/` (PA fictive), fixtures | | | | ● | ● | |
| `templates/admin/`, hooks de l'écran des transmissions | | | | | ● | |
| Dépôt `sylius-einvoice-transmission-plugin` | | | | ○ | ● (0.2.0) | |
| Dépôt `sylius-b2b-mcp-plugin` | | ● | | | | |
| Dépôt `sylius-pack-ai-headless` (gestes, README) | | ● (conditionnel) | | | | |
| Nouveau dépôt `sylius-erp-flatfile-plugin` | | | | | | ● |
| `sylius-webhook-outbox-plugin`, `sylius-facturx-plugin` | | | | ○ | | ○ |
| `docs/theme-b2b.md`, `docs/images/home-*`, README de la démo | ● | | | | | |
| `.bmad/sprint-status.yaml`, `docs/adr/`, `project-management/templates/` | | | | | | |

La dernière ligne n'est modifiée que par l'orchestrateur : les workers n'y touchent pas.

**Conflits à prévoir** :

1. **US-183 puis US-184** : mêmes fichiers (`composer.*`, `config/bundles.php`, `src/Demo/Einvoice/`, plugin de transmission). **Ordre imposé : US-184 démarre après le merge d'US-183** (le plugin 0.2.0 peut avancer en parallèle de la fin d'US-183, le branchement dans la démo non).
2. **`config/demo/proof.json`** : quatre stories de la démo le rafraîchissent (US-182, US-183, US-184, US-186). Après chaque rebase, `php bin/proof-refresh` puis `php bin/proof-check`, jamais de fusion à la main (T-TECH-26-10) ; merges de la démo l'un après l'autre.
3. **`templates/demo/integrate.html.twig`** : US-183 y ajoute un lien, US-182 peut le corriger. **US-182 démarre après le merge d'US-183**, ou rebase dessus.
4. **Compteur de plugins** : US-183 change `demo_plugin_count()`, lu par `HomeAuthorTest` (valeur dynamique) et par le hero que US-186 réécrit. Pas de conflit de fichier ; US-186 relit le compteur à chaque rebase.
5. **Hooks de la home** : seule US-186 modifie `demo_theme_b2b.yaml` et `demo_cta.yaml` ; la page de la PA fictive n'est pas liée depuis le menu de la home (lien depuis la page Intégrer seulement), ce qui évite un second écrivain.
6. **US-181 et US-185** sont indépendantes (dépôts distincts) ; leur seul point commun est la ressource lourde (vendor, Docker, MySQL).
7. **`docs/adr/` et `.bmad/sprint-status.yaml`** : un seul écrivain, l'orchestrateur.

## Répartition proposée en workers (max 3, sans conflit)

| Worker | Stories, dans l'ordre | Dépôts | Remarque |
|--------|-----------------------|--------|----------|
| **w186** | US-186 | démo | Seul sur les fichiers de la home ; commence par T-186-01 à T-186-03 (cadrage, mesure avant, photos) ; **s'arrête à T-186-05 jusqu'à l'accord de Thibaut sur la maquette** ; l'orchestrateur réalise la maquette (T-186-04) |
| **w183** | US-183, puis US-184 | démo, plugin de transmission | US-184 démarre après le merge d'US-183 ; attente du paquet sur Packagist (T-183-02) |
| **w185** | US-185, puis US-181, puis US-182 | nouveau plugin, `b2b-mcp`, packs, démo | Si US-185 est sacrifiée, US-181 et US-182 démarrent tout de suite ; US-182 après le merge d'US-183 |

Les tâches sans exécution lourde (cadrage, rédaction, photos, maquette, écriture des tests rouges) se recouvrent librement. Les **lots d'exécution** (`bin/run-tests`, installation d'un `vendor/`, Docker) sont annoncés à l'orchestrateur et passent **un à la fois** ; `bin/run-tests --wait` pour attendre le verrou. Les dépôts de plugins, eux, n'ont aucun conflit entre workers.

**Ordre de merge proposé** : plugins d'abord (b2b-mcp, plugin de transmission 0.2.0, nouveau plugin) sur GO, puis les PR de la démo une à une (US-182, US-183, US-184, US-186), chacune avec sa preuve rafraîchie, puis vérification en production après chaque déploiement automatique de `main` (US-182 et US-186 surtout).

## Règle 10 : toute tâche avec `git` dans l'arbre principal

Les six stories commitent ou poussent. **Aucune ne se confie à un worker lancé en worktree isolé** (règle 10 de [consigne-worker.md](../../../templates/consigne-worker.md)) : les workers travaillent dans l'arbre principal, séquencés par l'orchestrateur, un seul à la fois par dépôt. Un worker isolé ne reçoit que du travail sans `git` (lecture, sélection des photos et fichier de crédits rendus dans la réponse).

## Lieu de vérification du rendu des stories d'interface (action 4 de la rétro du sprint 23)

| US | Lieu | Procédure | État de la story avant la preuve |
|----|------|-----------|----------------------------------|
| US-186 | Production après le déploiement automatique de `main` par Railway, lecture seule | pa11y WCAG2AAA FR et EN, 360 px et 1280 px, LCP mobile avant/après (`docs/tests-locaux.md`, « Vérifier le rendu en production ») | review |
| US-182 | Production après le déploiement automatique, lecture seule | largeur des pages à tableau à 360 px et 1280 px | review |

US-181, US-183, US-184 et US-185 n'ont pas de rendu à juger en production : leurs preuves sont des runs de CI, des suites de tests et des sorties d'essai, citées dans leur table « Scénarios et preuves ». US-183 et US-184 ajoutent un contrôle de réponse 200 en production, sans jugement visuel.

## Actions de la rétrospective du sprint 25 appliquées

| Action | Application dans ce sprint |
|--------|----------------------------|
| 1. Budget disque d'un nouveau dépôt et restes listés | ✅ Ligne ajoutée à `checklist-integration.md` au planning (T-TECH-26-01) ; budget écrit dans US-184 et US-185 (`vendor/` environ 400 Mo) ; exécution à chaque lot : T-TECH-26-05 ; indicateur : espace libre minimal d'au moins 5 Go (3,9 Go au sprint 25) ; **5,2 Go libres au planning, voir « Actions de Thibaut »** |
| 2. Garde sur les `code` dans les tableaux | Story US-182 (1 point) ; indicateur : 0 défaut d'affichage trouvé en production (1 au sprint 25) |
| 3. Concordance des versions avant publication | ✅ Ligne ajoutée à `checklist-integration.md` (T-TECH-26-02) ; exécution avant chaque tag : T-TECH-26-11, T-181-05, T-184-07, T-185-12 ; indicateur : 0 artefact publié à une version non concordante |
| 4. Consigne pour un nouveau dépôt de plugin | ✅ Règle 12 ajoutée à `consigne-worker.md` (T-TECH-26-03) ; appliquée à US-185 ; indicateur : 0 worker bloqué par un refus prévisible, 0 second serveur de base |

Actions des sprints précédents maintenues : table « scénario → test ou preuve » dans chaque fichier de tâches (T-TECH-26-08), preuve rafraîchie dans la PR (T-TECH-26-10), statuts cochés dans le commit qui livre (T-TECH-26-09), lieu de vérification écrit au planning, clôture dès le périmètre vérifié, tests de pages à tableau avec les valeurs réelles les plus longues.

## Actions de Thibaut

| ID | Action | Durée |
|----|--------|-------|
| (avant le premier lot) | Libérer de l'espace disque : 5,2 Go libres au planning, sous le seuil de 6 Go (voir ci-dessous) | hors estimation |
| T-186-05 | Valider la maquette de la home (desktop et mobile) ou demander des retouches | 0,5 h |
| T-181-04 | GO pour pousser, fusionner et poser le tag correctif de `sylius-b2b-mcp-plugin` (et, séparément, la PR du pack si nécessaire) | 0,25 h |
| T-184-06 | GO pour pousser, fusionner le plugin de transmission, poser le tag et la release `v0.2.0` | 0,25 h |
| T-185-11 | GO pour créer le dépôt `thibmonier/sylius-erp-flatfile-plugin`, pousser et poser le tag `v0.1.0` | 0,25 h |
| T-185-13 | Soumettre le nouveau paquet à Packagist | 0,25 h |
| T-180-13 (sprint 25, **prérequis d'US-183**) | Soumettre `tmonier/sylius-einvoice-transmission-plugin` à Packagist s'il ne l'est pas encore | 0,25 h |

Notes :
- **Aucun push, tag, release, création de dépôt ni PR hors démo sans GO de Thibaut.** Commit, push et PR de la démo sur demande ou GO permanent (« fusionner les PR dès qu'elles sont vertes », `main` protégée : attendre la fin des contrôles). Un GO couvre l'objet nommé, pas le suivant.
- **Aucune commande Railway.** Railway déploie `main` automatiquement ; les vérifications en production sont en lecture seule.
- **Politique** : ton neutre, aucune comparaison avec une offre commerciale (Sylius Plus), aucune plateforme agréée ni aucun ERP réel nommé comme partenaire ; statuts de facturation « à vérifier » ; aucun plugin « concurrent » ou « à confirmer » mis en avant.
- **Sécurité** : aucun détail exploitable dans les dépôts publics (routes, valeurs, seuils).
- **Espace disque** : candidats à la suppression par Thibaut, hors sprint (chaque `vendor/` se recrée par Composer) : `vendor/` de `sylius-company-accounts-plugin` (536 Mo), `sylius-company-insights-plugin` (484 Mo), `sylius-purchase-approval-plugin` (344 Mo), `sylius-quick-order-plugin`, `sylius-customer-asset-management-plugin`, `sylius-container-fill-plugin`, `sylius-command-palette-plugin`, `sylius-admin-order-creation-plugin` (environ 340 Mo chacun), soit environ 3 Go ; commande : `/bin/rm -rf <dossier>/vendor` pour chacun. L'orchestrateur ne supprime rien de lui-même hors `bin/clean-test-caches --all`.

## Consigne de disque et de tests des workers

1. `df -h .`, `bin/check-disk` et mémoire libre avant chaque lot (`bin/run-tests` refuse sous 5 Go, seuil jamais abaissé ; **sous 6 Go libres, aucun nouveau lot**).
2. Tests de la démo : **uniquement** `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`. **Jamais phpunit en direct** (orchestrateur compris, règle 11). Identités proposées : w186, w183, w185 (et w182 ou w184 pour les suites ciblées).
3. Un seul serveur lourd à la fois (démo locale, MySQL de test, `vendor/` d'un plugin, Docker) ; arrêté dès la fin de la vérification.
4. **Après chaque lot** : aucun run actif, `bin/clean-test-caches --all` (orchestrateur seul), `df -h .`, liste à Thibaut des restes qu'un refus a empêché de supprimer ; sous 6 Go libres, prévenir Thibaut avant le lot suivant.
5. **Budget disque par story qui touche un dépôt hors de la démo** : US-181 (CI comme juge, aucune installation locale), US-184 (`vendor/` du plugin environ 400 Mo, supprimé en fin de lot), US-185 (`vendor/` environ 400 Mo plus base d'essai, supprimés en fin de lot). Le budget est relevé avant et après.
6. **Règle 12** (nouveau plugin) : aucun `.env*`, MySQL de la démo avec une base à son nom, jamais de second serveur.
7. Contrôles de qualité rejoués sur l'arbre exact avant chaque push (T-TECH-26-06).
8. Toute PR de la démo qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check` (T-TECH-26-10).
9. Refus d'outil : ne jamais le contourner ; le noter dans le rapport et continuer (règle 8 de `consigne-worker.md`).
10. Lecture d'un fichier à modifier : outil Read ou `rtk proxy cat`, jamais `cat`, `sed` ou `head` filtrés (règle 9).
11. Pièges du shell (`rm` aliasé, `sed -E`, sortie de test écrite dans un fichier, `catchExceptions(false)`) : section « Pièges connus » de `docs/tests-locaux.md`. Suppressions avec `/bin/rm`.
12. Dépôt existant ou cloné : `git fetch` et branche courante contrôlées avant tout commit (branches fusionnées périmées).
13. Chaque worker place le « Rapport de fin » du gabarit `consigne-worker.md` en tête de sa réponse finale.

## Prérequis d'environnement par story

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-186 | Maquette validée par Thibaut ; photos de banques libres et licences relues ; outil de conversion d'images ; conteneur MySQL du projet pour `bin/run-tests` ; Chromium et pa11y en dossier jetable pour la production ; 6 Go libres | T-186-03, T-186-05, T-TECH-26-05 |
| US-181 | Clone à jour de `sylius-b2b-mcp-plugin` (et du pack si besoin) ; `gh` en écriture **après GO** ; CI comme juge, aucune installation locale | T-181-01, T-181-04 |
| US-182 | Aucun prérequis lourd ; production en lecture seule pour le contrôle de largeur | T-182-05 |
| US-183 | Plugin de transmission v0.1.0 **résolu sur Packagist** ; Docker et MySQL du projet ; 6 Go libres | T-183-02 |
| US-184 | US-183 mergée ; clone du plugin de transmission ; Docker, MySQL de la démo, **budget `vendor/` d'environ 400 Mo** ; GO avant le tag | T-184-01, T-184-06 |
| US-185 | Dossier local du nouveau plugin ; dépôts `webhook-outbox` et `facturx` à jour (lecture) ; Docker et MySQL de la démo, **budget `vendor/` d'environ 400 Mo plus base d'essai** ; 6 Go libres ; GO avant tout dépôt distant | T-185-01, T-185-02, T-185-11 |
