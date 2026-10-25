# Tâches : US-201 : Tableau de bord des achats plus lisible, dans la continuité de la home

## Informations US

- **Epic** : EPIC-12 (lien : EPIC-15)
- **Persona** : Isabelle (administratrice d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **sacrifice : n°2**)
- **Sprint** : sprint-30-compte-tableau-de-bord-et-tva
- **Worker proposé** : w201 (orchestrateur pour la maquette)
- **Story** : [US-201](../../../backlog/user-stories/US-201.md)

## Résumé de la US

**En tant qu'** administratrice d'entreprise  
**Je veux** un tableau de bord des achats qui fait ressortir l'essentiel en quelques secondes  
**Afin de** savoir combien ma société dépense, comment cela évolue et où agir

## Règles et contraintes

- **Surcharge dans la démo** (décision de Thibaut du 2026-10-25) : hooks Twig et styles du thème B2B ; si aucun hook ne suffit, surcharge de gabarit dans `templates/bundles/` du seul gabarit concerné. **Aucune modification, aucun tag ni release du plugin company-insights** ; `composer.lock` inchangé pour ce plugin.
- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) dans une nouvelle page « Tableau de bord des achats » du canevas unique, **validée par Thibaut (T-201-04) avant toute intégration Twig**. Les tâches de code T-201-05 à T-201-11 sont 📅 tant que son GO n'est pas donné et noté au registre. La maquette réserve la place d'US-154 (conversion des devis, économies des paliers), hors périmètre.
- **Données réelles** : l'historique d'Acme Industries couvre 18 mois (US-155) ; la maquette et les tests utilisent ces données (comparaison N-1 possible).
- Graphique mensuel : comparaison N-1 qui ne repose pas sur la seule couleur, valeurs accessibles au clavier et au lecteur d'écran (table de données en repli si le graphique du plugin ne le permet pas).
- Reprise d'US-186 et d'US-189 : jetons de `b2b.scss`, polices auto-hébergées, AAA, cibles de 44 px, `prefers-reduced-motion`, 360 et 1280 px sans défilement horizontal (tableaux larges dans une boîte qui défile).
- Accès : inchangé (Isabelle oui, Claire refusée comme avant) ; l'export CSV et les pages « voir tout » gardent leur comportement.
- Fichiers communs (règle 21) : hooks dans `config/packages/demo_theme_b2b_insights.yaml` (fichier propre à la story), styles dans `assets/shop/styles/_insights.scss` (une ligne d'import dans `b2b.scss`), domaine de traduction `insights` ; `docs/theme-b2b.md` : seulement la section « Tableau de bord des achats ».
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun identifiant saisi en production.
- Tests uniquement par `WORKER_ID=w201 TEST_DB_NAME=b2b_w201 TEST_CACHE_DIR=var/cache-w201 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local et image reconstruite** (Isabelle, période par défaut, autre période, période sans commande ; Claire refusée) : pa11y WCAG2AAA FR et EN, 360 et 1280 px. **Production** : sans objet pour la page connectée ; relevé que l'adresse renvoie vers la connexion.

## Fichiers principaux touchés

- `templates/shop/theme_b2b/insights/` (nouveau) et, si nécessaire, `templates/bundles/` (gabarit du plugin surchargé, un seul à la fois, justifié dans le cadrage)
- `config/packages/demo_theme_b2b_insights.yaml` (nouveau), `assets/shop/styles/_insights.scss` (nouveau), `translations/insights.{fr,en}.yaml`
- `tests/Functional/Demo/` (nouveaux fichiers ; tests existants du tableau de bord relus)
- `config/demo/proof.json` (par `bin/proof-rebase`), `docs/theme-b2b.md` (section du tableau de bord), `docs/images/insights-*`

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-201-01 | [DOC] | Cadrage : gabarits et hooks du plugin relus contre le code (`dashboard`, `widgets`, `monthly`, `rankings`, `variation`, `sorts`, `breakdown`, widgets approbations et crédit, export), ce qui se surcharge par hook et ce qui demande `templates/bundles/` ; données d'Acme sur 18 mois ; droits (Isabelle, Claire) ; tests existants ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-201-02 | [TEST] | État de référence « avant » : tests du tableau de bord verts sur `main` ; captures connectées locales (bureau et 360 px) d'Isabelle (période par défaut, période sans commande) | 0,5 h | T-201-01, T-TECH-30-01 | Claude | ✅ |
| T-201-03 | [FE-WEB] | Maquette Claude Design (bureau 1440 px et téléphone 390 px), page « Tableau de bord des achats » du canevas : rangée d'indicateurs (valeur, évolution, période de comparaison), graphique mensuel avec N-1 distinct sans la seule couleur, tops compacts avec parts, sélecteur de période, états « aucune donnée » et « période partielle », place réservée à US-154 ; vraies données ; table « Acquis » mise à jour | 2 h | T-201-01, T-TECH-30-01 | Orchestrateur | ✅ |
| T-201-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-201-03 | Thibaut | ✅ |
| T-201-05 | [TEST] | Tests rouges, commités seuls et cités dans la PR : indicateurs en premier avec période de comparaison, graphique annonçant chaque mois et N-1, changement de période qui met tout à jour, Claire refusée, état vide, 360 px, charte des textes, plugin non modifié (`composer.lock`) | 1 h | T-201-04 | Claude | ✅ |
| T-201-06 | [FE-WEB] | Surcharge dans la démo : fichier de hooks propre, gabarits, partial `_insights.scss`, graphique accessible (ou table de repli), tops compacts, AAA, `prefers-reduced-motion` ; canal B2B seulement | 3 h | T-201-05 | Claude | ✅ |
| T-201-07 | [DOC] | Textes FR et EN (domaine `insights`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | T-201-06 | Claude | ✅ |
| T-201-08 | [TEST] | Suite ciblée par `bin/run-tests` (tableau de bord, classements, export, droits), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN, 360 et 1280 px ; commande exacte et dernière ligne citées | 0,75 h | T-201-06, T-201-07 | Claude | ✅ |
| T-201-09 | [DOC] | Captures `docs/images/insights-*` par `bin/render-page` (Isabelle, local, bureau et 360 px), section du tableau de bord dans `docs/theme-b2b.md` | 0,5 h | T-201-08 | Claude | ✅ |
| T-201-10 | [TEST] | 📅 Image reconstruite et conteneur neuf : tableau de bord d'Isabelle vérifié (règle 16 : rien hors base) ; production après le déploiement, lecture seule : l'adresse renvoie vers la connexion ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 📅 production |
| T-201-11 | [REV] | 📅 Revue (ton neutre, parité FR et EN, plugin non modifié) et table « scénario → test ou preuve » remplie | 0,5 h | T-201-10 | Claude | 📅 production |

**Total estimé : 10,5 h** (11 tâches ; 10 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Cadrage (T-201-01) et état « avant » (T-201-02), 2026-10-25

**Surcharge.** `dashboard.html.twig` du plugin est un bloc unique (période, filtres, export, titre, indicateurs, « en attente ») : un hook ne le découpe pas. Il est désactivé dans `config/packages/demo_theme_b2b_insights.yaml` et remplacé par des gabarits de `templates/shop/theme_b2b/insights/`. Les blocs `widgets`, `monthly`, `breakdown` et `rankings` sont des hooks distincts (priorités -15 à -30), remplaçables et réordonnables. `variation`, `sorts`, `table_region` et `ranking/*` n'ont pas de hook : réutilisés tels quels ou surchargés dans `templates/bundles/`, un seul à la fois et justifié. Les attributs `data-test-*` sont conservés (lus par les tests existants).

**Contraintes.** Le graphique (`chart_controller.js`, ApexCharts, barres N pleines et N-1 hachurées, `aria-hidden`) et le donut des catégories sont dans le plugin : non paramétrables sans le modifier ; la table de 12 mois reste la référence pour le clavier et le lecteur d'écran, ou un contrôleur Stimulus de la démo remplace le graphique. Les 12 mois du graphique ne dépendent pas de la période choisie. Aucun état hors base (règle 16).

**Données d'Isabelle (12 derniers mois, base `b2b_w201`).** Dépense 29 771,10 € HT (+112,9 %), 30 commandes (+114,3 %), panier moyen 992,37 € (-0,6 %), 4 acheteurs actifs (stable) ; top acheteurs Isabelle 45 %, Paul 39 %, Claire 13 %, Sébastien (désactivé) 2 % ; 10 produits, 10 catégories, 3 sites, 2 centres de coût ; 1 commande en attente (813,00 €). Montants HT uniquement : US-202 ne change aucun libellé du tableau de bord. Claire : 403 avec message. Données complètes : `scratchpad/w201/maquette-data.json`.

**Couverture N-1.** L'historique de 18 mois (`DemoOrderHistory`, `MONTHS_BACK = 17`) n'offre un N-1 que pour 6 des 12 mois (mai à octobre) ; novembre à avril ont N-1 = 0. Les variations de la période de 12 mois sont gonflées. Un N-1 complet demande `MONTHS_BACK = 23` : décision attendue de Thibaut.

**États vides.** Période sans commande : bandeau « Aucune commande sur la période. » mais indicateurs à zéro, « Pas de comparaison » et graphique conservés, écart avec le critère d'acceptation. Mois en cours marqué « en cours » avec une note.

**Fuseau horaire.** Avec PHP en UTC (local), la période s'affiche décalée d'un jour (31 oct. au lieu du 1 nov.) ; la production (`docker/php.ini`, Europe/Paris) est correcte. Rendus faits avec `-d date.timezone=Europe/Paris`.

**État « avant ».** `tests/Smoke/PurchasingDashboardSmokeTest.php` vert via `WORKER_ID=w201 TEST_DB_NAME=b2b_w201 TEST_CACHE_DIR=var/cache-w201 bin/run-tests` : 11 tests, 73 assertions, 2 deprecations. Captures locales (1280 et 360 px, FR, EN, période sans commande) : aucun défilement horizontal ; hauteur 6 885 px (1280) et 11 839 px (360).

## Preuves du développement (2026-10-25)

| Contrôle | Commande exacte | Dernière ligne |
|----------|-----------------|----------------|
| Tests ciblés | `WORKER_ID=w201 TEST_DB_NAME=b2b_w201 TEST_CACHE_DIR=var/cache-w201 bin/run-tests tests/Unit/Demo/Insights tests/Functional/Demo/PurchasingDashboardRedesignTest.php tests/Smoke/PurchasingDashboardSmokeTest.php` | `OK, but there were issues! Tests: 157, Assertions: 1637, Deprecations: 2.` |
| PHPStan (dépôt entier) | `php -d memory_limit=2G vendor/bin/phpstan analyse --no-progress` | `[OK] No errors` |
| ECS | `php -d memory_limit=2G vendor/bin/ecs check src tests` | `[OK] No errors found. Great job - your code is shiny in style!` |
| Rector | `php vendor/bin/rector process --dry-run` | `[OK] Rector is done!` |
| Twig | `APP_ENV=test APP_CACHE_DIR=var/cache-w201 php -d memory_limit=-1 bin/console lint:twig templates/shop/theme_b2b/insights` | `[OK] All 14 Twig files contain valid syntax.` |
| YAML | `Symfony\Component\Yaml\Yaml::parseFile` sur le hook et les quatre fichiers de textes | cinq fois `ok` |
| pa11y WCAG2AAA local | `pa11y.js --config` (Chromium local, `"standard": "WCAG2AAA"`) sur le rendu de `bin/render-page` : FR par défaut, FR période sans commande, EN, FR métrique commandes sur 30 jours | `No issues found!` quatre fois |
| 360 px | `puppeteer`, viewport 360 `isMobile`, `scrollWidth` contre `clientWidth` | 360 pour 360 (défaut, vide, EN, commandes) |

Écarts à la maquette : les filtres sont un bloc repliable sous la rangée période et export (pas à côté du bouton d'export) ; « Personnalisée » n'apparaît que lorsque des dates sont choisies ; l'aide de l'export est lue par `aria-describedby` mais n'est plus affichée. Le graphique est dessiné par le serveur (SVG), la mesure et la période se changent par des liens.


## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| L'essentiel en premier (nominal) | T-201-05, T-201-09 | |
| Graphique mensuel accessible (alternatif) | T-201-05, T-201-08 | |
| Changement de période (alternatif) | T-201-05 | |
| Accès refusé (erreur) | T-201-05 | |
| Aucune donnée sur la période (erreur) | T-201-05 | |
| Débordement sur téléphone (erreur) | T-201-05, T-201-08 | |
| Régression d'accessibilité (erreur) | T-201-08 | |
