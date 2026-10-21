# Sprint Review - Sprint 18 (Repositionnement)

> Préparée le 2026-10-21 à partir de la PR #43 (mergée en squash, 9a4c461), des PR des plugins, de `.bmad/sprint-status.yaml` et des tâches du sprint. Les livrables de communication sont hors dépôt (`communication/sprint-18/`, répertoriés dans `communication/INDEX.md`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-03-05 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-18-repositionnement` / PR #43 (mergée le 2026-10-21, 9a4c461) |
| Commits du sprint | 7 sur la branche de la démo (23698f5, 558bf91, 8526bae, 7de1789, 586e2bb, 1055d17, 9213e64), squashés en 9a4c461 |
| PR de plugins | 24 PR README (US-068), 5 PR de limiteurs de débit (US-148), purchase-approval #5 (test des onglets) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude, un worker à la fois)

## Sprint Goal

> « Aligner la démo, les plugins et le contenu LinkedIn sur la recherche d'emploi de Thibaut (appel à l'action vers le profil, guide technique, preuve de qualité), passer la démo en PHP 8.5 après la faille critique du 2026-10-21 et solder les dettes et les actions de rétro du sprint 17. »

**Atteint : ⚠️ PARTIELLEMENT.** L'alignement sur la recherche d'emploi est livré : CTA vers le profil, guide technique, page de preuve, README des 24 plugins et posts rédigés. Les dettes du sprint 17 sont soldées côté code. Le passage en PHP 8.5 n'est pas fait : à la clôture, le correctif de sécurité n'était pas publié (PHP 8.5.11, image `dunglas/frankenphp:1-php8.5-bookworm` non corrigée).

