# Task Board : Sprint 034 (spikes et thème)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut, du disque ou d'un déploiement | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

Total décomposé : **43 tâches, 33 h** (14 points). État au 2026-10-28 : **6 ✅, 28 🔲, 9 📅**. Les 📅 attendent le GO de l'ADR d'US-221 (T-221-03 avant US-222), le retour du disque au-dessus de 5 Go (tests, suite complète).

Règle : une tâche passe en ✅ dans le commit qui la livre. **Rouge prouvé (règle 27)** : une story de code n'est comptée « rouge prouvé » que si le commit de ses tests rouges cite l'échec observé de chaque test.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-210-04 | US-210 | REV | Revue (aucun détail sensible, ton neutre), relecture Thibaut, estimations US-211 à US-216 confirmées | 0,25 h | Claude |
| T-229-04 | US-229 | REV | Revue (formulation prudente validée, ton neutre), relecture Thibaut, estimations US-230 à US-235 confirmées | 0,25 h | Claude |
| T-TECH-34-02 | Transverse | OPS | Workflows YAML validés par analyseur, vérification job exécuté, testé sur workflow invalide | 0,25 h | Orchestrateur |
| T-TECH-34-04 | Transverse | OPS | Outils de rendu et de build (T-TECH-33-05, -06, -08 : rendu sans barre, script mesure, avertissement build périmé) | 0,25 h | Orchestrateur |
| T-TECH-34-06 | Transverse | OPS | Disque et machine : relevés avant le premier lot, aucun test sous 5 Go, au plus deux workers en test | 0 h | Orchestrateur |
| T-TECH-34-10 | Transverse | REV | Tenue du registre et des GO, règles 26, 28, 30 appliquées | 0 h | Orchestrateur |
| T-TECH-34-12 | Transverse | OPS | ECS, PHPStan max, Rector rejoués avant chaque push ; CI verte avant merge | 0 h | Orchestrateur |
| T-TECH-34-15 | Transverse | DOC | Revue et rétrospective du sprint 34, puis clôture | 0 h | Orchestrateur |
| T-222-12 | US-222 | DOC | Mise à jour de `US-222.md` (statut `done`) | 0 h | Claude |
| T-222-13 | US-222 | OPS | Préparation commande soumission Packagist pour Thibaut | 0 h | Claude |
| T-222-14 | US-222 | REV | Revue (aucun détail sensible, ton neutre, pas de comparaison Sylius Plus) et table « scénario → test ou preuve » | 0 h | Claude |

## 🔄 En cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 📅 En attente (GO de Thibaut, disque ou déploiement)

