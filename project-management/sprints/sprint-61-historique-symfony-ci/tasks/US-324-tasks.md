# US-324 — Démo en Symfony 8.2 : tâches

> Story : [US-324](../../../backlog/user-stories/US-324.md) · 3 points · Should · Dépôt : `sylius-b2b-demo` · Après la fusion d'US-323

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-324-01 | [OPS] [TEST] | Rouge : test qui exige Symfony 8.2 ou plus, vu en CI. Vert : `extra.symfony.require: 8.2.*`, `composer update "symfony/*" -W` avec Composer 2.10.3 ; diff du lock relu (seuls `symfony/*` et dépendances directes) ; recettes vérifiées (`composer recipes`) | 3 h | US-323 fusionnée | 🔲 À faire |
| T-324-02 | [TEST] | Dépréciations : nombre avant (240) et après dans la PR ; celles apportées par 8.2 corrigées dans la démo, celles des dépendances listées avec leur source | 2 h | T-324-01 | 🔲 À faire |
| T-324-03 | [DOC] [OPS] | Documents de la pile (versions affichées), `compat-refresh` puis `proof-refresh` relus ; après déploiement, `environment.symfony_version` vue en `ok` (règle 37) | 1 h | T-324-02 | 🔲 À faire |

**Total** : 6 h.

## Fin de tâche

- Un plugin qui casse sous Symfony 8.2 n'est pas corrigé ici : il est signalé à Thibaut et inscrit au backlog.
