# Registre des décisions : Sprint 033

> Action 3 de la rétrospective du sprint 28 : chaque décision ou GO est noté ici **avant** l'action, avec sa date et son **auteur** (Thibaut, orchestrateur ou worker). Une phrase « accepté par Thibaut » n'est écrite dans un document que si la décision figure dans ce registre avec Thibaut pour auteur. Règle 26 : toute PR hors sprint est notée ici avant son ouverture.

| Date | Décision ou GO | Auteur | Tâche |
|------|----------------|--------|-------|
| 2026-10-28 | Périmètre du sprint 33 : US-207 indexation des pages vitrine (5 pts) et US-209 chaîne d'approvisionnement des 27 dépôts de plugins et 2 packs (8 pts), soit **13 points** ; plus les reliquats techniques du sprint 32 (T-TECH-32-01, -03, -04) et le contrôle connecté d'US-203 | Thibaut | planning |
| 2026-10-28 | **GO dépôts de plugins (US-209)** : push d'une branche et ouverture d'une PR par dépôt, **fusion de chaque PR dont la CI est verte** ; **aucun tag ni release** (seules la CI et la configuration Composer changent) ; toute exception (CI rouge préexistante, dépôt sans CI) est remontée à Thibaut | Thibaut | US-209 |
| 2026-10-28 | Ordre de sacrifice : US-209 tranche (b) Composer d'abord, puis tranche (a) workflows ; **US-207 protégée** | Thibaut | planning |
| 2026-10-28 | Sitemap d'US-207 : Thibaut le déclare lui-même dans Google Search Console après le déploiement ; Claude fournit l'URL et les étapes, sans toucher à aucun compte | Thibaut | US-207 |
| 2026-10-28 | Disque à 5 Go libres, mémoire 1 Go, swap 3,5 Go au lancement : Thibaut prévenu ; au plus deux workers qui testent en même temps (règle 24), aucun test ni rendu sous 5 Go, suite complète laissée à la CI sous 6 Go | Orchestrateur | T-TECH-33 |
