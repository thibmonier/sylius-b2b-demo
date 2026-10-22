# Sprint Review - Sprint 20 (Démo enrichie)

> Préparée le 2026-10-22 à partir de la PR #49 (https://github.com/thibmonier/sylius-b2b-demo/pull/49, en brouillon), de `git log origin/main..HEAD` (13 commits), de `.bmad/sprint-status.yaml`, des fichiers de tâches du sprint et des constats de fin de sprint. Aucun détail de vulnérabilité n'est consigné dans ce dépôt public.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-04-02 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-20-demo-enrichie` / PR #49 (en brouillon, non mergée) |
| Commits du sprint | 13 sur la branche de la démo (d57fdb5, 4b13185, 6809667, d36a717, d7d93b7, d5840a6, 74e3724, 45732cd, 05341da, 2c2c2d1, 17744fd, fb056c0, 8dcfb9b) |
| Releases de plugins | company-insights v0.1.1, v0.2.0, v0.2.1 ; purchase-approval v1.2.0 (toutes publiées le 2026-10-22) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude, un worker à la fois)

## Sprint Goal

> « Rendre la démo plus riche et plus lisible pour un recruteur (accueil réorganisé, page Intégrations, simulateur PunchOut expliqué) et compléter le pilotage des achats (ventilation par site, approbations et encours, approbations en attente dans l'en-tête). »

**Atteint : ✅ OUI sur le périmètre engagé, sous réserve de la vérification en production et de la validation des écarts par Thibaut.** Les six stories engagées (22 points) sont livrées et laissées en review selon la DoD. Le catalogue de 150 références (US-093, 8 points) avait été retiré de l'objectif et sacrifié au planning (capacité), il passe au sprint 21.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-113 | Accueil réorganisé : trois colonnes, pourquoi cette démo, qui l'a conçue | 3 | ✅ | 👀 En review (écart à valider, T-113-06 non faite) |
| US-152 | Company-insights : ventilation par site et centre de coût, export CSV | 5 | ✅ | 👀 En review (company-insights v0.2.0) |
| US-153 | Company-insights : indicateurs des approbations et de l'encours | 5 | ✅ | 👀 En review (company-insights v0.2.0, v0.2.1) |
| US-156 | Purchase-approval : approbations en attente signalées dans l'en-tête | 3 | ✅ | 👀 En review (purchase-approval v1.2.0, captures README T-156-06 non faites) |
| US-108 | Page « Intégrations » | 3 | ✅ | 👀 En review (écart à valider) |
| US-099 | Simulateur PunchOut habillé et expliqué (report du sprint 19) | 3 | ✅ | 👀 En review (5e scénario côté OCI) |
| US-093 | Catalogue crédible, tranche 2 (environ 150 références) | 8 | - | ↪️ Sacrifiée au planning, reportée au sprint 21 |

**Livré : 22 points en review (6 stories) sur 22 engagés, soit 100 % du périmètre engagé. 8 points sacrifiés au planning (US-093). US-121 reste hors sprint (bloquée par T-103-03).**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| T-113-06 : captures d'accueil du README | Non faite | Fin de sprint, disque contraint | Reprise au sprint 21 |
| T-156-06 : captures de l'en-tête dans le README de purchase-approval | Non faite (v1.2.0 publiée sans captures) | Fin de sprint | Reprise au sprint 21 (patch de documentation) |
| T-TECH-02 : budget d'espace disque | Partielle | Le disque est descendu à 2 Go puis 4,7 Go malgré le suivi | Action 1 de la rétro |
| T-TECH-04 : gabarit de l'orchestrateur (gate recoupé, refus d'outil) | Non faite | Reportée | Action 2 de la rétro |
| T-TECH-08 : gabarit de CI d'un nouveau plugin | Partielle | company-insights est à jour (PHP 8.5, actions épinglées) ; matrice de purchase-approval sans PHP 8.5 | Action 3 de la rétro |
| T-TECH-05, T-TECH-06 : déploiement groupé et vérification en production | Bloquées | Railway toujours suspendu (plans Hobby) : la production n'a reçu aucune des PR #43 à #49 | Après la reprise de la plateforme ; c'est pourquoi les six stories restent en review |
| T-TECH-09, 10, 11 : garde-fous de l'orchestrateur | Non consignées comme faites | Statut non mis à jour pendant le sprint | À confirmer par l'orchestrateur à la clôture |

## Écarts par rapport aux fiches (à valider par Thibaut)

| Story | Écart | Décision demandée |
|-------|-------|-------------------|
| US-113 | Sous-titre « Offres négociées » ; trois colonnes pour le visiteur non connecté ; le bouton du hero ouvre le choix du rôle | Valider ou corriger |
| US-108 | Section dans `/integrer` au lieu d'une nouvelle page ; bouton de contact retiré ; schéma SVG sans état | Valider ou demander la page dédiée |
| US-099 | 5e scénario (URL de retour hors domaines autorisés) implémenté côté OCI | Valider |
| US-113 / T-113-06 | Captures d'accueil du README non faites | Reporter au sprint 21 ou retirer |

## Détail par story

### Plugin company-insights (US-152, US-153)
- v0.1.1 : nom du produit affiché une seule fois, PHP 8.5 en CI, actions épinglées par SHA.
- v0.2.0 : ventilation par site et dimension, filtres, export CSV protégé, widgets « Approbations » et « Encours ». La revue de code a relevé 5 points majeurs, tous corrigés avant le tag : agrégats SQL sans plafond, requêtes N+1 supprimées, montants hors taxes, période contrôlée à la validation de commande, fusion des sources de référence de bon de commande.
- v0.2.1 : tableaux défilants accessibles au clavier, filtres à la ligne, captures dans le README.
- Démo : axe « centre de coût », référence de bon de commande, trois sites, approbations datées à leur commande.

