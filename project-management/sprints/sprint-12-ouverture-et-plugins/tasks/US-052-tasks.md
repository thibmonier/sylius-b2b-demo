# Tâches — US-052 : Ouverture publique de la démo

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 3
- **Sprint** : sprint-12-ouverture-et-plugins
- **Dépendances** : US-049, US-050 (sprint 11, PR #21 mergée et déployée)

## Résumé

En tant que Thibaut, je veux ouvrir la démo au public sur mon GO et vérifier le résultat de bout en bout, afin de la partager sans faille connue et avec un retour arrière.

## Règles

- Démo publique mais **non promue** sur les canaux Sylius ; aucune donnée réelle.
- **La bascule n'est jamais exécutée sans GO explicite.** Claude ne lance aucune commande Railway (`redeploy`, `variables`, `config apply`) : il prépare, l'utilisateur exécute, Claude vérifie (`curl`, tests du mode public) et documente.
- Aucune lecture de `.env` ni de secret ; les identifiants de la porte restent en place pour le retour arrière.
- Tâche **sacrifiable** ⚠️ : T-052-02 ; ordre dans [README.md](./README.md).
- T-052-03 à T-052-07 : calendaires, GO et comptes de l'utilisateur.
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-052-01 | [DOC] | Relecture de `docs/ouverture-publique.md` contre l'état réel (variables, `railway.ts`, vérifications 1 à 12) ; check-list de GO consolidée | 1h | — | 🔲 |
| T-052-02 | [TEST] | ⚠️ Script `bin/verify-public-mode <url>` (lecture seule) : les vérifications 1 à 12 du runbook, plus un test fonctionnel du mode public rejoué | 2h | T-052-01 | 🔲 |
| T-052-03 | [OPS] | 📅 Moniteur externe sur `/healthz` (ex T-050-09) | 0.5h | T-052-01 | 📅 |
| T-052-04 | [OPS] | 📅 Bascule en mode public, GO explicite (ex T-050-10) | 1h | T-052-01, T-052-03 | 📅 |
| T-052-05 | [OPS] | 📅 Vérification de `X-Real-IP` après la bascule (vérification 11 : deux IP distinctes) | 0.5h | T-052-04 | 📅 |
| T-052-06 | [OPS] | 📅 Parcours MCP de bout en bout avec un vrai assistant (ex T-050-11) | 1h | T-052-04 | 📅 |
| T-052-07 | [OPS] | 📅 Relevé de coût Railway après le 2026-10-23 (ex T-049-08) | 0.5h | — | 📅 |
| T-052-08 | [DOC] | Mise à jour des docs avec les résultats : runbook, `docs/mcp.md`, `docs/deploiement-railway.md` (coût), état « public » | 1.5h | T-052-05, T-052-06, T-052-07 | 🔲 |
| T-052-09 | [REV] | Revue | 0.5h | T-052-08 | 🔲 |

**Total estimé : 8.5h** (9 tâches), dont 3.5h calendaires (T-052-03 à T-052-07) et 2h sacrifiables (T-052-02).

---

## Détail des tâches

### T-052-01 — Relecture du runbook

- **Type** : [DOC]
- **Estimation** : 1h
- **Fichiers (indicatifs)** : `docs/ouverture-publique.md`, `.railway/railway.ts`

**Description** : confronter le runbook à l'état du dépôt après la PR #21 : `OPEN_TO_PUBLIC`, variables `DEMO_BASIC_AUTH`, `DEMO_REGISTRATION_OPEN`, `DEMO_PUNCHOUT_PUBLIC_ENDPOINT`, les 12 vérifications, le retour arrière. Rédiger la check-list de GO (revue de sécurité close, reset et `/healthz` verts, moniteur créé) que l'utilisateur lit avant de répondre. Écarts corrigés dans le runbook.

**Critères de validation** :
- [ ] Chaque commande du runbook est exécutable telle quelle (URL en variable)
- [ ] La check-list de GO tient sur une page

### T-052-02 — Script de vérification ⚠️

- **Type** : [TEST]
- **Estimation** : 2h
- **Fichiers (indicatifs)** : `bin/verify-public-mode`, `tests/Functional/Demo/` (mode public déjà couvert au sprint 11, rejoué)

**Description** : un script POSIX en lecture seule qui prend l'URL, exécute les vérifications 1 à 9 et 12 du runbook (codes HTTP attendus) et affiche un tableau OK/KO ; code de sortie non nul au premier écart. Les vérifications 10 et 11 (limiteurs) restent manuelles (elles dépendent de deux connexions). Aucun secret, aucune écriture. Sacrifiable : le runbook garde les commandes `curl`.

### T-052-03 — Moniteur externe

- **Type** : [OPS] 📅
- **Estimation** : 0.5h

**Description** : par l'utilisateur, avant la bascule : moniteur HTTP sur `https://<domaine>/healthz` (UptimeRobot ou Better Stack), attendu `200`, intervalle 1 à 5 minutes, alerte par e-mail. `/healthz` est exempté de la porte en mode privé : le moniteur peut être créé avant. Claude fournit l'URL et les paramètres.

### T-052-04 — Bascule en mode public

- **Type** : [OPS] 📅
- **Estimation** : 1h
- **Condition** : GO explicite de l'utilisateur, donné pendant le sprint (date non fixée)

**Description** : Claude prépare le diff de `.railway/railway.ts` (`OPEN_TO_PUBLIC` à `true`) et la séquence de commandes ; l'utilisateur lance `railway config apply`. Claude exécute ensuite `bin/verify-public-mode` (ou les `curl` du runbook) et consigne le résultat. En cas d'écart : retour arrière (porte refermée, `401` attendu) avant toute analyse. La tâche reste ouverte sans GO.

### T-052-05 — Vérification de `X-Real-IP`

- **Type** : [OPS] 📅
- **Estimation** : 0.5h

**Description** : vérification 11 du runbook après la bascule : 130 appels depuis une connexion donnent des `429` ; depuis une seconde connexion (autre IP, 4G), la page répond encore `200`. Si les deux sont limitées ensemble, `X-Real-IP` n'est pas lu : refermer la porte et ouvrir un correctif de `RailwayClientIpListener`.

### T-052-06 — Parcours MCP

- **Type** : [OPS] 📅
- **Estimation** : 1h

**Description** : T-030-03 de `docs/mcp.md` : connexion OAuth d'un assistant hébergé au serveur MCP, puis appels d'outils de bout en bout. L'utilisateur connecte l'assistant ; Claude consigne le résultat (outils appelés, erreurs) dans `docs/mcp.md`.

### T-052-07 — Relevé de coût Railway

- **Type** : [OPS] 📅
- **Estimation** : 0.5h

**Description** : relevé complet du coût par service après le 2026-10-23 (ex T-048-11, T-049-08), par l'utilisateur sur le tableau de bord Railway ; Claude consigne les chiffres dans `docs/deploiement-railway.md` (budget) et compare au plafond.

### T-052-08 — Documentation après bascule

- **Type** : [DOC]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `docs/ouverture-publique.md`, `docs/mcp.md`, `docs/deploiement-railway.md`, `docs/durcissement-demo.md`

**Description** : consigner la date de bascule, les résultats des vérifications, le résultat de `X-Real-IP`, le parcours MCP et le coût ; passer l'état de la démo de « privée » à « publique » là où il est décrit ; si la bascule n'a pas eu lieu, documenter l'état « prête, en attente de GO ».

### T-052-09 — Revue

- **Type** : [REV]
- **Estimation** : 0.5h

**Description** : relecture des docs (cohérence des deux modes, retour arrière intact), absence de secret et de nom de domaine privé dans le dépôt, rappel de non-promotion présent.
