# Sprint Review - Sprint 10 (Vitrine et reports)

> Préparée le 2026-10-17 à partir de `git log main..HEAD` (19 commits), de la PR #19 (brouillon, ouverte), de `.bmad/sprint-status.yaml`, des tâches du sprint et des documents `docs/remise-a-zero-demo.md`, `docs/images-produits.md`, `docs/webhooks-demo.md`, `docs/durcissement-demo.md`. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-11-13 |
| Durée | 1 h |
| Branche / PR | `feature/sprint-10-vitrine-et-reports` / PR #19 (brouillon) |
| Commits du sprint | 19 (tous le 2026-10-17) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (workers Claude)

## Sprint Goal

> « Soigner la vitrine de la démo (images des produits, encart Sylius Plus, récepteur de webhooks) et solder les reports techniques du sprint 9. »

**Atteint : ⚠️ PARTIELLEMENT (4 stories sur 4 en review, deux objectifs ou tâches non soldés)**

Justification : les quatre stories sont livrées et vérifiées (base neuve via `bin/prepare-test-db`, 765 tests, 0 échec, ECS et PHPStan propres). Restent hors périmètre livré : l'objectif `demo_orders` ≤ 1 750 requêtes (1 911 obtenues, cœur Sylius), les crédits des images (T-047-09, sacrifiée) et deux relevés Railway qui dépendent du déploiement (T-048-11 partielle, T-048-13).

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-029 | Récepteur de webhooks de démo | 3 | ✅ | ✅ En review |
| US-046 | Encart Sylius Plus sur la page Plugins | 2 | ✅ | ✅ En review (texte validé tel quel par l'utilisateur) |
| US-047 | Images des produits de démo | 5 | ✅ | ✅ En review (T-047-09 sacrifiée) |
| US-048 | Reports techniques du sprint 9 | 3 | ✅ | ⚠️ En review (T-048-11 partielle, T-048-13 après déploiement) |

**Livré : 13/13 points en review (100 %)**, sous réserve de l'acceptation du PO. Hors sprint : US-045 (1 pt, faite le 2026-10-17 sur demande), PR #15, #17 et #18 mergées hors sprint.

## Éléments non terminés ou écartés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| T-047-09 Crédits des images | Sacrifiée | Ordre de sacrifice n°3, décision utilisateur ; le manifeste (CC0) suffit | Backlog |
| T-048-11 Relevé de coût Railway | Partiel | 1,62 $ consommés, 4,75 $ estimés, RAM 1,4 à 2,77 Go ; relevé complet attendu | Après le 2026-10-23, GO utilisateur |
| T-048-13 Pic mémoire du reset | Non fait | Choix utilisateur : après le déploiement du sprint 10 | Sprint 11 |
| Objectif `demo_orders` ≤ 1 750 | Non atteint (1 911) | Coût dans le cœur Sylius | Décision à prendre en séance |

## Détail par story

### US-029 : Récepteur de webhooks de démo (`docs/webhooks-demo.md`)
- Récepteur de démo et page des webhooks reçus.
- Revue : le limiteur à clé unique est corrigé (clé par IP).
- Mineurs acceptés : `127.0.0.1` dans `trusted_hosts` (Railway route par `Host`), corps chunked sans `Content-Length`.

### US-046 : Encart Sylius Plus (page Plugins)
- Texte d'après la documentation officielle, aucune promesse de compatibilité, validé tel quel par l'utilisateur (T-046-04) avant merge.

### US-047 : Images des produits (`docs/images-produits.md`)
- Source Openverse, licence CC0, quota anonyme de 200 requêtes par jour, d'où une recherche par type d'article.
- Résultat : 74 images (2,5 Mo), 23 propres à un produit et 187 produits qui partagent l'image de leur famille, accepté par l'utilisateur. La pertinence CC0 reste faible pour du matériel industriel.
- 1 image hors sujet retirée (OUTI-012).
- Le SM avait d'abord jugé 5 images douteuses sur une planche : 4 étaient pertinentes après lecture du manifeste.
- Revue : aucun bloquant ; correctifs faits (validation de l'id, `.dockerignore`, tests du manifeste). Mineurs non faits : limite de pixels et `Content-Length`, gestion du 429, image principale conservée si le manifeste change.

### US-048 : Reports techniques (`docs/remise-a-zero-demo.md`, `docs/durcissement-demo.md`)
Mesures du profil publié avant correctifs :

| Poste | Avant | Après | Objectif |
|-------|-------|-------|----------|
| Reset total (requêtes) | 9 064 | 7 014 (≈ 6 840 à périmètre égal) | ≤ 7 000 : atteint à périmètre égal |
| `container_fill` | 2 205 | 31 | atteint |
| `demo_orders` | 1 963 | 1 911 | 1 750 : NON atteint |
| Wishlist | N+1 | constante | atteint |

- Corrigés : NFC, capacité périmée, entrypoint (LoadMarker).
- Pic mémoire local : 458 Mo (et non 1,08 Go).
- T-048-12 : le 404 de `/api/v2` vient de l'API désactivée hors dev ; elle reste désactivée (décision utilisateur). Le MCP n'en dépend pas : l'hypothèse « impacte MCP » du sprint 9 était fausse.

## Vérification finale (Scrum Master)

- Base neuve via `bin/prepare-test-db` : 765 tests, 0 échec.
- ECS et PHPStan propres.
- CI de la PR #19 : en cours (le contrôle GitGuardian est en échec, les jobs « Quality and smoke tests » et « Docker image » sont en attente au moment de la préparation) ; à confirmer avant la séance.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 13 | sprint 9 : 11 |
| Points en review | 13 | sprint 9 : 7 |
| Taux de complétion | 100 % (tâches : 1 sacrifiée, 2 en attente de Railway) | ↗️ |
| Tests | 765, 0 échec | - |
| Commits | 19 | sprint 9 : 13 |

## Démonstration (ordre suggéré)

1. **US-047** (~5 min) : catalogue illustré après `app:demo:reset`, aucune requête réseau vers la banque.
2. **US-046** (~2 min) : encart Sylius Plus sur la page Plugins.
3. **US-029** (~4 min) : envoi d'un webhook, page des webhooks reçus, limiteur par IP.
4. **US-048** (~5 min) : tableau de profilage du reset, avant et après.

```gherkin
Given la démo vient d'être remise à zéro
When j'ouvre le catalogue
Then chaque produit affiche une image CC0 de sa famille
And aucune requête n'est partie vers Openverse

Given j'envoie un webhook de test au récepteur
When j'ouvre la page des webhooks reçus
Then l'événement apparaît
```

## Feedback à collecter

1. Les images partagées par famille sont-elles acceptables pour une démo publique ?
2. Faut-il restaurer les crédits (T-047-09) malgré la licence CC0 ?
3. L'écart sur `demo_orders` (1 911 contre 1 750) justifie-t-il un travail dans le cœur Sylius ?
4. Priorités du sprint 11.

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
| À créer | Crédits des images | Reprise de T-047-09 |
| À créer | Mineurs du script d'images | Limite de pixels et `Content-Length`, gestion du 429, image principale conservée si le manifeste change |
| À décider | `demo_orders` | Objectif 1 750 non atteint, cœur Sylius |
| Reportée | T-048-11, T-048-13 | Relevés Railway après déploiement |

## Prochaines étapes

1. Confirmer le résultat de la CI de la PR #19, puis demander le GO de merge à l'utilisateur.
2. Déployer, puis réaliser T-048-13 et le relevé complet T-048-11.
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
