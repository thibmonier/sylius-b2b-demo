# Task Board : Sprint 032 (preuve technique, architecture et script d'images)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut, du disque ou d'un déploiement | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

Total décomposé : **41 tâches, 28 h** (9 points). État au planning (2026-10-28) : **2 ✅, 16 🔲, 23 📅**. Les 📅 attendent le GO d'une maquette (T-205-04, T-206-04 et le Twig qui suit), le retour du disque au-dessus de 5 Go (tests, rendus, suite complète), ou le déploiement de `main` (vérifications de production).

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-32-01 | Transverse | OPS | **Machine et verrou des tests** : `bin/check-disk` annonce les runs autorisés, `bin/run-tests` en file d'attente … | 1,25 h | Orchestrateur |
| T-TECH-32-03 | Transverse | OPS | **Build des assets frais** : avertissement quand `public/build` est plus ancien que `assets/` … | 0,5 h | Orchestrateur |
| T-TECH-32-08 | Transverse | REV | Tenue du registre des décisions et des GO de Thibaut, règle 26 appliquée … | 0,75 h | Orchestrateur |
| T-TECH-32-09 | Transverse | OPS | **Disque et machine** : relevés avant chaque lot ; aucun test sous 5 Go, aucune suite complète sous 6 Go … | 0,5 h | Orchestrateur |
| T-TECH-32-10 | Transverse | OPS | ECS, PHPStan max (dépôt entier) et Rector rejoués avant chaque push ; CI verte avant le merge … | 0,75 h | Orchestrateur |
| T-TECH-32-12 | Transverse | TEST | **Capture à 1280 px relue avant la PR** : home connectée, « Preuve technique », « Architecture » … | 0,5 h | Orchestrateur |
| T-TECH-32-13 | Transverse | DOC | Revue et rétrospective du sprint 32, puis clôture | 1 h | Orchestrateur |
| T-062-01 | US-062 | DOC | ✅ Cadrage : fetcher, manifeste et script relus ; les quatre mineurs confirmés ou écartés contre le code … | 0,25 h | Claude |
| T-062-02 | US-062 | TEST | ✅ Tests rouges, commités seuls : image conforme, manifeste modifié, 429, image hors limites, `chunked` … | 0,5 h | Claude |
| T-062-03 | US-062 | BE | ✅ Correctifs du fetcher : pixels, `Content-Length`, 429, image principale conservée, corps `chunked` plafonné … | 0,75 h | Claude |
| T-205-01 | US-205 | DOC | ✅ Cadrage : `proof.html.twig`, `_fact.html.twig`, `ProofController` ; invariants de `ProofPageTest` … | 0,5 h | Claude |
| T-205-02 | US-205 | TEST | ✅ État de référence « avant » : `ProofPageTest` relu puis lancé dès 5 Go ; captures de production … | 0,25 h | Claude |
| T-205-03 | US-205 | FE-WEB | Maquette Claude Design « Preuve technique » (bureau 1440 px et téléphone 390 px), vraies données … | 1,25 h | Orchestrateur |
| T-206-01 | US-206 | DOC | Cadrage : `architecture.html.twig`, invariants d'`ArchitecturePageTest`, **canal B2C**, absence de bloc de code … | 0,75 h | Claude |
| T-206-02 | US-206 | TEST | État de référence « avant » : `ArchitecturePageTest` relu puis lancé dès 5 Go ; captures de production … | 0,25 h | Claude |
| T-206-03 | US-206 | FE-WEB | Maquette Claude Design « Architecture » (bureau 1440 px et téléphone 390 px), sommaire d'ancres, six visuels … | 2 h | Orchestrateur |

## 🔄 En cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 📅 En attente (GO de Thibaut, disque ou déploiement)

