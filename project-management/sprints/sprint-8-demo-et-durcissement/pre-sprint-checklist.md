# Checklist de démarrage — Sprint 8

## Prérequis (sprint précédent)

- [ ] Sprint 7 livré ou clôturé (revue, rétro faite : US-042 et US-043 en sont issues)
- [ ] Branche `feature/sprint-8-demo-et-durcissement` active (`git fetch` + vérification de la branche courante avant tout commit ; aucun commit sans demande)
- [ ] CI verte sur `main` et sur la branche
- [ ] `doctrine:migrations:status` : aucune migration en attente
- [ ] Intégration d'US-019 dans la démo faite (T-019-16), requise par T-040-12
- [ ] État d'US-020 connu (choix de T-040-02)

## Environnement technique

- [ ] Disque local presque plein (~3 Go libres, l'utilisateur libère de la place) : rien d'installé, pas de rebuild d'images
- [ ] Pas de wkhtmltopdf sur le Mac local (tests concernés en CI)
- [ ] `DEMO_MODE` vaut 0 par défaut

## Plugins (US-042, US-043)

- [ ] `sylius-channel-access-plugin` 0.1.0 : `Configuration.php`, `TmonierSyliusChannelAccessExtension.php` (constantes `RESTRICTED_ROUTES` / `RESTRICTED_PATHS`)
- [ ] Démo : `src/DependencyInjection/Compiler/RestrictPriceRoutesPass.php` repérée (routes devis, commande rapide, MCP, prix dégressifs)
- [ ] `sylius-b2b-mcp-plugin` : `McpEndpoint`, écran de consentement OAuth, authentification `/_mcp`
- [ ] Démo : `src/Demo/Mcp/McpApprovalGuard.php` repéré (priorité 6, `VisitorApprovalInterface`)
- [ ] Point d'extension de b2b-mcp sans dépendance dure à registration-approval
- [ ] Docker PHP 8.4 pour les tests des plugins (images déjà présentes)

## Actions soumises au GO de l'utilisateur

- [ ] GO publication de channel-access 0.2.0 (US-042)
- [ ] GO publication d'une version mineure de b2b-mcp (US-043)
- [ ] GO ouverture publique de la démo (distinct, hors périmètre)
- [ ] Rappel : pas de railway, gh, push, `composer require` sans accord explicite
- [ ] Accès au compte Railway pour le relevé partiel du coût (à date + projection) ; relevé complet à J+7 (T-041-15) au sprint 9

## Analyse préalable (US-030, US-040, US-041)

- [ ] `sprints/sprint-6-railway-securite/review-findings.md` relu (M2, M4, M5, M6, M8)
- [ ] `DemoResetCommand` et purge de `public/media/image` (M4)
- [ ] `http_client.transport` et clients scopés du serveur MCP (M5)
- [ ] `CsvProductReader` et fixtures (S2-R3 à S2-R6 ; S2-R7 à R10 et prix « dès » S4-R3 au sprint 9)
- [ ] Bandeau de démo (T-033-05), reset 03:00 Europe/Paris, T-034-02
- [ ] Fichiers `tasks/US-030`, `US-040`, `US-041` relus (non modifiés)

## Stories prêtes pour le développement

- [ ] US-030, US-040, US-041, US-042, US-043 au statut `ready-for-dev` (US-006 et US-029 au sprint 9)
- [ ] US-042 et US-043 relues (Gherkin 1 nominal, 2 alternatifs, 2 erreurs ; 5 points chacune)
- [ ] Gherkin d'US-030 complété (fait)
- [ ] Dépendances vérifiées (US-042 et US-043 après US-020 ; retrait des contournements après publication)

## Sprint Planning Part 1 — Quoi

- [ ] Sprint Goal validé (une phrase)
- [ ] 5 stories sélectionnées, 25 points, 90,5 h (arbitrage du 2026-10-16)
- [ ] Jalons compris : deux GO de publication, relevé Railway partiel
- [ ] Ordre de réalisation et tâches sacrifiables (T-041-10/11, puis US-030) validés

## Sprint Planning Part 2 — Comment

- [ ] Décomposition d'US-042 et US-043 faite, tâches de 0,5 h à 8 h
- [ ] Capacité confirmée après décomposition (`sprint-status.yaml` mis à jour par l'utilisateur ou sur demande)
- [ ] Tâches dépendant d'une action externe identifiées
- [ ] Graphe de dépendances et `task-board.md` initialisés

## Critères de fin de sprint

- [ ] Guide MCP livré (webhooks de démo et spike worker au sprint 9)
- [ ] M2, M4, M5, M6, M8 clos ou acceptés par écrit ; S2-R3 à S2-R6 traités (S2-R7 à R10 et S4-R3 au sprint 9)
- [ ] Bandeau de prochaine remise à zéro en place ; relevé partiel du coût Railway et projection livrés, relevé complet à J+7 au sprint 9
- [ ] channel-access et b2b-mcp améliorés (publiés après GO) ; `RestrictPriceRoutesPass` et `McpApprovalGuard` retirés, ou conservés faute de GO
- [ ] ECS, PHPStan, tests et CI verts ; aucun secret réel

## Ressources

| Ressource | Emplacement |
|-----------|-------------|
| Constats sprint 6 | `project-management/sprints/sprint-6-railway-securite/review-findings.md` |
| Plan du sprint 7 | `project-management/sprints/sprint-7-plugins-et-integrations/sprint-goal.md` |
| Tâches du sprint 8 | `project-management/sprints/sprint-8-demo-et-durcissement/tasks/README.md` |
| Backlog | `project-management/backlog/user-stories/` |
| Statut des sprints | `.bmad/sprint-status.yaml` (non modifié) |
