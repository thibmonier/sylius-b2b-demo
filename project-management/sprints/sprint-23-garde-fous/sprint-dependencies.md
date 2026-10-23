# Dépendances : Sprint 023 (garde-fous et cadrages)

> Périmètre validé par Thibaut le 2026-10-23 (12 points). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO["Rétro du sprint 22 : actions 1 à 6"]
    US084["US-084 (sprint 22) : scénario « Preuve en retard » non livré"]
    PR54["PR Dependabot #54 : PHPUnit 13"]
    FLAKY["Test instable RunTestsLockTest (PR #64)"]
    DISK["T-TECH-23-01 et 01b : disque au moins 5 Go, un serveur lourd à la fois"]
    US168["US-168 Contrôle de retard de la preuve (1 pt)"]
    US166["US-166 Méthodes longues et usleep (3 pts)"]
    US169["US-169 Spike PHPUnit 13 (2 pts)"]
    US132["US-132 Spike facture électronique (3 pts)"]
    US130["US-130 Spike connecteur ERP (3 pts)"]
    CI["ci.yaml partagé"]
    ADR["docs/adr/index.md partagé"]
    FACTURX["Plugin facturx (lecture)"]
    OUTBOX["Plugin webhook-outbox (lecture)"]
    DEC["Décisions de Thibaut : T-169-05, T-132-06, T-130-06"]

    US084 --> US168
    FLAKY --> US166
    PR54 --> US169
    RETRO --> US168
    RETRO --> US166
    DISK --> US168
    DISK --> US166
    US168 --> CI
    CI --> US166
    FACTURX --> US132
    OUTBOX --> US130
    US132 --> ADR
    ADR --> US130
    US169 --> DEC
    US132 --> DEC
    US130 --> DEC
```

## Lecture

| Story | Dépend de | Nature | Bloquant |
|-------|-----------|--------|----------|
| US-168 | Règle de comptage de `ProofCollector` | Code existant à partager | non |
| US-166 | US-168, à cause du fichier `ci.yaml` | Séquencement, si la garde passe par la CI | non (garde possible dans un test) |
| US-166, US-168 | Disque d'au moins 5 Go pour la suite locale | Environnement | non (la CI fait foi) |
| US-169 | PR #54 ouverte | Branche d'essai | non |
| US-130 | US-132, à cause de `docs/adr/index.md` (numéros 0015 puis 0016) | Séquencement | non |
| Clôture des spikes | Décisions de Thibaut | Action humaine | oui, pour passer en done |
