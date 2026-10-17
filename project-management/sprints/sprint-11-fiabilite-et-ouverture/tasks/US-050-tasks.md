# Tâches — US-050 : Préparer l'ouverture publique de la démo

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 5
- **Sprint** : sprint-11-fiabilite-et-ouverture
- **Dépendances** : US-035, US-036, US-037, US-040, US-030 (livrées) ; US-049 pour le moniteur `/healthz`

## Résumé

En tant que Thibaut, je veux préparer, vérifier et documenter l'ouverture publique, afin de la basculer en un geste, sans faille connue et avec retour arrière.

## Règles

- Démo publique mais **non promue** sur les canaux Sylius ; aucune donnée réelle.
- Les correctifs de code (T-050-04 à T-050-06) commencent par un test qui échoue (T-050-03).
- Tâches **sacrifiables** ⚠️ : T-050-06, T-050-08 ; ordre dans [README.md](./README.md).
- T-050-09 à T-050-11 : calendaires, GO et comptes de l'utilisateur ; la bascule n'est jamais exécutée sans GO explicite.
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-050-01 | [REV] | Revue de sécurité finale par un agent indépendant (OWASP, admin Sophie, inscriptions, limites de débit, en-têtes, MCP/OAuth, webhooks, Mailpit non exposé), mode public activé | 4h | T-050-04, T-050-05 | 🔲 |
| T-050-02 | [BE] | Correction des constats bloquants de T-050-01 (budget provisoire ; le reste tracé et reporté) | 4h | T-050-01 | 🔲 |
| T-050-03 | [TEST] | Tests rouges des deux modes : privé (401, avertissement MCP, preflight porte) et public (200, 403 inscriptions, pas d'avertissement, preflight sans porte) | 3h | — | ✅ |
| T-050-04 | [BE] | Bascule des dépendances de la porte : bandeaux, guide MCP « démo privée », `app:demo:preflight` | 3h | T-050-03 | ✅ |
| T-050-05 | [BE] | Mode public et inscriptions : décision `DEMO_REGISTRATION_OPEN=0` (refus, avertissement ou autorisation documentée) ; commande invité B2C et devis invité (`allow_guests`) | 2h | T-050-04 | ✅ |
| T-050-06 | [BE] | ⚠️ `robots.txt` / noindex selon le mode (démo non promue) | 2h | T-050-04 | ✅ |
| T-050-07 | [DOC] | Runbook `docs/ouverture-publique.md` : variables Railway, `railway.ts`, vérifications après bascule, retour arrière, moniteur `/healthz`, robots/noindex | 3h | T-050-02, T-050-05 | ✅ |
| T-050-08 | [OPS] | ⚠️ `.railway/railway.ts` préparé pour le mode public (variables, sans appliquer) | 1.5h | T-050-05 | ✅ |
| T-050-09 | [OPS] | 📅 Création du moniteur externe sur `/healthz` (UptimeRobot ou Better Stack, compte de l'utilisateur) | 0.5h | US-049 | 📅 |
| T-050-10 | [OPS] | 📅 Bascule le jour J, GO explicite (éventuellement hors sprint), vérifications du runbook | 1h | T-050-07, T-050-09 | 📅 |
| T-050-11 | [OPS] | 📅 Parcours MCP de bout en bout avec un vrai assistant après ouverture (T-030-03 de `docs/mcp.md`) | 1h | T-050-10 | 📅 |
| T-050-12 | [REV] | Revue | 1h | T-050-07 | 🔲 |

**Total estimé : 26h** (12 tâches), dont 2.5h calendaires (T-050-09 à T-050-11) et 3.5h sacrifiables (T-050-06, T-050-08).

---

## Détail des tâches

### T-050-01 — Revue de sécurité indépendante

- **Type** : [REV]
- **Estimation** : 4h

**Description** : agent sans contexte d'implémentation, configuré en mode public (`DEMO_BASIC_AUTH=0`, `DEMO_REGISTRATION_OPEN=0`). Périmètre : OWASP Top 10:2025, admin de Sophie restreint, inscriptions, limites de débit, en-têtes (CSP, HSTS, COOP/COEP/CORP), MCP et OAuth, récepteur de webhooks (SSRF, signature, taille), Mailpit jamais exposé. Livrable : matrice constats → sévérité → preuve ou report décidé, comme US-037.

**Critères de validation** :
- [ ] Aucun constat critique ou majeur ouvert après T-050-02
- [ ] Matrice versionnée dans `project-management/audits/`

### T-050-02 — Correction des constats bloquants

- **Type** : [BE]
- **Estimation** : 4h (provisoire, à réviser dès la matrice reçue)

**Description** : test rouge puis correctif par constat bloquant. Les constats non bloquants sont tracés au backlog, pas corrigés ici (YAGNI).

### T-050-03 — Tests des deux modes

- **Type** : [TEST]
- **Estimation** : 3h
- **Fichiers (indicatifs)** : `tests/Functional/Demo/` (porte, guide MCP, preflight, inscriptions)

**Description** : un jeu de scénarios paramétré par mode ; la configuration est posée par le test (jamais d'état partagé). Rouge avant T-050-04 sur ce qui ne bascule pas encore.

### T-050-04 — Dépendances de la porte

- **Type** : [BE]
- **Estimation** : 3h
- **Fichiers (indicatifs)** : `src/Demo/Http/`, `src/Demo/Command/` (preflight), `templates/demo/`, guide MCP

**Description** : bandeaux, avertissement du guide MCP et preflight lisent le même paramètre `demo.basic_auth.enabled` (source unique, DRY) ; aucune de ces vues ne dit « démo privée » en mode public. Hors `DEMO_MODE`, comportement inchangé.

### T-050-05 — Mode public et inscriptions

- **Type** : [BE]
- **Estimation** : 2h

**Description** : décision à confirmer avec l'utilisateur : porte ouverte + inscriptions ouvertes = refus du preflight, avertissement, ou cas documenté. Vérifier aussi la commande invité B2C et la demande de devis invité (`allow_guests`), signalées non couvertes dans `docs/deploiement-railway.md`.

### T-050-06 — robots.txt / noindex ⚠️

- **Type** : [BE]
- **Estimation** : 2h

**Description** : démo non promue : décider `Disallow: /` et en-tête `X-Robots-Tag: noindex` en mode public, ou indexation autorisée ; implémenter la décision par mode (`public/robots.txt` actuel à relire) et la tester.

### T-050-07 — Runbook d'ouverture

- **Type** : [DOC]
- **Estimation** : 3h
- **Fichiers (indicatifs)** : `docs/ouverture-publique.md`, liens depuis `docs/deploiement-railway.md`, `docs/durcissement-demo.md`, `docs/mcp.md`

**Description** : pas-à-pas pour l'utilisateur : prérequis (revue close, moniteur créé), variables Railway à changer (`DEMO_BASIC_AUTH`, `DEMO_REGISTRATION_OPEN`), `railway config apply`, vérifications après bascule (anonyme 200, inscription 403, `/healthz`, admin restreint, Mailpit injoignable, guide MCP), retour arrière en un geste (refermer la porte), moniteur externe, décision robots/noindex, rappel de non-promotion.

### T-050-08 — railway.ts préparé ⚠️

- **Type** : [OPS]
- **Estimation** : 1.5h

**Description** : variables du mode public prêtes dans `.railway/railway.ts` (non appliquées, aucun déploiement sans GO). Sacrifiable : le runbook décrit alors l'édition à faire à la bascule.

### T-050-09 — Moniteur externe

- **Type** : [OPS] 📅
- **Estimation** : 0.5h

**Description** : par l'utilisateur, avant la bascule : moniteur HTTP sur `/healthz` (UptimeRobot ou Better Stack), alerte vers sa messagerie. En mode privé, `/healthz` est exempté de la porte.

### T-050-10 — Bascule le jour J

- **Type** : [OPS] 📅
- **Estimation** : 1h
- **Condition** : GO explicite de l'utilisateur, éventuellement hors sprint

**Description** : application du runbook ; aucune bascule sans GO. La tâche reste ouverte si le GO n'est pas donné dans le sprint.

### T-050-11 — Parcours MCP avec un vrai assistant

- **Type** : [OPS] 📅
- **Estimation** : 1h

**Description** : T-030-03 de `docs/mcp.md`, après ouverture : connexion OAuth d'un assistant hébergé, appels d'outils de bout en bout. Résultat consigné dans `docs/mcp.md`.

### T-050-12 — Revue

- **Type** : [REV]
- **Estimation** : 1h

**Description** : relecture du runbook (exécutable tel quel, retour arrière inclus), cohérence des deux modes, absence de secret dans le dépôt.
