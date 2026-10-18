# Sprint 14 : Bloquants d'ouverture (accessibilité, sécurité, signaux de prototype)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 14 |
| Début | 2026-12-25 (férié : démarrage effectif le 2026-12-28) |
| Fin | 2027-01-08 |
| Durée | 9 jours ouvrés (les 2026-12-25 et 2027-01-01 sont fériés) |
| Capacité | 32 points engagés (décision D1) ; 66 h décomposées, dont 3 h calendaires (63 h de charge), contre une vélocité récente de 10-15 points (≈ 30-35 h) |
| Branche | `feature/sprint-14-bloquants-ouverture` |

## Sprint Goal

> « Lever les bloquants d'ouverture publique de la démo : une boutique utilisable au clavier et sur mobile, lisible (contrastes AAA), dotée d'un pied de page B2B, avec une branche principale protégée, une boîte de mails limitée aux données de démonstration et plus aucun signal de prototype, sans comparaison avec Sylius Plus. »

## Sprint Backlog

| ID | Titre | Points | MoSCoW | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| US-070 | Plugin punchout : traduire le titre et le fil d'Ariane des acheteurs | 1 | Should | 2,5 h (dont 0,5 h calendaire) | ready-for-dev |
| US-071 | Mise en page mobile sans débordement ni cible trop petite | 3 | Must | 6 h | ready-for-dev |
| US-072 | Structure sémantique du thème : repères, lien d'évitement et annonces | 5 | Must | 10 h | ready-for-dev |
| US-073 | Contrastes AAA et jetons de couleur uniques | 3 | Must | 6 h | ready-for-dev |
| US-074 | Pied de page dédié à la démo B2B | 3 | Must | 6 h | ready-for-dev |
| US-075 | Connexion : comptes de démo en premier, groupés par société | 2 | Should | 4 h | ready-for-dev |
| US-076 | Barre de démo sur une ligne, dans la langue du visiteur | 2 | Should | 4 h | ready-for-dev |
| US-077 | Libellés accessibles du cœur en français | 2 | Should | 4 h | ready-for-dev |
| US-079 | Sécurité avant ouverture : exposition, configuration, branche protégée | 3 | Must | 7 h (dont 2 h de Thibaut) | ready-for-dev |
| US-080 | Boîte de courriels de démo limitée aux mails des fixtures | 2 | Must | 4 h | ready-for-dev |
| US-086 | Exemple de commande rapide avec de vraies références | 2 | Must | 4,5 h (dont 0,5 h calendaire) | ready-for-dev |
| US-087 | Signaux « prototype » : 23/24 plugins, clés brutes, libellés anglais | 3 | Must | 6 h | ready-for-dev |
| US-104 | Retirer l'encart « Sylius Plus » de la page Plugins | 1 | Must | 2 h | ready-for-dev |

Total : 32 points, 72 tâches, 66 h. Report : US-052 (ouverture publique) reste `carried-over` ; ses tâches [OPS] (T-052-03 à T-052-07) sont celles de Thibaut, hors capacité (GO d'ouverture visé fin du sprint 15, US-085).

Ordre de sacrifice (détail et charges restantes dans [tasks/README.md](./tasks/README.md)) : les Should d'abord (US-070, US-077, US-076, US-075), puis les tâches de finition.

## Décisions de Thibaut (2026-10-18)

- **D1** : périmètre complet, 32 points, malgré une vélocité récente de 10-15 points. Risque assumé, réponse : l'ordre de sacrifice ci-dessus. Même après tous les sacrifices, la charge restante (≈ 46 h) dépasse la capacité historique : le Scrum Master le signale dès le premier retard.
- **D2** (US-104) : l'encart « Sylius Plus » de la page Plugins (traductions `translations/plugins_plus.*`, US-046) est remplacé par une phrase factuelle neutre : « Ces plugins s'installent sur Sylius 2.3. » (EN : « These plugins install on Sylius 2.3. »). Aucune comparaison.
- **D3** (US-079) : la protection de la branche `main` est appliquée par Claude via `gh api` : PR obligatoire, contrôles requis « Quality and smoke tests » et « Docker image » (noms vérifiés dans `.github/workflows/ci.yaml`, jobs `name:`), pas de revue tierce obligatoire, pas de force-push. La vérification de l'exposition des services Railway et la relecture des fichiers de configuration versionnés sont des tâches [OPS] de Thibaut ; Claude ne prépare qu'une checklist sans détail exploitable. Le dépôt est public : aucune story, tâche, PR ni rapport ne contient de détail exploitable.
- **D4** : stories inter-dépôts. US-070 se fait dans `/Users/tmonier/Projects/sylius_plugins/sylius-punchout-cxml-plugin` ; US-086 dans `/Users/tmonier/Projects/sylius_plugins/sylius-quick-order-plugin` (placeholder `SKU-001…` de `templates/shop/quick_order/_form.html.twig`, ligne 9) : option de configuration avec défaut neutre côté plugin, plus valeur adaptée (références réelles de la démo) côté démo. Une PR par dépôt de plugin, livrée avec la démo, puis release de patch du plugin si la démo en dépend. Thibaut ne soumet à Packagist que les NOUVEAUX paquets : les tags existants se mettent à jour seuls.
- **D5** : leçons des rétros. Vérifier le RENDU réel des pages, pas seulement la configuration. Tests via `bin/run-tests` : `TEST_DB_NAME=<db> WKHTMLTOPDF_BINARY="$PWD/tests/Fixtures/bin/fake-wkhtmltopdf" bin/run-tests …`. ECS sur `src tests`. Débordement mobile mesuré en émulation réelle : Chrome headless impose une largeur minimale d'environ 500 px, donc Puppeteer avec `setViewport` en `isMobile`, mesure du `scrollWidth` du document à 375 et à 320 px.

## Choix du Tech Lead

- **Ordre d'exécution** : les jetons de couleur (US-073) et la structure du layout (US-072) d'abord : US-071, US-074, US-075 et US-076 modifient les mêmes feuilles de style et gabarits (voir [sprint-dependencies.md](./sprint-dependencies.md)).
- **URL du site et du contact** : paramètres de configuration validés en https (créés par US-074, réutilisés par US-076), liens masqués sans URL (US-068 et US-069 restent au backlog).
- **Mesure mobile** : un script Puppeteer unique (T-071-01) sert aussi à US-076 ; la CI n'est chargée de l'exécuter que si Node y est disponible, sinon un test structurel PHP garde la règle et le script reste un contrôle de rendu local.
- **US-070 et US-087** : le titre punchout reste dans US-070 (non dupliqué dans US-087). Si US-070 est sacrifiée, la clé brute reste visible dans l'admin et le test « aucune clé brute » de l'admin l'ignore explicitement, avec trace dans la revue.
- **US-079** : l'écriture de la protection de branche est une action externe sur GitHub ; elle est jouée par Claude sur la confirmation de Thibaut au moment de la tâche (D3), précédée d'une lecture de l'état actuel.

## Dépendances

- US-072 (layout) avant US-074 (pied de page) ; US-073 (jetons) avant US-071, US-075, US-076.
- US-074 (configuration des URL) avant le lien contact d'US-076.
- US-104 avant le statut des plugins d'US-087 (même page Plugins).
- US-070 avant la dernière vérification d'US-087 (clés brutes de l'admin).
- US-086 : PR plugin quick-order mergée et release de patch avant la montée de version dans la démo (sinon surcharge temporaire).

