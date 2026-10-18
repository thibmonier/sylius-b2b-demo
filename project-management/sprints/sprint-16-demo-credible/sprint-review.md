# Sprint Review - Sprint 16 (Démo crédible)

> Préparée le 2026-10-19 à partir de `git log origin/main..HEAD` (7 commits), de la PR #30 (ouverte), de `.bmad/sprint-status.yaml` et des tâches du sprint. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2027-02-05 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-16-demo-credible` / PR #30 (ouverte) |
| Commits du sprint | 7 (dont 2 de planification et backlog ; 2026-10-18 et 2026-10-19) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude en parallèle et un relecteur indépendant)

## Sprint Goal

> « Rendre la démo crédible avant de la montrer : images produit persistantes sur Railway, photos cohérentes avec les produits et crédits visibles, données vivantes (panier, historique, approbation, devis, crédit, journal d'audit), appel à l'action « Me contacter » partout et mesure d'audience sans cookie. »

**Atteint : ✅ OUI côté code et tests ; restent les actions de Thibaut (application du volume Railway, URL Calendly, formulaire et Umami, relecture des photos).**

Justification : les 7 stories décidées (« démo d'abord », décision D1) sont en revue, sans sacrifice. Les boutons et Umami sont vérifiés avec des URL factices, les processus et médias tournent en `www-data`, la remise à zéro dure 36-37 s. Le volume, les URL et la relecture des photos dépendent de Thibaut.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-145 | Images produit persistantes sur Railway | 3 | ✅ | ✅ En review (application par Thibaut, T-145-08) |
| US-142 | Appel à l'action « Me contacter » (Calendly et formulaire) | 5 | ✅ | ✅ En review (URL de Thibaut) |
| US-143 | Mesure d'audience Umami sans cookie | 3 | ✅ | ✅ En review (URL et identifiant de Thibaut) |
| US-092 | Catalogue crédible, tranche 1 : photos cohérentes | 5 | ✅ | ✅ En review (relecture de Thibaut, T-092-10) |
| US-096 | Données vivantes (1/2) : panier, historique, approbation | 5 | ✅ | ✅ En review |
| US-097 | Données vivantes (2/2) : devis, crédit, brouillons, suggestions IA, audit | 5 | ✅ | ✅ En review |
| US-061 | Crédits des images de démo | 1 | ✅ | ✅ En review |

**Livré : 27 points en review (7 stories), soit 100 % de l'engagement. Aucun sacrifice.**

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| US-052 (ouverture publique, 3 pts) | Reportée | Tâches OPS de Thibaut, hors capacité, sur GO explicite | À Thibaut |
| Application du volume Railway | Non faite | `railway config plan` puis `apply`, remise à zéro, deux redéploiements | À Thibaut |
| URL Calendly, formulaire, Umami | Non fournies | Boutons masqués et mesure inactive tant que les variables sont vides (D3) | À Thibaut |
| N+1 du widget de panier | Documenté | Code du cœur Sylius, hors dépôt | Suivre côté Sylius |
| US-146 (adresses connues à la commande admin, 5 pts) | Backlog | Enhancement du plugin admin-order-creation demandé par Thibaut | Sprint 17 |

## Détail par story

### Images et crédits (US-145, US-092, US-061)
- Volume Railway `media` sur `/app/public/media` ; l'entrypoint démarre en root, rend les droits des médias puis passe en `www-data`. La CI vérifie l'utilisateur des processus.
- 210 photos relues : 137 validées, 73 visuels neutres CC0 ; auditeur du manifeste. Page des crédits des images.

### Contact et audience (US-142, US-143)
- Composants « Prendre rendez-vous » (`DEMO_BOOKING_URL`) et « Écrire un message » (`DEMO_CONTACT_URL`) avec UTM et événements Umami, sur la barre de démo, l'accueil, les fiches, Plugins, À propos, MCP, le simulateur et le pied de page ; masqués sans URL.
- Umami sans cookie, liste blanche des pages mesurées, sans paramètres d'URL, CSP élargie seulement si configuré ; mentions légales.

### Données vivantes (US-096, US-097)
- Panier de départ, historique ACME avec approbation en attente, devis en négociation, crédit avec pièce échue, brouillons, suggestions IA, journal d'audit métier.

## Vérification finale (Scrum Master)

- Suite complète : 1 306 tests, 8 251 assertions, 0 échec (sur une base neuve, lancée seule).
- ECS, PHPStan, lint Twig (85) et YAML (117) verts.
- pa11y WCAG2AAA : 0 erreur sur 7 pages ; `bin/measure-mobile` vert ; remise à zéro en 36-37 s.
- Revue indépendante : 0 bloquant, 2 majeurs corrigés (Umami mesurait les pages à jeton, donc fuite de jeton vers un tiers ; mentions légales inexactes) par une liste blanche ; mineurs corrigés (crédits, titres traduits, contexte des liens répétés, chown conditionnel, contrôle CI de l'utilisateur, budget de requêtes).

## Incidents du sprint

- Le correctif de revue « chown conditionnel » a empêché le conteneur de démarrer (sous-dossiers `image/cache` créés par root) : détecté à la reconstruction, corrigé avec un test ; la CI l'aurait bloqué.
- Une boucle d'attente (`pgrep -f "bin/run-tests"`) se détectait elle-même : ~15 min perdues.
- Deux suites complètes et deux remises à zéro simultanées sur la même base : 23 échecs et résultats invalides ; relancée seule sur base neuve : 0 échec.
- Deux tests de données vivantes dépendaient de l'ordre (panier de Claire modifié par un autre test) : assouplis ; la cause (un test modifie un compte de démo partagé sans annuler) demeure.
- Un développeur a lancé ses tests en parallèle malgré la consigne ; un autre a eu une collision de fixtures ; des `rm -i` bloqués ont été laissés (sans effet).

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 27 | sprint 15 : 35 (+1) |
| Points en review | 27 | sprint 15 : 36 |
| Taux de complétion | 7 stories sur 7 | → (100 %) |
| Tests | 1 306 tests, 8 251 assertions, 0 échec | sprint 15 : 1 166 tests |
| Commits | 7 (5 de réalisation) | sprint 15 : 11 |
| Erreurs pa11y AAA | 0 sur 7 pages | 5 pages au sprint 15 |
| Remise à zéro | 36-37 s | - |

Note : 27 points, sous le sprint 15 (36) mais au-dessus de la vélocité historique (10-15) ; aucun sacrifice.

## Démonstration (ordre suggéré)

1. **US-142 / US-143** (~10 min) : boutons « Me contacter » sur les pages clés, événements Umami, mentions légales.
2. **US-096 / US-097** (~15 min) : panier de départ, approbation en attente, devis, crédit échu, suggestions IA, journal d'audit.
3. **US-092 / US-061** (~8 min) : photos cohérentes, page des crédits.
4. **US-145** (~5 min) : volume `media`, droits `www-data`, redéploiement sans perte d'images (après application par Thibaut).

```gherkin
Given la démo avec DEMO_BOOKING_URL et DEMO_CONTACT_URL renseignées
When j'ouvre la fiche d'un produit
Then je vois « Prendre rendez-vous » et « Écrire un message » avec leurs UTM

Given Umami configuré
When j'ouvre la page de réinitialisation de mot de passe
Then aucune mesure n'est envoyée et le jeton ne sort pas du site
```

## Feedback à collecter

1. Les boutons « Me contacter » sont-ils assez visibles sans gêner la démo ?
2. Les données vivantes (approbation, devis, crédit) racontent-elles bien un cas B2B ?
3. Les photos relues sont-elles acceptables ? Lesquelles remplacer ?
4. Sprint 17 : US-146 d'abord ? Que faire du sprint 19 à 54 points ?

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
| Ajoutée | US-146 | Adresses connues à la création de commande admin (5 pts, plugin admin-order-creation) |
| Conservée | US-052 | Tâches OPS de Thibaut, sur GO |
| Décalée | Contenu LinkedIn et site | Sprints 17-18 |
| À arbitrer | Sprint 19 | 54 points, à réduire ou découper |
| Ajoutée au suivi | N+1 du widget de panier | Cœur Sylius, documenté |

## Prochaines étapes

1. Thibaut applique le volume (`railway config plan` puis `apply`), une remise à zéro, deux redéploiements.
2. Thibaut renseigne les URL Calendly, formulaire et Umami, et relit les photos.
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
