# Sprint Review - Sprint 26 (Vitrine et flux)

> Préparée le 2026-10-24, le jour du sprint. Les contrôles en production non consignés dans le dépôt sont indiqués tels quels dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 26, `sprint-26-vitrine-et-flux` |
| Période | 2026-10-24 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-21) |
| Points engagés | 23 au planning (US-186 8, US-183 5, US-185 5, US-184 3, US-181 1, US-182 1), **29** avec les trois stories ajoutées en cours de sprint (US-190 2, US-191 1, US-192 3) |
| Points en done | **29** |
| PR de la démo | #83 (lancement), #84 (US-183), #85 (US-182), #86 (US-184), #87 (US-186), #88 (US-190), #89 (US-186, en-tête), #90 (US-191), #91 (US-192) |
| Autres dépôts | `sylius-b2b-mcp-plugin` #10 et #11, release v1.2.3 ; `sylius-einvoice-transmission-plugin` #1, release v0.2.0 ; nouveau dépôt `sylius-erp-flatfile-plugin`, release v0.1.0 (Packagist) ; `sylius-pack-ai-headless` #3 |
| Mode | Orchestrateur (Claude) et workers : w186 (US-186), w183 (US-183, US-184), w185 (US-185, US-181, US-182, US-190) ; US-191 et US-192 traitées dans la journée sur retour de Thibaut |

## Sprint Goal

> « Donner à la démo une vitrine qui donne envie de la parcourir (home refaite, photos libres de droits, liens de présentation) et montrer deux flux de bout en bout sans système réel : la facture électronique transmise à une plateforme fictive avec ses statuts et sa relance, et l'export des commandes en fichier plat. »

