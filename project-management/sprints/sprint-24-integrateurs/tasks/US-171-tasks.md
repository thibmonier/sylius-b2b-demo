# Tâches : US-171 : Matrice de compatibilité et graphe des dépendances des plugins

## Informations US

- **Epic** : EPIC-12
- **Persona** : lead développeur d'une agence ; Camille (recruteuse IT)
- **Story Points** : 3 (Should, non sacrifiable)
- **Sprint** : sprint-24-integrateurs
- **Story** : [US-171](../../../backlog/user-stories/US-171.md)

## Résumé de la US

**En tant que** lead développeur
**Je veux** voir quelles versions chaque plugin supporte et de quels autres plugins il dépend
**Afin de** choisir un ensemble de plugins sans surprise d'installation

## Règles et contraintes

- La matrice est **générée**, jamais écrite à la main : dépendances depuis le `require` et le `suggest` des `composer.json` des 25 dépôts ; versions depuis la matrice de CI de chaque dépôt (PHP, Sylius, Symfony, base de données).
- Deux colonnes distinctes : « testé par la CI du plugin » et « testé ensemble dans la démo » (Sylius 2.3, Symfony 8.1). Pas de formule « validés pour fonctionner ensemble » : vrai seulement dans la configuration de la démo.
- Une dépendance obligatoire (`require`) se distingue d'une suggestion (`suggest`) autrement que par la couleur (trait plein ou pointillé et légende en texte). Exemple de référence : trade-credit requiert order-hold et suggère facturx.
- Même mécanisme de retard que la preuve (US-168) : lecture publique seulement (API GitHub par `gh`, Packagist), aucun jeton écrit dans un fichier ; une source qui échoue garde la dernière valeur vérifiée, marquée avec sa date.
- Ton neutre, aucune comparaison avec une offre commerciale (Sylius Plus), aucun détail de sécurité.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Lieu de vérification du rendu (action 4 de la rétro du sprint 23)

**Production, après déploiement, en lecture seule** (6 Go libres environ : l'image de la démo ne se construit pas en local, seuil de 10 Go). La story reste en review jusqu'à la preuve : pa11y WCAG2AAA en FR et en EN, mesure à 360 px selon [docs/tests-locaux.md](../../../../docs/tests-locaux.md), « Vérifier le rendu en production ». Le déploiement est l'action de Thibaut T-TECH-24-09 ; aucune commande Railway par Claude.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `src/Demo/Proof/` (collecte, lecture, contrôle d'écart), `bin/proof-refresh` ou nouveau `bin/compat-refresh`, `bin/proof-check`
- `config/demo/compatibility.json` (nouveau), `config/packages/demo_compatibility.yaml` (nouveau)
- `src/Demo/Controller/CompatibilityController.php` (nouveau), `templates/demo/compatibility.html.twig`, `translations/compatibility.fr.yaml` et `.en.yaml`
- `templates/Shop/plugins/` (lien depuis la page Plugins ; **pas** `templates/demo/architecture.html.twig`, réservé à US-179)
- `docs/theme-b2b.md` (« Bloc de preuve technique » : mécanisme de la matrice)
- `tests/Unit/Demo/Proof/`, `tests/Functional/Demo/`
- `config/demo/proof.json` (rafraîchi : nouveaux fichiers de tests)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-171-01 | [DOC] | Cadrage : format des `composer.json` et des workflows de CI des 25 dépôts, clés publiées, fichier de données, mécanisme de retard repris de `ProofFreshness`, route, liens d'accès, forme du graphe (rendu serveur en SVG ou liste, sans bibliothèque JavaScript), provenance de la colonne « testé ensemble dans la démo » | 0,75 h | - | Claude | 🔲 |
| T-171-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : collecte (`require` contre `suggest`, versions de la matrice de CI), dépôt illisible (dernière valeur conservée et datée), écart signalé par le contrôle, page FR et EN avec les deux colonnes, lien optionnel trade-credit vers facturx, aucun chiffre écrit dans le gabarit | 1 h | T-171-01 | Claude | 🔲 |
| T-171-03 | [BE] | Collecteur : lecture de `require`, `suggest` et de la matrice de CI de chaque dépôt (API publique via `gh`), écriture de `config/demo/compatibility.json`, repli sur la dernière valeur marquée datée | 1,25 h | T-171-02 | Claude | 🔲 |
| T-171-04 | [BE] | Contrôle de retard branché sur `bin/proof-check` (même mécanisme qu'US-168) ; lecteur des données, route localisée et contrôleur | 0,75 h | T-171-03 | Claude | 🔲 |
| T-171-05 | [FE-WEB] | Gabarit : tableau (PHP, Sylius, Symfony, base de données ; « testé par la CI du plugin » et « testé ensemble dans la démo »), graphe accessible (titre, description, trait plein ou pointillé et légende), textes FR et EN, lien depuis la page Plugins | 1,5 h | T-171-04 | Claude | 🔲 |
| T-171-06 | [OPS] | Première génération réelle (`php bin/compat-refresh`, lecture seule, réseau via `gh`), jeu de données commité ; `php bin/proof-refresh` puis `php bin/proof-check` (nouveaux fichiers de tests) | 0,5 h | T-171-05 | Claude | 🔲 |
| T-171-07 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w171 …`), ECS (`src tests`), PHPStan max, Rector, lint Twig et YAML | 0,5 h | T-171-06 | Claude | 🔲 |
| T-171-08 | [TEST] | Rendu en production après déploiement : pa11y WCAG2AAA en FR et en EN, 360 px sans défilement horizontal, graphe lisible ; story en review jusque-là | 0,75 h | T-171-07, T-TECH-24-09 | Claude | 🔲 |
| T-171-09 | [REV] | Revue (ton neutre, aucune formule « validés ensemble » hors démo, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-171-08 | Claude | 🔲 |

**Total estimé : 7,25 h** (9 tâches, dont 0 h de Thibaut ; 7,25 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Matrice et graphe publiés (nominal) | |
| Testé seul ou testé ensemble (alternatif) | |
| Dépendance suggérée (alternatif) | |
| Matrice en retard (erreur) | |
| Dépôt illisible (erreur) | |
