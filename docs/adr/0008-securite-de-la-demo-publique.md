# ADR-0008 — Sécurité de la démo publique

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Des comptes publics, admin compris, sont exposés sur Internet.

## Décision

Garde de l'admin de démo (écritures refusées sur les routes sensibles), comptes de démo non modifiables, **aucune sortie réelle** (Mailpit, récepteur de webhooks interne, IA démo, HttpClient scopé), rate limiting, en-têtes de sécurité, remise à zéro nocturne. Seule l'alerte d'échec de remise à zéro sort, vers une URL de l'opérateur.

## Alternatives considérées

Admin en lecture seule complète : rend la démo back-office sans intérêt. Pas d'admin public : contraire à l'objectif (Hugo veut voir l'admin).

## Conséquences

La démo back-office reste interactive, et les dégâts possibles disparaissent au plus tard à la remise à zéro suivante.

## Mise en œuvre (sprint 6)

La porte privée (US-032) et le durcissement applicatif (US-036) sont décrits dans [durcissement-demo.md](../durcissement-demo.md) : variables d'environnement, comportement attendu, statut de chaque constat des revues des sprints 2, 4 et 5 et risques acceptés. Choix structurants :

- **Fail-closed** : en `DEMO_MODE=1`, une démo sans identifiants de porte, ou dont `MAILER_DSN` n'est pas le Mailpit interne, ne sert aucune page (503) et son `/health` est rouge.
- **Un seul point de vérité par préoccupation** : un écouteur de requête pour la porte, un pour les limites de débit, un pour les en-têtes ; tous dans `src/Demo/Http`.
- **Pas de CSP stricte** : la CSP « compatible » garde les scripts et styles en ligne du thème et de l'admin Sylius, et restreint le reste à l'origine.

## Amendement (2026-10-16)

- **Alerte de remise à zéro** : l'URL d'alerte opérateur est abandonnée (aucune sortie réelle, pas même celle-là). L'échec est signalé par `DEMO_RESET_FAILED` sur stderr (journal Railway), un code de sortie non nul et les notifications e-mail natives de Railway (échec de déploiement / crash). Runbook : `docs/remise-a-zero-demo.md`.
- **Admin de démo** : politique et matrice des écritures dans `docs/securite-admin-demo.md` (`ROLE_DEMO_ADMIN`, liste blanche de routes, refus par défaut des écritures non classées et de toute suppression, routes de mot de passe oublié fermées, risques acceptés).
