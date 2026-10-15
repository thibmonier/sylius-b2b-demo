# Reprise — sprint 1 (walking skeleton), état au 2026-10-15 soir

Lancé par `/workflow:auto-sprint 001`. Branche `feature/sprint-1-walking-skeleton`, PR **brouillon #1** (thibmonier/sylius-b2b-demo).

## Phases de l'auto-sprint
| Phase | État |
|-------|------|
| 0 Normalisation | ✅ `.bmad/sprint-status.yaml` créé, dépôt GitHub créé (topics posés), branche |
| 1–2 Démarrage et décomposition | ✅ `sprint-goal.md`, `pre-sprint-checklist.md`, `task-board.md`, `tasks/` (36 tâches) |
| 3 Gate | ✅ après correction (métadonnées du sprint ajoutées dans le YAML) |
| 4 Implémentation | ✅ US-001, 002, 003, 004, 005, 007 en `review` ; US-031 `blocked` (agentic-feeds pas sur Packagist) |
| 5 PR | ✅ PR brouillon #1 |
| 6 CI | ❌ à refaire après la remédiation ci-dessous |
| 7 Revue, 8 Rétro, 9 Merge | ⏳ |

## Actions en cours (à finir dans cet ordre)
1. **L'utilisateur lance** (édition des `.env*` interdite à l'agent) :
   `! cd /Users/tmonier/Projects/sylius_plugins/sylius-b2b-demo && sed -i '' 's/^JWT_PASSPHRASE=.*/JWT_PASSPHRASE=/' .env && sed -i '' '/^APP_SECRET=/d' .env.dev .env.prod`
2. Commiter ces trois fichiers en `git commit --fixup=3ef8598`, puis lancer `GIT_SEQUENCE_EDITOR=: git rebase -i --autosquash main` et `git push --force-with-lease`. Les valeurs publiques de Sylius-Standard signalées par GitGuardian (clé JWT de test, `JWT_PASSPHRASE`, `APP_SECRET` de dev et prod, mot de passe MySQL local) sortent ainsi de l'historique de la branche. Les fixups des commits US-001, docker et CI sont déjà faits, mais pas encore poussés.
3. Environnement local : `docker compose down -v && docker compose up -d --build`. MySQL tourne désormais sans mot de passe, et l'ancien volume l'avait. Ensuite, réécrire `.env.local` et `.env.test.local` avec des URL `mysql://root@127.0.0.1:3340/...`, ce que l'utilisateur fait lui-même si l'écriture des `.env*` reste bloquée. Recréer la base de test : `doctrine:database:create`, `migrate`, `sylius:fixtures:load demo`, en `--env=test`.
4. Surveiller la CI (`gh pr checks 1 --watch`) et corriger si besoin. Note : la CI fait `composer install --no-scripts`, parce que le warmup du kernel de test sans debug bute sur `DoctrineMetadataCacheWarmer` (métadonnées chargées trop tôt par un service, cause à identifier plus tard). En local, ne jamais lancer `cache:clear --env=test` : supprimer `var/cache/test` à la place.
5. Phases 7 et 8 (revue et rétro, sous-agents), puis **pause** : merge seulement avec le GO de l'utilisateur.

## Fait pendant le sprint (hors démo)
- **punchout v1.1.1 publiée** : valeurs par défaut de `PUNCHOUT_SUPPLIER_*`, README (import de la config), icônes du menu admin préfixées `tabler:`.
- **command-palette v1.0.1 publiée** (PR #3) : une icône de menu invalide ne casse plus toutes les pages de l'admin.
- La démo requiert punchout ^1.1.1 et command-palette ^1.0.1. Le garde-fou temporaire des icônes est retiré, et les 19 tests de fumée passent.

## Constats techniques (à reporter dans la revue)
- `invoicing-plugin` 2.3 appelle KnpSnappy directement : wkhtmltox est obligatoire (installé dans l'image).
- Le canal est choisi par cookie (ADR-0011). Withdrawal n'apparaît que sur le canal B2C (condition de twig hook et voter `DEMO_CONSUMER_CHANNEL`).
- `services.yaml` réenregistre `src/`, donc les services de la démo se déclarent dans `services.yaml`, pas dans `config/packages`.
- `docker compose` lit le `.env` de Symfony pour l'interpolation : ne pas y utiliser de variables du même nom (`APP_ENV`).
- RTK réécrit `curl` et `grep -c` : utiliser `rtk proxy curl`, et ne jamais grepper les `.env*` (RTK affiche les lignes).
- Écarts de schéma restants : seulement dans le cœur Sylius, refund et messenger (DBAL 4, en amont).

## Prochain sprint (2)
US-008 (catalogue CSV), US-009 (entreprises et comptes), US-010 (données des plugins cohérentes), US-012 (commande de remise à zéro).
