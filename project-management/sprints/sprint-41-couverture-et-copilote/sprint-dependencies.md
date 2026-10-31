# Dépendances du Sprint 41

> Ce fichier documente les dépendances entre les stories et les tâches du sprint 41.

## Graphe des dépendances globales

```mermaid
graph TD
    subgraph "Actions techniques (T-TECH-41)"
        T01[T-TECH-41-01: Consigne fork strict]
        T02[T-TECH-41-02: Job coverage pilotes]
        T03[T-TECH-41-03: Montant maquette copilote]
    end

    subgraph "US-253 : Couverture tous plugins"
        U253[US-253: Coverage tous plugins]
        U253_CI[Jobs CI coverage 27 repos]
        U253_PROOF[ProofCollector + page Preuve]
    end

    subgraph "US-212 : Copilote réassort"
        U212[US-212: Réassort "mois dernier"]
        U212_PLUGIN[Services plugin copilot]
        U212_DEMO[Cassette + fixtures démo]
    end

    T02 -.pattern CI.-> U253
    U253 --> U253_CI
    U253_CI --> U253_PROOF

    T03 -.montant corrigé.-> U212
    U212 --> U212_PLUGIN
    U212_PLUGIN --> U212_DEMO

    style T01 fill:#e1f5ff
    style T02 fill:#e1f5ff
    style T03 fill:#e1f5ff
    style U253 fill:#d4edda
    style U253_CI fill:#d4edda
    style U253_PROOF fill:#d4edda
    style U212 fill:#fff3cd
    style U212_PLUGIN fill:#fff3cd
    style U212_DEMO fill:#fff3cd
```

## Dépendances inter-stories

| Story | Dépend de | Type | Notes |
|-------|-----------|------|-------|
| US-253 | US-252 (S40) | Pilote | Pattern CI coverage du pilote (T-TECH-41-02) |
| US-212 | T-TECH-41-03 | Bloquante | Montant maquette doit être corrigé avant |

## Dépendances intra-story

### US-253 : Couverture tous plugins

Flux séquentiel des tâches :
1. T-253-01 : Lire le pattern CI
2. T-253-02 à T-253-07 : Jobs coverage par lots (séquentiel par lot)
3. T-253-08 : ProofCollector lit les check runs
4. T-253-09 : proof-refresh tous plugins
5. T-253-10 à T-253-14 : Page Preuve + tests + doc

**Parallélisme possible** : Les lots 3 à 7 peuvent être traités en parallèle après le lot 2 (si plusieurs workers).

### US-212 : Copilote réassort

Flux TDD strict :
1. T-212-01 : Lecture ADR + maquette (DÉPEND de T-TECH-41-03)
2. T-212-02 à T-212-07 : RED-GREEN pour chaque service (séquentiel)
3. T-212-08 : Orchestration scénario
4. T-212-09 : Test fonctionnel plugin
5. T-212-10 à T-212-13 : Cassette + fixtures démo
6. T-212-14 à T-212-16 : Tests + doc + release

**Aucun parallélisme** : TDD strict nécessite un flux séquentiel.

## Workers recommandés

| Worker | Stories | Parallélisme | Notes |
|--------|---------|--------------|-------|
| wtech | T-TECH-41-01 à 03 | Oui (3 tâches indépendantes) | Terminer AVANT US-212 (T-TECH-41-03 bloquante) |
| w253 | US-253 | Non (lots séquentiels) | Peut être parallèle avec w212 |
| w212 | US-212 | Non (TDD strict) | Attendre T-TECH-41-03 terminée |

## Contraintes

- **RAM** : Au plus 2 workers lancent des tests en même temps (8 Go RAM)
- **Disque** : Vérifier l'espace avant les tests avec couverture (fichiers Clover XML)
- **Ordre** : wtech d'abord (T-TECH-41-03 bloquante pour w212), puis w253 et w212 en parallèle

## Chemin critique

```
T-TECH-41-03 → T-212-01 → ... → T-212-16 (US-212, 12 h)
```

Le chemin critique passe par US-212 (12 h) qui dépend de T-TECH-41-03. US-253 (8 h) peut se faire en parallèle.

**Durée minimale du sprint** : 12 h (US-212) si parallélisme optimal.