## Risques

| Risque | Mitigation |
|--------|------------|
| 32 points pour une vélocité de 10-15 | Ordre de sacrifice, point d'étape au jour 4 (mi-sprint) |
| Conflits de CSS entre US-071, 073, 075, 076 | Ordre fixé, jetons d'abord, un seul worker à la fois sur `assets/shop/styles` |
| Mesure mobile fausse (Chrome headless ≥ 500 px) | Puppeteer `isMobile`, mesure à 375 et 320 px (D5) |
| Faux positif de rendu : config bonne, page mauvaise | Vérification du rendu réel de chaque page touchée avant clôture |
| Détail exploitable dans un document public (US-079) | Checklist générique, aucune valeur ni nom de service, relue en revue |
| Protection de branche bloquant les PR du sprint (contrôles « en attente ») | Lecture des noms de jobs, test sur une PR avant d'activer, contournement écarté |
| Patch du plugin non publié à temps | Surcharge temporaire dans la démo, retirée après montée de version |
| Fériés (25/12, 01/01) | 9 jours ouvrés comptés, planning le 28/12 |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation explicite ; seule exception cadrée, la protection de `main` (D3). Aucune commande Railway par Claude. Pas de lecture de `.env` ni de secrets. Les dépôts de plugins sont modifiés sur une branche locale, après `git fetch` et vérification de la branche courante. Aucun commit sans demande. `git add` par chemins explicites. Chaque worker rend le bloc de rapport de fin ([tasks/README.md](./tasks/README.md)).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS (`src tests`) et PHPStan verts
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié (pas seulement la configuration)
- [ ] Aucun secret ni détail exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »)

## Checklist pré-sprint

- [ ] Sprint 13 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Branche `feature/sprint-14-bloquants-ouverture` active ; `git fetch` et branche vérifiés dans les dépôts `sylius-punchout-cxml-plugin` et `sylius-quick-order-plugin` (partir de `main` à jour)
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`) ; Docker PHP 8.4
- [ ] Puppeteer disponible pour la mesure mobile (D5) ; navigateur piloté pour les rendus
- [ ] `df -h .` : au moins 5 Go libres ; `var/cache-*` des workers précédents supprimés
- [ ] D1 à D5 lues par l'équipe ; ordre de sacrifice accepté
- [ ] `gh auth status` valide (US-079)
- [ ] US-104 : décision D2 consignée dans la story avant toute modification
- [ ] Actions de Thibaut identifiées : T-079-05, T-079-06 (vérifications), T-070-04, T-086-06 (PR et release de patch des plugins), T-052-03 à T-052-07 (reportées)

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2026-12-28 | 1 h |
| Sprint Planning Part 2 (Comment) | 2026-12-28 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-01-04 (le 2027-01-01 est férié) | 30 min |
| Affinage Backlog | 2027-01-05 | 1 h |
| Sprint Review | 2027-01-08 (matin) | 1 h |
| Rétrospective | 2027-01-08 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
