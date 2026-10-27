# Rétrospective - Sprint 31

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-27, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 30, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers w208, w203, w204) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint dans le code : **13 points livrés sur 13** plus T-TECH-31-11, sans sacrifice. Vélocité : S26 29, S27 13, S28 13, S29 13, S30 13, **S31 13**. Les contrôles qui exigent `main` déployé restent ouverts.

Les écarts du sprint :
- le disque entre 4 et 5,6 Go et le swap à 4 Go toute la journée : aucune suite complète locale (T-TECH-31-02) ;
- le verrou partagé de `bin/run-tests` a refusé des runs, des workers ont attendu ;
- `public/build` périmé depuis le 25 octobre, SCSS compilé à part par les workers ;
- capture du panier impossible : images produit non importées en local ;
- deux messages croisés avec w204 ;
- le correctif de la page Crédits (PR #124) noté au registre après l'ouverture de la PR.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- Les maquettes validées avant tout Twig, dans le canevas unique : GO de Thibaut le jour même.
- Les tests rouges commités seuls : 3 stories sur 3, plus trois tâches techniques.
- L'arrêt devant un conflit (retrait de l'ERP) avec options proposées, puis arbitrage tracé au registre.
- Le test de dérive des gabarits d'origine : les surcharges des plugins sont protégées d'une mise à jour silencieuse.
- La CI de l'ensemble verte au premier passage sur 7 contrôles, y compris le nouveau job zizmor.
- La correction de la CI de Timeline avant l'intégration, avec GO et sans tag.

### Commencer

- Vérifier la fraîcheur de `public/build` avant de capturer ou de compiler à part.
- Un jeu d'images produit local pour que `bin/render-page` rende le panier.
- Une file d'attente sur le verrou de `bin/run-tests` plutôt qu'un refus.

### Arrêter

- Lancer les workers sans que `bin/check-disk` annonce le nombre de runs de tests autorisés.
- Ouvrir une PR hors sprint avant d'avoir écrit sa décision au registre.

### Plus de

