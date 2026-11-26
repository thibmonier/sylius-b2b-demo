# US-295 — Sondes de files et de cache : tâches

> Story : [US-295](../../../backlog/user-stories/US-295.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · Démarre après la fusion d'US-294 (règle 27) · **✅ Done** : plugin PR #16, fusionnée le 2026-11-26 (e20ec44) ; rouge 71bc8be vu en CI sur les 10 combinaisons (unitaires : 25 échecs d'assertion, non unitaires : 1), vert 2e7d584, doc c43e97a

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-295-01 | [TEST] | Tests rouges : messages en attente sous et au-delà du seuil, âge du plus ancien (15 min, 1 h), transport non comptable et non Doctrine (`unknown`), transport d'échec vide, non vide, au-delà de 100, absent ; cache en panne ; dossier non inscriptible (chemin relatif seulement) | 3 h | US-294 fusionnée | ✅ Fait |
| T-295-02 | [BE] | `data.messenger_pending` (`MessageCountAwareInterface`), `data.messenger_oldest` (colonne `available_at` du transport Doctrine), `data.messenger_failed` ; transports `sync://` et `in-memory://` ignorés | 4 h | T-295-01 | ✅ Fait |
| T-295-03 | [BE] | `environment.cache_pool` (clé témoin propre au plugin, supprimée aussitôt) et `environment.writable_dirs` (`var/cache`, `var/log`, dossier des médias) | 2 h | T-295-01 | ✅ Fait |
| T-295-04 | [DOC] | Traductions FR/EN, README EN + FR (cinq sondes, seuils, transport d'échec recommandé), CHANGELOG `[Unreleased]` | 1,5 h | T-295-02, T-295-03 | ✅ Fait |

## Fin de tâche

- T-295-01 : rouge prouvé en CI sur les deux suites (règle 26) ; commit rouge propre à PHPStan et ECS (règle 31).
- T-295-02 : aucune sonde ne consomme, relance ni supprime de message ; aucun DSN affiché.
- T-295-03 : aucun chemin absolu du serveur rendu.
- Outils de qualité dans Docker (règle 28) ; pas de tag (règle 30).
