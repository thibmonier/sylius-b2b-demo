# Dépendances : Sprint 023 (garde-fous et lisibilité de l'architecture)

> Périmètre validé par Thibaut le 2026-10-23 puis remanié le même jour (11 points). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO["Rétro du sprint 22 : actions 1 à 6"]
    CRIT["Critique du 2026-10-23 : architecte Sylius et client B2B"]
    US084["US-084 (sprint 22) : scénario « Preuve en retard » non livré"]
    PR54["PR Dependabot #54 : PHPUnit 13"]
    FLAKY["Test instable RunTestsLockTest (PR #64)"]
    CLASS["Classement US-063 : plugins qui recoupent Sylius Plus"]
    US168["US-168 Contrôle de retard de la preuve (1 pt)"]
    US166["US-166 Méthodes longues et usleep (3 pts)"]
    US169["US-169 Spike PHPUnit 13 (2 pts)"]
    US170["US-170 Page Architecture de la démo (3 pts)"]
    US173["US-173 Spike distributions (2 pts)"]
    CI["ci.yaml partagé"]
    PROOF["src/Demo/Proof partagé"]
    S24["Sprint 24 : US-171 matrice, US-172 guide de surcharge, US-132, US-130"]
    DEC["Décisions de Thibaut : T-169-05, T-173-05"]

    US084 --> US168
    FLAKY --> US166
    PR54 --> US169
    RETRO --> US168
    RETRO --> US166
    CRIT --> US170
    CRIT --> US173
    CLASS --> US173
    US168 --> CI
    CI --> US166
    US168 --> PROOF
    PROOF --> US170
    US169 --> DEC
    US173 --> DEC
    US170 --> S24
    US173 --> S24
```

## Lecture

| Story | Dépend de | Nature | Bloquant |
|-------|-----------|--------|----------|
| US-168 | Règle de comptage de `ProofCollector` | Code existant à partager | non |
| US-166 | US-168, à cause du fichier `ci.yaml` | Séquencement, si la garde passe par la CI | non (garde possible dans un test) |
| US-170 | US-168, à cause de `src/Demo/Proof` (lecture de `ProofReader`) | Séquencement | non |
| US-169 | PR #54 ouverte | Branche d'essai | non |
| US-173 | Classement US-063 (`docs/vitrine-plugins.md`) | Règle de composition des packs | non |
| US-171, US-172 (sprint 24) | US-170 (page à compléter), US-173 (forme des distributions) | Suite | non pour ce sprint |
| Clôture des spikes | Décisions de Thibaut | Action humaine | oui, pour passer en done |
