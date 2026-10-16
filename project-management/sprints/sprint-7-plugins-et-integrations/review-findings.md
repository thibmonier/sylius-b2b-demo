# Revue — sprint 7 (plugins)

Date : 2026-10-16. Chaque plugin a eu une revue de sécurité indépendante (agent au contexte frais, OWASP Top 10:2025), puis une contre-vérification des correctifs ; l'intégration dans la démo a eu sa propre revue (analyse statique et sondes HTTP sur une base de test).

## Verdict

- **Merge (démo privée) : GO.** CI verte (533 tests, 0 échec).
- **Ouverture publique** : plus aucun constat majeur ouvert introduit par ce sprint ; les reports des sprints précédents (US-040, sprint 8) restent à traiter avant l'ouverture.

## Plugins publiés

| Plugin | Version | Revue | Constats corrigés avant publication |
|--------|---------|-------|-------------------------------------|
| sylius-channel-access-plugin (nouveau) | 0.1.0 | non publiable → publiable | **Majeur** : canal fermé, le catalogue restait visible aux anonymes (accueil, menu des catégories sur toutes les pages) ; mineur : une requête sans canal résolu était traitée comme ouverte (désormais fermée) |
| sylius-registration-approval-plugin (nouveau) | 0.1.0, 0.1.1 | non publiable → publiable | **Majeur** : un compte inscrit sur un canal non validant était « approuvé » et utilisable sur le canal validant (désormais toute inscription en libre-service est en attente) ; mineurs : avertissement d'un canal validant en mode ouvert, hachage factice contre l'oracle de temps (page et API). 0.1.1 : pages d'attente dans la langue du visiteur |
| sylius-punchout-cxml-plugin | 1.2.0 | publiable après correctif | Exception d'un écouteur de `PunchoutOrderCreated` qui renvoyait un 500 après commit (try/catch) ; CHANGELOG précisé |
| sylius-container-fill-plugin | 1.1.1 | — (correctif ciblé, CI 17 jobs) | — |

Les nouveaux plugins exigent Sylius 2.3 (les conditions des twig hooks n'existent pas en 2.2, décision utilisateur). CI : Symfony 6.4 / 7.4 / 8.0, MySQL, MariaDB, PostgreSQL ; deux défauts trouvés par la CI et corrigés (signature des voters sous Symfony 8, verrou `FOR UPDATE` avec jointure refusé par PostgreSQL).

## Intégration dans la démo

| # | Gravité | Constat | Statut |
|---|---------|---------|--------|
| 1 | **Majeur** | Le JSON-LD de la fiche produit (agentic-feeds) envoyait le prix à un anonyme sur B2B ; le smoke test ne reconnaissait pas ce format | **Corrigé** : hookable conditionné par `TMONIER_CHANNEL_ACCESS_SEE_PRICES` (B2C garde le sien), regex du smoke test étendue |
| 2 | **Majeur** | Un compte en attente ou refusé gardait l'accès MCP (consentement OAuth, `/_mcp`) | **Corrigé** : routes MCP de la boutique restreintes, `McpApprovalGuard` (403 si en attente/refusé, jeton antérieur compris) |
| 3 | Mineur | Commande rapide et matrice ajoutaient au panier sans passer par le garde des prix | **Corrigé** : routes restreintes |
| 4 | Mineur | Matrice de commande non masquée en mode prix masqués | **Corrigé** (test sur le registre de hooks : aucun produit à options dans le catalogue de démo) |
| 6 | Mineur | `/demo/mails` affichait le destinataire des e-mails d'approbation | **Corrigé** : adresse masquée (liste et détail) |
| 5 | Info | Un compte créé sur B2C est approuvé et voit les prix B2B via le cookie de canal | Accepté (identifiants B2B déjà publics sur `/demo/accounts`) |
| 7 | Info | `search_product_variants` (MCP) renvoie les prix sans masquage | Voulu pour un client connecté et approuvé ; un compte en attente est refusé en amont |

Fuites connues en mode prix masqués : le tri par prix révèle l'ordre relatif (pas les montants), accepté ; `/media` ne contient aucun prix ; un devis lu par son lien secret est un partage volontaire.

## Points vérifiés sans défaut

API boutique (produits, variantes, paliers en 401), pages et cartes produit (« dès », mention HT), cache privé `no-store`, flux B2B à jeton (`hash_equals`), Live Components (checksum), décision d'inscription de Sophie (seule la route POST `decide`, statut `pending` seulement), retrait des deux contournements PunchOut (parcours OrderRequest payé sur compte vert sans eux), ordre des fixtures, tests adaptés sans affaiblissement.
