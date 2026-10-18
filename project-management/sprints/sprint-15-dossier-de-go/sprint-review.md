# Sprint Review - Sprint 15 (Dossier de GO)

> Préparée le 2026-10-18 à partir de `git log origin/main..HEAD` (11 commits), de la PR #27 (ouverte : 128 fichiers, +4 930 / -270), de `.bmad/sprint-status.yaml`, des tâches du sprint et de `docs/dossier-de-go.md`. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-01-22 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-15-dossier-de-go` / PR #27 (ouverte) |
| Commits du sprint | 11 (tous le 2026-10-18) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude en parallèle et un relecteur indépendant)

## Sprint Goal

> « Fermer la sécurité et la performance de la démo, afficher les prix négociés B2B (paliers et prix de la société), mettre à jour les plugins et les hôtes de confiance, et remettre à Thibaut un dossier de GO complet pour décider l'ouverture publique. »

**Atteint : ✅ OUI côté code, tests et dossier ; reste la décision de Thibaut (GO) et US-052 (tâches OPS), ainsi que des prérequis d'ouverture (volume pour les images produit).**

Justification : les 12 stories de dev sont livrées en revue, sans sacrifice (périmètre complet décidé par Thibaut). Les mesures confirment l'objectif : performance mobile Lighthouse 74-76 passée à 97-99, LCP de 6,9-7,5 s à 2,1-2,2 s, pa11y AAA à 0 erreur sur 5 pages, suite complète sans échec. Le dossier de GO existe (`docs/dossier-de-go.md`).

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-139 | Plugins à jour dans la démo | 2 | ✅ | ✅ En review |
| US-140 | Hôtes de confiance indépendants du domaine public Railway | 1 | ✅ | ✅ En review (application par Thibaut, T-140-06) |
| US-141 | Nettoyage des caches de test | 1 | ✅ | ✅ En review |
| US-144 | Bandeau du haut sans offre newsletter (ajoutée en cours de sprint) | 1 | ✅ | ✅ En review |
| US-078 | SEO de base et vitesse d'affichage mobile | 5 | ✅ | ✅ En review |
| US-081 | Chaîne d'approvisionnement : scan d'image, SBOM, empreintes | 5 | ✅ | ✅ En review |
| US-082 | Durcissement applicatif | 5 | ✅ | ✅ En review |
| US-083 | Relire la licence du plugin MCP tiers | 1 | ✅ | ✅ En review (MIT retenue) |
| US-085 | Revue de sécurité de réouverture et dossier de GO | 2 | ✅ | ✅ En review |
| US-089 | Accueil connecté sans doublon | 5 | ✅ | ✅ En review |
| US-090 | Paliers de prix et prix négocié sur la fiche produit | 5 | ✅ | ✅ En review |
| US-091 | Paliers et prix négocié au catalogue et au panier | 3 | ✅ | ✅ En review |

**Livré : 36 points en review (12 stories de dev). Engagement initial : 35 points ; US-144 (1 point) ajoutée en cours de sprint à la demande de Thibaut.**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| US-052 (ouverture publique, 3 pts) | En cours | Tâches OPS de Thibaut (T-052-03 à T-052-07), sur GO explicite après le dossier | À Thibaut |
| Volume pour `public/media` | Non fait | Images produit perdues après des redéploiements sans remise à zéro ; prérequis bloquant du GO | À traiter avant l'ouverture (voir dossier de GO) |
| doctrine/orm 3.7 | Impossible | Conflit avec Sylius 2.3.1 | Reprendre à la prochaine version de Sylius |
| US-142 et US-143 | Backlog | Ajoutées pendant le sprint : appel à l'action contact + Calendly ; mesure d'audience Umami suivie dans le site thibaut-monier.fr (plan gratuit limité à un site) | À planifier |

## Détail par story

### Prix B2B et accueil (US-090, US-091, US-089)
- Paliers du plugin tier-pricing et prix négocié de la société de démonstration (« votre prix négocié ») sur la fiche, au catalogue et au panier ; société de démo = groupe de clients, aucun nouveau plugin (D2).
- Accueil connecté sans doublon : références habituelles, réassort, promotions négociées.
- Écart de prix PunchOut (requête sans session) résolu par `group_prices_for_guests` ; risque accepté.

### Performance et SEO (US-078, US-144)
- Meta description, canonical, affichage mobile. Cause du LCP : 723 Ko de polices Inter préchargées, réduites à 66 Ko. Lighthouse mobile performance 74-76 → 97-99, LCP 6,9-7,5 s → 2,1-2,2 s.
- Bandeau du haut sans offre newsletter du cœur.

### Sécurité et chaîne d'approvisionnement (US-082, US-081, US-083, US-085)
- Durcissement : en-têtes, fichiers servis, formulaire du simulateur, correctifs de dépendances.
- Scan d'image, SBOM, empreintes ; `bin/audit-js-dependencies` corrigé en revue (il passait sans auditer si yarn plantait).
- Licence MIT du plugin MCP retenue (décision de Thibaut).
- Dossier de GO remis : `docs/dossier-de-go.md`.

