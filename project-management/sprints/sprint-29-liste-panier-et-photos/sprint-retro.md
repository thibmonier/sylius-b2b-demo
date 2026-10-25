# Rétrospective - Sprint 29

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-25, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 28, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers w198, w200, w197) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **13 points livrés sur 13**, sans sacrifice. Vélocité : S25 17, S26 29, S27 13, S28 13, **S29 13** (moyenne des quatre précédents : 18). Le contrôle des photos en production reste ouvert jusqu'à la remise à zéro de 03:00 du 2026-10-26, avec les contrôles reportés du sprint 28.

Les écarts du sprint :
- un défaut visible seulement après le déploiement (quatrième produit de la home à la ligne), signalé par Thibaut et corrigé le jour même (#113) ;
- un fichier de configuration partagé commité par un worker avec les lignes d'un autre ;
- des captures faites sans les images, puis refaites ; des captures avant/après simulées faute d'outil ;
- deux tâches transverses non faites pour la troisième fois (`bin/proof-rebase`, cache de rendu unique) ;
- une entrée du registre écrite après l'action.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- La maquette validée avant tout Twig : aucune reprise de mise en page après le GO, et les écarts au code ont été tranchés par Thibaut en une réponse.
- Le canevas unique avec la planche « Acquis » : les règles et décisions des refontes précédentes étaient sous les yeux pour la liste et le panier.
- La planche de photos regardée aperçu par aperçu, avec recommandation : 9 candidates écartées pour texte ou logo avant toute demande à Thibaut.
- Le registre des décisions avec leur auteur (action 3 du sprint 28) : aucune décision mal attribuée.
- Les commandes refusées non contournées : la suppression revient à Thibaut, en chemins absolus.
- Le test de commande de bout en bout du panier.

### Commencer

- Déclarer à l'avance, au planning, quels fichiers sont partagés entre workers et qui les commite (thème 2).
- Rendre les vignettes `b2b_photo` dans `bin/render-page`, pour des captures réelles (thème 3).
- Vérifier une page connectée en largeur réelle de bureau avant la PR, pas seulement en test fonctionnel (thème 1).

### Arrêter

- Reporter les tâches transverses derrière les stories quand le sprint s'y prête (thème 4).
- Lancer des tests depuis un worktree dont le `vendor` est un lien symbolique (thème 5).

### Plus de

- Contrôles visuels des captures avant de les verser dans `docs/images`.

### Moins de

- Tests qui lisent l'état réel de `var/` au lieu d'un dossier propre au test.

## Thèmes et analyse

### Thème 1 : un défaut vu seulement après le déploiement (priorité haute)

Sur la home connectée, les blocs « Vos références habituelles », « Réassort rapide » et « Promotions négociées » montraient quatre produits ; le quatrième passait à la ligne sur bureau. Thibaut l'a vu après le déploiement de #111. Les tests vérifiaient la présence des blocs, pas leur nombre de cartes ni leur rendu à 1280 px, et la home n'était pas dans le périmètre visuel du sprint. Corrigé en TDD (test rouge `cc7e780`, limite à 3), fusionné le jour même.

### Thème 2 : fichier partagé et commits croisés (priorité moyenne)

`config/packages/demo_theme_b2b.yaml` sert à la liste et au panier. w198 l'a commité avec une ligne de w200 dont le gabarit n'était pas encore commité ; deux tests sont passés rouges chez l'autre worker. Le README du sprint listait les domaines de fichiers, mais pas ce fichier commun ni la règle de qui le commite.

### Thème 3 : outillage des captures (priorité moyenne)

`bin/render-page` ne produit pas les vignettes `b2b_photo` : les captures de la liste ont d'abord montré des vignettes vides, et les captures avant/après d'US-197 ont été simulées avec ImageMagick sur les vrais fichiers, ce qui reste une preuve indirecte. Les captures de la liste ont été refaites sur une base aux images chargées, à la main.

### Thème 4 : tâches transverses reportées (priorité haute)

T-TECH-29-01 (un seul cache de rendu imposé par l'outil) et T-TECH-29-02 (`bin/proof-rebase`) sont reportées pour la **troisième fois** (actions 1 et 2 de la rétro du sprint 27). La preuve publiée a encore été régénérée à la main. w198 a eu deux caches de rendu. Le sprint de 13 points laissait la place de les faire ; elles ont de nouveau cédé aux stories.

### Thème 5 : pièges d'environnement de test (priorité moyenne)

Deux faux signaux : `ErpDemoExchangeFixtureTest` échoue dès qu'un chargement de fixtures local a écrit dans le vrai `var/erp-exchange` ; et, pendant #113, un worktree dont `vendor` est un lien symbolique a exécuté le code de l'arbre principal (l'autoload de Composer résout le lien), donnant un test rouge alors que la correction était faite. Le second a été vu parce que le résultat ne bougeait pas après la correction.

### Thème 6 : registre écrit après l'action (priorité basse)

La demande de Thibaut sur la home a été codée, puis notée au registre dans la même PR. Rien de faux n'a été écrit, mais la règle « avant l'action » n'a pas été suivie.

## Suivi des actions de la rétrospective du sprint 28

| # | Action | Résultat au sprint 29 |
|---|--------|------------------------|
| 1 | Garde sur les commandes récursives et destructives | ✅ Règle 18 au planning ; 0 commande récursive non gardée ; suppressions refusées non contournées |
| 2 | Rejouer la séquence exacte de la CI | ✅ Règle 19 ; ECS, PHPStan, Rector et `proof-check` rejoués avant chaque push ; 0 CI rouge (#111, #112, #113) |
| 3 | Registre des décisions avec leur auteur | ✅ Tenu tout le sprint ; 0 décision mal attribuée ; une entrée tardive (thème 6) |
| 4 | Pas de fusion automatique avec une question ouverte | ✅ Commentaires vérifiés avant chaque fusion ; 0 PR concernée |
| 5 | Reprendre les actions 1, 2 et 5 du sprint 27 | 🔲 Non faites (T-TECH-29-01, 02) ; LCP de la home (T-TECH-29-03) après la remise à zéro du 2026-10-26 |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 29 | Sprint 28 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 | 1 incident |
| Espace libre minimal | 5 Go | **sous 5 Go une fois** (captures « avant » du panier différées), 8 à 9 Go ensuite | disque plein |
| Suite complète lancée sous le seuil | 0 | 0 (une seule suite, à 8,2 Go) | 0 |
| CI rouges après contrôles locaux verts | 0 | 0 | 1 |
| Commit au mauvais numéro de story | 0 | 0 relevé | 0 |
| Commit embarquant le travail d'un autre worker | 0 | **1** (`demo_theme_b2b.yaml`) | n/a |
| Défauts trouvés après le déploiement | 0 | **1** (home, corrigé le jour même) | 0 relevé |
| Commande récursive sur un chemin non vérifié | 0 | 0 | 1 |
| Push, tag ou téléchargement sans GO | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 : Faire en tête du sprint 30 `bin/proof-rebase` et le cache de rendu unique
| Attribut | Valeur |
|----------|--------|
| Description | T-TECH-29-01 et T-TECH-29-02 deviennent les deux premières tâches du sprint 30, avant toute story : `bin/render-page` refuse un second cache par worker et vérifie le seuil disque ; `bin/proof-rebase` rebase, régénère `proof.json` et commite. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Premier jour du sprint 30 |
| DoD | Les deux scripts existent, testés, et servent à chaque PR du sprint 30 ; 0 régénération manuelle de la preuve. |
| Priorité | Haute |

### Action 2 : Vignettes `b2b_photo` dans `bin/render-page`
| Attribut | Valeur |
|----------|--------|
| Description | Le rendu du noyau génère les vignettes des filtres `b2b_photo*` à partir des images chargées, pour des captures réelles (cartes, fiche). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la première capture du sprint 30 |
| DoD | Une capture de liste produite par `bin/render-page` montre 9 vignettes non vides ; 0 capture simulée au sprint 30. |
| Priorité | Moyenne |

### Action 3 : Fichiers partagés déclarés au planning
| Attribut | Valeur |
|----------|--------|
| Description | Le README du sprint liste les fichiers communs à plusieurs workers (configuration du thème, `docs/theme-b2b.md`, traductions) et désigne qui les commite ; un worker ne commite pas un fichier commun avec les lignes d'un autre. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 30 |
| DoD | 0 commit embarquant le travail d'un autre worker au sprint 30. |
| Priorité | Moyenne |

### Action 4 : Rendu bureau des pages touchées avant la PR
| Attribut | Valeur |
|----------|--------|
| Description | Avant la PR d'un sprint de refonte, une capture à 1280 px de la home connectée et de chaque page touchée est relue par l'orchestrateur ; un test compte les cartes des blocs à nombre fixe. |
| Responsable | Orchestrateur (Claude) |
| Deadline | PR du sprint 30 |
| DoD | 0 défaut de mise en page signalé après le déploiement au sprint 30. |
| Priorité | Haute |

### Action 5 : Environnement de test fiable
| Attribut | Valeur |
|----------|--------|
| Description | `ErpDemoExchangeFixtureTest` écrit et lit dans un dossier propre au test ; les tests ne se lancent jamais depuis un worktree au `vendor` en lien symbolique (règle ajoutée à la consigne). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 30 |
| DoD | La suite complète passe après un chargement de fixtures local ; règle présente dans `consigne-worker.md`. |
| Priorité | Moyenne |

## Intégration au sprint 30

Les actions 1 à 5 sont à reprendre au planning du sprint 30, avec les candidates [US-202](../../backlog/user-stories/US-202.md) (catégories de taxe), US-199 (« Mon compte ») et US-201 (tableau de bord des achats), dont les maquettes iront dans le canevas unique.

## Points positifs à conserver

- 13 points sur 13, sans sacrifice, avec deux pages refondues fidèles à des maquettes validées.
- Un canevas unique qui garde les acquis des refontes pour la suite.
- Des photos de la bonne couleur, choisies sur planche, sans retouche et créditées.
- 0 CI rouge et 0 PR fusionnée avec une question ouverte.
- Un défaut signalé après déploiement corrigé en TDD le jour même.
