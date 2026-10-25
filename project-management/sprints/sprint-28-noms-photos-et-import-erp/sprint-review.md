# Sprint Review - Sprint 28 (Noms, photos et import ERP)

> Préparée le 2026-10-25, le jour du sprint. Les contrôles de production qui ne peuvent avoir lieu qu'après la remise à zéro de 03:00 du 2026-10-26 sont indiqués tels quels dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 28, `sprint-28-noms-photos-et-import-erp` |
| Période | 2026-10-25 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-21) |
| Points engagés | **13** (US-187 8, US-196 3, US-195 2) |
| Points en done | **13** (code livré et fusionné ; contrôles de production d'US-195 et d'US-196 à faire après la remise à zéro du 2026-10-26) |
| PR de la démo | #103 (planning), #104 (US-195), #105 (US-187 tranche B), #106 et #107 (US-196) |
| Autres dépôts | `sylius-erp-flatfile-plugin` : PR #2 (import des prix par canal et des stocks) et #3 (`chore(release): 0.2.0`), tag `v0.2.0`, release GitHub, Packagist à jour |
| Mode | Orchestrateur (Claude) et workers : w195 (US-195), w196 (US-196), w187 (US-187) ; planche des photos par l'orchestrateur avec Claude Design |

## Sprint Goal

> « Présenter un catalogue crédible, avec des noms de produits sans marque réelle et une photo cohérente par famille, et fermer la boucle de l'échange avec l'ERP fictif : un fichier de prix et de stock est importé par le plugin, avec rapport de rejet et journal des lots, sans aucun système réel. »

