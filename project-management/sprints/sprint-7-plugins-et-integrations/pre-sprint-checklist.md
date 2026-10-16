# Checklist de démarrage — Sprint 7

## Prérequis (sprint précédent)

- [ ] Sprint 6 livré ou clôturé (PR mergée, revue de sécurité US-037 faite, rétro)
- [ ] Branche `feature/sprint-7-plugins-et-integrations` créée (`git fetch` + vérification de la branche courante avant tout commit ; aucun commit sans demande)
- [ ] CI verte sur `main` et sur la branche
- [ ] `docker compose up` démarre sans erreur ; `bin/console app:fixtures:load` passe
- [ ] `doctrine:migrations:status` : aucune migration en attente
- [ ] Reports du sprint 6 confirmés : US-006, US-029, US-030 ; M2, M4, M5, M6, M8 ; S2-R3 à S2-R10, S4-R3, T-033-05, T-034-02

## Nouveaux plugins (US-019, US-020)

- [ ] Modèle de dépôt relu : `sylius-company-accounts-plugin` (structure, CI, ECS/PHPStan/Rector, tests, README, licence)
- [ ] Noms des dépôts `tmonier/sylius-*` et des packages Packagist arrêtés (création des dépôts distants = action externe, accord requis)
- [ ] Topics GitHub à poser à la création : ecommerce, sylius, sylius-plugin, symfony + topics fonctionnels
- [ ] Compatibilité Sylius 2.3, Docker PHP 8.4 pour les tests
- [ ] Routes à exempter du mode fermé listées : accueil, connexion, inscription, PunchOut, MCP, feeds
- [ ] Contrat d'US-019 exploitable par US-020 (dépendance) identifié
- [ ] Plan de revue de sécurité des plugins avant demande de GO

## Plugins existants (US-038, US-039)

- [ ] `sylius-punchout-cxml-plugin` : `PunchoutCartFactory::create`, `setCustomerWithAuthorization`, mappings Doctrine, subscriber, workflow `sylius_order_checkout`
- [ ] `sylius-container-fill-plugin` : évaluation des règles « tous produits »
- [ ] Démo : `App\PunchOut\AuthorizedPunchOutOrderFactory`, `config/services.yaml`, `resolve_target_entities` repérés
- [ ] `sprint-3-nouveaux-plugins/review-findings.md` et `sprint-5-listes-et-scenarios/review-findings.md` relus

## Actions soumises au GO de l'utilisateur

- [ ] GO publication du plugin d'accès par canal (tag, Packagist)
- [ ] GO publication du plugin d'inscription validée
- [ ] GO publication de punchout-cxml (US-038)
- [ ] GO publication de container-fill (US-039)
- [ ] Rappel : pas de railway, gh, push, `composer require` sans accord explicite
- [ ] Accès au compte Railway pour le relevé du coût (US-041), à partir du 2026-10-23

## Environnement technique

- [ ] Rappels : `DEMO_MODE` vaut 0 par défaut ; pas de wkhtmltopdf sur le Mac local (CI)
- [ ] Disque local presque plein : éviter les rebuilds d'images
- [ ] Mode d'exécution actuel (FrankenPHP classique) documenté avant le spike US-006

## Analyse préalable (US-040, US-041)

- [ ] `review-findings.md` du sprint 6 (M2, M4, M5, M6, M8) relu
- [ ] `DemoResetCommand` et purge de `public/media/image` (M4)
- [ ] `http_client.transport` et clients scopés du serveur MCP (M5)
- [ ] `CsvProductReader` et fixtures (S2-R3 à S2-R10), calcul des prix « dès » (S4-R3)
- [ ] Bandeau de démo (T-033-05) ; planification du reset 03:00 Europe/Paris

## Stories prêtes pour le développement

- [ ] US-006, US-019, US-020, US-029, US-030, US-038, US-039, US-040, US-041 au statut `ready-for-dev`
- [ ] Gherkin relus (≥ 1 nominal, 2 alternatifs, 2 erreurs) : US-006, US-019, US-020, US-029 et US-030 n'ont qu'un ou deux scénarios à compléter
- [ ] Frontmatter US-019 et US-020 : Sprint 7 (fait)
- [ ] Dépendances vérifiées : US-020 après US-019 ; retrait du contournement après publication d'US-038

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé (une phrase)
- [ ] 9 stories sélectionnées, 34 points (provisoire)
- [ ] Jalons compris : quatre GO de publication, relevé du coût au 2026-10-23
- [ ] Ordre de réalisation et stories sacrifiables validés

## Sprint Planning Part 2 — Comment

- [ ] Décomposition en tâches (`/project:decompose-tasks 007`), estimations de 0,5 h à 8 h
- [ ] Capacité confirmée après décomposition (mise à jour de `sprint-status.yaml` à faire par l'utilisateur ou sur demande)
- [ ] Tâches dépendant d'une action externe identifiées et marquées
- [ ] Graphe de dépendances et Task Board initialisés (`task-board.md`)

## Critères de fin de sprint

- [ ] Deux plugins publiés (après GO) avec CI verte, revue de sécurité sans constat critique ou majeur, intégrés à la démo
- [ ] punchout-cxml et container-fill corrigés (publiés après GO) ; contournement démo retiré
- [ ] Webhooks de démo et guide MCP livrés ; ADR du spike worker
- [ ] M2, M4, M5, M6, M8 clos ou acceptés par écrit ; S2-R3 à S2-R10 et S4-R3 traités ou actés
- [ ] Bandeau de prochaine remise à zéro en place ; coût Railway relevé (ou report daté)
- [ ] ECS, PHPStan, tests et CI verts ; aucun secret réel

## Ressources

| Ressource | Emplacement |
|-----------|-------------|
| Constats sprint 2 | `project-management/sprints/sprint-2-donnees-b2b/review-findings.md` |
| Constats sprint 3 | `project-management/sprints/sprint-3-nouveaux-plugins/review-findings.md` |
| Constats sprint 4 | `project-management/sprints/sprint-4-theme-b2b/review-findings.md` |
| Constats sprint 5 | `project-management/sprints/sprint-5-listes-et-scenarios/review-findings.md` |
| Constats sprint 6 | `project-management/sprints/sprint-6-railway-securite/review-findings.md` |
| Backlog | `project-management/backlog/user-stories/` |
| Statut des sprints | `.bmad/sprint-status.yaml` (non modifié ici) |
