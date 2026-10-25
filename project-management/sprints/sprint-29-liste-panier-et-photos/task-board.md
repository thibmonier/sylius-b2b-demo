# Task Board : Sprint 029 (liste, panier et photos)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

**Sprint planifié le 2026-10-25.** Total décomposé : **39 tâches, 35 h** (13 points). État au planning : **1 ✅, 18 🔲, 20 📅, 0 🔄, 0 👀, 0 🚫**. Les 20 tâches 📅 attendent le GO de la maquette de la liste (T-198-04), celui de la maquette du panier (T-200-04) ou celui de la planche (T-197-05) : aucun Twig avant le premier, aucun téléchargement avant le dernier.

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-29-01 | Transverse | OPS | Un seul cache de rendu par worker, `bin/check-disk` avant chaque rendu, nettoyage `--all` … (**avant la première maquette**) | 1,25 h | Orchestrateur |
| T-TECH-29-02 | Transverse | OPS | `bin/proof-rebase` : rebase, régénération de `proof.json`, commit … | 1 h | Orchestrateur |
| T-198-01 | US-198 | DOC | Cadrage de la liste : hooks `product_card_*`, filtres, tri, pagination, trois pages types … | 0,75 h | Claude |
| T-198-02 | US-198 | TEST | Mesure « avant » en production : LCP mobile (5 passes), 360 px, pa11y, FR et EN … | 0,75 h | Claude |
| T-200-01 | US-200 | DOC | Cadrage du panier : hooks `cart_item` et `cart_tier_discount`, blocs des plugins, tunnel de commande … | 0,75 h | Claude |
| T-200-02 | US-200 | TEST | État de référence « avant » : tests du panier et du tunnel verts, captures connectées locales … | 0,5 h | Claude |
| T-197-01 | US-197 | DOC | Cadrage : réserves de la PR #106, manifeste, cadrage actuel des cartes, familles à couleur annoncée … | 0,5 h | Claude |
| T-197-02 | US-197 | TEST | Tests rouges : point focal, couleur annoncée, manifeste, visuel neutre conservé, aucun appel réseau … | 0,75 h | Claude |
| T-197-03 | US-197 | BE | Tranche 1 : point focal par photo, composant partagé `object-position` carte portrait et fiche 4:3 … | 1 h | Claude |
| T-197-04 | US-197 | DOC | Tranche 2 : recherche Pexels et Unsplash, licences relues, planche pour Thibaut, rien dans le dépôt … | 1,25 h | Claude |
| T-198-03 | US-198 | FE-WEB | Maquette Claude Design de la liste (bureau et téléphone) … | 2 h | Orchestrateur |
| T-200-03 | US-200 | FE-WEB | Maquette Claude Design du panier (bureau et téléphone) … | 2 h | Orchestrateur |
| T-197-08 | US-197 | TEST | Suite ciblée, contrôle complet, captures avant/après des cartes et de la fiche … | 0,5 h | Claude |
| T-TECH-29-03 | Transverse | TEST | LCP mobile de la home remesuré en production (seuil 1456 ms) … | 0,75 h | Claude |
| T-TECH-29-05 | Transverse | REV | Registre des décisions et des GO ; contrôle avant chaque passage en done (preuves, numéro de story) … | 0,75 h | Orchestrateur |
| T-TECH-29-06 | Transverse | OPS | Disque et machine avant et après chaque lot, aucune suite complète sous 6 Go … | 0,5 h | Orchestrateur |
| T-TECH-29-07 | Transverse | OPS | ECS, PHPStan dépôt entier et Rector rejoués avant chaque push, CI verte, aucune fusion avec question ouverte … | 0,75 h | Orchestrateur |
| T-TECH-29-08 | Transverse | DOC | Revue et rétrospective du sprint 29, clôture … | 1 h | Orchestrateur |

