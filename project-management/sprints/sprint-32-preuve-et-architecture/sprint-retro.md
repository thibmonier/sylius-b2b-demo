# Rétrospective - Sprint 32

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-28, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 31, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers w205, w206 et le worker d'US-062) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint dans le code : **9 points livrés sur 9**, sans sacrifice. Vélocité : S27 13, S28 13, S29 13, S30 13, S31 13, **S32 9** (périmètre volontairement réduit à cause du disque). Les contrôles qui exigent `main` déployé restent ouverts.

Les écarts du sprint :
- le disque entre 4 et 6 Go et le swap de 2,5 à 3,7 Go toute la journée : tests et rendus retardés, suite complète laissée à la CI ; le verrou de tests attendu deux fois ;
- les tests rouges d'US-205 (fournisseurs de données faux) et d'US-206 (corrigés deux fois) ;
- le conflit maquette/test sur le SVG, vu à l'implémentation ;
- des outils de rendu à contourner (barre de débogage, `bin/measure-mobile`).

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- Les maquettes validées avant tout Twig, dans le canevas unique : GO de Thibaut « tel quel » sur les deux pages.
- Les modifications de tests existants décidées au registre avant l'action et citées dans la PR (2 cas).
- Le périmètre réduit à 9 points face à un disque contraint : aucun sacrifice, CI verte sur 6 contrôles.
- L'ordre de sacrifice confirmé par Thibaut dès le planning.

### Commencer

- Passer chaque maquette au crible des tests « non modifiés » au planning, avant le GO.
- Vérifier qu'un test rouge échoue pour la bonne raison avant de le commiter seul.
- Un rendu de page sans barre de débogage pour les contrôles d'accessibilité.

### Arrêter

- Corriger les données d'un test rouge dans le commit du code : le rouge n'est alors plus prouvé.
- Planifier des tâches de test et de rendu sans que le disque les permette.

### Plus de

- Relevés de disque et de swap avant chaque lot, déjà tenus ; à compléter par un nombre de runs annoncé par l'outil.

### Moins de

- Contournements à la main des outils de rendu et de mesure.

## Thèmes et analyse

### Thème 1 : machine toujours à la limite (priorité haute)

Disque entre 4 et 6 Go, swap de 2,5 à 3,7 Go : les tests et les rendus ont été retardés, la suite complète locale n'a pas été jouée (troisième sprint de suite) et le verrou `var/run-tests.lock` a fait attendre des workers à deux reprises. Le périmètre de 9 points a absorbé la contrainte, mais l'action 1 de la rétro du sprint 31 (libérer le disque, file d'attente sur le verrou) reste à confirmer dans les fichiers de tâches.

### Thème 2 : tests rouges non prouvés (priorité haute)

US-205 : les fournisseurs de données des tests rouges étaient faux, corrigés dans le commit du code, donc ces tests n'ont pas été vus rouges au sens strict. US-206 : tests rouges corrigés deux fois (`870e6c0`, `3922a13`). La règle « rouge commité seul » est respectée dans la forme, moins dans le fond : la preuve que le test échoue pour la bonne raison manque.

### Thème 3 : conflit maquette et test tardif (priorité moyenne)

Le SVG imposé par un test « non modifié » est apparu à w206 à l'implémentation. La décision (option B, schéma en HTML/CSS, adaptation d'`ArchitecturePageTest` sur 3 points) a été prise au registre avant l'action, mais le conflit aurait pu être détecté au planning, en croisant la maquette avec les invariants listés au cadrage.

### Thème 4 : outils de rendu et de mesure (priorité moyenne)

`bin/render-page` inclut la barre de débogage Symfony, retirée à la main des copies pour pa11y. `bin/measure-mobile` vise un serveur : un script puppeteer équivalent l'a remplacé. L'installation pa11y principale de la scratchpad, abîmée, est de l'outillage local sans effet sur le dépôt.

### Thème 5 : production après la fusion de la PR #126 (priorité basse)

Le redéploiement Railway a produit une erreur « Application failed to respond » transitoire pendant une capture ; sans suite, mais à garder en tête pour les contrôles du jour du déploiement.

## Suivi des actions de la rétrospective du sprint 31

