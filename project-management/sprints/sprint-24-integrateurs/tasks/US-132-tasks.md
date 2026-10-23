# Tâches : US-132 : Spike P7 : facture électronique France (PA/PDP) et e-reporting

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut
- **Story Points** : 3 (Should, sacrifice n°2)
- **Sprint** : sprint-24-integrateurs
- **Story** : [US-132](../../../backlog/user-stories/US-132.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** cadrer la transmission des factures à une plateforme agréée et l'e-reporting
**Afin de** proposer une offre à jour du calendrier réglementaire

## Règles et contraintes

- **Spike borné à 4 h** pour les tâches T-132-01 à T-132-05 (4 h exactement) ; livrable : une note datée et l'ADR 0016 (`docs/adr/0016-facture-electronique-pa-pdp.md`) avec le découpage en stories de 8 points au plus.
- **Sources officielles uniquement** (calendrier de la réforme, formats, annuaire des plateformes) ; chaque affirmation cite sa source et sa date de consultation. Une règle non confirmée par une source officielle est marquée « à vérifier », jamais affirmée. Si une échéance a changé depuis une source plus ancienne, la source officielle la plus récente est retenue et l'écart est signalé.
- **Aucun avis juridique.** Aucune plateforme réelle : la démonstration décrit un flux vers une **plateforme fictive**, nommée comme telle.
- Le plugin `facturx` existe : la note dit ce qu'il couvre déjà et ce qui manque (transmission, statuts de cycle de vie, e-reporting). Lecture seule du dépôt du plugin.
- `docs/adr/index.md` n'a qu'un écrivain, l'orchestrateur (T-132-07), à cause du chevauchement avec US-130.
- Aucun nouveau dépôt, paquet ni publication sans GO de Thibaut.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `docs/adr/0016-facture-electronique-pa-pdp.md` (nouveau)
- `docs/adr/index.md` (ligne ajoutée par l'orchestrateur)
- Lecture : dépôt `sylius-facturx-plugin` (code, README, `composer.json`), sources officielles en ligne

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules (aucune tâche [TEST] : story de documentation).

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-132-01 | [DOC] | Cadrage : questions du spike, liste des sources officielles à lire (calendrier, formats, annuaire, spécifications externes), grille de datation (date de consultation), plan de l'ADR | 0,5 h | - | Claude | ✅ |
| T-132-02 | [DOC] | Lecture des sources officielles et note datée : échéances, formats, flux de facturation contre e-reporting, rôle des plateformes agréées ; règle non confirmée marquée « à vérifier » ; écart entre sources signalé | 1,25 h | T-132-01 | Claude | ✅ |
| T-132-03 | [DOC] | Écart avec le plugin `facturx` : ce qu'il couvre (génération Factur-X) et ce qui manque (transmission, statuts, e-reporting), lecture de son code et de son README | 0,75 h | T-132-01 | Claude | ✅ |
| T-132-04 | [DOC] | Démonstration sans plateforme réelle : flux de la commande à la facture Factur-X puis dépôt vers une « plateforme fictive » nommée comme telle, statuts de retour simulés | 0,75 h | T-132-02, T-132-03 | Claude | ✅ |
| T-132-05 | [DOC] | ADR 0016 : décision, périmètre, découpage en stories de 8 points au plus, questions ouvertes ; table « scénario → preuve » remplie ; relecture « pas d'avis juridique » | 0,75 h | T-132-04 | Claude | ✅ |
| T-132-06 | [REV] | 📅 Thibaut relit l'ADR et décide (périmètre, stories à planifier) | 0,25 h | T-132-05 | Thibaut | ✅ |
| T-132-07 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml`, ligne de l'ADR dans `docs/adr/index.md`, stories de réalisation créées au backlog | 0,25 h | T-132-06 | Orchestrateur | ✅ |

**Total estimé : 4,5 h** (7 tâches, dont 0,25 h de Thibaut ; 4,25 h pour Claude et l'orchestrateur, dont 4 h de spike).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Note datée et ADR (nominal) | [ADR 0016](../../../../docs/adr/0016-facture-electronique-pa-pdp.md), sections « Méthode et datation », « Calendrier » et « Sources » (date de consultation 2026-10-23), « Découpage en stories » |
| Plateforme fictive (alternatif) | [ADR 0016](../../../../docs/adr/0016-facture-electronique-pa-pdp.md), section « Démonstration sans plateforme réelle » (« PA fictive de démonstration ») |
| Lien avec Factur-X (alternatif) | [ADR 0016](../../../../docs/adr/0016-facture-electronique-pa-pdp.md), section « Écart avec le plugin `facturx` » (couvert / manque) |
| Information incertaine (erreur) | [ADR 0016](../../../../docs/adr/0016-facture-electronique-pa-pdp.md), mentions « à vérifier » dans « Méthode et datation », « Calendrier », « Formats », « Flux » et « Questions ouvertes » |
| Calendrier réglementaire modifié (erreur) | [ADR 0016](../../../../docs/adr/0016-facture-electronique-pa-pdp.md), sous-section « Écart entre sources » (calendrier de 2022 remplacé par la loi de finances 2024, art. 91 ; décret 2024-266 retenu) |

## Décision (T-132-06, 2026-10-23)

Thibaut ne planifie rien au sprint 24. L'ADR 0016 reste « Proposé » ; ses stories ne sont pas créées au backlog et la question revient au planning du sprint 25.
