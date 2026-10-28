# Tâches : US-209 : Chaîne d'approvisionnement des dépôts de plugins : workflows durcis, audit Composer et politique explicite

## Informations US

- **Epic** : EPIC-14 (lien : EPIC-10)
- **Persona** : Thibaut ; Hugo (intégrateur Sylius)
- **Story Points** : 8 (Should, **sacrifice : n°1 tranche (b) Composer, n°2 tranche (a) workflows**, registre du 2026-10-28)
- **Sprint** : sprint-33-indexation-et-depots
- **Worker proposé** : w209
- **Story** : [US-209](../../../backlog/user-stories/US-209.md)

## Résumé de la US

**En tant qu'** intégrateur qui installe un plugin de Thibaut  
**Je veux** que chaque dépôt applique la même hygiène de chaîne d'approvisionnement que la démo  
**Afin de** ne pas hériter d'une dépendance ou d'un workflow compromis

## Règles et contraintes

- **GO du registre (2026-10-28, auteur : Thibaut)** : push d'une branche et PR par dépôt, **fusion de chaque PR dont la CI est verte**, **aucun tag ni release** ; toute exception (CI rouge préexistante, dépôt sans CI) est remontée à Thibaut (T-209-11). Aucune création de dépôt.
- **Périmètre : 29 dépôts** = 27 plugins + 2 packs (`sylius-pack-ai-headless`, `sylius-pack-compliance-tooling`), copies locales sous `/Users/tmonier/Projects/sylius_plugins/` ; le fork de la wishlist (`sylius-wishlist-plugin-fork`) est **exclu** (il n'existe plus sur GitHub). La liste des 29 est versionnée dans la démo (noms publics seulement).
- **Script commun tenu dans la démo** : `bin/plugin-supply-chain`, avec trois modes : `report` (relevé de l'état des 9 contrôles, sur les branches par défaut **distantes** via `gh`, pas sur les copies locales), `apply` (génère les changements d'un dépôt, `--dry-run` par défaut) et `check` (les 9 contrôles d'un dépôt, sortie « sans objet » pour la revue du verrou d'un dépôt sans `composer.lock`). Il **réutilise** `bin/check-pinned-refs` et `bin/check-lock-diff` d'US-208 et le gabarit de workflow de la démo, sans les copier. Il applique la garde de la règle 18 (chemin sous `/Users/tmonier/Projects/sylius_plugins/`, dépôt Git) et le contrôle de la règle 29 (`git fetch`, branche courante, retard sur la branche par défaut) et **refuse de continuer** sinon.
- **Les 9 contrôles** : actions figées par SHA de commit (étiquette en commentaire), `permissions: {}` au niveau du workflow puis `contents: read` par job, `persist-credentials: false` sur chaque `actions/checkout`, aucun `pull_request_target`, zizmor (job obligatoire, version et image figées, `--offline`), `composer audit` bloquant (`--locked` si `composer.lock` ; après l'installation sinon : `sylius-erp-flatfile-plugin` et les 2 packs), `config.policy` explicite, `allow-plugins` en liste blanche (à ajouter aux 2 packs), Dependabot (Composer et GitHub Actions ; à ajouter à `sylius-einvoice-transmission-plugin`, `sylius-erp-flatfile-plugin`, `sylius-punchout-cxml-plugin` et aux 2 packs). Revue du diff du verrou par le script d'US-208 dans les dépôts qui versionnent un `composer.lock`. Pas de branche `dev-*` dans `require` et usage de `secrets.` : revérifiés au relevé de départ, rien à publier.
- **Découpage si la story déborde (registre)** : tranche (a) workflows (SHA, `permissions`, `persist-credentials`, `pull_request_target`, zizmor, Dependabot) ; tranche (b) Composer (audit, politique, `allow-plugins`, revue du verrou). Répartition indicative des points d'après les heures : (a) 7, (b) 1, à confirmer par Thibaut si le sacrifice s'applique.
- **Une PR par dépôt**, branche `chore/supply-chain-hardening` depuis la branche par défaut distante à jour ; ne changent que `.github/` et la section `config` de `composer.json` ; message Conventional Commits (`ci(supply-chain): …` / `build(composer): …`), neutre et bref ; **aucun code, aucune dépendance, aucun `composer.lock`**, aucune version publiée (seules la CI et `config` changent ; `config.policy` ne protège que la CI du dépôt).
- **Pilote d'abord** (`sylius-quick-order-plugin`, dépôt distant déjà vérifié au relevé du 2026-10-27) : sa CI verte valide le script, la version de Composer qui lit `config.policy` et les SHA, avant les quatre lots de sept dépôts.
- **Rien de sensible dans les dépôts publics** : messages, descriptions de PR et rapport joint à la PR de la démo ne contiennent que l'état conforme ou non des 9 contrôles (oui, non, sans objet), jamais de constat de sécurité détaillé ; ton neutre (politique Sylius Plus), aucun texte promotionnel.
- **Aucune protection de branche contournée** : une branche protégée qui exige une revue, ou une CI rouge préexistante, donne une PR laissée ouverte et une remontée, pas une fusion forcée.
- Fichiers communs (règle 21) : `bin/plugin-supply-chain`, `config/demo/plugin-repositories.*`, `tests/Unit/Bin/PluginSupplyChainTest.php`, `docs/chaine-approvisionnement-plugins.md` : US-209 seule ; `.github/` et `composer.*` de la démo : personne ; le relevé de départ est joint à la PR de la démo (`project-management/sprints/sprint-33-indexation-et-depots/releve-depart-depots.md`).
- Tests uniquement par `WORKER_ID=w209 TEST_DB_NAME=b2b_w209 TEST_CACHE_DIR=var/cache-w209 bin/run-tests`, dans l'arbre partagé (règle 22) ; le script est testé sur des dépôts de **fixtures** créés dans un dossier temporaire (règle 27 : rouge prouvé) ; aucun test ne touche un vrai dépôt de plugin.

## Lieu de vérification

**Local** (script et fixtures) et **dépôts distants** (CI de chaque PR, puis `bin/plugin-supply-chain check` sur les branches par défaut après fusion). Aucune vérification de production de la démo.

## Fichiers principaux touchés

- `bin/plugin-supply-chain`, `config/demo/plugin-repositories.*`, `tests/Unit/Bin/PluginSupplyChainTest.php` (nouveaux), `docs/chaine-approvisionnement-plugins.md` (nouveau), `config/demo/proof.json` (par `bin/proof-rebase`)
- Dans chacun des 29 dépôts : `.github/workflows/*.yml`, `.github/dependabot.yml` (si absent), `composer.json` (section `config`)
- `project-management/sprints/sprint-33-indexation-et-depots/releve-depart-depots.md` et `rapport-final-depots.md` (nouveaux)
- Mémoire du socle qualité des plugins (hors dépôt, par l'orchestrateur)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-209-01 | [DOC] | Cadrage et **relevé de départ refait sur les branches par défaut distantes** des 29 dépôts (`gh`, pas les copies locales) : état des 9 contrôles, présence de CI, version de Composer de la CI, branche par défaut, protection de branche, usage de `secrets.` (oui ou non, rien de plus), branches `dev-*` ; scripts et gabarit d'US-208 relus ; plan des tests ; relevé versionné sans détail sensible | 1,5 h | - | Claude | ✅ |
| T-209-02 | [TEST] | Tests rouges du script, **rouge prouvé (règle 27)**, commités seuls et cités : dépôt conforme (9 contrôles), dépôt sans verrou (audit après installation, revue du verrou « sans objet »), action en étiquette, checkout sans `persist-credentials: false`, `pull_request_target`, `permissions` absentes, politique et `allow-plugins` absentes, Dependabot absent ; refus hors du dossier attendu, d'un dépôt non Git et d'une branche périmée (règle 29) ; fixtures dans un dossier temporaire ; données de test relues avant le commit | 1,5 h | T-209-01 | Claude | ✅ (rouge prouvé, disque à 6 Go) |
| T-209-03 | [OPS] | **Tranche (a) workflows** dans `bin/plugin-supply-chain` : `check` et `apply` pour SHA (table d'actions figées reprise de la démo, `SyliusLabs/BuildTestAppAction` résolue une fois), `permissions: {}` puis `contents: read` par job, `persist-credentials: false`, absence de `pull_request_target`, job zizmor (version et image figées, `--offline`), Dependabot (Composer, GitHub Actions) ; liste des 29 dépôts versionnée | 2,5 h | T-209-02 | Claude | ✅ |
| T-209-04 | [OPS] | **Tranche (b) Composer** ⚠️ **sacrifice n°1** : `check` et `apply` pour `composer audit` bloquant (`--locked` ou après installation), `config.policy`, `allow-plugins` (packs), revue du diff du verrou (script d'US-208, « sans objet » sans verrou) ; rapport lisible par dépôt | 2 h | T-209-03 | Claude | ✅ |
| T-209-05 | [DOC] | `docs/chaine-approvisionnement-plugins.md` : méthode, les 9 contrôles, commandes du script, règle « plugin-repo-branch-check », ce qui ne change pas (aucun tag, aucune version), neutre ; lien depuis la documentation d'US-208 | 0,5 h | T-209-04 | Claude | ✅ |
| T-209-06 | [OPS] | **Pilote** sur `sylius-quick-order-plugin` : branche, `apply`, `check`, commit, push, PR, CI verte, fusion ; script et gabarit corrigés si la CI montre un écart (version de Composer, SHA, zizmor) avant les lots | 1,5 h | T-209-04 | Claude | ✅ |
| T-209-07 | [OPS] | **Lot 1** (7 dépôts, ordre de la liste versionnée) : règle 29 (`git fetch`, branche), `apply`, `check`, commit, push, PR, suivi de la CI, fusion si verte ; état noté par dépôt | 1 h | T-209-06 | Claude | ✅ |
| T-209-08 | [OPS] | **Lot 2** (7 dépôts), même procédure | 1 h | T-209-07 | Claude | ✅ |
| T-209-09 | [OPS] | **Lot 3** (7 dépôts), même procédure | 1 h | T-209-08 | Claude | ✅ |
| T-209-10 | [OPS] | **Lot 4** (7 dépôts, packs compris : `allow-plugins`, Dependabot, audit sans verrou), même procédure | 1 h | T-209-09 | Claude | ✅ |
| T-209-11 | [OPS] | **Remontée à Thibaut** des dépôts à CI rouge préexistante, sans CI, à branche protégée ou à PR restée ouverte : liste (nom, cause en une ligne, PR) sans détail sensible, au fil des lots ; décision de Thibaut notée au registre avant d'agir (📅 pour chaque dépôt concerné) | 0,5 h | T-209-07 | Claude | ✅ |
| T-209-12 | [DOC] | Rapport final `rapport-final-depots.md` : 29 dépôts conformes, ou écarts justifiés dépôt par dépôt (PR, CI, fusion, 9 contrôles) ; relevé de départ joint à la PR de la démo | 1 h | T-209-10 | Claude | ✅ |
| T-209-13 | [TEST] | Vérification finale : `bin/plugin-supply-chain check` sur les branches par défaut **distantes** des 29 dépôts après fusion ; écarts reportés au rapport ; suite ciblée du script, PHPStan max sur tout le dépôt, ECS, Rector ; commande exacte et dernière ligne citées | 0,75 h | T-209-10 | Claude | ✅ (disque à 3 Go : suite ciblée et Rector non rejoués) |
| T-209-14 | [DOC] | Mémoire du socle qualité des plugins mise à jour (état des 29 dépôts, script, exceptions) | 0,25 h | T-209-12 | Orchestrateur | 🔲 |
| T-209-15 | [REV] | Revue (**aucun tag, aucune version**, aucun code ni dépendance modifié, rien de sensible dans les dépôts et les PR, ton neutre, règle 29 respectée pour chaque dépôt, aucune protection contournée) et table « scénario → test ou preuve » remplie | 0,5 h | T-209-13 | Claude | 👀 |

**Total estimé : 16,5 h** (15 tâches, toutes pour Claude et l'orchestrateur). Heures de la tranche (b), si le sacrifice n°1 s'applique : T-209-04 (2 h) et environ 0,75 h de ses parts dans T-209-02 et les lots, soit environ 2,75 h.

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Dépôt conforme (nominal) | T-209-02, T-209-12, T-209-13 | `PluginSupplyChainTest::testAConformingRepositoryPassesTheNineControls` ; `check` sur les branches par défaut distantes des 29 dépôts, rapport final |
| Dépôt sans verrou (alternatif) | T-209-02, T-209-04, T-209-10 | `testARepositoryWithoutLockAuditsAfterTheInstallAndSkipsTheLockReview`, `testARepositoryThatTracksItsLockMustAuditTheLock` ; CI de `sylius-erp-flatfile-plugin` et des 2 packs |
| Mise à jour Dependabot d'une action (alternatif) | T-209-02, T-209-03 | `testAnActionPinnedByShaWithItsVersionInACommentIsAccepted` ; première PR Dependabot fusionnée observée ou, à défaut, le test seul et la mention dans le rapport |
| Avis de sécurité sur une dépendance (erreur) | T-209-02, T-209-04 | `testANonBlockingAuditFails` (l'étape doit être bloquante ; Composer nomme lui-même le paquet et l'avis, non simulé) ; aucune vraie dépendance vulnérable introduite |
| Régression d'un workflow (erreur) | T-209-02, T-209-03 | `testAnActionReferencedByATagFailsWithFileAndLine`, `testACheckoutWithoutPersistCredentialsFalseFailsWithFileAndLine`, `testAPullRequestTargetTriggerFailsWithFileAndLine` ; zizmor en CI |
