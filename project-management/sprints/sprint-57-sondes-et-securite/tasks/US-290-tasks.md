# US-290 — Rôle dédié, masquage des valeurs sensibles, aucune trace à l'écran : tâches

> Story : [US-290](../../../backlog/user-stories/US-290.md) · 3 points · Must · Dépôt : `sylius-platform-health-plugin` · Démarre quand US-293 est fusionnée (règle 27)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-290-01 | [TEST] | Tests rouges : 403 de la page, du point d'accès `/admin/…/_components/…` et du rafraîchissement hors rôle ; masquage (DSN, `password=`, clé longue, chemin absolu, trace) ; commande idempotente | 2,5 h | US-293 fusionnée | 🔲 À faire |
| T-290-02 | [BE] | Garde sur la route de la page, `#[IsGranted]` sur le composant, entrée de menu conditionnelle | 2,5 h | T-290-01 | 🔲 À faire |
| T-290-03 | [BE] | `SensitiveValueMasker` appliqué au rendu du résumé et du détail, détection de trace d'exécution, journalisation de la sonde fautive | 2 h | T-290-01 | 🔲 À faire |
| T-290-04 | [BE] | Commande `tmonier:platform-health:grant-role` (`--email`, `--all`), idempotente | 1,5 h | T-290-01 | 🔲 À faire |
| T-290-05 | [DOC] | README EN/FR (rôle, commande, rupture depuis 0.1.0), CHANGELOG (*Changed*) | 1 h | T-290-02, T-290-03, T-290-04 | 🔲 À faire |

## Fin de tâche

- T-290-01 : le test du point d'accès du composant est le test de la faille (A01) ; rouge vu en CI sur les deux suites (règle 26).
- T-290-02 : un 403 est un 403, pas une redirection ; aucun contrôle posé seulement dans le gabarit.
- T-290-03 : le masquage ne remplace pas la règle de conception n° 5 ; table de cas dans les tests ; aucun secret dans les journaux.
- T-290-04 : écart à signaler tout de suite si le mécanisme d'attribution change (règle 29) ; Thibaut a confirmé le mécanisme en lisant la fiche.
