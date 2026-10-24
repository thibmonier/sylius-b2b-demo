# Task Board : Sprint 027 (fiche produit et ERP fictif)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **45 tâches, 34,5 h** pour 13 points engagés ; **33,75 h pour Claude**, 0,75 h d'actions de Thibaut (2 tâches). État au planning (2026-10-24) : **1 ✅, 1 🔄, 33 🔲, 10 📅**.

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre. Sacrifices proposés, à confirmer par Thibaut : US-188 (n°1), puis US-193 (n°2) ; leurs tâches portent ⚠️ dans leur fichier si le sacrifice est décidé. Les tâches de code d'US-189 (T-189-07 à T-189-13) sont 📅 tant que Thibaut n'a pas validé la maquette (T-189-04).

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-189-01 | US-189 | DOC | Cadrage : inventaire de la fiche relu contre le code (hooks `product.show`, gabarits `product_*`, blocs B2B, canal B2C, tests existants de … | 0,75 h | Claude |
| T-189-02 | US-189 | TEST | Mesure « avant » en production (lecture seule) sur les trois fiches types : LCP mobile (médiane de 5 passes), poids et requêtes, largeur à … | 0,75 h | Claude |
| T-189-03 | US-189 | FE-WEB | Maquette Claude Design (desktop et mobile) : en-tête de fiche, galerie, référence, prix HT, paliers, action d'achat, états connecté, … | 2 h | Orchestrateur |
| T-193-01 | US-193 | DOC | Cadrage : `ExchangeFileSample`, `RecentPlacedOrders`, test `ErpFlatfilePageTest`, `ExportOrdersCommand` et son répertoire, fixtures et … | 0,25 h | Claude |
| T-193-02 | US-193 | TEST | Tests rouges, commités seuls et cités dans la PR : dernier fichier (nominal), aucun fichier, fichier d'un autre canal, répertoire absent ou … | 0,75 h | Claude |
| T-193-03 | US-193 | BE | Lecteur du dernier fichier du répertoire d'échange : en-tête et trois lignes du canal courant, e-mails masqués, aucune exception remontée, … | 1 h | Claude |
| T-193-04 | US-193 | BE | Fixtures de démo : quelques commandes passées (événements `order.placed`) et export lancé après le chargement ; remise à zéro nocturne … | 1 h | Claude |
| T-193-05 | US-193 | FE-WEB | Gabarit `erp_flatfile.html.twig` : extrait, messages neutres (aucun fichier, canal sans ligne, fichier vide, répertoire indisponible), … | 0,25 h | Claude |
| T-193-06 | US-193 | TEST | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; commande exacte et … | 0,5 h | Claude |
| T-193-07 | US-193 | TEST | Vérification en production après le déploiement (lecture seule) : extrait visible FR et EN, pa11y WCAG2AAA, 360 et 1280 px ; mesures … | 0,5 h | Claude |
| T-193-08 | US-193 | REV | Revue (ton neutre, aucun ERP nommé, e-mails masqués) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php … | 0,25 h | Claude |
| T-188-01 | US-188 | DOC | Cadrage : ADR 0017, `webhooks-demo.md`, récepteur (`WebhookReceiverController`, `ReceivedWebhooksController`), journal d'export du plugin … | 0,75 h | Claude |
| T-188-02 | US-188 | DOC | Affinage de la fiche : scénarios réécrits sans import (1 nominal, 2 alternatifs, 2 erreurs), renvoi explicite à US-187 ; section de l'ADR … | 0,5 h | Claude |
| T-188-03 | US-188 | TEST | Tests rouges, commités seuls et cités dans la PR : simulateur (commande traitée, accusé déposé atomiquement), fichier invalide rejeté sans … | 1,25 h | Claude |
| T-188-04 | US-188 | BE | Simulateur « ERP Démo (fictif) » : commande console qui lit le dernier CSV de commandes du répertoire d'échange, valide l'en-tête et les … | 1,75 h | Claude |
| T-188-05 | US-188 | BE | Journal des échanges : lecteur en lecture seule qui regroupe, par fichier, les événements du journal d'export du plugin (lots sortants) et … | 1,25 h | Claude |
| T-188-06 | US-188 | FE-WEB | Page « Échanges ERP (fictif) » au back-office : liste des lots, bandeau « statuts simulés », entrée de menu, état vide, FR et EN ; … | 1,5 h | Claude |
| T-188-07 | US-188 | BE | Remise à zéro nocturne : répertoire d'échange et journal du simulateur vidés, export puis simulation relancés après le chargement des … | 0,5 h | Claude |
| T-188-08 | US-188 | DOC | Documentation `docs/erp-demo.md` (schéma mermaid, fichiers, ce qui est simulé, ce qui reste à US-187), lien depuis `/echange-fichier` et … | 0,75 h | Claude |
| T-188-09 | US-188 | TEST | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; commande exacte et … | 0,5 h | Claude |
| T-188-10 | US-188 | TEST | Vérification après déploiement : pages publiques en production (pa11y, 360 px, documentation) ; page du back-office en local et sur l'image … | 0,25 h | Claude |
| T-188-11 | US-188 | REV | Revue (ton neutre, « ERP Démo (fictif) » partout, aucun éditeur nommé) et table « scénario → test ou preuve » remplie ; `php … | 0,5 h | Claude |
| T-TECH-27-02 | Transverse | TEST | **Action 2** : audit des tests de la démo, tout compteur écrit en dur (nombre de plugins, de fichiers de tests, de lignes de catalogue) … | 0,5 h | Claude |
| T-TECH-27-03 | Transverse | OPS | **Action 3** : `bin/check-disk` refuse une suite complète sous 6 Go (`--full`, appelé par `bin/run-tests` sans chemin ni filtre) et affiche … | 0,75 h | Orchestrateur |
| T-TECH-27-04 | Transverse | BE | **Action 4** : investiguer le 503 « the demo is not configured safely » en mode prod local (`DemoGateListener`, … | 1 h | Orchestrateur |
| T-TECH-27-05 | Transverse | DOC | **Action 4** : jeu minimal et procédure de rendu du noyau en mode prod documentés dans `docs/tests-locaux.md` (ou script de captures qui … | 0,5 h | Orchestrateur |
| T-TECH-27-06 | Transverse | TEST | **Action 5** : garde de largeur étendue aux tables larges sans `code` (tables à quatre colonnes ou plus des pages de `templates/demo/` : … | 0,75 h | Claude |
| T-TECH-27-07 | Transverse | TEST | **Action 5** : rattrapage du sprint 26 : LCP mobile « après » de la home remesuré en production (mêmes protocole et 5 passes que T-186-02), … | 0,75 h | Claude |
| T-TECH-27-08 | Transverse | OPS | **Action 3 (processus)** : avant chaque lot `df -h .`, `bin/check-disk`, mémoire libre et swap ; après chaque lot aucun run actif, … | 0,5 h | Orchestrateur |
| T-TECH-27-09 | Transverse | OPS | ECS (`src tests`), PHPStan max sur **tout le dépôt** et Rector rejoués sur l'arbre exact avant chaque push ; CI verte avant chaque merge (« … | 0,75 h | Orchestrateur |
| T-TECH-27-10 | Transverse | REV | Avant chaque passage en done : table « scénario → test ou preuve » complète, commit de tests rouges cité dans la PR, mesures de production … | 0,5 h | Orchestrateur |
| T-TECH-27-11 | Transverse | OPS | Registre des GO de Thibaut (maquette T-189-04, éléments T-189-05, tout dépôt, tag ou push de plugin) ; chaque GO noté avant l'action ; … | 0,25 h | Orchestrateur |
| T-TECH-27-12 | Transverse | DOC | Revue et rétrospective du sprint 27 : indicateurs de la rétro du sprint 26 (espace libre minimal d'au moins 5 Go, 0 suite complète sous le … | 1 h | Orchestrateur |

## 🔄 En Cours

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-194-01 | US-194 | FE-WEB | Test rouge commité seul puis logo de l'auteur dans `footer_demo.html.twig` : `alt` vide, `width` et `height`, fichier de moins de 8 Ko, 360 px … | 1 h | Claude (w185) |

## 📅 En attente d'une action de Thibaut

Actions de Thibaut :

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-189-04 | US-189 | OPS | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son accord** | 0,5 h | Thibaut |
| T-189-05 | US-189 | OPS | 📅 Thibaut fournit les photos ou éléments que la maquette demande, ou donne le GO sur une banque libre (sans objet si la maquette n'en … | 0,25 h | Thibaut |

Tâches de code d'US-189 bloquées sur le GO de la maquette (T-189-04) :

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-189-06 | US-189 | DOC | Photos ou éléments reçus : licence relue, optimisation, fichier de crédits (source, auteur, licence) ; conditionnelle | 0,5 h | Claude |
| T-189-07 | US-189 | TEST | Tests rouges, commités seuls et cités dans la PR : fiche B2B (nom, référence, prix HT, paliers, action), anonyme sans prix, B2C inchangé, … | 1 h | Claude |
| T-189-08 | US-189 | FE-WEB | Gabarits et hooks de la fiche, styles `b2b.scss` (jetons d'US-186 réutilisés), responsive 360 et 1280 px, AAA, cibles de 44 px, … | 2,75 h | Claude |
| T-189-09 | US-189 | DOC | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter`, `alt` des photos ; texte relu contre la maquette validée | 0,5 h | Claude |
| T-189-10 | US-189 | TEST | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML, pa11y local FR et EN, … | 0,75 h | Claude |
| T-189-11 | US-189 | DOC | Captures `docs/images/product-*` sur un rendu du noyau en mode prod, `docs/theme-b2b.md` et README mis à jour | 0,5 h | Claude |
| T-189-12 | US-189 | TEST | Vérification en production après le déploiement (lecture seule, trois fiches types) : pa11y WCAG2AAA FR et EN, 360 et 1280 px, LCP mobile « … | 0,75 h | Claude |
| T-189-13 | US-189 | REV | Revue (ton neutre, parité FR et EN, crédits complets, B2C inchangé) et table « scénario → test ou preuve » remplie ; `php … | 0,5 h | Claude |

## 🚫 Bloqué ou écarté

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-27-01 | Transverse | DOC | **Actions 1, 2 et 5 (règles)** : règles 13 (commits `git commit -m "…" -- <chemins>`, jamais `-a`, `git show --stat` avant push), 14 … | 0,75 h | Orchestrateur |
