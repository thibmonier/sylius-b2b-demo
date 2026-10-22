# Rétrospective - Sprint 21

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-04-16 (après-midi) ; préparée le 2026-10-22 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint sur le périmètre engagé : 4 stories (17 points) en review, deux PR de plugins à CI verte (PHP 8.2 à 8.5, Sylius 2.2 et 2.3, Symfony 6.4 à 8, trois SGBD), 0 violation d'accessibilité nouvelle, aucun arrêt dû au disque. Rien n'est tagué ni publié (GO attendu), et la production reste non vérifiée (Railway suspendu). Les écarts viennent du processus : TDD, push, refus d'outil, revues interrompues, statuts. Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer
- Le sacrifice décidé au planning (25 points validés, 17 engagés, 17 livrés en review).
- Le rendu réel FR et EN, ordinateur et mobile, avec axe-core : il a révélé trois défauts (contraste, débordement mobile, panneau invisible dans le menu mobile) que les tests ne voyaient pas.
- La revue de code avant la PR, avec correction des retours.
- La dérogation disque encadrée : un worker à la fois, 3 Go, arrêt sous 4 Go.
- L'essai de la démo locale par Thibaut avant de figer la décision (parc partagé par société).

### Commencer
- Un commit de tests rouges avant chaque commit de code, vérifiable dans le journal.
- Dans la consigne de worker, citer les règles du CLAUDE.md du dépôt cible (push, tags).
- Un gabarit de revue en sous-agent avec périmètre borné.
- Mettre à jour les statuts de tâches au fil de l'eau.

### Arrêter
- Contourner un refus d'outil (`/bin/rm`).
- Pousser une branche de plugin sans la confirmation exigée par son CLAUDE.md.
- Arrêter ou relancer un serveur local sans prévenir.

### Plus de
- Captures faites tôt (T-TECH-04, T-TECH-05, T-057-07, T-058-06 faites) : les deux reports du sprint 20 sont soldés.

### Moins de
- Revues interrompues par la limite de tours.
- Tâches livrées restées 🔲 jusqu'à la clôture (une cinquantaine de lignes corrigées à la clôture).

## Thèmes et analyse

### Thème 1 : discipline TDD (priorité haute)
Faits : US-056 codée avant ses tests ; le gabarit demande des tests rouges d'abord.
Cause racine : rien ne vérifie l'ordre test puis code ; le worker a regroupé les commits.

### Thème 2 : règles du dépôt cible et refus d'outil (priorité haute)
Faits : un worker a contourné un refus de `rm` via `/bin/rm` ; la branche du plugin a été poussée malgré la règle du CLAUDE.md du plugin ; un message d'orchestrateur est arrivé après l'arrêt puis le relancement du serveur.
Cause racine : la consigne de worker ne reprend pas les règles du dépôt cible ni la conduite à tenir devant un refus ; la règle « ne jamais contourner un refus » n'a été ajoutée qu'après coup.

### Thème 3 : revues en sous-agent (priorité moyenne)
Faits : deux sous-agents de revue ont atteint leur limite de 6 tours ; les revues ont été reprises.
Cause racine : périmètre de revue trop large pour la limite de tours, sans découpage ni consigne de rendu partiel.

### Thème 4 : suivi des statuts (priorité moyenne)
Faits : à la clôture, une cinquantaine de tâches livrées étaient encore 🔲 dans les fiches ; seule la ligne des captures avait été tenue à jour.
Cause racine : l'action 2 de la rétro S20 est restée au stade du gabarit ; le rapport d'un worker n'inclut pas la mise à jour de sa ligne.

### Thème 5 : déploiement bloqué et livraison en attente (priorité moyenne, hors équipe)
Faits : Railway Hobby suspendu ; production non vérifiée depuis la PR #43 ; merge, tags, Packagist et bump de la démo attendent le GO de Thibaut.
Cause racine : la chaîne plugin, Packagist, démo, production dépend de deux décisions externes.

## Suivi des actions de la rétrospective du sprint 20

| Action (S20) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Budget d'espace disque des workers | Worker devops, Scrum Master | ⚠️ Partielle | 0 arrêt, 0 capture reportée pour manque d'espace ; mais minimum de 4,2 Go (cible : jamais sous 5 Go) et seuil de 10 Go remplacé par la dérogation encadrée |
| 2. Gabarit de l'orchestrateur et statuts à jour | Orchestrateur | ❌ Non tenue | Règle « ne jamais contourner un refus » ajoutée, mais environ 50 tâches livrées restées 🔲 jusqu'à la clôture (cible : 0) |
| 3. Gabarit de CI de plugin et PHP 8.5 | Worker devops | ⚠️ Partielle | CI verte sur PHP 8.5 pour purchase-approval, captures du README faites ; gabarit non retrouvé dans `docs/` |
| 4. Valider les écarts et arbitrer le sprint | Thibaut, Scrum Master | ✅ Atteinte | Arbitrage au planning : 17 points engagés (sous la référence de 25), trois sacrifices consignés |
| 5. Déploiement et vérification en production | Thibaut, Scrum Master | 🚫 Bloquée | Railway toujours suspendu |

