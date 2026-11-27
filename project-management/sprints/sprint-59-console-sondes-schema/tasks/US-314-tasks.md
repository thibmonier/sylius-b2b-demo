# US-314 — CI des plugins, suite non unitaire toujours exécutée : tâches

> Story : [US-314](../../../backlog/user-stories/US-314.md) · 2 points · Should · Dépôts : les 30 plugins sans `if: ${{ !cancelled() }}` · En parallèle des autres stories (fichier distinct : `.github/workflows/build.yaml`)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-314-01 | [OPS] | Preuve sur un dépôt : commit rouge jetable qui fait échouer un test unitaire, pas non unitaire exécuté dans le même run, puis commit qui le retire | 1 h | — | ✅ Fait |
| T-314-02 | [OPS] | Les 29 autres dépôts : une PR `ci:` chacun (même ligne et même commentaire que health #5), zizmor sans nouveau constat, liste des PR et de leur état dans la fiche | 3 h | T-314-01 | ✅ Fait |

## Fin de tâche

- Une liste unique des 30 PR transmise à Thibaut pour la fusion ; aucune version de plugin.