| ID | US | Type | Tâche | Estimation | Réalisé par | Attend |
|----|-----|------|-------|------------|-------------|--------|
| T-221-03 | US-221 | DOC | 📅 ADR `docs/adr/0019-packaging-theme-sylius2.md`, **GO Thibaut avant US-222** | 0,25 h | Claude | relecture Thibaut |
| T-221-04 | US-221 | REV | 📅 Revue (ton neutre), relecture Thibaut, estimations US-222 à US-228 confirmées | 0,25 h | Claude | relecture Thibaut |
| T-222-01 | US-222 | DOC | 📅 Cadrage et table des invariants (règle 28) : thème démo, maquettes, tests existants touchés | 1 h | Claude | GO ADR US-221 |
| T-222-02 | US-222 | TEST | 📅 Tests rouges, **rouge prouvé (règle 27)** : paquet installable, jetons, composants, contrastes AAA, Sylius 2.2/2.3 | 1,5 h | Claude | GO ADR US-221 |
| T-222-03 | US-222 | BE | 📅 Extraction thème vers paquet : structure selon ADR US-221, `composer.json`, dépendances | 1,5 h | Claude | GO ADR US-221 |
| T-222-04 | US-222 | FE-WEB | 📅 Jetons de design (couleurs, typographie, espacements, contrastes AAA) : variables CSS/Sass, surchargeables | 1,5 h | Claude | GO ADR US-221 |
| T-222-05 | US-222 | FE-WEB | 📅 Composants communs (en-tête, pied, boutons, tableaux, champs, messages) et gabarit de base : Twig, hooks | 2 h | Claude | GO ADR US-221 |
| T-222-06 | US-222 | OPS | 📅 Recette Flex : fichiers copiés, configuration, ordre de priorité surcharges | 1 h | Claude | GO ADR US-221 |
| T-222-07 | US-222 | OPS | 📅 CI Sylius 2.2/2.3 : matrice, rendu smoke test, workflows durcis (SHA, `permissions`, zizmor, audit, Dependabot) | 1,5 h | Claude | GO ADR US-221 |
| T-222-08 | US-222 | TEST | 📅 Tests et contrôles : projet vierge Sylius 2.3, installation paquet, page affiche style ; surcharge en conflit ; contrastes ; ordre de priorité | 1 h | Claude | GO ADR US-221 |
| T-222-09 | US-222 | DOC | 📅 README français : installation, usage, lien démo, profil auteur, ton neutre | 0,5 h | Claude | GO ADR US-221 |
| T-222-10 | US-222 | REV | 📅 PHPStan max (dépôt entier), ECS, Rector, lints, rejoués avant push | 0,25 h | Claude | GO ADR US-221 |
| T-222-11 | US-222 | OPS | 📅 Push branche `feature/theme-tranche-1`, PR ; orchestrateur fusionne CI verte ; **aucun tag ni release** | 0,25 h | Claude | GO ADR US-221 |
| T-TECH-34-03 | Transverse | OPS | 📅 Thibaut libère disque (10 Go cibles), `bin/check-disk` annonce runs, `bin/run-tests` file d'attente (T-TECH-33-03, -04) | 0,25 h | Thibaut + Orchestrateur | Thibaut |
| T-TECH-34-05 | Transverse | OPS | 📅 Contrôles production US-207 **faits le 2026-10-28 (PR #131)** ; reste : reliquats T-TECH-33-07, -09 si disque | 0 h | Claude + Thibaut | déploiement |
| T-TECH-34-13 | Transverse | TEST | 📅 Suite complète locale avant push PR sprint, ou attente explicite dite dans PR (règle, T-TECH-33-13) | 0,25 h | Orchestrateur | disque (6 Go) |

## 🟡 Faites avec écart

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-34-01 | Transverse | DOC | **Règle 30** de la consigne : GO transmis avant consignation, GO orchestrateur ne se délègue qu'explicitement (fait au planning) | 0 h | Orchestrateur |
| T-221-01 | US-221 | DOC | Cadrage : timebox 6 h, projet vierge Sylius 2.3, maquettes G2StRQigRKD6SqNRoyHpkW | 0,5 h | Claude |
| T-221-02 | US-221 | OPS | Recherche packaging thème Sylius 2, Twig hooks, assets, Flex, Sylius 2.2/2.3, Sylius Plus ; timebox 6 h | 5,5 h | Claude |
| T-210-01 | US-210 | DOC | Cadrage : timebox 6 h, accès documentation b2b-mcp, Sylius Plus | 0,5 h | Claude |
| T-210-02 | US-210 | DOC | Recherche format rejeu, adaptateur LLM, outils exposés, Sylius Plus ; timebox 6 h | 5,5 h | Claude |
| T-210-03 | US-210 | DOC | ADR `docs/adr/0018-copilote-format-rejeu-adaptateur.md`, note privée hors dépôt | 0,25 h | Claude |
| T-229-01 | US-229 | DOC | Cadrage : timebox 6 h, accès règlement (UE) 2024/1781, calendriers, Sylius Plus | 0,5 h | Claude |
| T-229-02 | US-229 | DOC | Recherche règlement ESPR, calendrier batteries 2027, actes délégués, GS1, Sylius Plus ; timebox 6 h | 4,5 h | Claude |
| T-229-03 | US-229 | DOC | ADR `docs/adr/0020-passeport-numerique-produit-espr.md`, formulation prudente, ligne `docs/adr/index.md` | 0,5 h | Claude |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 14 (US-210 3, US-221 3, US-229 3, US-222 5) |
| Tâches | 43 (US-210 4, US-221 4, US-229 4, US-222 14, transverses 17) |
| Heures | 33 h (32,5 h Claude, 0,5 h Thibaut) pour 31,4 h de capacité, écart de −1,6 h |
| Ordre de sacrifice (registre) | US-222 d'abord, puis US-229 ; US-210 et US-221 protégées |
| Rouge prouvé | à relever à la clôture : US-222 (cible : 1 story sur 1, les spikes n'ont pas de code) |
