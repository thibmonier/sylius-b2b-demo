# US-289 — Encart du tableau de bord : tâches

> Story : [US-289](../../../backlog/user-stories/US-289.md) · 3 points · Must · Dépôt : `sylius-platform-health-plugin` · Démarre quand US-291 est fusionnée et que le point de contrôle du 2026-12-16 la maintient · **Sacrifiée en premier** si le sprint dérive

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-289-01 | [TEST] | Tests rouges : un feu par catégorie avec lien, tableau de bord rendu sans exécuter de sonde, cache partagé avec la page, 403 hors rôle, échec silencieux | 2 h | US-291 fusionnée, point de contrôle | 🔲 À faire |
| T-289-02 | [FE-WEB] | Hook du tableau de bord, Live Component en rendu différé, lecture du cache mutualisé, statut écrit | 3 h | T-289-01 | 🔲 À faire |
| T-289-03 | [BE] | Visibilité par rôle, exception de l'encart journalisée sans effet sur le tableau de bord | 1 h | T-289-01 | 🔲 À faire |
| T-289-04 | [DOC] | Traductions FR/EN, capture du tableau de bord, CHANGELOG | 1 h | T-289-02, T-289-03 | 🔲 À faire |

## Fin de tâche

- T-289-01 : rouge vu en CI sur les deux suites (règle 26) ; un test compte les exécutions de sonde pendant le rendu du tableau de bord.
- T-289-02 : aucun gabarit de Sylius surchargé ; hook déclaré dans `config/twig_hooks/admin.yaml`.
- Retirée au point de contrôle : les quatre tâches repassent au backlog (sprint 58), les statuts passent à `cancelled` avec le motif.
