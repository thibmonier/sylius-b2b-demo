# Tâches : US-176 : Installation réelle d'un pack en CI

## Informations US

- **Epic** : EPIC-14
- **Persona** : lead développeur d'une agence ; Thibaut
- **Story Points** : 5 (Should, non sacrifiable)
- **Sprint** : sprint-25-packs-et-transmission
- **Story** : [US-176](../../../backlog/user-stories/US-176.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** que la CI installe chaque pack dans un projet Sylius vierge
**Afin de** prouver qu'il fonctionne réellement et pas seulement qu'il se résout

## Règles et contraintes

- Travail dans les dépôts des packs `thibmonier/sylius-pack-ai-headless` et `thibmonier/sylius-pack-compliance-tooling`, clonés dans `/Users/tmonier/Projects/sylius_plugins/` (T-176-01). Le dépôt de la démo n'est pas modifié par cette story.
- **La CI est le juge** : aucune installation lourde en local (environ 7 Go libres, seuil de 5 Go de `bin/run-tests`). En local : `composer validate --strict`, tests des scripts, lint de workflow. Le projet Sylius vierge n'existe que dans la CI.
- Projet vierge : `Sylius-Standard` en version exacte, matrice Sylius 2.2 et 2.3 (plancher commun : PHP 8.2), pack ajouté par un dépôt `path` pointant sur le dépôt extrait (le paquet n'est pas republié pour l'essai), puis gestes du README, migrations, `lint:container`, `doctrine:schema:validate`.
- Les gestes du README sont appliqués par un script lisant un fichier de données du dépôt (source unique), et un test vérifie que le README décrit les mêmes gestes ; un geste faux fait échouer la CI.
- Un échec nomme le plugin et l'étape (enveloppe d'étape qui écrit `FAIL plugin=… step=…`).
- Les deux packs dans le même projet : conteneur compilé sans conflit de service ni de route.
- Écarts de version éventuels écrits dans le README, jamais cachés.
- **Règle 10** : la story fait du `git` (branche, commit, push) : elle se fait dans l'arbre principal, jamais dans un worktree isolé.
- **Aucun push, PR, tag ni release sans GO de Thibaut** (T-176-09). Les branches d'essai de la preuve négative sont supprimées après lecture du run rouge.
- Ton neutre, aucune comparaison avec une offre commerciale (Sylius Plus), aucun plugin « concurrent » ou « à confirmer » dans un pack ; aucun détail de sécurité.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 24 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de rendu. **Preuve : les runs de CI des deux dépôts de packs** (matrice Sylius 2.2 et 2.3, job « deux packs ensemble », runs rouges de la preuve négative), cités par numéro de run dans le tableau des scénarios.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Dépôt IA et headless : `.github/workflows/` (job d'installation), `etc/` (gestes), `bin/` (application des gestes, enveloppe d'étape), `tests/`, `README.md`, `README.fr.md`
- Dépôt Conformité et outillage : mêmes zones
- Aucun fichier de la démo

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-176-01 | [OPS] | Clones des deux dépôts de packs dans `/Users/tmonier/Projects/sylius_plugins/` (lecture seule d'abord), `git fetch` et branche courante contrôlées, branche de travail créée ; arbre principal, pas de worktree isolé (règle 10) | 0,25 h | - | Claude | ✅ |
| T-176-02 | [DOC] | Cadrage : README, `composer.json`, `etc/composition.json` et CI actuelle des deux packs lus ; gestes de l'ADR 0015 (bundles, configuration, routes, migrations) ; version exacte de Sylius-Standard pour 2.2 et 2.3 ; base de données de la CI ; format du fichier de gestes ; forme de l'échec nommant plugin et étape ; dépôt qui porte le job « deux packs ensemble » | 0,75 h | T-176-01 | Claude | ✅ |
| T-176-03 | [TEST] | Tests rouges, commités seuls et cités dans la PR de chaque dépôt : fichier de gestes appliqué sur un dossier fictif, README cohérent avec le fichier de gestes (geste manquant ou en trop refusé), enveloppe d'étape qui nomme plugin et étape, échec propagé | 1,25 h | T-176-02 | Claude | ✅ |
| T-176-04 | [BE] | Pack « IA et headless » : fichier de gestes, script d'application, enveloppe d'étape | 1,25 h | T-176-03 | Claude | ✅ |
| T-176-05 | [BE] | Pack « Conformité et outillage » : mêmes fichiers (duplication assumée, deux dépôts : règle des trois) | 0,5 h | T-176-04 | Claude | ✅ |
| T-176-06 | [OPS] | Workflow du pack IA : job d'installation en matrice Sylius 2.2 et 2.3 (projet vierge, dépôt `path`, gestes, migrations, `lint:container`, `doctrine:schema:validate`) ; actions épinglées par SHA (`bin/check-pinned-refs`) | 1,25 h | T-176-04 | Claude | ✅ |
| T-176-07 | [OPS] | Workflow du pack Conformité : même job | 0,5 h | T-176-05, T-176-06 | Claude | ✅ |
| T-176-08 | [OPS] | Job « deux packs ensemble » : les deux packs dans un même projet, conteneur compilé sans conflit de service ni de route (`debug:router`) | 0,75 h | T-176-07 | Claude | ✅ |
| T-176-09 | [OPS] | 📅 Thibaut donne son GO pour pousser les branches, ouvrir les PR et les fusionner dans les deux dépôts de packs (sans tag ni release) | 0,25 h | T-176-08 | Thibaut | ✅ |
| T-176-10 | [OPS] | Push des branches et PR des deux dépôts après GO ; CI suivie jusqu'au vert (corrections de workflow, de gestes ou de README selon les échecs réels) ; chaque cycle consigné | 1,5 h | T-176-09 | Claude | ✅ |
| T-176-11 | [TEST] | Preuves négatives en CI sur branches d'essai jetables : geste du README faux, membre qui casse l'installation (contrainte de version impossible) ; run rouge cité (plugin et étape nommés), branches supprimées, aucun merge | 0,75 h | T-176-10 | Claude | ✅ |
| T-176-12 | [DOC] | README FR et EN des deux packs : gestes corrigés si la CI en révèle de faux, écart de matrice écrit s'il existe, section sur la vérification en CI ; texte relu contre le fichier de gestes et le run | 0,75 h | T-176-11 | Claude | ✅ |
| T-176-13 | [OPS] | CI verte (tous les jobs, run terminé), puis merge des PR des deux dépôts ; aucun tag | 0,5 h | T-176-12 | Orchestrateur | ✅ |
| T-176-14 | [REV] | Revue (aucun plugin exclu dans un pack, ton neutre, parité FR et EN, aucun détail de sécurité) et table « scénario → preuve » remplie | 0,25 h | T-176-13 | Claude | ✅ |

**Total estimé : 10,5 h** (14 tâches, dont 0,25 h de Thibaut ; 10,25 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

PR fusionnées (squash) : Conformité #1 (`2eda2ba`), IA #1 (`b4c396e`). Commits rouges, tests seuls : IA `09ac319` `dc973aa` `a141304` `6e88d96` `1031207` `0dd0fe4` `1be6219` ; Conformité `6c1de62` `f44e0f3` `5cde8a3` `bbca2cc` `0eeb4bd` `1a8a250` `af72809`.

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Pack installé et démarré (nominal) | Jobs `install`, runs 40036530846 (IA) et 40036324215 (Conformité) : migrations, `lint:container`, `debug:router`, `doctrine:schema:validate --skip-sync`, « No difference on the tables of the pack » (13 tables pour l'IA) |
| Matrice de versions (alternatif) | Mêmes runs : Sylius-Standard 2.2.4 (Sylius ~2.2.9) et 2.3.0 (~2.3.0) verts ; écarts écrits dans les README (PHP 8.3 exigé par Standard, FOSRestBundle absent du squelette 2.2.4, schéma de base désynchronisé) |
| Les deux packs ensemble (alternatif) | Job `together`, run 40036530846 : « OK 711 routes, no conflict » (`bin/check-routes`), 21 tables |
| Membre qui casse l'installation (erreur) | Branches d'essai jetables, supprimées : run 40036044073 (IA) → `FAIL plugin=tmonier/sylius-webhook-outbox-plugin step=composer-require` ; run 40036046475 (Conformité) → `FAIL plugin=tmonier/sylius-gpsr-plugin step=composer-require` |
| Geste du README faux (erreur) | Branche d'essai jetable, run 40035832442 (IA, classe de bundle fausse) → `FAIL plugin=tmonier/sylius-webhook-outbox-plugin step=database-create` ; cohérence README/gestes : `bin/check-gestures`, testé dans `tests/gestures.php` |
