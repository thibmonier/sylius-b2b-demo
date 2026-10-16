# Checklist de démarrage — Sprint 3

## Prérequis (sprint précédent)

- [ ] Sprint 2 entièrement livré (26/26 points)
- [ ] Branche `feature/sprint-2-donnees-b2b` mergée sur `main`
- [ ] Branche `feature/sprint-3-nouveaux-plugins` créée depuis `main`
- [ ] CI verte sur `main` après le merge du sprint 2
- [ ] `docker compose up` démarre l'application sans erreur
- [ ] `bin/console app:fixtures:load` termine sans erreur (catalogue + entreprises + comptes)
- [ ] 150 à 250 produits industriels visibles sur les canaux B2B et B2C
- [ ] Au moins 5 entreprises créées avec membres et comptes correctement rattachés
- [ ] Les 21 plugins sont enregistrés sans conflit (`lint:container` vert)
- [ ] Canaux B2B et B2C configurés avec leurs locales FR/EN (US-007)

## Environnement technique

- [ ] PHP 8.4 (ou Docker) opérationnel
- [ ] Docker Desktop opérationnel
- [ ] `bin/console` répond sans erreur dans le conteneur
- [ ] `doctrine:migrations:status` : aucune migration en attente
- [ ] `bin/console app:demo:reset` fonctionne en moins de 2 minutes

## Données de démo disponibles

- [ ] Catalogue industriel chargé (EPI, quincaillerie, fournitures)
- [ ] Entreprises Acme, Beta, Gamma (et al.) présentes avec membres et comptes
- [ ] Comptes de démo rattachés aux plugins company-accounts, credit-limit, approval-workflow, PunchOut
- [ ] Canal B2B configuré (mode ouvert par défaut — US-019 modifiera cela)
- [ ] Canal B2C configuré et fonctionnel

## Décision d'architecture à confirmer en Sprint Planning Part 1

- [ ] **Point de décision acté** : les stories US-019 (mode d'accès), US-020 (inscription validée), US-021 (référence PO), US-022 (reprise de la référence) sont implémentées comme **fonctionnalités de l'application de démo** (`src/`, `config/`, `fixtures/`, `templates/`) — **pas** comme nouveaux dépôts de plugins publiés
- [ ] Si l'extraction en plugin autonome est jugée nécessaire pour US-019 ou US-020, une ADR est ouverte et la story est repoussée au sprint 4 (ne pas trancher au milieu du sprint)
- [ ] US-023 est un spike : le livrable est une ADR documentée dans `docs/adr/`, pas du code de production

## Risques identifiés — actions préventives

### US-022 : story transverse (4 surfaces à brancher)
- [ ] Identifier les extensions de commande déjà en place (entity, repository) avant de démarrer
- [ ] Lister les templates à modifier : grille admin, fiche commande, e-mail de confirmation
- [ ] Vérifier la disponibilité du champ BT-10 dans le plugin Factur-X (`sylius-b2b/facturx-invoice`)
- [ ] Vérifier que le plugin PunchOut expose un hook sur l'OrderRequest XML

### US-026 : correctifs inter-plugins
- [ ] Reproduire les trois scénarios de régression (container-fill, PunchOut checkout, OrderRequest + credit/approbation) avant d'écrire le moindre correctif
- [ ] Créer un test de fumée par scénario avant le correctif (TDD)

## Stories prêtes pour le développement

- [ ] US-019 au statut `ready-for-dev`, critères d'acceptation relus
- [ ] US-020 au statut `ready-for-dev` (peut attendre que US-019 soit en cours)
- [ ] US-021 au statut `ready-for-dev`, critères d'acceptation relus
- [ ] US-022 au statut `ready-for-dev` (peut attendre que US-021 soit en cours)
- [ ] US-023 au statut `ready-for-dev`, scope spike borné (ADR = livrable)
- [ ] US-026 au statut `ready-for-dev`, trois scénarios de régression identifiés

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé en équipe (une phrase)
- [ ] 6 stories sélectionnées, 27 points
- [ ] Contrainte d'architecture (fonctionnalités démo vs plugins publiés) confirmée
- [ ] Dépendances inter-stories confirmées (US-019→US-020 ; US-021→US-022 ; US-023 et US-026 indépendants)
- [ ] Ordre de réalisation validé

## Sprint Planning Part 2 — Comment

- [ ] Décomposition en tâches effectuée (voir `tasks/`)
- [ ] Estimations posées (0,5 h à 8 h par tâche max)
- [ ] Graphe de dépendances Mermaid créé (`task-board.md`)
- [ ] Task Board initialisé (`task-board.md`)

## Critères de fin de sprint (Definition of Done global)

- [ ] Canal B2B configurable en ouvert / prix masqués / fermé depuis l'admin
- [ ] Routes exemptées du canal fermé : accueil, login, inscription, PunchOut (`/punchout`), flux (`/feeds`), MCP
- [ ] Workflow d'inscription B2B soumis à validation admin : compte en attente, approbation/refus par e-mail
- [ ] Champ référence PO + centre de coût présent au checkout (obligatoire si canal configuré ainsi)
- [ ] Référence PO visible dans la grille admin, la fiche commande, l'e-mail de confirmation et la facture Factur-X (BT-10)
- [ ] OrderRequest PunchOut renseigne la référence depuis l'orderID
- [ ] ADR wishlist créée dans `docs/adr/` avec décision motivée (adopter / ne pas adopter)
- [ ] Scénarios US-026 verts : container-fill transparent, PunchOut checkout sans commande Sylius parasite, OrderRequest respecte credit-limit et approbation
- [ ] ECS et PHPStan passent sur tout le code ajouté/modifié
- [ ] Aucun secret dans le dépôt
- [ ] CI verte sur la branche du sprint

## Outils et ressources

| Ressource | URL / Commande |
|-----------|---------------|
| Plugin channel-access (référence) | `sylius-b2b/channel-access` (à vérifier Packagist) |
| Plugin registration-approval (référence) | `sylius-b2b/registration-approval` (à vérifier Packagist) |
| Wishlist officielle Sylius | `sylius/wishlist-plugin` sur Packagist |
| Tech-spec §2 (architecture plugins) | `project-management/tech-spec.md` §2 |
| ADR template | `docs/adr/0000-template.md` (à créer si absent) |
| Sprint 2 handoff | `project-management/sprints/sprint-2-donnees-b2b/` |
| Factur-X BT-10 spec | champ BuyerReference dans la norme EN 16931 |
