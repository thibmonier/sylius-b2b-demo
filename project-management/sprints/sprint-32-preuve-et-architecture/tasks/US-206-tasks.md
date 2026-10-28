# Tâches : US-206 : Refonte de la page « Architecture » dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Didier (DSI / CTO) ; Hugo (intégrateur) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **protégée**, proposition)
- **Sprint** : sprint-32-preuve-et-architecture
- **Worker proposé** : w206 (orchestrateur pour la maquette)
- **Story** : [US-206](../../../backlog/user-stories/US-206.md)

## Résumé de la US

**En tant que** visiteuse de la démo  
**Je veux** une page « Architecture » claire, aérée et dans la même charte que l'accueil  
**Afin de** comprendre en quelques minutes ce qu'elle me prouve et de pouvoir la parcourir sans effort

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau 1440 px et téléphone 390 px, vraies données) dans une nouvelle page du canevas unique G2StRQigRKD6SqNRoyHpkW, publiée par l'orchestrateur et **validée par Thibaut (T-206-04) avant toute intégration Twig**. Les tâches de code T-206-05 à T-206-12 sont 📅 tant que son GO n'est pas noté au registre.
- **Contenu inchangé** : la page garde ses six sections (`overview`, `layers`, `flows`, `operations`, `quality`, `security`), ses faits et ses liens vers le dépôt public ; l'indexation reste hors sujet (US-207, sprint 33, la page reste `noindex`) ; la route `/{_locale}/architecture` et le nom `app_demo_architecture` ne changent pas. Nouveautés de présentation : introduction courte, sommaire d'ancres, un visuel (schéma ou tableau) par section.
- **Blocs de code** : la fiche mentionne des blocs de code ou de configuration lisibles sur téléphone, mais la page actuelle n'en contient pas. Le cadrage (T-206-01) le confirme ; le contenu étant inchangé, la maquette n'en ajoute pas, et tout bloc éventuel défile dans sa propre région, jamais dans la page.
- **Invariants des tests existants** (`tests/Functional/Demo/ArchitecturePageTest.php`, 11 méthodes, **non modifié**) : six `section[data-architecture-section]` dans l'ordre, chacune avec un seul `h2` et au moins un lien vers `https://github.com/thibmonier/sylius-b2b-demo/` ; schéma `svg[data-architecture-overview][role="img"]` avec `aria-labelledby`, `<title>` et `<desc>` de plus de 120 caractères, **`viewBox="0 0 640 440"`, `font-size="14"`, `max-width: 44rem`, `min-width` en rem (étiquettes d'au moins 11 px CSS), classe `w-100`, couleurs en `currentColor` sans `#`** ; région `[data-architecture-scroll]` (`tabindex="0"`, `role="region"`, `overflow-auto`, nommée par le titre du schéma) ; liens du pied de page et du guide d'intégration ; chiffres issus du rapport de preuve ou de la configuration (rien en dur) ; section « sécurité » générale ; page `noindex` avec sa description ; charte éditoriale des textes.
- **Canal B2C** : la page est aussi servie sur le canal B2C (US-170 : utilitaires Bootstrap seulement). Le cadrage vérifie que les classes `b2b-home-*` et le partial de la page y sont chargés ; sinon la story prévoit un chargement propre à la page, sans modifier le thème B2C.
- **Sécurité** : la section « sécurité » reste générale (aucune route, valeur ni seuil) ; aucun détail de sécurité ajouté à la page ni à la PR.
- Principes d'US-189, d'US-200 et d'US-204 : jetons de `b2b.scss`, classes `b2b-home-*`, partial `_plugins.scss` comme référence de style, polices auto-hébergées, AAA, cibles de 44 px, `prefers-reduced-motion`, 320 et 1280 px sans défilement horizontal.
- **Disque** : aucun test ni rendu local avant 5 Go libres (6 Go pour la suite complète) ; avant, seuls le cadrage, les tests et le code sont écrits.
- Fichiers communs (règle 21) : `templates/demo/architecture.html.twig`, `ArchitectureController`, `assets/shop/styles/_architecture.scss` (nouveau) et une ligne d'import dans `b2b.scss`, domaine de traduction `architecture` : US-206 seule ; `docs/captures-vitrine.md` : sa section seulement, `git diff` relu avant le commit ; `config/demo/proof.json` : orchestrateur (`bin/proof-rebase`).
- Ton neutre, aucune comparaison avec Sylius Plus.
- Tests uniquement par `WORKER_ID=w206 TEST_DB_NAME=b2b_w206 TEST_CACHE_DIR=var/cache-w206 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local, image reconstruite et production** : page publique, sans compte. Mesures de production **le jour du déploiement** (pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px, `bin/measure-mobile`).

## Fichiers principaux touchés

- `templates/demo/architecture.html.twig`, `src/Demo/Controller/ArchitectureController.php` (si les données doivent être regroupées)
- `assets/shop/styles/_architecture.scss` (nouveau) et une ligne d'import dans `b2b.scss`, `translations/architecture.{fr,en}.yaml`
- `tests/Functional/Demo/` (nouveaux fichiers ; `ArchitecturePageTest` relu, non modifié)
- `docs/captures-vitrine.md` (section), `docs/images/architecture-*`, `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-206-01 | [DOC] | Cadrage : `architecture.html.twig` (166 lignes, six sections, schéma SVG, macro de liens) et contrôleur relus ; invariants d'`ArchitecturePageTest` listés (schéma, région défilante, sections, liens) ; absence de bloc de code confirmée ; **canal B2C** : classes `b2b-home-*` chargées ou non ; classes réutilisables ; visuel retenu par section (schéma existant, liste, tableau) ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-206-02 | [TEST] | État de référence « avant » : `ArchitecturePageTest` relu et, dès 5 Go libres, lancé sur la branche (11 méthodes) ; captures de production (sans connexion) de la page FR et EN en attendant le rendu local | 0,25 h | T-206-01 | Claude | 📅 disque |
| T-206-03 | [FE-WEB] | Maquette Claude Design (bureau 1440 px et téléphone 390 px), nouvelle page « Architecture » du canevas : introduction courte, sommaire d'ancres, six sections avec un visuel chacune (schéma d'ensemble réhabillé, couches, flux, exploitation, qualité, sécurité générale), liens vers le dépôt, schéma qui défile dans sa région à 390 px ; vraies données ; table « Acquis » mise à jour | 2 h | T-206-01 | Orchestrateur | 🔲 |
| T-206-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-206-03 | Thibaut | 📅 GO |
| T-206-05 | [TEST] | 📅 Tests rouges, commités seuls et cités dans la PR : introduction et sommaire d'ancres (une ancre par section, `href` vers un `id` existant), un visuel par section, navigation clavier par ancre (cible atteinte, titre annoncé, aucun piège de focus), classes de l'accueil, chiffres lus dans le rapport, anglais complet, `prefers-reduced-motion`, cibles de 44 px, 320 px sans défilement de la page | 1 h | T-206-04 | Claude | ✅ (écrits, non exécutés : 📅 disque) |
| T-206-06 | [FE-WEB] | 📅 Coquille de la page et premières sections : partial `_architecture.scss`, introduction, sommaire d'ancres, « vue d'ensemble » (chiffres clés et schéma réhabillé par classes, invariants du SVG gardés) et « couches » ; AAA, `prefers-reduced-motion`, route inchangée | 2,25 h | T-206-05 | Claude | ✅ (code ; rendu 📅 disque) |
| T-206-07 | [FE-WEB] | 📅 Sections « flux », « exploitation », « qualité » (ligne de CI, chiffres du rapport) et « sécurité » (générale) avec leur visuel et leurs liens ; sélecteurs `data-architecture-*` inchangés | 1,75 h | T-206-06 | Claude | ✅ (code ; rendu 📅 disque) |
| T-206-08 | [DOC] | 📅 Textes FR et EN (domaine `architecture` : introduction, sommaire, légendes), charte `PublicTextCharter` ; aucun chiffre dans les traductions ; texte relu contre la maquette validée | 0,5 h | T-206-07 | Claude | ✅ |
| T-206-09 | [TEST] | 📅 Suite ciblée par `bin/run-tests` (`ArchitecturePageTest` **non modifié**, nouveaux tests), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 320 et 375 px ; commande exacte et dernière ligne citées | 0,75 h | T-206-07, T-206-08 | Claude | 📅 disque |
| T-206-10 | [DOC] | 📅 Captures `docs/images/architecture-*` par `bin/render-page` (FR et EN, bureau et 320 px), section de `docs/captures-vitrine.md` | 0,5 h | T-206-09 | Claude | 📅 disque |
| T-206-11 | [TEST] | Image reconstruite et conteneur neuf (règle 16 : rien hors base) ; production **le jour du déploiement**, lecture seule : pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px, tests de fumée de la page ; mesures consignées | 0,5 h | merge de la PR | Claude | 📅 déploiement |
| T-206-12 | [REV] | Revue (ton neutre, parité FR et EN, contenu inchangé, **aucun détail de sécurité ajouté**, page toujours `noindex`) et table « scénario → test ou preuve » remplie | 0,25 h | T-206-11 | Claude | 📅 déploiement |

