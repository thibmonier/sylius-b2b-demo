# Rétrospective - Sprint 22

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-11-06 (après-midi), préparée le 2026-10-23 |
| Durée | 1 h 30 |
| Format | Starfish, comme au sprint 21, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint. Les 9 stories engagées après sacrifice (23 points) sont en done, et les 23 stories des sprints 17 à 21 sont closes après vérification : plus aucune story n'est en review.

La vérification a trouvé 4 défauts, tous corrigés dans la journée (PR #62, #63, plugin v0.1.1 et README de company-insights). Le plugin du parc d'équipements a eu sa première release corrective.

Les écarts du sprint :
- un scénario d'erreur d'US-084 n'est pas livré ;
- la discipline TDD n'est pas visible dans le journal git ;
- des statuts de tâches sont restés en retard ;
- la machine a planté par manque de ressources.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer
- La vérification story par story sur la démo locale et en production (lecture seule), avec un verdict écrit par story : elle a trouvé 4 défauts que la CI ne voyait pas.
- Un correctif livré le jour même pour chaque défaut, dans sa propre PR.
- Le sacrifice décidé au plan : US-062 puis US-154, dans l'ordre prévu, sans arbitrage de dernière minute.
- Respecter les refus d'outil : 4 refus rencontrés, aucun contourné.
- Ne rien pousser, taguer ou publier sans le GO de Thibaut (plugin v0.1.1, PR de la démo).
- Les relectures humaines groupées (stories de communication, registre), validées en une demi-journée par Thibaut.

### Commencer
- Avant de passer une story en done, confronter chaque scénario Gherkin à un test ou à une preuve.
- Cocher la tâche dans le même commit que son livrable.
- Faire un relevé de la mémoire et du disque avant de lancer un serveur lourd (démo locale, MySQL), et l'arrêter dès que la vérification est finie.

### Arrêter
- Laisser à la consigne de worker (règle 8) la formule « contourner proprement » : elle contredit la règle « ne jamais contourner un refus ».
- Écrire le test après le code (1235f07 après c35068f pour US-167).

### Plus de
- Des preuves de production lues sans connexion (`bin/verify-public-mode`, en-têtes, LCP mobile).

### Moins de
- Des tâches transverses laissées en 🔲 alors que leur travail est fait : 9 à la clôture.
- Des tests instables dans la CI : `RunTestsLockTest` a échoué une fois sur la PR #64.

## Thèmes et analyse

### Thème 1 : le done ne contrôle pas les scénarios (priorité haute)
Faits : US-084 est passée en done alors que son scénario d'erreur « Preuve en retard » n'est pas livré, et ses tâches T-084-02 et T-084-04 étaient encore 🔲. La vérification de T-TECH-22-01 a porté sur le comportement visible, pas sur la liste des scénarios.
Cause racine : la DoD demande des « critères d'acceptation automatisés et verts », mais rien ne relie chaque scénario à un test au moment du passage en done.

### Thème 2 : TDD invisible dans le journal (priorité haute, reprise de l'action 1 du sprint 21)
Faits : dans la PR #59, les tests sont commités avec le code, et un test de US-167 arrive après son code. Le merge par squash efface de toute façon l'ordre des commits dans `main`.
Cause racine : le contrôle prévu au sprint 21 n'a pas été exécuté, et il porte sur un ordre de commits que le squash fait disparaître.

### Thème 3 : ressources de la machine (priorité haute)
Faits : la machine a planté par manque de ressources ; le disque est descendu jusqu'à environ 2,6 Go ; la suite complète n'a jamais tourné en local, et la CI a fait foi. Plusieurs serveurs tournaient en même temps : la démo locale, MySQL, Mailpit et les conteneurs d'un autre projet.
Cause racine : `bin/check-disk` mesure le disque mais pas la mémoire, et rien ne limite le nombre de services lancés en même temps.

### Thème 4 : statuts des tâches (priorité moyenne, reprise de l'action 4 du sprint 21)
Faits : à la clôture, 9 tâches livrées étaient encore 🔲, contre environ 50 au sprint 21. Les tâches des stories sont presque toutes à jour ; le retard porte sur les tâches transverses.
Cause racine : les tâches transverses n'ont pas de « fin de story » qui déclenche leur mise à jour.

### Thème 5 : calendrier décalé du travail réel (priorité moyenne)
Faits : le sprint prévu du 2026-10-26 au 2026-11-06 a été lancé, développé et vérifié dès le 2026-10-23. Le planning, le point d'étape et la revue tombent après la fin du travail.
Cause racine : avec des développeurs Claude, le rythme dépend surtout de la disponibilité de Thibaut pour les GO et les relectures. La capacité en heures ne mesure pas cette contrainte.

## Suivi des actions de la rétrospective du sprint 21

| Action (S21) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Commit de tests rouges avant le code | Orchestrateur | ❌ Non tenue | Tests commités avec le code dans la PR #59, et un test postérieur au code (US-167) |
| 2. Consigne de worker : règles du dépôt cible et refus | Scrum Master | ⚠️ Partielle | Indicateurs atteints : 0 push sans confirmation, 0 contournement, 0 message tardif. Mais la règle 8 de `consigne-worker.md` dit encore « contourner proprement » |
| 3. Revues en sous-agent à périmètre borné | Orchestrateur | ⚠️ Non mesurable | Aucune revue interrompue rapportée, mais aucune trace de revue en sous-agent dans les PR du sprint |
| 4. Statuts de tâches au fil de l'eau | Orchestrateur | ⚠️ Partielle | 9 tâches livrées restées 🔲 (cible 0, environ 50 au sprint 21) |
| 5. Solder la livraison et décider du déploiement | Thibaut, orchestrateur | ✅ Atteinte | Railway repris, production vérifiée, stories des sprints 17 à 21 en done, plugins publiés |

Bilan : 1 action atteinte, 2 partielles, 1 non mesurable, 1 non tenue.

## Bilan des indicateurs

### Indicateurs du sprint 21

| Indicateur | Cible | Sprint 22 | Sprint 21 |
|------------|-------|-----------|-----------|
| Arrêts du sprint dus au disque | 0 | 0 arrêt, mais 1 plantage de la machine | 0 |
| Espace libre minimal | 5 Go | environ 2,6 Go | 4,2 Go |
| Tâches livrées restées 🔲 à la clôture | 0 | 9 | environ 50 |
| Contournements de refus d'outil | 0 | 0 (4 refus) | 1 |
| Push sans confirmation | 0 | 0 | 1 |
| Revues interrompues | 0 | 0 rapportée | 2 |
| Stories en done avec un scénario non livré | 0 | 1 (US-084) | non mesuré |

### Indicateurs d'US-161 (rétrospective du sprint 17)

Ce tableau complète les cases « à confirmer » de [bilan-us-161.md](./bilan-us-161.md).

| # | Indicateur | Constat au sprint 22 |
|---|------------|----------------------|
| 1 | 0 blocage disque de plus de 30 min | Le gel à 5 Go a refusé un run (4,6 Go libres), mais ce n'est pas un blocage : la CI a pris le relais. Hors indicateur : la machine a planté |
| 2 | 0 fichier écrasé, 0 base supprimée par un autre worker | 0 |
| 3 | 0 écart « vert annoncé / rouge à l'intégration » | 0. L'échec de la PR #64 vient d'un test instable, pas d'un vert annoncé à tort |
| 4 | 0 worker bloqué sur un run terminé | 0 |
| 5 | 0 prérequis découvert en cours de sprint | 1 : la base de test du plugin pointait vers une autre base |

## Actions (SMART)

### Action 1 : Contrôle des scénarios avant le done
| Attribut | Valeur |
|----------|--------|
| Description | Avant de passer une story en done, le fichier de tâches liste chaque scénario Gherkin avec son test (classe et méthode) ou sa preuve (commande et sortie). Un scénario sans test ni preuve laisse la story en review. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 23 (gabarit), puis contrôle de chaque story du sprint 23 |
| DoD | 0 story en done avec un scénario non livré (1 au sprint 22 : US-084). |
| Priorité | Haute |

### Action 2 : TDD vérifiable malgré le squash
| Attribut | Valeur |
|----------|--------|
| Description | Pour chaque story, la description de la PR cite le commit de tests rouges et le run qui échoue avant le code. Le contrôle se fait sur la branche, avant le squash. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Première PR du sprint 23 |
| DoD | 100 % des stories de code du sprint 23 avec un commit de tests rouges cité dans la PR (0 au sprint 22). |
| Priorité | Haute |

### Action 3 : Budget de ressources de la machine
| Attribut | Valeur |
|----------|--------|
| Description | Avant chaque lot, relever le disque et la mémoire libre. Ne faire tourner qu'un serveur lourd à la fois (démo locale ou MySQL de test), et l'arrêter dès la fin de la vérification. Thibaut libère de l'espace (conteneurs d'un autre projet) avant le sprint 23. |
| Responsable | Orchestrateur (relevés, arrêts), Thibaut (espace disque) |
| Deadline | Démarrage du sprint 23 |
| DoD | 0 plantage de la machine au sprint 23 (1 au sprint 22). Au moins 5 Go libres au démarrage (environ 2,6 Go au minimum du sprint 22). |
| Priorité | Haute |