**Atteint.** La home est refaite d'après une maquette validée avant le code, avec huit photos Pexels et une page de crédits ; la facture électronique suit un parcours complet (plateforme fictive, statuts, écran « Transmissions », relance d'un envoi en erreur) ; l'export des commandes en fichier plat est un plugin publié et visible dans la démo. Trois retours de Thibaut sur l'en-tête, la connexion et le menu de compte ont été traités le même jour.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-186 Refonte de la home | 8 | Hero éditorial, bande des engagements, rayons en tuiles photo, parcours, rôles, coulisses, bloc auteur ; bandeau « E-boutique de démonstration B2B » ; polices Fraunces et Inter auto-hébergées ; 8 photos WebP de 9 à 96 Ko ; page « Crédits des images » (#87). Puis menu dans la rangée du logo et logo sur deux lignes, sur retour de Thibaut (#89) | Tests rouges seuls `35454b0` puis `b8e6c3f` ; 355 tests ciblés verts ; pa11y WCAG2AAA sans problème (home FR, EN, acheteuse connectée) ; 360 sur 360 px ; LCP mobile « avant » en production : 1324 ms médian, 349 Ko, 11 requêtes |
| US-183 PA fictive de démonstration | 5 | Plateforme agréée fictive en processus, sans appel sortant, branchée sur le port du plugin de transmission ; statuts simulés par scénario ; page `/{locale}/pa-fictive` FR et EN avec bandeau « simulés » (#84) | Tests rouges seuls `15d85b0` ; 37 tests Fictitious* et Einvoice verts ; entrée au catalogue (26 plugins) |
| US-184 Statuts et relance | 3 | Plugin de transmission v0.2.0 (relance d'une ligne en erreur) ; écran « Transmissions » au back-office, bouton « Relancer » avec jeton seulement sur les lignes en erreur, panne simulée puis succès (#86) | Tests rouges seuls `17869e6` ; plugin : CI verte sur 10 jobs |
| US-185 Export CSV des commandes, dépôt atomique | 5 | Nouveau plugin `thibmonier/sylius-erp-flatfile-plugin` : contrat de fichier plat, export idempotent par `webhook_id`, dépôt atomique (nom provisoire puis renommage) ; release v0.1.0, sur Packagist | `ExportScenariosTest`, `AtomicFileDepositorTest`, `OrderLinesCsvTest`, `OrderExporterTest` ; CI verte (run 40088454801) |
| US-190 Plugin d'export dans la démo | 2 | Plugin `^0.1` installé, répertoire d'échange `var/erp-exchange`, page `/{locale}/echange-fichier` FR et EN (dix colonnes du contrat, extrait de fichier), aucun ERP nommé (#88) | Tests rouges seuls `b919369` ; 320 tests ciblés verts |
| US-181 Plugin b2b-mcp : `symfony/lock` et routes | 1 | `symfony/lock` requis, routes MCP importées une seule fois sans Flex ; release v1.2.3 ; pack IA en `^1.2.3` sans le geste manuel | b2b-mcp #10 ; pack IA #3 |
| US-182 Garde des `code` de tableaux | 1 | Test d'architecture sur `templates/demo/` ; deux gabarits non conformes corrigés (`accounts`, simulateur PunchOut) (#85) | Tests rouges seuls `10392f4` ; `OK (13 tests, 13 assertions)` |
| US-191 Page de connexion | 1 | Formulaire à gauche, comptes de démo à droite, formulaire d'abord sur téléphone (#90) | `LoginDemoAccountsTest` rouge puis vert ; pa11y sans problème FR et EN ; validée en production par Thibaut |
| US-192 Menu de compte | 3 | Menu déroulant « Bonjour <prénom> ! », compteur d'approbations (badge limité à 99+), menu catalogue ouvert dès 1200 px, barre de démo sans débordement à 320 et 360 px (#91) | `HeaderAccountMenuTest`, `AccountBadgeTemplateTest`, `HomeStylesTest`, `HomeRedesignTest` ; pa11y sans problème (visiteur, acheteur, approbateur) ; validée en production par Thibaut |

## Décisions de Thibaut (2026-10-24)

- Lancement : refonte de la home, ADR 0016 stories B et C, ADR 0017 story A, sorties du sprint 25 ; ordre de sacrifice US-185 puis US-184 (jamais appliqué).
- Maquette Claude Design validée avant tout gabarit Twig ; photos choisies avec lui.
- GO de publication : tag de `sylius-b2b-mcp-plugin`, `v0.2.0` du plugin de transmission, dépôt et `v0.1.0` du plugin de fichier plat ; soumission à Packagist par Thibaut.
- Ajout d'US-190 (GO), puis d'US-191 et d'US-192 sur ses retours en production (en-tête, connexion, menu de compte).
- US-189 (refonte de la fiche produit) au sprint 27.

## Éléments non terminés ou à confirmer

- **US-186, T-186-14 et T-186-15** : la mesure « après » (LCP mobile, pa11y, 360 et 1280 px en production), la table « scénario → test ou preuve » et la revue ne sont pas consignées dans le dépôt. Thibaut a vu la home en production (ses retours ont donné #89) ; la preuve chiffrée reste à noter.
- **US-182, T-182-05** : contrôle de largeur des pages à tableau en production non consigné.
- **US-190** : pa11y et 360 px de `/echange-fichier` en production annoncés « après déploiement » dans la PR, non consignés.
- **Tâches transverses T-TECH-26-06, 08, 09, 11** : sans trace datée à citer ; T-TECH-26-06 n'a pas été tenue en totalité (trois CI rouges, voir les incidents).
- Rien dans le périmètre n'a été sacrifié.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Arbre de travail partagé entre workers : le commit de tests rouges `7d08546` (US-184) a emporté trois suppressions de gabarits de la home indexées par un autre worker | Suppressions publiées par erreur, relevées avant le merge | Tests rouges reportés par cherry-pick sans les suppressions (`17869e6`) ; règle `git commit -m … -- <chemins>` adoptée en cours de sprint |
| Configuration non commitée dans l'arbre partagé : noyau cassé pour tous (500) | Tous les workers bloqués un temps | Même règle ; action 1 |
| CI rouge sur #87 après des tests ciblés verts : locale `en` contre `xx_YY`, variable d'environnement qui fuit entre tests | Un tour de CI | Corrigé dans la PR ; action 2 |
| CI rouge sur #88 : nombre de plugins codé en dur à 26 dans un test, alors que le catalogue passe à 27 | Un tour de CI | Compteur déduit du catalogue ; action 2 |
| CI rouge sur #91 : PHPStan sur un fichier de test non analysé en local | Un tour de CI | Corrigé ; action 2 |
| Disque : espace libre tombé à 2-3 Go, workers arrêtés sous 5 Go ; un worker a lancé une suite complète à 5,0 Go ; quitter Dia a libéré environ 5 Go de swap | Lots interrompus ou différés | Action 3 |
| Rendu local en mode prod impossible (503 « the demo is not configured safely ») | Captures faites à partir de HTML rendu par un test jetable | Action 4 |

## Métriques

| Métrique | Sprint 26 | Sprint 25 |
|----------|-----------|-----------|
| Points engagés / en done | 29 (dont 6 ajoutés en cours de sprint) / 29 | 17 / 17 |
| Stories de code avec commit rouge | 7 sur 9 citées dans la PR de la démo (US-181 et US-185 : dépôts de plugins, commit rouge non revérifié) | 4 sur 4 |
| Tâches décomposées à la clôture | 63 ✅, 7 🔲 (vérifications en production et transverses sans trace) | 61 ✅, 1 📅 |
| Plugins au catalogue de la démo | 27 | 26 |
| Fichiers de tests de la démo (preuve publiée, #87) | 242 | 227 |
| Plantage de la machine | 0 | 0 |
| Espace libre minimal | environ 2 à 3 Go (cible 5 Go) | 3,9 Go |
| Contournements de refus d'outil | non relevés dans le dépôt | 0 (5 refus) |
| CI rouges après tests ciblés verts | 3 (#87, #88, #91) | 1 |
| Défauts d'affichage trouvés en production après merge | 3 retours de Thibaut (en-tête, connexion, menu de compte), traités dans la journée | 1 |
| Publication sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. La home sur téléphone puis sur grand écran : hero, tuiles photo, crédits des images.
2. Le menu de compte déroulant avec le compteur d'approbations (compte approbateur) ; la page de connexion.
3. La PA fictive : une facture transmise, un refus, une panne simulée ; l'écran « Transmissions » et la relance.
4. `/echange-fichier` : les dix colonnes du contrat et l'extrait de fichier ; le plugin sur GitHub et Packagist.
5. La garde des `code` de tableaux (13 tests).

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| [US-189](../../backlog/user-stories/US-189.md) Refonte de la fiche produit (5 points) | Sprint 27 |
| [US-187](../../backlog/user-stories/US-187.md) Import CSV de prix et de stock (8), [US-188](../../backlog/user-stories/US-188.md) Démonstration avec l'ERP fictif (5) | Backlog (ADR 0017 stories B et C), non engagées |
| Stories D et E de l'ADR 0016 et de l'ADR 0017 | Non créées |
| Preuve chiffrée d'US-186 (LCP « après »), largeur en production d'US-182 et d'US-190 | À consigner (action 5 de la rétro) |

## Prochaines étapes

1. Validation de la revue, de la rétrospective et de la clôture du sprint 26 par Thibaut.
2. Planning du sprint 27 (US-189 et actions de la rétro).