| ID | US | Type | Tâche | Estimation | Réalisé par | Attend |
|----|-----|------|-------|------------|-------------|--------|
| T-205-04 | US-205 | OPS | 📅 Thibaut valide la maquette « Preuve technique » : **aucun Twig avant son GO**, noté au registre | 0,5 h | Thibaut | maquette T-205-03 |
| T-205-05 | US-205 | TEST | ✅ Tests rouges, commités seuls : chiffres lus dans le rapport, faits groupés, pastilles, « non disponible » … | 0,5 h | Claude | GO T-205-04 |
| T-205-06 | US-205 | FE-WEB | ✅ Gabarit et styles (`_proof.scss`), chiffres clés, groupes, tableau en cartes à 320 px … | 1,75 h | Claude | GO T-205-04 |
| T-205-07 | US-205 | DOC | ✅ Textes FR et EN (domaine `proof`), charte `PublicTextCharter` … | 0,25 h | Claude | GO T-205-04 |
| T-205-08 | US-205 | TEST | 🔄 Suite ciblée, PHPStan, ECS, Rector, lints, pa11y local, `bin/measure-mobile` … | 0,75 h | Claude | disque |
| T-205-09 | US-205 | DOC | 📅 Captures `docs/images/proof-*` par `bin/render-page`, section de `docs/captures-vitrine.md` | 0,5 h | Claude | disque |
| T-205-10 | US-205 | TEST | Image reconstruite, conteneur neuf ; production le jour du déploiement, lecture seule … | 0,5 h | Claude | déploiement |
| T-205-11 | US-205 | REV | Revue (ton neutre, parité FR et EN, contenu inchangé) et table « scénario → test ou preuve » | 0,25 h | Claude | déploiement |
| T-206-04 | US-206 | OPS | 📅 Thibaut valide la maquette « Architecture » : **aucun Twig avant son GO**, noté au registre | 0,5 h | Thibaut | maquette T-206-03 |
| T-206-05 | US-206 | TEST | ✅ Tests rouges, commités seuls : sommaire d'ancres, un visuel par section, focus au clavier, chiffres du rapport … | 1 h | Claude | T-206-04 (GO) |
| T-206-06 | US-206 | FE-WEB | ✅ Coquille, introduction, sommaire, « vue d'ensemble » (schéma réhabillé) et « couches » (`_architecture.scss`) | 2,25 h | Claude | T-206-04 (GO) |
| T-206-07 | US-206 | FE-WEB | ✅ Sections « flux », « exploitation », « qualité » et « sécurité » (générale) | 1,75 h | Claude | T-206-04 (GO) |
| T-206-08 | US-206 | DOC | ✅ Textes FR et EN (domaine `architecture`), charte `PublicTextCharter` … | 0,5 h | Claude | T-206-04 (GO) |
| T-206-09 | US-206 | TEST | 📅 Suite ciblée, PHPStan, ECS, Rector, lints, pa11y local, `bin/measure-mobile` … | 0,75 h | Claude | disque |
| T-206-10 | US-206 | DOC | 📅 Captures `docs/images/architecture-*` par `bin/render-page`, section de `docs/captures-vitrine.md` | 0,5 h | Claude | disque |
| T-206-11 | US-206 | TEST | Image reconstruite, conteneur neuf ; production le jour du déploiement, lecture seule … | 0,5 h | Claude | déploiement |
| T-206-12 | US-206 | REV | Revue (ton neutre, parité FR et EN, aucun détail de sécurité ajouté, `noindex`) et table « scénario → test ou preuve » | 0,25 h | Claude | déploiement |
| T-062-04 | US-062 | TEST | 🔄 Suite ciblée, PHPStan, ECS, Rector ; `bin/proof-rebase` … | 0,25 h | Claude | disque |
| T-062-05 | US-062 | REV | Revue (aucun appel réseau dans les tests, comportement nominal inchangé) et table « scénario → test ou preuve » | 0,25 h | Claude | disque |
| T-TECH-32-02 | Transverse | OPS | 📅 Thibaut libère le disque (cible 10 Go libres), commandes en chemins absolus précédées d'un `du -sh` | 0,25 h | Thibaut | Thibaut |
| T-TECH-32-04 | Transverse | OPS | 📅 Images produit locales et capture du panier par `bin/render-page` (clôt la DoD de T-TECH-31-04) ⚠️ | 1 h | Orchestrateur | disque |
| T-TECH-32-07 | Transverse | TEST | 📅 Reliquats du sprint 31 : contrôle connecté d'US-203 sur image reconstruite (T-203-14 et 15) | 0,75 h | Claude | disque |
| T-TECH-32-11 | Transverse | TEST | 📅 Suite complète locale avant le push de la PR de sprint, ou attente explicite dite dans la PR | 0,5 h | Orchestrateur | disque (6 Go) |

## 🟡 Faites avec écart

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-32-05 | Transverse | DOC | **Règle 26** de la consigne de worker : registre avant toute PR hors sprint, les workers relisent le registre avant d'attendre (faite au planning) | 0,25 h | Orchestrateur |
| T-TECH-32-06 | Transverse | OPS | **Remesure de production** après le déploiement : LCP de la home, de la fiche et de la liste sous le seuil de 1 456 ms, pa11y de la page « Plugins » (faite le 2026-10-28, avant le planning) | 0 h | Orchestrateur |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 9 (US-206 5, US-205 3, US-062 1) |
| Tâches | 41 (US-206 12, US-205 11, US-062 5, transverses 13) |
| Heures | 28 h (26,75 h Claude, 1,25 h Thibaut) pour 26,9 h de capacité, marge de 0,15 h |
| Ordre de sacrifice (proposition) | T-TECH-32-04 (négociable), puis US-062, puis US-205 ; US-206 protégée |