**Atteint dans le code, à confirmer en production.** Les 86 noms sont réécrits, 201 produits sur 210 ont une photo libre et sans marque, et l'ERP fictif dépose des fichiers de prix et de stock que le plugin `v0.2.0` importe. Les noms et les photos ne seront visibles en production qu'après la remise à zéro de 03:00 du 2026-10-26 (la base survit aux déploiements) : ce contrôle reste ouvert.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-195 Noms de produits sans marque réelle | 2 | 86 noms réécrits en FR et EN (les 77 de l'inventaire du sprint 27, plus 9 qui citaient une marque ou une marque déposée) ; `mpn` des 210 produits passé à `DEMO-<SKU>` (valeur validée par Thibaut) ; SKU et slugs inchangés ; liste noire dans `config/demo/forbidden-brands.txt`, seul endroit qui cite ces marques ; garde `ProductBrandGuardTest` sur les CSV (nom, descriptions, `mpn`) et sur le reste du dépôt (#104) | Tests rouges seuls `0b9feeb` ; 600 tests ciblés sans échec ; PHPStan sur tout le dépôt, ECS, Rector, lint Twig et YAML : OK |
| US-187 Import de prix et de stock | 8 | **Tranche A (plugin, 5 points)** : import des prix par canal et du stock, rapport de rejet, journal des lots, idempotence (PR #2 du plugin, puis release #3, tag `v0.2.0`, release GitHub sur GO de Thibaut, Packagist à jour). **Tranche B (démonstration, 3 points)** : l'ERP Démo (fictif) dépose trois fichiers atomiques (prix valides, prix invalide exprès, stock), le plugin les importe à chaque construction de l'échange, la page « Échanges ERP » du back-office liste chaque lot avec son rapport (12 motifs traduits FR et EN), `docs/erp-demo.md` (#105, plugin `^0.2`) | Plugin : tests rouges `5c31693`, code `bacbbbf`, correctif d'isolation `3485a05`, matrice CI verte avant le tag. Démo : tests rouges seuls `ad48171` ; 435 tests ciblés verts avec le vrai paquet `v0.2.0` ; ECS, PHPStan sur tout le dépôt, Rector, `lint:container` : OK |
| US-196 Photos du catalogue | 3 | 30 types de produits couvrent les 210 produits : 29 photos (Pexels et Unsplash, licences relues, crédits tenus) et un visuel neutre pour la veste de pluie ; la botte en caoutchouc passe en visuel neutre dans #107 : **201 produits en photo, 9 en visuel neutre** ; import local reproductible `bin/import-product-images` (aucun accès réseau, recadrage 4:3, 800 × 600, 78 Ko au plus, WebP) ; page « Crédits des images » FR et EN ; garde d'import (source vide ou racine du disque refusée avant toute écriture) (#106, #107) | Tests rouges seuls `dc70aac`, `a50cd39` (#106) et `6b74c9a` (#107) ; 83 puis 85 tests ciblés verts ; PHPStan sur tout le dépôt, ECS, Rector : OK ; captures avant/après sur la base rechargée |

## Décisions de Thibaut (2026-10-25)

- Questions 3, 4 et 6 de l'ADR 0017 tranchées : **prix par canal**, **stock en valeur absolue** (`on_hand`), **aucune rétention** dans le plugin (ADR 0017 mis à jour).
- Ordre de sacrifice validé (tranche B d'US-187, puis US-196 ; US-195 protégée), jamais appliqué.
- GO de push, de tag et de release du plugin ; Packagist mis à jour par Thibaut.
- `mpn` des produits sous la forme `DEMO-<SKU>`.
- Photos : choix sur la planche et GO de téléchargement ; **pas de retouche** (les photos pixellisées sont remplacées) ; la veste de pluie et la botte en visuel neutre ; les clés gardées malgré la gravure de matière tronquée « CHR… ».
- Sylius Plus : un recouvrement avec le multi-source d'inventaire est possible et n'est pas levé ; le code de l'import est conservé, **aucune promotion** de cette fonction (politique de communication du 2026-10-14).

## Éléments non terminés ou à confirmer

- **Contrôles de production d'US-195 et d'US-196** (T-195-06, T-196-09, T-196-10) : les noms et les photos ne sont visibles qu'après la remise à zéro de 03:00 du 2026-10-26. À faire ce jour-là : URL de `EPI-CASK-001` en 200 avec le même slug, aucun nom de la liste noire dans le rendu FR et EN, pa11y et largeurs (catalogue, une fiche, page Crédits), LCP du catalogue avant et après.
- **Contrôles de production d'US-187** (T-187-20, faite avec écart) : le 2026-10-25, après le déploiement de #105, `/echange-fichier` affiche l'extrait et `/healthz` répond 200. La page d'administration « Échanges ERP » n'est vérifiée qu'en local (aucun identifiant saisi en production).
- **Actions de la rétro du sprint 27 non faites** : action 1 (un seul cache de rendu et seuil disque dans `bin/render-page`, T-TECH-28-01), action 2 (`bin/proof-rebase`, T-TECH-28-02), action 5 (remesure du LCP de la home, T-TECH-28-05) : reportées au sprint 29. Les actions 3 et 4 sont faites (règles 13 et 16 de `consigne-worker.md`).
- **Écart documenté** : un lot ignoré parce que déjà traité n'est pas journalisé par le plugin ; il n'apparaît que dans la sortie de la commande.
- **Réserves d'US-196** : une seule photo par type, donc toutes les couleurs d'une famille montrent la même photo ; les cartes portrait recadrent au centre. Story de suivi : [US-197](../../backlog/user-stories/US-197.md).
- Capture `docs/images/admin-erp-exchanges-desktop-fr.jpg` à régénérer avec les nouveaux noms.
- Rien dans le périmètre n'a été sacrifié.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| **2026-10-25 vers 09:08** : un worker lance à la main `cp -Rn "$D"/. public/media/image/` avec `$D` vide (glob sans résultat) ; la commande copie la racine du Mac (`/Library`, `/usr`, …, 6,4 Go) dans `public/media/image` jusqu'au disque plein ; OrbStack et MySQL s'arrêtent | Machine de développement arrêtée un temps. Originaux intacts (vérifiés), rien commité (le dossier est ignoré par git) ; Thibaut supprime les copies | Nouvelle règle (voir la rétrospective) : aucune copie, suppression ni déplacement récursif sur un chemin issu d'une variable ou d'un glob sans garde |
| CI rouge de la PR #2 du plugin : les tests d'intégration n'étaient pas isolés des fixtures Sylius chargées par la CI | Premier push rouge ; tag différé | Correctif d'isolation `3485a05`, matrice verte, puis release #3 et tag `v0.2.0` |
| Espace libre sous 5 Go plusieurs fois (`vendor` du plugin de 459 Mo réinstallé, caches) | Lots différés | Action 1 reportée ; voir la rétrospective |
| Une réserve attribuée à tort à Thibaut par un agent (ruban) ; « accepté par Thibaut » écrit par un worker pour une décision de l'orchestrateur | Décisions mal attribuées dans des documents | Registre des décisions avec auteur |
| PR #106 fusionnée par l'enchaînement automatique avant la réponse de Thibaut à une question ouverte | Question traitée après coup (#107) | Pas de fusion automatique quand une question à Thibaut est en suspens sur la PR |

## Métriques

| Métrique | Sprint 28 | Sprint 27 |
|----------|-----------|-----------|
| Points engagés / en done | 13 / 13 (contrôles de production en attente) | 13 (dont 1 ajouté) / 13 |
| Stories de code avec commit rouge | 3 sur 3 (US-195, US-187, US-196) | 4 sur 4 |
| Tâches décomposées à la clôture de la revue | 37 ✅, 4 🟡, 1 👀, 6 🔲 sur 48 | 42 ✅, 1 🟡, 2 ⊘ |
| Espace libre minimal | sous 5 Go plusieurs fois, jusqu'au disque plein le 2026-10-25 | 3 à 4 Go |
| CI rouges après tests ciblés verts | 1 (PR #2 du plugin) | 0 |
| Défauts trouvés en production après merge | 0 relevé à ce stade (noms et photos pas encore visibles) | 1 |
| Incidents sur la machine de développement | 1 (disque plein, 6,4 Go copiés) | 0 |
| Publication sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Le catalogue en français puis en anglais : des noms génériques, des photos cohérentes (après la remise à zéro du 2026-10-26 en production ; en local dès maintenant).
2. La page « Crédits des images » : auteur, source et licence de chaque photo.
3. Le back-office, Intégrations › Échanges ERP (fictif) : lots d'import de prix et de stock, dont le lot invalide rejeté avec son rapport.
4. `/echange-fichier` et `docs/erp-demo.md` : ce qui est simulé, ce que fait le plugin `v0.2.0`.
5. Le dépôt du plugin : release `v0.2.0` et CI verte.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| [US-197](../../backlog/user-stories/US-197.md) Photos du catalogue : cadrage des cartes et variantes de couleur (3) | Créée, candidate au sprint 29 |
| T-TECH-28-01, 02, 05 (actions 1, 2 et 5 de la rétro du sprint 27) | Reportées au sprint 29 |
| Suite de l'ADR 0017 (story C) | Backlog, non engagée |

## Prochaines étapes

1. GO de Thibaut sur la revue et la rétrospective.
2. Contrôles de production après la remise à zéro de 03:00 du 2026-10-26 (T-195-06, T-196-09, T-196-10), puis clôture du sprint 28.
3. Planning du sprint 29 (US-197 et actions reportées).
