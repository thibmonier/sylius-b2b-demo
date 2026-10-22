# Sprint Review - Sprint 19 (Pilotage des achats)

> Préparée le 2026-10-22 à partir de la PR #47 (https://github.com/thibmonier/sylius-b2b-demo/pull/47, avant merge), du journal git de la branche, de `.bmad/sprint-status.yaml` et des tâches du sprint. Aucun détail de vulnérabilité n'est consigné dans ce dépôt public.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-03-19 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-19-pilotage-achats` / PR #47 (ouverte, CI verte, merge à venir) |
| Commits du sprint | 10 sur la branche de la démo (764e817, 339d451, 3d26d44, 8c3c3b8, d3a4b49, a01595c, 3e5ffc0, 8593f30, 3d985d0, 18cdbdd) |
| Releases de plugins | company-insights v0.1.0 (nouveau dépôt public, Packagist le 2026-10-22), admin-order-creation v1.1.0, company-accounts v1.3.0 |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude, un worker à la fois)

## Sprint Goal

> « Livrer en démo le tableau de bord des achats d'EPIC-15 (plugin company-insights 0.1.0), compléter le kit média et rendre le simulateur PunchOut lisible »

**Atteint : ⚠️ PARTIELLEMENT.** Le cœur du but est livré : le plugin company-insights 0.1.0 est publié et intégré à la démo, avec 18 mois d'historique d'achats. Le kit média (US-121) et le simulateur PunchOut (US-099) ne font pas partie du périmètre : ils ont été sacrifiés au planning, la décomposition (89,25 h) dépassant la capacité (75,8 h).

Justification : les 5 stories engagées (25 points) sont livrées. Les 2 stories sacrifiées (8 points) sont reportées au sprint 20 selon l'ordre de sacrifice annoncé, sans surprise en cours de sprint.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-149 | Company-insights : squelette du plugin et indicateurs de la période | 5 | ✅ | 👀 En review |
| US-150 | Company-insights : achats par mois sur 12 mois, comparés à l'année précédente | 5 | ✅ | 👀 En review |
| US-151 | Company-insights : tops acheteurs, produits et catégories | 5 | ✅ | 👀 En review (plugin v0.1.0 publié sur Packagist) |
| US-155 | Company-insights : historique d'achats et intégration à la démo | 5 | ✅ | 👀 En review (captures T-155-12/13 et relecture T-155-14 restantes) |
| US-146 | Création de commande admin : adresse de l'entreprise ou du carnet du client | 5 | ✅ | 👀 En review |
| US-121 | Kit média (2/2) | 5 | - | ↪️ Sacrifiée, reportée au sprint 20 |
| US-099 | Simulateur PunchOut habillé et expliqué | 3 | - | ↪️ Sacrifiée, reportée au sprint 20 |

**Livré : 25 points en review (5 stories) sur 25 engagés, soit 100 % du périmètre engagé. 8 points sacrifiés au planning.**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| T-155-12 : captures de la page Plugins et du tableau de bord | Non faites | Disque chroniquement sous 7 Go, démo locale impossible à remettre à zéro | À faire avant le merge, dès que l'espace le permet |
| T-155-13 : captures dans le README du plugin | Documentation écrite, captures absentes | Dépend de T-155-12 | Même échéance |
| T-155-14 : relecture de la documentation technique de référence | Non faite | Action de Thibaut, non bloquante | Thibaut relit s'il a accès et confirme ou corrige la classe du plugin |
| US-121, US-099 | Non démarrées | Sacrifiées au planning (capacité) | Sprint 20 ; T-103-03 bloque toujours US-121 |
| Cellule PHP 8.5 de la matrice CI de company-insights | Absente | Mineur de la revue, reporté | Ajout au sprint 20 |
| Actions CI non épinglées par SHA, sans bloc `permissions` | À faire | Mineur de la revue, reporté | Durcissement du workflow au sprint 20 |
| Déploiement en production | Bloqué | Builds Railway suspendus : la production n'a pas encore reçu le sprint 18, #45 et #46 | Reprise dès la levée de l'incident |

## Détail par story

### Plugin company-insights (US-149, US-150, US-151)
- Nouveau plugin `tmonier/sylius-company-insights-plugin` en v0.1.0 : dépôt public créé, publié sur Packagist le 2026-10-22 par Thibaut.
- Indicateurs de la période, achats par mois sur 12 mois comparés à l'année précédente, tops acheteurs, produits et catégories, avec cloisonnement par entreprise.

### Historique et intégration à la démo (US-155)
- L'historique de 18 mois est écrit en masse dans le plan de commandes ; un membre parti est désactivé pour illustrer le cas.
- Le plugin est intégré à la démo avec un scénario du tableau de bord.
- Coût mesuré : la remise à zéro passe de 6,09 s à 6,50 s.

