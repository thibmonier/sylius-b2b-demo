# US-308 — Point d'entrée HTTP de la santé : tâches

> Story : [US-308](../../../backlog/user-stories/US-308.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · Release v0.6.0 dans T-TECH-60-03, sur GO, avant US-321

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-308-01 | [TEST] | Documentation de Railway relue (contrôle sans en-tête : à confirmer) ; nœud de configuration `http` ; tests rouges, une ligne par ligne du tableau de cas, plus un test « aucune fuite » (version, chemin, exception) | 2 h | — | 🔲 À faire |
| T-308-02 | [BE] | Contrôleur : `hash_equals`, 404 sans jeton valide, niveau global ou détail, niveau « vivant » optionnel, lecture du cache, limiteur par IP ; `symfony/rate-limiter` compatible Sylius 2.2 et 2.3 | 4 h | T-308-01 | 🔲 À faire |
| T-308-03 | [DOC] | README EN/FR « Exposer la santé en HTTP » (sécurité, Railway, différence avec un 503 sur migrations en attente), CHANGELOG `[Unreleased]` | 1 h | T-308-02 | 🔲 À faire |

## Fin de tâche

- T-308-01 : rouge d'assertion vu en CI sur les 10 combinaisons (règle 26).
- T-308-02 : `curl` sur l'application de test : 404 sans jeton, 200 avec, 429 à la 31e requête.
