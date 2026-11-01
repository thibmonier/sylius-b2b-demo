# Tâches - US-214 : Copilote d'achat — garde-fous et journal d'audit

## Informations US
- **Epic** : EPIC-16
- **Persona** : Sophie (back-office), Isabelle (administratrice)
- **Story Points** : 5
- **Sprint** : sprint-43-copilote-complet-et-passeport

## Résumé de la US
**En tant que** Sophie
**Je veux** que le copilote n'agisse qu'avec les droits du compte connecté et qu'il trace ses actions
**Afin de** pouvoir démontrer qu'il reste sous contrôle humain

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-214-01 | [DOC] | Lecture du code existant : controller, outils, `CopilotModelInterface` | 0,25 h | — | 🔲 |
| T-214-02 | [TEST] | RED : test unitaire — vérification des droits du compte (scopes) | 0,5 h | T-214-01 | 🔲 |
| T-214-03 | [BE] | GREEN : guard vérifiant les scopes `b2b:read`, `b2b:cart` avant chaque outil | 0,75 h | T-214-02 | 🔲 |
| T-214-04 | [TEST] | RED : test unitaire — liste blanche d'outils (outil non autorisé rejeté) | 0,5 h | T-214-03 | 🔲 |
| T-214-05 | [BE] | GREEN : `ToolWhitelist` filtrant les appels d'outils | 0,5 h | T-214-04 | 🔲 |
| T-214-06 | [TEST] | RED : test unitaire — journal d'audit (entrée tracée par action) | 0,5 h | T-214-05 | 🔲 |
| T-214-07 | [BE] | GREEN : `CopilotAuditLogger` écrivant dans le journal d'audit | 1 h | T-214-06 | 🔲 |
| T-214-08 | [TEST] | RED : test unitaire — refus de commander ou payer | 0,5 h | T-214-07 | 🔲 |
| T-214-09 | [BE] | GREEN : message de refus pour les intentions de commande/paiement | 0,25 h | T-214-08 | 🔲 |
| T-214-10 | [TEST] | RED : test unitaire — limite de débit quotidienne | 0,5 h | T-214-09 | 🔲 |
| T-214-11 | [BE] | GREEN : `RateLimiter` par compte et par jour | 0,75 h | T-214-10 | 🔲 |
| T-214-12 | [TEST] | Tests fonctionnels plugin — scénarios garde-fous et audit | 1 h | T-214-11 | 🔲 |
| T-214-13 | [OPS] | Intégration démo : configuration des garde-fous, cassettes | 0,5 h | T-214-12 | 🔲 |
| T-214-14 | [DOC] | README du plugin mis à jour (principes, pas de détails de sécurité) | 0,25 h | T-214-13 | 🔲 |
| T-214-15 | [REV] | CI verte plugin + démo, vérification aucun détail de sécurité exposé | 0,5 h | T-214-14 | 🔲 |

**Total estimé** : 8,25 h

---

## Détail des tâches

### T-214-01 : Lecture du code existant
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : —

Relire le `CopilotChatController`, le `CopilotModelInterface`, le `ReplayCopilotAdapter`, les cassettes et les tests existants. Identifier les points d'injection pour les garde-fous (avant l'appel au modèle, avant l'exécution des outils).

### T-214-02 : RED — vérification des droits du compte
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-01
- **Scénario** : « Rôle sans droit d'achat »

Test unitaire : un membre en lecture seule demande un ajout au panier → exception levée ou réponse de refus avec le message approprié. Le test doit échouer (rouge prouvé).

### T-214-03 : GREEN — guard de vérification des scopes
- **Type** : [BE]
- **Estimation** : 0,75 h
- **Dépend de** : T-214-02

Créer un décorateur ou un middleware vérifiant les scopes du compte connecté (`b2b:read`, `b2b:cart`) avant chaque exécution d'outil. Passer le contexte du membre dans le `$context` du `chat()`.

### T-214-04 : RED — liste blanche d'outils
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-03
- **Scénario** : « Outil non autorisé demandé par le modèle »

Test unitaire : un ToolCall avec un nom hors whitelist → rejeté, tracé, conversation continue sans effet.

### T-214-05 : GREEN — `ToolWhitelist`
- **Type** : [BE]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-04

Implémenter le filtre sur les noms d'outils autorisés. La configuration liste les outils permis. Les outils de commande et de paiement sont exclus par conception.

### T-214-06 : RED — journal d'audit
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-05
- **Scénario** : « Action tracée (nominal) »

Test unitaire : chaque appel d'outil produit une entrée d'audit avec le compte, l'intention, le nom de l'outil et le résultat.

### T-214-07 : GREEN — `CopilotAuditLogger`
- **Type** : [BE]
- **Estimation** : 1 h
- **Dépend de** : T-214-06

Service traçant les actions copilote dans le journal d'audit existant (integration avec `sylius-audit-log-plugin` si disponible, sinon propre entité). Les entrées sont filtrables par « copilote » dans l'admin.

### T-214-08 : RED — refus de commander ou payer
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-07
- **Scénario** : « Demande de commander ou payer »

Test unitaire : l'intent « passe la commande et paie » → réponse de refus expliquant que le copilote prépare le panier uniquement.

### T-214-09 : GREEN — message de refus
- **Type** : [BE]
- **Estimation** : 0,25 h
- **Dépend de** : T-214-08

Ajouter la cassette de refus FR/EN pour les intentions de commande/paiement. Le copilote renvoie vers le passage de commande.

### T-214-10 : RED — limite de débit quotidienne
- **Type** : [TEST]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-09
- **Scénario** : « Limite quotidienne atteinte »

Test unitaire : un compte au plafond reçoit un message de limite.

### T-214-11 : GREEN — `RateLimiter`
- **Type** : [BE]
- **Estimation** : 0,75 h
- **Dépend de** : T-214-10

Rate limiter par compte et par jour utilisant le composant Symfony RateLimiter. Configurable dans le bundle.

### T-214-12 : Tests fonctionnels plugin
- **Type** : [TEST]
- **Estimation** : 1 h
- **Dépend de** : T-214-11

Tests fonctionnels couvrant tous les scénarios Gherkin : droits, whitelist, audit, refus commande, limite.

### T-214-13 : Intégration démo
- **Type** : [OPS]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-12

Configurer les garde-fous dans la démo. Ajouter les cassettes de refus dans les fixtures.

### T-214-14 : README
- **Type** : [DOC]
- **Estimation** : 0,25 h
- **Dépend de** : T-214-13

Mettre à jour le README du plugin avec les principes « l'IA prépare, l'humain valide ». Aucun détail de sécurité.

### T-214-15 : CI verte et review
- **Type** : [REV]
- **Estimation** : 0,5 h
- **Dépend de** : T-214-14

CI verte (PHPStan max, ECS, tests). Vérifier qu'aucun détail de sécurité n'est exposé dans le code public.
