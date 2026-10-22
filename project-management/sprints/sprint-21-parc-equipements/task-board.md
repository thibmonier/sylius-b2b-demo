# Task Board : Sprint 021 (parc d'équipements)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Conditionnelle (action de Thibaut ou GO) | ⚠️ Sacrifiable

Total décomposé : 90 tâches, 86,75 h ; **périmètre engagé après les sacrifices d'US-154, US-088 et US-098 : 61 tâches, 62,25 h (1,25 h d'actions de Thibaut, 4 tâches)**, soit 61 h de charge pour Claude. Périmètre validé par Thibaut le 2026-10-22 : variante B, 25 points, ramenés à 17 par les sacrifices (US-056, US-057, US-058, US-157 engagées). État : 59 🔲, 2 🚫, aucune commencée. Ordre de sacrifice restant : US-157 (plancher = MMF, 15 points). Voir [tasks/README.md](./tasks/README.md).

**Capacité** : 60,5 h (25 points × 2,1 h + 8 h de tâches transverses) ; charge de Claude 85 h avant sacrifices ; sacrifices du 2026-10-22 (US-154 12,75 h, US-088 7,25 h, US-098 4 h) : reste 61 h, soit 0,5 h au-dessus de la capacité ; **59 h exécutables** hors T-TECH-07 (2 h, bloquée, Railway suspendu), sous la capacité. Point d'étape le 2027-04-09.

## 🔲 À Faire

Les tâches de Thibaut sont marquées dans la colonne « Réalisé par ». Les tâches ⚠️ d'US-157 (seule story sacrifiable restante) sont à ne pas engager avant le MMF.

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-056-10 | US-056 | OPS | Thibaut donne le GO de push, PR et merge des PR du plugin (US-056 à US-058) ; aucun tag | 0,25 h | Thibaut |
| T-157-08 | US-157 | OPS | Thibaut donne le GO du tag et de la release `v1.3.0` de purchase-approval | 0,25 h | Thibaut |
| T-157-10 | US-157 | OPS | Thibaut confirme que Packagist indexe la v1.3.0 | 0,25 h | Thibaut |
| T-157-11 | US-157 | OPS | Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | Claude |
| T-157-12 | US-157 | BE | Démo : `php -d memory_limit=1G composer require tmonier/sylius-purchase-approval-plugin:^1.3` ; `composer.lock` écrit seul (avant T-154-14) | 0,5 h | Claude |
| T-157-13 | US-157 | TEST | Tests de la démo : panneau de Paul (5 plus anciennes, « Tout voir »), entrée d'Isabelle, aucune entrée pour Claire ni pour un visiteur, parcours d'US-156 inchangés ; rendu réel et pa11y ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1,25 h | Claude |
| T-157-14 | US-157 | DOC | Parcours guidé de Paul (`config/demo/plugins.yaml`) et `docs/vitrine-plugins.md` mis à jour | 0,25 h | Claude |
| T-157-15 | US-157 | OPS | Fin de worker : suppression de `vendor`, des caches et de l'image du plugin (après T-157-09), puis des caches du worker de démo ; `df -h .` avant et après | 0,25 h | Claude |

## 🔄 En Cours

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-058-10 | US-058 | OPS | Préparation de la release 0.1.0 : CI de `main` verte sur PHP 8.2 à 8.5, `composer validate --strict`, `branch-alias`, script du scratchpad à OK ou FAIL (tag et vérification Packagist) remis à Thibaut ; aucun tag | 0,5 h | Claude |
| T-157-09 | US-157 | OPS | Push, PR, matrice complète verte dont PHP 8.5 (run terminé), merge, tag `v1.3.0` et release GitHub ; script de vérification Packagist remis à Thibaut | 0,75 h | Claude |
| T-TECH-02 | Transverse | DOC | Gabarit de l'orchestrateur (action 2 de la rétro S20, reprise de T-TECH-04 du S20) : gate recoupé avec la somme des points du fichier de statut (écart affiché), « un refus d'outil n'arrête jamais le sprint », et ligne de tâche mise à jour dans le fichier de tâches pour toute tâche faite dans un autre dépôt | 0,5 h | Orchestrateur |
| T-TECH-03 | Transverse | OPS | PHP 8.5 dans la CI de purchase-approval (action 3 de la rétro S20, écart T-TECH-08 du S20) : gabarit de CI de plugin documenté dans `docs/` (PHP 8.2 à 8.5, actions épinglées par SHA, `permissions` minimal) à partir de company-insights, appliqué à purchase-approval dans la PR de la v1.3.0 ; CI verte sur PHP 8.5 | 1,5 h | Claude |
| T-TECH-09 | Transverse | OPS | ECS et PHPStan rejoués sur l'arbre exact avant chaque push (démo et trois dépôts de plugins, dans Docker) ; dernière ligne de sortie citée dans le rapport | 1 h | Orchestrateur |
| T-TECH-10 | Transverse | OPS | Scripts du scratchpad pour les commandes de Thibaut, à OK ou FAIL par élément : tags `v0.1.0` (conditionnel), `v1.3.0`, `v0.3.0`, vérification Packagist, `composer` avec `php -d memory_limit=1G` | 0,75 h | Orchestrateur |
| T-TECH-11 | Transverse | OPS | Reconstruction de l'image et démarrage du conteneur (`/healthz`) par l'orchestrateur avant la revue, puis après tout correctif touchant l'image (un seul build à la fois, image supprimée ensuite) | 0,75 h | Orchestrateur |

