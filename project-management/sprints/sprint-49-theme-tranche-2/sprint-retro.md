# Rétrospective du sprint 49 — Thème Industrie & Négoce, tranche 2

**Date** : 2026-11-05

> « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. » — Directive fondamentale

## Suivi des actions du sprint 48

| Action | Résultat |
|--------|----------|
| A-48-1 disque ≥ 10 Go avant les workers | Fait : 18 Go libres au lancement, aucun ENOSPC |
| A-48-2 comparer le SHA local et `headRefOid` | Fait pour chaque rapport ; a révélé qu'un worker n'avait pas revérifié après son second push |
| A-48-3 `main` de la démo vert au lancement | Fait : vert (cba3ada) |
| A-48-4 story GTIN | Fait au sprint 48 (US-272) |

## Ce qui a bien marché

- Trois workers en parallèle dans le même dépôt sans conflit bloquant : un fichier de hooks et un partiel SCSS par page, seuls `theme.scss` et les README ont demandé une résolution (concaténation).
- Le cas « thème inactif » a été posé comme scénario de test avant la fusion : le rouge (500) a été prouvé puis corrigé, au lieu d'être découvert en production.
- Le test de dérive a prouvé son utilité sur de vrais fichiers dès l'intégration (8 surcharges nommées).
- Sprint bouclé en une session : planification, implémentation, trois fusions.

## Ce qui a coincé

- Les fiches US-223/224 ne disaient pas comment le thème se comporte sur un canal qui ne le porte pas ; la question est venue de l'orchestrateur à la lecture de la PR.
- Deux schémas coexistent (surcharge au chemin du cœur pour US-223, hooks gardés pour US-224) : les deux sont justes, mais ma première consigne à w223 aurait imposé le second sans raison.
- Les captures et l'audit pa11y faits sur l'application de test ne servent pas de vitrine.
- Deux workers ont poussé un second commit sans attendre la fin du run en cours (consigne « un seul push »).
- La CI d'une PR tourne sur sa fusion avec `main` : fusionner le test de dérive avant les pages aurait rendu leurs PR rouges ; l'ordre de fusion a été choisi en conséquence.

## Actions

| ID | Action | Responsable | Échéance |
|----|--------|-------------|----------|
| A-49-1 | Pour toute story de thème, ajouter au cadrage le scénario « canal sans le thème : pages standard en 200 » | Claude | cadrage d'US-226, US-227, US-228 |
| A-49-2 | Lire la PR d'un worker avant de lui imposer un schéma décidé pour une autre story | Claude | chaque sprint |
| A-49-3 | Captures et audit d'accessibilité d'un thème ou d'un plugin d'interface : sur la démo, pas sur l'application de test | Claude | US-228 |
| A-49-4 | Dans le brief worker, préciser : après le premier push, attendre la fin du run avant tout autre push, même pour un test ajouté | Claude | sprint 50 |
