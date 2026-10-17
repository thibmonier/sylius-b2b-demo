# Tâches — US-063 : Classement Sylius Plus des 24 plugins et charte de ton

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 2
- **Sprint** : sprint-13-vitrine-des-plugins
- **Dépendances** : politique de communication Sylius Plus ; décision D2

## Règles

- Sources Plus : documentation officielle et pages publiques, avec URL et date ; jamais de mémoire. Aucune prise de contact ni publication.
- Aucune modification d'un dépôt de plugin.
- Non sacrifiable.
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-063-01 | [DOC] | Inventaire des 24 plugins (fonction, écrans, point de contact éventuel avec Plus) à partir des README | 1.5h | — | ✅ |
| T-063-02 | [DOC] | Relevé des fonctions de Sylius Plus pertinentes (documentation officielle, pages publiques) : URL, date de consultation | 1.5h | — | ✅ |
| T-063-03 | [DOC] | Classement des 24 plugins (concurrent, complémentaire, hors périmètre, « à confirmer ») avec sources | 1.5h | T-063-01, T-063-02 | ✅ |
| T-063-04 | [DOC] | Charte de ton : formulations autorisées et interdites par classe, FR et EN, glossaire FR | 1h | T-063-03 | ✅ |
| T-063-05 | [DOC] | `docs/vitrine-plugins.md` : classement, charte, liste des plugins sans image justifiée, critères d'image | 0.5h | T-063-04 | ✅ |
| T-063-06 | [REV] | Revue ; questions ouvertes à l'utilisateur (cas « à confirmer ») | 0.5h | T-063-05 | 🔲 |

**Total estimé : 6.5h** (6 tâches).
