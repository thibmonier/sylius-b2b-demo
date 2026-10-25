# Registre des décisions : Sprint 030

> Action 3 de la rétrospective du sprint 28 : chaque décision ou GO est noté ici **avant** l'action, avec sa date et son **auteur** (Thibaut, orchestrateur ou worker). Une phrase « accepté par Thibaut » n'est écrite dans un document que si la décision figure dans ce registre avec Thibaut pour auteur.

| Date | Décision ou GO | Auteur | Tâche |
|------|----------------|--------|-------|
| 2026-10-25 | Périmètre du sprint 30 : US-199 « Mon compte » (5 pts), US-201 tableau de bord des achats (5 pts), US-202 catégories de taxe (3 pts), soit 13 points ; plus les deux tâches techniques reportées pour la troisième fois (cache de rendu unique, `bin/proof-rebase`) en tête de sprint et les autres actions de la rétro du sprint 29 | Thibaut | planning |
| 2026-10-25 | US-201 : refonte par **surcharge dans la démo** (hooks Twig et styles du thème B2B) ; aucune modification ni release du plugin company-insights | Thibaut | planning |
| 2026-10-25 | Ordre de sacrifice : US-202 d'abord, puis US-201 ; US-199 protégée | Thibaut | planning |
| 2026-10-25 | US-202 : la limite d'approbation compare le **total HT** (comportement inchangé une fois la TVA affichée) | Thibaut | T-202-01, T-202-05 |
| 2026-10-25 | Maquettes Claude Design pour US-199 et US-201 dans le canevas unique (nouvelles pages), validées par Thibaut avant tout Twig, tâches de code 📅 jusqu'au GO ; US-202 sans maquette (la maquette du panier validée au sprint 29 montre déjà HT, TVA et TTC) | Thibaut | T-199-03, T-201-03 |
| 2026-10-25 | Contrôles de production ouverts (T-197-09, T-195-06, T-196-09, T-196-10, T-TECH-29-03) après la remise à zéro de 03:00 du 2026-10-26 : hors budget du sprint 30, en lecture seule | Thibaut | hors sprint |
| 2026-10-25 | Règles 21 à 23 de la consigne (fichiers partagés déclarés, aucun test depuis un worktree au `vendor` en lien symbolique, capture à 1280 px relue avant la PR), issues de la rétro du sprint 29 | Orchestrateur | T-TECH-30-05 |
| 2026-10-25 | GO de la maquette « Mon compte » (projet de maquettes unique, page « Mon compte (US-199) ») : accueil par rôle (Claire, Paul, Isabelle, Nathalie), menu regroupé selon les droits, « À faire » en tête, raccourcis, dernières commandes, entreprise, paiement à terme, listes et devis ; B2C inchangé | Thibaut | T-199-04 |
| 2026-10-25 | GO de la maquette du tableau de bord des achats (page « Tableau de bord des achats (US-201) ») : « À traiter » en tête, 4 indicateurs, graphique N / N-1 propre à la démo avec tableau, tops en barres, sites et centres de coût, approbations et encours, état vide réel | Thibaut | T-201-04 |
| 2026-10-25 | Historique des commandes de démonstration gardé à 18 mois (comparaison N-1 partielle, 6 mois sur 12) ; aucune story | Thibaut | T-201-04 |
| 2026-10-25 | US-202, limite comparée au total HT : le plugin sylius-purchase-approval-plugin évolue (résolveur de montant, HT par défaut ou configurable, tests) ; branche et PR du plugin autorisées ; **tag et release sur un GO séparé** de Thibaut ; montée de version dans la démo ensuite ; ordre de sacrifice applicable si la marge est dépassée | Thibaut | T-202-05 |
| 2026-10-25 | US-202 : l'historique de commandes généré (BulkOrderWriter) reste sans TVA, pour ne pas changer en silence le tableau de bord ; la livraison est taxée comme la marchandise (TVA 20 %, conforme à la maquette du panier validée) | Orchestrateur | T-202-02, T-202-03 |
| 2026-10-25 | GO fusion de la PR #9 de sylius-purchase-approval-plugin (réglage amount_basis, CI verte sur 21 contrôles), tag v1.4.0 et release GitHub ; aucune promotion | Thibaut | T-202-05 |