| # | Action | Résultat au sprint 32 |
|---|--------|------------------------|
| 1 | Machine et verrou des tests | 🟡 Disque encore entre 4 et 6 Go, verrou attendu deux fois, suite complète locale non jouée ; T-TECH-32-01 **non faite**, reportée au sprint 33 |
| 2 | Build des assets frais | ❌ Non faite (T-TECH-32-03) : `public/build` reconstruit à la main par les workers ; reportée au sprint 33 |
| 3 | Images produit locales pour les captures | ❌ Non faite (T-TECH-32-04, dépend du disque) ; reportée au sprint 33 |
| 4 | Registre avant l'action, réponses relues avant d'attendre | ✅ Règle 26 écrite au planning ; 2 modifications de tests décidées avant l'action |
| 5 | Remesure de production après le déploiement | ✅ Faite le 2026-10-28 avant le planning (T-TECH-32-06) |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 32 | Sprint 31 |
|------------|-------|-----------|-----------|
| Espace libre minimal | 5 Go (7 Go pour 3 runs) | **4 Go environ** | 4 Go environ |
| Suite complète locale avant le push | oui | **non** (CI verte) | non |
| Verrou de tests attendu entre workers | 0 | **2** | refusé, nombre non mesuré |
| CI rouges après contrôles locaux verts | 0 | 0 | 0 |
| Tests rouges non vus rouges | 0 | **2 stories** (US-205, US-206) | non mesuré |
| Test existant modifié sans décision préalable | 0 | 0 (2 modifiés, décidés avant) | 0 |
| Conflit maquette/test détecté après le GO | 0 | **1** | non mesuré |
| Capture simulée | 0 | 0 | 0 |
| Push, tag ou release sans GO | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 : Rouge prouvé avant le code
| Attribut | Valeur |
|----------|--------|
| Description | Règle 27 de la consigne de worker : avant de commiter des tests rouges, le worker les exécute et cite dans le message du commit la ligne d'échec de chaque test ; si le disque l'empêche, il le dit et la story n'est pas comptée « rouge prouvé ». Les fournisseurs de données sont relus avant ce commit. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 33 |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 33, 0 correction des données d'un test rouge dans le commit du code ; chaque commit de tests rouges cite l'échec observé. |
| Priorité | Haute |

### Action 2 : Croiser maquettes et tests existants au planning
| Attribut | Valeur |
|----------|--------|
| Description | Pour toute refonte, la tâche de cadrage liste les tests « non modifiés » et l'orchestrateur les confronte à la maquette avant de demander le GO à Thibaut ; tout conflit est noté au registre avec options. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le GO de la première maquette du sprint 33 |
| DoD | Table « invariant de test → élément de la maquette » jointe au cadrage ; 0 conflit détecté après le GO. |
| Priorité | Moyenne |

### Action 3 : Disque, swap et verrou des tests
| Attribut | Valeur |
|----------|--------|
| Description | Thibaut libère le disque (cible 10 Go libres, commandes en chemins absolus précédées d'un `du -sh`) ; `bin/check-disk` annonce le nombre de runs autorisés et `bin/run-tests` met en file d'attente derrière le verrou. Statut de T-TECH-32-01 et T-TECH-32-02 confirmé au préalable. |
| Responsable | Thibaut pour le disque ; Orchestrateur (Claude) pour les scripts |
| Deadline | Avant le lancement des workers du sprint 33 |
| DoD | Au moins 10 Go libres au lancement ; 0 attente de verrou de plus de 5 minutes ; suite complète locale jouée avant la PR du sprint 33 ou attente explicite demandée à Thibaut avant le push. |
| Priorité | Haute |

### Action 4 : Rendu et mesure sans contournement
| Attribut | Valeur |
|----------|--------|
| Description | `bin/render-page` n'inclut pas la barre de débogage Symfony quand une option l'indique (ou un script documenté produit la copie pour pa11y) ; le script puppeteer de mesure à 320 et 375 px est versionné dans `bin/` ou `docs/` avec sa documentation dans `docs/captures-vitrine.md`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Première capture du sprint 33 |
| DoD | Un contrôle pa11y est lancé sur une copie sans barre de débogage sans retouche manuelle ; la mesure mobile fonctionne sans serveur lancé à la main ; le tout est couvert par un test ou une commande documentée. |
| Priorité | Moyenne |

### Action 5 : Contrôles de production et reliquats
| Attribut | Valeur |
|----------|--------|
| Description | Le jour du déploiement de la PR #127 : pa11y WCAG2AAA FR et EN et largeurs à 320, 375 et 1280 px des pages « Preuve technique » et « Architecture » en production (T-205-10 et 11, T-206-11 et 12), avec une nouvelle tentative en cas d'erreur transitoire de redéploiement ; puis les reliquats du sprint 31 (T-TECH-32-07, T-TECH-32-04) si le disque le permet. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Le jour du déploiement de `main`, avant le planning du sprint 33 |
| DoD | Mesures consignées dans les fichiers de tâches ; statuts des tâches du sprint 32 alignés sur `.bmad/sprint-status.yaml` ; tout défaut ouvre une tâche au sprint 33 sur décision de Thibaut. |
| Priorité | Haute |

## Intégration au sprint 33

Les actions 1 à 5 sont à reprendre au planning du sprint 33 (T-TECH-33-01 à 05). Périmètre prévu par Thibaut : US-207 (indexation des pages vitrine, débloquée par US-205 et US-206) puis US-209 (dépôts de plugins). Les reliquats du sprint 32 s'y ajoutent s'ils ne sont pas soldés.

## Points positifs à conserver

- 9 points sur 9, sans sacrifice, avec des maquettes validées « tel quel » avant le code.
- La CI verte sur 6 contrôles et deux modifications de tests décidées avant l'action.
- Une règle 26 qui a tenu : 0 décision écrite après l'action.
- 0 capture simulée, 0 publication sans GO.
