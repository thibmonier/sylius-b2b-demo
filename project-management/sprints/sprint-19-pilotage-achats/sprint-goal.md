# Sprint 19 : Pilotage des achats (tableau de bord company-insights, kit média, simulateur PunchOut)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 19 |
| Début | 2027-03-05 (revue et rétro du sprint 18 ; démarrage effectif le 2027-03-08) |
| Fin | 2027-03-19 |
| Durée | 10 jours ouvrés (2027-03-08 au 2027-03-19) |
| Capacité | 25 points (20 Must, 5 Should) après ajustement du 2026-10-21 : la décomposition donnait 89,25 h pour 75,8 h de capacité ; US-121 et US-099 sacrifiées (72,25 h restantes) |
| Branche | `feature/sprint-19-pilotage-achats` |

Périmètre issu de la section « Sprint 19 » de la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md).

## Sprint Goal

> « Livrer en démo le tableau de bord des achats d'EPIC-15 (nouveau plugin company-insights 0.1.0), compléter le kit média pour la série de posts et rendre le simulateur PunchOut lisible pour un visiteur qui découvre le sujet. »

## Sprint Backlog

| ID | Titre | Points | MoSCoW | Statut |
|----|-------|--------|--------|--------|
| US-149 | Company-insights : squelette du plugin et indicateurs de la période | 5 | Must | ready-for-dev |
| US-150 | Company-insights : achats par mois sur 12 mois, comparés à l'année précédente | 5 | Must | ready-for-dev |
| US-151 | Company-insights : tops acheteurs, produits et catégories | 5 | Must | ready-for-dev |
| US-155 | Company-insights : historique d'achats de 12 mois et intégration à la démo | 5 | Must | ready-for-dev |
| US-121 | Kit média (2/2) : approbation, MCP, container-fill et visite complète | 5 | Should | sacrifiée (sprint 20) |
| US-146 | Création de commande admin : choisir une adresse de l'entreprise ou du carnet du client | 5 | Should | ready-for-dev |
| US-099 | Simulateur PunchOut habillé et expliqué | 3 | Should | sacrifiée (sprint 20) |

Total engagé : 25 points (20 Must, 5 Should) ; 33 points au cadrage, US-121 (5) et US-099 (3) sacrifiées le 2026-10-21.

**Statuts à surveiller** :
- US-121 reste en `backlog` : elle dépend d'US-103, qui attend T-103-03 (conversation réelle de Thibaut), et n'a que 3 scénarios Gherkin (1 nominal, 1 alternatif, 1 erreur) contre le minimum 1 + 2 + 2. À compléter au planning, puis passage en `ready-for-dev` si T-103-03 est livrée.
- US-099 a 4 scénarios (1 nominal, 2 alternatifs, 1 erreur) : une erreur à ajouter au planning.

**Ordre de sacrifice** : US-121, US-099, puis US-146. Les quatre Must d'EPIC-15 (20 points) ne se sacrifient pas.

## Jalons

- **MMF d'EPIC-15 en démo** : le tableau de bord des achats est visible pour Isabelle (compte de démo) avec 12 mois d'historique (US-155).
- **Plugin company-insights 0.1.0** : tag et release préparés par Claude ; soumission à Packagist par Thibaut lui-même.
- **Classe vis-à-vis de Sylius Plus (docs/vitrine-plugins.md)** : company-insights n'est pas encore classé dans le tableau. Les sources relevées (S1, S2) ne décrivent aucun tableau de bord d'achats ; seul l'« historique » du compte maître d'Organization Management (S2) s'en approche. Classe provisoire : **hors périmètre (à confirmer)**, traitée comme « concurrent » pour le ton : sobre, factuel, aucune mention de Plus dans les textes publiés. Ligne à ajouter au tableau dans US-149 (ou US-155) ; aucune communication sur les canaux Sylius.
- **Fin de sprint** : tableau de bord en démo, kit média complet (si US-103 livrée), simulateur lisible.

## Dépendances

