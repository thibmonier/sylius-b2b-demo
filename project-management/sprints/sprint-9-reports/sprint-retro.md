# Rétrospective - Sprint 9

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-10-30 (après-midi) ; préparée le 2026-10-17 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), workers Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal partiellement atteint. US-044 et US-006 en review, listes 59/164/213 → 25/25/25 requêtes, S2-R8..R10 clos. T-041-06 : objectif −50 % manqué (−13 %). T-041-15 en attente du 2026-10-23. US-029 reportée par arbitrage de capacité.

## Observations (Starfish)

### Continuer
- La revue indépendante a trouvé 3 vrais défauts sur US-006 (injection Caddy, leak-test aveugle, ADR trop affirmative) et a aussi permis d'écarter un faux positif PunchOut après vérification.
- Mesurer avant de conclure, puis publier les chiffres tels quels (T-041-06 : −13 %, pas −50 %).
- L'arbitrage de capacité en début de sprint (US-029 reportée) a évité un sprint surchargé.
- Les fixtures dérivées des CSV plutôt que saisies à la main.

### Commencer
- Vérifier le commit et le résultat de chaque worker à sa fin (worker-reports n'a pas commité).
- Documenter la préparation de la base de test locale (memory_limit, `WKHTMLTOPDF_BINARY`).
- Mesures de performance en au moins 3 passes avec écart.
- Vérifier sur Railway les points qui ne peuvent pas l'être en local (`/api/v2/shop/*`, mémoire du reset).

### Arrêter
- Considérer qu'un worker arrêté a terminé : plusieurs se sont arrêtés sans rapport avec leur tâche.
- Fixer un objectif chiffré (−50 %) sans mesure préalable des postes de coût.

### Plus de
- Preuves reproductibles (scripts de bench et de fuite d'état versionnés).
- Tests de non-régression pour les bugs trouvés en revue.

### Moins de
- Déploiements Railway manuels.
- CI non déclenchée à pousser plusieurs fois pour la relancer.

## Thèmes et analyse

### Thème 1 : fiabilité des workers (priorité haute)
Faits : workers arrêtés sans rapport, relances manuelles, un worker sans commit.

5 Pourquoi :
1. Pourquoi des vérifications manuelles ? → on ne savait pas si le worker avait fini.
2. Pourquoi ? → pas de critère de fin explicite dans la consigne.
3. Cause racine : la consigne n'impose ni commit/état final ni rapport de fin vérifiable.

Solution : un contrat de fin (statut + liste des fichiers + commit ou raison du non-commit).

### Thème 2 : environnement de test et CI (priorité moyenne)
Faits : base locale à préparer à la main, échec Factur-X local connu, CI parfois non déclenchée, déploiement Railway manuel.
Cause racine : le prérequis local n'est écrit nulle part, et la CI ne signale pas quand elle n'a pas démarré.

### Thème 3 : objectifs de performance (priorité moyenne)
Faits : −50 % visé, −13 % obtenu ; reste du reset : `demo_orders` ≈ 2000 requêtes, container_fill 776 SELECT+INSERT, pic ≈ 1,08 Go.
Cause racine : l'objectif a été fixé avant la répartition des requêtes par poste. Elle existe maintenant.

## Actions (SMART)

### Action 1 : Contrat de fin de worker
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à chaque consigne de worker un bloc de fin obligatoire : état (terminé/bloqué), fichiers modifiés, commit fait ou non et pourquoi. Le SM refuse de clore une tâche sans ce bloc. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 10 (2026-10-30 ou avant) |
| DoD | Modèle de bloc ajouté au `README.md` des tâches du sprint 10 ; 100 % des workers du sprint 10 rendent le bloc ; 0 relance faute de rapport. |
| Priorité | Haute |

### Action 2 : Documenter et scripter la base de test locale
| Attribut | Valeur |
|----------|--------|
| Description | Écrire la procédure (memory_limit, `WKHTMLTOPDF_BINARY`, création et migration de la base de test) et l'exposer en une commande. |
| Responsable | Worker devops, validé par le SM |
| Deadline | 2026-11-06 (milieu du sprint 10) |
| DoD | Une commande unique prépare la base de test sur un clone propre ; la procédure est dans `docs/` ; `OrderHistorySmokeTest` (assertion Factur-X) passe en local ou est explicitement exclu avec sa raison. |
| Priorité | Moyenne |

### Action 3 : Vérifier sur Railway les points non testables en local
| Attribut | Valeur |
|----------|--------|
| Description | Contrôler `/api/v2/shop/*` (404 en local), relever la mémoire du reset contre la limite du service, puis réaliser T-041-15. |
| Responsable | Thibaut (GO et accès Railway), assisté du SM |
| Deadline | `/api/v2/shop/*` et mémoire : 2026-10-23 ; T-041-15 : à partir du 2026-10-23, clos avant le 2026-10-30 |
| DoD | Résultat consigné dans `docs/` (statut HTTP, pic mémoire en Go, coût relevé) ; ticket créé si la limite est dépassée ou si l'API répond 404. |
| Priorité | Haute |

### Action 4 : Fixer les objectifs de performance après profilage
| Attribut | Valeur |
|----------|--------|
| Description | Avant tout objectif chiffré sur le reset, publier la répartition des requêtes par poste (`demo_orders`, container_fill, import) et viser un poste précis. |
| Responsable | Worker performance |
| Deadline | Avant l'engagement du sprint 10 (2026-10-30) |
| DoD | Tableau de répartition committé ; objectif du sprint 10 exprimé par poste (par exemple `demo_orders` ≈ 2000 requêtes → valeur cible chiffrée). |
| Priorité | Moyenne |

### Action 5 : Traiter les mineurs de revue et le N+1 Wishlist
| Attribut | Valeur |
|----------|--------|
| Description | Créer les tâches : capacité périmée (`VariantLogisticsSeeder`), NFC (`PackagingUnitParser`), rechargement des fixtures après interruption, N+1 Wishlist (28 requêtes à 50 produits). |
| Responsable | SM pour la création, workers pour la mise en œuvre |
| Deadline | Création : planning du sprint 10 ; mise en œuvre : sprint 10 si la capacité le permet |
| DoD | 4 tâches au backlog avec estimation ; chacune avec un test qui échoue avant correction. |
| Priorité | Basse |

## Suivi des actions précédentes

Les actions de la rétrospective du sprint 8 sont dans `../sprint-8-demo-et-durcissement/retrospective.md` : à relire en séance et à cocher (non évaluées ici faute de statut fiable).

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
