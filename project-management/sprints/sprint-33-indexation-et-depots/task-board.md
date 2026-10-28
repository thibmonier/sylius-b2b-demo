# Task Board : Sprint 033 (indexation des pages vitrine et dépôts de plugins)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut, du disque ou d'un déploiement | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

Total décomposé : **45 tâches, 35 h** (13 points). État au planning (2026-10-28) : **2 ✅, 33 🔲, 10 📅**. Les 📅 attendent le GO de la liste blanche (T-207-03), le retour du disque au-dessus de 5 Go (tests, rendus, suite complète) ou le déploiement de `main` (vérifications de production, déclaration du sitemap).

Règle : une tâche passe en ✅ dans le commit qui la livre. **Rouge prouvé (règle 27)** : une story de code n'est comptée « rouge prouvé » que si le commit de ses tests rouges cite l'échec observé de chaque test.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-33-04 | Transverse | OPS | **Machine et verrou des tests** : `bin/check-disk` annonce les runs autorisés, `bin/run-tests` en file d'attente … | 1,25 h | Orchestrateur |
| T-TECH-33-05 | Transverse | OPS | **Rendu sans barre de débogage** pour les contrôles pa11y … | 0,75 h | Orchestrateur |
| T-TECH-33-06 | Transverse | OPS | **Script puppeteer de mesure** à 320 et 375 px versionné et documenté | 0,5 h | Orchestrateur |
| T-TECH-33-08 | Transverse | OPS | **Build des assets frais** : avertissement quand `public/build` est plus ancien que `assets/` … | 0,5 h | Orchestrateur |
| T-TECH-33-10 | Transverse | REV | Tenue du registre des décisions et des GO, règles 26 et 28 appliquées … | 0,5 h | Orchestrateur |
| T-TECH-33-11 | Transverse | OPS | **Disque et machine** : relevés avant chaque lot ; aucun test sous 5 Go, aucune suite complète sous 6 Go … | 0,25 h | Orchestrateur |
| T-TECH-33-12 | Transverse | OPS | ECS, PHPStan max (dépôt entier) et Rector rejoués avant chaque push ; CI verte avant le merge … | 0,5 h | Orchestrateur |
| T-TECH-33-14 | Transverse | TEST | **Capture à 1280 px relue avant la PR** : home connectée et page vitrine listée … | 0,5 h | Orchestrateur |
| T-TECH-33-15 | Transverse | DOC | Revue et rétrospective du sprint 33, puis clôture | 0,75 h | Orchestrateur |
| T-207-15 | US-207 | REV | Revue (ton neutre, exclusions, aucun détail sensible) et table « scénario → test ou preuve » | 0,25 h | Claude |
| T-209-01 | US-209 | DOC | Cadrage et **relevé de départ sur les branches par défaut distantes** des 29 dépôts | 1,5 h | Claude |
| T-209-02 | US-209 | TEST | Tests rouges du script, **rouge prouvé (règle 27)**, sur dépôts de fixtures | 1,5 h | Claude |
| T-209-03 | US-209 | OPS | **Tranche (a) workflows** dans `bin/plugin-supply-chain` (SHA, permissions, checkout, `pull_request_target`, zizmor, Dependabot) | 2,5 h | Claude |
| T-209-04 | US-209 | OPS | **Tranche (b) Composer** (audit, politique, `allow-plugins`, revue du verrou) ⚠️ sacrifice n°1 | 2 h | Claude |
| T-209-05 | US-209 | DOC | `docs/chaine-approvisionnement-plugins.md` | 0,5 h | Claude |
| T-209-06 | US-209 | OPS | **Pilote** sur `sylius-quick-order-plugin` : PR, CI verte, fusion | 1,5 h | Claude |
| T-209-07 | US-209 | OPS | Lot 1 (7 dépôts) : règle 29, `apply`, `check`, PR, CI, fusion si verte | 1 h | Claude |
| T-209-08 | US-209 | OPS | Lot 2 (7 dépôts) | 1 h | Claude |
| T-209-09 | US-209 | OPS | Lot 3 (7 dépôts) | 1 h | Claude |
| T-209-10 | US-209 | OPS | Lot 4 (7 dépôts, packs compris) | 1 h | Claude |
| T-209-11 | US-209 | OPS | Remontée à Thibaut des dépôts à CI rouge préexistante, sans CI ou à branche protégée | 0,5 h | Claude |
| T-209-12 | US-209 | DOC | Rapport final : 29 dépôts conformes ou écarts justifiés ; relevé joint à la PR de la démo | 1 h | Claude |
| T-209-13 | US-209 | TEST | Vérification finale sur les branches par défaut distantes des 29 dépôts ; PHPStan, ECS, Rector | 0,75 h | Claude |
| T-209-14 | US-209 | DOC | Mémoire du socle qualité des plugins mise à jour | 0,25 h | Orchestrateur |
| T-209-15 | US-209 | REV | Revue (aucun tag, rien de sensible, ton neutre, règle 29) et table « scénario → test ou preuve » | 0,5 h | Claude |

