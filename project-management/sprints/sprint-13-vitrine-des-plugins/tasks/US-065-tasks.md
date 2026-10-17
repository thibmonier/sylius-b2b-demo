# Tâches — US-065 : Captures d'écran manquantes et vérification des existantes

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 4
- **Sprint** : sprint-13-vitrine-des-plugins
- **Dépendances** : US-063 (liste des plugins purement techniques : `order-hold` seul, décision du 2026-10-17) ; démo locale (`compose.yaml`)

## Règles

- Captures réelles, prises dans un navigateur piloté depuis la démo locale (`docker compose`) ; application de test du plugin si absent de la démo. Aucune donnée réelle, aucun secret, aucun identifiant visible.
- Dépôts de plugins : `git fetch` et branche vérifiés ; fichiers dans `docs/images/` seulement, sur la branche locale d'US-067.
- `customer-asset-management` : reporté à US-056 et suivantes ; plugins purement techniques exclus.
- Tâches sacrifiables ⚠️ : T-065-05, T-065-07 ; ordre dans [README.md](./README.md).
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-065-01 | [DOC] | Audit des images des 19 plugins qui en ont : face boutique et admin couvertes, fraîcheur ; liste des manques | 1.5h | T-063-05 | ✅ |
| T-065-02 | [DOC] | Protocole de capture : fenêtre, langue, jeu de données, nommage `shop-*` et `admin-*`, poids (cible < 300 Ko), contrôle des données visibles ; liste des captures à prendre | 0.5h | T-065-01 | ✅ |
| T-065-03 | [OPS] | Démo locale : `docker compose`, remise à zéro des données, connexion de démo, vérification | 1h | — | ✅ |
| T-065-04 | [FE-WEB] | Captures des plugins sans image présents dans la démo (`channel-access`, `registration-approval`, `punchout-cxml`) et complément `command-palette` | 2.5h | T-065-02, T-065-03 | ✅ |
| T-065-05 | [FE-WEB] | ⚠️ Captures de comblement relevées par l'audit (boutique ou admin manquants) | 1h | T-065-04 | ✅ |
| T-065-06 | [BE] | Insertion des images dans les README EN et FR (sections Screenshots) des plugins concernés, sur les branches d'US-067 | 1h | T-065-04, T-067-03 | 🔲 |
| T-065-07 | [TEST] | ⚠️ Contrôle : liens d'images résolus, poids, aucune donnée réelle ni secret visible | 0.5h | T-065-06 | 🔄 |
| T-065-08 | [REV] | Revue | 0.5h | T-065-06 | 🔲 |

**Total estimé : 8.5h** (8 tâches), dont 1.5h sacrifiables (T-065-05, T-065-07).
