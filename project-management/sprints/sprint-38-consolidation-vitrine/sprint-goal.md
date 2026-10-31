# Sprint 38 : Consolidation vitrine — copilote et passeport installés, Registration Approval et fiche équipement

> **Planifié le 2026-10-30**, après la clôture du sprint 37 (PR #142). Périmètre décidé par Thibaut le 2026-10-30 : **10 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches sera faite au planning. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-38`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 38 |
| Début | 2026-10-30 (planning) |
| Fin | **2026-11-13** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **10 points** ; tâches à décomposer |
| Vélocité de référence | S32 9, S33 13, S34 9, S35 8, S36 11, S37 13 |
| Branche | `feature/sprint-38-consolidation-vitrine` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre retient 10 points : copilote d'achat installé dans la démo avec captures (US-244, 3 pts), démo Registration Approval en mode public et contrôle des boutons « Voir la démo » (US-243, 3 pts), fiche équipement dans le plugin customer-asset-management (US-242, 3 pts), passeport vérifié en production avec test de fumée (US-245, 1 pt). Les actions de rétro du sprint 37 (T-TECH-38-01 à 05) seront intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, disque 7,1 Go libres le 2026-10-30 (alerte sous 15 Go) ; tests ciblés seulement ; suite complète de la démo par la CI.

## Sprint Goal

> « Installer le copilote d'achat dans la démo avec ses captures, vérifier le passeport produit en production, rendre la démo Registration Approval utilisable en mode public et créer la fiche équipement avec lien vers le passeport et les pièces compatibles. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-244 : 1+2+2, US-243 : 1+2+2, US-242 : 1+2+2, US-245 : 1+0+1). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-244](../../backlog/user-stories/US-244.md) | Copilote d'achat : installation dans la démo et captures du widget | 3 | Should | ready-for-dev | Dépend d'US-211 (v0.1.0) ; captures pour README |
| [US-243](../../backlog/user-stories/US-243.md) | Vitrine : démo Registration Approval en mode public et contrôle des boutons « Voir la démo » | 3 | Should | ready-for-dev | Origine : audit fonctionnel 2026-10-30 |
| [US-242](../../backlog/user-stories/US-242.md) | Parc d'équipements : fiche équipement avec lien vers le passeport et les pièces compatibles | 3 | Should | ready-for-dev | Dépend d'US-233 ; plugin customer-asset-management |
| [US-245](../../backlog/user-stories/US-245.md) | Passeport produit : vérification en production et test de fumée du lien passeport | 1 | Should | ready-for-dev | Dépend d'US-241 ; vérification post-déploiement |

Total engagé : **10 points**.

- **US-244** installe le plugin copilote ^0.1 dans la démo, configure le canal B2B dans `enabled_channels`, ajoute l'entrée au catalogue de la vitrine et produit les captures FR et EN pour les README du plugin.
- **US-243** rend la démo Registration Approval utilisable en mode public (page dédiée ou scénario adapté) et ajoute un test qui parcourt toutes les `demo_route` du catalogue en mode public.
- **US-242** crée la fiche équipement dans le plugin customer-asset-management (route, contrôleur, template, hook Twig) avec le bloc passeport et pièces compatibles accroché par le plugin passeport.
- **US-245** vérifie que les passeports sont visibles en production après une remise à zéro et ajoute un test de fumée du lien passeport sur EPI-CASK-001.
- **Tâches techniques** (actions de rétro du sprint 37) : gabarit de test fonctionnel partagé (T-TECH-38-01), tests en local obligatoires avant tout push (T-TECH-38-02, règle 46), TaskStop automatique après passation (T-TECH-38-03), suite complète ou pré-contrôle avant PR de fin de sprint (T-TECH-38-04), test d'installation vérifiant kernel.bundles (T-TECH-38-05).

**Hors sprint** : US-212 reportée.

## Décisions de Thibaut (2026-10-30, registre)

1. Périmètre : US-244 (3), US-243 (3), US-242 (3), US-245 (1), soit 10 points, plus les actions de rétro T-TECH-38-01 à 05.
2. Contraintes machine : 8 Go RAM, disque 7,1 Go libres le 2026-10-30 (alerte sous 15 Go) ; tests ciblés seulement ; suite complète de la démo par la CI.
3. Actions de rétro du sprint 37 (T-TECH-38-01 à 05) : intégrées comme tâches transverses.

## Estimation du sprint

À compléter après décomposition en tâches.

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote, passeport et parc avec Sylius Plus. En cas de recoupement avéré pendant le sprint, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugins). Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-30)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 37 fusionné (PR #142), US-211, US-232, US-233, US-241 en done, copilote v0.1.0 et passeport v0.2.0 publiés
- [x] Actions de la rétrospective du sprint 37 reprises :
  - action 1 : T-TECH-38-01 (gabarit de test fonctionnel partagé) ;
  - action 2 : T-TECH-38-02 (tests en local obligatoires avant tout push, règle 46) ;
  - action 3 : T-TECH-38-03 (TaskStop automatique après passation) ;
  - action 4 : T-TECH-38-04 (suite complète ou pré-contrôle avant PR de fin de sprint) ;
  - action 5 : T-TECH-38-05 (test d'installation vérifiant kernel.bundles).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-30 (roadmap-innovation, sprint 38)
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [ ] Capacité calculée : à calculer après décomposition
- [x] Fiches US-244, US-243, US-242, US-245 à jour (en-tête « Sprint 38 »)
- [x] Branche `feature/sprint-38-consolidation-vitrine` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md) (US-244 ← US-211, US-242 ← US-233, US-245 ← US-241)
- [ ] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] Tags demandés quand les plugins sont fusionnés

