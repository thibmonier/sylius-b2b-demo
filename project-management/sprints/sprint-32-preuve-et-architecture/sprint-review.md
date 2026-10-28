# Sprint Review - Sprint 32 (Preuve technique, Architecture et script d'images)

> Préparée le 2026-10-28, le jour du sprint, avant la fusion de la PR #127 (brouillon, CI verte sur 6 contrôles). Ce qui ne peut se vérifier qu'après la fusion et le déploiement de `main` est indiqué tel quel dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 32, `sprint-32-preuve-et-architecture` |
| Période | 2026-10-28 (planifié et terminé le même jour ; fin au plus tard prévue le 2026-11-10) |
| Points engagés | **9** (US-206 5, US-205 3, US-062 1) |
| Points en done | **9** (code livré en revue sur la branche ; contrôles de production en attente) |
| PR de la démo | #127 (sprint, brouillon, 55 fichiers, +3 814 / -554), #126 (vérifications de production du sprint 31, fusionnée avant le planning) |
| Maquettes | Canevas Claude Design unique, version 22 : pages « Preuve technique » et « Architecture », validées « tel quel » par Thibaut |
| Mode | Orchestrateur (Claude) et workers w205, w206 et le worker d'US-062, sur la branche du sprint |

## Sprint Goal

> « Donner aux pages « Preuve technique » et « Architecture » la clarté et la charte de l'accueil, avec des maquettes validées avant le code, et solder les mineurs du script d'images, sans lancer de test ni de rendu tant que le disque ne le permet pas. »

**Atteint dans le code, à confirmer après déploiement.** Les deux pages suivent des maquettes validées avant tout Twig, et les mineurs du script d'images sont corrigés. Le volet « sans lancer de test ni de rendu tant que le disque ne le permet pas » a été tenu en partie seulement (voir « Incidents et écarts de processus »). Restent les contrôles qui exigent `main` déployé.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-206 Page « Architecture » | 5 | Page refondue dans la charte de l'accueil : introduction courte, sommaire de 6 ancres, sections numérotées avec tous les textes et liens actuels, 4 tuiles de chiffres, schéma en HTML/CSS (remplace le SVG) empilé sur téléphone, textes FR et EN | Tests rouges `b9fdaad`, puis corrigés `870e6c0` et `3922a13` ; code `fcacfab` ; captures `docs/images/architecture-*` (`f72462b`) |
| US-205 Page « Preuve technique » | 3 | Page refondue : 6 tuiles de synthèse, date du relevé affichée une fois, colonne Couverture masquée tant qu'aucun plugin n'a de chiffre, fiche par plugin sur téléphone ; plugin ERP gardé en ton neutre | Tests rouges `b47aa08` ; code `0349dd8` ; pa11y WCAG2AAA FR et EN à 1280 et 320 px sans problème, aucun défilement horizontal à 320, 375 et 1280 px ; captures `docs/images/proof-*` (`c8268ef`) ; relevé de preuve régénéré (`0636d98`, 318 fichiers de test) |
| US-062 Mineurs du script d'images | 1 | Limites de pixels et de `Content-Length`, gestion du 429, image principale conservée si le manifeste change, corps `chunked` plafonné ; aucun appel réseau dans les tests | Tests rouges seuls `3d8646d` ; correctifs `2104b06` |

Tests existants modifiés, chacun décidé au registre avant l'action et cité dans la PR :
- `ArchitecturePageTest`, 3 points (diagramme SVG, région défilante, taille de police du SVG), remplacés par des assertions équivalentes sur le schéma HTML ; le conflit venait du GO de Thibaut sur le schéma en HTML/CSS ;
- `ProofPageTest`, dates et colonne Couverture, en application du GO sur la maquette.

Contrôles avant la PR : la CI a joué la suite complète, verte sur les 6 contrôles (Quality and smoke tests, Docker image, Dependency audit, Image scan and SBOM, Workflow audit zizmor, GitGuardian). **Suite complète locale non lancée** (disque entre 4 et 6 Go, dit dans la PR).

## Décisions de Thibaut (2026-10-28, registre du sprint)

- Périmètre de 9 points ; sprint 33 prévu : US-207 puis US-209.
- GO des maquettes « Preuve technique » et « Architecture » (version 22) tel quel ; plugin ERP gardé sur la page de preuve, ton neutre ; ordre de sacrifice US-062, puis US-205, US-206 protégée (confirmé, jamais appliqué).
- Clôture au planning : US-208 et US-204 en done, US-203 en review.

