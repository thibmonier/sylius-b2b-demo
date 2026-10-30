# Sprint 37 : Copilote et Passeport — lancer le copilote d'achat, compléter le passeport numérique produit, veille et actions de rétro

> **Planifié le 2026-10-30**, après la clôture du sprint 36 (PR #135). Périmètre décidé par Thibaut le 2026-10-30 : **13 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches sera faite au planning. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-37`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 37 |
| Début | 2026-10-30 (planning) |
| Fin | **2026-11-13** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** (vélocité de référence) ; tâches à décomposer |
| Vélocité de référence | S31 13, S32 9, S33 13, S34 9, S35 8, S36 11 |
| Branche | `feature/sprint-37-copilote-et-passeport` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre retient 13 points sur la vélocité de référence : copilote d'achat (US-211, 5 pts), passeport numérique produit (US-232, 3 pts ; US-233, 3 pts ; US-241, 2 pts). Les actions de rétro du sprint 36 (T-TECH-37-01 à 05) seront intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, OrbStack 2 Go ; `docker compose up -d mysql mailpit` seulement ; au plus deux workers lancent des tests en même temps.

## Sprint Goal

> « Lancer le copilote d'achat B2B avec son widget du compte et son adaptateur rejoué, compléter le passeport numérique produit avec QR, lien GS1 Digital Link et pièces compatibles, et installer le plugin passeport dans la démo. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-211 : 1+2+2, US-232 : 1+2+2, US-233 : 1+2+2, US-241 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-211](../../backlog/user-stories/US-211.md) | Copilote d'achat : squelette du plugin, widget du compte et adaptateur rejoué | 5 | Must | ready-for-dev | Nouveau dépôt ; tag et Packagist sur GO de Thibaut |
| [US-232](../../backlog/user-stories/US-232.md) | Passeport numérique produit : QR et lien GS1 Digital Link sur le produit et l'emballage | 3 | Must | ready-for-dev | Dépend d'US-231 (v0.1.0) |
| [US-233](../../backlog/user-stories/US-233.md) | Passeport numérique produit : pièces détachées compatibles liées à customer-asset-management | 3 | Must | ready-for-dev | Dépend d'US-230 ; plugin customer-asset-management publié |
| [US-241](../../backlog/user-stories/US-241.md) | Passeport numérique produit : installation dans la démo, captures et audit pa11y AAA | 2 | Must | backlog | Dépend d'US-231 ; captures reportées d'US-231 |

Total engagé : **13 points**.

- **US-211** crée le dépôt `thibmonier/sylius-b2b-sales-copilot-plugin` (paquet Composer `tmonier/sylius-b2b-sales-copilot-plugin`), public, durci comme US-209 ; widget Stimulus dans « Mon compte » B2B, adaptateur rejoué. **Règle : orchestrateur crée le dépôt et fusionne ; worker pousse branche et PR seulement ; aucun tag ni release sans GO de Thibaut.**
- **US-232** génère un QR et un lien GS1 Digital Link pour chaque passeport (GTIN et lot).
- **US-233** affiche les pièces détachées compatibles d'un équipement depuis son passeport (lien optionnel avec customer-asset-management).
- **US-241** installe le plugin passeport ^0.1 dans la démo, configure des passeports de démonstration, capture la page publique pour les README EN/FR du plugin passeport, audit pa11y AAA (reporté d'US-231).
- **Tâches techniques** (actions de rétro du sprint 36) : vérification automatique que chaque fichier de tests/ appartient à une suite exécutée en CI (T-TECH-37-01), squelette de plugin avec e2e_js: "yes" et suite all par défaut (T-TECH-37-02), contrôle de conformité des rapports de workers (T-TECH-37-03), bin/render-page respecte TEST_DB_NAME (T-TECH-37-04), règle 42 : vérifier .gitignore au premier commit d'un nouveau dépôt (T-TECH-37-05).

**Hors sprint** : US-212 reportée au sprint 38.

## Décisions de Thibaut (2026-10-30, registre)

1. Périmètre : US-211 (5), US-232 (3), US-233 (3), US-241 (2), soit 13 points, plus les actions de rétro T-TECH-37-01 à 05.
2. **GO nouveau dépôt du copilote d'achat (US-211)** : `thibmonier/sylius-b2b-sales-copilot-plugin` (Composer `tmonier/sylius-b2b-sales-copilot-plugin`), public, MIT, durci comme US-209, topics habituels ; l'orchestrateur crée le dépôt et fusionne les PR à CI verte ; le worker pousse sa branche et ouvre la PR ; aucun tag ni release sans GO ; Packagist par Thibaut.
3. Contraintes machine : 8 Go RAM, OrbStack 2 Go ; `docker compose up -d mysql mailpit` seulement ; au plus deux workers lancent des tests en même temps.
4. Actions de rétro du sprint 36 (T-TECH-37-01 à 05) : intégrées comme tâches transverses.

## Estimation du sprint

À compléter après décomposition en tâches.

## Politique Sylius Plus

Ton neutre partout. Le spike US-210 a vérifié la formulation prudente du copilote d'achat et du passeport numérique produit : aucun recoupement connu avec Sylius Plus. En cas de recoupement avéré pendant le sprint, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugin). Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-30)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 36 fusionné (PR #135), US-240, US-231, US-239 en done, US-230 v0.1.0 publié
- [x] Actions de la rétrospective du sprint 36 reprises :
  - action 1 : T-TECH-37-01 (vérification automatique que chaque fichier de tests/ appartient à une suite exécutée en CI) ;
  - action 2 : T-TECH-37-02 (squelette de plugin avec e2e_js: "yes" et suite all par défaut) ;
  - action 3 : T-TECH-37-03 (contrôle de conformité des rapports de workers) ;
  - action 4 : T-TECH-37-04 (bin/render-page respecte TEST_DB_NAME) ;
  - action 5 : T-TECH-37-05 (règle 42 : vérifier .gitignore au premier commit d'un nouveau dépôt).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-30 (roadmap-innovation, sprint 37)
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [ ] Capacité calculée : à calculer après décomposition
- [x] Fiches US-211, US-232, US-233, US-241 à jour (en-tête « Sprint 37 »)
- [x] Branche `feature/sprint-37-copilote-et-passeport` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md) (US-232 ← US-231, US-233 ← US-230, US-241 ← US-231)
- [ ] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO du dépôt copilote et tags demandés quand les plugins sont fusionnés

## Definition of Ready (contrôle du 2026-10-30)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-211 | oui | oui (1+2+2) | nouveau dépôt créé par l'orchestrateur sur GO de Thibaut ; squelette de CI copié d'un plugin existant | prête (porte : GO création dépôt, donné au registre) |
| US-232 | oui | oui (1+2+2) | US-231 fusionnée, plugin passeport v0.1.0 | prête (dépendance US-231) |
| US-233 | oui | oui (1+2+2) | US-230 fusionnée, plugin customer-asset-management publié | prête (dépendance US-230) |
| US-241 | oui | oui (1+2+2) | US-231 fusionnée, plugin passeport v0.1.0 | prête (dépendance US-231) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code (US-211, US-232, US-233, US-241) : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-211 : dépôt durci comme US-209 (workflows aux actions figées par SHA, `permissions` minimales, `persist-credentials: false`, zizmor, `composer audit`, Dependabot) ; CI Sylius 2.2/2.3 verte ; README anglais + `README.fr.md` français (installation, usage, lien vers la démo, lien vers le profil de l'auteur) + captures ; ton neutre (politique Sylius Plus) ; Packagist soumis par Thibaut ; aucun tag sans GO
- [ ] US-232, US-233 : plugin passeport v0.1.1 ; captures pour le README ; rendu réel vérifié en FR et EN
- [ ] US-241 : plugin passeport ^0.1 installé dans la démo ; passeports de démonstration configurés ; captures de la page publique en FR et EN pour les README EN/FR du plugin passeport (PR du plugin, fusion à CI verte) ; audit pa11y AAA de la page publique
- [ ] **Tout état écrit hors base** (règle 16) : le dépôt du plugin copilote est créé une fois, à son URL définitive ; aucune variable d'environnement nouvelle dans la démo ce sprint

## Cérémonies

| Cérémonie | Date | Heure | Durée | Participants |
|-----------|------|-------|-------|--------------|
| Sprint Planning Part 1 (QUOI) | 2026-10-30 | 09:00 | 2h | PO, Scrum Master, équipe |
| Sprint Planning Part 2 (COMMENT) | 2026-10-30 | 14:00 | 2h | Équipe |
| Daily Scrum | Chaque jour | 09:30 | 15min | Équipe |
| Affinage du backlog | 2026-11-06 | 14:00 | 1h | PO, Scrum Master, équipe |
| Sprint Review | 2026-11-13 | 14:00 | 2h | PO, Scrum Master, équipe, parties prenantes |
| Rétrospective | 2026-11-13 | 16:30 | 1h30 | Scrum Master, équipe |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth
