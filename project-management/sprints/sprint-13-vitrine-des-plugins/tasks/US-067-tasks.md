# Tâches — US-067 : README FR du lot B et livraison des 24 dépôts

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 3
- **Sprint** : sprint-13-vitrine-des-plugins
- **Dépendances** : US-064

## Règles

- Une PR par dépôt (décision du 2026-10-17) : Claude ouvre les PR, vérifie le rendu GitHub et la CI, présente le lot ; **un GO unique** de l'utilisateur merge les 24 PR.
- Dépôt sale ou hors `main` : ignoré et signalé, jamais nettoyé.
- Tâche sacrifiable ⚠️ : T-067-04 ; T-067-05 calendaire.
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-067-01 | [DOC] | `README.fr.md` du lot B (12 plugins, `gpsr` à `withdrawal`) | 3h | T-064-04 | ✅ |
| T-067-02 | [TEST] | `bin/vitrine-readme --check` sur les 24 dépôts ; écarts corrigés | 1h | T-067-01, T-064-05 | ✅ |
| T-067-03 | [OPS] | Branche locale `docs/readme-fr-demo` et commit conventionnel dans chacun des 24 dépôts (après `git fetch` et vérification de la branche) | 1.5h | T-067-02 | ✅ |
| T-067-04 | [DOC] | ⚠️ Note de GO : 24 branches, état de la CI attendu et blocages éventuels, ordre de merge | 0.5h | T-067-03 | ✅ |
| T-067-05 | [OPS] | 📅 Ouverture des 24 PR, vérification du rendu GitHub et de la CI, présentation du lot, puis merge squash sur le GO unique de l'utilisateur | 1.5h | T-067-04 | 📅 |
| T-067-06 | [REV] | Revue | 0.5h | T-067-03 | 🔲 |

**Total estimé : 8h** (6 tâches), dont 1.5h calendaires (T-067-05) et 0.5h sacrifiable (T-067-04).
