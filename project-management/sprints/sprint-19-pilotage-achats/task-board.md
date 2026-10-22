# Task Board : Sprint 019 (pilotage des achats)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire (action de Thibaut) | ⚠️ Sacrifiable

Total : 83 tâches, 89,25 h (1,75 h d'actions de Thibaut). État à la décomposition du 2026-10-21 : 79 🔲, 4 🚫, aucune commencée. Ordre de sacrifice : US-121, US-099, US-146 (voir [tasks/README.md](./tasks/README.md)).

**Ajustement du 2026-10-21 (capacité 75,8 h)** : US-121 (12 tâches, 10,5 h) et US-099 (7 tâches, 6,5 h) sont sacrifiées et reportées au sprint 20. Leurs tâches `T-121-*` et `T-099-*` restent listées ci-dessous pour mémoire, **hors périmètre**. Périmètre engagé : 64 tâches, 72,25 h.

**Clôture du 2026-10-22** : les 5 stories engagées (US-149, US-150, US-151, US-155, US-146) sont livrées en review (PR #47). Le détail par tâche des sections ci-dessous reflète l'état à la décomposition ; restent ouvertes T-155-12, T-155-13 (captures) et T-155-14 (relecture de Thibaut). Voir [sprint-review.md](./sprint-review.md).

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-149-01 | US-149 | DOC | Cadrage : lire `composer.json` et la matrice CI de `company-accounts` et d'`agentic-feeds`, rubrique « versions supportées (PHP, Symfony, Sylius) et impact de version » du README ; relever le menu du compte entreprise, `CurrentMemberResolver`, `CompanyOrder` et le marqueur des commandes en attente d'approbation ; décider de la matrice du nouveau dépôt | 1 h | Claude |
| T-149-02 | US-149 | OPS | Squelette local du dépôt (`composer.json`, ECS, PHPStan max, Rector, PHPUnit unit et non-unit, application de test, `compose.yml`, `build.yaml` de la matrice retenue, Dependabot, licence MIT, README minimal) ; `composer validate --strict`, `lint:container` et amorçage dans Docker | 2 h | Claude |
| T-149-03 | US-149 | OPS | Thibaut donne le GO : création du dépôt public `sylius-company-insights-plugin` et premier push | 0,25 h | Thibaut |
| T-149-04 | US-149 | OPS | Création du dépôt GitHub (public, sujets `ecommerce`, `sylius`, `sylius-plugin`, `symfony`, `b2b`, `reporting`), push du squelette, branche `feature/walking-skeleton`, premier run de la matrice complète | 0,75 h | Claude |
| T-149-05 | US-149 | TEST | Tests d'intégration rouges (MySQL) des agrégats : dépense HT, nombre de commandes, panier moyen, acheteurs actifs, en attente (nombre et montant) ; période précédente de même durée ; commandes annulées exclues ; autre entreprise exclue ; entreprise sans commande | 1,5 h | Claude |
| T-149-06 | US-149 | BE | `PeriodResolver` (30 jours, trimestre en cours, 12 mois par défaut, année civile, période précédente), requêtes d'agrégation SQL, fournisseur d'indicateurs avec variation, port optionnel des commandes en attente | 3 h | Claude |
| T-149-07 | US-149 | DB | Migration des index (entreprise, date) après relevé de ceux de `company-accounts` (T-149-01) ; testée en montée et en descente | 0,75 h | Claude |
| T-149-08 | US-149 | TEST | Tests fonctionnels rouges : administratrice (200), acheteuse (refus expliqué, menu sans l'entrée), période en liste blanche rappelée dans le titre et l'URL, paramètre visant une autre entreprise ignoré, entreprise sans commande | 1,5 h | Claude |
| T-149-09 | US-149 | BE | Voter `COMPANY_VIEW_INSIGHTS` (administrateur du membre courant), contrôleur et route `/account/company/insights`, entrée du menu du compte entreprise, refus expliqué | 2 h | Claude |
| T-149-10 | US-149 | FE-WEB | Page « Tableau de bord des achats » : sélecteur de période, cinq indicateurs avec variation, bloc « En attente d'approbation » à part, message « Aucune commande sur la période » ; libellés FR et EN ; contrastes AAA ; mobile | 2 h | Claude |
| T-149-11 | US-149 | DOC | README EN et FR (installation, permission, matrice et versions supportées, captures), ADR du choix d'apexcharts sans CDN, `CHANGELOG.md` | 1,25 h | Claude |
| T-149-12 | US-149 | TEST | ECS, PHPStan max, Rector, couverture ≥ 80 %, `lint:container`, validation Doctrine ; rendu réel FR et EN dans l'application de test et pa11y WCAG2AAA sur la page ; matrice CI de la PR verte (run terminé) | 1,5 h | Claude |
| T-149-13 | US-149 | REV | Revue de sécurité (voter, cloisonnement entre entreprises sur chaque requête, paramètres en liste blanche) et de la matrice CI | 0,75 h | Claude |
| T-150-01 | US-150 | TEST | Tests d'intégration rouges de la requête mensuelle : 12 barres plus l'année précédente, mois sans commande à zéro, commande du 31 mars à 23 h 30 en mars (fuseau du canal), deux devises sans total mélangé, mois en cours marqué | 1,5 h | Claude |
| T-150-02 | US-150 | BE | Requête mensuelle (regroupement par mois dans le fuseau du canal et par devise), constructeur de série qui comble les mois vides, bascule montant / nombre | 2,5 h | Claude |
| T-150-03 | US-150 | TEST | Tests fonctionnels rouges : somme des 12 barres égale à la dépense de l'indicateur « 12 derniers mois », bascule vers le nombre de commandes, tableau équivalent avec en-têtes (`th scope`), deux séries pour deux devises | 1 h | Claude |
| T-150-04 | US-150 | FE-WEB | Graphique en barres apexcharts embarqué (sans CDN), tableau de données équivalent, motifs sans couleur seule, mois en cours marqué, bascule (Stimulus), libellés FR et EN | 3 h | Claude |
| T-150-05 | US-150 | TEST | Rendu réel mobile et ordinateur en FR et EN, pa11y WCAG2AAA, contrôle qu'aucune ressource externe n'est chargée ; ECS, PHPStan max, Rector, couverture | 1 h | Claude |
| T-150-06 | US-150 | DOC | README (graphique, fuseau, devises, accessibilité) et `CHANGELOG.md` | 0,5 h | Claude |
| T-150-07 | US-150 | REV | Revue (somme mensuelle égale à l'indicateur, aucun mois manquant, aucune ressource externe, matrice CI verte) | 0,5 h | Claude |
| T-151-01 | US-151 | TEST | Tests d'intégration rouges des trois requêtes : top acheteurs (parts 62 % et 38 %, membre désactivé), top produits (tri dépense et quantité, dernière date), top catégories (somme 100 %), autre entreprise exclue, produit sans traduction affiché par son code | 2 h | Claude |
| T-151-02 | US-151 | BE | Trois requêtes d'agrégation SQL, objets de lecture, limite de 10 lignes et pages « Voir tout » paginées (tri et page en liste blanche) | 2,75 h | Claude |
| T-151-03 | US-151 | TEST | Tests fonctionnels rouges : classements sur le tableau de bord, pages « Voir tout », tri par quantité, mention « désactivé », aucun e-mail dans le HTML, aucun achat d'une autre entreprise | 1 h | Claude |
| T-151-04 | US-151 | FE-WEB | Blocs des trois classements, pages « Voir tout », anneau des catégories avec tableau équivalent, mention « désactivé », libellés FR et EN, contrastes AAA, mobile | 2,5 h | Claude |
| T-151-05 | US-151 | TEST | Rendu réel FR et EN, pa11y WCAG2AAA ; ECS, PHPStan max, Rector, couverture | 0,75 h | Claude |
| T-151-06 | US-151 | DOC | README (classements, données personnelles) et `CHANGELOG.md` | 0,5 h | Claude |
| T-151-07 | US-151 | REV | Revue de sécurité (cloisonnement, aucune donnée personnelle au-delà du nom, paramètres en liste blanche) et matrice CI verte | 0,75 h | Claude |
| T-155-01 | US-155 | DOC | `docs/vitrine-plugins.md` : ligne 25 `company-insights` (tableau avec fonction, écrans, point de contact, classe **hors périmètre (à confirmer)**, sources S1 et S2 dont l'« historique » du compte maître), totaux mis à jour, ton neutre ; vérification de la documentation officielle consignée avant toute communication | 0,75 h | Claude |
| T-155-02 | US-155 | OPS | Mesure de référence de la durée de `app:demo:reset` (trois runs, `bin/profile-reset`) avant toute modification du plan | 0,5 h | Claude |
| T-155-03 | US-155 | TEST | Tests rouges du plan étendu : environ 45 commandes Acme sur 18 mois avec creux en août, 4 acheteurs dont un désactivé, 2 sites, 2 centres de coût, 10 produits récurrents, commandes de TechParts et BuildCo, mêmes commandes et dates relatives après deux remises à zéro, 8 commandes existantes inchangées | 1,5 h | Claude |
| T-155-04 | US-155 | BE | Extension de `DemoOrderPlan` et de la fabrique déterministe (saisonnalité, acheteurs, sites, centres de coût, produits), scénarios existants conservés à l'identique | 2,75 h | Claude |
| T-155-05 | US-155 | DOC | Préparation de la 0.1.0 du plugin : `CHANGELOG.md`, numéro de version, matrice CI de `main` verte (run terminé), README à jour | 0,5 h | Claude |
| T-155-06 | US-155 | OPS | Thibaut donne le GO du tag et de la release `v0.1.0` du plugin | 0,25 h | Thibaut |
| T-155-07 | US-155 | OPS | Tag `v0.1.0` et release GitHub ; URL du dépôt remise à Thibaut avec le script de soumission (OK ou FAIL par élément) | 0,5 h | Claude |
| T-155-08 | US-155 | OPS | Thibaut soumet le plugin à Packagist (nouveau paquet) et confirme l'indexation de la 0.1.0 | 0,25 h | Thibaut |
| T-155-09 | US-155 | BE | Démo : `php -d memory_limit=1G composer require tmonier/sylius-company-insights-plugin:^0.1`, bundle, routes, configuration, migration et menu ; `composer.lock` écrit seul | 1,25 h | Claude |
| T-155-10 | US-155 | FE-WEB | Scénario « Isabelle consulte le tableau de bord des achats » dans `config/demo/plugins.yaml` (FR et EN, compte conseillé `isabelle`) et badge du plugin | 0,5 h | Claude |
| T-155-11 | US-155 | TEST | Tests de la démo : 12 mois avec creux en août et comparaison, au moins 4 acheteurs et 10 produits dans les tops, TechParts absent des tops d'Acme, parcours de Claire, Paul et Isabelle inchangés, durée de remise à zéro ≤ +20 % ; suite complète via `bin/run-tests`, ECS (`src tests`), PHPStan | 2 h | Claude |
| T-155-12 | US-155 | OPS | Captures de la page Plugins et du tableau de bord (démo locale remise à zéro, données fictives, sans marque tierce) pour la page Plugins et le README | 1 h | Claude |
| T-155-13 | US-155 | DOC | Documentation de la démo (`docs/remise-a-zero-demo.md`, `docs/vitrine-plugins.md`) et captures dans le README du plugin | 0,5 h | Claude |
| T-155-14 | US-155 | REV | Thibaut relit la documentation technique de Sylius Plus (S9) s'il y a accès et confirme ou corrige la classe de company-insights (non bloquant) | 0,25 h | Thibaut |
| T-155-15 | US-155 | REV | Revue (déterminisme, cloisonnement visible, durée de remise à zéro, ton neutre, aucun secret, `composer.lock`) | 0,75 h | Claude |
| T-146-01 | US-146 | DOC | Cadrage : `git fetch`, partir de `main`, branche vérifiée ; relire `composer.json` et la matrice CI (PHP 8.2, Symfony 6.4, Sylius 2.2) et rédiger la rubrique « versions supportées et impact de version » ; valider la correction de la fiche (carnet déjà livré) et le lieu de l'adaptateur | 1 h | Claude |
| T-146-02 | US-146 | TEST | Tests rouges : unitaires de la chaîne de sources (ordre entreprise, carnet, saisie ; source absente ; identifiant d'une autre entreprise ou d'un autre client refusé) et fonctionnels des six scénarios (entreprise et carnet, carnet seul, aucune adresse, plugin company-accounts absent, adresse étrangère refusée avec message, copie non liée à la source) | 2,5 h | Claude |
| T-146-03 | US-146 | BE | Point d'extension (interface et étiquette de service), chaîne de sources, source « carnet du client » extraite du contrôleur, contrôle serveur de l'identifiant avec message d'erreur, copie de l'adresse dans la commande | 2 h | Claude |
| T-146-04 | US-146 | FE-WEB | Étape adresse : choix groupé (« Adresses de l'entreprise », « Carnet d'adresses », « Saisir une adresse ») dans un groupe étiqueté, préremplissage, formulaire de saisie inchangé ; libellés dans les sept langues, FR et EN d'abord | 2 h | Claude |
| T-146-05 | US-146 | TEST | company-accounts : tests rouges de l'adaptateur (adresses de l'entreprise du client, client sans entreprise, autre entreprise exclue) | 0,5 h | Claude |
| T-146-06 | US-146 | BE | company-accounts : adaptateur `src/Bridge/AdminOrderCreation/` (garde de présence du plugin comme le pont existant), `suggest` à jour | 1,25 h | Claude |
| T-146-07 | US-146 | DOC | README EN et FR (point d'extension, versions supportées) et `CHANGELOG.md` (à créer) d'admin-order-creation ; CHANGELOG de company-accounts | 0,75 h | Claude |
| T-146-08 | US-146 | TEST | Dans chaque dépôt : ECS, PHPStan max, Rector et tests dans Docker (PHP 8.4, Sylius 2.3) ; vérification locale des cellules PHP 8.2 et Symfony 6.4 par lecture du code | 0,75 h | Claude |
| T-146-09 | US-146 | OPS | Thibaut donne le GO unique : push des deux branches, deux PR, merge quand les CI sont vertes, tags v1.1.0 (admin-order-creation) et v1.3.0 (company-accounts) | 0,25 h | Thibaut |
| T-146-10 | US-146 | OPS | Deux PR, suivi des matrices CI, merges après run terminé, tags et releases dans l'ordre ; vérification Packagist (script du scratchpad à OK ou FAIL) | 1 h | Claude |
| T-146-11 | US-146 | BE | Démo : montée des versions (`composer update` ciblé avec `php -d memory_limit=1G`), `composer.lock` écrit seul | 0,5 h | Claude |
| T-146-12 | US-146 | TEST | Scénario « Sophie crée une commande pour Claire d'ACME et choisit une adresse de l'entreprise » dans `config/demo/plugins.yaml` ; rendu réel dans l'admin de la démo en FR et EN, accessibilité du choix ; suite via `bin/run-tests`, ECS, PHPStan | 1 h | Claude |
| T-146-13 | US-146 | REV | Revue (aucune dépendance obligatoire vers company-accounts, refus d'une adresse étrangère, copie non liée, matrices vertes) | 0,5 h | Claude |
| T-099-01 | US-099 | DOC | Fiche complétée d'une erreur (URL de retour hors domaines autorisés) et d'une aide au clavier ; plan des quatre étapes numérotées, glossaire (cXML, OCI, OrderRequest, HOOK_URL) en une phrase chacun, schéma du flux — correction proposée le 2026-10-21, à valider au planning | 0,75 h | Claude |
| T-099-02 | US-099 | TEST | Tests rouges : quatre étapes numérotées (liste ordonnée) et retour de panier lisible, aide lisible au survol et au focus clavier, parité des clés FR et EN, session expirée avec lien pour relancer, URL de retour refusée avec explication | 1 h | Claude |
| T-099-03 | US-099 | BE | Étape courante calculée par le contrôleur (lancement, boutique, panier reçu, commande), message de session expirée avec action « relancer », explication du refus de domaine avec les domaines autorisés | 0,75 h | Claude |
| T-099-04 | US-099 | FE-WEB | Habillage du portail fictif (sans marque réelle), étapes numérotées, aides du jargon, schéma du flux en SVG avec alternative textuelle, retour de panier lisible, protocoles cXML et OCI ; libellés FR et EN (`simulator.*.yaml`) ; contrastes AAA | 2 h | Claude |
| T-099-05 | US-099 | TEST | Rendu réel FR et EN (cXML et OCI), pa11y WCAG2AAA, `bin/measure-mobile`, suite via `bin/run-tests`, ECS (`src tests`) et PHPStan | 1 h | Claude |
| T-099-06 | US-099 | DOC | Documentation à jour (guide des parcours, page du simulateur) et mention du simulateur habillé dans le kit média | 0,5 h | Claude |
| T-099-07 | US-099 | REV | Revue (aucune marque réelle comme logo, ton de la charte, parité FR et EN) | 0,5 h | Claude |
| T-121-01 | US-121 | DOC | Fiche complétée à 1 + 2 + 2 scénarios (correction proposée) ; plan des trois séquences et prérequis d'environnement (démo remise à zéro, comptes, T-103-03, US-099, retour HTTPS d'US-120) | 0,75 h | Claude |
| T-121-02 | US-121 | OPS | Préparer la démo locale : remise à zéro propre, comptes, fenêtre de captation ; contrôle d'absence de donnée sensible, d'URL privée et de marque tierce | 0,75 h | Claude |
| T-121-03 | US-121 | OPS | Captation de la séquence approbation (Claire dépasse sa limite d'achat, Paul approuve) | 1,25 h | Claude |
| T-121-04 | US-121 | OPS | Captation de la séquence container-fill (jauge de la palette ou du camion) | 1,25 h | Claude |
| T-121-06 | US-121 | DOC | Script minuté de la visite complète de 3 minutes (voix off, étapes, 180 s) : segments approbation, container-fill, PunchOut, devis et commande rapide | 1,25 h | Claude |
| T-121-08 | US-121 | OPS | Export des séquences approbation et container-fill : sous-titres FR incrustés, version carrée 1080 x 1080, MP4 et GIF de moins de 10 Mo (`export.sh`) | 1 h | Claude |
| T-121-10 | US-121 | DOC | Textes alternatifs, descriptions et fiche du kit (`kit-media/README.md`), registre `communication/INDEX.md` à jour | 0,75 h | Claude |
| T-121-11 | US-121 | TEST | Contrôle de durée, poids et dimensions (`ffprobe`, `check.sh`) et relecture d'images extraites de chaque séquence (données sensibles, marques, lisibilité sur mobile) | 0,75 h | Claude |
| T-TECH-01 | Transverse | TEST | Tests rouges de `bin/check-disk` pour la VM Docker : espace libre de la VM mesuré, alerte, refus de `bin/run-tests` sous 3 Go dans la VM (action 1 de la rétro S18) | 0,75 h | Claude |
| T-TECH-02 | Transverse | OPS | `bin/check-disk` mesure aussi le disque de la VM Docker ; `bin/run-tests` refuse de démarrer sous 3 Go dans la VM, le gel à 5 Go de la machine restant inchangé (worker devops) | 1,25 h | Claude |
| T-TECH-03 | Transverse | DOC | Rubrique « versions supportées (PHP, Symfony, Sylius) et impact de version » et « lire la matrice CI et le `composer.json` de chaque plugin touché au cadrage » dans `project-management/templates/user-story.md` et dans le gabarit de consigne de worker (action 2) | 0,75 h | Claude |
| T-TECH-04 | Transverse | OPS | ECS et PHPStan rejoués sur l'arbre exact avant chaque push (démo et dépôts de plugins, dans Docker) ; la dernière ligne de sortie citée dans le rapport (action 3) | 0,5 h | Orchestrateur |
| T-TECH-05 | Transverse | TEST | Test garde rouge : aucun faux jeton au format réel (préfixes connus de clés d'API et de jetons) dans les `tests/` de la démo ; l'échec nomme le fichier (action 4) | 0,75 h | Claude |
| T-TECH-06 | Transverse | DOC | Règle « secrets fictifs recomposés à l'exécution, jamais au format réel écrit en clair » dans `docs/tests-locaux.md` et dans le gabarit de consigne de worker | 0,5 h | Claude |
| T-TECH-07 | Transverse | OPS | Scripts du scratchpad pour les commandes longues de Thibaut, à OK ou FAIL par élément : tag et soumission du plugin company-insights, tags des deux releases d'US-146, `composer` avec `php -d memory_limit=1G` (action 5) | 0,75 h | Orchestrateur |
| T-TECH-08 | Transverse | OPS | Reconstruction de l'image et démarrage du conteneur (`/healthz`) par l'orchestrateur avant la revue, puis après tout correctif touchant l'image | 0,75 h | Orchestrateur |
| T-TECH-09 | Transverse | DOC | Bilan des indicateurs de la rétro S18 (disque, matrices CI, ECS et PHPStan avant push, alertes de secrets, scripts de Thibaut) préparé pour la rétro du 2027-03-19 | 0,5 h | Orchestrateur |

## 🔄 En Cours

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|

## 👀 En Review

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|

## 🚫 Bloqué

Les quatre tâches ci-dessous dépendent de **T-103-03** (conversation réelle de Thibaut, sprint 18) ; elles se débloquent quand le transcript est enregistré et relu (T-103-07).

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-121-05 | US-121 | OPS | Captation de la séquence MCP : rejeu du parcours du transcript d'US-103 — *bloquée : dépend de T-103-03 (conversation réelle de Thibaut) et de T-103-07* | 1,25 h | Claude |
| T-121-07 | US-121 | DOC | Segment MCP du script (reprend le transcript d'US-103) et minutage final à 180 s — *bloquée : dépend de T-103-03 et de T-103-07* | 0,5 h | Claude |
| T-121-09 | US-121 | OPS | Export de la séquence MCP (mêmes formats) — *bloquée : dépend de T-121-05, donc de T-103-03* | 0,5 h | Claude |
| T-121-12 | US-121 | REV | Thibaut relit et valide les séquences et le script de la visite complète (sinon séquence refaite) — *bloquée : attend le segment et la séquence MCP* | 0,5 h | Thibaut |
