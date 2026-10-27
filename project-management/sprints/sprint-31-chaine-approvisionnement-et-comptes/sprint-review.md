# Sprint Review - Sprint 31 (Chaîne d'approvisionnement et comptes)

> Préparée le 2026-10-27, le jour du sprint, avant la fusion de la PR #125 (brouillon, CI verte). Ce qui ne peut se vérifier qu'après la fusion et le déploiement de `main` est indiqué tel quel dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 31, `sprint-31-chaine-approvisionnement-et-comptes` |
| Période | 2026-10-27 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-09) |
| Points engagés | **13** (US-208 5, US-203 5, US-204 3), plus la tâche technique T-TECH-31-11 (LCP de la home, 2 h) |
| Points en done | **13** (code livré en revue sur la branche ; contrôles après fusion en attente) |
| PR de la démo | #125 (sprint, brouillon, 83 fichiers, +4 342 / -292), #123 (vérifications de production du 2026-10-27, fusionnée), #124 (page Crédits, fusionnée) |
| Autres dépôts | `sylius-timeline-plugin` : PR #7 (test de commande déterministe), fusionnée sur GO de Thibaut, **aucun tag** |
| Maquettes | Canevas Claude Design unique, version 20 : planche du relevé de paiements à terme et page « Plugins », GO de Thibaut le jour même |
| Mode | Orchestrateur (Claude) et workers w208 (US-208), w203 (US-203), w204 (US-204), sur la branche partagée `feature/sprint-31-dev` |

## Sprint Goal

> « Fermer les portes d'entrée de la chaîne d'approvisionnement PHP de la démo (verrou, plugins Composer, workflows), et donner aux pages de compte des plugins et à la page « Plugins » la charte de « Mon compte » et de la home, avec des maquettes validées avant le code. »

**Atteint dans le code, à confirmer après déploiement.** Le verrou est revu contre la branche de base, la politique Composer est explicite, les workflows sont durcis et audités ; les pages de compte des plugins et la page « Plugins » suivent des maquettes validées par Thibaut avant tout Twig. Restent les contrôles qui exigent `main` déployé (image reconstruite, mesures de production) et le réglage des contrôles requis par Thibaut.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-208 Chaîne d'approvisionnement PHP | 5 | `config.policy` Composer explicite ; `bin/check-lock-diff` (compare `composer.lock` à la branche de base : tag réécrit à version égale, nouveau `composer-plugin`, résumé des mises à jour ordinaires, sans réseau) ; wishlist passée de `1.4.x-dev` à `v1.4.0` (seul paquet changé dans le verrou) ; `permissions: {}` et `persist-credentials: false` ; job zizmor 1.31.1 figé par condensat (9 alertes, 0 après correction) ; ligne dans `docs/durcissement-demo.md` | Tests rouges seuls `d8e8859` ; contrôle « si vous avez été touché » : aucune version visée installée dans les 37 révisions du verrou ; CI : Dependency audit, Workflow audit (zizmor) verts |
| US-203 Pages de compte des plugins | 5 | Devis (liste, détail), assistants IA, page de consentement (cadre B2B seul) et relevé de paiements à terme dans le cadre « Mon compte », par surcharge dans la démo (plugins intacts, `data-test` conservés) ; test de dérive sur les empreintes des 5 gabarits d'origine ; bouton de refus au contraste AAA, numéro de commande du relevé en cible de 44 px | Tests rouges seuls `27152ab`, `72f7f49`, `a92ed69` ; maquette validée avant Twig (`368a98f`) ; pa11y WCAG2AAA sans problème sur 10 pages rendues en local ; captures `docs/images/account-*` relues à 1280 et 375 px |
| US-204 Page « Plugins » | 3 | Page dans la charte de la home : cartes par famille, badges issus de la preuve, description coupée à 4 lignes ; plugin ERP retiré de la page (`listed: false`, option B) mais compté ailleurs | Test rouge seul `a36ce9c` ; pa11y AAA FR et EN sans problème ; `bin/measure-mobile` FR tout OK à 320 et 375 px |
| T-TECH-31-11 LCP de la home | 0 (2 h) | Photo du hero préchargée en bureau seulement, priorité haute retirée, préchargement d'Inter retiré sur toutes les pages | Test rouge seul `10fa917` ; 1 696 → 1 332 ms en local au protocole w186 ; **à remesurer en production** |

Transverses : cohérence HT et TTC de l'historique (`135e006`, échec prouvé sur une commande taxée simulée) ; `bin/render-page` pour les URL relatives (`39d63c7`, `f14f4f6`) ; règles 24 et 25 de la consigne ; `memory_limit=1G` documenté.

Contrôles avant la PR : ECS, PHPStan max sur tout le dépôt, Rector, lint Twig et YAML verts ; preuve régénérée par `bin/proof-refresh` (311 fichiers de tests). **Suite complète locale non lancée** (T-TECH-31-02 : 5,0 Go libres, 4 Go de swap, dit dans la PR) : la CI l'a jouée, verte sur les 7 contrôles (Quality and smoke tests, Docker image, Dependency audit, Image scan and SBOM, Workflow audit zizmor, dependabot, GitGuardian).

## Contrôles de production du 2026-10-27 (PR #123, fusionnée, hors budget)

- OK : T-195-06, T-199-10 et T-199-11, T-201-10 et T-201-11, T-202-08 et T-202-09.
- Page Crédits à 1 046 px de large sur 360 px : corrigée hors sprint par la PR #124 (fusionnée) ; la cause datait d'US-186. **Écart de processus** : la décision a été notée au registre après l'ouverture de la PR.
- LCP mobile de la home à 1 616 ms (FR) pour un seuil de 1 456 ms : devenu T-TECH-31-11.

