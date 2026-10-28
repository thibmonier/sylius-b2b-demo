# Chaîne d'approvisionnement des dépôts de plugins (US-209)

Les 27 plugins et les 2 packs appliquent la même hygiène que la démo (US-208, voir [durcissement-demo.md](durcissement-demo.md)). La liste des dépôts est versionnée dans `config/demo/plugin-repositories.json` (noms publics seulement ; le premier est le dépôt pilote).

## Les 9 contrôles

| Contrôle | Attendu |
|----------|---------|
| `pinned-actions` | chaque action est référencée par le SHA d'un commit, la version en commentaire (`bin/check-pinned-refs` de la démo) |
| `permissions` | `permissions: {}` au niveau du workflow, puis les permissions de chaque job |
| `persist-credentials` | chaque `actions/checkout` porte `persist-credentials: false` |
| `pull-request-target` | aucun déclencheur `pull_request_target` |
| `zizmor` | un job zizmor, image figée par version et condensat, sans réseau (`--offline`) |
| `composer-audit` | `composer audit` bloquant, après l'installation (`--locked` si le dépôt versionne `composer.lock`) |
| `policy` | `config.policy` explicite dans `composer.json` (elle ne protège que la CI du dépôt, pas les projets qui installent le plugin) |
| `allow-plugins` | `config.allow-plugins` en liste explicite de booléens |
| `dependabot` | Composer et GitHub Actions, chacun avec un délai de maturité (`cooldown`) |

Dixième ligne du rapport, hors des neuf : `lock-review`, la revue du diff de `composer.lock` (`bin/check-lock-diff`). Elle est « sans objet » dans un dépôt qui ne versionne pas son verrou, ce qui est le cas des 29 dépôts.

## Commandes

```bash
bin/plugin-supply-chain list [--lot=N]       # noms des dépôts ; lot 0 = pilote, lots 1 à 4 = sept dépôts
bin/plugin-supply-chain check DIR            # les contrôles sur une copie locale (code de sortie 1 si l'un échoue)
bin/plugin-supply-chain apply DIR            # simulation : liste les fichiers qui changeraient
bin/plugin-supply-chain apply DIR --write    # écrit les changements
bin/plugin-supply-chain report [NOM...]      # les contrôles sur les branches par défaut distantes (gh)
```

`apply` ne touche que `.github/` (workflows, `dependabot.yml`) et la section `config` de `composer.json` : ni code, ni dépendance, ni `composer.lock`. Une action absente de la table `pinnedActions` de la configuration arrête la commande : le SHA se résout une fois (`gh api repos/<action>/commits/<version>`) et s'ajoute à la table.

## Règle « plugin-repo-branch-check »

Avant tout commit dans un dépôt de plugin : `git fetch origin`, contrôle de la branche courante et de son retard sur la branche par défaut distante, puis branche `chore/supply-chain-hardening` créée depuis `origin/<branche par défaut>`. `apply --write` fait ce contrôle et refuse la branche par défaut comme une branche en retard. Le script refuse aussi un chemin hors de `/Users/tmonier/Projects/sylius_plugins/` et un dossier qui n'est pas un dépôt Git.

## Ce qui ne change pas

Aucun tag, aucune version publiée : seules la CI et `config` changent. Une PR par dépôt, fusionnée quand la CI est verte et terminée. Un dépôt à CI rouge préexistante, sans CI ou à branche protégée est signalé, pas forcé.
