# Registre des décisions : Sprint 036

> Action 3 de la rétrospective du sprint 28 : chaque décision ou GO est noté ici **avant** l'action, avec sa date et son **auteur** (Thibaut, orchestrateur ou worker). Une phrase « accepté par Thibaut » n'est écrite dans un document que si la décision figure dans ce registre avec Thibaut pour auteur. Règle 26 : toute PR hors sprint est notée ici avant son ouverture. Règle 30 : la règle d'exécution d'un GO est transmise au worker concerné avant ou avec son inscription ici ; un GO donné à l'orchestrateur ne se délègue que par une phrase explicite.

| Date | Décision ou GO | Auteur | Tâche |
|------|----------------|--------|-------|
| 2026-10-29 | Périmètre du sprint 36 : US-240 alertes garanties et captures (3 pts), US-230 squelette du plugin passeport (5 pts), US-231 page publique du passeport (3 pts), US-239 veille hebdomadaire (3 pts), soit **14 points** (écart d'un point sur la vélocité de 13 accepté) ; plus les actions de rétro T-TECH-36-01 à 05 | Thibaut | planning |
| 2026-10-29 | **Règle confirmée pour US-240** : un client est signalé « en retard » dès qu'il dépasse sa date de recommande prévue, dans la limite d'un intervalle médian de commande | Thibaut | US-240 |
| 2026-10-29 | **GO nouveau dépôt du plugin passeport numérique produit (US-230)** : `thibmonier/sylius-digital-product-passport-plugin` (Composer `tmonier/sylius-digital-product-passport-plugin`), public, durci comme US-209, topics habituels ; **fusion si la CI est verte** ; aucun tag ni release sans GO. Règle d'exécution : c'est **l'orchestrateur** qui crée le dépôt et fusionne ; le worker pousse sa branche et ouvre la PR, il ne crée aucun dépôt et ne fusionne pas | Thibaut | US-230 |
| 2026-10-29 | Actions de rétro du sprint 35 reprises comme tâches transverses : T-TECH-36-01 (hook pre-push refusant le force-push + ECS/PHPStan), T-TECH-36-02 (script wait-for-ci), T-TECH-36-03 (consigne worker durcie), T-TECH-36-04 (README bilingue + captures dans la DoD plugin), T-TECH-36-05 (script check-resources) | Orchestrateur | planning |
| 2026-10-29 | Contraintes machine : 8 Go RAM, OrbStack 2 Go ; `docker compose up -d mysql mailpit` seulement | Orchestrateur | planning |
| 2026-10-29 | Branche `proof/us-219-red` supprimée | Thibaut | planning |
| 2026-10-29 | US-232 et US-233 reportées au sprint 37 | Orchestrateur | planning |
| 2026-10-29 | **GO tag v0.1.1 du plugin prédictif** (classes pures IntervalCalculator et AlertCalculator, PR #5 fusionnée en 7ba09bb) ; démo montée en ^0.1.1 par l'orchestrateur | Thibaut | US-240 |
| 2026-10-29 | Commit 85ff026 (US-240) a embarqué par erreur les tests d'US-239 (`tests/Unit/Stack/VersionsReportTest.php`, assistant `extractSection`) : accepté en l'état, non poussé, la PR sera fusionnée en squash ; signalé par w239 | Orchestrateur | US-239, US-240 |
