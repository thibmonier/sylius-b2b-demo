# Tâches : US-130 : Spike P5 : connecteur ERP de référence

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut
- **Story Points** : 3 (Should, sacrifice n°1)
- **Sprint** : sprint-24-integrateurs
- **Story** : [US-130](../../../backlog/user-stories/US-130.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** cadrer un connecteur ERP de référence (export de commandes, import de prix et de stock)
**Afin de** décider de son découpage et de son périmètre

## Règles et contraintes

- **Spike borné à 4 h** pour les tâches T-130-01 à T-130-05 (4 h exactement) ; livrable : l'ADR 0017 (`docs/adr/0017-connecteur-erp-de-reference.md`), un schéma et le découpage en stories de 8 points au plus.
- Contenu à cadrer : contrat d'événements, format de fichier plat, API, journal des échanges, ERP cibles (Sage, Divalto, Odoo, SAP B1, Dynamics) à confirmer. Les stories de découpage d'origine (US-136, US-137) sont abandonnées : le spike propose de nouvelles stories, que Thibaut décide de planifier ou non.
- **Aucun ERP réel** : la démonstration s'appuie sur un fichier plat et sur le plugin `webhook-outbox` existant, avec un ERP fictif nommé comme tel.
- Toute capacité d'import ou d'export prêtée à un ERP cible cite la documentation publique de l'éditeur avec sa date de consultation, sinon elle est marquée « à vérifier ».
- Si le périmètre est jugé trop large, l'ADR conclut par un périmètre réduit (no-go partiel).
- `docs/adr/index.md` n'a qu'un écrivain, l'orchestrateur (T-130-07), à cause du chevauchement avec US-132.
- Aucun nouveau dépôt, paquet ni publication sans GO de Thibaut.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `docs/adr/0017-connecteur-erp-de-reference.md` (nouveau)
- `docs/adr/index.md` (ligne ajoutée par l'orchestrateur)
- Lecture : dépôt `sylius-webhook-outbox-plugin` (événements, journal, README), documentation publique des ERP cibles

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules (aucune tâche [TEST] : story de documentation).

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-130-01 | [DOC] | Cadrage : contrat d'événements de `webhook-outbox` relu, questions du spike, liste des ERP cibles et des capacités à sourcer, plan de l'ADR | 0,5 h | - | Claude | 🔲 |
| T-130-02 | [DOC] | Relevé sourcé des capacités d'import et d'export de chaque ERP cible (documentation publique de l'éditeur, date de consultation) ; sinon « à vérifier » | 1,25 h | T-130-01 | Claude | 🔲 |
| T-130-03 | [DOC] | Contrats : export de commandes (événements puis fichier plat), import de prix et de stock (format, API), journal des échanges, reprise et idempotence | 0,75 h | T-130-01 | Claude | 🔲 |
| T-130-04 | [DOC] | Démonstration sans ERP réel : fichier plat et `webhook-outbox`, ERP fictif nommé comme tel, schéma des échanges | 0,75 h | T-130-02, T-130-03 | Claude | 🔲 |
| T-130-05 | [DOC] | ADR 0017 : format, contrats, périmètre (avec périmètre réduit si trop large), découpage en stories de 8 points au plus ; table « scénario → preuve » remplie | 0,75 h | T-130-04 | Claude | 🔲 |
| T-130-06 | [REV] | 📅 Thibaut relit l'ADR et décide (périmètre, stories à planifier) | 0,25 h | T-130-05 | Thibaut | 📅 |
| T-130-07 | [DOC] | Décision consignée dans `.bmad/sprint-status.yaml`, ligne de l'ADR dans `docs/adr/index.md`, stories de réalisation créées au backlog | 0,25 h | T-130-06 | Orchestrateur | 🔲 |

**Total estimé : 4,5 h** (7 tâches, dont 0,25 h de Thibaut ; 4,25 h pour Claude et l'orchestrateur, dont 4 h de spike).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| ADR et découpage (nominal) | |
| Décision no-go (alternatif) | |
| Démonstration sans ERP réel (alternatif) | |
| Dépassement (erreur) | |
| Affirmation sur un ERP non sourcée (erreur) | |
