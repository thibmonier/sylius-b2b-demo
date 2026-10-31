# Dépendances du Sprint 42

> Ce fichier documente les dépendances entre les stories et les tâches du sprint 42.

## Graphe des dépendances globales

```mermaid
graph TD
    subgraph "Actions techniques (T-TECH-42)"
        T01[T-TECH-42-01: Garde-fou CSS]
        T02[T-TECH-42-02: bin/plugin-push-all]
        T03[T-TECH-42-03: Montant maquette copilote]
        T04[T-TECH-42-04: Bump copilote ^1.1]
    end

    subgraph "US-213 : Ajout par référence"
        U213[US-213: Ajout par référence et état du panier]
        U213_PLUGIN[Services plugin copilot]
        U213_DEMO[Cassettes + fixtures démo]
    end

    subgraph "US-215 : Mode rejoué démo"
        U215[US-215: Mode rejoué et scénarios]
        U215_REPLAY[Enrichissement replay adapter]
        U215_HELP[Page aide scénarios]
    end

    subgraph "US-237 : Parcours guidé"
        U237[US-237: Parcours guidé copilote]
        U237_TOUR[Tour config + étapes]
        U237_TEST[Tests fonctionnels]
    end

    T04 -.bump plugin.-> U213
    T04 -.bump plugin.-> U215
    U213 --> U213_PLUGIN
    U213_PLUGIN --> U213_DEMO

    U215 --> U215_REPLAY
    U215_REPLAY --> U215_HELP

    U237 --> U237_TOUR
    U237_TOUR --> U237_TEST

    style T01 fill:#e1f5ff
    style T02 fill:#e1f5ff
    style T03 fill:#e1f5ff
    style T04 fill:#e1f5ff
    style U213 fill:#d4edda
    style U213_PLUGIN fill:#d4edda
    style U213_DEMO fill:#d4edda
    style U215 fill:#fff3cd
    style U215_PLUGIN fill:#fff3cd
    style U215_HELP fill:#fff3cd
    style U237 fill:#f8d7da
    style U237_TOUR fill:#f8d7da
    style U237_TEST fill:#f8d7da
```

## Dépendances inter-stories

| Story | Dépend de | Type | Notes |
|-------|-----------|------|-------|
| US-213 | T-TECH-42-04 | Souhaitable | Bump copilote ^1.1 avant d'ajouter les nouvelles cassettes |
| US-215 | T-TECH-42-04 | Souhaitable | Idem |
| US-237 | US-212 (S41) | Externe (done) | Parcours guidé nécessite le copilote fonctionnel |

## Dépendances intra-story

### US-213 : Ajout par référence et état du panier

Flux TDD strict :
1. Lecture ADR 0018 + cassettes existantes
2. RED-GREEN pour chaque scénario (ajout par référence, état du panier)
3. Cassettes démo FR/EN
4. Tests fonctionnels plugin + démo
5. Documentation

### US-215 : Mode rejoué de la démo publique

Flux séquentiel :
1. Analyse du replay adapter existant
2. Scénarios supplémentaires (cassettes)
3. Gestion des variantes de formulation
4. Page d'aide scénarios
5. Tests fonctionnels

### US-237 : Parcours guidé copilote

Flux séquentiel :
1. Lecture du système de tours existant
2. Configuration du parcours copilote
3. Tests fonctionnels
4. Captures

## Workers recommandés

| Worker | Stories | Parallélisme | Notes |
|--------|---------|--------------|-------|
| wtech | T-TECH-42-01 à 04 | Oui (4 tâches indépendantes) | Terminer T-TECH-42-04 AVANT US-213/US-215 |
| w213 | US-213 | Non (TDD strict) | Peut être parallèle avec w215 (fichiers disjoints) |
| w215 | US-215 | Non (séquentiel) | Peut être parallèle avec w213 |
| w237 | US-237 | Non (séquentiel) | Après wtech (le copilote doit être fonctionnel) |

## Contraintes

- **RAM** : Au plus 2 workers lancent des tests en même temps (8 Go RAM)
- **Ordre** : wtech d'abord (T-TECH-42-04 souhaitable avant US-213/US-215), puis w213 et w215 en parallèle, puis w237
- **Plugin** : Les modifications du plugin copilote (US-213) nécessitent un tag avant intégration démo

## Chemin critique

```
T-TECH-42-04 → US-213 (plugin + démo) → US-237 (parcours)
```

Le chemin critique passe par US-213 (5 pts) puis US-237 (3 pts). US-215 (3 pts) peut se faire en parallèle.
