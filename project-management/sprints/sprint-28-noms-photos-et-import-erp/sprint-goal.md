# Sprint 28 : Noms, photos et import ERP (catalogue sans marque réelle, photos cohérentes, import de prix et de stock)

> **Planifié le 2026-10-25**, au lendemain de la clôture du sprint 27 (13 points sur 13). Périmètre validé par Thibaut le 2026-10-24 : **13 points**. La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-28`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 28 |
| Début | 2026-10-25 (planning) |
| Fin | au plus tard le 2026-11-21 ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 48 tâches, 34,2 h (33,25 h pour Claude, 0,95 h d'actions de Thibaut) pour 35,3 h de capacité (13 × 2,1 h + 8 h transverses), soit 2,05 h de marge |
| Vélocité de référence | S24 19, S25 17, S26 29, S27 13 (moyenne 19,5) |
| Branche | `feature/sprint-28-dev` (branche partagée, créée depuis `main` ; pas de checkout dans l'arbre partagé) |

Le périmètre (13 points) est sous la moyenne (19,5), mais deux stories passent par des feux verts de Thibaut (planche de photos, GO du plugin) et US-187 occupe 8 points sans marge propre. L'ordre de sacrifice ci-dessous est **validé par Thibaut le 2026-10-25**.

## Sprint Goal

> « Présenter un catalogue crédible, avec des noms de produits sans marque réelle et une photo cohérente par famille, et fermer la boucle de l'échange avec l'ERP fictif : un fichier de prix et de stock est importé par le plugin, avec rapport de rejet et journal des lots, sans aucun système réel. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-195](../../backlog/user-stories/US-195.md) | Noms de produits sans marque réelle | 2 | Should | ready-for-dev | **protégée** |
| [US-196](../../backlog/user-stories/US-196.md) | Photos du catalogue cohérentes et sans marque | 3 | Should | ready-for-dev (planche et GO de Thibaut avant tout téléchargement) | **n°2 (validé)** |
| [US-187](../../backlog/user-stories/US-187.md) | Import CSV de prix et de stock : plugin (tranche A, 5 pts) puis démonstration (tranche B, 3 pts) | 8 | Could | ready-for-dev (questions 3, 4 et 6 tranchées le 2026-10-25, GO du plugin à venir) | **n°1 pour la tranche B (validé)** |

Total engagé : **13 points**.

- **US-195** retire les marques réelles de 77 produits sur 210 (noms, descriptions, référence fabricant) ; SKU et slugs ne changent pas ; un test de garde interdit leur retour.
- **US-196** reprend le processus d'US-186 : recherche sur Pexels et Unsplash (licence relue), **planche présentée à Thibaut**, **GO explicite avant tout téléchargement**, puis WebP, manifeste et page Crédits. Elle dépend d'US-195 pour les noms affichés sur la planche.
- **US-187** est la story B de l'[ADR 0017](../../../docs/adr/0017-connecteur-erp-de-reference.md). La **tranche A** vit dans le plugin `thibmonier/sylius-erp-flatfile-plugin` (dépôt séparé, TDD, CI du plugin, puis **GO de Thibaut pour push, tag et release**) ; la **tranche B** intègre le plugin à la démo (montée de version, simulateur qui produit un fichier de prix et de stock, import au chargement, page des échanges étendue).
- Les **cinq actions de la rétrospective du sprint 27** sont des tâches transverses T-TECH-28-01 à T-TECH-28-05 (voir [tasks/technical-tasks.md](./tasks/technical-tasks.md)) ; deux d'entre elles (règles 13 et 16 de la consigne, lignes de la checklist) sont déjà écrites au planning.

**Hors sprint** : API d'import et adaptateurs d'éditeur (stories D et E de l'ADR 0017), US-128 (attend ses données), US-103 et US-121 (conditionnées au transcript T-103-03), US-154, US-062.

## Décisions de Thibaut (2026-10-25)

1. Ordre de sacrifice validé : tranche B d'US-187 (démo), puis US-196 ; US-195 protégée.
2. Questions 3, 4 et 6 de l'ADR 0017 : prix par canal (code de variante + canal + prix, `ChannelPricing`), sans groupe de clients ; stock en valeur absolue `on_hand`, sans toucher à `on_hold` ; aucune rétention dans le plugin, remise à zéro nocturne dans la démo.
3. Le GO du tag `v0.2.0` sera demandé quand la tranche A sera prête : T-187-10 reste 📅.

## Estimation d'US-187

La fiche annonce 8 points. La décomposition donne 16,75 h de Claude pour 16,8 h attendues : **8 points tiennent, sans marge**. Le découpage en deux tranches rend le risque lisible : la tranche A (10 h, plugin) a de la valeur seule, la tranche B (6,75 h, démonstration) est sacrifiée en premier. Inconnues : le modèle de prix et de stock de Sylius et la matrice CI du plugin (tranche A), l'attente du GO de Thibaut puis de Packagist avant de commiter la montée de version dans la démo (tranche B).

## Politique Sylius Plus

Aucun plugin qui recouvre Sylius Plus n'est promu. Un recouvrement **possible**, à lever avant toute promotion : l'import de prix et de stock (US-187) peut toucher le périmètre de l'inventaire de Plus ; la documentation officielle de Plus est lue en T-187-01 et le résultat noté. Tant qu'il n'est pas levé : dépôt public mais ni annonce, ni comparaison, ni fiche Marketplace. Les noms et les photos (US-195, US-196) ne sont pas concernés.

## Checklist de démarrage (2026-10-25)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 27 clos le 2026-10-24 (13 points sur 13, revue et rétro écrites)
- [x] Actions de la rétrospective du sprint 27 reprises :
  - action 1 : un seul cache de rendu par worker, seuil disque appliqué aux rendus, nettoyage `--all` : T-TECH-28-01 et T-TECH-28-06, **avant le premier lot** ;
  - action 2 : `bin/proof-rebase` et règle « PR fusionnées en série » (écrite au planning dans la consigne) : T-TECH-28-02 ;
  - action 3 : ✅ règle 16 « que devient cet état après un déploiement ? » écrite au planning (T-TECH-28-03), appliquée à US-187 (T-187-16, T-187-19) ;
  - action 4 : ✅ règle 13 complétée au planning (T-TECH-28-04) ;
  - action 5 : LCP de la home remesuré au premier lot (T-TECH-28-05), puis LCP du catalogue à la livraison d'US-196 (T-196-01 et T-196-09).
- [x] Backlog priorisé : périmètre validé par Thibaut le 2026-10-24
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,3 h pour 33,25 h de charge de Claude
- [x] Fiches US-187, US-195 et US-196 à jour ; ADR 0017 mis à jour (section « Décision (2026-10-25, sprint 28) »)
- [x] Branche `feature/sprint-28-dev` créée depuis `main` ; `.bmad/sprint-status.yaml` mis à jour
- [ ] **Disque : relever `df -h .` et `bin/check-disk` avant le premier lot** (6 Go pour une suite complète, 5 Go pour tout le reste) ; swap relevé (T-TECH-28-06)

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [x] Questions 3, 4 et 6 de l'ADR 0017 tranchées par Thibaut le 2026-10-25 (T-187-02)
- [ ] Planche de photos présentée à Thibaut et GO de téléchargement (T-196-04)
- [ ] GO de Thibaut pour push, tag et release du plugin (T-187-10)

## Definition of Ready (contrôle du 2026-10-25)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis | État |
|----|-------|----------------------|-----------|------|
| US-195 | oui | oui (1+2+2) | inventaire T-189-01 ; fixtures `fixtures/csv/*.csv` ; 6 Go libres | prête |
| US-196 | oui | oui (1+2+2) | US-195 pour les noms de la planche ; sélection et GO de Thibaut avant téléchargement ; `bin/fetch-product-images` à adapter ; un seul cache de rendu | prête (porte : planche, puis GO) |
| US-187 | oui (affinée à l'étape T-187-03) | oui (1+2+2 plugin, 1+1 démonstration) | questions 3, 4 et 6 tranchées le 2026-10-25 ; plugin v0.1.0 dans son dépôt ; GO de Thibaut avant push, tag et release ; 6 Go libres | prête (porte : GO plugin, demandé quand la tranche A sera prête) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR (y compris pour le code de l'orchestrateur et pour le plugin)
- [ ] US-195 : vérifiée en production (URL de `EPI-CASK-001` en 200, aucun nom de la liste noire dans le rendu public, pa11y). US-196 : pa11y, largeurs et LCP du catalogue avant/après en production. US-187 : pages publiques vérifiées en production ; la page du back-office l'est en local et sur l'image reconstruite (aucun identifiant n'est saisi en production)
- [ ] **Tout état écrit hors base** (règle 16) : où il vit, ce qui le recrée au démarrage, vérifié dans un conteneur neuf avant le merge
- [ ] **Mesures de production consignées dans le fichier de tâches le jour du déploiement** ; 0 tâche de contrôle en production ouverte à la clôture
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur
- [ ] PR de la démo qui ajoute ou supprime un fichier de tests : `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check`
- [ ] Aucun secret ni détail de sécurité exploitable dans un dépôt public ; ton neutre, aucune comparaison avec Sylius Plus, aucun ERP réel nommé
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] **Aucun téléchargement de photo avant le GO de Thibaut ; aucun push, tag, release, dépôt ni PR de plugin sans GO de Thibaut** ; aucun identifiant saisi en production

## Actions de Thibaut

- ✅ Questions 3, 4 et 6 de l'ADR 0017 tranchées et ordre de sacrifice validé le 2026-10-25.
- Choisir les photos sur la planche et donner le GO de téléchargement (T-196-04).
- Donner le GO pour push, tag et release du plugin (T-187-10) ; soumettre ou mettre à jour le paquet sur Packagist s'il le faut (T-187-12).
- Quitter les applications lourdes si le swap dépasse la moitié de l'espace libre (relevé avant chaque lot).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| US-187 sous-estimée (plugin et démonstration à 16,75 h pour 16,8 h attendues) | Moyenne | Élevé | Découpage en tranches A et B ; tranche B sacrifiée la première ; point d'étape à la fin de la tranche A |
| GO du plugin ou Packagist tardifs : la tranche B ne peut pas commiter la montée de version | Moyenne | Moyen | Développement de la tranche B sur une copie locale du plugin non commitée ; `composer.json` et verrou commités après le tag ; sinon tranche B sacrifiée |
| Planche refusée ou retouchée plusieurs fois : US-196 bloquée avant tout téléchargement | Moyenne | Moyen | Aucune image dans le dépôt avant le GO ; 2 à 3 candidats par famille ; travaux sans téléchargement (tests rouges, script) avancés pendant l'attente ; US-195 et US-187 avancent en parallèle |
| Photos sous licence douteuse ou marque visible | Faible | Élevé | Licence relue sur le site pour chaque candidat ; revue visuelle famille par famille ; test de manifeste qui échoue sans licence libre |
| 77 noms en deux langues, descriptions comprises : volume sous-estimé | Moyenne | Moyen | Tableau ancien → nouveau relu avant application (T-195-03) ; application assistée par script ; US-195 protégée |
| Recouvrement avec Sylius Plus sur l'import de prix et de stock | Faible | Moyen | Documentation de Plus lue en T-187-01 ; aucune promotion ni comparaison tant que non levé |
| État de l'échange perdu à chaque déploiement (fichiers dans le conteneur, journal en base) | Moyenne | Moyen | Règle 16 ; recréation au démarrage écrite en T-187-16 ; vérification en conteneur neuf en T-187-19 (cause du défaut #100) |
| Espace libre sous 6 Go : aucune suite complète | Moyenne | Élevé | Un seul cache de rendu (T-TECH-28-01), `bin/check-disk` avant chaque rendu, relevé avant et après chaque lot, `clean-test-caches --all` hors lots, budget disque du dépôt du plugin relevé |
| Conflits de `proof.json` entre PR parallèles | Moyenne | Faible | `bin/proof-rebase` (T-TECH-28-02), PR fusionnées en série |
| Page du back-office non vérifiable en production (aucun identifiant saisi) | Certaine | Faible | Vérification locale et sur l'image reconstruite ; DoD écrite en conséquence |
| Commit au mauvais numéro de story | Moyenne | Faible | Règle 13 : `git show --stat` et `git log -1` relus contre la branche et la PR avant le push |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-21. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-28-10), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-25 (périmètre validé par Thibaut, décomposition faite) | fait |
| Point d'étape | À la fin de la tranche A d'US-187 (tag du plugin) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-21 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
