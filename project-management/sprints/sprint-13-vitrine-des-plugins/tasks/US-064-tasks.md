# Tâches — US-064 : Bloc standard, outil de contrôle et README FR du lot A

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 4
- **Sprint** : sprint-13-vitrine-des-plugins
- **Dépendances** : US-063 (charte de ton)

## Règles

- Aucun lien vers le site ni la page contact (décision D1, US-068). Lien vers la démo : dépôt public tant que la démo est privée.
- L'outil vit dans la démo ; les dépôts de plugins ne reçoivent que des fichiers de documentation, sur une branche locale `docs/readme-fr-demo` partie de `main` à jour (`git fetch` et branche vérifiés).
- Aucun push sans GO. Tâche sacrifiable ⚠️ : T-064-06 ; ordre dans [README.md](./README.md).
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-064-01 | [DOC] | Livraison par PR par dépôt (retenue le 2026-10-17) : relevé des 24 workflows (`paths-ignore`), de la protection de branche et de la branche courante de chaque dépôt (lecture seule) ; blocages éventuels de la CI | 1h | — | ✅ |
| T-064-02 | [DOC] | Bloc standard EN et FR (démo, dépôt public, mention « non présent dans la démo »), en-tête de liens croisés ; ton selon la charte | 1h | T-063-04, T-064-01 | ✅ |
| T-064-03 | [TEST] | Tests rouges de `bin/vitrine-readme` : bloc présent, idempotence, lien croisé en tête, structure identique, blocs de code intacts, aucun lien de site, termes interdits | 1.5h | T-064-02 | ✅ |
| T-064-04 | [BE] | `bin/vitrine-readme` (application idempotente sur un dépôt, `--check` en lecture seule sur les 24) | 2h | T-064-03 | ✅ |
| T-064-05 | [DOC] | `README.fr.md` du lot A (12 plugins, `admin-order-creation` à `facturx`) ; blocs de code et identifiants intacts | 3h | T-064-04 | ✅ |
| T-064-06 | [TEST] | ⚠️ Contrôle structurel du lot A (titres, tableaux, liens) rejoué sur les 12 dépôts | 0.5h | T-064-05 | ✅ |
| T-064-07 | [REV] | Revue | 0.5h | T-064-06 | 🔲 |

**Total estimé : 9.5h** (7 tâches), dont 0.5h sacrifiable (T-064-06).
