# US-292 — Traductions, README, capture, CHANGELOG et intégration à la démo : tâches

> Story : [US-292](../../../backlog/user-stories/US-292.md) · 3 points · Must · Dépôts : `sylius-platform-health-plugin` (T-292-01, 02) puis `sylius-b2b-demo` (T-292-03 à 05) · Dernière story du sprint

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-292-01 | [TEST] | Tests rouges : complétude des traductions FR/EN, aucune clé référencée absente (sondes d'US-293 comprises) | 1,5 h | US-293, US-290, US-291 fusionnées | 🔲 À faire |
| T-292-02 | [DOC] | README EN/FR réécrits (sondes, rôle, commande, encart si livré, démo, profil de l'auteur), captures réelles FR/EN clair/sombre, CHANGELOG 0.2.0 | 3 h | T-292-01 | 🔲 À faire |
| T-292-03 | [OPS] | Démo : `composer require tmonier/sylius-platform-health-plugin:^0.2`, bundle, routes, configuration, fiche `plugins.yaml`, rôle accordé aux comptes d'administration publics (`grant-role --all` au chargement des fixtures), `bin/compat-refresh`, `bin/proof-refresh` | 3,5 h | **GO et tag v0.2.0 de Thibaut**, Packagist | 🔲 À faire |
| T-292-04 | [TEST] | Démo : page visible avec le rôle, 403 sans, rôle toujours accordé au chargement des fixtures (rouge prouvé en CI) | 2 h | T-292-03 | 🔲 À faire |
| T-292-05 | [REV] | Captures de la démo relues à 1280 px, constat sur les sondes réelles de l'image (aucune sonde truquée) | 0,5 h | T-292-03 | 🔲 À faire |

## Fin de tâche

- T-292-01 : rouge vu en CI sur les deux suites (règle 26).
- T-292-02 : plus aucune mention « aucune sonde réelle » ; la PR du plugin est fusionnée par Thibaut avant le tag.
- T-292-03 : la démo ne référence que la version publiée (jamais une branche ni un dépôt local) ; si le tag n'est pas posé à la fin du sprint, T-292-03 à 05 sont reportées et dites telles à la revue (pas comptées done).
- T-292-04 : le garde d'écriture de la démo (`DemoAdminWriteGuard`) n'est pas déclenché par la page ni par le rafraîchissement.
