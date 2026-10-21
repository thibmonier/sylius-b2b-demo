# Rétrospective - Sprint 18

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-03-05 (après-midi) ; préparée le 2026-10-21 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal partiellement atteint : 9 stories sur 10 (33 points sur 36) sont sur `main`, 7 en review et 2 en cours. US-147 (PHP 8.5) sort du sprint, faute de correctif publié à la clôture. Les difficultés viennent de l'environnement d'exécution (RAM, disque de la machine puis de la VM Docker, deux redémarrages), d'un périmètre incomplet (Symfony 6.4 dans US-148) et de contrôles non rejoués avant le push (ECS), pas du code livré. Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer
- Un seul worker à la fois depuis la crise de RAM : aucune base ni aucun fichier de travail écrasé n'a été relevé.
- La suite en ordre aléatoire sur trois graines : elle a trouvé deux tests dépendants de l'état laissé par d'autres tests, corrigés à la racine plutôt que relancés.
- Les décisions de Thibaut demandées au bon moment (abandon de Symfony 6.4, merge avant le correctif PHP), avec une option recommandée.
- `bin/check-disk` et le point de reprise en mémoire, qui ont permis de reprendre après deux redémarrages sans refaire le travail.

### Commencer
- Lire la matrice CI et les contraintes `composer.json` de chaque dépôt touché au cadrage d'une story de plugin.
- Rejouer ECS et PHPStan sur l'arbre exact avant chaque push, pas seulement la suite.
- Mesurer aussi l'espace du disque de la VM Docker, pas seulement celui de la machine.
- Fournir à Thibaut les commandes à lancer par `!` sous forme de script du scratchpad, jamais en ligne longue à copier.

### Arrêter
- Écrire des jetons au format réel (Bearer, JWT) en clair dans les données de test.
- Laisser Composer lancer `cache:clear` avec la mémoire par défaut de PHP (128 Mo).

### Plus de
- Contrôles finaux (rendu réel, pa11y, `bin/measure-mobile`) planifiés tôt, avant que la session ne soit consommée par l'environnement.

### Moins de
- Temps perdu par l'environnement : deux redémarrages, un run de 6 min invalide, OrbStack à relancer.

## Thèmes et analyse

