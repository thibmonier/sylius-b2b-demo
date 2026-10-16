# Reprise — sprint 5 (listes et scénarios), état au 2026-10-16

Document de reprise après nettoyage du contexte. Lancé par `/workflow:auto-sprint 005`, après l'audit inter-sprint (`project-management/audits/2026-10-16-inter-sprint/audit-report.md`, 60/100).

- Dépôt : `sylius-b2b-demo`, branche `feature/sprint-5-listes-et-scenarios`, **PR #5 en brouillon** (https://github.com/thibmonier/sylius-b2b-demo/pull/5).
- Dernier commit poussé : `0f46ac6` (US-027 partie 1). Statut CI : voir la section « CI » en bas (à revérifier : `gh pr view 5 --json statusCheckRollup`).
- Suivi : `.bmad/sprint-status.yaml` (bloc sprint-5, capacité 29 pts) ; tâches dans `tasks/`, Kanban dans `task-board.md`.

## Phases de l'auto-sprint

| Phase | État |
|-------|------|
| 0 Normalisation, branche | ✅ |
| 1-2 Démarrage, décomposition | ✅ 46 tâches, 76,5 h ; US-025 ré-estimée 5→8, US-028 2→3 |
| 3 Porte | ✅ PASS 84/100 + remédiation documentaire |
| 4 Implémentation | 🔄 voir tableau des stories |
| 5 PR | ✅ brouillon #5 |
| 6 CI | 🔄 à confirmer après le dernier push |
| 7 Revue, 8 Rétro, 9 Merge | ⏳ (merge seulement sur GO de l'utilisateur) |

## Stories

| Story | État | Reste à faire |
|-------|------|---------------|
| US-011 Historique de commandes et factures | review (`b4455ca`) | Doc T-011-06 ; BuildCo et IndusPro sans commande (couverture « entreprise sans commande » partielle) ; facture de la commande approuvée datée du jour (plugin purchase-approval utilise `new DateTimeImmutable`) |
| US-024 Listes d'achat partagées | review (`1f6990f`) | Rien de bloquant (vérifié en conteneur : Claire 200, Marc/TechParts 403, anonyme → login) |
| US-025 Carnet d'adresses (company-accounts v1.2) | in-progress | **Plugin publié v1.2.0** (Packagist OK). Reste **l'intégration dans la démo** : `composer require tmonier/sylius-company-accounts-plugin:^1.2`, migration du plugin, fixture d'adresses Acme (« Usine de Lyon »…) via les services réels du plugin, smoke test (Isabelle crée, Claire choisit « Livrer ici » au checkout, Claire ne peut pas modifier) — tâches T-025-10 à T-025-12 |
| US-027 Simulateur d'outil d'achat | in-progress (`0f46ac6`, partie 1) | (1) ajouter l'**hôte public de la démo** aux `returnDomains` du buyer PunchOut via une variable d'environnement (`PunchoutDemoFixture`, HTTPS exigé ; sans cela la session est refusée avec un message qui nomme l'hôte) ; (2) valider la **création de commande de bout en bout** par OrderRequest (stock/prix) ; (3) parcours OCI non simulé (aucun buyer OCI en fixture) — à décider : ajouter ou documenter |
| US-028 Lecture filtrée des e-mails | review (`fb17461`) | Doc T-028-06 |
| Tâche technique (audit action 1) | fait (`4565c6c`) | Vérifier dans la CI : résumé de couverture et `doctrine:schema:validate --skip-sync` |

## Décisions de l'utilisateur (2026-10-16)

- US-019/US-020 : plugins autonomes publiés, **au sprint 7**, avec les correctifs des plugins punchout-cxml (cibler les interfaces Doctrine, événement à la création de commande, guard de workflow `complete`) et container-fill.
- US-025 faite au sprint 5 (publication = accord explicite ; obtenu et faite).
- US-024 : POC GO → wishlist officielle étendue (ADR-0012 acceptée), télémétrie retirée par compiler pass.
- US-028 : Mailpit **jamais exposé** ; lecture côté serveur (`DEMO_MAILPIT_API_URL`, interne) en excluant les e-mails sensibles.
- `DEMO_MODE` vaut 0 par défaut (=1 dans compose.yaml, phpunit.xml.dist, CI) ; à définir sur Railway.
- Ne pas ouvrir la démo au public avant le sprint 6 (admin de démo à accès complet, données personnelles).

## Constat à reporter dans la revue

- **Le formulaire « mot de passe oublié » du shop répond 200 sans envoyer d'e-mail** (vérifié en conteneur : aucun message dans Mailpit, aucune erreur loguée, `SendEmailMessage` non routé en asynchrone). À diagnostiquer (hors périmètre des stories du sprint 5).

## Pièges connus (à relire avant de reprendre)

- **Disque presque plein** (~4 Go libres sur le volume Data) : limiter les `docker compose up --build` ; après un rebuild, supprimer les couches orphelines du projet. Ne pas toucher aux images des autres projets sans accord.
- RTK réécrit `curl` et `du` : utiliser `rtk proxy curl`, `rtk proxy du`.
- Fixtures : `SyliusFixturesBundle` trie les priorités en `SORT_DESC` → fixtures custom en priorités **négatives** (taxons -10, entreprises -15, attributs -20, produits -30, plugins -40/-50, commandes -60). Injecter `EntityManagerInterface`.
- Formulaires Sylius : les contraintes doivent être dans les groupes `sylius` / `sylius_checkout_complete` (le groupe `Default` est ignoré).
- Twig hooks : les templates de hookable lisent `hookable_metadata.configuration`, pas `hookable_configuration`.
- Vérifier le **rendu réel** (curl en conteneur, connexion rejouée avec son jeton CSRF) : plusieurs bugs n'étaient visibles qu'ainsi (catégories vides, badges jamais rendus, mailbox).
- Workers : exiger `git add` fichier par fichier, la sortie collée de phpstan/ecs, et des tests sur le comportement rendu, pas sur la configuration.
- Base de test locale : les smoke tests exigent `sylius_b2b_demo_test` ; les workers ont utilisé des bases jetables sur le MySQL du compose (127.0.0.1:3340). `phpunit` a besoin de `-d memory_limit=3G`.

## CI

- Run du commit `0f46ac6` : **vert à tort**. PHPUnit affichait 285 tests, 2 échecs, mais `phpunit | tee` renvoyait le code de sortie de `tee`. Correction : `shell: bash` (donc `-eo pipefail`) sur l'étape PHPUnit.
- Les 2 échecs (`OrderHistorySmokeTest`) venaient des paniers laissés par les autres smoke tests (PunchOut, liste vers panier) : `findAll()` les comptait. Le test ne compte plus que les commandes passées (`state != cart`). La correction est poussée et **la CI est à revérifier**.
- Couverture mesurée : Classes 40,70 %, Methods 55,91 %, **Lines 53,51 %** (cible 80 %, non bloquante pour l'instant).

## Reprise conseillée

1. `git fetch && git switch feature/sprint-5-listes-et-scenarios && git pull` ; vérifier la CI de la PR #5.
2. Terminer US-025 (intégration v1.2) puis US-027 (domaine de retour + commande de bout en bout).
3. Docs T-011-06 et T-028-06.
4. Phase 7 : revue indépendante (Symfony + sécurité) ; phase 8 : rétro ; pause pour le GO de merge.
