# Tâches - US-215 : Copilote d'achat — mode rejoué de la démo publique et scénarios enregistrés

## Informations US
- **Epic** : EPIC-16
- **Persona** : Camille (recruteuse), Nadia (responsable e-commerce B2B)
- **Story Points** : 3
- **Sprint** : sprint-42-copilote-et-parcours

## Résumé de la US
**En tant que** visiteuse de la démo publique
**Je veux** essayer le copilote sans connaître les phrases attendues
**Afin d'** en comprendre l'apport en moins de 2 minutes

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-215-01 | [DOC] | Analyser le ReplayCopilotAdapter et le matching existant | 0,25 h | T-TECH-42-04 | 🔲 |
| T-215-02 | [TEST] | RED : test — formulation voisine rattachée au bon scénario | 0,5 h | T-215-01 | 🔲 |
| T-215-03 | [BE] | GREEN : enrichir le matching du ReplayCopilotAdapter (variantes) | 1 h | T-215-02 | 🔲 |
| T-215-04 | [TEST] | RED : test — texte libre hors jeu renvoie le fallback | 0,25 h | T-215-03 | 🔲 |
| T-215-05 | [BE] | GREEN : fallback amélioré (propose les suggestions disponibles) | 0,5 h | T-215-04 | 🔲 |
| T-215-06 | [TEST] | Test fonctionnel plugin — variantes FR/EN et fallback | 0,5 h | T-215-05 | 🔲 |
| T-215-07 | [FE-WEB] | Mention « Réponses enregistrées en démo » dans le bandeau | 0,25 h | T-215-06 | 🔲 |
| T-215-08 | [TEST] | Test démo — vérifier l'absence de clé API dans la config | 0,25 h | T-215-07 | 🔲 |
| T-215-09 | [TEST] | Test démo — remise à zéro nocturne préserve les cassettes | 0,25 h | T-215-08 | 🔲 |
| T-215-10 | [REV] | CI verte plugin + démo | 0,25 h | T-215-09 | 🔲 |

**Total estimé** : 4 h

---

## Détail des tâches

### T-215-01 : Analyser le ReplayCopilotAdapter et le matching existant
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : T-TECH-42-04 (bump copilote ^1.1)

Lire `ReplayCopilotAdapter::chat()` pour comprendre comment l'intent est matché aux cassettes. Identifier les axes d'enrichissement : matching par mots-clés, normalisation, variantes.

### T-215-02 : RED — formulation voisine rattachée au bon scénario
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-215-01

Test dans le plugin : envoyer des formulations voisines des intents enregistrés (ex : « refais ma commande du mois dernier », « repass ma dernière commande », « what was in my last order ») → elles doivent être rattachées au scénario `reorder_last_month`. Le test doit échouer.

### T-215-03 : GREEN — enrichir le matching du ReplayCopilotAdapter
- **Type** : [BE]
- **Estimation** : 1 h
- **Dépend de** : T-215-02

Ajouter dans le `ReplayCopilotAdapter` un système de matching par mots-clés ou synonymes : chaque cassette peut avoir un champ `aliases` (liste de formulations alternatives). Le matching teste d'abord l'intent exact, puis les aliases par containment ou similarité simple (str_contains normalisé en minuscules).

### T-215-04 : RED — texte libre hors jeu renvoie le fallback
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-215-03

Test : envoyer un texte sans rapport (ex : « quel temps fait-il ? ») → la réponse doit être le fallback qui liste les suggestions disponibles, sans appel externe.

### T-215-05 : GREEN — fallback amélioré
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-215-04

Améliorer le message de fallback pour qu'il propose les suggestions cliquables disponibles (« Essayez une des suggestions suivantes : ... »). Le fallback doit lister les intents enregistrés de façon conviviale.

### T-215-06 : Test fonctionnel plugin — variantes FR/EN et fallback
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-215-05

Tests fonctionnels couvrant : suggestion cliquée → bonne réponse, formulation voisine FR → rattachée, formulation voisine EN → rattachée, texte hors jeu → fallback.

### T-215-07 : Mention « Réponses enregistrées en démo » dans le bandeau
- **Type** : [FE-WEB]
- **Estimation** : 0,25 h
- **Dépend de** : T-215-06

Ajouter dans le template override ou le widget copilote de la démo une mention visible « Réponses enregistrées en démo » (traduction FR/EN). Le bandeau de la démo (`demo_bar`) peut porter cette information.

### T-215-08 : Test démo — absence de clé API
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-215-07

Test fonctionnel dans la démo vérifiant que la configuration du copilote n'a pas de clé d'API LLM configurée (seul l'adaptateur rejoué est actif).

### T-215-09 : Test démo — remise à zéro préserve les cassettes
- **Type** : [TEST]
- **Estimation** : 0,25 h
- **Dépend de** : T-215-08

Test fonctionnel dans la démo vérifiant qu'après `bin/reset-demo` les cassettes sont toujours chargées et le copilote répond normalement.

### T-215-10 : CI verte plugin + démo
- **Type** : [REV]
- **Estimation** : 0,25 h
- **Dépend de** : T-215-09

Vérifier que la CI du plugin et de la démo est verte. Pas de captures supplémentaires pour cette US (le widget est déjà capturé par US-213).
