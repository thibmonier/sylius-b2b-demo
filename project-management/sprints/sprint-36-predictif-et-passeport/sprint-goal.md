# Sprint 36 : Prédictif et Passeport — compléter le commerce prédictif, lancer le DPP, veille et actions de rétro

> **Planifié le 2026-10-29**, après la clôture du sprint 35 (PR #133). Périmètre décidé par Thibaut le 2026-10-29 : **14 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches sera faite au planning. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-36`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 36 |
| Début | 2026-10-29 (planning) |
| Fin | **2026-11-13** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **14 points** (écart d'un point sur la vélocité de 13 accepté) ; tâches à décomposer |
| Vélocité de référence | S30 13, S31 13, S32 9, S33 13, S34 9, S35 13 |
| Branche | `feature/sprint-36-predictif-et-passeport` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre retient 14 points sur la vélocité de 13 (écart d'un point accepté) : suite d'US-219 (US-240, 3 pts), lancement du plugin passeport numérique produit (US-230, 5 pts + US-231, 3 pts) et veille hebdomadaire (US-239, 3 pts). Les actions de rétro du sprint 35 (T-TECH-36-01 à 05) seront intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, OrbStack 2 Go ; `docker compose up -d mysql mailpit` seulement.

## Sprint Goal

> « Garantir une démo prédictive vivante à toute date (alertes garanties), lancer le passeport numérique produit avec sa page publique, et automatiser la veille hebdomadaire des versions du socle. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-240 : 1+2+2, US-230 : 1+2+2, US-231 : 1+2+2, US-239 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-240](../../backlog/user-stories/US-240.md) | Commerce prédictif : alertes garanties à toute date dans la démo et captures (suite d'US-219) | 3 | Must | ready-for-dev | Suite d'US-219 partielle ; plugin prédictif v0.1.1 |
| [US-230](../../backlog/user-stories/US-230.md) | Passeport numérique produit : squelette du plugin et modèle de données ouvert et versionné | 5 | Must | ready-for-dev | Nouveau dépôt ; tag et Packagist sur GO de Thibaut |
| [US-231](../../backlog/user-stories/US-231.md) | Passeport numérique produit : page publique du passeport | 3 | Must | ready-for-dev | Dépend d'US-230 |
| [US-239](../../backlog/user-stories/US-239.md) | Veille hebdomadaire des versions du socle et récapitulatif des montées majeures | 3 | Must | ready-for-dev | Issue GitHub hebdomadaire |

Total engagé : **14 points**.

- **US-240** complète US-219 : garantir au moins 2 alertes à toute date de remise à zéro (aucune nouvelle commande, conception de l'historique existant), recalcul du plugin v0.1.1, captures pour le README.
- **US-230** crée le dépôt `thibmonier/sylius-digital-product-passport-plugin` (paquet Composer `tmonier/sylius-digital-product-passport-plugin`), public, durci comme US-209 ; modèle de données ouvert et versionné (ESPR, formulation prudente). **Règle : orchestrateur crée le dépôt et fusionne ; worker pousse branche et PR seulement ; aucun tag ni release sans GO de Thibaut.**
- **US-231** affiche la page publique du passeport : données structurées, conformité ESPR, ton prudent ; dépend d'US-230.
- **US-239** automatise la veille hebdomadaire des versions : issue GitHub chaque lundi, récapitulatif des Dependabot PRs de la démo, montées majeures résumées pour le backlog.
- **Tâches techniques** (actions de rétro du sprint 35) : hook pre-push refusant le force-push + ECS/PHPStan (T-TECH-36-01), script wait-for-ci (T-TECH-36-02), consigne worker durcie (T-TECH-36-03), README bilingue + captures dans la DoD plugin (T-TECH-36-04), script check-resources (T-TECH-36-05).

**Hors sprint** : US-232 et US-233 reportées au sprint 37.

## Décisions de Thibaut (2026-10-29, registre)

1. Périmètre : US-240 (3), US-230 (5), US-231 (3), US-239 (3), soit 14 points, plus les actions de rétro T-TECH-36-01 à 05.
2. **Règle confirmée pour US-240** : un client est signalé « en retard » dès qu'il dépasse sa date de recommande prévue, dans la limite d'un intervalle médian de commande.
3. **GO nouveau dépôt du plugin passeport (US-230)** : création du dépôt public durci comme US-209 (topics habituels), push du code par PR, fusion si la CI est verte, aucun tag ni release sans GO de Thibaut. Règle d'exécution : **l'orchestrateur crée le dépôt et fusionne** ; le worker pousse sa branche et ouvre la PR, il ne crée aucun dépôt et ne fusionne pas.
4. Contraintes machine : 8 Go RAM, OrbStack 2 Go ; `docker compose up -d mysql mailpit` seulement.
5. Actions de rétro du sprint 35 (T-TECH-36-01 à 05) : intégrées comme tâches transverses.
6. Branche `proof/us-219-red` supprimée.

## Estimation du sprint

À compléter après décomposition en tâches.

## Politique Sylius Plus

Ton neutre partout. Le spike US-229 (sprint 34) a vérifié la formulation prudente du passeport numérique produit : aucun recoupement connu avec Sylius Plus. En cas de recoupement avéré pendant le sprint, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugin). Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-29)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 35 fusionné (PR #133), US-217, US-218, US-238 en done, US-219 partielle
- [x] Actions de la rétrospective du sprint 35 reprises :
  - action 1 : T-TECH-36-01 (hook pre-push refusant le force-push + ECS/PHPStan) ;
  - action 2 : T-TECH-36-02 (script wait-for-ci) ;
  - action 3 : T-TECH-36-03 (consigne worker durcie) ;
  - action 4 : T-TECH-36-04 (README bilingue + captures dans la DoD plugin) ;
  - action 5 : T-TECH-36-05 (script check-resources).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-29 (roadmap-innovation, sprint 36)
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [ ] Capacité calculée : à calculer après décomposition
- [x] Fiches US-240, US-230, US-231, US-239 à jour (en-tête « Sprint 36 »)
- [x] Branche `feature/sprint-36-predictif-et-passeport` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md) (US-231 ← US-230)
- [ ] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO du dépôt passeport et tags demandés quand les plugins sont fusionnés

## Definition of Ready (contrôle du 2026-10-29)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-240 | oui | oui (1+2+2) | plugin prédictif v0.1.0 installé ; US-219 partielle | prête (dépendance US-219) |
| US-230 | oui | oui (1+2+2) | nouveau dépôt créé par l'orchestrateur sur GO de Thibaut ; squelette de CI copié d'un plugin existant | prête (porte : GO création dépôt, donné au registre) |
| US-231 | oui | oui (1+2+2) | US-230 fusionnée | prête (dépendance US-230) |
| US-239 | oui | oui (1+2+2) | GitHub Actions, démo sur GitHub | prête |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code (US-240, US-230, US-231, US-239) : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-230, US-231 : dépôt durci comme US-209 (workflows aux actions figées par SHA, `permissions` minimales, `persist-credentials: false`, zizmor, `composer audit`, Dependabot) ; CI Sylius 2.2/2.3 verte ; README anglais + `README.fr.md` français (installation, usage, lien vers la démo, lien vers le profil de l'auteur) + captures ; ton neutre (politique Sylius Plus) ; Packagist soumis par Thibaut ; aucun tag sans GO
- [ ] US-240 : plugin prédictif v0.1.1 ; captures pour le README ; rendu réel vérifié en FR et EN
- [ ] US-239 : issue GitHub hebdomadaire créée, récapitulatif des PR Dependabot, montées majeures résumées
- [ ] **Tout état écrit hors base** (règle 16) : le dépôt du plugin passeport est créé une fois, à son URL définitive ; aucune variable d'environnement nouvelle dans la démo ce sprint
