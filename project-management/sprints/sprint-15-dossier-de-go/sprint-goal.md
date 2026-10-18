# Sprint 15 : Dossier de GO (sécurité, performance, prix B2B, décision d'ouverture)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 15 |
| Début | 2027-01-08 (revue et rétro du sprint 14 ; démarrage effectif le 2027-01-11) |
| Fin | 2027-01-22 |
| Durée | 10 jours ouvrés (2027-01-11 au 2027-01-22) |
| Capacité | 35 points engagés (décision D1), hors US-052 (3 points, tâches [OPS] de Thibaut) ; 73,5 h décomposées, dont 1,5 h calendaires (72 h de charge), contre 66 h au sprint 14 (32 points tenus avec trois développeurs en parallèle) |
| Branche | `feature/sprint-15-dossier-de-go` |

## Sprint Goal

> « Fermer la sécurité et la performance de la démo, afficher les prix négociés B2B (paliers et prix de la société), mettre à jour les plugins et les hôtes de confiance, et remettre à Thibaut un dossier de GO complet pour décider l'ouverture publique. »

## Sprint Backlog

| ID | Titre | Points | MoSCoW | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| US-139 | Plugins à jour dans la démo | 2 | Must | 4 h | ready-for-dev |
| US-140 | Hôtes de confiance indépendants du domaine public Railway | 1 | Must | 3,5 h (dont 0,5 h calendaire) | ready-for-dev |
| US-141 | Nettoyage des caches de test | 1 | Should | 3,5 h | ready-for-dev |
| US-078 | SEO de base et vitesse d'affichage mobile | 5 | Should | 10 h | ready-for-dev |
| US-081 | Chaîne d'approvisionnement : scan d'image, SBOM, empreintes | 5 | Should | 10 h | ready-for-dev |
| US-082 | Durcissement applicatif : en-têtes, fichiers servis, formulaire, correctifs | 5 | Should | 10 h | ready-for-dev |
| US-083 | Relire la licence du plugin MCP tiers | 1 | Should | 2,5 h (dont 0,5 h calendaire) | ready-for-dev |
| US-085 | Revue de sécurité de réouverture et dossier de GO | 2 | Must | 4 h (dont 0,5 h de Thibaut) | ready-for-dev |
| US-089 | Accueil connecté sans doublon | 5 | Must | 10 h | ready-for-dev |
| US-090 | Paliers de prix et prix négocié sur la fiche produit | 5 | Must | 10 h | ready-for-dev |
| US-091 | Paliers et prix négocié au catalogue et au panier | 3 | Must | 6 h | ready-for-dev |

Total : 35 points, 72 tâches, 73,5 h. Report : US-052 (ouverture publique, 3 points) entre au sprint comme story ; ses tâches [OPS] (T-052-03 à T-052-07) sont celles de Thibaut, hors capacité de développement, et ne se jouent que sur son GO explicite après le dossier d'US-085.

Ordre de sacrifice (détail et charges restantes dans [tasks/README.md](./tasks/README.md)) : les Should d'abord (US-078, US-081, US-082, US-083, US-141) ; les Must ne sont pas sacrifiables.

## Décisions de Thibaut (2026-10-18)

