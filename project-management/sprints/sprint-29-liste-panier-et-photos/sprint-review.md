# Sprint Review - Sprint 29 (Liste, panier et photos)

> Préparée le 2026-10-25, le jour du sprint. Le contrôle en production des photos d'US-197, qui ne peut avoir lieu qu'après la remise à zéro de 03:00 du 2026-10-26, est indiqué tel quel dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 29, `sprint-29-liste-panier-et-photos` |
| Période | 2026-10-25 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-21) |
| Points engagés | **13** (US-198 5, US-200 5, US-197 3) |
| Points en done | **13** (code livré et fusionné ; contrôle de production d'US-197 après la remise à zéro du 2026-10-26) |
| PR de la démo | #110 (planning), #111 (sprint), #112 (mesures de production de la liste), #113 (home : trois produits par bloc) |
| Maquettes | Canevas Claude Design unique « Fournitures Pro — maquettes de la démo » : planche « Acquis », home, fiche produit, liste, panier, photos |
| Mode | Orchestrateur (Claude) et workers : w198 (US-198), w200 (US-200), w197 (US-197) sur la branche partagée `feature/sprint-29-dev` |

## Sprint Goal

> « Donner à la liste des produits et au panier la clarté de la home et de la fiche produit, avec des maquettes validées avant le code, et des photos de catalogue mieux cadrées et de la bonne couleur. »

**Atteint, à confirmer en production pour les photos.** La liste et le panier B2B suivent les maquettes validées par Thibaut avant tout gabarit Twig. Les photos sont cadrées sur leur point focal et six types montrent la couleur annoncée par leur nom ; elles ne seront visibles en production qu'après la remise à zéro de 03:00 du 2026-10-26.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-198 Liste des produits | 5 | En-tête de catégorie, avis des prix, catégories avec compteurs, recherche, tri, nombre par page, grille et liste côté serveur, état de stock sur la carte (stock suivi dans les fixtures, `DemoStockLevel`), ajout au panier depuis la carte, état « aucun résultat », visiteur sans prix avec invitation, B2C inchangé, `sizes` des vignettes adapté au 4:3 | Tests rouges seuls `76222f5`, `0211103`, `b0664e2` ; production (#112) : LCP mobile 464 à 632 ms, pa11y WCAG2AAA sans problème en FR et EN, aucun défilement à 360 et 1280 px |
| US-200 Panier | 5 | Lignes lisibles, prix négocié par ligne, récapitulatif, message d'approbation dès le panier (limite de l'acheteur), commande rapide et devis, panier vide, test de commande de bout en bout | Tests rouges seuls `0a62cd6` ; `CartPageTest`, `CartToOrderTest`, `CartTextCharterTest` ; pa11y WCAG2AAA local sans problème ; Puppeteer 360 et 1280 px, boutons de 44 px |
| US-197 Photos du catalogue | 3 | Tranche 1 : point focal par photo (`focal` du manifeste, `PhotoFraming`), filtres 4:3 `b2b_photo*`. Tranche 2 : planche de 18 aperçus, choix et GO de Thibaut, 6 photos libres (casques jaune, rouge, orange, bleu, gilet jaune, botte verte), aucune retouche, crédits à jour, **204 produits sur 210 en photo** ; import partiel `--only` | Tests rouges seuls `756372f`, `947e1c1` ; `ProductPhotoColourTest`, `ProductPhotoFramingTest`, `PhotoFramingTest` ; captures avant/après (simulées) |

