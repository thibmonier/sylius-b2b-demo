# Rétrospective — sprint 1 (walking skeleton)

Date : 2026-10-15. Équipe : solo + agents Claude. Format : Start / Stop / Continue + directive fondamentale.

> **Directive fondamentale de la rétrospective** : quels qu'aient été les constats, nous comprenons et croyons sincèrement que chacun a fait de son mieux avec ce qu'il savait, ses compétences, les ressources disponibles et la situation du moment.

## Sprint Goal — atteint ?

Objectif : un walking skeleton Sylius 2.3 réunissant les 21 plugins tmonier, deux canaux B2B/B2C sur un domaine, image Docker FrankenPHP, CI verte, déployable sur Railway.

**Atteint.** US-001, 002, 003, 004, 005, 007 livrées et revues. US-031 (agentic-feeds) légitimement bloquée (plugin pas sur Packagist). CI verte, 19 smoke tests, revue indépendante passée sans défaut critique/haut.

## Ce qui a bien marché (Continue)

- **Revue par 3 agents indépendants** avant clôture : a produit des constats concrets et croisés (argon2i, en-têtes, `.dockerignore` relevés par 2 agents), conformément à la leçon « relecture indépendante ».
- **Vérification systématique des rapports** : le constat « majeur » order-hold était un faux positif, écarté en 2 minutes de vérification plutôt que corrigé à tort. La leçon « ne jamais croire un rapport de builder/reviewer » a payé une fois de plus.
- **Secrets maîtrisés** : la remédiation GitGuardian (rebase/autosquash) a tenu ; audit confirme l'historique propre.
- **ADR au fil de l'eau** (0011 pour le cookie de canal) : la revue a pu s'appuyer dessus pour distinguer by-design et défaut.

## Ce qu'il faut arrêter / corriger (Stop)

- **Builders trop optimistes** : les rapports des agents de revue surévaluent (vendor « 20 paquets », `.dockerignore` « absent » alors qu'il existe). → toujours recouper avec le dépôt réel.
- **Agents de revue qui s'arrêtent à 6-8 tours sans livrer** : piège récurrent (déjà noté sur punchout). Coût en relances.
- **Warmup kernel test contourné** (`composer install --no-scripts`) : dette non résolue (`DoctrineMetadataCacheWarmer`). À ne pas laisser s'installer.

## À tenter (Start)

- **Donner d'emblée aux agents de revue** la consigne « livre ton rapport avant la limite de tours, même partiel » (fait cette fois en relance — à mettre dès le prompt initial).
- **Un ticket « cause racine warmup test »** dans le backlog sprint 2, pour sortir du `--no-scripts`.
- **Renovate/Dependabot** dès le sprint 2 pour le pinning SHA (images Docker + actions GitHub), au lieu d'épingler à la main.

## Actions concrètes pour le sprint 2

1. US-008 (catalogue CSV), US-009 (entreprises/comptes), US-010 (données plugins cohérentes), US-012 (reset nocturne).
2. Intégrer les 14 constats reportés de `review-findings.md`, en priorité : portée de `DemoChannelContext` (R1/R2), atomicité entrypoint + reset (R3/R4, à concevoir avec US-012), bornage threads FrankenPHP (R5).
3. Ouvrir le ticket cause racine warmup test (R9).
4. Config Railway au déploiement : `APP_SECRET`, `JWT_PASSPHRASE`, healthcheck `/healthz`, dimensionnement mémoire.

## Métriques

- Stories livrées : 6/7 (1 bloquée externe).
- CI : verte (Quality & smoke ✅, Docker ✅, GitGuardian ✅).
- Revue : 1 faux positif écarté, 0 critique/haut, 6 quick wins appliqués, 14 reportés.
- Publications annexes pendant le sprint : punchout v1.1.1, command-palette v1.0.1.
