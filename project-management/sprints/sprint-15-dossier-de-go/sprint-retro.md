# Rétrospective - Sprint 15

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-01-22 (après-midi) ; préparée le 2026-10-18 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code : 12 stories de dev (36 pts, dont US-144 ajoutée en cours de sprint) en revue, sans sacrifice. 1 166 tests sans échec, pa11y AAA à 0 erreur, Lighthouse mobile 74-76 → 97-99, LCP 6,9-7,5 s → 2,1-2,2 s. Les difficultés viennent du disque saturé, de consignes contournées, de rapports tronqués et d'un manque d'infrastructure (volume des images) découvert à l'usage.

## Observations (Starfish)

### Continuer
- La revue indépendante : 0 bloquant, 1 majeur (audit JS qui passait sans auditer) et 7 mineurs corrigés.
- Les mesures objectives (Lighthouse, pa11y, `bin/measure-mobile`) : elles ont identifié la vraie cause du LCP (723 Ko de polices).
- Le périmètre complet décidé tôt par Thibaut, sans sacrifice.

### Commencer
- Un volume persistant ou une procédure de remise à zéro avant tout redéploiement de la démo.
- Lancer toute la suite avant la revue après chaque story qui touche un thème ou un canonical (2 échecs trouvés tardivement).
- Une porte d'entrée unique pour les tests quand le disque est gelé.

### Arrêter
- Lancer phpunit directement pour contourner `bin/run-tests` (deux développeurs).
- Laisser des variables d'environnement d'hôte (`RAILWAY_PUBLIC_DOMAIN`) piloter des listes de confiance.

### Plus de
- Tests qui lisent la configuration de bout en bout (hôtes de confiance, canonical).
- Décisions de Thibaut consignées au planning (D1 à D4).

### Moins de
- Relances pour rapports tronqués (3 ce sprint, 2 au précédent).
- Conflits entre développeurs sur un test partagé (PunchOut).

## Thèmes et analyse

### Thème 1 : rapports encore tronqués (priorité haute)
Fait : 3 relances, malgré l'action du sprint 14.
5 Pourquoi :
1. Pourquoi tronqués ? → les rapports sont longs et arrivent en fin de contexte.
2. Pourquoi longs ? → la limite de 100 mots n'est pas dans le contrat de fin de worker.
3. Pourquoi absente ? → l'action était une intention, sans vérification de son application.
Cause racine : action non intégrée au gabarit de brief ; pas de contrôle.

### Thème 2 : disque et contournement de `bin/run-tests` (priorité haute)
Faits : disque saturé à 2 Go, gel décrété, deux développeurs ont lancé phpunit en direct. `bin/clean-test-caches` existe désormais, mais la règle « demander à l'orchestrateur » n'est pas suffisamment simple à suivre.
Cause racine : pas de voie légitime et rapide quand le gel est décrété (qui nettoie, combien de temps).

### Thème 3 : état non persistant de la démo (priorité haute)
Faits : images produit perdues après redéploiements ; incident du domaine (`TRUSTED_HOSTS` dépendant de `RAILWAY_PUBLIC_DOMAIN`).
Cause racine : l'IaC suppose un domaine unique et aucun volume pour `public/media`.

### Thème 4 : coordination des tests partagés (priorité moyenne)
Fait : échec chez l'un, succès chez l'autre sur un test PunchOut, tranché par l'orchestrateur (exécution antérieure au correctif).
Cause racine : pas de règle « relancer après récupération du dernier commit/correctif avant de signaler ».

## Actions (SMART)

### Action 1 : Volume `public/media` et remise à zéro documentée
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter un volume Railway pour `public/media` (ou une remise à zéro des images au déploiement) et documenter la procédure de redéploiement. |
| Responsable | Worker devops + Thibaut (création du volume) |
| Deadline | Avant le GO d'US-052 |
| DoD | Deux redéploiements successifs sans perte d'images produit, vérifiés sur la démo ; procédure écrite dans `docs/`. |
| Priorité | Haute |

### Action 2 : Rapport de fin de moins de 100 mots dans le gabarit de brief
| Attribut | Valeur |
|----------|--------|
| Description | Inscrire en tête de chaque brief : rapport de moins de 100 mots (fichiers, tests, points ouverts), écrit avant toute dernière vérification. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 16 |
| DoD | 0 relance pour rapport tronqué au sprint 16 (3 au sprint 15). |
| Priorité | Haute |

### Action 3 : Règle de gel disque sans contournement
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à `tasks/README.md` : en cas de gel, seul `bin/run-tests` est autorisé, ou une demande à l'orchestrateur ; `bin/clean-test-caches` à lancer avant chaque lot de tests, `df -h .` au démarrage. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 16 |
| DoD | Règle écrite ; 0 lancement direct de phpunit au sprint 16 ; 0 saturation sous 2 Go. |
| Priorité | Haute |

### Action 4 : Relancer avant de signaler un échec partagé
| Attribut | Valeur |
|----------|--------|
| Description | Avant de signaler un échec sur un test touché par un autre développeur, mettre à jour le dépôt et relancer ce test. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 16 |
| DoD | Consigne dans le brief ; 0 conflit de résultat de test au sprint 16. |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 14

| Action (S14) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Procédure disque et script de nettoyage | Worker devops | ⚠️ Partiel | `bin/clean-test-caches` fait (US-141) ; mais le disque a de nouveau saturé et deux contournements de `bin/run-tests` ont eu lieu (reprise en action 3) |
| 2. Documenter `bin/measure-mobile` | Scrum Master | ✅ Fait | Variables Puppeteer documentées (T-141-04) ; `bin/measure-mobile` vert |
| 3. Rapports de fin ≤ 100 mots | Scrum Master | ❌ Non tenu | Rapports encore tronqués, 3 relances (reprise en action 2) |

Bilan : 1 action atteinte, 1 partielle, 1 non tenue.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
