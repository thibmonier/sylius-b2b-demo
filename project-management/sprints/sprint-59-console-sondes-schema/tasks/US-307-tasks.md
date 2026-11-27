# US-307 — Commande `tmonier:health:check` : tâches

> Story : [US-307](../../../backlog/user-stories/US-307.md) · 3 points · Must · Dépôt : `sylius-platform-health-plugin` · Démarre après la fusion d'US-303 (règle 27)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-307-00 | [DOC] | Fiche US-307 écrite à la planification, avec le tableau de cas aux bornes des codes de sortie (A-58-4) ; `unknown` donne 1 (décision de Thibaut du 2026-11-26) | 0,5 h | — | ✅ Fait |
| T-307-01 | [TEST] | Tests rouges : une ligne de test par ligne du tableau de cas ; JSON valide ; filtres répétables ; aucune lecture du cache ; rien de masqué par la page n'apparaît | 2,5 h | US-303 fusionnée | ✅ Fait |
| T-307-02 | [BE] | Commande sur l'exécuteur, sorties texte et JSON, filtres, codes 0/1/2/3 | 2,5 h | T-307-01 | ✅ Fait |
| T-307-03 | [DOC] | Traductions FR/EN ; README EN + FR (CI après déploiement, cron, `railway ssh`) ; CHANGELOG `[Unreleased]` | 1 h | T-307-02 | ✅ Fait |

## Fin de tâche

- T-307-01 : rouge d'assertion vu en CI sur les deux suites (règle 26).
- T-307-02 : `php bin/console tmonier:health:check` fonctionne sur l'application de test et rend le code attendu.