## 🔄 En cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 📅 En attente (GO de Thibaut, disque ou déploiement)

| ID | US | Type | Tâche | Estimation | Réalisé par | Attend |
|----|-----|------|-------|------------|-------------|--------|
| T-207-02 | US-207 | TEST | État de référence « avant » : tests existants de la table lancés dès 5 Go | 0,25 h | Claude | disque |
| T-207-13 | US-207 | OPS | Image reconstruite ; production le jour du déploiement (`robots.txt`, en-têtes, sitemap, runbook, validateur, pa11y, largeurs) | 0,75 h | Claude | déploiement, T-207-03 |
| T-207-14 | US-207 | OPS | 📅 Thibaut déclare le sitemap dans Google Search Console | 0,25 h | Thibaut | déploiement |
| T-TECH-33-03 | Transverse | OPS | 📅 Thibaut libère le disque (cible 10 Go libres), commandes en chemins absolus précédées d'un `du -sh` | 0,25 h | Thibaut | Thibaut |
| T-TECH-33-07 | Transverse | TEST | 📅 Contrôle connecté d'US-203 sur image reconstruite (reliquat T-TECH-32-07) | 0,75 h | Claude | disque |
| T-TECH-33-09 | Transverse | OPS | 📅 Images produit locales et capture du panier par `bin/render-page` (reliquat T-TECH-32-04) ⚠️ | 1 h | Orchestrateur | disque |
| T-TECH-33-13 | Transverse | TEST | 📅 Suite complète locale avant le push de la PR de sprint, ou attente explicite dite dans la PR | 0,5 h | Orchestrateur | disque (6 Go) |

## 🟡 Faites avec écart

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-33-01 | Transverse | DOC | **Règle 27 « rouge prouvé »** de la consigne de worker (faite au planning) | 0,25 h | Orchestrateur |
| T-TECH-33-02 | Transverse | DOC | **Règle 28 : table « invariants de test » au cadrage**, table d'US-207 préremplie (faite au planning) | 0,25 h | Orchestrateur |
| T-207-01 | US-207 | DOC | Cadrage : table « invariants de test » confirmée (règle 28), liste blanche proposée, canal B2C, hôte canonique, exclusions … | 0,75 h | Claude |
| T-207-03 | US-207 | OPS | 📅 Thibaut confirme la **liste blanche** et l'**activation** ; GO noté au registre avant d'activer | 0,25 h | Thibaut | Thibaut (cadrage T-207-01) |
| T-207-04 | US-207 | TEST | Tests rouges, **rouge prouvé (règle 27)**, commités seuls : page listée, sitemap, données structurées, pages privées, route non listée, ton neutre … | 1,25 h | Claude |
| T-207-05 | US-207 | BE | Liste blanche unique, `NoIndexSubscriber` (refus par défaut), `RobotsController`, réglage d'activation (désactivé par défaut) | 1,25 h | Claude |
| T-207-06 | US-207 | BE | `sitemap.xml` : liste blanche, `hreflang` FR/EN et `x-default`, hôte canonique, référencé par `robots.txt` | 1 h | Claude |
| T-207-07 | US-207 | FE-WEB | Titres, descriptions, canonical, Open Graph et `hreflang` par page (FR et EN, charte, ton neutre) | 1 h | Claude |
| T-207-08 | US-207 | BE | Données structurées JSON-LD : `WebSite`, `Person`, `SoftwareSourceCode` ; exclusions | 0,75 h | Claude |
| T-207-09 | US-207 | TEST | Tests existants de la table adaptés (ligne de registre avant), fixture du routeur, `bin/verify-public-mode` | 0,5 h | Claude |
| T-207-10 | US-207 | DOC | Documents (`ouverture-publique`, `durcissement-demo`, `seo-et-vitesse`, `dossier-de-go`), runbook, phrase de la page Architecture | 0,75 h | Claude |
| T-207-11 | US-207 | TEST | Suite ciblée, PHPStan, ECS, Rector, lints, pa11y local, `bin/measure-mobile`, JSON-LD validé localement | 0,75 h | Claude | disque |
| T-207-12 | US-207 | DOC | Captures : constat que le rendu est inchangé ; `docs/captures-vitrine.md` si une page change | 0,25 h | Claude | disque |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 13 (US-209 8, US-207 5) |
| Tâches | 45 (US-207 15, US-209 15, transverses 15) |
| Heures | 35 h (34,25 h Claude, 0,75 h Thibaut) pour 35,3 h de capacité, marge de 1,05 h |
| Ordre de sacrifice (registre) | T-TECH-33-09 (négociable), puis US-209 tranche (b), puis US-209 tranche (a) ; US-207 protégée |
| Rouge prouvé | à relever à la clôture : US-207 et US-209 (cible : 2 stories sur 2) |