## Definition of Ready (contrôle du 2026-10-30)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-244 | oui | oui (1+2+2) | US-211 fusionnée, plugin copilote v0.1.0 publié sur Packagist | prête (dépendance US-211) |
| US-243 | oui | oui (1+2+2) | aucun | prête |
| US-242 | oui | oui (1+2+2) | US-233 fusionnée, plugin customer-asset-management publié | prête (dépendance US-233) |
| US-245 | oui | oui (1+0+1) | US-241 fusionnée, plugin passeport installé dans la démo | prête (dépendance US-241) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code (US-244, US-243, US-242, US-245) : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-244 : plugin copilote ^0.1 installé dans la démo (`bin/check-lock-diff --base=main` cité), assets et routes importés, `enabled_channels` réglé sur le canal B2B ; entrée du catalogue ; tests fonctionnels ; captures FR et EN pour README (PR du plugin, fusion à CI verte) ; audit pa11y AAA sans erreur
- [ ] US-243 : démo Registration Approval utilisable en mode public (page dédiée ou scénario adapté, FR et EN) ; test qui parcourt toutes les `demo_route` du catalogue en mode public et refuse un 403 ou un 404 ; rouge prouvé sur l'état actuel
- [ ] US-242 : fiche équipement dans customer-asset-management (route, contrôleur, template, hook Twig) ; bloc passeport accroché par le plugin passeport ; rouge prouvé en CI ; README EN et FR des deux plugins à jour, captures de la fiche
- [ ] US-245 : vérification en production consignée (URL et statut HTTP) ; test de fumée du lien passeport sur EPI-CASK-001, rouge prouvé
- [ ] **Tout état écrit hors base** (règle 16) : aucune variable d'environnement nouvelle dans la démo ce sprint

## Cérémonies

| Cérémonie | Date | Heure | Durée | Participants |
|-----------|------|-------|-------|--------------|
| Sprint Planning Part 1 (QUOI) | 2026-10-30 | 09:00 | 2h | PO, Scrum Master, équipe |
| Sprint Planning Part 2 (COMMENT) | 2026-10-30 | 14:00 | 2h | Équipe |
| Daily Scrum | Chaque jour | 09:30 | 15min | Équipe |
| Affinage du backlog | 2026-11-06 | 14:00 | 1h | PO, Scrum Master, équipe |
| Sprint Review | 2026-11-13 | 14:00 | 2h | PO, Scrum Master, équipe, parties prenantes |
| Rétrospective | 2026-11-13 | 16:30 | 1h30 | Scrum Master, équipe |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth
