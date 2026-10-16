# Rétrospective — sprint 3 (fonctionnalités B2B)

Date : 2026-10-16. Format : Start / Stop / Continue + directive fondamentale.

> **Directive fondamentale** : quels qu'aient été les constats, nous croyons que chacun a fait de son mieux avec ce qu'il savait, ses compétences, les ressources et la situation du moment.

## Sprint Goal — atteint ?

Périmètre in-démo (après la décision du 2026-10-16 d'extraire US-019/020 en plugins autonomes) : référence PO et centre de coût au checkout, leur reprise partout, spike wishlist, correctifs inter-plugins.

**Atteint** pour les 4 stories in-démo (17 pts) : US-021, US-022, US-023 (ADR-0012), US-026. CI verte. US-019/020 (10 pts) sortent du sprint, par décision de périmètre et non par échec.

## Métriques

- Stories in-démo : 4/4 livrées (17/17 pts). 2 stories extraites en plugins (10 pts).
- CI : verte à chaque push (US-021, US-022, US-026) dès la première tentative — la repro locale a absorbé les erreurs en amont.
- Revue : 1 bug réel (validation jamais exécutée) + 5 défauts de conception corrigés ; 6 points reportés aux plugins.
- Tests unitaires : 53 (dont des tests exécutant le vrai Validator).

## Ce qui a bien marché (Continue)

- **Décider tôt du périmètre** : repérer en phase 1 la mention « Nouveau plugin publié » et poser la question avant la décomposition a évité de construire dans la démo ce qui devait être un plugin.
- **Repro locale systématique** : zéro aller-retour CI gaspillé sur ce sprint (contre plusieurs au sprint 2).
- **Vérifier les rapports de workers dans `vendor/`** : la route `tmonier_punchout_cxml_endpoint`, l'unicité de `sylius_order.number`, l'absence d'instanciation de `ReceivedPurchaseOrder` ont été prouvées, pas supposées.
- **`doctrine:schema:validate` en repro** : a révélé le défaut de mapping préexistant du plugin punchout.

## Ce qu'il faut arrêter / corriger (Stop)

- **Tests qui vérifient la déclaration au lieu du comportement** : le test d'US-021 constatait qu'une contrainte existait, pas qu'elle s'exécutait ; le bug des groupes de validation est passé à travers la CI. Exiger des tests qui passent par le vrai Validator / le vrai formulaire.
- **Déduire une provenance d'un champ libre** (les notes) : toujours établir l'origine côté serveur.
- **Agents de revue/recherche sans outils d'écriture** : le research-assistant n'a pas pu écrire l'ADR ; choisir un agent outillé quand un livrable fichier est attendu.

## À tenter (Start)

- Ajouter `doctrine:schema:validate --skip-sync` à la CI une fois le patch punchout publié.
- Exiger, dans chaque prompt de worker manipulant un formulaire Sylius, la mention des groupes de validation du type parent.
- Ouvrir les projets plugins US-019 (mode d'accès par canal) puis US-020 (inscription validée) avec la méthode SDD.

## Actions

1. Patchs plugins à proposer (accord de l'utilisateur avant publication) : punchout-cxml (R1, R2, R3, R6), container-fill (R5).
2. Projets plugins dédiés : US-019, puis US-020.
3. Sprint 4 (thème B2B) : inclut US-015 (comptes de démo sur la mire de login + correction de la mention obsolète de Marc).
