# Dépendances — Sprint 011 (Fiabilité et ouverture)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S6["Sprint 6 (terminé): US-033 reset, US-035 admin restreint, US-036 durcissement, US-037 revue"]
    S8["Sprint 8 (terminé): US-040 durcissement, US-030 guide MCP"]
    S10["Sprint 10 (terminé): US-029 webhooks, US-048 reports, PR #20 memory_limit"]
    US049["US-049 Fiabilité du reset (12h)"]
    US050["US-050 Ouverture publique (26h)"]
    S6 --> US049
    S10 --> US049
    S6 --> US050
    S8 --> US050
    S10 -.->|"récepteur de webhooks à revoir"| US050
    US049 -.->|"/healthz fiable pour le moniteur"| US050
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T04901["T-049-01 test rouge marqueur"] --> T04902["T-049-02 marqueur"]
    T04903["T-049-03 test rouge sous-processus"] --> T04904["T-049-04 sous-processus"]
    T04902 --> T04904
    T04905["T-049-05 test Caddy"] --> T04906["T-049-06 Caddyfile"]
    T04904 --> T04907["T-049-07 doc"]
    T04904 --> T04909["T-049-09 revue"]
    T04907 --> T04909
    T04908["T-049-08 coût Railway (calendaire)"]
    T05003["T-050-03 tests deux modes"] --> T05004["T-050-04 porte"]
    T05004 --> T05005["T-050-05 inscriptions"]
    T05004 --> T05006["T-050-06 robots"]
    T05004 --> T05001["T-050-01 revue sécurité"]
    T05005 --> T05001
    T05001 --> T05002["T-050-02 correctifs"]
    T05002 --> T05007["T-050-07 runbook"]
    T05005 --> T05007
    T05005 --> T05008["T-050-08 railway.ts"]
    T05007 --> T05012["T-050-12 revue"]
    T04902 -.-> T05009["T-050-09 moniteur (utilisateur)"]
    T05009 --> T05010["T-050-10 bascule (GO)"]
    T05007 --> T05010
    T05010 --> T05011["T-050-11 parcours MCP"]
```

## Chemin critique

US-050 : T-050-03 → T-050-04 → T-050-05 → T-050-01 → T-050-02 → T-050-07 → T-050-12 (≈ 20,5 h). US-049 : T-049-01 → T-049-02 → T-049-04 → T-049-09 (≈ 7,5 h), parallèle. La bascule (T-050-10) dépend du GO de l'utilisateur et peut sortir du sprint.

## Ressources partagées

- `src/Demo/Command/DemoResetCommand.php`, `src/Demo/Command/ResetStatus.php` : US-049 seulement (T-049-02 puis T-049-04, sérialisés).
- `src/Demo/Http/` (porte, inscriptions), guide MCP, preflight : US-050 seulement (T-050-04 puis T-050-05 et T-050-06, sérialisés).
- `docker/Caddyfile` : T-049-06 ; `public/robots.txt` et en-têtes : T-050-06 ; les deux touchent la couche HTTP, ne pas les mener en parallèle sur un même fichier.
- Docs : `docs/remise-a-zero-demo.md` (US-049), `docs/ouverture-publique.md` (US-050), `docs/deploiement-railway.md` (T-049-08 et T-050-07 : sérialiser).
- Comptes et GO de l'utilisateur : T-049-08, T-050-09, T-050-10, T-050-11.
