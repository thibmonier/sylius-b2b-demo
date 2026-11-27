# US-303 — Sondes facultatives : tâches

> Story : [US-303](../../../backlog/user-stories/US-303.md) · 5 points · Must · Dépôt : `sylius-platform-health-plugin` · Avant US-307 dans le même dépôt (règle 27)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-303-00 | [DOC] | Fiche US-303 écrite à la planification (mécanisme : `suggest` et `require-dev`, fichier de services à part chargé si l'interface existe, catégorie vide masquée) | 1 h | — | ✅ Fait |
| T-303-01 | [TEST] | Tests rouges : plugin de démonstration dans `tests/Fixture`, sonde taguée et affichée quand l'interface existe ; conteneur booté sans erreur et sans sonde quand la vérification dit qu'elle n'existe pas ; catégorie vide masquée sur la page et dans l'encart ; sonde fournie qui lève une exception, `unknown` ; composer.json du plugin de démonstration lu | 3 h | — | ✅ Fait |
| T-303-02 | [BE] | Extension du plugin de démonstration avec la vérification injectable ; masquage des catégories vides | 3 h | T-303-01 | ✅ Fait |
| T-303-03 | [DOC] | README EN + FR « Fournir une sonde depuis son plugin » (exemple complet à copier) ; CHANGELOG `[Unreleased]` | 1,5 h | T-303-02 | ✅ Fait |

## Fin de tâche

- T-303-01 : rouge d'assertion vu en CI sur les deux suites, sur les 10 combinaisons (règle 26).
- T-303-02 : aucun `require` vers le plugin de santé dans l'exemple ; Symfony 6.4 à 8.0.
