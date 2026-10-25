# Sprint Review - Sprint 30 (Compte, tableau de bord et TVA)

> Préparée le 2026-10-25, le jour du sprint. Les vérifications de production, qui ne peuvent avoir lieu qu'après la remise à zéro de 03:00 du 2026-10-26 (la TVA des fixtures n'apparaît qu'à ce moment), sont indiquées telles quelles dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 30, `sprint-30-compte-tableau-de-bord-et-tva` |
| Période | 2026-10-25 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-21) |
| Points engagés | **13** (US-199 5, US-201 5, US-202 3) |
| Points en done | **13** (code livré et fusionné ; vérifications de production après la remise à zéro du 2026-10-26) |
| PR de la démo | #115 (planning), #116 (sprint, `29bae07`) |
| Autres dépôts | `sylius-purchase-approval-plugin` : PR #9 (réglage `amount_basis`) et #10 (`chore(release): 1.4.0`), tag `v1.4.0`, release GitHub, Packagist à jour ; aucune promotion |
| Maquettes | Canevas Claude Design unique « Fournitures Pro — maquettes de la démo » : nouvelles pages « Mon compte (US-199) » et « Tableau de bord des achats (US-201) » |
| Mode | Orchestrateur (Claude) et workers : w202 (US-202), w199 (US-199), w201 (US-201), plus un fork pour les tâches transverses de rendu et de preuve, sur la branche partagée `feature/sprint-30-dev` |

## Sprint Goal

> « Donner à « Mon compte » et au tableau de bord des achats la clarté de la home, avec des maquettes validées avant le code, et afficher une TVA juste au panier et sur les commandes. »