## 👀 En Review

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-056-11 | US-056 | OPS | Push, PR vers `main`, matrice complète verte (run terminé), merge | 0,5 h | Claude |
| T-057-10 | US-057 | OPS | Push, PR vers `main`, matrice complète verte (run terminé), merge (GO de T-056-10) | 0,5 h | Claude |
| T-058-09 | US-058 | OPS | Push, PR vers `main`, matrice complète verte (run terminé), merge (GO de T-056-10) | 0,5 h | Claude |

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-056-01 | US-056 | DOC | Cadrage : `git fetch`, branche vérifiée puis `feature/us-056-modeles-equipement` depuis `origin/main` ; relevé `df -h .` et `bin/check-disk` ; matrice CI et `composer.json` relus ; rubriques « versions supportées » et « ordre de publication » ; contrat des entités figé d'après l'ADR-0001 | 1 h | Claude |
| T-056-02 | US-056 | OPS | Matrice CI : PHP 8.2 à 8.5 (Symfony `^6.4`, `^7.4`, `^8.0`, Sylius `~2.3.0`, MySQL 8.4, MariaDB 11.4, PostgreSQL 17), actions épinglées par SHA, `permissions: contents: read` ; `composer.json` en `php ^8.2` si Sylius 2.3 le permet (à vérifier) ; PHPStan toujours sauté sur Symfony 6.4 | 1,5 h | Claude |
| T-056-03 | US-056 | TEST | Tests rouges : unitaires (modèle : code, traductions, ajout et retrait de variantes), intégration (schéma et index de la migration, suppression d'un modèle retire ses liens), fonctionnels d'administration (création, code en double, données invalides, 403 sans permission, fiche de variante) | 2 h | Claude |
| T-056-04 | US-056 | DB | Entités `EquipmentModel` et traductions, relation many-to-many avec `ProductVariant` (côté propriétaire sur le modèle), dépôt, migration avec index `(equipment_model_id, variant_id)`, préfixe de la configuration des migrations | 2,5 h | Claude |
| T-056-05 | US-056 | BE | Ressource Sylius des modèles : routes d'administration, grille, formulaire et validation (code unique ≤ 64, nom ≤ 255), permission dédiée, entrée de menu ; libellés FR et EN | 2,5 h | Claude |
| T-056-06 | US-056 | FE-WEB | Extension du formulaire de variante (hook Twig) : champ « Modèles compatibles », gabarit ; nombre de variantes compatibles dans la liste des modèles | 2 h | Claude |
| T-056-07 | US-056 | TEST | Dans Docker : ECS, PHPStan max, Rector, suite complète ; rendu réel FR et EN des écrans, pa11y WCAG2AAA ; migration montée et descendue sur MySQL, MariaDB et PostgreSQL ; lecture du code pour les cellules PHP 8.2 et 8.5 et Symfony 6.4 | 1,25 h | Claude |
| T-056-08 | US-056 | REV | Revue de sécurité (permission, CSRF, validation) rapportée hors dépôt public, et revue de code | 0,5 h | Claude |
| T-056-09 | US-056 | DOC | README EN et FR (installation, administration des modèles), `CHANGELOG.md` [Unreleased], sans comparaison avec Sylius Plus | 0,5 h | Claude |
| T-056-12 | US-056 | OPS | Fin de worker : suppression de `vendor`, des caches et de l'image Docker du plugin ; `df -h .` avant et après dans le rapport | 0,25 h | Claude |
| T-057-01 | US-057 | DOC | Cadrage : `git fetch`, `feature/us-057-parc-client` depuis `origin/main` (US-056 fusionnée), branche vérifiée ; relevé du disque ; contrat du voter, détection de company-accounts, règles RGPD relus | 0,75 h | Claude |
| T-057-02 | US-057 | TEST | Tests rouges : unitaires (entité, validation), voter (propriétaire, membre de l'entreprise avec et sans company-accounts, autre client, visiteur), fonctionnels (CRUD, 404 pour un UUID étranger en lecture, modification et suppression, saisie invalide, redirection d'un visiteur, garde de suppression d'un modèle utilisé, aucune plaque dans les journaux ni les URLs, suppression en cascade) | 2 h | Claude |
| T-057-03 | US-057 | DB | Entité `CustomerAsset` (UUID, client, modèle, libellé, identifiant), migration (clé étrangère client `ON DELETE CASCADE`, clé étrangère modèle, index), cascade Doctrine | 2 h | Claude |
| T-057-04 | US-057 | BE | Voter, dépôt par propriétaire ou entreprise (détection de company-accounts), validation serveur (identifiant ≤ 40, sans caractère de contrôle, modèle existant), réponse 404 uniforme, aucun journal de l'identifiant, garde de suppression d'un modèle utilisé | 2,5 h | Claude |
| T-057-05 | US-057 | FE-WEB | Contrôleurs et routes `/account/equipment`, formulaires, gabarits liste, ajout, modification et suppression avec confirmation, entrée dans le menu du compte ; libellés FR et EN ; mobile ; contraste AAA | 3 h | Claude |
| T-057-06 | US-057 | TEST | Dans Docker : ECS, PHPStan max, Rector, suite ; rendu réel FR et EN, ordinateur et mobile, pa11y WCAG2AAA ; cascade vérifiée sur MySQL, MariaDB et PostgreSQL | 1,5 h | Claude |
| T-057-07 | US-057 | OPS | Captures de « Mon matériel » pour le README (application de test, données fictives, sans plaque réelle) ; relevé du disque avant ; **au plus tard le jour 5** | 0,5 h | Claude |
| T-057-08 | US-057 | REV | Revue de sécurité (voter, cloisonnement, 404, UUID, RGPD) rapportée hors dépôt public, et revue de code | 0,75 h | Claude |
| T-057-09 | US-057 | DOC | README EN et FR (cloisonnement, données personnelles, limitation du débit laissée à l'application, captures), `CHANGELOG.md` | 0,5 h | Claude |
| T-057-11 | US-057 | OPS | Fin de worker : suppression de `vendor`, des caches et de l'image Docker du plugin ; `df -h .` avant et après | 0,25 h | Claude |
| T-058-01 | US-058 | DOC | Cadrage : `git fetch`, `feature/us-058-filtre-compatibilite` depuis `origin/main` (US-057 fusionnée) ; relevé du disque ; méthode de listing du dépôt de produits de Sylius 2.3 (taxons et recherche), hooks Twig des listes, état en session | 0,75 h | Claude |
| T-058-02 | US-058 | TEST | Tests rouges : extension de requête (`EXISTS`, tout le parc ou une machine, parc vide, visiteur, machine d'un autre client ignorée), état en session et dans l'URL, message « aucune pièce compatible », nombre de requêtes sans filtre actif ; fixtures d'un catalogue de 40 produits dont 12 compatibles | 2 h | Claude |
| T-058-03 | US-058 | BE | Extension de la requête de listing, état du filtre (session et requête, identifiant de machine validé et rattaché au client), décision « contrôle proposé » | 3 h | Claude |
| T-058-04 | US-058 | FE-WEB | Contrôle « Compatible avec mon matériel » sur les listes de catégories et la recherche (hooks Twig), message vide et lien « Retirer le filtre » ; libellés FR et EN ; clavier ; contraste AAA ; mobile | 2 h | Claude |
| T-058-05 | US-058 | TEST | Dans Docker : ECS, PHPStan max, Rector, suite ; rendu réel FR et EN, pa11y WCAG2AAA ; `EXPLAIN` de la requête sur MySQL, MariaDB et PostgreSQL (index utilisé) ; lecture du code pour PHP 8.2 et 8.5 | 1,5 h | Claude |
| T-058-06 | US-058 | OPS | Captures du filtre (avec et sans filtre, ordinateur et mobile) pour le README ; relevé du disque avant ; **au plus tard le jour 5** | 0,5 h | Claude |
| T-058-07 | US-058 | REV | Revue de sécurité (identifiant de machine dans l'URL, cloisonnement, absence de fuite) rapportée hors dépôt public, et revue de code | 0,5 h | Claude |
| T-058-08 | US-058 | DOC | README EN et FR complet (installation, configuration, usage, limites du listing, versions supportées, captures), `CHANGELOG.md` prêt pour la 0.1.0, statut des tranches dans `SPEC.md` | 1 h | Claude |
| T-058-11 | US-058 | OPS | Fin de worker : suppression de `vendor`, des caches et de l'image Docker du plugin ; `df -h .` avant et après | 0,25 h | Claude |
| T-157-01 | US-157 | DOC | Cadrage : `git fetch`, branche depuis `origin/main` (v1.2.0), branche vérifiée ; matrice CI et `composer.json` relus ; hooks de l'en-tête d'US-156, Turbo Frame ou composant Live ; contrat du panneau (5 plus anciennes décidables, échéance `expire_after_days`) | 0,75 h | Claude |
| T-157-02 | US-157 | TEST | Tests rouges : 5 plus anciennes dans l'ordre, lien « Tout voir (N) », mention « expire dans N jours », cloisonnement par entreprise et par limite d'approbation, aucune requête sans clic, 403 sans la permission `APPROVAL_LIST` | 1,25 h | Claude |
| T-157-03 | US-157 | BE | Contrôleur de fragment et requête (les 5 plus anciennes à décider par ce membre, total, échéance), permission, chargement différé | 1,5 h | Claude |
| T-157-04 | US-157 | FE-WEB | Panneau d'en-tête : bouton `aria-expanded`, Turbo Frame chargé au premier clic, fermeture par Échap, focus rendu au bouton, repli sans JavaScript ; libellés FR et EN ; contraste AAA ; cible de 44 px | 1,75 h | Claude |
| T-157-05 | US-157 | TEST | Dans Docker : ECS, PHPStan max, Rector, suite ; rendu réel FR et EN, ordinateur et mobile, pa11y WCAG2AAA ; lecture du code pour PHP 8.2 et Symfony 6.4 | 1 h | Claude |
| T-157-06 | US-157 | REV | Revue (permission, cloisonnement, aucune donnée personnelle superflue) rapportée hors dépôt public, et revue de code | 0,5 h | Claude |
| T-157-07 | US-157 | DOC | README EN et FR (panneau, versions supportées), `CHANGELOG.md` de la v1.3.0, captures de T-TECH-04 | 0,5 h | Claude |
| T-TECH-01 | Transverse | OPS | Dérogation disque encadrée (décision de Thibaut du 2026-10-22, écart à l'action 1 de la rétro S20) : relevé `df -h .` et `bin/check-disk` au planning et avant chaque worker, `bin/clean-test-caches`, règles consignées dans les consignes de worker (un seul worker, ≤ 3 Go, nettoyage en fin de tâche, arrêt sous 4 Go libres) ; relevé noté dans le sprint-goal | 0,75 h | Orchestrateur |
| T-TECH-04 | Transverse | OPS | Captures du README de purchase-approval (reprise de T-156-06 du S20 : en-tête ordinateur et mobile, puis panneau d'US-157) ; relevé du disque avant ; au plus tard le jour 5 | 0,5 h | Claude |
| T-TECH-05 | Transverse | DOC | Reprise de T-113-06 du S20 : documentation à jour (`docs/theme-b2b.md`, README de la démo) et captures d'accueil pour le README de la démo ; relevé du disque avant ; au plus tard le jour 5 | 0,5 h | Claude |
| T-TECH-06 | Transverse | DOC | Promotion levée (décision de Thibaut du 2026-10-22) dans le dépôt du plugin parc d'équipements : `docs/plus-overlap.md` et mention « Promotion suspended » du README neutralisés, sans comparaison avec Sylius Plus dans les textes publics ; fait dans le worker d'US-058 (même dépôt, même PR) | 0,5 h | Claude |
| T-TECH-12 | Transverse | DOC | Bilan des indicateurs de la rétro S20 (arrêts dus au disque, captures reportées, écart de gate, tâches livrées restées 🔲, CI de purchase-approval sur PHP 8.5) préparé pour la rétro du 2027-04-16 | 0,5 h | Orchestrateur |

## 🚫 Bloqué

| ID | US | Raison | Action |
|----|-----|--------|--------|
| T-TECH-07 | Transverse | Railway suspendu (plans Hobby) : rien à vérifier en production | Reprise de la plateforme, puis déploiement groupé (T-TECH-08) |
| T-TECH-08 | Transverse | Railway suspendu (plans Hobby) : aucun déploiement possible | Thibaut déploie à la reprise ; aucune commande Railway par Claude |

## 📅 Conditionnelles (hors total) : publication de la 0.1.0 du plugin parc d'équipements

Engagées seulement sur GO de Thibaut (US-059 et US-060, sprint 22 par défaut).

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-058-12 | US-058 (cond.) | OPS | Thibaut donne le GO du tag et de la release `v0.1.0` du plugin parc d'équipements | 0,25 h | Thibaut |
| T-058-13 | US-058 (cond.) | OPS | Tag `v0.1.0` et release GitHub (CI de `main` verte) | 0,5 h | Claude |
| T-058-14 | US-058 (cond.) | OPS | Thibaut soumet le **nouveau paquet** à Packagist (URL du dépôt) et confirme l'indexation de la 0.1.0 | 0,5 h | Thibaut |
| T-058-15 | US-058 (cond.) | OPS | Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | Claude |
| T-058-16 | US-058 (cond.) | BE | Démo : `php -d memory_limit=1G composer require tmonier/sylius-customer-asset-management-plugin:^0.1`, `composer.lock` écrit seul (le reste de l'intégration est US-060, sprint 22) | 1 h | Claude |

## ↪️ Hors périmètre : US-154, US-088 et US-098 sacrifiées (reportées au sprint 22)

US-154, US-088 et US-098 sont sacrifiée le 2026-10-22 (capacité), reportée au sprint 22 (statut backlog, ordre de sacrifice validé par Thibaut ; pas un retard du sprint). 29 tâches, 24,5 h, **hors total** ; listées pour mémoire.

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-098-01 | US-098 | DOC | Cadrage : gabarit de la jauge et `docs/remplissage-contenants.md` lus, définition de « pertinent », emplacement du rendu (démo ou plugin) | 0,5 h | Claude |
| T-098-02 | US-098 | TEST | Tests rouges : 2 contenants pertinents sur 9, détails repliables au clavier, 9 sur 9 sans contrôle « détails », aucune donnée de contenant (bloc masqué), `<details>` sans JavaScript | 1 h | Claude |
| T-098-03 | US-098 | FE-WEB | Gabarit : filtre des contenants pertinents, `<details>` des autres, libellés FR et EN, contraste AAA | 1,25 h | Claude |
| T-098-04 | US-098 | TEST | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile` ; suite ciblée via `bin/run-tests`, ECS (`src tests`), PHPStan | 0,75 h | Claude |
| T-098-05 | US-098 | DOC | `docs/remplissage-contenants.md` mis à jour | 0,25 h | Claude |
| T-098-06 | US-098 | REV | Revue (ton neutre, parité FR et EN, accessibilité) | 0,25 h | Claude |
| T-088-01 | US-088 | DOC | Cadrage : `AuditLogDemoFixture`, `app:demo:reset`, écouteur du plugin d'audit et ses points d'extension lus ; choix « exclure » ou « purger » consigné | 0,75 h | Claude |
| T-088-02 | US-088 | TEST | Tests rouges : journal sans ligne de reset, 5 à 6 événements métier datés et attribués, deux resets de suite sans doublon, modification réelle journalisée, échec explicite si le filtre est désactivé | 1,5 h | Claude |
| T-088-03 | US-088 | BE | Exclusion des écritures de la remise à zéro du journal (ou purge en fin de reset), sans toucher aux écritures des visiteurs | 2 h | Claude |
| T-088-04 | US-088 | BE | Rejeu de 5 à 6 événements métier (prix, validation d'inscription, plafond, approbation) attribués à des comptes de démo, dates cohérentes | 1,5 h | Claude |
| T-088-05 | US-088 | TEST | Reset réel exécuté deux fois ; rendu réel du journal FR et EN, pa11y WCAG2AAA ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-088-06 | US-088 | DOC | `docs/remise-a-zero-demo.md` mis à jour (journal propre, événements rejoués) | 0,25 h | Claude |
| T-088-07 | US-088 | REV | Revue (aucun secret, chiffres vérifiables, parité FR et EN) | 0,25 h | Claude |
| T-154-01 | US-154 | DOC | Cadrage : `git fetch`, branche depuis `origin/main` (v0.2.1), branche vérifiée ; matrice CI et `composer.json` relus ; services publics de quote-request et tier-pricing, prix de base disponible dans la ligne de commande, `InsightsWidgetProviderInterface` lus ; rubriques « versions » et « ordre de publication » | 1 h | Claude |
| T-154-02 | US-154 | TEST | Tests rouges : indicateurs des devis (4 demandes, 3 devis, 2 commandes, 67 %, délai médian, expirés), économie (120 casques à 9 € au lieu de 11 € = 240 € HT), grille modifiée après l'achat, un seul compagnon, aucun compagnon, période sans demande, service en erreur | 1,5 h | Claude |
| T-154-03 | US-154 | BE | Widget « Devis » (fournisseur de widget, permission, taux « non applicable » sans demande, repli « Données indisponibles ») | 2 h | Claude |
| T-154-04 | US-154 | BE | Widget « Économies » (différence prix de base et prix figé, sommée sur la période, rapportée à la dépense HT) | 2 h | Claude |
| T-154-05 | US-154 | FE-WEB | Rendu des deux widgets avec tableau équivalent ; libellés FR et EN, libellé factuel « Économies sur prix dégressifs » ; contraste AAA ; mobile | 1 h | Claude |
| T-154-06 | US-154 | TEST | Dans Docker : ECS, PHPStan max, Rector, suite avec et sans compagnons ; rendu réel FR et EN, pa11y WCAG2AAA ; lecture du code pour les cellules PHP 8.2 et Symfony 6.4 | 1 h | Claude |
| T-154-07 | US-154 | OPS | Captures des deux widgets pour le README (application de test, données fictives) ; relevé du disque avant ; au plus tard le jour 5 si la story est maintenue | 0,5 h | Claude |
| T-154-08 | US-154 | REV | Revue (économie sur prix figé, cloisonnement entreprise, journal sans donnée de commande) rapportée hors dépôt public, et revue de code | 0,5 h | Claude |
| T-154-09 | US-154 | DOC | README EN et FR (deux widgets, plugins optionnels, versions supportées), `CHANGELOG.md` de la 0.3.0, captures | 0,5 h | Claude |
| T-154-10 | US-154 | OPS | Thibaut donne le GO du tag et de la release `v0.3.0` de company-insights | 0,25 h | Thibaut |
| T-154-11 | US-154 | OPS | Push, PR, matrice complète verte (run terminé), merge, tag `v0.3.0` et release GitHub ; script de vérification Packagist remis à Thibaut | 0,75 h | Claude |
| T-154-12 | US-154 | OPS | Thibaut confirme que Packagist indexe la 0.3.0 (paquet déjà soumis) | 0,25 h | Thibaut |
| T-154-13 | US-154 | OPS | Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | Claude |
| T-154-14 | US-154 | BE | Démo : `php -d memory_limit=1G composer require tmonier/sylius-company-insights-plugin:^0.3` ; `composer.lock` écrit seul (après T-157-12) | 0,5 h | Claude |
| T-154-15 | US-154 | TEST | Tests de la démo : widgets d'Acme (devis, économies), TechParts absent des chiffres d'Acme ; scénario d'Isabelle dans `config/demo/plugins.yaml` et `docs/vitrine-plugins.md` ; rendu réel et pa11y ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-154-16 | US-154 | OPS | Fin de worker : suppression de `vendor`, des caches et de l'image du plugin, puis des caches du worker de démo ; `df -h .` avant et après | 0,25 h | Claude |

## Métriques

- **Tâches** : 61 engagées (hors 5 conditionnelles et 29 tâches sacrifiées) | 41 terminées, 3 en review, 7 en cours, 10 à faire ou bloquées (dont T-TECH-07 et T-TECH-08, bloquées par Railway) ; 67 % terminées
- **Heures** : 62,25 h estimées | consommation non relevée (pas de pointage horaire) | restant : actions de Thibaut (GO, tags, Packagist), bump de la démo et vérification en production
- **Points** : 17 engagés (25 validés, 8 sacrifiés) | 17 en review (livraison réelle après merge, tags et production)
