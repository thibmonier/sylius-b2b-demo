# Tâches : US-170 : Page « Architecture de la démo »

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) ; lead développeur d'une agence
- **Story Points** : 3 (Should, non sacrifiable (décision de Thibaut du 2026-10-23))
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-170](../../../backlog/user-stories/US-170.md)

## Résumé de la US

**En tant que** lead développeur qui évalue les plugins
**Je veux** une page qui montre comment la démo est construite
**Afin de** juger en quelques minutes la qualité de l'architecture et la façon dont les plugins s'assemblent

## Règles et contraintes

- La page « Intégrer » garde son « Architecture type » (projet client) et renvoie vers la nouvelle page ; pas de doublon de contenu.
- Chaque affirmation renvoie à un fichier du dépôt public ou à un `docs/*.md` ; les chiffres viennent de `proof.json` ou de la configuration, jamais écrits dans le gabarit.
- Sécurité en termes généraux seulement : aucune route, valeur ni seuil.
- Schéma en SVG inline accessible (titre, description, thème clair et sombre), comme celui de la page Intégrer.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `src/Demo/Controller/` (nouveau contrôleur ou route)
- `templates/demo/architecture.html.twig`
- `translations/architecture.fr.yaml`, `architecture.en.yaml`
- `templates/demo/integrate.html.twig` (lien)
- Pied de page de la boutique (lien)
- `tests/Functional/Demo/`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-170-01 | [DOC] | Cadrage : sources de chaque rubrique (`docs/*.md`, `Dockerfile`, `docker/Caddyfile`, configuration, workflows), route et liens d'accès, chiffres lus dans `proof.json` | 0,75 h | - | Claude | 🔲 |
| T-170-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : page FR et EN avec ses six rubriques, liens depuis Intégrer et le pied de page, SVG avec titre et description, chiffres égaux à ceux de `proof.json`, aucun motif de détail de sécurité | 1 h | T-170-01 | Claude | 🔲 |
| T-170-03 | [BE] | Route localisée et contrôleur ; chiffres fournis par `ProofReader` | 0,75 h | T-170-02 | Claude | 🔲 |
| T-170-04 | [FE-WEB] | Gabarit et schéma SVG d'ensemble (thème clair et sombre), textes FR et EN des six rubriques avec leurs liens vers le dépôt | 2 h | T-170-03 | Claude | 🔲 |
| T-170-05 | [FE-WEB] | Liens : section « Architecture type » de la page Intégrer et pied de page de la boutique | 0,5 h | T-170-03 | Claude | 🔲 |
| T-170-06 | [TEST] | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile` (360 px sans défilement horizontal) ; suite via `bin/run-tests`, ECS, PHPStan max, Rector | 0,75 h | T-170-04, T-170-05 | Claude | 🔲 |
| T-170-07 | [REV] | Revue (aucun détail de sécurité, ton neutre, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-170-06 | Claude | 🔲 |

**Total estimé : 6 h** (7 tâches, dont 0 h de Thibaut ; 6 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Page complète (nominal) | |
| Accès depuis le guide d'intégration et le pied de page (alternatif) | |
| Schéma lisible au clavier, au lecteur d'écran et sur mobile (alternatif) | |
| Chiffre non sourcé (erreur) | |
| Détail de sécurité (erreur) | |
