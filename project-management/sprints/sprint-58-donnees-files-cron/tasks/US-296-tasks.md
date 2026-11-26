# US-296 — Battement des tâches planifiées : tâches

> Story : [US-296](../../../backlog/user-stories/US-296.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · Démarre après la fusion d'US-295 (règle 27) · Stockage en base (décision de Thibaut du 2026-11-25) · **✅ Done** : plugin PR #17, fusionnée le 2026-11-26 (87f70b9) ; rouge 5a74e40 + ac4b132 vu en CI sur les 10 combinaisons (unitaires : 16 échecs d'assertion, non unitaires : 3 ; 5a74e40 seul restait une erreur fatale sous Symfony ^8.0, helper renommé), vert c169bae et 4f64e76, doc 6c281ae

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-296-01 | [TEST] | Tests rouges : seules les commandes déclarées sont enregistrées ; à l'heure, en retard de 2 et 4 battements, en échec répété, jamais lancée (`unknown` puis `warning`) ; panne d'écriture sans effet sur le code de sortie ; configuration invalide refusée à la compilation | 3 h | US-295 fusionnée | ✅ Fait |
| T-296-02 | [DB] | Entité `Heartbeat`, mapping XML sous `config/doctrine`, dépôt (mise à jour ou insertion d'une ligne par commande), migration du plugin (MySQL et PostgreSQL) | 2,5 h | T-296-01 | ✅ Fait |
| T-296-03 | [BE] | Abonné `ConsoleEvents::TERMINATE` et `ERROR` (attrape et journalise), configuration `heartbeats` (commande et durée), sonde `scheduled_tasks.heartbeat` (pire statut, une ligne par commande, horloge injectée) | 3,5 h | T-296-02 | ✅ Fait |
| T-296-04 | [DOC] | Traductions FR/EN, README EN + FR (déclaration, exemple avec les crons de la démo, migration à lancer), CHANGELOG `[Unreleased]` | 1,5 h | T-296-03 | ✅ Fait |

## Fin de tâche

- T-296-01 : rouge prouvé en CI sur les deux suites (règle 26) ; aucun test ne dépend de l'heure réelle.
- T-296-02 : la migration passe sur MySQL et PostgreSQL de la CI ; la table n'a qu'une ligne par commande déclarée.
- T-296-03 : la sonde n'écrit rien ; l'abonné n'écrit qu'en fin de commande ; jamais d'argument ni de sortie de commande affichés.
- Outils de qualité dans Docker (règle 28) ; pas de tag (règle 30).
