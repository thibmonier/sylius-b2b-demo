# US-309 — Historique de santé : tâches

> Story : [US-309](../../../backlog/user-stories/US-309.md) · 5 points · Must · Dépôts : `sylius-platform-health-plugin` (T-309-01 à 04, release v0.7.0 sur GO), puis `sylius-b2b-demo` (T-309-05)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-309-01 | [DB] [TEST] | Entité `HealthRecord` et mapping XML, table `tmonier_platform_health_record` (`identifier`, `category`, `status`, `recorded_at`, index identifiant + date) ; migration du plugin ; configuration `history.retention_days` (30, minimum 1) ; tests rouges du tableau de cas | 3 h | — | 🔲 À faire |
| T-309-02 | [BE] [TEST] | Commande `tmonier:health:record` : mesure sans cache, une ligne par sonde à la même date, écriture DBAL, purge au-delà de `retention_days` ; sonde en exception relevée `unknown` ; base injoignable : code 1 ; bornes J-30 et J-31 | 3 h | T-309-01 | 🔲 À faire |
| T-309-03 | [FE-WEB] [TEST] | Frise de 30 cases dépliée sous chaque sonde : pire statut du jour, case « aucun relevé », texte par case, rendu serveur sans JavaScript ; traductions ; 403 sans le rôle sur la page et le composant | 4 h | T-309-01 | 🔲 À faire |
| T-309-04 | [DOC] | README EN/FR : section « Historique », cron conseillé (horaire), rétention ; CHANGELOG `[Unreleased]`, puis PR de release v0.7.0 (règle 30) | 1 h | T-309-02, T-309-03 | 🔲 À faire |
| T-309-05 | [OPS] [TEST] | Démo en `^0.7` : `--dump-sql` de production relevé en lecture seule et écrit dans la fiche avant le premier test (règle 35) ; ligne horaire `tmonier:health:record` dans `docker/crontab` et son battement (17 commandes) ; migration du plugin au preDeploy ; test de la page (frise présente) ; `proof.json` (règle 34) | 3 h | release v0.7.0, US-324 fusionnée | 🔲 À faire |

**Total** : 14 h.

## Fin de tâche

- Rouges vus en CI sur la matrice du plugin et dans la démo (règles 26 et 39).
- Après déploiement de la démo : relevé `tmonier:health:check --format=json` noté dans la PR de suivi (règle 37), frise vue sur la page avec au moins un relevé.