## Décisions de Thibaut (2026-10-27, registre du sprint)

- Périmètre de 13 points ; ordre de sacrifice US-204, puis T-TECH-31-11, puis US-203 ; US-208 protégée (jamais appliqué).
- Surcharge dans la démo pour les pages de compte, page de consentement en cadre B2B seul, US-207 et US-209 hors sprint.
- GO des maquettes (relevé, page « Plugins ») ; plugin ERP retiré de la page tant que le recoupement avec Sylius Plus n'est pas levé ; description à 4 lignes sans champ « rôle ».
- Correction de la CI de Timeline avant l'intégration, puis GO de fusion de la PR #7, sans tag.

Décision de l'orchestrateur : option B pour le retrait de l'ERP (plugin gardé dans le catalogue avec `listed: false`, compteurs publics inchangés), en application de la décision de Thibaut.

## Éléments non terminés ou à confirmer

- **Après la fusion** : T-208-11 (Thibaut ajoute zizmor aux contrôles requis de `main`) ; T-208-10 ; T-203-14 et T-203-15 (image reconstruite, conteneur neuf, revue) ; T-204-08 et T-204-09 (captures `docs/images/plugins-*`, pa11y de production) ; T-204-10 et T-204-11.
- **Mesures de production** : LCP de la home, et de la fiche et de la liste produit (le préchargement d'Inter a été retiré partout).
- **Faites avec écart** : T-TECH-31-04 (la capture du panier par `bin/render-page` n'a pas pu être faite, images produit non importées en local) ; T-TECH-31-02 (suite complète non jouée en local) ; T-TECH-31-01 (le relevé de la machine a été fait, mais `bin/check-disk` n'affiche pas le nombre de workers autorisés) ; T-TECH-31-06 (registre après l'action pour la PR #124) ; T-TECH-31-07 (seuils de disque non tenus) ; T-TECH-31-11 (remesure en production en attente) ; T-208-08 (suite complète non jouée).

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Disque entre 4 et 5,6 Go toute la journée, swap de 3,7 à 4,2 Go | Aucune suite complète locale ; seuil de 7 Go de la règle 24 jamais atteint | Rétrospective, thème 1 |
| Verrou partagé de `bin/run-tests` : « already running » | Des runs refusés, workers en attente | Rétrospective, thème 1 |
| `public/build` périmé (25 octobre) | Workers ayant compilé le SCSS à part ; reconstruction par l'orchestrateur (`yarn build:prod`) avant les captures | Rétrospective, thème 2 |
| Images produit non importées en local | Capture du panier par `bin/render-page` impossible | Rétrospective, thème 3 |
| CI de Timeline instable (zone de livraison tirée au hasard par les fixtures Sylius) | Preuve rouge pour ce dépôt | PR #7 du plugin, fusionnée sur GO, sans tag |
| Conflit sur le retrait de l'ERP de la page | Arbitrage de l'orchestrateur (option B) | Noté au registre |
| Messages croisés, deux fois (w204 attendait une réponse déjà envoyée) | Attente inutile ; la règle 25 a aidé en partie | Rétrospective, thème 4 |
| Correctif de la page Crédits : registre après la PR | Écart à la règle « registre avant l'action » | Rétrospective, thème 4 |
| Connexion en un clic non déclenchée par un clic simple dans puppeteer | `form.requestSubmit()` nécessaire | À noter dans `docs/captures-vitrine.md` |

## Métriques

| Métrique | Sprint 31 | Sprint 30 |
|----------|-----------|-----------|
| Points engagés / en done | 13 / 13 (contrôles après fusion en attente) | 13 / 13 |
| Stories de code avec commit rouge | 3 sur 3 (plus T-TECH-31-11, -03 et -04) | 3 sur 3 |
| Tâches à la clôture | 33 ✅, 7 🟡, 8 📅 sur 48 | 32 ✅, 3 🟡, 6 en attente sur 41 |
| CI rouges sur la PR du sprint | 0 | 1 |
| Défauts signalés après le déploiement | 0 relevé à la clôture | 0 |
| Relances pour une décision déjà envoyée | 2 | non mesuré |
| PR fusionnée avec une question ouverte | 0 | 0 |
| Publication ou tag sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Le canevas de maquettes (version 20) : planche du relevé et page « Plugins ».
2. « Mon compte » de Claire : devis, relevé de paiements à terme, assistants IA dans le même cadre ; la page de consentement en cadre B2B.
3. La page « Plugins » en FR et EN, à 1280 et 375 px.
4. La CI : job zizmor et revue du verrou sur la PR #125 ; `bin/check-lock-diff` sur une fixture de tag réécrit.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-208, US-203, US-204 | En revue (contrôles après fusion en attente) |
| US-207 (indexation), US-209 (dépôts de plugins), US-205, US-206 | Hors sprint, prêtes pour développement ; US-209 dépend d'US-208, US-207 vient après US-204 à US-206 |

## Prochaines étapes

1. Fusion de la PR #125 sur GO de Thibaut, après CI verte.
2. Thibaut : zizmor dans les contrôles requis de `main` (T-208-11).
3. Après déploiement : T-203-14 et 15, T-204-08 à 11, T-208-10, remesure du LCP (home, fiche, liste), cadrée par la rétrospective.
4. Planning du sprint 32.
