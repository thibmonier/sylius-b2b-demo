# US-294 — Sondes de données : tâches

> Story : [US-294](../../../backlog/user-stories/US-294.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · **Première story du sprint**

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-294-01 | [TEST] | Tests rouges : base saine, lente (seuil 500 ms), injoignable (DSN masqué), migrations en attente et exécutées inconnues, dérive du schéma, dérive trop longue (`unknown`), tables sous et au-delà du seuil, plateforme non gérée | 3 h | | 🔲 À faire |
| T-294-02 | [BE] | `data.connection` et `data.pending_migrations` (`doctrine/migrations`), seuils configurables, liens de documentation | 3 h | T-294-01 | 🔲 À faire |
| T-294-03 | [BE] | `data.schema_drift` (nombre d'instructions de `SchemaTool::getUpdateSchemaSql`, jamais le SQL, budget de temps) et `data.table_growth` (`information_schema.TABLES.TABLE_ROWS` ou `pg_class.reltuples`, tables déclarées) | 4 h | T-294-01 | 🔲 À faire |
| T-294-04 | [DOC] | Traductions FR/EN, README EN + FR (quatre sondes, seuils, déclaration des tables), CHANGELOG `[Unreleased]` | 1,5 h | T-294-02, T-294-03 | 🔲 À faire |

## Fin de tâche

- T-294-01 : rouge valide = échec d'assertion vu en CI sur le SHA poussé, **unitaire et non unitaire** (règle 26) ; le commit rouge passe PHPStan et ECS, sortie lue sans filtre (règle 31) ; matrice MySQL et PostgreSQL.
- T-294-02 : aucun DSN, hôte ni mot de passe dans le résumé ou le détail, même quand l'exception les porte.
- T-294-03 : aucune requête `COUNT(*)` (vérifié par un test sur les requêtes exécutées) ; `unknown` sur dépassement du budget, jamais d'exception.
- Outils de qualité dans Docker (règle 28) ; pas de tag (règle 30).