- US-149 avant US-150, US-151 et US-155 (squelette du plugin, route et menu du compte entreprise).
- US-150 et US-151 avant US-155 (intégration à la démo et données d'historique).
- US-121 dépend d'US-103 (sprint 18), donc de T-103-03 (conversation réelle de Thibaut).
- US-146 se fait dans le dépôt du plugin `sylius-admin-order-creation-plugin` (matrice CI et versions supportées à lire au cadrage, action 2 de la rétro S18) ; dépendance optionnelle à `company-accounts`.
- Entrées requises de Thibaut : conversation réelle pour T-103-03 ; soumission du nouveau paquet à Packagist.

## Risques

| Risque | Mitigation |
|--------|------------|
| US-121 bloquée par T-103-03 (conversation réelle de Thibaut) | Demander la conversation dès le planning ; US-121 sacrifiée en premier si elle n'arrive pas avant le point d'étape du 2027-03-12 |
| Disque d'environ 7 Go libres (crises disque des sprints 17-18) | `df -h .` et `bin/check-disk` en début et milieu de journée, `bin/clean-test-caches`, un seul worker à la fois, un seul build d'image à la fois |
| Railway en incident : redéploiements bloqués | Vérifier en démo locale ; le redéploiement de la démo reste une commande de Thibaut, reportée après la fin de l'incident |
| PR #46 (PHP 8.5.12) en attente du merge de Thibaut | Ne pas bâtir de travail sur la plateforme PHP tant qu'elle n'est pas mergée ; US-147 reste hors de ce sprint |
| Nouveau dépôt company-insights : CI et matrice des versions à créer de zéro | Reprendre la matrice d'un plugin récent (Symfony 7.4 et 8, PHP 8.4 et 8.5) ; lire les contraintes `composer.json` de `company-accounts` |
| US-146 : matrice CI du plugin admin-order-creation non relue | Lire les versions supportées et la matrice CI au cadrage (action 2 de la rétro S18), avant d'écrire la fiche de tâches |
| Cloisonnement des données entre entreprises (indicateurs d'achat) | Scénarios « autre entreprise » d'US-149 et d'US-151 en tests d'intégration ; revue de sécurité du voter `COMPANY_VIEW_INSIGHTS` |
| Classe Sylius Plus de company-insights non confirmée | Ton neutre selon la charte ; aucune promesse sur les canaux Sylius ; vérification auprès de la documentation technique si Thibaut y a accès |
| Secrets fictifs au format réel dans les tests (alertes GitGuardian au sprint 18) | Action 4 de la rétro S18 : jetons recomposés à l'exécution |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation. Soumission à Packagist, publication LinkedIn et commandes Railway par Thibaut. `main` protégée : PR et contrôles requis, attendre la fin du run CI. Tests via `bin/run-tests` seulement. Un seul worker à la fois. Seul l'orchestrateur reconstruit l'image Docker. Aucune précision sur une faille dans un dépôt public. Rapports de fin ≤ 100 mots.

## Actions de la rétrospective du sprint 18

| Action (S18) | Responsable | Échéance | Prise en charge |
|--------------|-------------|----------|-----------------|
| 1. `bin/check-disk` mesure aussi la VM Docker ; `bin/run-tests` refuse de démarrer sous 3 Go dans la VM | Worker devops | Planning du sprint 19 | Tâche technique sans points ; DoD : 0 run invalidé par le disque |
| 2. Rubrique « versions supportées (PHP, Symfony, Sylius) et impact de version » dans le gabarit de story de plugin | Scrum Master | Affinage du sprint 19 | Appliquée à US-146 et au nouveau dépôt d'US-149 ; DoD : 0 cellule de matrice CI en échec découverte par la PR |
| 3. ECS et PHPStan rejoués sur l'arbre exact avant chaque push (T-161-06) | Orchestrateur | Début du sprint 19 | Citer la dernière ligne de sortie dans le rapport ; DoD : 0 CI rouge sur ECS ou PHPStan |
| 4. Secrets fictifs recomposés à l'exécution, règle dans `docs/tests-locaux.md` | Scrum Master | Planning du sprint 19 | Règle à documenter ; DoD : 0 alerte GitGuardian sur des données de test |
| 5. Commandes pour Thibaut sous forme de script du scratchpad ; Composer avec `php -d memory_limit=1G` | Orchestrateur | Immédiat | Scripts à OK ou FAIL par élément (tag et soumission du plugin) |

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts (stories de code)
- [ ] ECS (`src tests`) et PHPStan max verts, rejoués sur l'arbre exact avant le push
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié, `bin/measure-mobile`, pa11y WCAG2AAA
- [ ] Aucun secret ni détail exploitable dans le dépôt ; jetons fictifs recomposés à l'exécution
- [ ] Documentation à jour (README EN et FR du nouveau plugin)
- [ ] CI verte (« Quality and smoke tests », « Docker image » pour la démo ; matrice complète pour les plugins), run terminé avant le merge

## Checklist pré-sprint

- [ ] Sprint 18 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Branche `feature/sprint-19-pilotage-achats` active ; `git fetch` et branche vérifiés
- [ ] `df -h .` : au moins 5 Go libres (environ 7 Go constatés) ; `bin/check-disk` et `bin/clean-test-caches` lancés
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [ ] Les 7 stories ont une estimation ; Gherkin à compléter pour US-121 (1 + 2 + 2) et US-099 (une erreur)
- [ ] T-103-03 (conversation réelle de Thibaut) : date convenue
- [ ] Matrice CI et versions supportées de `admin-order-creation` lues (US-146)
- [ ] PR #46 (PHP 8.5.12) : décision de merge de Thibaut connue
- [ ] Incident Railway : état connu avant tout redéploiement
- [ ] Actions 1 à 5 de la rétro du sprint 18 prises en charge

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-03-08 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-03-08 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-03-12 | 30 min |
| Affinage Backlog (sprint 20) | 2027-03-15 | 1 h |
| Sprint Review | 2027-03-19 (matin) | 1 h |
| Rétrospective | 2027-03-19 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
