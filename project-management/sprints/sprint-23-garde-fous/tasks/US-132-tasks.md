# Tâches : US-132 : Spike P7 : facture électronique France (PA/PDP) et e-reporting

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut
- **Story Points** : 3 (Could, n°2)
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-132](../../../backlog/user-stories/US-132.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** cadrer la transmission des factures à une plateforme agréée et l'e-reporting
**Afin de** proposer une offre à jour du calendrier réglementaire

## Règles et contraintes

- Sources officielles uniquement (administration fiscale, Légifrance, annuaire des plateformes), avec date de consultation ; aucune affirmation non sourcée, aucun avis juridique.
- Démonstration éventuelle vers une plateforme fictive, nommée comme telle.
- Ton neutre, sans comparaison avec une offre commerciale concurrente.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Note datée : `docs/` (nom fixé au cadrage)
- ADR : `docs/adr/0015-*.md`, `docs/adr/index.md`
- Lecture : dépôt `../sylius-facturx-plugin`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-132-01 | [DOC] | Sources officielles relevées (calendrier, formats, annuaire des plateformes, e-reporting), avec URL et date de consultation | 1,5 h | - | Claude | 🔲 |
| T-132-02 | [DOC] | Couverture du plugin facturx relevée dans son dépôt (formats produits, profils) et manques pour la transmission et l'e-reporting | 0,75 h | - | Claude | 🔲 |
| T-132-03 | [DOC] | Note datée : flux de transmission, e-reporting, démonstration vers une plateforme fictive ; règles non confirmées marquées « à vérifier » | 1,5 h | T-132-01, T-132-02 | Claude | 🔲 |
| T-132-04 | [DOC] | ADR et découpage en stories de 8 points au plus | 1 h | T-132-03 | Claude | 🔲 |
| T-132-05 | [REV] | Relecture : chaque affirmation sourcée ou marquée, pas d'avis juridique, ton neutre ; table « scénario → preuve » remplie | 0,5 h | T-132-04 | Claude | 🔲 |
| T-132-06 | [REV] | 📅 Thibaut relit la note et décide de la suite (stories à planifier ou non) | 0,25 h | T-132-05 | Thibaut | 🔲 |

**Total estimé : 5,5 h** (6 tâches, dont 0,25 h de Thibaut ; 5,25 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Note datée et ADR (nominal) | |
| Plateforme fictive (alternatif) | |
| Lien avec Factur-X (alternatif) | |
| Information incertaine (erreur) | |
| Calendrier réglementaire modifié (erreur) | |
