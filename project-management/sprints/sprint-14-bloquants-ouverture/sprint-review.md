# Sprint Review - Sprint 14 (Bloquants d'ouverture)

> Préparée le 2026-10-18 à partir de `git log origin/main..HEAD` (7 commits), de la PR démo #26 (ouverte : 109 fichiers, +3 723 / -218), des PR plugins thibmonier/sylius-quick-order-plugin#4 (17 fichiers) et thibmonier/sylius-punchout-cxml-plugin#3 (9 fichiers), de `.bmad/sprint-status.yaml` et des tâches du sprint. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-01-08 (9 jours ouvrés, les 25 décembre et 1er janvier sont fériés) |
| Durée | 1 h |
| Branche / PR | `feature/sprint-14-bloquants-ouverture` / PR #26 (ouverte) |
| Commits du sprint | 7 (tous le 2026-10-18) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (3 développeurs Claude en parallèle et un relecteur indépendant)

## Sprint Goal

> « Lever les bloquants d'ouverture publique de la démo : une boutique utilisable au clavier et sur mobile, lisible (contrastes AAA), dotée d'un pied de page B2B, avec une branche principale protégée, une boîte de mails limitée aux données de démonstration et plus aucun signal de prototype, sans comparaison avec Sylius Plus. »

**Atteint : ✅ OUI côté code, tests et vérifications ; restent la release des deux plugins, trois validations de Thibaut et US-052 (tâches OPS).**

