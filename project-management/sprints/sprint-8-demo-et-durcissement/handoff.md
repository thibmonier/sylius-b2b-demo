# Reprise — sprint 8 (démo et durcissement), état au 2026-10-16 soir

## État final (orchestrateur)

- Branche poussée, **PR #11** (brouillon). CI verte : run 38944652436, lancé à la main parce que l'événement
  `pull_request` ne s'est pas déclenché. GitGuardian vert.
- Contre-vérification et revue faites : voir `review-findings.md` (section finale). Course du simulateur corrigée
  (`1f3888e`), README corrigé (`d554cb4`), relevé de coût écrit (`b239763`). Rétro : `retrospective.md`.
- **Reste à faire** : GO de merge de l'utilisateur. Ensuite, squash merge, repointer le service web Railway sur `main`
  (`railway config apply`), redéployer, puis reset en `www-data`.
- À valider avec l'utilisateur : M2, M5, M8, et la règle MCP par canal (R2).

## Rapport worker-demo

Branche `feature/sprint-8-demo-et-durcissement`, sans push. Suite : 593 tests, 1 échec attendu (Factur-X, `OrderHistorySmokeTest`), PHPStan 0, ECS 0.

| Commit | Contenu |
|--------|---------|
| 5ca58f5 | M2 `DEMO_REGISTRATION_OPEN` (ouvert par défaut ; **mettre à 0 avant l'ouverture publique**) |
| a33202a | M6 limiteur `/health(z)` + cache 5 s (`DEMO_RATE_LIMIT_HEALTH`) |
| 2d45bba | M4 purge des images orphelines au reset (tests : `var/media-test`) |
| e86a683 | M5 garde sortant sur `http_client.transport`, redirections non suivies |
| 6885730 | M8 `ReturnAuthenticator` (journal du plugin PunchOut, usage unique) |
| db98567 | `review-findings.md` du sprint 8 (M2, M4, M5, M6, M8) |
| 53dce52 | US-041 CSV (S2-R3 à R6), 210 produits identiques |
| 4da9d9a | US-041 bandeau « prochaine remise à zéro » |
| e3f1597 | US-030 page `/{locale}/mcp` + `docs/mcp.md` |
| 0eb746f | channel-access ^0.2 + registration-approval ^0.1.2, `RestrictPriceRoutesPass` supprimée |
| c1ce20a | b2b-mcp ^1.2.1, `ApprovedRegistrationChecker` remplace `McpApprovalGuard` |

Points ouverts :
- Non faits (externes ou hors worker) : T-040-14, T-041-12/13/14, T-030-03 manuel (après ouverture publique), T-030-04 ; sprint 9 : T-041-06 à 09 et 15.
- Le guide MCP n'est suivable de bout en bout qu'après l'ouverture publique (la porte basic auth bloque Claude/ChatGPT).
- `ApprovedRegistrationChecker` ne reçoit que le client (contrat du plugin) : il refuse dès qu'un canal de la politique d'approbation existe, pas selon le canal propre de la connexion. À remonter si une règle par canal est voulue (évolution de l'interface).
- Décisions à valider : M2 (risque accepté tant que privé), M5 (pas de redirection), M8 (retour à usage unique).
- Les fixtures demo n'ont aucune image : « images conservées » n'est prouvé qu'en unitaire.
- Disque faible (~3,9 Go libres).

Document de reprise après nettoyage du contexte. Lancé par `/workflow:auto-sprint 008`.

- Dépôt : `sylius-b2b-demo`, branche **`feature/sprint-8-demo-et-durcissement`** (non poussée, pas de PR encore).
- Suivi : `.bmad/sprint-status.yaml` (bloc sprint-8, 25 pts, 5 stories en `ready-for-dev`) ; tâches dans `tasks/`, `task-board.md`.
- Planification commitée : `ce51c2c`. Sprint 9 planifié (`sprint-9-reports` : US-006, US-029, T-041-06..09, T-041-15).

## Phases de l'auto-sprint

| Phase | État |
|-------|------|
| 0-3 Normalisation, démarrage, décomposition, gate | ✅ (126,5 h décomposées → arbitrage utilisateur : 25 pts, 90,5 h ; gate 100/100) |
| 4 Implémentation | 🔄 voir stories |
| 5 PR, 6 CI, 7 revue, 8 rétro, 9 merge | ⏳ (merge seulement sur GO utilisateur) |

## Stories

| Story | État | Reste à faire |
|-------|------|---------------|
| US-030 Guide MCP | code fait (worker-demo, `e3f1597`) | T-030-03 validation manuelle avec un vrai Claude (après ouverture publique) ; T-030-04 revue à froid |
| US-040 Reports sécurité (M2, M4, M5, M6, M8) | code fait (worker-demo, 7 commits `5ca58f5..db98567`) | T-040-14 contre-vérification (agent indépendant) |
| US-041 Qualité données / reset | code fait (worker-demo `53dce52`, `4da9d9a`) | T-041-12/13 : **relevé de coût partiel fait** (voir plus bas), à écrire dans `docs/deploiement-railway.md` ; T-041-14 revue |
| US-042 channel-access routes restreintes | **publié 0.2.0** (revue mutualisée → 2 majeurs corrigés → contre-vérifié, CI verte) | intégration démo par worker-demo (en cours) : remplacer `src/DependencyInjection/Compiler/RestrictPriceRoutesPass.php` par `restricted_routes` / `restricted_paths` |
| US-043 b2b-mcp statut d'utilisabilité | **publié 1.2.1** (voir incident) | intégration démo par worker-demo : vérificateur `AccountUsabilityCheckerInterface` dans la démo (statut d'approbation + canal validant), suppression de `src/Demo/Mcp/McpApprovalGuard.php` |

Workers encore actifs (adressables par nom avec SendMessage) : **worker-demo** (intégration US-042 puis US-043 dans la démo ; il écrit son rapport final en tête de ce fichier), **worker-plugins** (inactif, connaît les plugins).

## Publications du sprint (toutes sur GO utilisateur)

- `tmonier/sylius-channel-access-plugin` **0.2.0** (Packagist OK).
- `tmonier/sylius-registration-approval-plugin` **0.1.2** : contrainte `channel-access ^0.1 || ^0.2` (sans elle, la démo ne pouvait pas monter en 0.2).
- `tmonier/sylius-b2b-mcp-plugin` **1.2.1** (Packagist OK, ref `8f9f2f6`).
- **Incident** : `v1.2.0` de b2b-mcp a d'abord été taguée sur l'ancien main (le push du CHANGELOG avait échoué, la branche locale suivait `origin/main`) ; tag et release supprimés puis recréés sur le bon commit, mais **Packagist garde l'ancienne référence de v1.2.0** (Update sans effet). D'où 1.2.1. L'utilisateur peut supprimer la version 1.2.0 sur Packagist. Ne jamais dépendre de `^1.2.0` : utiliser `^1.2.1`.

## Décisions utilisateur (2026-10-16)

- Périmètre : routes restreintes channel-access et statut d'approbation b2b-mcp ajoutés au sprint ; US-006, US-029, S2-R7..R10 au sprint 9.
- Relevé de coût Railway **partiel** : `sylius-b2b-demo` 0,065 $ en ~5 h (journée de builds) → projection ≈ 9,5 $/mois (surestimée), workspace estimé 3,95 $/période, alerte à 30 $. Relevé complet à J+7 (T-041-15) à partir du 2026-10-23.
- Disque libéré par `docker image prune -a --filter until=24h` (2,6 Go).

## Décisions du worker-demo à valider avec l'utilisateur (bilan du sprint)

1. M2 : inscription libre-service ouverte par défaut (risque accepté par écrit) ; `DEMO_REGISTRATION_OPEN=0` à poser avant l'ouverture publique.
2. M5 : en mode démo, les appels sortants ne suivent plus les redirections.
3. M8 : le retour du simulateur PunchOut est à usage unique (actualiser = « déjà reçu »).
4. Tests : images de test dans `var/media-test`.

## Reprise conseillée

1. `git switch feature/sprint-8-demo-et-durcissement && git log --oneline main..HEAD` ; lire le rapport du worker-demo en tête de ce fichier (s'il est arrivé) ; sinon `SendMessage` à `worker-demo` pour l'état.
2. Écrire le relevé de coût partiel dans `docs/deploiement-railway.md` (section Budget) — T-041-12/13.
3. Contre-vérification indépendante d'US-040 (T-040-14) et revue de l'intégration (US-042/043 dans la démo), puis suite complète.
4. Push, PR brouillon, CI, revue (`review-findings.md`, le worker en a commencé un), rétro, puis GO de merge et redéploiement Railway (reset manuel **en `www-data`** : `railway ssh --service web -- su -s /bin/sh www-data -c 'php bin/console app:demo:reset --no-interaction'`).

## Pièges (à relire)

- `railway ssh` = root : toute commande qui reconstruit le cache passe par `su -s /bin/sh www-data -c …`.
- Disque : ~3,5 Go libres ; `rm` est interactif (utiliser `/usr/bin/find <dir> -delete`) ; vendors des plugins supprimés (relancer `composer install` si besoin).
- Tests locaux : base `sylius_b2b_demo_test_orch` (cache `var/cache-orch`) ; faux wkhtmltopdf dans le scratchpad (`WKHTMLTOPDF_BINARY`) → seul échec attendu : assertion Factur-X d'`OrderHistorySmokeTest` ; `APP_DEBUG=0` en test → vider le cache après tout changement de service.
- Publication : vérifier push → PR head → merge → contenu du commit tagué AVANT de taguer ; nouveaux paquets soumis à Packagist par l'utilisateur ; un tag publié ne se réécrit pas.
