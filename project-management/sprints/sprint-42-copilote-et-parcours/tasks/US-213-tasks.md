# Tâches - US-213 : Copilote d'achat — ajout par référence et état du panier

## Informations US
- **Epic** : EPIC-16
- **Persona** : Claire (acheteuse)
- **Story Points** : 5
- **Sprint** : sprint-42-copilote-et-parcours

## Résumé de la US
**En tant que** Claire
**Je veux** ajouter des articles par référence et connaître l'état de mon panier en une phrase
**Afin de** composer une commande sans quitter la conversation

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-213-01 | [DOC] | Lire ADR 0018, cassettes existantes et controller du plugin | 0,25 h | T-TECH-42-04 | 🔲 |
| T-213-02 | [TEST] | RED : test unitaire — ajout par référence au panier | 0,5 h | T-213-01 | 🔲 |
| T-213-03 | [BE] | GREEN : cassette « ajoute [ref] au panier » (FR/EN) | 1 h | T-213-02 | 🔲 |
| T-213-04 | [TEST] | RED : test unitaire — état du panier | 0,5 h | T-213-03 | 🔲 |
| T-213-05 | [BE] | GREEN : cassette « état de mon panier » enrichie (lignes détaillées) | 1 h | T-213-04 | 🔲 |
| T-213-06 | [TEST] | RED : test unitaire — ajout de plusieurs références | 0,5 h | T-213-05 | 🔲 |
| T-213-07 | [BE] | GREEN : cassette « ajoute 3 références » (lot) | 0,5 h | T-213-06 | 🔲 |
| T-213-07b | [TEST] | RED : tests unitaires — référence inconnue, hors catalogue, quantité invalide | 0,5 h | T-213-07 | 🔲 |
| T-213-08 | [BE] | GREEN : cassettes d'erreur (référence inconnue, hors catalogue, quantité invalide) | 0,5 h | T-213-07b | 🔲 |
| T-213-09 | [TEST] | Test fonctionnel plugin — scénarios ajout/état/erreurs | 1 h | T-213-08 | 🔲 |
| T-213-10 | [OPS] | Intégration démo : cassettes dans la démo, fixtures si nécessaire | 0,5 h | T-213-09 | 🔲 |
| T-213-11 | [TEST] | Test fonctionnel démo : scénario ajout par référence | 0,5 h | T-213-10 | 🔲 |
| T-213-12 | [DOC] | Bouton suggestion « Ajoute [ref] au panier » dans le widget (translation) | 0,25 h | T-213-11 | 🔲 |
| T-213-13 | [REV] | CI verte plugin + démo, captures README | 0,5 h | T-213-12 | 🔲 |

**Total estimé** : 8 h

---

## Détail des tâches

### T-213-01 : Lire ADR 0018, cassettes existantes et controller du plugin
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : T-TECH-42-04 (bump copilote ^1.1)

Relire l'ADR 0018 (replay with scenario buttons), les cassettes existantes dans `config/cassettes.jsonl` (6 entrées : cart, reorder, reorder with substitution en FR/EN), et le `ReplayCopilotAdapter` pour comprendre le mécanisme de matching des intents.

### T-213-02 : RED — test unitaire ajout par référence au panier
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-01

Test dans le plugin : envoyer l'intent « ajoute 20 de la référence EPI-GANT-09 » → la cassette doit répondre avec confirmation de l'ajout, prix négocié et total. Le test doit échouer (rouge prouvé).

### T-213-03 : GREEN — cassette « ajoute [ref] au panier »
- **Type** : [BE]
- **Estimation** : 1 h
- **Dépend de** : T-213-02

Ajouter les cassettes FR/EN pour l'action `quick_order_add` dans `config/cassettes.jsonl`. L'intent normalisé déclenche un `quick_order_add` avec la référence et la quantité en paramètres. La réponse confirme la ligne ajoutée avec prix HT.

### T-213-04 : RED — test unitaire état du panier
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-03

Test : envoyer l'intent « quel est l'état de mon panier » → la cassette doit répondre avec le détail des lignes (pas seulement le total comme dans la v1.1.0 mais chaque ligne avec référence, quantité et prix unitaire). Le test doit échouer.

### T-213-05 : GREEN — cassette « état du panier » enrichie
- **Type** : [BE]
- **Estimation** : 1 h
- **Dépend de** : T-213-04

Remplacer les cassettes `get_cart` existantes par des versions enrichies : la réponse inclut le détail ligne par ligne (référence, nom, quantité, prix unitaire HT, sous-total) puis le total HT et le lien checkout.

### T-213-06 : RED — test unitaire ajout de plusieurs références
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-05

Test : envoyer l'intent « ajoute 10 de EPI-GANT-09, 5 de SEC-CASQ-01 et 2 de NET-CAT6-10 » → la cassette doit répondre avec les 3 lignes ajoutées en une action. Le test doit échouer.

### T-213-07 : GREEN — cassette « ajoute 3 références » (lot)
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-06

Ajouter les cassettes FR/EN pour l'action `quick_order_add_batch`. La réponse récapitule les 3 lignes ajoutées.

### T-213-07b : RED — tests unitaires erreurs
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-07

Tests dans le plugin : envoyer les 3 intents d'erreur (référence inconnue « ajoute 5 de XXXXXX », produit hors catalogue fermé, quantité invalide « ajoute -3 de EPI-GANT-09 ») → chaque test doit échouer (rouge prouvé) car les cassettes n'existent pas encore.

### T-213-08 : GREEN — cassettes d'erreur
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-07b

Ajouter les cassettes FR/EN pour les cas d'erreur :
- Référence inconnue → message clair, suggestions de références proches
- Produit hors catalogue fermé → refus sans révéler l'existence
- Quantité invalide → demande de précision

### T-213-09 : Test fonctionnel plugin
- **Type** : [TEST]
- **Estimation** : 1 h
- **Dépend de** : T-213-08

Tests fonctionnels dans le plugin couvrant les 6 scénarios Gherkin de la US : ajout nominal, état du panier, plusieurs références, référence inconnue, hors catalogue, quantité invalide.

### T-213-10 : Intégration démo
- **Type** : [OPS]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-09

Vérifier que les cassettes du plugin sont disponibles dans la démo après le bump. Ajouter des fixtures si nécessaire (ex : produits avec les références citées dans les cassettes).

### T-213-11 : Test fonctionnel démo
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-10

Test fonctionnel dans la démo : se connecter en Claire, envoyer « ajoute 20 de la référence EPI-GANT-09 », vérifier la réponse dans le widget.

### T-213-12 : Bouton suggestion dans le widget
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : T-213-11

Ajouter une clé de traduction pour le bouton suggestion « ajoute au panier par référence » dans `translations/messages.{fr,en}.yaml` du plugin. Le bouton est déjà rendu par le template BEM v1.1.

### T-213-13 : CI verte et captures README
- **Type** : [REV]
- **Estimation** : 0,5 h
- **Dépend de** : T-213-12

Vérifier que la CI du plugin et de la démo est verte. Mettre à jour le README du plugin avec les nouveaux scénarios documentés. Captures à faire sur GO de Thibaut.
