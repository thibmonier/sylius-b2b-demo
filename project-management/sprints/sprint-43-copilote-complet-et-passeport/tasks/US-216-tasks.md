# Tâches - US-216 : Copilote d'achat — adaptateur LLM réel pour les intégrateurs

## Informations US
- **Epic** : EPIC-16
- **Persona** : Hugo (intégrateur Sylius)
- **Story Points** : 5
- **Sprint** : sprint-43-copilote-complet-et-passeport

## Résumé de la US
**En tant qu'** Hugo, intégrateur
**Je veux** brancher mon fournisseur de LLM sur le copilote
**Afin de** l'utiliser sur un vrai site sans le modifier

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-216-01 | [DOC] | Lire ADR 0018, `CopilotModelInterface`, Symfony AI 0.x | 0,25 h | US-214 done | 🔲 |
| T-216-02 | [TEST] | RED : test unitaire — appel LLM réussi avec outils autorisés | 0,5 h | T-216-01 | 🔲 |
| T-216-03 | [BE] | GREEN : `SymfonyAiCopilotAdapter` implémentant `CopilotModelInterface` | 1,5 h | T-216-02 | 🔲 |
| T-216-04 | [TEST] | RED : test unitaire — fournisseur alternatif configuré | 0,5 h | T-216-03 | 🔲 |
| T-216-05 | [BE] | GREEN : configuration par env vars (provider, model, API key) | 0,5 h | T-216-04 | 🔲 |
| T-216-06 | [TEST] | RED : test unitaire — bascule vers rejoué en cas d'erreur | 0,5 h | T-216-05 | 🔲 |
| T-216-07 | [BE] | GREEN : fallback vers `ReplayCopilotAdapter` avec mention | 0,5 h | T-216-06 | 🔲 |
| T-216-08 | [TEST] | RED : test unitaire — délai dépassé | 0,5 h | T-216-07 | 🔲 |
| T-216-09 | [BE] | GREEN : timeout configurable, message d'indisponibilité | 0,25 h | T-216-08 | 🔲 |
| T-216-10 | [TEST] | RED : test unitaire — plafond de coût journalier | 0,5 h | T-216-09 | 🔲 |
| T-216-11 | [BE] | GREEN : compteur de coût journalier avec rejet au plafond | 0,5 h | T-216-10 | 🔲 |
| T-216-12 | [TEST] | Tests fonctionnels plugin — scénarios LLM mockés | 0,75 h | T-216-11 | 🔲 |
| T-216-13 | [OPS] | Intégration démo : config env vars (désactivé par défaut, replay actif) | 0,25 h | T-216-12 | 🔲 |
| T-216-14 | [DOC] | README : section configuration du fournisseur LLM | 0,25 h | T-216-13 | 🔲 |
| T-216-15 | [REV] | CI verte plugin + démo, vérification aucune clé dans les logs | 0,5 h | T-216-14 | 🔲 |

**Total estimé** : 7,75 h

---

## Détail des tâches

### T-216-01 : Lecture de l'existant et Symfony AI
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : US-214 terminée (garde-fous en place)

Relire l'ADR 0018 (décision d'architecture replay/LLM), le port `CopilotModelInterface`, et la documentation de Symfony AI (`symfony/ai` ^0.14) pour identifier le bridge à utiliser (Anthropic, OpenAI, Ollama, etc.).

### T-216-02 : RED — appel LLM réussi avec outils autorisés
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-01
- **Scénario** : « Appel réel réussi »

Test unitaire avec mock Symfony AI : l'intent « réassort comme le mois dernier » → le modèle retourne les outils autorisés. Le test doit échouer (rouge prouvé).

### T-216-03 : GREEN — `SymfonyAiCopilotAdapter`
- **Type** : [BE]
- **Estimation** : 1,5 h
- **Dépend de** : T-216-02

Implémenter l'adaptateur utilisant Symfony AI : système prompt, description des outils, mapping des réponses vers `CopilotResponse` et `ToolCall`. Le garde-fou `ToolWhitelist` d'US-214 filtre les outils avant exécution.

### T-216-04 : RED — fournisseur alternatif
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-03
- **Scénario** : « Fournisseur alternatif »

Test : un autre provider configuré (Ollama au lieu d'Anthropic) → l'adaptateur fonctionne.

### T-216-05 : GREEN — configuration par env vars
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-04

Extension DI : paramètres `COPILOT_AI_PROVIDER`, `COPILOT_AI_MODEL`, `COPILOT_AI_API_KEY` dans la config du bundle. Aucune clé par défaut.

### T-216-06 : RED — bascule vers rejoué
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-05
- **Scénario** : « Bascule vers le rejoué »

Test : le provider lève une exception → le fallback replay est utilisé avec mention.

### T-216-07 : GREEN — fallback replay
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-06

Décorateur `FallbackCopilotAdapter` : try l'adaptateur réel, catch → replay avec mention « (réponse enregistrée) ».

### T-216-08 : RED — délai dépassé
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-07
- **Scénario** : « Délai dépassé »

Test : timeout du provider → message d'indisponibilité, panier inchangé.

### T-216-09 : GREEN — timeout configurable
- **Type** : [BE]
- **Estimation** : 0,25 h
- **Dépend de** : T-216-08

Config `copilot_ai_timeout` (défaut 10 s). HttpClient timeout dans l'adaptateur.

### T-216-10 : RED — plafond de coût journalier
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-09
- **Scénario** : « Plafond de coût atteint »

Test : compteur atteint → refus sans appel au modèle.

### T-216-11 : GREEN — compteur de coût journalier
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-10

Compteur en cache (Symfony Cache) par jour. Configurable dans le bundle. Rejet au plafond.

### T-216-12 : Tests fonctionnels
- **Type** : [TEST]
- **Estimation** : 0,75 h
- **Dépend de** : T-216-11

Tests fonctionnels avec mock HTTP client couvrant les scénarios Gherkin.

### T-216-13 : Intégration démo
- **Type** : [OPS]
- **Estimation** : 0,25 h
- **Dépend de** : T-216-12

Config démo : adaptateur replay par défaut (env vars non renseignées). Doc pour activer le LLM réel dans un `.env.local`.

### T-216-14 : README
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : T-216-13

Section « Configuration du fournisseur LLM » dans le README FR/EN. Aucune clé dans le README.

### T-216-15 : CI verte et review
- **Type** : [REV]
- **Estimation** : 0,5 h
- **Dépend de** : T-216-14

CI verte. Vérifier qu'aucune clé API n'apparaît dans les logs, traces, fichiers versionnés.
