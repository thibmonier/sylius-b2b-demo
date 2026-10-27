# Registre des décisions : Sprint 031

> Action 3 de la rétrospective du sprint 28 : chaque décision ou GO est noté ici **avant** l'action, avec sa date et son **auteur** (Thibaut, orchestrateur ou worker). Une phrase « accepté par Thibaut » n'est écrite dans un document que si la décision figure dans ce registre avec Thibaut pour auteur.

| Date | Décision ou GO | Auteur | Tâche |
|------|----------------|--------|-------|
| 2026-10-27 | Périmètre du sprint 31 : US-208 chaîne d'approvisionnement PHP de la démo (5 pts), US-203 pages de compte des plugins devis, paiements à terme et assistants IA alignées sur « Mon compte », par surcharge dans la démo (5 pts), US-204 refonte de la page « Plugins » (3 pts), soit **13 points** ; plus les actions de la rétro du sprint 30 en tâches techniques | Thibaut | planning |
| 2026-10-27 | Ordre de sacrifice : US-204 d'abord, puis US-203 ; **US-208 protégée** | Thibaut | planning |
| 2026-10-27 | Maquettes dans l'unique projet Claude Design (canevas G2StRQigRKD6SqNRoyHpkW) : une planche pour le relevé de paiements à terme d'US-203 et la page « Plugins » d'US-204 ; les tâches Twig de ces deux parties sont 📅 jusqu'au GO de Thibaut sur la maquette, noté ici avant l'action. Les devis et les assistants IA réutilisent la maquette validée de « Mon compte » (US-199) | Thibaut | T-203-03, T-204-03 |
| 2026-10-27 | Page de consentement du plugin MCP (US-203) : cadre B2B seul (en-tête, charte, polices), **sans menu du compte** ; aucun changement de la logique de consentement | Thibaut | T-203-08 |
| 2026-10-27 | US-207 (indexation) vient après US-204 à US-206 ; US-209 (dépôts de plugins) vient après US-208 : **ni l'une ni l'autre dans ce sprint** | Thibaut | planning |
| 2026-10-27 | Les vérifications de production des 2026-10-26 et 2026-10-27 (sprints 28, 29 et 30) se font **en parallèle, dans une PR séparée**, **hors budget** du sprint 31 : leurs heures ne sont pas comptées dans les 35,3 h de capacité | Orchestrateur (périmètre rappelé à la demande de Thibaut) | hors sprint |
| 2026-10-27 | Actions 1 à 5 de la rétro du sprint 30 reprises en T-TECH-31-01 à 05 ; règles 24 (parallélisme réglé sur la machine) et 25 (réponses aux workers avec l'identifiant de la décision, règle 1 rappelée en tête) ajoutées à la consigne de worker | Orchestrateur | T-TECH-31-05 |
| 2026-10-27 | LCP mobile de la home en production 1 616 ms (FR) et 1 588 ms (EN) pour un seuil de 1 456 ms (T-TECH-29-03, PR #123) : **tâche technique dans le sprint 31** (T-TECH-31-11, 2 h) ; ordre de sacrifice : US-204, puis T-TECH-31-11, puis US-203 ; US-208 protégée | Thibaut | T-TECH-31-11 |
| 2026-10-27 | Page Crédits des images à 1 046 px de large sur 360 px (T-196-09, T-197-09) : correctif hors sprint, PR #124, fusionnée verte selon le GO permanent des PR de la démo ; T-196-09 et T-197-09 remesurées après son déploiement | Orchestrateur | PR #124 |