Justification : 7 stories (25 points) sont en review, 2 stories (8 points) restent en cours et US-147 (3 points) est sortie du sprint. Il n'y a pas eu de sacrifice dans l'ordre prévu : US-147 sort parce qu'une dépendance externe n'était pas disponible, pas faute de capacité.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-158 | Repositionnement de la démo : profil LinkedIn, libellés neutres, guide technique | 3 | ✅ | 👀 En review (rendu réel, pa11y, `measure-mobile` ; `DEMO_PROFILE_URL` sur Railway par Thibaut) |
| US-148 | Plugins : limiteurs de débit typés `RateLimiterFactoryInterface` (Symfony 8) | 3 | ✅ | 👀 En review (test d'autowiring surchargé non écrit) |
| US-160 | Dettes du sprint 17 : verrou atomique, GET sans écriture de session, menu compte nommé | 5 | ✅ | 👀 En review (pa11y et `measure-mobile` non rejoués) |
| US-111 | Bloc de preuve technique : qualité, compatibilité, versions | 3 | ✅ | 👀 En review (rendu réel, pa11y, `measure-mobile`) |
| US-068 | Liens vers la démo et le profil de l'auteur dans les 24 README | 3 | Hors démo | 👀 En review (`--check` sur les 24 branches principales) |
| US-159 | Contenu LinkedIn réaligné sur la recherche d'emploi : calendrier et posts de suite | 3 | Hors démo | 👀 En review (relecture et publication par Thibaut) |
| US-124 | Posts LinkedIn rédigés, lot 2 : semaines 3 à 8 | 5 | Hors démo | 👀 En review (relectures et chiffres des semaines 1 et 2 par Thibaut) |
| US-161 | Actions de la rétrospective du sprint 17 | 5 | Hors démo | 🔄 En cours (checklist d'intégration, gabarit TaskStop, validation du registre) |
| US-103 | Assistants IA : page utile avec transcript d'une vraie conversation | 3 | Partielle | 🔄 En cours (conversation réelle de Thibaut, T-103-03) |
| US-147 | Démo en PHP 8.5 (image, CI et plateforme Composer) | 3 | - | ↪️ Sortie du sprint |

**Livré : 25 points en review (7 stories) sur 36 engagés, soit 69 %. 8 points en cours, 3 points sortis du sprint.**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| US-147 : démo en PHP 8.5 | Veille horaire active, aucun code | Correctif de sécurité PHP 8.5 non publié à la clôture | PR séparée dès l'image FrankenPHP corrigée, épinglée par digest ; sinon sprint 19 |
| US-103 : transcript réel | Bloc transcript livré, vide de vraie conversation | Action de Thibaut (T-103-03) | Thibaut enregistre la conversation sur le MCP de production |
| US-161 : checklist d'intégration (T-161-06), gabarit TaskStop (T-161-09) | À faire | Temps absorbé par la crise disque | Reprise en action de la rétro du sprint 18 |
| Vérifications de rendu (pa11y, `bin/measure-mobile`, rendu réel) | Non rejouées | VM OrbStack saturée puis arrêtée pendant le sprint | À rejouer avant le passage en `done` des stories concernées |
| `DEMO_PROFILE_URL` sur Railway | À faire | Le dépôt local n'a pas de service lié (`railway link`) | Thibaut lie le service, pose `DEMO_PROFILE_URL` puis retire `DEMO_BOOKING_URL` |
| Post 1 de suite | Rédigé | Action de Thibaut | Relecture et publication avant le 2026-10-26 |

## Détail par story

### Repositionnement de la démo (US-158)
- Les appels à l'action pointent vers `DEMO_PROFILE_URL` (profil LinkedIn), à la place du lien de prise de rendez-vous.
- `/integrer` devient un guide technique, sans formule ni délai, et la sortie des parcours est neutre.

### Limiteurs de débit des plugins (US-148)
- Cinq plugins typent leurs limiteurs par `RateLimiterFactoryInterface` et un test interdit le type concret dans `src/`.
- **Écart de périmètre** : la story ne prévoyait pas Symfony 6.4, encore supporté par quatre plugins. L'interface n'existe que depuis Symfony 7.3 : la cellule CI 6.4 est tombée en rouge. Décision de Thibaut le 2026-10-21 : abandon de Symfony 6.4, avec des versions mineures.
- Releases : punchout-cxml v1.3.0, quote-request v1.1.0, quick-order v1.3.0, command-palette v1.1.0, plus b2b-mcp v1.2.2 en patch (il ne supportait déjà pas 6.4). Packagist est vérifié.
- La démo est montée à ces cinq versions (586e2bb) et la suite complète est verte.

### Dettes du sprint 17 (US-160)
- Le verrou périmé de `bin/run-tests` est repris de façon atomique.
- Le GET d'une étape de parcours n'écrit plus la session : l'avancement passe par un POST avec jeton CSRF.
- Le menu du compte a un nom accessible.
- Deux tests dépendaient de l'état laissé par d'autres tests ; ils sont corrigés (7de1789) :
  - `DemoResetCommandTest` compare au nombre de commandes des fixtures (graine 1818) ;
  - `ContainerFillDemoTest` lève l'égalité de `createdAt` à la seconde sur le dernier panier (graine 2121).

### Preuve technique (US-111)
- Page de preuve alimentée par `config/demo/proof.json`, rafraîchi par `bin/proof-refresh` (1055d17) : toutes les CI des plugins sont vertes et les compteurs de releases incluent les nouvelles versions.

### README des plugins (US-068)
- 24 PR `docs/readme-demo-profile` fusionnées en squash, sur le GO de Thibaut.
- purchase-approval #5 (test des onglets, rouge depuis company-accounts 1.2.0) a été fusionnée avant #4.

### Communication (US-159, US-124)
- Calendrier réaligné et posts de suite, lot 2 sur six semaines : tout est rédigé hors dépôt public et attend la relecture de Thibaut.

### Assistants IA (US-103)
- Chargement et contrôle des transcripts (refus des lignes sensibles) livrés. Les jetons fictifs des tests sont découpés pour ne plus déclencher GitGuardian (9213e64).

## Vérification finale (Scrum Master)

- CI de la PR #43 :
  - les checks obligatoires sont verts : Quality and smoke tests, Docker image ;
  - Dependency audit et Image scan and SBOM sont verts aussi ;
  - premier run rouge sur ECS (guillemets dans `StandardBlock.php`), corrigé en 9213e64 ;
  - GitGuardian rouge sur deux faux positifs (jetons fictifs de `TranscriptLoaderTest`) ; les jetons sont découpés et Thibaut a marqué les deux incidents comme faux positifs.
- Suite complète : 1 633 tests verts, aussi en ordre aléatoire sur les graines 1818, 2121 et 3131. Une première exécution de la graine 3131 était invalide : le disque de la VM OrbStack était plein, puis OrbStack s'est arrêté.
- ECS et PHPStan : OK en local sur `src` et `tests`, et en CI.
- `lint:container` : OK après la montée des plugins.
- pa11y, `bin/measure-mobile`, rendu réel et `/healthz` local : **non rejoués** à la clôture.

## Incidents du sprint

- Saturation de la RAM puis swap sur le même conteneur APFS : l'espace libre est descendu de 8 Go à 1,4 Go, avec deux redémarrages de la machine.
- Disque de la VM OrbStack plein pendant la graine 3131 : 79 « No space left on device », puis OrbStack s'est arrêté (838 « Connection refused »). Run invalidé, puis rejoué après la relance d'OrbStack par Thibaut.
- Le classifieur refuse `gh pr merge` à Claude : Thibaut lance les merges avec `!`. Une commande collée, coupée sur deux lignes, a échoué ; un script du scratchpad l'a remplacée.
- Symfony 6.4 absent du cadrage d'US-148 : quatre PR de plugins sont tombées en rouge.
- ECS non rejoué avant le push : un run de CI rouge de plus.
- `cache:clear` lancé par composer en échec de mémoire (PHP local à 128 Mo) : relancé avec 1 Go, sans effet sur le code.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 36 | sprint 17 : 38 (-2) |
| Points en review | 25 | sprint 17 : 38 |
| Taux de complétion | 7 stories sur 10 en review | ↘ (100 % au sprint 17) |
| Tests | 1 633 tests verts (3 graines aléatoires) | sprint 17 : 1 424 (+209) |
| Commits | 7 sur la démo, 30 PR de plugins | sprint 17 : 6 |
| Releases de plugins | 5 (4 mineures, 1 patch) | - |
| Erreurs pa11y AAA | Non mesuré à la clôture | sprint 17 : 0 sur les nouveaux gabarits |
| Sacrifices appliqués | 0 (US-147 sortie pour dépendance externe) | - |

## Démonstration (ordre suggéré)

1. **US-158** (~8 min) : CTA vers le profil, `/integrer` en guide technique, sortie neutre d'un parcours, en FR et EN.
2. **US-111** (~7 min) : page de preuve, avec ses sources (runs de CI, Packagist).
3. **US-148** (~5 min) : releases des plugins et note de changement Symfony 6.4.
4. **US-068** (~3 min) : bloc « Démo B2B » d'un README de plugin.
5. **Communication** (~15 min) : calendrier et posts de suite, à relire par Thibaut.

```gherkin
Given la démo avec DEMO_PROFILE_URL renseignée
When je termine un parcours guidé
Then le dernier écran propose le profil de l'auteur, sans libellé commercial

Given la page de preuve
When je l'ouvre
Then chaque chiffre affiché renvoie à sa source (run de CI ou Packagist)
```

## Feedback à collecter

1. Les CTA vers le profil et le guide technique servent-ils la recherche d'emploi ?
2. La page de preuve est-elle lisible par un recruteur non technique ?
3. Posts de suite : ton, cadence A ou B, à corriger ?
4. Abandon de Symfony 6.4 : faut-il l'annoncer dans les README ?

## Notes de session

Feedback reçu :
- {à remplir}

Nouvelles demandes :
- {à remplir}

Décisions prises :
- 2026-10-21 : abandon de Symfony 6.4 dans quatre plugins, avec des versions mineures (Thibaut).
- 2026-10-21 : merge du sprint sans attendre le correctif PHP ; US-147 dans une PR séparée (Thibaut).

## Impact sur le backlog

| Action | Élément | Description |
|--------|---------|-------------|
| Sortie du sprint | US-147 | PR séparée dès l'image FrankenPHP corrigée, sinon sprint 19 |
| Reportée | US-161 (T-161-06, T-161-09) | Reprise par la rétro du sprint 18 |
| En attente de Thibaut | US-103 (T-103-03) | Conversation réelle sur le MCP de production |
| Idée | Fiches Sylius Marketplace | Thibaut a un compte vendeur : story à cadrer pour un prochain sprint, sans les plugins qui recoupent Sylius Plus |

## Prochaines étapes

1. Thibaut lie le service Railway (`railway link`), pose `DEMO_PROFILE_URL`, puis retire `DEMO_BOOKING_URL`.
2. Thibaut relit et publie le post 1 avant le 2026-10-26.
3. Claude livre US-147 dès la sortie du correctif (veille horaire).
4. Rejouer pa11y, `bin/measure-mobile`, le rendu réel et `/healthz` avant de passer les stories en `done`.
5. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