**Total estimé : 11 h** (12 tâches ; 10,5 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Lecture dans la charte de la home (nominal) | T-206-05, T-206-10 | test fonctionnel du sommaire et des six sections (à nommer) ; `testThePageHasItsSixSectionsEachWithLinksToTheRepository` non modifié ; capture `docs/images/architecture-*` |
| Lecture sur téléphone (alternatif) | T-206-09 | `bin/measure-mobile` à 320 et 375 px ; cibles de 44 px ; `testTheDiagramScrollsInItsOwnFocusableRegionNamedAfterTheDiagram` non modifié |
| Anglais et mouvement réduit (alternatif) | T-206-05, T-206-09 | test de traduction et de `prefers-reduced-motion` (à nommer) ; `testTheTextsRespectTheEditorialCharter` non modifié |
| Contenu inchangé (erreur) | T-206-09 | les 11 méthodes d'`ArchitecturePageTest` non modifiées et vertes (schéma, chiffres, sécurité générale, `noindex`) |
| Navigation par ancres (erreur) | T-206-05, T-206-09 | test de la cible de chaque ancre et du focus au clavier (à nommer) ; contrôle manuel au clavier consigné |
| Régression d'accessibilité (erreur) | T-206-09, T-206-11 | pa11y WCAG2AAA FR et EN, en local puis en production le jour du déploiement |