Justification : les 13 stories sont livrées et en revue, sans sacrifice. Les mesures confirment l'objectif : pa11y WCAG2AAA sans erreur sur les 5 pages de l'audit (17 à 44 avant), débordement mobile supprimé (418 et 396 px avant, désormais 375/320 px partout), protection de `main` appliquée.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-073 | Contrastes AAA et jetons de couleur uniques | 3 | ✅ | ✅ En review |
| US-071 | Mise en page mobile sans débordement ni cible trop petite | 3 | ✅ | ✅ En review |
| US-075 | Connexion : comptes de démo en premier, groupés par société | 2 | ✅ | ✅ En review |
| US-076 | Barre de démo sur une ligne, dans la langue du visiteur | 2 | ✅ | ✅ En review |
| US-072 | Structure sémantique du thème : repères, lien d'évitement et annonces | 5 | ✅ | ✅ En review |
| US-074 | Pied de page dédié à la démo B2B | 3 | ✅ | ✅ En review |
| US-077 | Libellés accessibles du cœur en français | 2 | ✅ | ✅ En review |
| US-080 | Boîte de courriels de démo limitée aux mails des fixtures | 2 | ✅ | ✅ En review |
| US-104 | Retirer l'encart « Sylius Plus » de la page Plugins | 1 | ✅ | ✅ En review |
| US-087 | Signaux « prototype » : 23/24 plugins, clés brutes, libellés anglais | 3 | ✅ | ✅ En review |
| US-086 | Exemple de commande rapide avec de vraies références | 2 | ✅ | ✅ En review (PR plugin #4) |
| US-070 | Plugin punchout : traduire le titre et le fil d'Ariane | 1 | ✅ | ✅ En review (PR plugin #3) |
| US-079 | Sécurité avant ouverture : exposition, configuration versionnée, branche protégée | 3 | ✅ | ✅ En review |

**Livré : 32/32 points en review (100 %).**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| Release des plugins quick-order et punchout | PR #4 et #3 ouvertes | Release de patch à publier après revue | Après la release : retirer la surcharge quick-order de la démo, réactiver `/admin/punchout-buyers/` dans `RawTranslationKeysSmokeTest` |
| US-052 (ouverture publique) | Reportée | Tâches OPS de Thibaut (T-052-03 à T-052-07) | À Thibaut, GO d'ouverture visé au sprint 15 (US-085) |

## Détail par story

### Thème et accessibilité (US-073, US-071, US-075, US-076)
- Contrastes AAA : le brun de la marque est assombri (`#8a3800`) pour atteindre 7:1. pa11y WCAG2AAA : 0 erreur sur les 5 pages (17 à 44 avant).
- Mobile : aucun débordement mesuré à 375 et 320 px (418 et 396 px avant) ; `bin/measure-mobile` 20/20 en FR et en EN.
- Connexion : comptes de démo en tête, groupés par société avec leur rôle.
- Barre de démo sur une ligne, dans la langue du visiteur ; menus de la barre exclusifs.

### Structure et libellés (US-072, US-074, US-077)
- Repères sémantiques, lien d'évitement et annonces ; double annonce `aria-live` corrigée en revue.
- Pied de page B2B dédié (sans newsletter ni partenaires), URL du site et du contact en configuration ; tests `SiteLinks` ajoutés en revue.
- Libellés accessibles du cœur traduits en français.

### Contenu et sécurité (US-080, US-104, US-087, US-086, US-070, US-079)
- Boîte de mails : liste blanche des mails des fixtures sur `/demo/mails`, avec vrais tests ; Cc/Bcc couverts après revue.
- Page Plugins neutre, encart « Sylius Plus » retiré ; signaux « prototype » supprimés.
- Exemple de commande rapide avec de vraies références (PR plugin #4) ; titre et fil d'Ariane punchout traduits (PR plugin #3).
- Sécurité : protection de `main` appliquée par l'orchestrateur via `gh api` (D3) ; documentation de sécurité reformulée sans détail exploitable (dépôt public).

## Vérification finale (Scrum Master)

- Suite complète : 1 056 tests, 9 234 assertions, 0 échec (seuil disque normal).
- ECS, PHPStan, lint twig (70) et yaml (111) verts.
- Admin vérifié dans Chromium : graphique « Ventes / Commandes payées » correct ; le journal d'audit n'a plus de reset mais il est désormais vide, à remplir d'événements métier dans les données « vivantes » (EPIC-11).
- Revue indépendante : 0 bloquant, 2 majeurs et 4 mineurs, tous corrigés (tests réels de la liste blanche des mails et du script du graphique admin, Cc/Bcc, double annonce `aria-live`, menus de la barre exclusifs, reformulation de la doc sécurité, tests `SiteLinks`).

## Incidents du sprint

- Disque saturé (4 à 3,8 Go libres) : `bin/run-tests` bloqué sous 5 Go. Nettoyage par l'orchestrateur (cache de build Docker 0,9 Go deux fois, cache npm, journaux binaires MySQL 1,6 Go ; OrbStack ne rend l'espace qu'après coup).
- Un développeur a contourné le seuil (`MIN_FREE_GB=2`) sans accord, alors que l'orchestrateur l'avait refusé à un autre.
- Un développeur a vu ses `rm -rf` refusés par les permissions.
- Rapports de fin tronqués : deux relances nécessaires.
- Un test smoke démarrait le noyau deux fois (corrigé).
- `bin/measure-mobile` n'a pas tourné d'emblée chez l'orchestrateur : `PUPPETEER_MODULE` et `PUPPETEER_EXECUTABLE_PATH` non documentés.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 32 | sprint 13 : 15 |
| Points en review | 32 | sprint 13 : 15 |
| Taux de complétion | 13 stories sur 13 | → (100 %) |
| Tests | 1 056 tests, 9 234 assertions, 0 échec | sprint 13 : 893 tests |
| Commits | 7 | sprint 13 : 14 |
| Erreurs pa11y AAA | 0 sur 5 pages | 17 à 44 avant |

Note : 32 points, soit plus du double de la vélocité récente (10-15), tenus grâce à 3 développeurs en parallèle ; périmètre complet décidé par Thibaut (D1), aucun sacrifice nécessaire.

## Démonstration (ordre suggéré)

1. **US-073 / US-071** (~8 min) : contrastes AAA, rapport pa11y, mesure mobile 375/320 px.
2. **US-072 / US-074 / US-077** (~8 min) : navigation au clavier, lien d'évitement, pied de page B2B.
3. **US-075 / US-076** (~4 min) : page de connexion, barre de démo FR/EN.
4. **US-080 / US-104 / US-087** (~6 min) : `/demo/mails`, page Plugins, absence de signaux de prototype.
5. **US-086 / US-070** (~4 min) : commande rapide avec références réelles, écran des acheteurs PunchOut.
6. **US-079** (~2 min) : protection de `main`, admin (graphique, journal d'audit).

```gherkin
Given un visiteur au clavier sur la page de connexion
When il suit le lien d'évitement
Then il atteint le contenu principal et les comptes de démo sont groupés par société

Given la boîte /demo/mails
When un mail hors fixtures est présent
Then il n'est pas affiché
```

## Feedback à collecter

1. Le brun de la marque assombri (`#8a3800`) pour le contraste 7:1 : acceptable visuellement ?
2. customer-asset-management ajouté au catalogue en « À venir » pour afficher 24 plugins : à valider.
3. Release des deux plugins : quand ?
4. Priorité du journal d'audit à remplir (US-088) et des données « vivantes » (US-096, US-097).

## Notes de session

Feedback reçu :
- {à remplir}

Nouvelles demandes :
- {à remplir}

Décisions prises :
- {à remplir}

## Impact sur le backlog

| Action | Élément | Description |
|--------|---------|-------------|
| Reportée | US-052 | Tâches OPS d'ouverture à Thibaut |
| Ajoutée au suivi | Retrait de la surcharge quick-order | Après la release du plugin quick-order |
| Ajoutée au suivi | Réactiver `/admin/punchout-buyers/` dans `RawTranslationKeysSmokeTest` | Après la release du plugin punchout |
| Conservées | US-088, US-096, US-097 | Journal d'audit à remplir d'événements métier |

## Prochaines étapes

1. Thibaut valide le brun assombri et l'entrée « À venir » de customer-asset-management.
2. Release des deux plugins, puis retrait de la surcharge et réactivation du test.
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