### Thème 1 : ressources de la machine et de la VM Docker (priorité haute)
Faits : saturation de la RAM puis swap de macOS (jusqu'à 6 Go) sur le même conteneur APFS, espace libre tombé à 1,4 Go, deux redémarrages ; plus tard, disque de la VM OrbStack plein pendant la graine 3131 (79 « No space left on device », 838 « Connection refused »), OrbStack arrêté et relancé par Thibaut.
5 Pourquoi :
1. Pourquoi le run est-il invalide ? → MySQL a perdu son disque, puis Docker s'est arrêté.
2. Pourquoi le disque de la VM était-il plein ? → la VM a sa propre taille, distincte de l'espace libre du Mac (14 Go à ce moment-là).
3. Pourquoi ne l'a-t-on pas vu venir ? → `bin/check-disk` ne mesure que le disque de la machine et les volumes du projet.
4. Pourquoi la RAM a-t-elle saturé plus tôt ? → plusieurs workers, Chromium et pa11y lancés en même temps.
Cause racine : la surveillance couvre la machine, pas la VM Docker ; la charge parallèle n'était pas bornée par la RAM.

### Thème 2 : périmètre d'une story de plugin incomplet (priorité haute)
Faits : US-148 prévoyait la compatibilité Symfony 7.4 et 8, mais quatre plugins supportaient encore Symfony 6.4 ; leurs CI ont échoué (« service limiter.… inexistant »), d'où une décision de Thibaut et des versions mineures au lieu de patchs.
Cause racine : la fiche a été rédigée à partir de la démo (Symfony 8.1) sans relire la matrice CI ni les contraintes de chaque dépôt touché.

### Thème 3 : contrôles non rejoués avant le push (priorité moyenne)
Faits : la suite était verte en local, mais la CI de la PR #43 a échoué sur ECS (guillemets simples) ; GitGuardian a signalé deux faux secrets écrits en clair dans les tests du détecteur d'US-103.
Cause racine : l'action 3 du sprint 17 (vérification croisée avant « vert ») n'est pas encore dans la checklist d'intégration (T-161-06 ouverte) ; aucune règle sur la forme des secrets fictifs dans les tests.

### Thème 4 : actions sous contrôle de Thibaut (priorité moyenne)
Faits : le classifieur refuse à Claude `gh pr merge` en lot, ainsi que les commandes Railway ; une commande collée, coupée sur deux lignes, a échoué ; un script du scratchpad a fonctionné du premier coup.
Cause racine : les commandes longues à copier sont fragiles ; le refus du classifieur est attendu et n'est pas un défaut à contourner.

## Actions (SMART)

### Action 1 : Surveiller le disque de la VM Docker
| Attribut | Valeur |
|----------|--------|
| Description | `bin/check-disk` mesure aussi l'espace libre de la VM Docker (`docker system df` et `df` dans un conteneur) et alerte à 10 Go ; `bin/run-tests` refuse de démarrer sous 3 Go dans la VM. |
| Responsable | Worker devops |
| Deadline | Planning du sprint 19 |
| DoD | Test du script (VM sous le seuil, Docker absent) ; 0 run de tests invalidé par le disque au sprint 19 (1 au sprint 18). |
| Priorité | Haute |

### Action 2 : Lire la matrice CI des dépôts touchés au cadrage
| Attribut | Valeur |
|----------|--------|
| Description | Le gabarit de story impose, pour toute story qui touche un plugin, la liste des versions supportées (PHP, Symfony, Sylius) lue dans `composer.json` et la matrice CI de chaque dépôt, et l'impact sur la version (patch, mineure, majeure). |
| Responsable | Scrum Master (Claude) |
| Deadline | Affinage du sprint 19 |
| DoD | Rubrique ajoutée au gabarit ; appliquée à US-146 ; 0 cellule de matrice CI découverte en échec par la PR au sprint 19 (4 au sprint 18). |
| Priorité | Haute |

### Action 3 : ECS et PHPStan rejoués avant chaque push
| Attribut | Valeur |
|----------|--------|
| Description | Termine T-161-06 : avant tout push, l'orchestrateur rejoue ECS et PHPStan sur l'arbre exact et cite la dernière ligne de sortie dans le rapport. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Début du sprint 19 |
| DoD | T-161-06 fermée ; 0 run de CI rouge sur ECS ou PHPStan au sprint 19 (1 au sprint 18). |
| Priorité | Moyenne |

### Action 4 : Secrets fictifs non détectables dans les tests
| Attribut | Valeur |
|----------|--------|
| Description | Règle de test : un jeton fictif au format réel (Bearer, JWT, clé d'API) est recomposé à l'exécution, jamais écrit d'un bloc ; rappel dans `docs/tests-locaux.md`. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 19 |
| DoD | Règle documentée ; 0 alerte GitGuardian sur des données de test au sprint 19 (2 au sprint 18). |
| Priorité | Basse |

### Action 5 : Commandes pour Thibaut sous forme de script
| Attribut | Valeur |
|----------|--------|
| Description | Toute commande de plus d'une ligne que Thibaut doit lancer avec `!` (merges en lot, tags) est un script du scratchpad avec un OK ou FAIL par élément ; Composer est lancé avec `php -d memory_limit=1G` dans la démo. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Immédiat |
| DoD | 0 commande collée en échec au sprint 19 (1 au sprint 18) ; 0 `cache:clear` en échec mémoire. |
| Priorité | Basse |

## Suivi des actions de la rétrospective du sprint 17

| Action (S17) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Contrôle et diagnostic de l'espace disque | Worker devops | ⚠️ Partiel | `bin/check-disk` livré avec test (US-161) ; DoD non tenue : blocages par le disque de la machine (deux redémarrages) puis de la VM Docker (non couverte, action 1) |
| 2. Isoler les workers parallèles | Worker devops + Scrum Master | ✅ Fait | Clean limité au worker, gabarit à jour ; aucun fichier ni base écrasé relevé (un seul worker à la fois depuis la crise de RAM) |
| 3. Vérification croisée à l'intégration | Scrum Master | ⚠️ Partiel | Rejeu fait à la main (PHPStan et ECS OK après correction) ; T-161-06 ouverte ; 1 écart « vert en local, rouge en CI » sur ECS (action 3) |
| 4. Attente bornée par le verrou | Orchestrateur + Worker devops | ✅ Fait | `--wait` livré avec test ; aucun worker bloqué relevé ; T-161-09 (consigne TaskStop dans le gabarit) ouverte |
| 5. Prérequis d'environnement et registre hors dépôt | Scrum Master ; Thibaut | ⚠️ Partiel | Rubrique du gabarit et `communication/INDEX.md` livrés ; format à valider par Thibaut (T-161-12) ; 1 prérequis découvert en cours de sprint (correctif PHP non publié pour US-147) |

Bilan : 2 actions atteintes, 3 partielles.

### Bilan des cinq indicateurs (T-TECH-02)

Données partielles : aucun compteur n'a été tenu pendant le sprint ; les valeurs viennent des rapports et de l'historique de la session de clôture.

| Indicateur | Cible | Sprint 18 | Source |
|------------|-------|-----------|--------|
| Blocages de tests de plus de 30 min pour cause de disque | 0 | au moins 1 (deux redémarrages ; durée non mesurée) | point de reprise de la session |
| Fichiers de travail écrasés ou bases supprimées par un autre worker | 0 | 0 relevé | aucun incident rapporté |
| Écarts « vert annoncé / rouge à l'intégration » | 0 | 1 (ECS, PR #43) | CI de la PR #43 |
| Workers bloqués sur un run terminé | 0 | 0 relevé | aucun incident rapporté |
| Prérequis découverts en cours de sprint | 0 | 1 (correctif PHP 8.5 non publié) ; plus 4 cellules Symfony 6.4 (périmètre, action 2) | US-147, CI des PR US-148 |

## Points positifs à conserver

- 30 PR de plugins fusionnées et 5 versions publiées dans le même sprint, chacune après une CI verte.
- Deux dépendances d'ordre dans les tests corrigées à la racine, vérifiées sur trois graines.
- Reprise propre après deux redémarrages grâce au point de reprise en mémoire.

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