- **D1** : périmètre complet, 35 points (≈ 37 annoncés avec les arrondis du roadmap, 38 avec US-052), le sprint 14 ayant tenu 32 points avec trois développeurs. Ordre de sacrifice : Should d'abord (US-078, US-081, US-082, US-083, US-141), Must non sacrifiables. Après tous les sacrifices, ≈ 37 h restent : le Scrum Master le signale dès le premier retard et la décision de couper une Must revient à Thibaut. Toute story sacrifiée devient un risque résiduel daté dans le dossier de GO.
- **D2** (US-090, US-091) : prix négocié. Tableau des paliers du plugin tier-pricing sur la fiche, au catalogue et au panier, PLUS une règle de prix propre à une société de démonstration (ex. ACME), présentée comme « votre prix négocié ». Aucun nouveau plugin. Le plugin gère des règles par canal, groupe de clients, produit, variante et catégorie, mais pas par société : la société de démo s'appuie sur son groupe de clients, ce qui est documenté (T-090-01, T-090-07).
- **D3** : domaine du site `thibaut-monier.fr` (réservé chez Hostinger le 2026-10-18), démo sur `sylius-b2b.thibaut-monier.fr` (CNAME et `DEMO_PUBLIC_HOST` posés, propagation DNS en cours). La bascule `CANONICAL_IS_PUBLIC_HOST`, la correction des hôtes de confiance (US-140) et le dossier de GO (US-085) en tiennent compte. Catalogue : `customer-asset-management` reste « À venir » (24 plugins) ; brun `#8a3800` validé.
- **D4** : rappels. Tests via `bin/run-tests` (le seuil `MIN_FREE_GB` n'est jamais abaissé). Rendu réel vérifié, `bin/measure-mobile` avec `PUPPETEER_MODULE=$HOME/.npm/_npx/eb7a23b7405e0ca3/node_modules/puppeteer` et `PUPPETEER_EXECUTABLE_PATH=/Applications/Chromium.app/Contents/MacOS/Chromium` (documenté par T-141-04, action de rétro). Aucun détail exploitable dans les documents (dépôt public). `main` est protégée : PR et contrôles requis, attendre la fin du run CI avant le merge.
- **Stories nouvelles** (format d'US-069) : US-139 (plugins quick-order ^1.2.0 et punchout ^1.2.1, retrait de la surcharge temporaire), US-140 (incident du 2026-10-18 : `TRUSTED_HOSTS` construit depuis `RAILWAY_PUBLIC_DOMAIN`), US-141 (`bin/clean-test-caches`, action de rétro du sprint 14).

## Choix du Tech Lead

- **Ordre d'exécution** : US-139 d'abord (plugins à jour, `composer.lock`), puis trois pistes parallèles qui ne partagent pas de fichiers : (1) prix et accueil : US-090 → US-091 et US-089 ; (2) infra : US-082 → US-081 ; (3) petites stories : US-140, US-141, US-083, puis US-078 après US-090 (même gabarit de fiche). US-085 en dernier.
- **US-090 et le plugin** : le plugin tier-pricing sait déjà afficher un tableau sur la fiche et des prix conditionnels au panier ; la première tâche (T-090-01) établit pourquoi la démo ne les montre pas avant d'écrire un gabarit.
- **US-082 et US-081** : aucun détail exploitable dans les tâches ni la documentation (dépôt public) ; les éléments détaillés restent dans l'audit privé.
- **US-085** : un risque résiduel (story sacrifiée) est consigné avec une date de revue ; la recommandation est NO-GO tant qu'un bloquant reste ouvert.

## Dépendances

- US-139 avant la montée de dépendances d'US-082 (`composer.lock`).
- US-090 avant US-091 et avant le bloc de promotions négociées d'US-089 (T-089-04).
- US-090 (gabarit de fiche) avant US-078 (images et en-tête de la fiche).
- US-082 (serveur, en-têtes) avant US-081 (`Dockerfile`, `ci.yaml`) : mêmes fichiers.
- US-140 avant le dossier : les deux domaines doivent répondre (T-140-06, Thibaut).
- US-085 après toutes les autres stories ; US-052 (bascule) après le GO de Thibaut.

## Risques

| Risque | Mitigation |
|--------|------------|
| 35 points / 72 h de charge | Ordre de sacrifice, trois développeurs en parallèle, point d'étape le 2027-01-15 |
| Le plugin tier-pricing n'a pas de règle par société | Groupe de clients de la société de démo, documenté (D2) ; aucun nouveau plugin |
| En-têtes Cross-Origin qui cassent images ou scripts | Suite de fumée complète et rendu réel après changement (T-082-07) |
| Conflits sur `composer.lock`, `Dockerfile`, `ci.yaml` | Un seul worker à la fois ; US-139 puis US-082 puis US-081 |
| Disque saturé (rétro S14) | `df -h .` au démarrage, `bin/clean-test-caches`, seuil jamais abaissé |
| Détail exploitable dans un document public (US-081, US-082, US-085) | Conclusions seulement, relecture dédiée en revue |
| Propagation DNS et hôtes de confiance (D3) | US-140 corrige l'IaC, Thibaut applique et vérifie les deux domaines |
| Story sacrifiée qui bloque le GO | Risque résiduel daté au dossier, acceptation explicite de Thibaut |
| Licence du plugin MCP incompatible avec une démo publique | US-083 avant le dossier ; décision de Thibaut |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation explicite. Aucune commande Railway par Claude (T-140-06 et US-052 sont à Thibaut). Pas de lecture de `.env` ni de secrets. Aucun commit sans demande. `git add` par chemins explicites. Chaque worker rend le bloc de rapport de fin (≤ 100 mots, [tasks/README.md](./tasks/README.md)).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS (`src tests`) et PHPStan verts
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié (pas seulement la configuration)
- [ ] Aucun secret ni détail exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge

## Checklist pré-sprint

- [ ] Sprint 14 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Branche `feature/sprint-15-dossier-de-go` active ; `git fetch` et branche vérifiés
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`) ; Docker PHP 8.4
- [ ] Puppeteer et Chromium disponibles pour `bin/measure-mobile` (variables d'environnement de D4)
- [ ] `df -h .` : au moins 5 Go libres ; `var/cache-*` des workers précédents supprimés
- [ ] D1 à D4 lues par l'équipe ; ordre de sacrifice accepté
- [ ] Actions de Thibaut identifiées : T-140-06, T-083-04, T-085-05, puis T-052-03 à T-052-07 (sur son GO)

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-01-11 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-01-11 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-01-15 | 30 min |
| Affinage Backlog | 2027-01-19 | 1 h |
| Sprint Review (dossier de GO) | 2027-01-22 (matin) | 1 h |
| Rétrospective | 2027-01-22 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
