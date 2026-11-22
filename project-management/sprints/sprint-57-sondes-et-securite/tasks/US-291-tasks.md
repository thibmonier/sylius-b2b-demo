# US-291 — Accessibilité AA : tâches

> Story : [US-291](../../../backlog/user-stories/US-291.md) · 3 points · Must · Dépôt : `sylius-platform-health-plugin` · Démarre quand US-290 est fusionnée (règle 27)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-291-01 | [TEST] | Tests rouges : statut écrit et icône distincte par statut, tableau annoncé (`caption`, `scope`), région d'état, contraste calculé en clair et en sombre | 2 h | US-290 fusionnée | 🔲 À faire |
| T-291-02 | [FE-WEB] | Gabarits : `<table>` légendée, `<details>` natif, jetons CSS suivant `data-bs-theme`, région `role="status"` et `aria-busy`, mouvement réduit | 3,5 h | T-291-01 | 🔲 À faire |
| T-291-03 | [REV] | Revue manuelle : clavier seul, lecteur d'écran, mode sombre, mouvement réduit ; constat consigné dans la PR | 1,5 h | T-291-02 | 🔲 À faire |
| T-291-04 | [DOC] | CHANGELOG, section accessibilité du README | 0,5 h | T-291-02 | 🔲 À faire |

## Fin de tâche

- T-291-01 : le rouge est le test de contraste (un jeton qui donne 2,8:1 le fait échouer) ; vu en CI sur les deux suites (règle 26).
- T-291-03 : constat, pas déclaration ; capture du mode sombre jointe à la PR.