### Adresse en commande admin (US-146)
- admin-order-creation v1.1.0 et company-accounts v1.3.0 : choix d'une adresse de l'entreprise ou du carnet du client dans la commande admin.
- Un scénario et 4 tests côté démo (8593f30).

## Vérification finale (Scrum Master)

- CI de la PR #47 : verte.
- Suite complète : 1 669 tests verts avant le dernier correctif. Le correctif (18cdbdd) relit la date du reset en base dans les tests d'historique, au lieu de l'horloge réelle ; la suite complète n'a pas été rejouée en local (disque sous 5 Go), la CI fait foi.
- Revue de sécurité : aucun point bloquant. Un test dépendant de l'horloge réelle a été corrigé ; deux mineurs sont reportés (matrice CI, durcissement du workflow).
- Rendu réel, pa11y et captures : non rejoués à la clôture (disque).

## Incidents du sprint

- Gate initial calculé à 23 points au lieu de 25 : erreur de calcul corrigée avant le démarrage (score final 90/100).
- Un worker mort sur une erreur réseau, repris sans perte de travail.
- Arrêt erroné du sprint par l'orchestrateur après un appel d'outil refusé ; Thibaut : « Je n'ai pas demandé d'arrêt ». Le sprint a été repris.
- CI de company-accounts rouge tant qu'admin-order-creation 1.1.0 n'était pas publié : ordre de publication à respecter.
- Conflit `composer.lock` au rebase après US-147 (PHP 8.5).
- Disque chroniquement sous 7 Go : captures T-155-12/13 non faites, suite locale impossible sous 5 Go.
- Incident Railway : builds suspendus, la production n'a pas reçu le sprint 18, #45 et #46.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 25 | sprint 18 : 36 (-11) |
| Points en review | 25 | sprint 18 : 25 |
| Taux de complétion | 5 stories sur 5 engagées en review | ↗ (7 sur 10 au sprint 18) |
| Tests | 1 669 tests verts avant le dernier correctif | sprint 18 : 1 633 (+36) |
| Commits | 10 sur la démo | sprint 18 : 7 |
| Releases de plugins | 3 (1 nouveau plugin, 2 mineures) | sprint 18 : 5 |
| Durée de remise à zéro de la démo | 6,50 s | 6,09 s avant US-155 (+0,41 s) |
| Sacrifices appliqués | 2 stories (8 points), au planning | sprint 18 : 0 |
| Erreurs pa11y AAA | Non mesuré à la clôture | - |

## Démonstration (ordre suggéré)

1. **US-155 et US-149** (~10 min) : tableau de bord de l'entreprise avec 18 mois d'historique, indicateurs de la période.
2. **US-150 et US-151** (~10 min) : courbe mensuelle comparée à l'année précédente, tops acheteurs, produits et catégories.
3. **US-146** (~7 min) : commande admin avec choix de l'adresse de l'entreprise ou du carnet.
4. **Plugin** (~3 min) : dépôt public et page Packagist de company-insights.

```gherkin
Given la démo remise à zéro avec l'historique de 18 mois
When un acheteur ouvre le tableau de bord de son entreprise
Then il voit les achats par mois comparés à l'année précédente, sans donnée d'une autre entreprise

Given une commande créée depuis l'administration pour un client d'une entreprise
When je choisis l'adresse
Then je peux sélectionner une adresse de l'entreprise ou du carnet du client
```

## Feedback à collecter

1. Le tableau de bord est-il lisible par un recruteur non technique ?
2. L'historique de 18 mois est-il crédible pour la démonstration ?
3. Le choix d'adresse en commande admin couvre-t-il le besoin initial ?
4. US-121 et US-099 : à garder au sprint 20 ou à reprioriser ?

## Notes de session

Feedback reçu :
- {à remplir}

Nouvelles demandes :
- {à remplir}

Décisions prises :
- 2026-10-21 : sacrifice d'US-121 et d'US-099, reportées au sprint 20 (capacité).

## Impact sur le backlog

| Action | Élément | Description |
|--------|---------|-------------|
| Reportée | US-121 | Sprint 20, toujours dépendante de T-103-03 |
| Reportée | US-099 | Sprint 20 ; ajouter l'erreur manquante aux scénarios Gherkin |
| Nouvelle tâche | company-insights | Cellule PHP 8.5 dans la matrice CI |
| Nouvelle tâche | company-insights | Actions épinglées par SHA et bloc `permissions` |
| En attente | T-155-12/13/14 | Captures puis relecture de Thibaut, avant le merge |

## Prochaines étapes

1. Faire les captures T-155-12/13 dès que le disque le permet.
2. Thibaut relit la documentation de référence (T-155-14), non bloquant.
3. Merger la PR #47 après attente du run de CI (branche `main` protégée).
4. Déployer sur Railway dès la reprise des builds, avec le sprint 18, #45 et #46.
5. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
