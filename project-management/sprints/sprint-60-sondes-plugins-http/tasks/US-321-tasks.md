# US-321 — Démo : `/healthz` servi par le plugin de santé : tâches

> Story : [US-321](../../../backlog/user-stories/US-321.md) · 2 points · Must · Dépôt : `sylius-b2b-demo` · Après la release santé v0.6.0

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-321-01 | [BE] [TEST] | Démo en santé `^0.6` ; sondes de l'application `demo.configuration` et `demo.reset` (catégorie `environment`) ; tests rouges vus en CI | 2 h | release v0.6.0 | 🔲 À faire |
| T-321-02 | [OPS] [TEST] | Route du plugin sur `/healthz`, `liveness: true`, jetons lus dans l'environnement ; `HealthController`, la route `/health` et le limiteur `demo_health` retirés ; `HealthzTest` remplacé ; `DemoGateListener` vérifié ; `proof.json` ; vérification de `data.pending_migrations` en `critical` | 3 h | T-321-01 | 🔲 À faire |

## Fin de tâche

- Rouge vu en CI (règle 26, A-58-5).
- Après déploiement : `/healthz` vu à 200 sans en-tête, relevé `tmonier:health:check --format=json` noté dans la PR de suivi (règle 37).
