# Tâches : US-170 : Page « Architecture de la démo »

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) ; lead développeur d'une agence
- **Story Points** : 3 (Should, non sacrifiable (décision de Thibaut du 2026-10-23))
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-170](../../../backlog/user-stories/US-170.md)

## Résumé de la US

**En tant que** lead développeur qui évalue les plugins
**Je veux** une page qui montre comment la démo est construite
**Afin de** juger en quelques minutes la qualité de l'architecture et la façon dont les plugins s'assemblent

## Règles et contraintes

- La page « Intégrer » garde son « Architecture type » (projet client) et renvoie vers la nouvelle page ; pas de doublon de contenu.
- Chaque affirmation renvoie à un fichier du dépôt public ou à un `docs/*.md` ; les chiffres viennent de `proof.json` ou de la configuration, jamais écrits dans le gabarit.
- Sécurité en termes généraux seulement : aucune route, valeur ni seuil.
- Schéma en SVG inline accessible (titre, description, thème clair et sombre), comme celui de la page Intégrer.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `src/Demo/Controller/` (nouveau contrôleur ou route)
- `templates/demo/architecture.html.twig`
- `translations/architecture.fr.yaml`, `architecture.en.yaml`
- `templates/demo/integrate.html.twig` (lien)
- Pied de page de la boutique (lien)
- `tests/Functional/Demo/`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-170-01 | [DOC] | Cadrage : sources de chaque rubrique (`docs/*.md`, `Dockerfile`, `docker/Caddyfile`, configuration, workflows), route et liens d'accès, chiffres lus dans `proof.json` | 0,75 h | - | Claude | ✅ |
| T-170-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : page FR et EN avec ses six rubriques, liens depuis Intégrer et le pied de page, SVG avec titre et description, chiffres égaux à ceux de `proof.json`, aucun motif de détail de sécurité | 1 h | T-170-01 | Claude | ✅ |
| T-170-03 | [BE] | Route localisée et contrôleur ; chiffres fournis par `ProofReader` | 0,75 h | T-170-02 | Claude | ✅ |
| T-170-04 | [FE-WEB] | Gabarit et schéma SVG d'ensemble (thème clair et sombre), textes FR et EN des six rubriques avec leurs liens vers le dépôt | 2 h | T-170-03 | Claude | ✅ |
| T-170-05 | [FE-WEB] | Liens : section « Architecture type » de la page Intégrer et pied de page de la boutique | 0,5 h | T-170-03 | Claude | ✅ |
| T-170-06 | [TEST] | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile` (360 px sans défilement horizontal) ; suite via `bin/run-tests`, ECS, PHPStan max, Rector | 0,75 h | T-170-04, T-170-05 | Claude | ✅ |
| T-170-07 | [REV] | Revue (aucun détail de sécurité, ton neutre, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-170-06 | Claude | ✅ |

**Total estimé : 6 h** (7 tâches, dont 0 h de Thibaut ; 6 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Page complète (nominal) | `ArchitecturePageTest::testThePageHasItsSixSectionsEachWithLinksToTheRepository` (FR et EN : six rubriques, un `h2` et au moins un lien vers le dépôt chacune) |
| Accès depuis le guide d'intégration et le pied de page (alternatif) | `ArchitecturePageTest::testTheFooterAndTheIntegrationGuideLinkToThePage` (4 cas : pied de page et section « Architecture type », FR et EN) |
| Schéma lisible au clavier, au lecteur d'écran et sur mobile (alternatif) | `ArchitecturePageTest::testTheDiagramHasATitleAndATextualEquivalentAndFollowsTheTheme` (titre, description de plus de 120 caractères, `currentColor`, `w-100`) ; production le 2026-10-23 après déploiement (T-170-06) : pa11y 9 WCAG2AAA « No issues found! » en FR et en EN ; à 360 px (puppeteer-core et Chromium, thèmes clair et sombre émulés), `scrollWidth` = 360 sur FR et EN, schéma de 336 px de large |
| Chiffre non sourcé (erreur) | `ArchitecturePageTest::testEveryFigureComesFromTheProofOrTheConfiguration` (tout nombre du rendu vient de `proof.json` ou de la configuration), `testTheTranslationsHoldNoFigure`, `testWithoutAProofFileThePageStillAnswersAndShowsNoTestFigure` |
| Détail de sécurité (erreur) | `ArchitecturePageTest::testTheSecuritySectionStaysGeneral` (aucun chiffre, aucune route, aucun terme de réglage) ; `testTheTextsRespectTheEditorialCharter` (ton neutre) |

## Cadrage (T-170-01, 2026-10-23)

- Route `/{_locale}/architecture` (`app_demo_architecture`), `ArchitectureController` ; chiffres : `proof.json` via `ProofReader` (fichiers de tests, niveau PHPStan, CI, date), configuration (nombre de plugins du catalogue, versions de Sylius, Symfony et PHP lues dans les constantes). Aucun chiffre dans les traductions : tout passe en paramètre.
- Sources citées : `docker/Dockerfile`, `docker/Caddyfile`, `docker/crontab`, `composer.json`, `src/Demo`, `.github/workflows/ci.yaml`, `phpstan.dist.neon`, `docs/*.md` et les ADR 0002, 0004, 0008.
- Rendu réel (T-170-06) : pas d'image de la démo en local et 6,9 Go libres ; construire l'image risquait de passer sous le seuil de 5 Go. pa11y et la mesure à 360 px se font en lecture seule sur la production après le déploiement ; la story reste en review jusque-là.

## Vérification en production (T-170-06, 2026-10-23)

- `/fr_FR/architecture` et `/en_US/architecture` : 200, six rubriques, chiffres de `proof.json` (25 plugins, 203 fichiers de tests) et de la configuration (Sylius 2.3, Symfony 8.1), 20 liens vers le dépôt, `X-Robots-Tag: noindex, nofollow`, lien dans le pied de page.
- pa11y 9, standard WCAG2AAA, Chromium local : « No issues found! » dans les deux langues.
- 360 px : pas de défilement horizontal de la page. `bin/measure-mobile` vise la démo locale (pas d'image locale, faute de disque) : mesure équivalente par script puppeteer-core sur la production.
- Constats hors périmètre, reportés dans [US-179](../../../backlog/user-stories/US-179.md) : à 360 px, le schéma est réduit à environ 52 % (libellés d'environ 7 px CSS) ; en français, la barre commune de la démo dépasse de 4 px (aussi sur `/fr_FR/preuve`, donc antérieur), sans défilement. La boutique n'a pas de thème sombre : le rendu sombre est identique au clair, le schéma en `currentColor` suivrait un tel thème.
