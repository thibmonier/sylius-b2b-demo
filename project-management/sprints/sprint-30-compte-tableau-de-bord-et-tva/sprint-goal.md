# Sprint 30 : Compte, tableau de bord et TVA (refonte de « Mon compte » et du tableau de bord des achats, TVA affichée)

> **Planifié le 2026-10-25**, après la clôture du sprint 29 (13 points sur 13). Périmètre décidé par Thibaut le 2026-10-25 : **13 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-30`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 30 |
| Début | 2026-10-25 (planning) |
| Fin | au plus tard le 2026-11-21 ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 41 tâches, 34,5 h (33,5 h pour Claude, 1 h d'actions de Thibaut) pour 35,3 h de capacité (13 × 2,1 h + 8 h transverses), soit 1,8 h de marge |
| Vélocité de référence | S25 17, S26 29, S27 13, S28 13, S29 13 (moyenne 17) |
| Branche | `feature/sprint-30-dev` (branche partagée, créée depuis `main` ; pas de checkout dans l'arbre partagé) |

Le périmètre (13 points) est égal aux trois derniers sprints. La marge est étroite (5 %) : les deux refontes sont estimées à leur capacité exacte, deux portes de Thibaut jalonnent le sprint (deux maquettes) et les deux tâches techniques reportées trois fois passent en tête. L'ordre de sacrifice ci-dessous est **décidé par Thibaut le 2026-10-25**.

## Sprint Goal

> « Donner à « Mon compte » et au tableau de bord des achats la clarté de la home, avec des maquettes validées avant le code, et afficher une TVA juste au panier et sur les commandes. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-199 : 1+3+3, US-201 : 1+2+4, US-202 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-199](../../backlog/user-stories/US-199.md) | Refonte de la page « Mon compte » | 5 | Should | ready-for-dev (maquette validée avant tout Twig) | **protégée** |
| [US-201](../../backlog/user-stories/US-201.md) | Tableau de bord des achats plus lisible | 5 | Should | ready-for-dev (maquette validée avant tout Twig ; surcharge dans la démo) | **n°2** |
| [US-202](../../backlog/user-stories/US-202.md) | Catégories de taxe : TVA au panier et sur les commandes | 3 | Should | ready-for-dev (sans maquette) | **n°1** |

Total engagé : **13 points**.

- **US-199** refond « Mon compte » : accueil par rôle (Claire, Paul, Isabelle), raccourcis groupés (Achats, Validation, Société, Profil), menu latéral sur bureau et accès compact sur téléphone. Les entrées des plugins sont reprises par leurs hooks, sans modifier les plugins. Maquette Claude Design **validée par Thibaut avant tout Twig**.
- **US-201** refond le tableau de bord des achats du plugin company-insights **par surcharge dans la démo** (hooks Twig et styles du thème B2B) : indicateurs d'abord, graphique mensuel accessible avec comparaison N-1, tops compacts, période claire, état vide. **Aucune modification ni release du plugin** (décision de Thibaut). Même démarche de maquette.
- **US-202** affecte la catégorie de taxe « standard » (déjà en fixtures, TVA 20 %, prix HT) aux produits du catalogue et aux produits importés par l'ERP fictif, réaffiche la ligne TVA et le total TTC du panier (maquette validée au sprint 29) et garde **la limite d'approbation comparée au total HT** (décision de Thibaut).
- **Tâches techniques en tête du sprint** (action 1 de la rétro du sprint 29, reportées trois fois) : un seul cache de rendu par worker avec seuil disque (T-TECH-30-01), `bin/proof-rebase` (T-TECH-30-02) ; puis les actions 2 à 5 : vignettes `b2b_photo` dans `bin/render-page` (T-TECH-30-03), test d'échange ERP isolé (T-TECH-30-04), règles 21 à 23 écrites au planning (T-TECH-30-05), capture à 1280 px relue avant la PR (T-TECH-30-09).

**Hors sprint** : US-128, US-103, US-121 (conditionnées), US-154. Les contrôles de production ouverts (T-197-09 du sprint 29 ; T-195-06, T-196-09, T-196-10 du sprint 28 ; T-TECH-29-03, LCP de la home), après la remise à zéro de 03:00 du 2026-10-26, restent dans les fichiers de leurs sprints et **ne sont pas comptés** dans les 34,5 h.

## Décisions de Thibaut (2026-10-25)

1. Périmètre : US-199, US-201 et US-202 (13 points), plus les tâches techniques reportées et les actions de la rétro du sprint 29.
2. US-201 : surcharge dans la démo ; aucune release du plugin company-insights.
3. Ordre de sacrifice : US-202 d'abord, puis US-201 ; US-199 protégée.
4. US-202 : la limite d'approbation compare le total HT.
5. Maquettes Claude Design pour US-199 et US-201 dans le canevas unique ; validées avant tout Twig ; US-202 sans maquette.

## Estimation du sprint

La fiche annonce 5 + 5 + 3 points. La décomposition donne 10 h (US-199), 10 h (US-201) et 6 h (US-202) pour Claude, plus 7,5 h de transverses, soit **33,5 h pour 35,3 h : les 13 points tiennent, avec 1,8 h de marge**. Franchement : c'est tenable parce que 13 points ont été livrés aux sprints 27 à 29 avec une marge comparable. Inconnues : les retouches des deux maquettes ; la densité des entrées de plugins dans « Mon compte » ; l'accessibilité du graphique du plugin (à surcharger sans modifier le plugin) ; **la comparaison HT de la limite d'approbation, que le plugin ne fait pas** (voir risques).

## Politique Sylius Plus

Aucun plugin qui recouvre Sylius Plus n'est promu. Ce sprint ne modifie aucun plugin : les entrées de « Mon compte » et le tableau de bord sont repris par leurs hooks existants et surchargés dans la démo. Ton neutre, aucune comparaison avec Plus.

## Checklist de démarrage (2026-10-25)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 29 clos le 2026-10-25 (13 points sur 13, revue et rétro écrites ; T-197-09 en point ouvert)
- [x] Actions de la rétrospective du sprint 29 reprises :
  - action 1 : T-TECH-30-01 et 02 en tête du sprint, avant la première maquette ;
  - action 2 : T-TECH-30-03 avant la première capture ;
  - action 3 : ✅ fichiers partagés déclarés au planning (règle 21 et matrice de [tasks/README.md](./tasks/README.md)) ;
  - action 4 : T-TECH-30-09 (capture à 1280 px relue avant la PR, règle 23) ;
  - action 5 : T-TECH-30-04 et ✅ règle 22 (aucun test depuis un worktree au `vendor` en lien symbolique).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-25
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,3 h pour 33,5 h de charge de Claude
- [x] Fiches US-199, US-201 et US-202 à jour (en-tête « Sprint 30 ») ; EPIC-12 à jour
- [ ] Branche `feature/sprint-30-dev` à créer depuis `main` après la fusion du planning
- [ ] Disque : 8 Go libres à la clôture du sprint 29 ; relever `df -h .` et `bin/check-disk` avant le premier lot

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO de la maquette « Mon compte » (T-199-04) et du tableau de bord des achats (T-201-04)

## Definition of Ready (contrôle du 2026-10-25)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis | État |
|----|-------|----------------------|-----------|------|
| US-199 | oui | oui (1+3+3) | US-186 mergée ; comptes Claire, Paul et Isabelle en fixtures (faits) ; `bin/render-page` à cache unique (T-TECH-30-01) ; GO de la maquette avant tout Twig | prête (porte : maquette, puis GO) |
| US-201 | oui | oui (1+2+4) | US-149 à US-151 et US-155 livrées ; historique de commandes d'Acme sur 18 mois (US-155, fait) ; surcharge dans la démo décidée ; GO de la maquette avant tout Twig | prête (porte : maquette, puis GO) |
| US-202 | oui | oui (1+2+2) | US-200 livrée ; catégorie `standard` et taux `fr_standard` (20 %, hors prix) déjà en fixtures ; règle HT décidée par Thibaut | prête |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR
- [ ] **Maquette validée par Thibaut, GO noté au registre, avant le premier gabarit Twig** (US-199, US-201)
- [ ] US-199 et US-201 : pa11y WCAG2AAA FR et EN, 360 et 1280 px en local (comptes des fixtures) et sur l'image reconstruite ; B2C inchangé. US-202 : prix HT affichés inchangés (comparés avant/après), tunnel de commande vert, limite comparée au HT
- [ ] **Captures réelles** par `bin/render-page` (vignettes comprises, T-TECH-30-03) ; **capture à 1280 px de la home connectée et de chaque page touchée relue par l'orchestrateur avant la PR** (règle 23)
- [ ] **Tout état écrit hors base** (règle 16) : où il vit, ce qui le recrée au démarrage, vérifié dans un conteneur neuf avant le merge (aucun attendu ce sprint, à confirmer au cadrage)
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public ; US-202 : prix et catégories visibles en production après la remise à zéro de 03:00 suivant le déploiement (dit à l'avance)
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur
- [ ] Preuve publiée par `bin/proof-rebase` (T-TECH-30-02) ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun secret ni détail de sécurité exploitable dans un dépôt public ; ton neutre, aucune comparaison avec Sylius Plus ; **aucun plugin modifié**
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu ; aucun commit embarquant le travail d'un autre worker (règle 21)
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun identifiant saisi en production

## Actions de Thibaut

- Valider la maquette « Mon compte » (T-199-04) et celle du tableau de bord des achats (T-201-04), ou demander des retouches.
- Trancher, si le cadrage d'US-202 le confirme, entre une décoration du contrôle d'approbation dans la démo et une option du plugin (voir risques).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Marge de 1,8 h (5 %) : les deux refontes sont à leur capacité exacte | Moyenne | Élevé | Ordre de sacrifice décidé (US-202, puis US-201) ; sous-pages du compte (US-199) négociables ; point d'étape après les deux maquettes |
| **Limite d'approbation en HT** : le plugin compare `$order->getTotal()`, qui inclura la TVA | Certaine | Moyen | Cadrage T-202-01 : décoration des services du plugin dans la démo (sans modifier le plugin), sinon question à Thibaut (option du plugin = PR, tag et release sur son GO) ; aucun changement de comportement sans décision au registre |
| Graphique du plugin peu accessible et difficile à surcharger | Moyenne | Moyen | Cadrage T-201-01 des gabarits et hooks ; surcharge de gabarit dans `templates/bundles/` si aucun hook ne suffit ; table de données en repli accessible |
| Maquettes retouchées plusieurs fois : tâches de code 📅 trop longtemps | Moyenne | Moyen | Captures sur le rendu réel (T-TECH-30-01 et 03 d'abord) ; US-202 et les transverses avancent pendant l'attente |
| Entrées de plugins dans « Mon compte » sans droit pour le rôle (lien vers un refus d'accès) | Moyenne | Moyen | Test par rôle dans les tests rouges ; raccourcis conditionnés par `is_granted` comme les menus actuels |
| US-202 : prix affichés modifiés par erreur (taxe incluse) ou commandes des fixtures sans TVA | Moyenne | Élevé | Taux `included_in_price: false` relu ; prix comparés avant/après sur la liste et la fiche ; fixtures rechargées en local |
| Commit embarquant le travail d'un autre worker (incident du sprint 29) | Moyenne | Faible | Règle 21 : un fichier de hooks, un partial SCSS et un domaine de traduction par story ; `git diff` relu sur les fichiers communs |
| Tests lancés depuis un worktree au `vendor` en lien symbolique (code de l'arbre principal exécuté) | Faible | Moyen | Règle 22 |
| Disque et mémoire | Moyenne | Moyen | Un seul cache de rendu (T-TECH-30-01), `bin/check-disk`, aucune suite complète sous 6 Go |
| Conflits de `proof.json` entre PR | Moyenne | Faible | `bin/proof-rebase` (T-TECH-30-02), PR fusionnées en série |
| Page connectée non vérifiable en production (aucun identifiant saisi) | Certaine | Faible | Vérification locale et sur l'image reconstruite ; DoD écrite en conséquence |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-21. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-30-10), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-25 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après les deux maquettes (T-199-03 et T-201-03) et les GO | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-21 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
