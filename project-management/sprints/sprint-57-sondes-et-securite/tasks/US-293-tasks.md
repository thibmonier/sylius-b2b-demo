# US-293 — Sondes d'environnement : tâches

> Story : [US-293](../../../backlog/user-stories/US-293.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · **Première story du sprint** : démarre après T-TECH-57-02 (démo) et T-TECH-57-03 (CI du plugin) fusionnées

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-293-01 | [TEST] | Tests rouges des six sondes : sain, fin de support proche et dépassée (horloge injectée), extension manquante, debug en prod, branche absente de la table, table périmée | 3 h | T-TECH-57-02, T-TECH-57-03 | 🔲 À faire |
| T-293-02 | [BE] | `EndOfSupportTable` versionnée (`2026-12`), sondes PHP et Sylius, sonde Symfony par les constantes `Kernel::END_OF_MAINTENANCE` et `END_OF_LIFE` | 3,5 h | T-293-01 | 🔲 À faire |
| T-293-03 | [BE] | Sondes extensions, OPcache, `APP_DEBUG` ; seuils configurables ; liens d'action (documentation interne) | 3,5 h | T-293-01 | 🔲 À faire |
| T-293-04 | [DOC] | Traductions FR/EN des six sondes, README (liste, seuils, maintenance de la table), CHANGELOG | 1,5 h | T-293-02, T-293-03 | 🔲 À faire |

## Fin de tâche

- T-293-01 : rouge valide = échec d'assertion vu en CI sur le SHA poussé, **unitaire et non unitaire** (règle 26) ; les stubs rouges passent PHPStan et ECS ; aucune dépendance à la date du jour (horloge injectée).
- T-293-02 : chaque sonde dit la version de la table ; une branche absente rend `unknown`, jamais `ok` ; `identifier()` est une constante littérale (décision A).
- T-293-03 : aucune valeur de variable d'environnement rendue ; aucun appel réseau ; `unknown` si l'API OPcache est restreinte.
- Outils de qualité lancés dans Docker (règle 28).