### Approbations en attente dans l'en-tête (US-156)
- purchase-approval v1.2.0 (matrice CI sans PHP 8.5 : écart T-TECH-08). Dans la démo, l'administratrice décide aussi (`approver_roles` = `[approver, admin]`).

### Accueil, Intégrations, simulateur (US-113, US-108, US-099)
- Accueil en trois colonnes, sections « Qui a conçu cette démo » et « Comment elle a été faite ».
- Intégrations avec leur état sur la page du guide.
- Simulateur PunchOut habillé en portail d'achat fictif, étapes numérotées.

## Vérification finale (Scrum Master)

- Suite complète : 1 786 tests, 0 échec. PHPStan et ECS verts.
- axe-core : 0 violation sur les pages touchées. Une violation préexistante (`aria-required-parent`, en-tête du thème de base) est hors périmètre. pa11y n'est pas installé : axe-core a été utilisé à la place.
- Captures dans le scratchpad (`w1/shots` et `w2/shots`).
- CI de la PR #49 : à relever avant le merge (PR en brouillon).
- Production : non vérifiée (Railway suspendu).

## Incidents du sprint

- Disque descendu à 2 Go puis 4,7 Go (vendor, caches et images Docker des workers de plugins, swap macOS) : deux arrêts du sprint, nettoyage par Thibaut.
- Deux suppressions de cache refusées par l'outil.
- Cache de production local périmé (erreur 500) et migration manquante sur la base locale.
- Serveur local de démonstration lancé pour Thibaut (le mode démo est requis pour `/demo/accounts`).
- Railway toujours suspendu (plans Hobby) : aucune vérification en production.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 22 | sprint 19 : 25 (-3) |
| Points en review | 22 | sprint 19 : 25 (-3) |
| Taux de complétion | 6 stories sur 6 engagées en review (100 %) | = (5 sur 5 au sprint 19) |
| Tests | 1 786, 0 échec | sprint 19 : 1 669 (+117) |
| Commits | 13 sur la démo | sprint 19 : 10 |
| Releases de plugins | 4 (company-insights x3, purchase-approval x1) | sprint 19 : 3 |
| Tâches du périmètre engagé | 67 ✅, 1 🚫, 9 🔲 sur 77 | sprint 19 : 53 ✅, 4 👀, 7 🔲 sur 64 |
| Sacrifices appliqués | 1 story (8 points), au planning | sprint 19 : 2 |
| Violations axe-core sur les pages touchées | 0 (1 préexistante hors périmètre) | pa11y non mesuré |
| Arrêts du sprint dus au disque | 2 | sprint 19 : 0 |

Vélocité de référence : 22 points en review (sprints 17 à 20 : 38, 25, 25, 22 ; le sprint 18 comptait 36 engagés).

## Démonstration (ordre suggéré)

1. **US-113 et US-108** (~10 min) : accueil en trois colonnes pour un visiteur non connecté, choix du rôle, section Intégrations avec états.
2. **US-099** (~7 min) : simulateur PunchOut habillé, quatre étapes, cas OCI et URL de retour refusée.
3. **US-152 et US-153** (~12 min) : tableau de bord d'Acme avec ventilation par site et centre de coût, export CSV, widgets Approbations et Encours.
4. **US-156** (~5 min) : pastille d'approbations en attente dans l'en-tête (Paul, Isabelle).
5. **Plugins** (~3 min) : company-insights 0.2.1 et purchase-approval 1.2.0 sur Packagist.

```gherkin
Given la démo remise à zéro
When un visiteur non connecté ouvre l'accueil
Then il voit trois colonnes (Acheter, Valider, S'intégrer) et le choix du rôle

Given un acheteur d'Acme sur son tableau de bord
When il filtre par site et exporte en CSV
Then il ne voit que les données de son entreprise et le fichier respecte les filtres

Given une approbation en attente que Paul peut décider
When il ouvre une page de la boutique
Then l'en-tête affiche l'entrée « Approbations » avec le nombre en attente
```

## Feedback à collecter

1. L'accueil en trois colonnes convient-il (sous-titre « Offres négociées », bouton du hero = choix du rôle) ?
2. La section Intégrations dans `/integrer` suffit-elle, sans page dédiée ni bouton de contact ?
3. Le 5e scénario du simulateur (URL de retour refusée, côté OCI) est-il validé ?
4. US-093 (catalogue de 150 références) au sprint 21, qui monte à 41 points : que sacrifier ou reporter ?

## Notes de session

Feedback reçu :
- {à remplir}

Nouvelles demandes :
- {à remplir}

Décisions prises :
- 2026-10-22 : sacrifice d'US-093 (capacité), reportée au sprint 21.

## Impact sur le backlog

| Action | Élément | Description |
|--------|---------|-------------|
| Reportée | US-093 | Sprint 21, qui monte à 41 points : à arbitrer |
| Hors sprint | US-121 | Toujours bloquée par T-103-03 |
| Nouvelle tâche | T-113-06 | Captures d'accueil du README (reprise) |
| Nouvelle tâche | purchase-approval | Captures du README et cellule PHP 8.5 de la matrice CI |
| En attente | Production | Vérification des stories des sprints 18 à 20 après la reprise de Railway |

## Prochaines étapes

1. Thibaut valide les écarts d'US-113, US-108 et US-099.
2. Relever la CI de la PR #49, la sortir du brouillon et la merger quand elle est verte.
3. Déployer sur Railway dès la reprise (PR #43 à #49), puis vérifier en production.
4. Arbitrer le sprint 21 (41 points).
5. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