Contrôles d'ensemble avant la PR #111 : suite complète locale **2907 tests**, un échec dû à un fichier d'échange ERP laissé par un chargement de fixtures local (rejoué seul vert après l'avoir mis de côté) ; ECS, PHPStan sur tout le dépôt, Rector : OK ; preuve publiée à jour (287 fichiers de tests) ; CI verte au premier passage sur #111, #112 et #113.

## Décisions de Thibaut (2026-10-25, registre du sprint)

- Périmètre de 13 points et ordre de sacrifice (US-197, puis US-200), jamais appliqué.
- Maquettes pour la liste et le panier seulement, dans un **canevas unique** qui garde aussi les acquis des refontes.
- Stock activé dans les fixtures pour l'afficher sur les cartes.
- Panier : message d'approbation dès le panier, « enregistrer en liste » retiré, test de commande de bout en bout.
- GO des maquettes de la liste et du panier.
- Écarts acceptés : ligne TVA masquée tant que les produits n'ont pas de catégorie de taxe (story [US-202](../../backlog/user-stories/US-202.md)) ; récapitulatif après les lignes sur téléphone.
- Planche US-197 : recommandations suivies, GO de téléchargement ; casque vert inchangé, veste de pluie en visuel neutre.
- Home connectée : trois produits par bloc au lieu de quatre (#113).
- GO de clôture avec le contrôle des photos après la remise à zéro en point ouvert.

## Éléments non terminés ou à confirmer

- **T-197-09** : photos d'US-197 en production après la remise à zéro de 03:00 du 2026-10-26 (pa11y, largeurs, page Crédits, une fiche par famille touchée).
- **Contrôles reportés du sprint 28** au même moment : T-195-06 (noms), T-196-09 et T-196-10 (photos), T-TECH-29-03 (LCP mobile de la home, seuil 1456 ms).
- **Faites avec écart** : captures « avant » du panier non faites (disque sous 5 Go) ; `bin/measure-mobile` non lancé (mesures Puppeteer à la place) ; README non touché ; panier connecté non vérifiable en production (aucune connexion) ; captures avant/après d'US-197 simulées sur les vrais fichiers, pas rendues par le noyau ; une entrée du registre écrite après l'action (#113).
- **Non faites, reportées au sprint 30** : T-TECH-29-01 (un seul cache de rendu imposé par l'outil) et T-TECH-29-02 (`bin/proof-rebase`), pour la troisième fois.
- Écarts de la maquette assumés et documentés dans les fichiers de tâches : « dès N pièces » et « unités », bouton « Appliquer », référence de commande à la dernière étape du tunnel, libellé « prix négocié ».

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Suppressions récursives de caches refusées aux workers | Aucune contournée ; commandes en chemins absolus données à Thibaut | Règle 18 respectée ; le nettoyage reste manuel |
| `config/packages/demo_theme_b2b.yaml`, partagé, commité par w198 avec une ligne de w200 | Gabarit de w200 manquant un temps ; deux tests rouges croisés | Prévenu entre workers, corrigé ; voir la rétrospective |
| Captures de la liste avec trois vignettes vides | Refaites après l'intégration des photos | Captures rendues sur une base aux images chargées |
| `bin/render-page` ne produit pas le filtre `b2b_photo` | Vignettes générées à la main, captures d'US-197 simulées | Voir la rétrospective |
| Test `ErpDemoExchangeFixtureTest` sensible aux fichiers du vrai `var/erp-exchange` | Un échec dans la suite complète locale | Test à isoler (rétrospective) |
| Worktree avec `vendor` en lien symbolique : l'autoload exécute le code de l'arbre principal | Faux résultat de test pendant #113, vu à temps | Vérification refaite dans l'arbre principal, remis à l'identique |

## Métriques

| Métrique | Sprint 29 | Sprint 28 |
|----------|-----------|-----------|
| Points engagés / en done | 13 / 13 (contrôle des photos en attente) | 13 / 13 |
| Stories de code avec commit rouge | 3 sur 3 | 3 sur 3 |
| Tâches à la clôture | 30 ✅, 6 🟡, 2 🔲 reportées, 2 📅 sur 40 | 37 ✅, 4 🟡, 1 👀, 6 🔲 sur 48 |
| CI rouges après contrôles locaux verts | 0 | 1 |
| Défauts signalés après le déploiement | 1 (quatrième produit de la home à la ligne, #113) | 0 relevé |
| PR fusionnée avec une question ouverte | 0 | 1 |
| Publication sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Le canevas de maquettes : planche « Acquis », puis liste et panier validés.
2. La liste Visserie connectée (Claire) : stock sur les cartes, grille et liste, recherche sans résultat ; puis en visiteur, sans prix.
3. Le panier de Claire : message d'approbation, commande rapide, devis, puis la commande jusqu'à l'approbation.
4. Les casques de couleur et la page « Crédits des images » (en production après le 2026-10-26).

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| [US-202](../../backlog/user-stories/US-202.md) Catégories de taxe (3) | Créée, candidate |
| US-199 « Mon compte », US-201 tableau de bord des achats | Backlog, maquettes à faire dans le canevas unique |
| T-TECH-29-01, T-TECH-29-02 | Reportées au sprint 30 |

## Prochaines étapes

1. Sprint 29 clos le 2026-10-25 sur GO de Thibaut.
2. Point ouvert : contrôles de production après la remise à zéro de 03:00 du 2026-10-26 (T-197-09, T-195-06, T-196-09, T-196-10, T-TECH-29-03), en lecture seule.
3. Planning du sprint 30.
