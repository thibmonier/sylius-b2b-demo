# Tâches — US-066 : Préparation du sous-domaine de la démo

## Informations US

- **Epic** : EPIC-09
- **Persona** : Thibaut
- **Story Points** : 2
- **Sprint** : sprint-13-vitrine-des-plugins
- **Dépendances** : US-052 (runbook d'ouverture) ; préfixe `sylius-b2b` fixé (2026-10-17), domaine du site inconnu (URL complète en US-068)

## Règles

- Aucune commande Railway ni aucun changement DNS par Claude : l'utilisateur crée le CNAME et le domaine personnalisé.
- Préfixe `sylius-b2b` (sylius-b2b.<domaine du site>) ; aucune valeur de domaine codée en dur ; aucune lecture de `.env` ni de secret.
- Coupe franche : T-066-02, T-066-03, T-066-07 ; ordre dans [README.md](./README.md). T-066-05 et T-066-06 calendaires.
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-066-01 | [DOC] | Analyse des endroits où l'hôte compte : `TRUSTED_HOSTS`, cookies, URL absolues (e-mails, MCP, OAuth), robots, liens de la page Plugins | 1h | — | ✅ |
| T-066-02 | [TEST] | ⚠️ Tests rouges : domaine Railway et sous-domaine acceptés, hôte inconnu refusé (400), sans sous-domaine seul le domaine Railway | 0.5h | T-066-01 | ✅ |
| T-066-03 | [BE] | ⚠️ `.railway/railway.ts` et configuration : variable du sous-domaine (sans secret), les deux hôtes acceptés pendant la transition | 1h | T-066-02 | ✅ |
| T-066-04 | [DOC] | Runbook `docs/sous-domaine-demo.md` (CNAME, domaine Railway, certificat, retour arrière) et références paramétrées dans `ouverture-publique.md`, `deploiement-railway.md`, `mcp.md` | 1h | T-066-01 | ✅ |
| T-066-05 | [OPS] | 📅 CNAME et domaine personnalisé dans Railway, par l'utilisateur (`sylius-b2b.<domaine du site>`, domaine à fournir) | 0.5h | T-066-04 | 📅 |
| T-066-06 | [OPS] | 📅 Vérification du certificat et de la réponse sur le sous-domaine (`curl`, `openssl`, lecture seule) | 0.5h | T-066-05 | 📅 |
| T-066-07 | [REV] | ⚠️ Revue | 0.5h | T-066-03, T-066-04 | 🔲 |

**Total estimé : 5h** (7 tâches), dont 1h calendaire (T-066-05, T-066-06) et 2h de coupe franche (T-066-02, T-066-03, T-066-07).