### Action 4 : Corriger la règle des refus dans la consigne de worker
| Attribut | Valeur |
|----------|--------|
| Description | Dans `project-management/templates/consigne-worker.md`, remplacer la règle 8 par : « Ne jamais contourner un refus par un autre chemin ; le noter dans le rapport et continuer le reste. » |
| Responsable | Scrum Master (Claude) |
| Deadline | Avant le planning du sprint 23 |
| DoD | Règle corrigée, et 0 contournement au sprint 23. |
| Statut | ✅ Règle corrigée le 2026-10-23 (clôture du sprint 22) ; l'indicateur se mesure au sprint 23. |
| Priorité | Moyenne |

### Action 5 : Statuts des tâches transverses
| Attribut | Valeur |
|----------|--------|
| Description | Une tâche est cochée dans le commit qui livre son travail. Les tâches transverses sont revues à chaque merge de PR. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 23, contrôle à chaque merge |
| DoD | 0 tâche livrée restée 🔲 à la clôture du sprint 23 (9 au sprint 22). |
| Priorité | Moyenne |

### Action 6 : Caler le calendrier sur la disponibilité de Thibaut
| Attribut | Valeur |
|----------|--------|
| Description | Thibaut décide si le sprint 23 garde un rythme fixe de 2 semaines ou s'il se clôt dès que son périmètre est vérifié. Les cérémonies sont alors posées sur les créneaux de GO et de relecture de Thibaut. |
| Responsable | Thibaut (décision), Scrum Master (calendrier) |
| Deadline | Revue du sprint 22 |
| DoD | Décision consignée dans `.bmad/sprint-status.yaml`, et dates du sprint 23 alignées sur le travail réel. |
| Statut | ✅ Décidé le 2026-10-23 : sprint 22 clos dès son périmètre vérifié, passage au sprint 23. |
| Priorité | Moyenne |

## Points positifs à conserver

- 23 points en done et 23 stories des sprints précédents closes : plus aucune story n'est en review.
- Une vérification qui trouve des défauts réels, et des correctifs livrés dans la journée.
- La production est vérifiée sans jamais se connecter.
- Le LCP mobile du catalogue est en nette baisse (1 976 ms puis 1 448 ms), avec une couverture désormais protégée par un seuil.
- Aucun refus contourné, aucune publication sans GO.
