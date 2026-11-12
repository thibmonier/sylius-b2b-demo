# Consignes aux workers de développement

Transmises à chaque worker (sous-agent) au lancement d'une story. Issues des rétrospectives des sprints 50 à 53.

## Dépôt et branche

- Lire le dépôt cible avec `git remote get-url origin` avant toute commande `gh -R` (A-53-4). Le thème est `thibmonier/sylius-industry-trade-theme`.
- Travailler uniquement dans son worktree ; branche créée avec `--no-track` ; push explicite `git push origin HEAD:<branche>` ; vérifier ensuite `headRefOid` de la PR (A-50-1).
- `git fetch` et vérification de la branche courante avant chaque commit.

## Rouge, puis vert (A-53-3)

1. Écrire le test qui décrit le comportement attendu.
2. Le lancer en local : il doit échouer sur une **assertion** (pas une erreur de syntaxe, de style ou d'analyse statique).
3. Formater (`vendor/bin/ecs check --fix`) avant de commiter : un échec ECS en CI n'est pas un rouge valide.
4. Commiter le test seul, le pousser, puis **s'arrêter** et rendre la main avec le SHA et le message d'échec attendu.
5. L'orchestrateur lit le rouge dans les journaux de la CI, puis pousse le correctif (préparé par le worker dans un commit local non poussé, ou écrit par l'orchestrateur).

## PHPStan (A-53-1)

- Le verdict PHPStan est celui de la CI : en local, les dépendances résolues peuvent différer (Symfony 8.1 au lieu de `^7.4`) et produire des erreurs que la CI n'a pas.
- Une erreur PHPStan n'est « préexistante » que si elle apparaît aussi sur le dernier run de `main` (`gh run list --branch main`, puis `gh run view <id> --log`). Sinon, elle est à corriger dans la story.
- Toute erreur PHPStan dans un fichier modifié par la story est à corriger, quelle que soit sa cause.

## Compte rendu

- Moins de 50 mots : SHA poussé, test rouge attendu, ce qui reste à faire.
- Aucune fusion, aucun tag, aucune modification d'un autre dépôt.
