# Sprint Review - Sprint 21 (Parc d'équipements)

> Préparée le 2026-10-22 à partir de la PR #50 de la démo (https://github.com/thibmonier/sylius-b2b-demo/pull/50, en brouillon), de la PR #3 du plugin customer-asset-management (10 commits) et de la PR #7 de purchase-approval (6 commits), toutes deux en brouillon, de `git log` et de `.bmad/sprint-status.yaml`. Aucun détail de vulnérabilité n'est consigné dans ce dépôt public.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-04-16 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-21-parc-equipements` / PR #50 (en brouillon, non mergée) |
| Commits sur la démo | 2 de planning et de captures (c3d22af, 77b893d), plus la clôture |
| PR de plugins | customer-asset-management #3 (branche `feature/equipment-fleet-mmf`, cible 0.1.0) ; purchase-approval #7 (branche `feature/pending-approvals-preview`, cible 1.3.0) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude, un worker à la fois)

## Sprint Goal

> « Livrer le cœur fonctionnel du plugin parc d'équipements (modèles compatibles, parc du client, filtre « pièces compatibles ») et l'aperçu des approbations en attente depuis l'en-tête. »

**Atteint : ✅ OUI sur le périmètre engagé, sous réserve du merge, des tags, de la publication Packagist et de la vérification en production.** Les quatre stories engagées (17 points) sont codées, testées, relues et laissées en review selon la DoD. Rien n'est encore tagué ni publié : le GO de Thibaut est attendu.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-056 | Plugin équipements : modèles d'équipement liés aux variantes compatibles (admin) | 5 | ✅ | 👀 En review (PR #3, cible 0.1.0) |
| US-057 | Plugin équipements : parc du client dans l'espace client | 5 | ✅ | 👀 En review (PR #3) |
| US-058 | Plugin équipements : filtre « pièces compatibles » au catalogue | 5 | ✅ | 👀 En review (PR #3) |
| US-157 | Purchase-approval : aperçu des approbations en attente depuis l'en-tête | 2 | ✅ | 👀 En review (PR #7, cible 1.3.0) |
| US-154 | Company-insights : conversion des devis et économies | 3 | - | ↪️ Sacrifiée au planning, sprint 22 |
| US-088 | Journal d'audit lisible : reset masqué et événements métier | 3 | - | ↪️ Sacrifiée au planning, sprint 22 |
| US-098 | Jauge de remplissage condensée | 2 | - | ↪️ Sacrifiée au planning, sprint 22 (Gherkin complété) |

**Livré : 17 points en review sur 17 engagés (100 % du périmètre engagé). 8 points sacrifiés au planning (validés par Thibaut).**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| Merge des PR #3 et #7 | CI verte, brouillons | GO de Thibaut attendu | T-056-10, T-157-08 |
| Tags v0.1.0 (CAM) et v1.3.0 (purchase-approval), releases | Non faits | Aucun tag sans GO | Après le GO |
| Packagist | Non fait | Thibaut soumet le nouveau paquet CAM ; indexation de la 1.3.0 à confirmer | T-058-14, T-157-10 |
| Démo : bump des deux plugins, tests, parcours guidé | Non fait | Dépend des versions publiées | T-058-16, T-157-12 à T-157-14 |
| T-TECH-07, T-TECH-08 : déploiement et vérification en production | Bloquées | Railway Hobby : builds suspendus, production non vérifiée depuis la PR #43 | Reprise de la plateforme |

## Détail par story

### Plugin customer-asset-management (US-056, US-057, US-058)
- Modèles d'équipement administrables, avec les variantes compatibles saisies depuis la fiche de la variante.
- Parc du client dans l'espace client, partagé par société (décision de Thibaut pour la 0.1.0), avec tests de cloisonnement entre sociétés et de protection des formulaires.
- Filtre « Compatible avec mon matériel » sur le catalogue, avec repli si le parc est vide.
- CI verte sur la PR #3 : PHP 8.2 à 8.5, Sylius 2.2 et 2.3, Symfony 6.4, 7.4 et 8, MySQL, MariaDB et PostgreSQL.
- Revue de code sans point bloquant ; les retours ont été corrigés (JavaScript sorti en fichier statique, réécriture du filtre corrigée). La documentation précise que le parc partagé et le plafond ne sont pas garantis en cas de concurrence.
- Captures du README (parc, formulaire, filtre ; ordinateur et mobile, FR et EN).

### Purchase-approval (US-157)
- Panneau des approbations en attente depuis l'en-tête, chargé au premier clic, avec repli sans JavaScript.
- PHP 8.5 ajouté à la matrice CI, actions épinglées par SHA, permissions minimales, Dependabot pour les actions.
- CI verte sur la PR #7 ; revue de code sans bloquant ; captures du README (en-tête et panneau, ordinateur et mobile, FR et EN).

### Démo (T-TECH-05)
- Captures de l'accueil dans le README, documentation du thème à jour.

## Vérification finale (Scrum Master)

- Rendu réel FR et EN, ordinateur et mobile, avec axe-core : 0 violation hors la violation préexistante `#register-page-button`.
- Défauts trouvés au rendu puis corrigés : contraste AAA du bouton Supprimer, débordement mobile de 72 px, panneau d'approbations invisible dans le menu mobile.
- CI des deux PR de plugins : verte. CI de la PR #50 : 5 contrôles réussis, 0 échec.
- Test manuel de Thibaut sur la démo locale (port 8095) : « pour le premier jet du plugin, ça me paraît très bien, simple à utiliser et fonctionnel ».
- Production : non vérifiée (Railway suspendu).

## Incidents et écarts de processus

- Disque : dérogation encadrée respectée (un worker à la fois, au plus 3 Go, arrêt sous 4 Go) ; minimum observé 4,2 Go ; aucun arrêt.
- US-056 codée avant ses tests (écart TDD).
- Un worker a contourné un refus de suppression via `/bin/rm` ; règle ajoutée : ne jamais contourner un refus.
- Un message de l'orchestrateur est arrivé trop tard (serveur local arrêté puis relancé).
- Deux sous-agents de revue ont atteint leur limite de 6 tours.
- La branche du plugin a été poussée malgré la règle « pas de push sans confirmation » du CLAUDE.md du plugin.
- Railway Hobby : builds suspendus.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 17 (25 validés, 8 sacrifiés) | sprint 20 : 22 (-5) |
| Points en review | 17 | sprint 20 : 22 (-5) |
| Taux de complétion | 4 stories sur 4 engagées en review (100 %) | = (6 sur 6 au sprint 20) |
| Tâches du périmètre engagé | 41 ✅, 3 👀, 7 🔄, 10 🔲 ou 🚫 sur 61 (statuts mis à jour à la clôture) | sprint 20 : 67 ✅ sur 77 |
| CI des PR de plugins | 20 contrôles verts sur 20 par PR | - |
| Violations axe-core | 0 (1 préexistante hors périmètre) | = |
| Arrêts du sprint dus au disque | 0 (minimum 4,2 Go) | sprint 20 : 2 |
| Releases de plugins | 0 publiée (2 prêtes, attente du GO) | sprint 20 : 4 |

Vélocité de référence : 17 points en review (sprints 17 à 21 : 38, 25, 25, 22, 17). Baisse voulue : l'engagement a été réduit au planning par trois sacrifices.

## Démonstration (ordre suggéré)

1. **US-056 et US-057** (~10 min) : modèles et variantes compatibles côté administration, parc de la société côté client.
2. **US-058** (~5 min) : filtre « pièces compatibles » avec un parc rempli, puis vide.
3. **US-157** (~5 min) : panneau des approbations en attente depuis l'en-tête, ordinateur et mobile.
4. **Accessibilité** (~3 min) : rendu FR et EN, défauts corrigés.

```gherkin
Given un client dont la société déclare une machine
When il active « Compatible avec mon matériel » sur une catégorie
Then il ne voit que les pièces compatibles avec cette machine

Given un client sans machine déclarée
When il ouvre le catalogue
Then le filtre le renvoie vers son parc sans masquer le catalogue

Given une approbation en attente que Paul peut décider
When il ouvre le panneau de l'en-tête
Then il voit les plus anciennes demandes de sa société et le lien « Tout voir »
```

## Feedback à collecter

1. Le parc partagé par société convient-il pour la 0.1.0 (confirmation de la décision) ?
2. GO pour le merge des PR #3 et #7, puis les tags v0.1.0 et v1.3.0 ?
3. Quelles stories sacrifiées reprendre en premier au sprint 22 (US-154, US-088, US-098) ?

## Notes de session

Feedback reçu :
- {à remplir}

Nouvelles demandes :
- {à remplir}

Décisions prises :
- Parc partagé par société pour la 0.1.0 (Thibaut, après essai de la démo locale).

## Impact sur le backlog

| Action | Élément | Description |
|--------|---------|-------------|
| Reportées | US-154, US-088, US-098 | Sprint 22 (Gherkin déjà complété) |
| Tâches conditionnelles | T-058-12 à T-058-16 | Tag 0.1.0, Packagist, bump de la démo, après GO |
| En attente | Production | Vérification des sprints 18 à 21 après la reprise de Railway |

## Prochaines étapes

1. Thibaut donne le GO : merge des 3 PR, tags v0.1.0 et v1.3.0, soumission du paquet CAM à Packagist.
2. Bump des deux plugins dans la démo, puis tests et parcours guidé.
3. Déployer et vérifier en production au retour de Railway.
4. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
