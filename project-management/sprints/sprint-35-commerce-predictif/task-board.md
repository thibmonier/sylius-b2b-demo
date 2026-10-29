# Task Board : Sprint 035 (commerce prédictif MVP)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut, du disque ou d'un déploiement | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

Total décomposé : **46 tâches, 37,5 h** (16 points). État au 2026-10-28 : **4 ✅, 38 🔲, 4 📅**. Les 📅 attendent le GO du tag v0.1.0 (T-219-03) et la libération du disque (T-TECH-35-05, T-TECH-35-06).

Règle : une tâche passe en ✅ dans le commit qui la livre. **Rouge prouvé (règle 27)** : une story de code n'est comptée « rouge prouvé » que si le commit de ses tests rouges cite l'échec observé de chaque test.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-217-01 | US-217 | DOC | Cadrage et table des invariants (règle 28) : nouveau dépôt, aucun test existant | 0,5 h | Claude |
| T-217-02 | US-217 | TEST | Tests d'installation rouges, **rouge prouvé (règle 27 et 33)** : squelette de CI copié de quick-order, paquet installable | 1 h | Claude |
| T-217-03 | US-217 | DB | Requête SQL calcul intervalle médian par entreprise et référence : commandes complétées, au moins 3, médiane des intervalles | 2 h | Claude |
| T-217-04 | US-217 | BE | Service de domaine `PredictiveReorderCalculator` : interface, implémentation SQL, Value Object `ReorderPrediction` | 2 h | Claude |
| T-217-05 | US-217 | OPS | Recalcul planifié : commande Symfony `app:predictive:recalculate`, enregistrement `messenger.yaml`, quotidien | 1 h | Claude |
| T-217-06 | US-217 | OPS | CI Sylius 2.2/2.3 : matrice PHP 8.3 et 8.5, workflows durcis (SHA, `permissions`, zizmor, audit, Dependabot) | 1,5 h | Claude |
| T-217-07 | US-217 | TEST | Tests et contrôles : projet vierge Sylius 2.3, installation, calcul d'intervalle 3/5/7 commandes, médiane, cloisonnement | 2 h | Claude |
| T-217-08 | US-217 | DOC | README français : installation, usage, lien démo, profil auteur, ton neutre | 0,5 h | Claude |
| T-217-09 | US-217 | REV | PHPStan max (dépôt entier), ECS, Rector, lints, rejoués avant push | 0,5 h | Claude |
| T-217-10 | US-217 | OPS | Push branche `feature/predictive-mvp`, PR ; orchestrateur fusionne CI verte ; **aucun tag ni release** | 0,25 h | Claude |
| T-217-11 | US-217 | REV | Revue (aucun détail sensible, ton neutre, pas de comparaison Sylius Plus) et table « scénario → test ou preuve » | 0,25 h | Claude |
| T-218-01 | US-218 | DOC | Cadrage et table des invariants (règle 28) : page « Mon compte », lien reorder existant, tests touchés | 0,5 h | Claude |
| T-218-02 | US-218 | TEST | Tests rouges, **rouge prouvé (règle 27)** : bloc affiché si alerte dans 7 jours, explication, bouton, masquable, cloisonnement | 1 h | Claude |
| T-218-03 | US-218 | FE-WEB | Bloc « À réassortir bientôt » dans « Mon compte » : requête prédictions 7 jours, affichage référence, explication, bouton | 2 h | Claude |
| T-218-04 | US-218 | BE | Bouton « Préparer le panier » : lien reorder existant, quantité habituelle (médiane), panier pré-rempli, redirection panier | 2 h | Claude |
| T-218-05 | US-218 | BE | Masquage d'alerte : entité `DismissedPrediction` (référence, entreprise, utilisateur, type), filtrage des alertes affichées | 1,5 h | Claude |
| T-218-06 | US-218 | TEST | Tests et contrôles : alerte affichée 7 jours, panier pré-rempli quantité, alerte masquée, aucune échéance, référence retirée, cloisonnement | 2 h | Claude |
| T-218-07 | US-218 | DOC | Captures (page « Mon compte » avec alertes, panier pré-rempli) et mise à jour README du plugin (section « Alertes ») | 0,5 h | Claude |
| T-218-08 | US-218 | REV | PHPStan max (dépôt plugin et démo), ECS, Rector, lints, rejoués avant push | 0,5 h | Claude |
| T-218-09 | US-218 | OPS | PR dépôt plugin (branche `feature/predictive-alerts`) et PR démo ; orchestrateur fusionne CI verte ; aucun tag ni release | 0,25 h | Claude |
| T-218-10 | US-218 | REV | Revue (aucun détail sensible, ton neutre, pas de comparaison Sylius Plus) et table « scénario → test ou preuve » | 0,25 h | Claude |
| T-219-01 | US-219 | DOC | Cadrage et table des invariants (règle 28) : fixtures existantes 12 mois, pages Plugins/Preuve existantes, tests touchés | 0,5 h | Claude |
| T-219-02 | US-219 | DB | Fixtures rythmes d'achat réalistes : 12 mois, rythmes distincts (gants 14j, lunettes 60j, visserie irrégulière), alerte garantie Claire, dates relatives « aujourd'hui », test vraisemblance (7-120j) | 2,5 h | Claude |
| T-219-05 | US-219 | OPS | Installation plugin dans démo : `composer require tmonier/sylius-b2b-predictive-analytics-plugin:^0.1`, recette Flex, config, recalcul planifié `messenger.yaml` | 1,5 h | Claude |
| T-219-06 | US-219 | DOC | Page « Plugins » et « Preuve technique » : ajout plugin prédictif, fiche (nom, description, lien GitHub, règles explicables), capture bloc alertes, lien profil auteur | 1 h | Claude |
| T-219-07 | US-219 | TEST | Tests et contrôles : alerte garantie Claire, au moins 2 alertes avec explications, dates relatives cohérentes après reset, aucune alerte factice si historique court, données réalistes (vraisemblance intervalles) | 1,5 h | Claude |
| T-219-08 | US-219 | DOC | Captures (page « Mon compte » Claire avec alertes, panier pré-rempli) et rendu réel vérifié FR/EN | 0,5 h | Claude |
| T-219-09 | US-219 | OPS | PR démo (branche du sprint) ; orchestrateur fusionne CI verte | 0,25 h | Claude |
| T-219-10 | US-219 | REV | Revue (aucun détail sensible, ton neutre, pas de comparaison Sylius Plus) et table « scénario → test ou preuve » | 0,25 h | Claude |
| T-238-01 | US-238 | DOC | Cadrage et table des invariants (règle 28) : thème créé S34 (PR #1 fusionnée), composants en-tête/pied/messages créés, boutons/tableaux/champs à compléter, aucun test de rendu | 0,5 h | Claude |
| T-238-02 | US-238 | TEST | Tests de rendu rouges, **rouge prouvé (règle 27)** : application test Sylius 2.2/2.3, thème installé et activé, page d'accueil rendue avec marqueur gabarit, composants communs rendus | 1 h | Claude |
| T-238-03 | US-238 | FE-WEB | Composants restants (boutons, tableaux, champs) et leurs tests : gabarits Twig avec jetons de design, hooks enregistrés, tests unitaires de contraste, lint Twig | 2 h | Claude |
| T-238-04 | US-238 | TEST | Tests et contrôles : projet vierge Sylius 2.3, thème installé et activé, page d'accueil rendue (200, marqueur), composants communs rendus avec jetons, thème inactif ne casse rien, gabarit invalide détecté lint Twig, contraste insuffisant détecté test unitaire | 1 h | Claude |
| T-238-05 | US-238 | REV | PHPStan max (dépôt entier), ECS, Rector, lints, rejoués avant push | 0,5 h | Claude |
| T-238-06 | US-238 | OPS | Push branche `feature/theme-render-test`, PR dépôt thème ; orchestrateur fusionne CI verte ; **aucun tag ni release** | 0,25 h | Claude |
| T-238-07 | US-238 | REV | Revue (aucun détail sensible, ton neutre, pas de comparaison Sylius Plus) et table « scénario → test ou preuve » | 0,25 h | Claude |
| T-TECH-35-10 | Transverse | REV | Tenue du registre et des GO, règles 26, 28, 30, 31 appliquées | 0 h | Orchestrateur |
| T-TECH-35-12 | Transverse | OPS | ECS, PHPStan max, Rector rejoués avant chaque push PR sprint ; CI verte avant merge | 0 h | Orchestrateur |
| T-TECH-35-15 | Transverse | DOC | Revue et rétrospective du sprint 35, puis clôture | 0,25 h | Orchestrateur |

## 🔄 En cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 📅 En attente (GO de Thibaut, disque ou déploiement)

| ID | US | Type | Tâche | Estimation | Réalisé par | Attend |
|----|-----|------|-------|------------|-------------|--------|
| T-219-03 | US-219 | OPS | 📅 **Demander le GO du tag v0.1.0 à Thibaut** quand le plugin est fusionné (registre du 2026-10-28) | 0 h | Claude | plugin fusionné |
| T-219-04 | US-219 | OPS | 📅 **Thibaut soumet le paquet à Packagist** (action calendaire) | 0,5 h | Thibaut | GO tag v0.1.0 |
| T-TECH-35-05 | Transverse | OPS | 📅 Disque et rendu : Thibaut libère disque (10 Go cibles, commande en chemins absolus avec `du -sh`) ; reliquats T-TECH-34-02, -03, -04 | 0,5 h | Thibaut + Orchestrateur | Thibaut |
| T-TECH-35-06 | Transverse | OPS | Disque et machine : relevés avant le premier lot (df, mémoire libre, swap), aucun test sous 5 Go, au plus deux workers en test | 0 h | Orchestrateur | disque libéré |
| T-TECH-35-13 | Transverse | TEST | 📅 Suite complète locale avant push PR sprint, ou attente explicite dite dans PR (règle, T-TECH-33-13) | 0,25 h | Orchestrateur | disque (6 Go) |

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-35-01 | Transverse | DOC | **Règle 31** de la consigne : vérifier les faits techniques avant présentation à Thibaut (fait au planning) | 0 h | Orchestrateur |
| T-TECH-35-02 | Transverse | DOC | **Règle 32** de la consigne : interdire de retirer ou d'affaiblir un test (fait au planning) | 0 h | Orchestrateur |
| T-TECH-35-03 | Transverse | DOC | **Règle 33** de la consigne : nouveau paquet commence par le test d'installation (fait au planning) | 0 h | Orchestrateur |
| T-TECH-35-04 | Transverse | OPS | Validation YAML de `sprint-status.yaml` : script `bin/check-sprint-status` testé vert et rouge (fait au planning) | 0 h | Orchestrateur |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 16 (US-217 5, US-218 5, US-219 3, US-238 3) |
| Tâches | 46 (US-217 11, US-218 10, US-219 10, US-238 7, transverses 8) |
| Heures | 37,5 h (37 h Claude, 0,5 h Thibaut) pour 35,6 h de capacité, écart de −1,9 h |
| Ordre de sacrifice (registre) | US-219 d'abord, puis US-238 ; US-217 et US-218 protégées |
| Rouge prouvé | à relever à la clôture : US-217, US-218, US-238, US-219 (cible : 4 stories sur 4) |