Décision de l'orchestrateur : option B de w206 pour le schéma (HTML/CSS) et adaptation explicite d'`ArchitecturePageTest`, en application de la décision de Thibaut, notée au registre avant l'action.

## Éléments non terminés ou à confirmer

- **Après la fusion et le déploiement** : T-205-10 et T-205-11, T-206-11 et T-206-12 (pa11y et largeurs en production, revues) ; reliquats du sprint 31 : contrôle connecté d'US-203 sur image reconstruite (T-TECH-32-07), capture du panier (T-TECH-32-04), selon le disque.
- **Faites avec écart** : T-205-08 (pa11y et largeurs faits, suite complète laissée à la CI) ; T-062-04 (suite complète laissée à la CI ; preuve régénérée par `bin/proof-refresh` dans 0636d98, `bin/proof-check` à jour) ; US-205 et US-206 : tests rouges non vus rouges au sens strict (voir ci-dessous).
- **Tâches transverses non faites** : T-TECH-32-01 (verrou en file d'attente, runs autorisés affichés par `bin/check-disk`), T-TECH-32-03 (avertissement de `public/build` périmé) et T-TECH-32-04 (images produit locales, capture du panier) : aucun commit ne touche `bin/check-disk`, `bin/run-tests` ni `bin/render-page` sur la branche ; **reportées au sprint 33**.
- **Fichiers de tâches** : les statuts de T-TECH-32-08 à -13 et de T-206-02 à -04 sont à rapprocher du registre à la clôture.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Disque entre 4 et 6 Go toute la journée, swap de 2,5 à 3,7 Go | Tests et rendus retardés, suite complète laissée à la CI | Rétrospective, thème 1 |
| Verrou `var/run-tests.lock` attendu deux fois entre workers | Attente, sans run perdu | Rétrospective, thème 1 |
| Tests rouges d'US-205 : fournisseurs de données faux, corrigés dans le commit du code | Les tests n'ont pas été vus rouges au sens strict | Rétrospective, thème 2 |
| Tests rouges d'US-206 corrigés deux fois (`870e6c0`, `3922a13`) | Écart à la discipline « rouge commité seul, puis vert » | Rétrospective, thème 2 |
| Conflit maquette/test (SVG imposé par un test « non modifié ») détecté à l'implémentation par w206 | Décision et adaptation au registre en cours de sprint | Rétrospective, thème 3 |
| `bin/render-page` inclut la barre de débogage Symfony | Retirée à la main des copies pour pa11y | Rétrospective, thème 4 |
| `bin/measure-mobile` vise un serveur | Remplacé par un script puppeteer équivalent | Rétrospective, thème 4 |
| Installation pa11y principale de la scratchpad abîmée (outillage local, hors dépôt) | Contournement par une autre installation | Sans suite dans le dépôt |
| Redéploiement Railway après la PR #126 | Une erreur « Application failed to respond » transitoire pendant une capture | Capture refaite ; aucun défaut persistant |

## Métriques

| Métrique | Sprint 32 | Sprint 31 |
|----------|-----------|-----------|
| Points engagés / en done | 9 / 9 (contrôles après déploiement en attente) | 13 / 13 |
| Sacrifices appliqués | 0 | 0 |
| Stories de code avec commit rouge | 3 sur 3 (non vus rouges au sens strict : US-205, US-206) | 3 sur 3 |
| CI rouges sur la PR du sprint | 0 (6 contrôles verts) | 0 |
| Tests existants modifiés | 2 (`ArchitecturePageTest`, `ProofPageTest`), décidés avant l'action | 0 |
| Défauts signalés après le déploiement | 0 relevé à la clôture | 0 |
| Publication ou tag sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Le canevas de maquettes (version 22) : pages « Preuve technique » et « Architecture ».
2. La page « Preuve technique » en FR et EN, à 1280 et 320 px (tuiles, date unique, fiche par plugin).
3. La page « Architecture » : sommaire d'ancres, schéma HTML/CSS empilé sur téléphone.
4. La CI de la PR #127 : 6 contrôles verts.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-206, US-205, US-062 | En revue (contrôles de production en attente) |
| US-207 (indexation), US-209 (dépôts de plugins) | Sprint 33 prévu par Thibaut, dans cet ordre ; US-207 est désormais débloquée par US-205 et US-206 |

## Prochaines étapes

1. Fusion de la PR #127 sur GO de Thibaut, après CI verte.
2. Après déploiement : T-205-10 et 11, T-206-11 et 12, puis les reliquats du sprint 31 selon le disque.
3. Rétrospective (voir `sprint-retro.md`), puis planning du sprint 33.