- Contrôles de production menés en parallèle et hors budget (PR #123) : ils ont fait remonter la page Crédits et le LCP le jour même.

### Moins de

- Attente de workers sur une réponse déjà envoyée.

## Thèmes et analyse

### Thème 1 : machine toujours à la limite (priorité haute)

Disque entre 4 et 5,6 Go, swap de 3,7 à 4,2 Go sur 5 : la règle 24 (au plus deux workers qui lancent des tests au-delà de 2 Go de swap ou sous 7 Go de disque) n'a été appliquée que par le jugement de l'orchestrateur, car `bin/check-disk` n'affiche pas le nombre autorisé (T-TECH-31-01 faite avec écart). Le verrou partagé de `bin/run-tests` a refusé des runs (« already running ») et des workers ont attendu. Aucune suite complète locale pour la deuxième fois : la CI a fait foi, verte ici, mais l'action 2 de la rétro du sprint 30 n'est pas tenue.

### Thème 2 : `public/build` périmé (priorité moyenne)

Les assets compilés dataient du 25 octobre. Les workers ont compilé le SCSS à part pour vérifier leurs pages ; l'orchestrateur a reconstruit avec `yarn build:prod` avant les captures. Rien n'avertit qu'un build est plus ancien que ses sources.

### Thème 3 : captures sans images produit (priorité moyenne)

Les images produit ne sont pas importées en local : la capture du panier par `bin/render-page` n'a pas pu être faite, donc la DoD de T-TECH-31-04 n'est pas remplie, même si les vignettes des URL relatives sont corrigées et testées.

### Thème 4 : coordination et registre (priorité moyenne)

Deux fois, w204 a attendu une réponse déjà envoyée : la règle 25 (citer l'identifiant de la décision) a aidé en partie, mais le worker n'a pas relu le registre avant de signaler son attente. La page Crédits (1 046 px sur 360 px, cause dans US-186) a été corrigée par la PR #124 avant que la décision ne soit écrite : même écart que l'action 3 de la rétro du sprint 28.

### Thème 5 : mesure en production après un changement de précharge (priorité moyenne)

Le préchargement d'Inter a été retiré sur toutes les pages : le gain local (1 696 → 1 332 ms) n'est pas encore confirmé en production et la fiche et la liste produit n'ont pas été remesurées.

## Suivi des actions de la rétrospective du sprint 30

| # | Action | Résultat au sprint 31 |
|---|--------|------------------------|
| 1 | Parallélisme réglé sur la machine | 🟡 Règle 24 écrite, relevés faits à la main ; `bin/check-disk` n'affiche pas le nombre de workers autorisés ; 0 arrêt de worker rapporté, mais seuils non tenus |
| 2 | Suite complète locale avant le push, ou attente explicite | 🟡 Dite dans la PR (T-TECH-31-02), CI verte ; suite complète non jouée en local |
| 3 | Cohérence HT et TTC des montants | ✅ `135e006`, échec prouvé sur une commande d'historique taxée |
| 4 | Outils de rendu et de console | 🟡 Vignettes des URL relatives et `memory_limit` faits ; capture du panier impossible (thème 3) |
| 5 | Échanges avec les workers | 🟡 Règles 24 et 25 écrites ; 2 messages croisés ; 0 `phpunit` en direct relevé |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 31 | Sprint 30 |
|------------|-------|-----------|-----------|
| Arrêt de worker pour cause de disque | 0 | 0 rapporté (seuils non tenus) | environ 40 minutes |
| Espace libre minimal | 5 Go (7 Go pour 3 runs, règle 24) | **4 Go environ** | 4,9 Go |
| Suite complète locale avant le push | oui | **non** (5,0 Go libres) | non |
| CI rouges après contrôles locaux verts | 0 | 0 | 1 |
| Régénération manuelle de la preuve | 0 | 0 | 0 |
| Capture simulée | 0 | 0 (panier non capturé) | 0 |
| Commit embarquant le travail d'un autre worker | 0 | 0 relevé | 0 |
| Relances pour une décision déjà envoyée | 0 | **2** | non mesuré |
| `phpunit` lancé en direct | 0 | 0 relevé | 1 |
| PR fusionnée avec une question ouverte | 0 | 0 | 0 |
| Décision écrite après l'action | 0 | **1** (PR #124) | 2 |
| Push, tag ou release sans GO | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 : Machine et verrou des tests
| Attribut | Valeur |
|----------|--------|
| Description | `bin/check-disk` affiche le nombre de runs de tests autorisés selon `df` et le swap (règle 24) ; `bin/run-tests` met en file d'attente derrière le verrou partagé au lieu de refuser (« already running »), avec un test dans `tests/Unit/Bin` ; Thibaut libère le disque (cible 10 Go libres) avant le lancement des workers du sprint 32. |
| Responsable | Orchestrateur (Claude) ; Thibaut pour le disque |
| Deadline | Avant le lancement des workers du sprint 32 |
| DoD | 0 run refusé pour verrou et 0 worker en attente de plus de 5 minutes sur le verrou ; suite complète locale jouée avant la PR du sprint 32 ou attente explicite demandée à Thibaut avant le push. |
| Priorité | Haute |

### Action 2 : Build des assets frais
| Attribut | Valeur |
|----------|--------|
| Description | `bin/render-page` et `bin/run-tests` avertissent quand `public/build` est plus ancien que `assets/` ; l'orchestrateur lance `yarn build:prod` au démarrage du sprint si l'avertissement apparaît. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Première capture du sprint 32 |
| DoD | 0 compilation du SCSS à part par un worker au sprint 32 ; l'avertissement est couvert par un test. |
| Priorité | Moyenne |

### Action 3 : Images produit locales pour les captures
| Attribut | Valeur |
|----------|--------|
| Description | Documenter et outiller l'import local des images produit (commande dans `docs/tests-locaux.md`, vérifiée par `bin/render-page`) pour rendre le panier avec ses vignettes ; noter dans `docs/captures-vitrine.md` le `form.requestSubmit()` de la connexion en un clic. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant la première capture du sprint 32 |
| DoD | Une capture du panier par `bin/render-page` montre ses vignettes (clôt la DoD de T-TECH-31-04), sans copie de travail du script. |
| Priorité | Moyenne |

### Action 4 : Registre avant l'action, réponses relues avant d'attendre
| Attribut | Valeur |
|----------|--------|
| Description | Règle 26 de la consigne : tout correctif hors sprint (PR séparée) a sa ligne au registre avant l'ouverture de la PR ; un worker qui s'apprête à écrire « j'attends » relit d'abord le registre et ses messages reçus. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 32 |
| DoD | 0 PR hors sprint ouverte avant sa ligne au registre ; 0 relance pour une décision déjà envoyée. |
| Priorité | Moyenne |

### Action 5 : Remesure de production après le déploiement
| Attribut | Valeur |
|----------|--------|
| Description | Le jour du déploiement de la PR #125 : LCP mobile de la home, de la fiche et de la liste produit au protocole de w186 (5 passes, FR et EN, seuil de 1 456 ms), pa11y de la page Plugins en production, puis T-203-14 et 15 et T-204-08 à 11 ; résultats consignés dans les fichiers de tâches. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Le jour du déploiement de `main`, avant le planning du sprint 32 |
| DoD | Mesures consignées ; si la home dépasse encore 1 456 ms, une tâche technique est ouverte au sprint 32 sur décision de Thibaut. |
| Priorité | Haute |

## Intégration au sprint 32

Les actions 1 à 5 sont à reprendre au planning du sprint 32 (T-TECH-32-01 à 05). Le reste du sprint 31 s'y ajoute : T-208-11 (Thibaut), T-208-10, T-203-14 et 15, T-204-08 à 11, remesure du LCP. US-207 vient après US-204 à US-206 ; US-209 (dépôts de plugins) dépend d'US-208, désormais en revue.

## Points positifs à conserver

- 13 points sur 13 et la tâche de LCP, sans sacrifice, dont une story de sécurité demandée le jour même (US-208) et deux refontes alignées sur des maquettes validées.
- La CI verte au premier passage sur 7 contrôles, avec un nouveau job zizmor passé de 9 alertes à 0.
- Des contrôles de production menés en parallèle, hors budget, qui ont attrapé deux écarts (page Crédits, LCP) le jour même.
- 0 capture simulée, 0 régénération manuelle de la preuve, 0 publication sans GO, 0 tag pour le plugin Timeline.
