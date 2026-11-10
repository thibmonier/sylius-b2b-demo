# Rétrospective du sprint 52 — Pile à jour et mode sombre du thème

**Date** : 2026-11-11

> « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. » — Directive fondamentale

## Suivi des actions du sprint 51

| Action | Résultat |
|--------|----------|
| A-51-1 valider l'environnement d'un job qui monte une application de zéro avant les assertions | Pas de nouveau job de ce type ; le job « projet vierge » a reçu une étape de diagnostic qui a permis d'identifier le plantage de `php -S` sans reproduire en local |
| A-51-2 `composer install` dans le checkout principal de la démo après une fusion qui change `composer.lock` | À appliquer après la fusion de #181 |
| A-51-3 canari Symfony AI sur toute la famille `symfony/ai-*` | Fait ; la 0.15 a été montée dès ce sprint |
| A-51-4 distinguer un rouge d'infrastructure d'un rouge réel | Appliqué : le rouge du thème après #21 a été relancé (vert), puis sa cause réelle cherchée et corrigée plutôt que classée en incident |

## Ce qui a bien marché

- Les trois dépôts (plugin IA, plugin MCP, thème) ont avancé en parallèle, un worker par dépôt, sans conflit.
- Les deux montées impossibles ont été détectées le jour du lancement, avant tout code : la licence d'apexcharts en lisant le `LICENSE.md` du paquet, le blocage MCP en résolvant la chaîne de dépendances avec l'audit de sécurité actif.
- US-274 a suivi l'ordre prévu (plugin, tag, démo) en une journée, avec un `composer.lock` limité aux trois paquets attendus.
- Le plantage intermittent de `php -S` a été réglé à la racine (PHP 8.4) avec un diagnostic conservé dans le job, au lieu d'empiler des relances.

## Ce qui a coincé

- Deux stories sur quatre (5 points sur 11) ont été retirées le jour même : la licence et la chaîne de dépendances n'avaient pas été vérifiées en préparant le sprint.
- J'ai modifié le workflow du thème pendant que le worker le modifiait aussi : ma modification a échoué sur son assertion de contrôle, sans dégât, mais le travail était en double.
- Le texte du plan citait `ignore this major version` pour apexcharts, contraire à la règle du 2026-11-04 (`ignore this dependency`) ; corrigé avant d'agir.
- Le `cache:clear` lancé après `composer update` a manqué de mémoire (128 Mo) ; relancé avec `memory_limit=1G`.

## Actions

| ID | Action | Responsable | Échéance |
|----|--------|-------------|----------|
| A-52-1 | Avant de transformer une montée majeure en story : lire la licence de la version cible et lancer `composer require --dry-run` (ou `npm install --dry-run`) avec l'audit actif ; noter le résultat dans la story | Claude | veille de la semaine 47 (2026-11-16) |
| A-52-2 | Ne pas modifier un fichier du worktree d'un worker tant qu'il travaille : lui envoyer la demande par message | Claude | sprint 53 |
| A-52-3 | Lancer les commandes de console de la démo avec `php -d memory_limit=1G` après une montée de dépendances | Claude | sprint 53 |