## 🔄 En Cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 📅 En attente d'une action de Thibaut

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-198-04 | US-198 | OPS | 📅 Thibaut valide la maquette de la liste : **aucun Twig avant son GO** | 0,5 h | Thibaut |
| T-200-04 | US-200 | OPS | 📅 Thibaut valide la maquette du panier : **aucun Twig avant son GO** | 0,5 h | Thibaut |
| T-197-05 | US-197 | OPS | 📅 Thibaut choisit les photos sur la planche et donne le **GO de téléchargement** | 0,5 h | Thibaut |
| T-198-05 | US-198 | TEST | 📅 Tests rouges de la liste (cartes, anonyme, tri, B2C, vide, 360 px, charte) … | 1 h | Claude |
| T-198-06 | US-198 | FE-WEB | 📅 Gabarits, hooks et styles de la liste ; vue liste négociable … | 3 h | Claude |
| T-198-07 | US-198 | DOC | 📅 Textes FR et EN de la liste … | 0,5 h | Claude |
| T-198-08 | US-198 | TEST | 📅 Suite ciblée, PHPStan dépôt entier, ECS, Rector, pa11y local, `bin/measure-mobile` … | 0,75 h | Claude |
| T-198-09 | US-198 | DOC | 📅 Captures `docs/images/product-list-*`, `docs/theme-b2b.md` … | 0,5 h | Claude |
| T-198-10 | US-198 | TEST | ✅ Vérification en production : pa11y AAA, 360 et 1280 px, LCP après (jour du déploiement) … | 0,75 h | Claude |
| T-198-11 | US-198 | REV | 📅 Revue et table « scénario → test ou preuve » de la liste … | 0,5 h | Claude |
| T-200-05 | US-200 | TEST | 📅 Tests rouges du panier (lignes, palier, approbation, actions, vide, 360 px, tunnel) … | 1 h | Claude |
| T-200-06 | US-200 | FE-WEB | 📅 Gabarits, hooks et styles du panier, récapitulatif collant … | 3 h | Claude |
| T-200-07 | US-200 | DOC | 📅 Textes FR et EN du panier … | 0,5 h | Claude |
| T-200-08 | US-200 | TEST | 📅 Suite ciblée, tunnel de bout en bout, contrôle complet, pa11y local … | 1 h | Claude |
| T-200-09 | US-200 | DOC | 📅 Captures `docs/images/cart-*`, `docs/theme-b2b.md` … | 0,5 h | Claude |
| T-200-10 | US-200 | TEST | 📅 Image reconstruite, conteneur neuf, production (public seulement) … | 0,5 h | Claude |
| T-200-11 | US-200 | REV | 📅 Revue et table « scénario → test ou preuve » du panier … | 0,5 h | Claude |
| T-197-06 | US-197 | DOC | 📅 Après le GO seulement : téléchargement, WebP, manifeste, `git rm` des orphelins … | 0,75 h | Claude |
| T-197-07 | US-197 | BE | 📅 Variantes de couleur, crédits, visuels neutres remplacés si photo retenue … | 0,75 h | Claude |
| T-197-09 | US-197 | TEST | 📅 Vérification en production après la remise à zéro de 03:00, puis revue et table des scénarios … | 0,75 h | Claude |

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 🟡 Faite avec écart

_Aucune tâche._

## ⊘ Sans objet

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-29-04 | Transverse | DOC | Règles 18 à 20 de la consigne (garde des chemins variables, CI rejouée, pas de fusion avec question ouverte), lignes de la checklist, registre des décisions (écrits au planning, 2026-10-25) | 0,5 h | Orchestrateur |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 13 (US-198 5, US-200 5, US-197 3) |
| Tâches | 39 (1 ✅, 18 🔲, 20 📅) |
| Heures | 35 h (33,5 h Claude, 1,5 h Thibaut) pour 35,3 h de capacité |
| Vélocité de référence | S24 19, S25 17, S26 29, S27 13, S28 13 |
