# Reprise — sprint 5 (listes et scénarios), état au 2026-10-16

Document de reprise après nettoyage du contexte. Lancé par `/workflow:auto-sprint 005`, après l'audit inter-sprint (`project-management/audits/2026-10-16-inter-sprint/audit-report.md`, 60/100).

- Dépôt : `sylius-b2b-demo`, branche `feature/sprint-5-listes-et-scenarios`, **PR #5 en brouillon** (https://github.com/thibmonier/sylius-b2b-demo/pull/5).
- Dernier commit poussé : `0beb10e` (correctif CI), après `0f46ac6` (US-027 partie 1). Statut CI : voir la section « CI » en bas (à revérifier : `gh pr view 5 --json statusCheckRollup`).
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
| 7 Revue | ✅ GO (`review-findings.md`) |
| 8 Rétro | ✅ (`retrospective.md`) |
| 9 Merge | ⏳ seulement sur GO de l'utilisateur |

## Stories

| Story | État | Reste à faire |
|-------|------|---------------|
| US-011 Historique de commandes et factures | review (`b4455ca`) | Doc T-011-06 ; BuildCo et IndusPro sans commande (couverture « entreprise sans commande » partielle) ; facture de la commande approuvée datée du jour (plugin purchase-approval utilise `new DateTimeImmutable`) |
| US-024 Listes d'achat partagées | review (`1f6990f`) | Rien de bloquant (vérifié en conteneur : Claire 200, Marc/TechParts 403, anonyme → login) |
| US-025 Carnet d'adresses (company-accounts v1.2) | review (`1575e4f`) | Rien : démo sur v1.2.0, fixture `demo_company_addresses` (3 adresses Acme), smoke test (Claire « Livrer ici » → formulaire pré-rempli ; Claire 403 en écriture ; Isabelle gère ; Marc ne voit rien) |
| US-027 Simulateur d'outil d'achat | review (`83dd39f`) | Rien : hôte HTTPS de `DEFAULT_URI` ajouté aux `returnDomains` ; commande de bout en bout testée. **Défaut punchout-cxml trouvé** : `PunchoutCartFactory` crée des commandes « invité » → paiement sur compte refusé (500). Contournement démo `App\PunchOut\AuthorizedPunchOutOrderFactory` (décision utilisateur), à retirer au sprint 7 avec le correctif du plugin. OCI non simulé : documenté (décision utilisateur), T-027-02 reporté |
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
- Les 2 échecs (`OrderHistorySmokeTest`) venaient des paniers laissés par les autres smoke tests (PunchOut, liste vers panier) : `findAll()` les comptait. Le test ne compte plus que les commandes passées (`state != cart`). Corrigé dans `0beb10e` : **CI verte** (285 tests, 0 échec, 1 ignoré ; 3 checks OK).
- Couverture mesurée : Classes 40,70 %, Methods 55,91 %, **Lines 53,51 %** (cible 80 %, non bloquante pour l'instant).

## Mise à jour du 2026-10-16 (après-midi)

- Toutes les stories en review ; docs T-011-06, T-027-07, T-028-06 dans le README (`c2df2d0`).
- Tests locaux : suite complète 298 tests ; seuls échecs locaux attendus = absence de `wkhtmltopdf` sur le Mac (contourné par `WKHTMLTOPDF_BINARY=<faux binaire>`, qui fait alors échouer l'assertion Factur-X d'`OrderHistorySmokeTest`) et cache `prod` périmé (`APP_ENV=prod bin/console cache:clear`).
- La base de test `sylius_b2b_demo_test` avait disparu : recréée (create, migrate, fixtures).
- Constats pour la revue : `doctrine:schema:validate` sans `--skip-sync` signale 4 écarts préexistants (tables Sylius refund/payment) ; le README annonçait Marc approbateur chez BuildCo alors que `accounts.yaml` ne l'y met pas ; `demo_mailpit_url()` (Twig) n'est utilisée par aucun template.

## Phase 7 — revue (2026-10-16)

- Revue Symfony : corrigés dans `a1a5cd5` (boîte de démo : appels Mailpit en parallèle, page bornée à ~4 s ; garde du dossier de factures vide). Écartés : « BuyerCredentialsProvider absent » (faux, fichier présent dans HEAD) ; rejeu de l'état signé avec le même n° (dédupliqué par le plugin).
- Sécurité : GO pour le merge (démo non publique). **À traiter au sprint 6, avant l'ouverture publique** :
  1. Simulateur PunchOut public = commandes Acme anonymes à volonté (état signé rejouable 1 h avec un autre n° de commande ; le secret de démo permet de toute façon de poster sur `/punchout/cxml`). Accès restreint à `/demo/*` ou nonce à usage unique + rate limiter par IP, et reset régulier d'Acme (crédit, commandes).
  2. `framework.trusted_hosts` absent et `TRUSTED_PROXIES` = `REMOTE_ADDR` : restreindre aux plages du proxy Railway.
  3. Le rate limiter de setup du plugin voit toutes les requêtes du simulateur en 127.0.0.1 : un visiteur peut bloquer les autres (429). Limiter par IP visiteur côté simulateur.
  4. Boîte de démo : affiche tous les e-mails quel que soit le destinataire (RGPD si inscription/commande avec une vraie adresse) → n'afficher que les destinataires des comptes de démo (liste blanche).

## Reprise conseillée

1. `git fetch && git switch feature/sprint-5-listes-et-scenarios && git pull` ; vérifier la CI de la PR #5.
2. Phase 7 : revue indépendante (Symfony + sécurité) lancée, traiter les constats ; phase 8 : rétro ; pause pour le GO de merge.
