# Tâches : US-130 : Spike P5 : connecteur ERP de référence

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut
- **Story Points** : 3 (Could, n°1)
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-130](../../../backlog/user-stories/US-130.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** cadrer un connecteur ERP de référence (export de commandes, import de prix et de stock)
**Afin de** décider de son découpage et de son périmètre

## Règles et contraintes

- Spike limité à 3 points. Les stories de découpage d'origine (US-136, US-137) sont abandonnées : le spike en propose de nouvelles, sans engagement.
- Capacités des ERP cibles (Sage, Divalto, Odoo, SAP Business One, Dynamics) tirées de leur documentation publique, avec date de consultation.
- Ton neutre, sans comparaison avec une offre commerciale concurrente.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- ADR : `docs/adr/0016-*.md`, `docs/adr/index.md`
- Schéma Mermaid dans l'ADR
- Lecture : dépôt `../sylius-webhook-outbox-plugin`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-130-01 | [DOC] | Contrat d'événements existant du plugin webhook-outbox relevé (événements, charge utile, rejeu, journal) | 0,75 h | - | Claude | 🔲 |
| T-130-02 | [DOC] | Documentation publique des 5 ERP cibles : formats d'import et d'export, API, avec URL et date de consultation | 1,5 h | - | Claude | 🔲 |
| T-130-03 | [DOC] | ADR : format de fichier plat, contrats, API, journal des échanges, périmètre (ou no-go avec périmètre réduit), schéma Mermaid | 1,5 h | T-130-01, T-130-02 | Claude | 🔲 |
| T-130-04 | [DOC] | Découpage en stories de 8 points au plus et démonstration avec un ERP fictif nommé comme tel | 0,75 h | T-130-03 | Claude | 🔲 |
| T-130-05 | [REV] | Relecture : sources citées ou « à vérifier », temps du spike consigné ; table « scénario → preuve » remplie | 0,5 h | T-130-04 | Claude | 🔲 |
| T-130-06 | [REV] | 📅 Thibaut relit l'ADR et décide (stories à planifier ou non) | 0,25 h | T-130-05 | Thibaut | 🔲 |

**Total estimé : 5,25 h** (6 tâches, dont 0,25 h de Thibaut ; 5 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| ADR et découpage (nominal) | |
| Décision no-go (alternatif) | |
| Démonstration sans ERP réel (alternatif) | |
| Dépassement (erreur) | |
| Affirmation sur un ERP non sourcée (erreur) | |
