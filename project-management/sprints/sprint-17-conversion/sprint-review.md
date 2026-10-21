# Sprint Review - Sprint 17 (Conversion)

> Préparée le 2026-10-21 à partir de `git log origin/main..HEAD` (6 commits), de la PR #42 (brouillon), de `.bmad/sprint-status.yaml` et des tâches du sprint. Les livrables de communication sont hors dépôt (`communication/sprint-17/`) et ne sont pas reproduits ici. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-02-19 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-17-conversion` / PR #42 (brouillon) |
| Commits du sprint | 6 (dont 1 de planification, 2026-10-20 ; 5 le 2026-10-21) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude en parallèle)

## Sprint Goal

> « Transformer l'élan de l'annonce du 2026-10-19 en visites et en échanges (profil à jour, posts de suite, suivi à J+7 et J+30, mesure par campagne, charte, calendrier, kit média) et livrer la conversion dans la démo : parcours guidés complets par persona et page « Intégrer ces plugins dans votre projet ». »

**Atteint : ✅ OUI côté code et rédaction, avec réserves : les contenus de communication attendent la relecture et la publication de Thibaut (post 1 et profil avant le 2026-10-26) et la séquence PunchOut du kit média n'est pas captée.**

Justification : les 10 stories engagées sont en review, sans sacrifice. Parcours guidés, page « Intégrer » et mesure par campagne sont livrés dans la démo. La publication relève de Thibaut.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-105 | Parcours guidés : moteur de visite par étapes | 8 | ✅ | ✅ En review |
| US-106 | Parcours guidés : contenu par persona et écran de sortie vers le contact | 5 | ✅ | ✅ En review |
| US-107 | Page « Intégrer ces plugins dans votre projet » | 5 | ✅ | ✅ En review |
| US-117 | Mesure de la communication : visites par campagne sans cookie | 3 | ✅ | ✅ En review |
| US-116 | Charte éditoriale LinkedIn et messages clés | 2 | Hors démo | ✅ En review (relecture de Thibaut) |
| US-122 | Calendrier éditorial LinkedIn sur huit semaines | 3 | Hors démo | ✅ En review (choix de cadence par Thibaut) |
| US-123 | Posts LinkedIn, lot 1 : trois posts de suite | 3 | Hors démo | ✅ En review (relecture et publication par Thibaut) |
| US-127 | Suivi de l'annonce d'ouverture, de J+1 à J+30 | 2 | Hors démo | ✅ En review |
| US-129 | Profil LinkedIn : texte, bannière et « À la une » | 2 | Hors démo | ✅ En review (application par Thibaut) |
| US-120 | Kit média (1/2) : GIF de PunchOut, devis et commande rapide | 5 | Partielle | ⚠️ En review, PunchOut non captée |

**Livré : 38 points implémentés en review (10 stories), soit 100 % de l'engagement. Aucun sacrifice appliqué.**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| US-120 : séquence PunchOut | Non captée (2 séquences sur 3 exportées) | Le plugin exige un retour HTTPS, la démo locale est en HTTP | Repli de Thibaut documenté dans le kit ; PunchOut décalé en semaine 5 du calendrier |
| Relecture et publication des contenus | En attente | Actions de Thibaut | Post 1 et profil avant le 2026-10-26 |
| Validation des séquences du kit (T-120-09) | À faire | Tâche de Thibaut | À Thibaut |
| Bouton du menu compte Sylius sans nom accessible | Constat | Dette du cœur Sylius, hors sprint | Suivre, hors sprint |

## Détail par story

### Parcours guidés et page « Intégrer » (US-105, US-106, US-107)
- Moteur de visite par étapes, contenu par persona et écran de sortie vers le contact ; page « Intégrer ces plugins dans votre projet ».
- Constat corrigé : débordement mobile de la barre de démo lorsque `DEMO_CONTACT_URL` est renseignée.

### Mesure par campagne (US-117)
- Visites par campagne sans cookie via Umami.
- Constat corrigé : le paramètre `data-exclude-search` d'Umami supprimait les UTM, donc la mesure par campagne.

### Communication (US-116, US-122, US-123, US-127, US-129)
- Charte, calendrier sur huit semaines, trois posts de suite, suivi de J+1 à J+30 et texte du profil rédigés, hors dépôt public. Aucun contenu de prospect dans le dépôt.

### Kit média (US-120)
- Séquences devis et commande rapide captées et exportées ; PunchOut en repli.

## Vérification finale (Scrum Master)