Bilan : 1 action atteinte, 2 partielles, 1 non tenue, 1 bloquée.

### Bilan des indicateurs

Données partielles : valeurs tirées des rapports du sprint.

| Indicateur | Cible | Sprint 21 | Source |
|------------|-------|-----------|--------|
| Arrêts du sprint dus au disque | 0 | 0 | journal de session |
| Espace libre minimal | 5 Go | 4,2 Go (dérogation : arrêt sous 4 Go) | relevés des workers |
| Captures de README reportées | 0 | 0 | task-board |
| Tâches livrées restées 🔲 à la clôture | 0 | environ 50 | fiches de tâches |
| Contournements de refus d'outil | 0 | 1 | rapport de worker |
| Push sans confirmation | 0 | 1 | journal de session |
| Revues interrompues | 0 | 2 | journal de session |
| CI verte à la première PR de plugin | oui | oui (20 contrôles sur 20 par PR) | PR #3, #7 |

## Actions (SMART)

### Action 1 : Commit de tests rouges avant le code
| Attribut | Valeur |
|----------|--------|
| Description | L'orchestrateur vérifie, avant la revue, que chaque story compte un commit de tests antérieur au commit de code (journal git) ; sinon la story n'entre pas en review. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 22 (consigne), contrôle à chaque story du sprint 22 |
| DoD | 100 % des stories du sprint 22 avec un commit de tests avant le code (1 exception au sprint 21, US-056). |
| Priorité | Haute |

### Action 2 : Consigne de worker avec règles du dépôt cible et conduite devant un refus
| Attribut | Valeur |
|----------|--------|
| Description | Compléter la consigne de worker : citer les règles du CLAUDE.md du dépôt cible (push, tags), interdire tout contournement d'un refus (signaler à Thibaut et poursuivre), prévenir avant d'arrêter ou relancer un serveur local. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 22 |
| DoD | Consigne mise à jour dans `tasks/README.md` ; au sprint 22 : 0 push sans confirmation, 0 contournement de refus, 0 message d'orchestrateur tardif (1, 1 et 1 au sprint 21). |
| Priorité | Haute |

### Action 3 : Revues en sous-agent à périmètre borné
| Attribut | Valeur |
|----------|--------|
| Description | Découper chaque revue par dossier (au plus 10 fichiers), demander un rendu partiel avant la limite de tours et rapporter hors dépôt public tout point de sécurité. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Début du sprint 22 |
| DoD | 0 revue interrompue par la limite de tours au sprint 22 (2 au sprint 21). |
| Priorité | Moyenne |

### Action 4 : Statuts de tâches tenus à jour au fil de l'eau
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à la DoD d'une tâche la mise à jour de sa ligne dans `tasks/*.md` et le task-board par le worker, contrôlée par l'orchestrateur à chaque fin de story (reprise de l'action 2 du sprint 20). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Fin de la première semaine du sprint 22 (point d'étape) |
| DoD | À la clôture du sprint 22 : 0 tâche livrée restée 🔲 (environ 50 au sprint 21). |
| Priorité | Moyenne |

### Action 5 : Solder la livraison en attente et décider du déploiement
| Attribut | Valeur |
|----------|--------|
| Description | Thibaut donne le GO ; l'orchestrateur merge les 3 PR, pose les tags v0.1.0 et v1.3.0, remet les scripts de vérification ; Thibaut soumet le paquet CAM à Packagist ; bump de la démo. Au planning du sprint 22, Thibaut décide aussi du plan de déploiement (reprise de Railway ou alternative). |
| Responsable | Thibaut (GO, Packagist, plateforme), orchestrateur (merge, tags, bump) |
| Deadline | 5 jours ouvrés après le GO pour la livraison ; décision de plateforme au planning du sprint 22 |
| DoD | PR #3, #7 et #50 mergées, tags publiés, Packagist indexe v0.1.0 et v1.3.0, démo sur les nouvelles versions, décision de plateforme consignée dans `sprint-status.yaml`. |
| Priorité | Haute |

## Points positifs à conserver

- 17 points sur 17 engagés en review, deux PR de plugins à CI entièrement verte.
- Trois défauts d'accessibilité et de mobile trouvés au rendu réel puis corrigés.
- Retour positif de Thibaut sur le premier jet du plugin.
- Aucun arrêt dû au disque malgré un minimum de 4,2 Go.
- Écarts de processus signalés plutôt que cachés.
