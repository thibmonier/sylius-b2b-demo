# US-323 — CI de la démo en moins de 10 minutes : tâches

> Story : [US-323](../../../backlog/user-stories/US-323.md) · 3 points · Should · Dépôt : `sylius-b2b-demo` · Première PR de la démo du sprint

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-323-01 | [OPS] | `--log-junit` dans l'étape PHPUnit ; les 20 classes de tests les plus lentes publiées dans le résumé du job ; classes anormalement lentes corrigées ou notées | 2 h | — | ✅ Done |
| T-323-02 | [OPS] | ECS, PHPStan et Rector dans un job parallèle, toujours bloquants | 1 h | — | ✅ Done |
| T-323-03 | [OPS] [TEST] | PHPUnit en 4 jobs (matrice), chacun avec sa base et ses fixtures ; découpage par fichier équilibré d'après T-323-01 ; `--coverage-php` par morceau, fusion `phpcov`, seuil `COVERAGE_MIN_LINES` inchangé et check `coverage` publié par le job de fusion ; contrôle « aucun test perdu », dont le rouge est vu en CI (règle 39) ; seuil appliqué sur la couverture fusionnée, rouge vu | 5 h | T-323-01 | ✅ Done |
| T-323-04 | [DOC] | `docs/tests-locaux.md` si la commande locale change ; durée du chemin critique mesurée sur deux runs de `main` après fusion, comparée aux 20 minutes du run 46136950984 | 1 h | T-323-03 | 🔲 À faire |

**Total** : 9 h.

## Fin de tâche

- `bin/proof-refresh` lit toujours le check `coverage` ; `bin/proof-check` vert.
- Taux de couverture fusionné égal à celui d'une exécution unique à 0,1 point près (comparaison dans la PR).
