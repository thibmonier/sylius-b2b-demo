# Sprint 11 : Fiabilité et ouverture (reset fiable, préparation de l'ouverture publique)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 11 |
| Début | 2026-11-13 |
| Fin | 2026-11-27 |
| Durée | 11 jours ouvrés (aucun férié) |
| Capacité | 8 points, environ 30-35 h (38 h décomposées, dont 3 h calendaires ; 6 h sacrifiables) |
| Branche | `feature/sprint-11-fiabilite-et-ouverture` |

## Sprint Goal

> « Rendre la remise à zéro nocturne fiable (toute erreur fatale est vue par `/healthz`) et préparer l'ouverture publique de la démo : revue de sécurité finale, mode public testé, runbook d'ouverture et retour arrière. »

## Sprint Backlog

| ID | Titre | Points | Heures | Statut |
|----|-------|--------|--------|--------|
| US-049 | Fiabilité de la remise à zéro | 3 | 12 h | ready-for-dev |
| US-050 | Préparer l'ouverture publique de la démo | 5 | 26 h | ready-for-dev |

Engagement : 8 points, 21 tâches, 38 h décomposées (35 h de charge hors calendaires) pour ≈ 30-35 h de capacité. Ordre de sacrifice dans [tasks/README.md](./tasks/README.md).

## Décisions de l'utilisateur (2026-10-17)

- **US-049** : marqueur « en cours » ou fonction d'arrêt pour les erreurs fatales ; `cache:clear` / `cache:warmup` en sous-processus (le `-d memory_limit=1G` du crontab est conservé) ; plafond `request_body max_size` côté Caddy ; limite de `demo_orders` (1 911 requêtes, cœur Sylius) actée en documentation, sans code ; T-048-11 (relevé de coût) pas avant 2026-10-23, par l'utilisateur.
- **US-050** : démo publique mais **non promue** sur les canaux Sylius, aucune donnée réelle ; revue de sécurité indépendante avant ouverture ; moniteur externe sur `/healthz` créé par l'utilisateur ; bascule le jour J sur GO explicite, éventuellement hors sprint.
- Actions de la rétro du sprint 10 : contrat de fin de worker en version courte (action 2) et règle d'isolation des workers par chemins explicites (action 1), dans [tasks/README.md](./tasks/README.md).

## Dépendances

- US-049 dépend d'US-033, US-041 et US-048 (livrées). La sécurité de `POST /demo/webhooks/receive` s'appuie sur US-029 (sprint 10).
- US-050 dépend d'US-035, US-036, US-037, US-040 et US-030 (livrées) ; le moniteur externe suppose US-049 (`/healthz` fiable).
- Tâches [OPS] : GO et comptes de l'utilisateur (Railway, moniteur, assistant MCP).

## Risques

| Risque | Mitigation |
|--------|------------|
| La revue de sécurité révèle des constats bloquants plus gros que le budget de correction (4 h) | Correctifs bloquants prioritaires, le reste tracé et reporté ; la bascule n'est pas décidée par le sprint |
| Tests des deux modes (porte, inscriptions) fragiles à cause de variables d'environnement globales | Configuration par mode dans les tests, jamais d'état partagé |
| `request_body max_size` non testable hors Caddy | Test de fumée sur l'image ou test d'intégration Caddy en CI ; sinon tâche sacrifiable |
| Bascule ou parcours MCP sans GO dans le sprint | Calendaires, hors chemin critique, jamais closes sur une estimation |
| Reset en échec fatal difficile à simuler | Option `--simulate-failure` existante étendue ou sous-processus de test dédié |

## Contraintes

Aucun push, merge ni déploiement sans GO de l'utilisateur. Aucun commit sans demande. Chaque worker rend le bloc de rapport de fin (contrat de fin de worker) et respecte la règle d'isolation (`git add` par chemins explicites).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS et PHPStan verts
- [ ] Libellés FR et EN si interface
- [ ] Aucun secret dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2026-11-13 | 1 h |
| Sprint Planning Part 2 (Comment, confirmation de la décomposition) | 2026-11-13 | 1 h |
| Daily Scrum | Chaque jour | 15 min |
| Affinage Backlog | 2026-11-20 | 1 h |
| Sprint Review | 2026-11-27 (matin) | 1 h |
| Rétrospective | 2026-11-27 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