### Plateforme et outillage (US-139, US-140, US-141)
- Plugins à jour (quick-order, punchout), surcharge temporaire retirée.
- Hôtes de confiance indépendants de `RAILWAY_PUBLIC_DOMAIN` (incident du 2026-10-18).
- `bin/clean-test-caches` créé (action de rétro du sprint 14).

## Vérification finale (Scrum Master)

- Suite complète : 1 166 tests, 9 979 assertions, 0 échec.
- ECS, PHPStan, lint Twig (81) et YAML (115) verts.
- pa11y WCAG2AAA : 0 erreur sur 5 pages ; `bin/measure-mobile` vert.
- Revue indépendante : 0 bloquant, 1 majeur (audit JS) et 7 mineurs, tous corrigés ; 2 échecs de la suite complète corrigés (`ThemeHooksTest` après US-144, `TrustedProxiesTest` après le canonical).

## Incidents du sprint

- 2026-10-18 : ajout du domaine personnalisé `sylius-b2b.thibaut-monier.fr`, `RAILWAY_PUBLIC_DOMAIN` modifié, `TRUSTED_HOSTS` de l'IaC refuse le domaine Railway (erreur 400). Corrigé à la main par Thibaut puis dans l'IaC (US-140).
- TXT de vérification Railway mal nommé chez Hostinger (corrigé) ; certificat Let's Encrypt émis.
- Images produit perdues après des redéploiements sans remise à zéro (aucun volume pour `public/media`).
- Disque de nouveau saturé (2 Go) : gel des builds et tests décrété par l'orchestrateur ; deux développeurs ont lancé phpunit directement pour contourner `bin/run-tests` (écart de consigne).
- Rapports de fin encore tronqués (3 relances).
- Conflit sur un test PunchOut (échec chez l'un, succès chez l'autre : exécution antérieure au correctif), tranché par l'orchestrateur.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 35 (+1 ajouté) | sprint 14 : 32 |
| Points en review | 36 | sprint 14 : 32 |
| Taux de complétion | 12 stories de dev sur 12 | → (100 %) |
| Tests | 1 166 tests, 9 979 assertions, 0 échec | sprint 14 : 1 056 tests |
| Commits | 11 | sprint 14 : 7 |
| Erreurs pa11y AAA | 0 sur 5 pages | → |
| Lighthouse mobile (performance) | 97-99 | 74-76 avant |
| LCP mobile | 2,1-2,2 s | 6,9-7,5 s avant |

Note : 36 points, deuxième sprint consécutif au-dessus de la vélocité historique (10-15), tenu avec des développeurs en parallèle ; aucun sacrifice.

## Démonstration (ordre suggéré)

1. **US-090 / US-091 / US-089** (~12 min) : paliers et prix négocié ACME sur fiche, catalogue, panier ; accueil connecté.
2. **US-078 / US-144** (~6 min) : Lighthouse avant/après, bandeau sans newsletter.
3. **US-082 / US-081 / US-083** (~8 min) : en-têtes, scan d'image, SBOM, licence.
4. **US-140 / US-139 / US-141** (~4 min) : domaine personnalisé, plugins à jour, nettoyage des caches.
5. **US-085** (~10 min) : lecture du dossier de GO, risques résiduels, prérequis bloquant (volume des images).

```gherkin
Given un acheteur de la société de démonstration connecté
When il ouvre une fiche produit
Then il voit le tableau des paliers et « votre prix négocié »

Given la démo sur sylius-b2b.thibaut-monier.fr
When le domaine Railway et le domaine personnalisé sont appelés
Then les deux répondent sans erreur 400
```

## Feedback à collecter

1. Le dossier de GO est-il suffisant pour décider ? Quels risques résiduels refuse-t-on ?
2. Volume pour `public/media` : à faire avant le GO ?
3. Priorité d'US-142 (contact + Calendly) et d'US-143 (Umami).
4. Le prix négocié par groupe de clients est-il assez parlant pour la démo ?

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
| Ajoutée et faite | US-144 | Bandeau du haut sans offre newsletter |
| Ajoutée | US-142 | Appel à l'action contact + Calendly |
| Ajoutée | US-143 | Mesure d'audience Umami (suivie dans le site thibaut-monier.fr) |
| Conservée | US-052 | Tâches OPS de Thibaut, sur GO |
| Ajoutée au suivi | Volume `public/media` | Prérequis bloquant du GO |
| Ajoutée au suivi | doctrine/orm 3.7 | Bloqué par Sylius 2.3.1 |

## Prochaines étapes

1. Thibaut décide le GO à partir de `docs/dossier-de-go.md`.
2. Volume pour `public/media`, puis tâches OPS d'US-052.
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
