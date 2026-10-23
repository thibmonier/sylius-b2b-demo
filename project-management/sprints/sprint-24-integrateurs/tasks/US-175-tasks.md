# Tâches : US-175 : Métapaquets des packs « IA et headless » et « Conformité et outillage »

## Informations US

- **Epic** : EPIC-14
- **Persona** : lead développeur d'une agence ; Thibaut
- **Story Points** : 3 (Should, **conditionnelle**, sacrifice n°3)
- **Sprint** : sprint-24-integrateurs (conditionnelle : n'entre que si T-175-01 est donnée par Thibaut)
- **Story** : [US-175](../../../backlog/user-stories/US-175.md)

## Résumé de la US

**En tant que** lead développeur
**Je veux** installer un ensemble cohérent de plugins en une seule commande
**Afin de** démarrer plus vite sans choisir les plugins un par un

## Règles et contraintes

- **Condition d'entrée** : Thibaut donne son GO pour créer les **deux dépôts** et valide les **noms des paquets** (question ouverte 1 de l'[ADR 0015](../../../../docs/adr/0015-distributions-de-plugins.md)). Sans ce GO (T-175-01), la story reste au backlog et aucune des tâches suivantes ne démarre.
- Deux métapaquets Composer (`type: metapackage`), un dépôt chacun, composition fixée par l'ADR 0015 : « IA et headless » (agentic-feeds, ai-catalog-copilot, b2b-mcp, webhook-outbox ; audit-log en `suggest`) et « Conformité et outillage » (facturx, gpsr, audit-log, timeline, command-palette, bulk-actions).
- Aucun plugin classé « concurrent » ou « à confirmer » dans `docs/vitrine-plugins.md` ; le README et le code ne parlent pas d'une offre commerciale (`PublicTextCharter`, ton neutre).
- Vérification de composition : un plugin du pack qui requiert un plugin absent du pack fait échouer la vérification en nommant la dépendance.
- README FR et EN : pour chaque plugin, les gestes restants (bundle, configuration, routes, migrations), relevés dans l'ADR 0015, « Ce qui reste à la main après `composer require` du métapaquet ».
- **Aucun dépôt, tag ni paquet sans GO de Thibaut** ; la soumission à Packagist est faite par Thibaut (T-175-10). Le GO de T-175-01 couvre la création locale ; la création des dépôts GitHub, leur push et le tag demandent un GO explicite (T-175-09).
- Travail dans des dossiers de scratchpad hors du dépôt de la démo jusqu'à la création des dépôts ; les projets jetables sont supprimés avec `/bin/rm` (alias `rm -i`).
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Deux nouveaux dépôts (noms validés par Thibaut) : `composer.json` de type `metapackage`, `bin/check-composition`, `tests/`, `.github/workflows/`, `README.md` (FR et EN), `LICENSE`
- Lecture : `docs/adr/0015-distributions-de-plugins.md`, `docs/vitrine-plugins.md`, `composer.json` des 10 plugins
- Aucun fichier de la démo modifié (le lien « Installer un pack » est la story 4 de l'ADR 0015, hors sprint)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-175-01 | [OPS] | 📅 Thibaut donne son GO pour créer les deux dépôts et valide les noms des paquets ; condition d'entrée de la story | 0,25 h | - | Thibaut | 📅 |
| T-175-02 | [DOC] | Cadrage : noms validés, bornes `^` des 10 plugins lues sur Packagist (`config/demo/proof.json`), `suggest`, gabarit de README, chaîne de vérification de la CI de chaque dépôt (actions épinglées par SHA, `bin/check-pinned-refs`) | 0,5 h | T-175-01 | Claude | 🔲 |
| T-175-03 | [TEST] | Tests rouges, commités seuls et cités dans la PR de chaque dépôt : `bin/check-composition` (membres exacts du pack, plugin classé « concurrent » ou « à confirmer » refusé, dépendance obligatoire hors du pack nommée dans l'échec, audit-log dans `suggest` et non dans `require`) | 1 h | T-175-02 | Claude | 🔲 |
| T-175-04 | [BE] | Dépôt « IA et headless » en local : `composer.json` métapaquet, vérificateur de composition, CI, LICENSE | 1 h | T-175-03 | Claude | 🔲 |
| T-175-05 | [BE] | Dépôt « Conformité et outillage » en local, mêmes fichiers | 0,75 h | T-175-03 | Claude | 🔲 |
| T-175-06 | [DOC] | README FR et EN de chaque pack : pour chaque plugin le bundle, la configuration, les routes et les migrations ; ton neutre | 1,25 h | T-175-04, T-175-05 | Claude | 🔲 |
| T-175-07 | [TEST] | `composer validate --strict` et résolution Composer dans un projet jetable hors dépôt (`composer update --no-install --no-scripts --no-plugins`), dernière ligne citée ; projet supprimé avec `/bin/rm` | 0,5 h | T-175-06 | Claude | 🔲 |
| T-175-08 | [REV] | Revue (composition conforme à l'ADR, aucune mention d'offre commerciale, parité FR et EN) et table « scénario → preuve » remplie | 0,25 h | T-175-07 | Claude | 🔲 |
| T-175-09 | [OPS] | Après GO explicite : création des deux dépôts GitHub avec leurs topics (ecommerce, sylius, sylius-plugin, symfony et topics du pack), push, CI verte, tag | 0,5 h | T-175-08 | Claude | 🔲 |
| T-175-10 | [OPS] | 📅 Thibaut soumet les deux paquets à Packagist | 0,25 h | T-175-09 | Thibaut | 📅 |

**Total estimé : 6,25 h** (10 tâches, dont 0,5 h de Thibaut ; 5,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Pack installable (nominal) | |
| Dépendance suggérée (alternatif) | |
| Gestes restants documentés (alternatif) | |
| Plugin qui recoupe une offre commerciale (erreur) | |
| Dépendance obligatoire hors du pack (erreur) | |
