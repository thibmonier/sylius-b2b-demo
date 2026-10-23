# Tâches : US-179 : Schéma d'architecture lisible sur mobile et barre de la démo sans dépassement

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) sur téléphone
- **Story Points** : 1 (Could, non sacrifiable)
- **Sprint** : sprint-24-integrateurs
- **Story** : [US-179](../../../backlog/user-stories/US-179.md)

## Résumé de la US

**En tant que** visiteuse sur téléphone
**Je veux** lire le schéma d'architecture sans zoomer et une barre de démo qui tient dans l'écran
**Afin de** comprendre la démo depuis mon téléphone

## Règles et contraintes

- Constats du 2026-10-23 à 360 px sur la production : schéma réduit à environ 52 % (libellés d'environ 7 px CSS) ; en français, quatre éléments de la barre commune dépassent de 4 px (aussi sur `/fr_FR/preuve`), sans défilement de la page.
- Cible : libellés du schéma d'au moins 11 px CSS à 360 px ; aucun élément de la barre ne dépasse le bord droit ; rendu à 1280 px inchangé.
- Piste du schéma : conteneur à défilement propre, focusable au clavier, au nom accessible du schéma ; la description textuelle reste l'équivalent. Piste de la barre : retour à la ligne ou libellés plus courts.
- Les tests d'attributs ne mesurent pas les pixels : le test fixe le calcul (largeur minimale du SVG, police du `viewBox`) et la mesure réelle à 360 px est la preuve.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Lieu de vérification du rendu (action 4 de la rétro du sprint 23)

**Production, après déploiement, en lecture seule** (environ 6 Go libres, image non construite en local) : pa11y WCAG2AAA en FR et en EN, mesure à 360 px du schéma et de la barre sur `/fr_FR/architecture` et `/fr_FR/preuve`, 1280 px inchangé. La story reste en review jusque-là. Procédure : [docs/tests-locaux.md](../../../../docs/tests-locaux.md), « Vérifier le rendu en production ». Déploiement : action de Thibaut T-TECH-24-09.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `templates/demo/architecture.html.twig` (conteneur et taille du schéma)
- `templates/demo/banner.html.twig`, `translations/theme_demo.fr.yaml` (barre de la démo)
- `tests/Functional/Demo/ArchitecturePageTest.php`, `tests/Functional/Demo/DemoBannerTest.php` (existants, complétés)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-179-01 | [DOC] | Cadrage : choix du conteneur du schéma (région à défilement focusable, nom accessible du schéma, largeur minimale du SVG) et de la correction de la barre (retour à la ligne ou libellés courts), relevé des quatre éléments qui dépassent | 0,25 h | - | Claude | ✅ |
| T-179-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : conteneur focusable au nom du schéma, police rendue d'au moins 11 px CSS calculée à 360 px, rendu à 1280 px inchangé, libellés de la barre qui peuvent passer à la ligne | 0,5 h | T-179-01 | Claude | ✅ |
| T-179-03 | [FE-WEB] | Schéma dans son conteneur à défilement propre ; barre de la démo sans dépassement en français | 0,75 h | T-179-02 | Claude | ✅ |
| T-179-04 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w179 …`), ECS (`src tests`), PHPStan max, Rector ; `php bin/proof-check` (aucun fichier de test ajouté) | 0,25 h | T-179-03 | Claude | ✅ |
| T-179-05 | [TEST] | Rendu en production après déploiement : pa11y WCAG2AAA en FR et en EN, 360 px (libellés du schéma et éléments de la barre), 1280 px ; story en review jusque-là | 0,5 h | T-179-04, T-TECH-24-09 | Claude | 🔲 |
| T-179-06 | [REV] | Revue (aucune régression sur grand écran) et table « scénario → test » remplie | 0,25 h | T-179-05 | Claude | 🔲 |

**Total estimé : 2,5 h** (6 tâches, dont 0 h de Thibaut ; 2,5 h pour Claude et l'orchestrateur).

## Cadrage (T-179-01)

- **Schéma** : le SVG (`viewBox` 640, police 14) est placé dans une région `role="region"` à défilement propre (`overflow-auto`, `tabindex="0"`, nommée par le titre du schéma) ; le SVG a une largeur minimale de 32 rem (512 px), soit des libellés de 14 × 512 / 640 = 11,2 px CSS. Au-delà de 32 rem la région n'a rien à faire défiler ; à 1280 px le `max-width` de 44 rem, le `viewBox` et la police sont inchangés. La description textuelle (`<desc>`) reste l'équivalent.
- **Barre** : les quatre éléments relevés (Comptes de démo, Canal, E-mails de la démo, prochaine remise à zéro) sont les lignes des deux panneaux de menu. Les panneaux passent à une colonne `minmax(0, 1fr)` (une ligne peut devenir plus étroite que son contenu) et à `text-break` ; la ligne du canal peut passer sur deux lignes. La barre de premier niveau (une seule ligne, deux menus) ne change pas.
- **Limite** : l'environnement ne mesure pas les pixels ; les tests fixent le calcul, la mesure à 360 px est la preuve de production (T-179-05).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Schéma lisible à 360 px (nominal) | `ArchitecturePageTest::testTheDiagramLabelsAreAtLeastElevenCssPixelsOnAPhoneAndTheDesktopRenderingIsUnchanged` (calcul 11,2 px) ; mesure à 360 px en production : T-179-05, en attente du déploiement |
| Schéma au clavier (alternatif) | `ArchitecturePageTest::testTheDiagramScrollsInItsOwnFocusableRegionNamedAfterTheDiagram` (`tabindex`, `role`, nom = titre du schéma) |
| Grand écran inchangé (alternatif) | même test que le nominal (`viewBox`, police, `max-width` inchangés) ; mesure à 1280 px en production : T-179-05 |
| Barre de démo trop large (erreur) | `DemoBannerTest::testTheMenuPanelsLetTheirLabelsWrapInsteadOfOverflowing` (FR et EN) ; mesure à 360 px en production : T-179-05 |
| Régression d'accessibilité (erreur) | pa11y WCAG2AAA FR et EN en production : T-179-05, en attente du déploiement ; suite `tests/Functional/Demo` verte (658 tests) |
