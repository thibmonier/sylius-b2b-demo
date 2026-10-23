# Tâches : US-173 : Spike : distributions de plugins (packs clé en main)

## Informations US

- **Epic** : EPIC-14
- **Persona** : lead développeur d'une agence ; Thibaut
- **Story Points** : 2 (Should, n°2)
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-173](../../../backlog/user-stories/US-173.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** choisir la forme et la composition de distributions qui regroupent des plugins
**Afin de** proposer une installation plus clé en main sans contredire la politique de communication vis-à-vis de Sylius

## Règles et contraintes

- Spike limité à 4 h. Aucune publication, aucun nouveau dépôt ni paquet sans GO de Thibaut.
- Packs sans recoupement avec Sylius Plus (décision de Thibaut du 2026-10-23) : aucun plugin classé « concurrent » ou « à confirmer » dans `docs/vitrine-plugins.md`.
- Volet commercial écarté (licences, SLA, Packagist privé, place de marché payante) ; aucun nom en « Enterprise ».
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- ADR : `docs/adr/0015-*.md`, `docs/adr/index.md`
- Lecture : `composer.json` des 18 plugins sans recoupement, `docs/vitrine-plugins.md`
- Projet jetable pour l'essai (hors dépôt, supprimé après)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-173-01 | [DOC] | Relevé : classement US-063, `require` et `suggest` des 18 plugins sans recoupement, dépendances internes entre eux | 0,5 h | - | Claude | ✅ |
| T-173-02 | [OPS] | Essai d'un métapaquet « IA et headless » dans un projet jetable (dépôt de type `path`) : ce qui reste à faire à la main (bundles, configuration, routes, migrations) | 1,25 h | T-173-01 | Claude | ✅ |
| T-173-03 | [DOC] | Comparaison des trois formes (métapaquet, recettes Flex sur `recipes-contrib`, squelette de projet) : effet, maintenance, publication | 0,75 h | T-173-02 | Claude | ✅ |
| T-173-04 | [DOC] | ADR : forme retenue, composition des packs, découpage de la réalisation en stories de 8 points au plus ; table « scénario → preuve » remplie | 1 h | T-173-03 | Claude | ✅ |
| T-173-05 | [REV] | 📅 Thibaut relit l'ADR et décide (forme, packs, stories à planifier) | 0,25 h | T-173-04 | Thibaut | ✅ |
| T-173-06 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml` et stories de réalisation créées au backlog | 0,25 h | T-173-05 | Orchestrateur | ✅ |

**Total estimé : 4 h** (6 tâches, dont 0,25 h de Thibaut ; 3,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| ADR et découpage (nominal) | [ADR-0015](../../../../docs/adr/0015-distributions-de-plugins.md), sections « Décision proposée » (forme, composition) et « Réalisation proposée » (4 stories de 2 à 5 points) ; essai : section « Essai », 3 résolutions Composer vertes (`composer update --no-install --no-scripts --no-plugins`, dernière ligne `Writing lock file`) |
| Plusieurs formes combinées (alternatif) | ADR-0015, « Décision proposée » point 1 : métapaquet puis recette Flex sur le métapaquet, squelette écarté avec son coût de maintenance (tableau « Comparaison des trois formes ») |
| Pack avec dépendances internes (alternatif) | ADR-0015, « Relevé des dépendances internes » et « Décision proposée » point 3 ; essai 3 : trade-credit tire order-hold (`Locking tmonier/sylius-order-hold-plugin (1.0.0)`), registration-approval tire channel-access ; company-insights (exige company-accounts, exclu) reste hors pack |
| Plugin qui recoupe Sylius Plus (erreur) | ADR-0015, « Contexte » (7 plugins exclus, en termes neutres) et « Décision proposée » point 2 : aucun des 10 plugins des deux packs n'est classé « concurrent » ou « à confirmer » dans `docs/vitrine-plugins.md` ; `grep -c "Plus" docs/adr/0015-distributions-de-plugins.md` ne trouve aucune occurrence |
| Spike hors budget (erreur) | Non déclenché : spike mené en bien moins de 4 h ; questions ouvertes consignées en fin d'ADR-0015 |

## Décision (T-173-05, 2026-10-23)

Thibaut accepte l'ADR 0015 : forme, deux packs et quatre stories. Stories créées au backlog : US-175 (3), US-176 (5), US-177 (5), US-178 (2). Noms des paquets et pull request sur recipes-contrib à trancher au cadrage, GO requis pour toute publication.
