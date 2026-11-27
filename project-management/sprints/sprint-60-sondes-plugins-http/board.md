# Tableau du sprint 60 — Sondes des plugins B2B et santé exposée en HTTP

> Vue établie le 2026-11-27 depuis `.bmad/sprint-status.yaml` (`sprint-60`). À régénérer par `/bear-workflow:status --board` ; ne pas éditer à la main.

**Objectif** : les trois plugins B2B les plus exposés remontent leur propre état sur la page de santé, et la démo est surveillée par le point d'entrée HTTP du plugin au lieu de son `/healthz` maison.

| 🔲 À faire (12) | 🔄 En cours (0) | 👀 Review (0) | ✅ Fait (2) |
|-----------------|-----------------|---------------|-------------|
| T-304-01 · T-304-02 · T-304-03 | | | T-TECH-60-01 · T-TECH-60-02 |
| T-308-01 · T-308-02 · T-308-03 | | | |
| T-321-01 · T-321-02 | | | |
| T-319-01 · T-319-02 | | | |
| T-TECH-60-03 · T-TECH-60-04 | | | |

## Par story

| Story | Pts | Dépôt | État |
|-------|-----|-------|------|
| [US-304](tasks/US-304-tasks.md) | 5 | webhook-outbox, purchase-approval, trade-credit | 🔲 Prête |
| [US-308](tasks/US-308-tasks.md) | 5 | santé | 🔲 Prête |
| [US-321](tasks/US-321-tasks.md) | 2 | démo | 🔲 Prête (après santé v0.6.0) |
| [US-319](tasks/US-319-tasks.md) | 2 | bulk-actions | 🔲 Prête (enquête bornée à 4 h) |

Points engagés : 14/15 (93 %), livrés : 0/14 · Tâches : 2/14 faites · Heures : 2 h/33,5 h · Sprint planifié le 2026-11-27
