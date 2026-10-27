# Tâches : US-208 : Chaîne d'approvisionnement PHP : politique Composer, revue du verrou et durcissement des workflows

## Informations US

- **Epic** : EPIC-10
- **Persona** : Thibaut (mainteneur de la démo) ; Didier (DSI / CTO)
- **Story Points** : 5 (Should, **protégée**)
- **Sprint** : sprint-31-chaine-approvisionnement-et-comptes
- **Worker proposé** : w208
- **Story** : [US-208](../../../backlog/user-stories/US-208.md)

## Résumé de la US

**En tant que** mainteneur de la démo  
**Je veux** que la CI bloque un paquet Composer réécrit, un plugin Composer non revu ou un workflow GitHub mal configuré  
**Afin qu'** une dépendance compromise n'atteigne ni l'image ni la production

## Règles et contraintes

- **Sans maquette** : story d'outillage (CI, Composer, scripts) ; aucun gabarit Twig, aucune porte de Thibaut hormis le réglage des contrôles requis de `main` (T-208-11).
- **Écart relevé le 2026-10-27** (fiche, tableau en 8 points) : points 2, 3 faits (rien à faire) ; à faire : 1 (liste `allow-plugins` figée par un test), 4 (`config.policy` explicite, `--no-blocking` interdit), 5 (`bin/check-lock-diff`), 6 (`sylius/wishlist-plugin` en `1.4.x-dev`), 7 (`permissions: {}`, `persist-credentials: false`, zizmor), 8 (inventaire des secrets **hors dépôt public**) ; Composer de la CI et de l'image sur la même version 2.10.x, suivie par Dependabot. **Ne pas utiliser `policy.cooldown`** (sans effet en Composer 2.10.3).
- **Contrôle « si vous avez été touché »** : au début (T-208-01), dans l'historique de `composer.lock`, aucune version visée par les alertes de l'article n'a été installée ; résultat noté ici, sans détail d'infrastructure.
- **Dépôt public** : aucun nom de secret, aucune valeur, aucun détail d'infrastructure dans les fichiers, les messages de commit ni la PR (règle 12). L'inventaire des secrets vit hors du dépôt.
- **`vendor` de l'arbre partagé jamais modifié par un worker** : le passage de `sylius/wishlist-plugin` sur une version stable change `composer.lock` ; il se vérifie dans un répertoire propre avec son `vendor` (disque relevé avant, règle 24), puis l'orchestrateur lance `composer install` dans l'arbre partagé **après** la fusion. Les autres workers ne subissent aucun changement de dépendances en cours de sprint.
- **Hors périmètre** : les dépôts de plugins (US-209, après ce sprint), la sortie réseau des runners et de Railway (évaluation notée dans l'inventaire privé).
- Fichiers communs (règle 21) : `composer.json`, `composer.lock`, `.github/`, `docker/Dockerfile`, `bin/check-lock-diff` (nouveau), `docs/durcissement-demo.md` : US-208 seule ce sprint ; aucun fichier de thème, de traduction ni de fixtures touché.
- Tests uniquement par `WORKER_ID=w208 TEST_DB_NAME=b2b_w208 TEST_CACHE_DIR=var/cache-w208 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local, CI et image reconstruite** : tests des scripts et des fixtures de verrou, jobs « Quality and smoke tests », « Docker image », « Dependency audit » et le nouveau job zizmor, rejoués tels quels en local avant le push (règle 19). **Production** : lecture seule, après le déploiement de `main`, relevé que la démo démarre avec l'image reconstruite (version de Composer, wishlist) ; aucun secret ni identifiant.

## Fichiers principaux touchés

- `composer.json` (`config.policy`, `allow-plugins`, contrainte du wishlist), `composer.lock`, `docker/Dockerfile` (Composer 2.10.x), `.github/dependabot.yml`
- `.github/workflows/ci.yaml` (`permissions: {}`, `contents: read` par job, `persist-credentials: false`, étape de revue du verrou, job zizmor)
- `bin/check-lock-diff` (nouveau), `tests/Unit/Bin/`, `tests/Unit/Supply/` et `tests/Fixtures/composer-lock/` (nouveaux fichiers)
- `docs/durcissement-demo.md` (ligne « chaîne d'approvisionnement »), `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-208-01 | [DOC] | Cadrage : **contrôle « si vous avez été touché »** (historique de `composer.lock` contre les alertes de l'article, résultat noté sans détail sensible) ; relevé de l'état réel (`allow-plugins`, `config.policy`, `permissions`, `persist-credentials`, versions de Composer de la CI et de l'image) ; version de zizmor et condensat de l'image retenus ; contrainte du wishlist (stable ou commit figé) ; plan des tests et des fixtures de verrou | 1 h | - | Claude | 🔲 |
| T-208-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : fixtures de verrou (mise à jour ordinaire, tag réécrit à version égale, nouveau `composer-plugin`) ; test qui fige `allow-plugins` ; test `config.policy` présent ; test qui interdit `--no-blocking` (CI, scripts, Dockerfile) ; test « pas de branche en production » (`dev-*`, `*.x-dev` sauf commit figé avec sa raison) ; test des workflows (`permissions: {}`, `persist-credentials: false`, pas de `pull_request_target`) avec fichier, ligne et règle dans le message | 1,5 h | T-208-01 | Claude | 🔲 |
| T-208-03 | [BE] | `bin/check-lock-diff` : compare `composer.lock` à la branche de base ; échoue en nommant paquet, version et les deux références pour un tag réécrit, et pour un nouveau paquet de type `composer-plugin` ; résumé de job (paquet, ancienne et nouvelle version) pour une mise à jour ordinaire ; sans réseau | 1,5 h | T-208-02 | Claude | 🔲 |
| T-208-04 | [OPS] | `composer.json` : `config.policy` explicite (blocage des paquets malveillants, sans `cooldown`), `allow-plugins` inchangée mais figée par le test ; wishlist sur version stable (v1.4.0 ou suivante si elle contient le correctif attendu) sinon `1.4.x-dev#<sha>` avec la raison notée ; Composer 2.10.x identique dans la CI et `docker/Dockerfile`, suivi par Dependabot ; vérifié **dans un répertoire propre avec son `vendor`**, `vendor` partagé intact | 1 h | T-208-02 | Claude | 🔲 |
| T-208-05 | [OPS] | Workflows : `permissions: {}` au niveau du workflow et `contents: read` par job ; `persist-credentials: false` sur chaque `actions/checkout` ; étape `bin/check-lock-diff` contre la branche de base avec résumé de job ; `bin/check-pinned-refs` toujours vert | 0,75 h | T-208-03 | Claude | 🔲 |
| T-208-06 | [OPS] | Job zizmor obligatoire : version figée, image figée par condensat, `--offline` ; alertes corrigées ou justifiées une à une dans la PR ; durée cumulée de l'audit Composer, de la revue du verrou et de zizmor mesurée sur une PR sans changement de dépendances (moins de 2 minutes) | 1,25 h | T-208-05 | Claude | 🔲 |
| T-208-07 | [DOC] | `docs/durcissement-demo.md` (ligne « chaîne d'approvisionnement », sans détail sensible) ; **inventaire des secrets de CI** et évaluation de la sortie réseau, tenus **hors du dépôt public** (le dépôt n'en documente que le principe) | 0,5 h | T-208-06 | Claude | 🔲 |
| T-208-08 | [TEST] | Suite ciblée par `bin/run-tests` (scripts, fixtures, workflows, Dockerfile), puis suite complète si le disque le permet (≥ 6 Go, règle 2), PHPStan max sur tout le dépôt, ECS, Rector, lint YAML ; **séquence exacte de la CI rejouée** en local ; commande exacte et dernière ligne citées | 0,75 h | T-208-04, T-208-06, T-208-07 | Claude | 🔲 |
| T-208-09 | [REV] | Revue (aucun secret ni détail d'infrastructure dans le dépôt, la PR et les messages ; plugins de la démo inchangés ; test rouge vérifié sur une modification simulée de chaque garde) et table « scénario → test ou preuve » remplie | 0,5 h | T-208-08 | Claude | 🔲 |
| T-208-10 | [OPS] | Après le déploiement de `main` : image reconstruite et conteneur neuf (règle 16 : rien hors base), démarrage vérifié, version de Composer et wishlist relevées, lecture seule ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 📅 production |
| T-208-11 | [OPS] | 📅 Thibaut ajoute le job zizmor aux contrôles requis de `main` (« Quality and smoke tests », « Docker image », « Dependency audit » déjà requis), une fois le job vert sur la PR ; noté au registre | 0,25 h | T-208-06 | Thibaut | 📅 Thibaut |

**Total estimé : 9,5 h** (11 tâches ; 9,25 h pour Claude, 0,25 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| CI verte sur une PR sans changement de dépendances (nominal) | T-208-06, T-208-08 | durée cumulée de l'audit, de la revue du verrou et de zizmor sur une PR témoin (à consigner) |
| Mise à jour Dependabot ordinaire (alternatif) | T-208-02, T-208-03 | test `CheckLockDiffTest` avec la fixture « mise à jour ordinaire » (à nommer) |
| Dépendance de branche figée (alternatif) | T-208-02, T-208-04 | test « pas de branche en production » (à nommer) |
| Tag réécrit (erreur) | T-208-02, T-208-03 | `CheckLockDiffTest` avec la fixture « tag réécrit » (à nommer) |
| Nouveau plugin Composer ou workflow fautif (erreur) | T-208-02, T-208-05, T-208-06 | tests `allow-plugins`, `--no-blocking`, `persist-credentials`, nouveau `composer-plugin` (à nommer) |
