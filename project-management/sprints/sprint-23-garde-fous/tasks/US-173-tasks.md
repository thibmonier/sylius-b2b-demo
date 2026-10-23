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
| T-173-01 | [DOC] | Relevé : classement US-063, `require` et `suggest` des 18 plugins sans recoupement, dépendances internes entre eux | 0,5 h | - | Claude | 🔲 |
| T-173-02 | [OPS] | Essai d'un métapaquet « IA et headless » dans un projet jetable (dépôt de type `path`) : ce qui reste à faire à la main (bundles, configuration, routes, migrations) | 1,25 h | T-173-01 | Claude | 🔲 |
| T-173-03 | [DOC] | Comparaison des trois formes (métapaquet, recettes Flex sur `recipes-contrib`, squelette de projet) : effet, maintenance, publication | 0,75 h | T-173-02 | Claude | 🔲 |
| T-173-04 | [DOC] | ADR : forme retenue, composition des packs, découpage de la réalisation en stories de 8 points au plus ; table « scénario → preuve » remplie | 1 h | T-173-03 | Claude | 🔲 |
| T-173-05 | [REV] | 📅 Thibaut relit l'ADR et décide (forme, packs, stories à planifier) | 0,25 h | T-173-04 | Thibaut | 🔲 |
| T-173-06 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml` et stories de réalisation créées au backlog | 0,25 h | T-173-05 | Orchestrateur | 🔲 |

**Total estimé : 4 h** (6 tâches, dont 0,25 h de Thibaut ; 3,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| ADR et découpage (nominal) | |
| Plusieurs formes combinées (alternatif) | |
| Pack avec dépendances internes (alternatif) | |
| Plugin qui recoupe Sylius Plus (erreur) | |
| Spike hors budget (erreur) | |
