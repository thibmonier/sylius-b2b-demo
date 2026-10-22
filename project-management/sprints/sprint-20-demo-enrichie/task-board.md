# Task Board : Sprint 020 (démo enrichie)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire (action de Thibaut) | ⚠️ Sacrifiable

Total décomposé : 88 tâches, 87,5 h (1,5 h d'actions de Thibaut, 5 tâches). **Périmètre engagé après le sacrifice d'US-093 : 77 tâches, 71 h (1,5 h de Thibaut)**. État à la décomposition du 2026-10-22 : 77 🔲 engagées, aucune commencée. Périmètre validé par Thibaut le 2026-10-22 : 30 points, ramenés à 22 par le sacrifice d'US-093. Ordre de sacrifice restant : US-099, US-108, US-156, US-153 (voir [tasks/README.md](./tasks/README.md)).

**Clôture du 2026-10-22** : sur les 77 tâches du périmètre engagé, 67 ✅, 1 🚫 (T-TECH-06, vérification en production : Railway suspendu), 9 🔲 (T-113-06 captures d'accueil du README ; T-156-06 captures de purchase-approval ; T-TECH-02, 04, 05, 08, 09, 10, 11, faites en partie ou non faites, voir [sprint-review.md](./sprint-review.md)). Les 6 stories restent 👀 en review : vérification en production en attente (incident Railway).

**Capacité** : 71 h (30 points × 2,1 h + 8 h de tâches transverses) ; charge de Claude 86 h ; excès 15 h résorbé par le sacrifice d'US-093 (16,5 h) le 2026-10-22 : reste 69,5 h pour Claude, 22 points. Point d'étape le 2027-03-26.

## 🔲 À Faire

US-093 est sacrifiée le 2026-10-22 : ses 11 tâches sont listées en fin de fichier, hors périmètre. Les tâches de Thibaut (📅) sont marquées dans la colonne « Réalisé par ».

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-156-06 | US-156 | OPS | Captures de l'en-tête (ordinateur et mobile) pour le README (application de test, données fictives) ; relevé du disque avant ; à faire au plus tard le jour 5 | 0,5 h | Claude |
| T-113-06 | US-113 | DOC | Documentation à jour (`docs/theme-b2b.md`, README de la démo) et captures d'accueil pour le README | 0,5 h | Claude |
| T-TECH-02 | Transverse | OPS | Budget d'espace disque (action 1 de la rétro S19) : relevé `df -h .` et `bin/check-disk` au planning, `bin/clean-test-caches`, captures des plugins planifiées avant le jour 5, relevé noté dans le sprint-goal | 0,5 h | Orchestrateur |
| T-TECH-04 | Transverse | DOC | Gabarit de l'orchestrateur : gate recoupé avec la somme des points du fichier de statut (écart affiché), et « un refus d'outil n'arrête jamais le sprint » (action 3 de la rétro S19) | 0,5 h | Orchestrateur |
| T-TECH-05 | Transverse | OPS | Thibaut déploie d'un coup les PR #43 à #48 sur Railway à la reprise (échéance 2027-03-26, action 5 de la rétro S19) ; aucune commande Railway par Claude | 0,5 h | Thibaut |
| T-TECH-08 | Transverse | OPS | Gabarit de CI d'un nouveau plugin (action 4 de la rétro S19) : PHP 8.4 et 8.5, actions épinglées par SHA, `permissions` minimal ; documenté et vérifié sur company-insights avant la 0.2.0 (CI verte sur PHP 8.5 ; déjà présents au cadrage) ; écarts de purchase-approval consignés | 1 h | Claude |
| T-TECH-09 | Transverse | OPS | ECS et PHPStan rejoués sur l'arbre exact avant chaque push (démo et dépôts de plugins, dans Docker) ; dernière ligne de sortie citée dans le rapport | 0,5 h | Orchestrateur |
| T-TECH-10 | Transverse | OPS | Scripts du scratchpad pour les commandes de Thibaut, à OK ou FAIL par élément : tag v0.2.0 de company-insights, tag v1.2.0 de purchase-approval, vérification Packagist, `composer` avec `php -d memory_limit=1G` | 0,75 h | Orchestrateur |
| T-TECH-11 | Transverse | OPS | Reconstruction de l'image et démarrage du conteneur (`/healthz`) par l'orchestrateur avant la revue, puis après tout correctif touchant l'image (un seul build à la fois) | 0,75 h | Orchestrateur |

## 🔄 En Cours

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|

## 👀 En Review

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-152-01 | US-152 | DOC | Cadrage : `git fetch`, partir de `main` (v0.1.1), branche vérifiée ; matrice CI et `composer.json` relus, rubriques « versions supportées et impact de version » et « ordre de publication » rédigées ; contrat de `OrderDimensionProviderInterface` et du format d'export figés | 1 h | Claude |
| T-152-02 | US-152 | TEST | Tests d'intégration rouges de la ventilation : par site (adresse d'entreprise reconnue, sinon « Autres adresses »), par dimension (fournisseur de test, bloc absent sans fournisseur), filtres membre, site et période, cloisonnement « autre entreprise » | 1,5 h | Claude |
| T-152-03 | US-152 | BE | Point d'extension `OrderDimensionProviderInterface` (étiquette de service), requêtes de ventilation portables MySQL, MariaDB et PostgreSQL, filtres en liste blanche appliqués au tableau de bord, objets de lecture | 2 h | Claude |
| T-152-04 | US-152 | TEST | Tests rouges de l'export CSV : encodage et BOM, séparateur, colonnes, une ligne par ligne de commande, filtres et cloisonnement, neutralisation des formules (valeurs piégées recomposées à l'exécution), refus au-delà de 24 mois ou 50 000 lignes sans fichier, journal sans contenu | 1,5 h | Claude |
| T-152-05 | US-152 | BE | Exporteur CSV en flux, neutralisation des cellules commençant par `=`, `+`, `-` ou `@`, limites et message, journalisation (qui, quand, période), permission de l'export | 1,75 h | Claude |
| T-152-06 | US-152 | FE-WEB | Filtres (membre, site, période), bloc de ventilation avec tableau équivalent, bouton d'export, message de période trop longue ; libellés FR et EN ; contrastes AAA, mobile | 2 h | Claude |
| T-152-07 | US-152 | TEST | Dans Docker (PHP 8.4, Sylius 2.3) : ECS, PHPStan max, Rector, couverture ; rendu réel FR et EN et pa11y WCAG2AAA dans l'application de test ; lecture du code pour les cellules PHP 8.2, Symfony 6.4 et PostgreSQL | 1 h | Claude |
| T-152-08 | US-152 | OPS | Captures de la ventilation et de l'export pour le README (application de test du plugin, données fictives, sans marque tierce) ; relevé du disque avant (`bin/check-disk`) ; **à faire avant le jour 5** | 0,5 h | Claude |
| T-152-09 | US-152 | REV | Revue de sécurité (cloisonnement, injection de formule, limites, journal, permission), rapportée hors dépôt public, et revue de code | 0,75 h | Claude |
| T-152-10 | US-152 | DOC | Préparation de la 0.2.0 : README EN et FR (point d'extension, export, limites, versions supportées), `CHANGELOG.md`, captures, matrice CI de `main` verte (run terminé) ; attend US-153 (T-153-09, T-153-12) si elle est maintenue | 0,75 h | Claude |
| T-152-11 | US-152 | OPS | Thibaut donne le GO du tag et de la release `v0.2.0` de company-insights | 0,25 h | Thibaut |
| T-152-12 | US-152 | OPS | Push de la branche, PR, matrice CI complète verte (run terminé), merge, tag `v0.2.0` et release GitHub ; script de vérification Packagist remis à Thibaut | 0,75 h | Claude |
| T-152-13 | US-152 | OPS | Thibaut confirme que Packagist indexe la 0.2.0 (mise à jour du paquet déjà soumis) | 0,25 h | Thibaut |
| T-152-14 | US-152 | OPS | Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | Claude |
| T-152-15 | US-152 | BE | Démo : `php -d memory_limit=1G composer require tmonier/sylius-company-insights-plugin:^0.2`, migration éventuelle ; `composer.lock` écrit seul (jamais en parallèle d'une autre montée de version) | 0,5 h | Claude |
| T-152-16 | US-152 | BE | Démo : implémentation « Centre de coût » du fournisseur de dimension (`ATELIER-01`, `MAINT-02`) et enregistrement du service | 0,75 h | Claude |
| T-152-17 | US-152 | TEST | Tests de la démo : ventilation d'Acme (Atelier de Lyon, Dépôt de Grenoble), centres de coût, export CSV, TechParts absent des chiffres d'Acme ; scénario d'Isabelle dans `config/demo/plugins.yaml` et `docs/vitrine-plugins.md` ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-153-01 | US-153 | DOC | Cadrage : services publics de purchase-approval (compteur d'attente, `ApprovalPolicy`, historique) et de trade-credit (limite, encours, échu, prochaine échéance) lus ; écarts avec les indicateurs demandés consignés ; matrice des compagnons absents ou présents | 1,25 h | Claude |
| T-153-02 | US-153 | TEST | Tests rouges du registre de widgets : fournisseurs détectés à l'exécution, plugin compagnon absent donc widget absent sans erreur, permission requise par widget | 1 h | Claude |
| T-153-03 | US-153 | BE | Interface `InsightsWidgetProviderInterface`, registre par étiquette de service, rendu générique des widgets sur le tableau de bord | 1,5 h | Claude |
| T-153-04 | US-153 | TEST | Tests rouges des deux Bridges : approbations (attente de 4 jours, montant, plus ancienne en tête, délai médian, taux de refus, acheteurs souvent soumis : 4 commandes sur 5), encours (échu depuis 18 jours, utilisation du crédit, prochaine échéance, entreprise sans compte), cloisonnement, avec et sans compagnon, service en erreur (page 200, message, journal) | 2 h | Claude |
| T-153-05 | US-153 | BE | `Bridge/PurchaseApproval` et `Bridge/TradeCredit` : garde de présence à l'exécution, appels aux services publics, gestion d'erreur journalisée, `suggest` dans `composer.json` sans `require` | 2,25 h | Claude |
| T-153-06 | US-153 | FE-WEB | Widgets « Approbations » et « Encours » : jauge d'utilisation avec équivalent textuel, liens vers les pages des plugins compagnons, états vide (« Pas de compte de paiement à terme ») et d'erreur ; libellés FR et EN ; contrastes AAA, mobile | 1,75 h | Claude |
| T-153-07 | US-153 | TEST | Dans Docker : ECS, PHPStan max, Rector, couverture ; matrice locale avec et sans chaque compagnon ; rendu réel FR et EN et pa11y WCAG2AAA dans l'application de test | 1 h | Claude |
| T-153-08 | US-153 | OPS | Captures des deux widgets pour le README (application de test, données fictives, sans marque tierce) ; relevé du disque avant ; **à faire avant le jour 5** | 0,5 h | Claude |
| T-153-09 | US-153 | REV | Revue de sécurité (cloisonnement, aucune donnée personnelle au-delà du nom, aucune dépendance obligatoire) rapportée hors dépôt public, et revue de code | 0,75 h | Claude |
| T-153-10 | US-153 | BE | Démo : widgets pour Isabelle ; ajustement minimal du plan de commandes si une donnée manque (commande de Claire en attente depuis 4 jours, facture échue depuis 18 jours), parcours existants inchangés | 0,75 h | Claude |
| T-153-11 | US-153 | TEST | Tests de la démo : widgets d'Isabelle (approbations, encours), autre entreprise exclue, durée de remise à zéro ≤ +20 % ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-153-12 | US-153 | DOC | README EN et FR (interface de widget, intégrations optionnelles), entrée du `CHANGELOG.md` de la 0.2.0, captures | 0,5 h | Claude |
| T-156-01 | US-156 | DOC | Cadrage : `git fetch`, partir de `main` (v1.1.0), branche vérifiée ; matrice CI et `composer.json` relus, rubriques « versions supportées et impact de version » et « ordre de publication » ; hooks Twig de l'en-tête et thème `theme_b2b` lus ; contrat du compteur « à décider par ce membre » | 1 h | Claude |
| T-156-02 | US-156 | TEST | Tests rouges : compteur par membre (propre commande et dépassement de limite exclus, autre entreprise exclue, égal à la liste filtrée), « 99+ », visibilité par `APPROVAL_LIST`, aucune requête de comptage pour un visiteur ou un acheteur (comptage des requêtes), option `header_badge` | 1,75 h | Claude |
| T-156-03 | US-156 | BE | Service de comptage par membre, mémoïsé pour la requête, fonction Twig, option de configuration `header_badge` (true par défaut) | 1,5 h | Claude |
| T-156-04 | US-156 | FE-WEB | Composant d'en-tête sur les hooks Twig ordinateur et mobile : icône, libellé, pastille, libellé accessible « Aucune approbation en attente », lien vers la liste à décider ; libellés FR et EN ; contraste AAA, cible de 44 px | 1,5 h | Claude |
| T-156-05 | US-156 | TEST | Dans Docker : ECS, PHPStan max, Rector, tests ; rendu réel FR et EN, ordinateur et mobile, pa11y WCAG2AAA dans l'application de test ; lecture du code pour les cellules PHP 8.2 et Symfony 6.4 | 1 h | Claude |
| T-156-07 | US-156 | DOC | README EN et FR (en-tête, option `header_badge`, versions supportées), `CHANGELOG.md` de la v1.2.0, captures | 0,5 h | Claude |
| T-156-08 | US-156 | REV | Revue (permission, aucune requête pour un visiteur, cloisonnement, absence de donnée personnelle) rapportée hors dépôt public, et revue de code | 0,5 h | Claude |
| T-156-09 | US-156 | OPS | Thibaut donne le GO du tag et de la release `v1.2.0` de purchase-approval | 0,25 h | Thibaut |
| T-156-10 | US-156 | OPS | Push de la branche, PR, matrice CI complète verte (run terminé), merge, tag `v1.2.0` et release GitHub ; script de vérification Packagist remis à Thibaut | 0,75 h | Claude |
| T-156-11 | US-156 | OPS | Thibaut confirme que Packagist indexe la v1.2.0 | 0,25 h | Thibaut |
| T-156-12 | US-156 | OPS | Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | Claude |
| T-156-13 | US-156 | BE | Démo : `php -d memory_limit=1G composer require tmonier/sylius-purchase-approval-plugin:^1.2`, `approver_roles` = `[approver, admin]` (limite d'approbation d'Isabelle inchangée) ; `composer.lock` écrit seul | 0,75 h | Claude |
| T-156-14 | US-156 | TEST | Tests de la démo : pastille de Paul, entrée d'Isabelle selon la décision, aucune entrée pour Claire ni pour un visiteur, parcours d'Isabelle inchangés (commandes sur compte, e-mails « approbation demandée »), en-tête et barre de démo du thème ; rendu réel ordinateur et mobile, pa11y ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1,5 h | Claude |
| T-156-15 | US-156 | DOC | Parcours guidé de Paul et d'Isabelle (`config/demo/plugins.yaml`) et `docs/vitrine-plugins.md` mis à jour | 0,25 h | Claude |
| T-113-01 | US-113 | DOC | Cadrage : accueil actuel relu (`home_*.html.twig`), liste des chiffres vérifiables (source publique de chacun), wireframe des trois colonnes Acheter, Valider, S'intégrer et du choix de rôle, textes FR et EN rédigés | 0,75 h | Claude |
| T-113-02 | US-113 | TEST | Tests rouges : trois colonnes, choix de rôle et auteur visibles à 1440 px, section auteur et méthode avec liens profil et dépôt, auteur non cliquable sans URL de profil, contrôle de charte (titre d'éditeur signalé, chiffre invérifiable refusé) | 1,25 h | Claude |
| T-113-03 | US-113 | BE | Contrôle de charte des textes publics (formulations ambiguës, chiffres non sourcés par liste blanche) et lecture tolérante de `DEMO_PROFILE_URL` | 0,75 h | Claude |
| T-113-04 | US-113 | FE-WEB | Bandeau en trois colonnes, section « Qui a conçu cette démo » et « Comment elle a été faite », mention dans À propos ; libellés FR et EN ; contrastes AAA, mobile | 1,75 h | Claude |
| T-113-05 | US-113 | TEST | Rendu réel FR et EN à 1440 px et sur mobile, pa11y WCAG2AAA, `bin/measure-mobile`, suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-113-07 | US-113 | REV | Revue (ton neutre, aucune prestation ni Sylius Plus, chiffres vérifiables, parité FR et EN) | 0,5 h | Claude |
| T-108-01 | US-108 | DOC | Cadrage : page `integrate` et `plugins.yaml` relus, matrice intégration, plugin, état (ERP, PIM, transporteurs, PA/PDP, portails d'achat, assistants), confirmation que la page ne dépend pas d'US-130, textes FR et EN rédigés | 0,5 h | Claude |
| T-108-02 | US-108 | TEST | Tests rouges : chaque intégration a un état et un lien vers son plugin, « à venir » ou « en cours » pour un plugin non publié, « non fourni » avec renvoi vers le contrat d'événements de webhook-outbox, cohérence d'état (plugin publié marqué à venir échoue), charte (formulations commerciales refusées) | 1,25 h | Claude |
| T-108-03 | US-108 | BE | Source des états dérivée de `config/demo/plugins.yaml`, contrôle de cohérence, extension du contrôle de charte, route et contrôleur de la page | 1 h | Claude |
| T-108-04 | US-108 | FE-WEB | Page « Intégrations » : schéma en SVG avec alternative textuelle, badges d'état, liens vers les plugins, entrée de menu ou de pied de page, aucun bouton de contact ; libellés FR et EN ; contrastes AAA, mobile | 1,75 h | Claude |
| T-108-05 | US-108 | TEST | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile`, suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-108-06 | US-108 | DOC | Documentation à jour (`docs/vitrine-plugins.md`, README de la démo) | 0,5 h | Claude |
| T-108-07 | US-108 | REV | Revue (aucune formulation commerciale, états cohérents avec la vitrine, parité FR et EN) | 0,5 h | Claude |
| T-099-01 | US-099 | DOC | Fiche : 5e scénario (URL de retour hors domaines autorisés) et aide au clavier confirmés ou retirés avec Thibaut ; plan des quatre étapes numérotées, glossaire (cXML, OCI, OrderRequest, HOOK_URL) en une phrase chacun, schéma du flux | 0,75 h | Claude |
| T-099-02 | US-099 | TEST | Tests rouges : quatre étapes numérotées (liste ordonnée) et retour de panier lisible, aide lisible au survol et au focus clavier, parité des clés FR et EN, session expirée avec lien pour relancer, URL de retour refusée avec explication | 1 h | Claude |
| T-099-03 | US-099 | BE | Étape courante calculée par le contrôleur (lancement, boutique, panier reçu, commande), message de session expirée avec action « relancer », explication du refus de domaine avec les domaines autorisés | 0,75 h | Claude |
| T-099-04 | US-099 | FE-WEB | Habillage du portail fictif (sans marque réelle), étapes numérotées, aides du jargon, schéma du flux en SVG avec alternative textuelle, retour de panier lisible, protocoles cXML et OCI ; libellés FR et EN (`simulator.*.yaml`) ; contrastes AAA | 2 h | Claude |
| T-099-05 | US-099 | TEST | Rendu réel FR et EN (cXML et OCI), pa11y WCAG2AAA, `bin/measure-mobile`, suite via `bin/run-tests`, ECS (`src tests`) et PHPStan | 1 h | Claude |
| T-099-06 | US-099 | DOC | Documentation à jour (guide des parcours `docs/parcours-guides.md`, page du simulateur) et mention du simulateur habillé dans le kit média | 0,5 h | Claude |
| T-099-07 | US-099 | REV | Revue (aucune marque réelle comme logo, ton de la charte, parité FR et EN) | 0,5 h | Claude |
| T-TECH-01 | Transverse | OPS | Démo : monter le lock sur company-insights 0.1.1 (`php -d memory_limit=1G composer update tmonier/sylius-company-insights-plugin`, lock actuellement sur v0.1.0, contrainte `^0.1` inchangée) ; seul ce paquet change dans `composer.lock` ; ECS, PHPStan et tests de fumée via `bin/run-tests` | 0,75 h | Claude |
| T-TECH-03 | Transverse | DOC | Rubriques « ordre de publication » et « versions supportées (PHP, Symfony, Sylius) et impact de version, matrice CI lue au cadrage » dans `project-management/templates/user-story.md` et le gabarit de consigne de worker (action 2 de la rétro S19 ; absorbe T-TECH-03 du sprint 19) | 0,5 h | Claude |
| T-TECH-07 | Transverse | DOC | Note au runbook : plan de déploiement de repli quand les builds Railway sont suspendus (état de la plateforme, ordre de déploiement des PR en attente) — action 5 de la rétro S19 | 0,75 h | Claude |
| T-TECH-12 | Transverse | DOC | Bilan des indicateurs de la rétro S19 (captures reportées pour le disque, CI rouge d'ordre de publication, écart de gate, arrêts non demandés, workers morts) préparé pour la rétro du 2027-04-02 | 0,5 h | Orchestrateur |

## 🚫 Bloqué

Aucune tâche bloquée à la décomposition. T-TECH-06 attend la reprise de Railway et le déploiement de Thibaut (T-TECH-05, échéance 2027-03-26) : elle passera 🚫 si la plateforme n'a pas repris au point d'étape.

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-06 | Transverse | TEST | Vérification en production des sprints 18 et 19 dès la reprise de Railway : checks du runbook (`docs/ouverture-publique.md`, `docs/deploiement-railway.md`) et un contrôle par story livrée (US-149, US-150, US-151, US-155, US-146, stories du sprint 18) ; anomalies ouvertes en backlog, rien de sensible dans le dépôt public | 1,5 h | Claude |

## ⏸ Reprises conditionnelles du sprint 19 (hors total)

Engagées seulement si la rétrospective du 2027-03-19 les reporte (3,25 h, voir [tasks/technical-tasks.md](./tasks/technical-tasks.md)).

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-13 | Transverse | TEST | Reprise de T-TECH-01 du sprint 19 : tests rouges de `bin/check-disk` pour la VM Docker (espace libre mesuré, alerte, refus de `bin/run-tests` sous 3 Go dans la VM) | 0,75 h | Claude |
| T-TECH-14 | Transverse | OPS | Reprise de T-TECH-02 du sprint 19 : `bin/check-disk` mesure le disque de la VM Docker ; `bin/run-tests` refuse de démarrer sous 3 Go dans la VM, le gel à 5 Go de la machine restant inchangé | 1,25 h | Claude |
| T-TECH-15 | Transverse | TEST | Reprise de T-TECH-05 du sprint 19 : test garde rouge, aucun faux jeton au format réel dans les `tests/` de la démo ; l'échec nomme le fichier | 0,75 h | Claude |
| T-TECH-16 | Transverse | DOC | Reprise de T-TECH-06 du sprint 19 : règle « secrets fictifs recomposés à l'exécution » dans `docs/tests-locaux.md` et le gabarit de consigne de worker | 0,5 h | Claude |

## ↪️ Hors périmètre : US-093 sacrifiée (reportée au sprint 21)

US-093 est sacrifiée le 2026-10-22 (capacité) et reportée au sprint 21 (statut backlog, pas un retard du sprint). 11 tâches, 16,5 h, **hors total** ; listées pour mémoire.

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-093-01 | US-093 | DOC | Cadrage : inventaire du catalogue actuel (≈ 80 références), répartition cible par famille (visserie, EPI, outillage, fournitures), attributs, conditionnements et paliers ; budget disque des images estimé en Mo et comparé à l'espace libre ; `fixtures/README.md` et `docs/images-produits.md` relus | 1 h | Claude |
| T-093-02 | US-093 | TEST | Tests rouges de données : au moins 150 références avec famille, conditionnement, prix et image ; doublon de code refusé en nommant le code ; palier supérieur au prix de base refusé ; familles non vides | 1,75 h | Claude |
| T-093-03 | US-093 | BE | Contrôle de données du chargement des fixtures (doublon, palier, champs obligatoires) avec message nommant la cause | 1,5 h | Claude |
| T-093-04 | US-093 | DB | Fixtures : tranche visserie et quincaillerie (environ 35 références nouvelles, libellés FR et EN, attributs, conditionnements, paliers) | 2,25 h | Claude |
| T-093-05 | US-093 | DB | Fixtures : tranche EPI et outillage (environ 35 références nouvelles, mêmes règles) | 2,25 h | Claude |
| T-093-06 | US-093 | DB | Fixtures : fournitures, familles et attributs de filtrage, cohérence des prix entre tranches | 1,5 h | Claude |
| T-093-07 | US-093 | OPS | Images : manifeste et téléchargement (`bin/fetch-product-images`), compression, crédits ; `bin/check-disk` avant et après, `bin/clean-test-caches` ciblé | 1,75 h | Claude |
| T-093-08 | US-093 | OPS | Mesures : durée de `app:demo:reset` avant et après (`bin/profile-reset`, objectif sous 10 minutes), pages filtrées par famille sous 800 ms au p95 | 1,5 h | Claude |
| T-093-09 | US-093 | TEST | Rendu réel FR et EN du catalogue et des filtres par famille, pa11y WCAG2AAA, `bin/measure-mobile`, suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1,5 h | Claude |
| T-093-10 | US-093 | DOC | `fixtures/README.md`, `docs/images-produits.md`, `docs/remise-a-zero-demo.md` (volume et durée), crédits d'images | 0,75 h | Claude |
| T-093-11 | US-093 | REV | Revue (cohérence des données, aucune marque réelle, licences et crédits d'images, espace disque restant) | 0,75 h | Claude |
