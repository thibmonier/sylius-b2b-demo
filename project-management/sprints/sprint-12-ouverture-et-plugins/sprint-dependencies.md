# Dépendances — Sprint 012 (Ouverture et plugins)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S5["Sprint 5 (terminé): US-027 simulateur cXML"]
    S6["Sprint 6 (terminé): US-036 durcissement"]
    S11["Sprint 11 (terminé): US-049 reset fiable, US-050 mode public + runbook"]
    US052["US-052 Ouverture publique (8,5h)"]
    US053["US-053 Simulateur OCI (12,5h)"]
    US054["US-054 Plugin parc d'équipements, tranche 1 (14h)"]
    US055["US-055 Rétro et dette (8,5h)"]
    S11 --> US052
    S5 --> US053
    S6 --> US053
    US055 -.->|"bin/run-tests pour les suites"| US053
    US055 -.->|"bin/run-tests pour les suites"| US052
    US054 -.->|"hors sprint: US-056 à US-060"| BACK["Backlog: fonctionnel du plugin, publication, démo"]
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T05201["T-052-01 relecture runbook"] --> T05202["T-052-02 script de vérification"]
    T05202 --> T05204["T-052-04 bascule (GO)"]
    T05203["T-052-03 moniteur (utilisateur)"] --> T05204
    T05204 --> T05205["T-052-05 X-Real-IP"]
    T05204 --> T05206["T-052-06 parcours MCP"]
    T05205 --> T05208["T-052-08 docs"]
    T05206 --> T05208
    T05207["T-052-07 coût Railway"] --> T05208
    T05208 --> T05209["T-052-09 revue"]
    T05301["T-053-01 analyse OCI"] --> T05302["T-053-02 tests rouges"]
    T05302 --> T05303["T-053-03 entrée OCI"]
    T05303 --> T05304["T-053-04 retour OCI"]
    T05304 --> T05305["T-053-05 garde-fous"]
    T05305 --> T05306["T-053-06 tests fonctionnels"]
    T05306 --> T05307["T-053-07 doc"]
    T05307 --> T05308["T-053-08 revue"]
    T05401["T-054-01 mini-spec"] --> T05402["T-054-02 ADR"]
    T05401 --> T05403["T-054-03 verdict Plus"]
    T05403 --> T05404["T-054-04 dépôt GitHub (utilisateur)"]
    T05402 --> T05405["T-054-05 skeleton"]
    T05405 --> T05406["T-054-06 socle qualité + CI"]
    T05406 --> T05407["T-054-07 test de fumée"]
    T05406 --> T05408["T-054-08 README"]
    T05407 --> T05409["T-054-09 revue"]
    T05408 --> T05409
    T05502["T-055-02 bin/run-tests"] --> T05503["T-055-03 deux passes"]
    T05503 --> T05504["T-055-04 doc"]
    T05505["T-055-05 consigne relecteurs"] --> T05508["T-055-08 revue"]
    T05506["T-055-06 action 4 S10"] --> T05508
    T05507["T-055-07 US-031"] --> T05508
```

## Chemin critique

US-053 : T-053-01 → T-053-02 → T-053-03 → T-053-04 → T-053-05 → T-053-06 → T-053-07 → T-053-08 (≈ 12,5 h). US-054 : T-054-01 → T-054-02 → T-054-05 → T-054-06 → T-054-07 → T-054-09 (≈ 11 h), parallèle. US-052 : la bascule T-052-04 dépend du GO de l'utilisateur et peut sortir du sprint. US-055 : T-055-02 → T-055-03 en première semaine (≈ 4 h).

## Ressources partagées

- `src/Demo/Controller/PunchOutSimulatorController.php`, `src/Demo/PunchOutSimulator/` : US-053 seulement (T-053-03 → T-053-05 sérialisés).
- `config/packages/demo_hardening.yaml` (limiteurs) : US-053 (T-053-05) ; ne pas modifier pendant la bascule d'US-052.
- `.railway/railway.ts`, `docs/ouverture-publique.md`, `docs/deploiement-railway.md`, `docs/mcp.md` : US-052 seulement ; la bascule est exécutée par l'utilisateur.
- `bin/prepare-test-db`, `bin/run-tests`, `docs/tests-locaux.md` : US-055 seulement ; les autres stories attendent T-055-02 pour leurs suites complètes.
- `composer.json` / `composer.lock` de la démo : US-055 (T-055-07) seulement.
- `/Users/tmonier/Projects/sylius_plugins/sylius-customer-asset-management-plugin` : US-054 seulement, dépôt séparé.
- `.bmad/sprint-status.yaml` : orchestrateur seulement (écrivain unique).
- Comptes et GO de l'utilisateur : T-052-03 à T-052-07, T-054-04.