**Atteint, à confirmer en production.** « Mon compte » et le tableau de bord suivent les maquettes validées par Thibaut avant tout gabarit Twig. Le panier et les commandes affichent HT, TVA 20 % et TTC ; la limite d'approbation reste comparée au HT grâce au plugin d'approbation 1.4.0. La TVA des fixtures n'apparaîtra en production qu'après la remise à zéro de 03:00 du 2026-10-26.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-199 Mon compte | 5 | Accueil par rôle (acheteur, approbateur, administrateur, compte sans commande) : « À faire » tiré des vraies données (commande en attente d'approbation, devis, approbation à examiner, échéance), raccourcis, cinq dernières commandes avec l'état en une phrase et « Commander à nouveau », cartes entreprise, paiement à terme, listes et devis ; menu en quatre blocs selon les droits ; B2C inchangé | Tests rouges seuls `92361fd`, `c8e359c` ; 359 tests verts (dont `AccountDashboardTest`) ; pa11y WCAG2AAA sans problème pour 4 rôles en FR et EN ; captures `docs/images/account-*` relues à 1280 et 360 px |
| US-201 Tableau de bord des achats | 5 | Surcharge dans la démo (plugin non modifié) : « À traiter » en tête, période et filtres repliables, 4 indicateurs avec évolution en pastille, graphique N / N-1 propre à la démo avec tableau de référence, « Qui achète » et « Ce qui est acheté » en barres, produits, sites et centres de coût, approbations et encours, état vide réel | Tests rouges seuls `e31c833` ; 157 tests verts ; pa11y WCAG2AAA sans problème (FR, EN, période vide, métrique commandes) ; captures `docs/images/insights-*` refaites avec le menu stylé |
| US-202 Catégories de taxe | 3 | Catégorie `standard` (20 %) sur le catalogue et la livraison, TVA et total TTC au panier et sur la commande, prix HT affichés inchangés, zone de taxe corrigée, limite d'approbation comparée au total HT (plugin 1.4.0, `amount_basis: net`), historique de commandes généré gardé sans TVA | Tests rouges seuls `324bfb1`, `477f541`, `b3c2668`, `6a4246a`, `e239587`, `5c508bb` ; `CartTaxTest`, `CartApprovalNetLimitTest`, `OrderHistoryWithoutTaxTest` ; pa11y du panier FR et EN sans problème |

Transverses faites en tête de sprint : `bin/proof-rebase` (`7a64c80`), un seul cache de rendu par worker avec contrôle du disque et vignettes `b2b_photo` réelles dans `bin/render-page` (`25ad343`), `ErpDemoExchangeFixtureTest` isolé (`f529aa4`).

Contrôles d'ensemble avant la fusion de #116 : ECS, PHPStan sur tout le dépôt, Rector, `lint:container`, `lint:twig` (180 fichiers) : OK ; preuve publiée par `bin/proof-rebase` (299 fichiers de tests). **Suite complète locale non lancée** (5,9 Go libres selon `df`, sous le seuil de 6 Go) : la CI a fait foi. Premier passage rouge sur un test (voir « Incidents »), second passage vert sur les 5 contrôles.

## Décisions de Thibaut (2026-10-25, registre du sprint)

- Périmètre de 13 points, ordre de sacrifice US-202 puis US-201 (jamais appliqué), US-199 protégée.
- US-201 par surcharge dans la démo, sans release du plugin company-insights.
- Limite d'approbation comparée au total HT ; faute de point d'extension, **option A** : évolution du plugin d'approbation (réglage `amount_basis`), PR autorisée, puis GO séparé de fusion, tag v1.4.0 et release.
- GO des maquettes « Mon compte » et tableau de bord.
- Historique des commandes de démonstration gardé à 18 mois (comparaison N-1 sur 6 mois sur 12).
- GO de clôture avec les vérifications de production en point ouvert.

Décisions de l'orchestrateur notées au registre : livraison taxée comme la marchandise ; historique de commandes généré sans TVA.

## Éléments non terminés ou à confirmer

- **En attente de production** (après la remise à zéro de 03:00 du 2026-10-26) : T-199-10, T-201-10, T-202-08 (image reconstruite et pages vérifiées ; pages connectées vérifiables en local seulement, aucune connexion en production), et les revues T-199-11, T-201-11, T-202-09 (tables « scénario → test ou preuve » à compléter avec ces vérifications).
- **Contrôles reportés des sprints 28 et 29** au même moment : T-197-09, T-195-06, T-196-09, T-196-10, T-TECH-29-03.
- **Faites avec écart** : tenue du registre (une décision par défaut d'un worker notée après coup) ; disque (sous 5 Go à plusieurs reprises, workers à l'arrêt) ; T-202-05 faite par évolution du plugin et non par décoration dans la démo.
- Écarts de maquette assumés, documentés dans les fichiers de tâches : rôles au masculin générique du plugin, ligne « Membres » pour tous, « année glissante » au lieu de « 12 mois », filtres repliables sous la rangée période et export, « Personnalisée » seulement si des dates sont choisies, indicateurs en 2 × 2 à 1280 px.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Disque sous 5 Go à plusieurs reprises (swap à 4 Go avec quatre agents et MySQL) | Les trois workers à l'arrêt environ 40 minutes ; nettoyage par Thibaut | Voir la rétrospective (thème 1) |
| Finder à 8 Go, `df` à 5,9 Go (espace purgeable) | Suite complète locale refusée | La CI a fait foi ; seuil à mesurer avec `df` |
| CI rouge : `OrderHistoryFixtureQueryCountTest` (1120 requêtes pour 1050) | A révélé un vrai défaut : 15 commandes d'historique taxées par le tunnel, mélange HT et TTC | Corrigé en TDD (`5c508bb`, `6f50fc1`), budget non relevé |
| Zone de taxe `EU` retenue comme zone de taxe | TVA nulle dès la saisie de l'adresse de livraison | Corrigé dans les fixtures (portée `shipping`) |
| Plugin d'approbation sans point d'extension pour le montant | w202 s'est arrêté avant de coder un contournement et a proposé trois options | Option A choisie par Thibaut, plugin 1.4.0 |
| Suppressions récursives de caches refusées aux workers | Aucune contournée ; `bin/clean-test-caches` utilisé | Règle 18 respectée |
| `phpunit` lancé une fois en direct par w201 | Sans effet (test unitaire pur), signalé par le worker | Rappel de la règle 1 |
| `bin/console` plante à 128 Mo de mémoire en recompilant le cache local | Lint Twig retardé pour deux workers | Relancé avec `-d memory_limit=1G` |
| `bin/render-page` ne génère pas les vignettes pour les URL relatives (panier) | Copie de travail du script utilisée par w202 | À corriger (rétrospective) |
| Notifications d'attente des workers antérieures aux réponses | Relances inutiles | Voir la rétrospective |

## Métriques

| Métrique | Sprint 30 | Sprint 29 |
|----------|-----------|-----------|
| Points engagés / en done | 13 / 13 (production en attente) | 13 / 13 |
| Stories de code avec commit rouge | 3 sur 3 | 3 sur 3 |
| Tâches à la clôture | 32 ✅, 3 🟡, 6 en attente de production sur 41 | 30 ✅, 6 🟡, 2 🔲, 2 📅 sur 40 |
| Tâches transverses reportées | 0 | 2 |
| CI rouges après contrôles locaux verts | 1 (suite complète non lancée en local) | 0 |
| Défauts signalés après le déploiement | 0 relevé à la clôture | 1 |
| Commit embarquant le travail d'un autre worker | 0 | 1 |
| PR fusionnée avec une question ouverte | 0 | 0 |
| Publication sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Le canevas de maquettes : pages « Mon compte » (réglage « rôle ») et « Tableau de bord des achats ».
2. « Mon compte » de Claire, puis de Paul et d'Isabelle : le bloc « À faire » change avec le rôle.
3. Le tableau de bord d'Isabelle : « À traiter », indicateurs, graphique N / N-1, période sans commande.
4. Le panier de Claire : HT, livraison, TVA 20 %, TTC, message d'approbation en hors taxes (en production après le 2026-10-26).

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-199, US-201, US-202 | Done (vérifications de production en attente) |
| Plugin `sylius-purchase-approval-plugin` | v1.4.0 publiée (réglage `amount_basis`) |
| Refontes graphiques demandées le 2026-10-25 (home, fiche, liste, panier, compte, tableau de bord) | Toutes livrées ; maquettes réunies dans le canevas unique |

## Prochaines étapes

1. Sprint 30 clos le 2026-10-25 sur GO de Thibaut.
2. Point ouvert : vérifications de production après la remise à zéro de 03:00 du 2026-10-26 (T-199-10, T-201-10, T-202-08, puis les revues ; T-197-09, T-195-06, T-196-09, T-196-10, T-TECH-29-03), en lecture seule et sans connexion.
3. Planning du sprint 31.
