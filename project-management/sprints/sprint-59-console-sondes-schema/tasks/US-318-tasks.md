# US-318 — Dérive du schéma de la démo : tâches

> Story : [US-318](../../../backlog/user-stories/US-318.md) · 3 points · Must · Dépôts : `sylius-b2b-predictive-analytics-plugin`, puis `sylius-b2b-demo` · En tête du sprint (« ASAP », Thibaut, 2026-11-26) · Release du plugin prédictif (v0.2.1) dans T-TECH-59-05, sur GO

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-318-01 | [TEST] | Prédictif, tests rouges : schéma issu des migrations égal au mapping (`--dump-sql` vide) sur les 10 combinaisons ; supprimer une entreprise supprime ses prédictions stockées | 2 h | — | 🔲 À faire |
| T-318-02 | [DB] | Prédictif : migration qui ajoute 5 clés étrangères et 5 index, renomme 1 index, supprime d'abord les lignes orphelines et en affiche le nombre (MySQL, MariaDB, PostgreSQL) | 2,5 h | T-318-01 | 🔲 À faire |
| T-318-03 | [DB] | Démo : cause des 4 instructions de Sylius établie, en comparant avec l'application de test Sylius vierge ; puis migration de la démo, ou filtre documenté ; `messenger_messages` en test déclaré proprement | 3 h | — | 🔲 À faire |
| T-318-04 | [TEST] | Démo : test « aucune dérive » (rouge poussé et vu en CI, A-58-5) ; il passe après T-318-03 et le passage au prédictif v0.2.1 | 1,5 h | T-318-03 | 🔲 À faire |
| T-318-05 | [DOC] | CHANGELOG `[Unreleased]` du prédictif ; fiche mise à jour (cause des 4 instructions de Sylius, choix fait) | 0,5 h | T-318-02, T-318-03 | 🔲 À faire |

## Fin de tâche

- T-318-01 : rouge d'assertion vu en CI sur les deux suites (règle 26).
- T-318-02 : la migration passe sur une base qui contient des lignes orphelines, sans échouer.
- T-318-04 : la production montre « Dérive du schéma » en `ok` après le déploiement de T-TECH-59-05.
