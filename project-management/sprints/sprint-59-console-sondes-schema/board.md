# Tableau du sprint 59 — Console, sondes des plugins et schéma de la démo

> Vue établie le 2026-11-27 depuis `.bmad/sprint-status.yaml` (`sprint-59`). À régénérer par `/bear-workflow:status --board` ; ne pas éditer à la main.

**Objectif** : le diagnostic de santé se lit aussi en console avec un code de sortie utile, un plugin peut fournir sa sonde sans imposer le plugin de santé, et la démo n'a plus de dérive de schéma.

| 🔲 À faire (0) | 🔄 En cours (1) | 👀 Review (0) | ✅ Fait (19) |
|-----------------|-----------------|---------------|-------------|
| | T-TECH-59-05 (releases faites ; reste « Dérive du schéma » `ok` en production) | | T-TECH-59-01 · T-TECH-59-02 (#235) · T-TECH-59-03 (#236) · T-TECH-59-04 |
| | | | T-318-01 · T-318-02 (prédictif #16) · T-318-03 · T-318-04 (démo #234) · T-318-05 |
| | | | T-303-00 · T-303-01 · T-303-02 · T-303-03 (health #19) |
| | | | T-307-00 · T-307-01 · T-307-02 · T-307-03 (health #20, #21) |
| | | | T-314-01 · T-314-02 (30 PR `ci:`) |

## Par story

| Story | Pts | Dépôt | État |
|-------|-----|-------|------|
| [US-318](tasks/US-318-tasks.md) | 3 | prédictif, démo, santé | 👀 Review : code fusionné, prédictif v0.2.1 et santé v0.5.0 publiés, sonde à voir `ok` en production |
| [US-303](tasks/US-303-tasks.md) | 5 | santé | ✅ Fait (health #19, v0.5.0) |
| [US-307](tasks/US-307-tasks.md) | 3 | santé | ✅ Fait (health #20 et #21, v0.5.0) |
| [US-314](tasks/US-314-tasks.md) | 2 | 30 plugins | ✅ Fait (30 PR, CI de `main` verte) |

Points engagés : 13/15 (87 %), livrés : 10/13 (US-318 en review) · Tâches : 19/20 faites · Heures : 33,5 h/36,5 h · Sprint planifié le 2026-11-26