- CI de la PR #42 : verte au commit 642a757 (Quality and smoke tests, Docker image, Image scan and SBOM, Dependency audit). Deux incidents corrigés en route : CVE-2026-98621 (CRITICAL) dans `linux-libc-dev` de l’image de base, mise à niveau ciblée (413a525) ; job de tests bloqué 3 h 25 par le test SIGTERM du verrou (dash diffère le trap tant que l’enfant au premier plan vit), PHPUnit lancé en arrière-plan avec `wait` (18312a6) et `timeout-minutes` sur chaque job (642a757).
- Suite complète : 1 424 tests verts, en ordre aléatoire sur trois graines (avant correctif, la graine 2 faisait échouer 2 tests de `PagesTest`). ECS, PHPStan, lint Twig (85 fichiers), YAML (117) et conteneur : OK. pa11y WCAG2AAA : 0 erreur sur les nouveaux gabarits (`/integrer`, parcours) en FR et EN ; une erreur Sylius hors sprint (menu compte). `bin/measure-mobile` : 23 pages OK en FR et EN, avec et sans `DEMO_CONTACT_URL`.
- PHPStan était rouge à l'intégration (trait `RestoresDemoCarts`) : corrigé par l'orchestrateur.
- Revue indépendante (symfony-reviewer, partielle) : aucun bloquant. Majeur corrigé : un lancement de parcours « panier » sans ligne ajoutable vidait le panier du visiteur (`CartScenarioPreparer`, test `CartScenarioPreparerTest`). Écarté : script Umami inline sans nonce (la CSP autorise `unsafe-inline`). Dette mineure pour le sprint 18 : reprise non atomique d’un verrou périmé de `bin/run-tests` (course entre deux runs), écriture en session sur le GET d’une étape de parcours, nom et rôle du compte de démo affichés aux anonymes (comptes déjà publics sur la page de connexion).

## Incidents du sprint

- Disque sous le seuil de 5 Go : tests bloqués plusieurs heures, espace libéré par Thibaut.
- Un worker est resté bloqué à attendre un run déjà terminé : arrêté et remplacé.
- Deux workers ont partagé le même dossier scratchpad : fichiers écrasés.
- Deux smoke tests en échec n'ont pas été reproduits ; une vraie dépendance d'ordre a été trouvée dans `DemoResetCommandTest`.
- PHPStan rouge à l'intégration (trait `RestoresDemoCarts`), corrigé par l'orchestrateur.
- CI bloquée 3 h 25 sur le test SIGTERM du verrou (comportement de dash, invisible sous macOS) : corrigé et borné par `timeout-minutes`.
- Nouvelle CVE critique de l’image de base publiée pendant le sprint : mise à niveau ciblée de `linux-libc-dev`.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 38 | sprint 16 : 27 (+11) |
| Points en review | 38 (dont 5 avec réserve) | sprint 16 : 27 |
| Taux de complétion | 10 stories sur 10 | → (100 %) |
| Tests | 1 424 tests verts (3 graines aléatoires) | sprint 16 : 1 306 tests, 8 251 assertions |
| Commits | 6 (5 de réalisation) | sprint 16 : 7 |
| Erreurs pa11y AAA | 0 sur les nouveaux gabarits (1 erreur Sylius hors sprint) | sprint 16 : 0 sur 7 pages |
| Sacrifices appliqués | 0 | - |

Note : 38 points, au-dessus du sprint 16 (27) et de la vélocité historique (10-15) ; une partie du volume est rédactionnelle.

## Démonstration (ordre suggéré)

1. **US-105 / US-106** (~15 min) : un parcours guidé par persona, de bout en bout, jusqu'à l'écran de sortie vers le contact.
2. **US-107** (~8 min) : page « Intégrer ces plugins dans votre projet », FR et EN, mobile.
3. **US-117** (~7 min) : visite avec UTM, lecture par campagne dans Umami, sans cookie.
4. **Communication** (~15 min) : charte, calendrier, post 1 et profil (relecture de Thibaut) ; kit média (devis, commande rapide).

```gherkin
Given la démo avec DEMO_CONTACT_URL renseignée
When je suis un parcours guidé jusqu'à son dernier écran
Then je vois la sortie vers le contact avec ses UTM

Given Umami configuré
When j'ouvre la démo avec des UTM de campagne
Then la visite est comptée pour la campagne, sans cookie
```

## Feedback à collecter

1. Les parcours guidés racontent-ils bien le cas de chaque persona ?
2. La page « Intégrer » donne-t-elle envie de contacter Thibaut ?
3. Posts, profil et calendrier : ton, cadence A ou B, à corriger ?
4. PunchOut : repli de Thibaut en semaine 5, ou autre option ?

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
| Décalée | US-120 (séquence PunchOut) | Captation par repli de Thibaut, semaine 5 du calendrier |
| Ajoutée au suivi | Menu compte Sylius sans nom accessible | Dette, hors sprint |
| Conservée | US-146 | Sprint 19 |

## Prochaines étapes

1. Thibaut relit et publie le post 1 et met à jour le profil avant le 2026-10-26.
2. Thibaut valide le kit média et fournit la séquence PunchOut (repli).
3. Compléter la section vérification quand la CI de la PR #42 est terminée.
4. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
