# Dépendances — Sprint 009 (Reports)

> **Arbitrage du 2026-10-16** : US-029 (récepteur de webhooks, 15,5 h) est reportée au sprint 10. Ses tâches restent ci-dessous pour mémoire, hors engagement du sprint 9.

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S8["Sprint 8 (terminé): T-041-05, T-041-12, M5 garde sortant"]
    US002["US-002 (livrée)"]
    US004["US-004 (livrée)"]
    US008["US-008, US-010, US-039 (livrées)"]
    US006["US-006 Spike worker (9h)"]
    US029["US-029 Récepteur webhooks (15.5h)"]
    US044["US-044 container-fill (29.5h)"]
    US041["US-041 reports (14h)"]
    US002 --> US006
    US004 --> US029
    US008 --> US044
    S8 --> US029
    S8 --> US041
    US006 -.->|"threads libres pour le récepteur"| US029
    US044 -.->|"mesurer le reset avec la fixture"| US041
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T00601["T-006-01 bornage threads"] --> T00606["T-006-06 mémoire + cron"]
    T00601 --> T00602["T-006-02 fuite d'état"]
    T00601 --> T00603["T-006-03 mesure"]
    T00602 --> T00604["T-006-04 ADR-0013"]
    T00603 --> T00604
    T00606 --> T00604
    T00604 --> T00605["T-006-05 revue"]
    T02901["T-029-01"] --> T02902["T-029-02"]
    T02901 --> T02904["T-029-04"]
    T02902 --> T02905["T-029-05"]
    T02903["T-029-03"] --> T02905
    T02904 --> T02905
    T02905 --> T02907["T-029-07 revue"]
    T00601 -.->|"un thread libre"| T02902
    T04401["T-044-01"] --> T04402["T-044-02"] --> T04405["T-044-05"]
    T04403["T-044-03"] --> T04404["T-044-04"] --> T04405
    T04405 --> T04406["T-044-06"] --> T04407["T-044-07"]
    T04407 --> T04408["T-044-08"]
    T04407 --> T04409["T-044-09"]
    T04407 --> T04410["T-044-10"]
    T04409 --> T04411["T-044-11"]
    T04409 --> T04412["T-044-12"]
    T04410 --> T04412
    T04408 --> T04413["T-044-13 revue"]
    T04411 --> T04413
    T04412 --> T04413
    T04408 -.->|"durée du reset"| T04106["T-041-06"]
    T04108["T-041-08"] --> T04109["T-041-09"]
    T04112["T-041-12 (sprint 8)"] -.->|">= 2026-10-23"| T04115["T-041-15"]
```

## Chemin critique

US-044 : T-044-02 → T-044-05 → T-044-06 → T-044-07 → T-044-09 → T-044-12 → T-044-13 (≈ 18 h). Les autres stories sont indépendantes et parallélisables. Les ordres de priorité, en cas de dépassement de capacité, sont dans [tasks/README.md](./tasks/README.md).

## Ressources partagées

- Disque local ~3 Go : un seul build d'image pour US-006 (en CI), aucun autre rebuild.
- `docker/Caddyfile` : modifié par US-006 seulement.
- `src/Demo/Http/DemoGateListener.php` : modifié par US-029 seulement.
