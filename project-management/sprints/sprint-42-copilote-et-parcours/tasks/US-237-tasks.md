# Tâches - US-237 : Parcours guidé « copilote d'achat » dans les parcours de la démo

## Informations US
- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse), Nadia (responsable e-commerce B2B)
- **Story Points** : 3
- **Sprint** : sprint-42-copilote-et-parcours

## Résumé de la US
**En tant que** visiteuse de la démo
**Je veux** un parcours guidé qui me fait essayer le copilote en 3 étapes
**Afin de** voir l'apport sans chercher

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-237-01 | [DOC] | Analyser le système de parcours existant (journeys.yaml, translations) | 0,25 h | US-213, US-215 | 🔲 |
| T-237-02 | [BE] | Ajouter le parcours `copilot` dans config/demo/journeys.yaml | 0,5 h | T-237-01 | 🔲 |
| T-237-03 | [FE-WEB] | Traductions FR/EN du parcours (journeys.fr.yaml, journeys.en.yaml) | 0,5 h | T-237-02 | 🔲 |
| T-237-04 | [BE] | Préparateur de scénario `copilot` (optionnel : pré-remplir le widget) | 0,5 h | T-237-03 | 🔲 |
| T-237-01b | [TEST] | RED : test fonctionnel — parcours copilote complet (doit échouer) | 0,25 h | T-237-01 | 🔲 |
| T-237-05 | [TEST] | GREEN : vérifier que chaque étape s'ouvre avec le bon compte (FR/EN) | 0,5 h | T-237-04 | 🔲 |
| T-237-06 | [TEST] | Test fonctionnel — compte non connecté redirige vers la connexion | 0,25 h | T-237-05 | 🔲 |
| T-237-07 | [TEST] | pa11y AAA sur les pages du parcours copilote | 0,25 h | T-237-06 | 🔲 |
| T-237-09 | [TEST] | Test fonctionnel — reprise du parcours après interruption | 0,25 h | T-237-05 | 🔲 |
| T-237-10 | [TEST] | Test fonctionnel — saisie libre renvoie vers les suggestions dans le parcours | 0,25 h | T-237-05 | 🔲 |
| T-237-08 | [REV] | CI verte démo, vérification de la page des parcours | 0,25 h | T-237-07, T-237-09, T-237-10 | 🔲 |

**Total estimé** : 3,75 h

---

## Détail des tâches

### T-237-01 : Analyser le système de parcours existant
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : US-213 et US-215 (les scénarios copilote doivent être disponibles)

Lire `config/demo/journeys.yaml` (5 parcours existants : buyer, approver, finance, ecommerce, it), `docs/parcours-guides.md` (architecture), `translations/journeys.{fr,en}.yaml`. Identifier la route du copilote et les étapes pertinentes.

### T-237-02 : Ajouter le parcours `copilot` dans journeys.yaml
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-237-01

Ajouter l'entrée `copilot` dans `config/demo/journeys.yaml` :
- `persona: buyer` (Claire)
- `account: claire@acme.example`
- `minutes: 4`
- 4 étapes : (1) page d'accueil B2B ou dashboard → voir le widget, (2) page du copilote → cliquer une suggestion de réassort, (3) panier → vérifier les articles ajoutés, (4) copilote → essayer l'ajout par référence.

Le parcours doit pointer vers des routes existantes (vérifier les noms).

### T-237-03 : Traductions FR/EN du parcours
- **Type** : [FE-WEB]
- **Estimation** : 0,5 h
- **Dépend de** : T-237-02

Ajouter les clés dans `translations/journeys.{fr,en}.yaml` :
- `journey.copilot.title` / `.goal`
- `journey.copilot.step.1.title` / `.text` à `step.4.title` / `.text`

Rappel : mentionner « L'IA prépare, l'acheteuse valide » et « Réponses enregistrées en démo ».

### T-237-04 : Préparateur de scénario `copilot`
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-237-03

Optionnel : créer un `ScenarioPreparer` avec la clé `copilot` qui, au lancement de l'étape 2, pourrait pré-remplir la conversation du widget avec un message de bienvenue. Si ce n'est pas nécessaire (le widget s'ouvre vide par défaut), cette tâche se limite à vérifier que le scénario fonctionne sans préparateur.

### T-237-01b : RED — test fonctionnel parcours copilote
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-237-01

Écrire le test fonctionnel qui vérifie que le parcours `copilot` existe et que ses étapes s'ouvrent. Le test doit échouer (rouge prouvé) car le parcours n'existe pas encore dans `journeys.yaml`.

### T-237-05 : GREEN — vérifier que chaque étape s'ouvre
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-237-04

Le test existant `JourneyTest::testEveryStepOpensItsScreenWithTheAccountOfTheJourney` parcourt automatiquement chaque étape de chaque parcours. Vérifier qu'il passe avec le nouveau parcours `copilot` en FR et EN.

### T-237-06 : Test fonctionnel — compte non connecté
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-237-05

Vérifier que le parcours `copilot` redirige vers la connexion quand le visiteur n'est pas connecté en Claire, conformément au comportement existant des parcours.

### T-237-07 : pa11y AAA sur les pages du parcours
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-237-06

Exécuter `pa11y` en niveau AAA sur les 4 étapes du parcours copilote (FR). Vérifier 0 erreur.

### T-237-09 : Test fonctionnel — reprise du parcours après interruption
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-237-05

Couvre le scénario Gherkin « Reprise du parcours » : simuler une interruption du parcours (fermer la page), revenir, vérifier que l'étape en cours est reprise. Le comportement de reprise est hérité du système de parcours existant — ce test vérifie qu'il fonctionne avec le parcours copilote.

### T-237-10 : Test fonctionnel — saisie libre renvoie vers les suggestions
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-237-05

Couvre le scénario Gherkin « Réponse non enregistrée » : dans le contexte du parcours copilote, envoyer un texte libre qui ne matche aucune cassette, vérifier que le widget propose les suggestions disponibles et que le parcours guide la visiteuse vers un clic de suggestion.

### T-237-08 : CI verte et vérification
- **Type** : [REV]
- **Estimation** : 0,25 h
- **Dépend de** : T-237-07

CI verte. Vérifier visuellement la page `/{locale}/guides` pour s'assurer que le parcours copilote apparaît dans la liste.
