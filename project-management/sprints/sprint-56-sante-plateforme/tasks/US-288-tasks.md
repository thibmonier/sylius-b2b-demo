# US-288 — Page d'administration, synthèse par catégorie et rendu différé : tâches

> Story : [US-288](../../../backlog/user-stories/US-288.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · Suit US-287 dans le même dépôt (un seul worker, un seul worktree)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-288-01 | [TEST] | Tests rouges : synthèse et ordre, squelette rendu sans exécuter de sonde, cache mutualisé, rafraîchissement, « non mesurable », aucune sonde | 2,5 h | T-287-03 | 👀 Review (health #2) |
| T-288-02 | [FE-WEB] | Page sous *Configuration*, entrée de menu, rendu différé (technique tranchée par le spike), synthèse par catégorie, détail replié, lien d'action par route | 4 h | T-288-01 | 👀 Review (health #2) |
| T-288-03 | [BE] | Cache court mutualisé, TTL configurable, rafraîchissement explicite qui ignore le cache | 2 h | T-288-01 | 👀 Review (health #2) |
| T-288-04 | [DOC] | Traductions FR/EN, capture de la page, CHANGELOG | 1,5 h | T-288-02, T-288-03 | 👀 Review (health #2) |

## Fin de tâche

- T-288-01 : rouge valide vu en CI (mêmes règles que T-287-02).
- T-288-02 : la page s'affiche sans exécuter de sonde au premier rendu ; une sonde en échec ne casse pas la page.
- T-288-04 : capture jointe à la PR ; aucune sonde réelle annoncée (le README dit que le plugin n'embarque pas encore de diagnostic).
