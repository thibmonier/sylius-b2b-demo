# US-287 — Squelette du plugin, contrat de sonde et exécution à sécurité passive : tâches

> Story : [US-287](../../../backlog/user-stories/US-287.md) · 5 points · Must · Dépôt : nouveau, `sylius-platform-health-plugin` (nom à confirmer par le spike et le GO de Thibaut)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-287-01 | [OPS] | Dépôt créé (sujets, description), squelette d'un plugin récent, `phpstan.neon` / `ecs.php` / `rector.php` sur `src` **et** `tests`, `bin/plugin-supply-chain apply`, CI verte à vide | 2 h | T-286-02 + GO de Thibaut | ✅ Done (v0.1.0) |
| T-287-02 | [TEST] | Tests rouges : trois sondes exécutées, aucune sonde, budget dépassé, exception capturée, identifiant en double au `cache:warmup` | 2,5 h | T-287-01 | ✅ Done (v0.1.0) |
| T-287-03 | [BE] | `HealthCheckInterface`, `HealthStatus`, `HealthCategory`, `HealthResult`, `HealthCheckRunner` à sécurité passive, passe de compilation de l'unicité des identifiants | 4 h | T-287-02 | ✅ Done (v0.1.0) |
| T-287-04 | [DOC] | README EN/FR : contrat, sonde maison en 15 lignes, non-objectifs ; CHANGELOG | 1,5 h | T-287-03 | ✅ Done (v0.1.0) |

## Fin de tâche

- T-287-01 : les trois outils de qualité couvrent `src` et `tests` dès le premier commit, vérifié en revue (report du thème, semaine du 17 novembre).
- T-287-02 : rouge valide = échec d'assertion vu en CI, pas ECS, pas PHPStan, pas une erreur de configuration ; le stub rouge passe PHPStan sans `@phpstan-ignore` (A-55-2).
- T-287-03 : une sonde qui lève rend `unknown`, l'exception est journalisée, rien ne remonte à l'appelant, aucun message brut n'atteint l'écran.
- Liste de contrôle du squelette (A-55-1) : `assets/admin/entrypoint.js`, `assets/admin/controllers.json`, `assets/shop/entrypoint.js`, `.env` et `.env.test` de l'application de test écrits neufs, jamais copiés.
