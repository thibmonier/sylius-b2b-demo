# Sprint Review - Sprint 9 (Reports)

> Préparée le 2026-10-17 à partir de `git log origin/main..HEAD` (13 commits), de la PR #16 (brouillon, ouverte), de `.bmad/sprint-status.yaml`, des tâches du sprint et d'ADR-0013. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-10-30 (matin) |
| Durée | 1 h |
| Branche / PR | `feature/sprint-9-reports` / PR #16 (brouillon) |
| Commits du sprint | 13 (4 le 2026-10-16, 9 le 2026-10-17) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (workers Claude)

## Sprint Goal

> « Faire vivre container-fill dans la démo et solder les reports techniques du sprint 8. »

**Atteint : ⚠️ PARTIELLEMENT**

- Container-fill vit dans la démo (US-044) : atteint.
- Reports techniques : requêtes des listes (T-041-08/09) et vérifications S2-R8..R10 (T-041-07) soldées ; US-006 tranchée par ADR. Deux écarts : T-041-06 n'atteint pas son objectif (reset −13 % au lieu de −50 %, tâche sacrifiable) et T-041-15 (coût Railway) reste ouverte, car elle ne peut pas avoir lieu avant le 2026-10-23.

## User Stories et tâches livrées

| ID | Titre | Points | Status | Résultat clé |
|----|-------|--------|--------|--------------|
| US-044 | Données container-fill | 5 | review | Presets du plugin chargés ; fixture `demo_container_fill` dérivée des CSV ; règles B2B seulement ; bouton de commande du panier masqué par les règles, condition PunchOut conservée |
| US-006 | Spike mode worker FrankenPHP | 2 | review | Threads bornés (8/16, pilotables par env) ; mode worker opt-in ; ADR-0013 : mode classique conservé |
| T-041-08/09 | Prix « dès » calculés en lot | (reports US-041) | fait | Requêtes des listes 59/164/213 → 25/25/25 (5/20/50 produits), hors Wishlist |
| T-041-07 | S2-R8..R10 | (reports US-041) | fait | Trois risques clos par des tests |
| T-041-06 | SELECT de l'import produits | (reports US-041) | fait, objectif manqué | SELECT 1666 → 308, mais reset total 10421 → 9063 requêtes (−13 %, objectif −50 %) |

**Engagement : 7 points de stories (US-006, US-044) + reports d'US-041, soit 52,5 h.** Les deux stories sont en `review` (pas encore `done` : CI et GO utilisateur en attente).

## Non terminé ou reporté

| ID | Titre | Points | Raison | Action |
|----|-------|--------|--------|--------|
| US-029 | Récepteur de webhooks de démo | 3 | Capacité (68 h pour ~52 h), arbitrage utilisateur du 2026-10-16 | Sprint 10 (tâches prêtes) |
| T-041-15 | Relevé de coût Railway à J+7 | 0,5 h | Pas avant le 2026-10-23 | Reste ouverte, avec GO utilisateur |
| T-041-06 (objectif) | Reset −50 % | - | −13 % obtenu | Voir reste du reset ci-dessous |

## Métriques

| Métrique | Valeur |
|----------|--------|
| Points de stories engagés | 7 |
| Points en review | 7 (100 %), 0 `done` à ce jour |
| Points reportés | 3 (US-029, hors engagement après arbitrage) |
| Référence sprint 8 | 25 points livrés (US-030, 040, 041, 042, 043) |
| Tests | PHPUnit 650 ; ECS et PHPStan 0 erreur |
| Échec connu | Assertion Factur-X d'`OrderHistorySmokeTest` en local (faux wkhtmltopdf) |
| CI | Pas encore verte de façon confirmée (case DoD ouverte de la PR #16) |
| Revue indépendante | 3 constats confirmés corrigés ; 1 constat PunchOut écarté après vérification |

La vélocité n'est pas comparable à celle du sprint 8 (le sprint 9 est volontairement allégé : 7 points plus des reports techniques en heures).

## Démonstration

Ordre suggéré (environnement local en mode prod, Railway non déployé pour ce sprint).

1. **US-044 container-fill** (~20 min)
   - Fiche produit et panier avec jauge de remplissage dérivée des capacités par contenant.
   - Règles B2B : palette pleine pour l'emballage, 20 % minimum pour la visserie, cartons entiers.
   - Bouton de commande du panier masqué tant que les règles ne sont pas satisfaites ; vérifier qu'une session PunchOut garde son propre comportement.
   - Un panier B2C n'est pas touché par les règles.
2. **Requêtes des listes** (~10 min) : compteur de requêtes (profiler) sur une liste de 5, 20 et 50 produits : 25 constantes au lieu de 59/164/213.
3. **US-006 / ADR-0013** (~10 min) : tableau de latence classique contre worker, décision de rester en classique, régression quick-order en mode worker, flag `FRANKENPHP_WORKER_MODE`.
4. **Reset** (~5 min) : chiffres de T-041-06 présentés honnêtement (SELECT de l'import −81 %, reset −13 %).

```gherkin
Given un panier B2B contenant des visseries sous le minimum de 20 % d'un contenant
When j'ouvre le panier
Then la jauge indique le remplissage et le bouton de commande est masqué
And après ajout de la quantité manquante, le bouton réapparaît
```

## Revue indépendante (US-044, US-006)

Confirmés et corrigés (commit `d5686b3`) :
- directive Caddy injectable par variable d'environnement : désormais reconstruite par l'entrypoint ;
- le leak-test ne comptait pas l'échec de l'ajout quick-order ;
- ADR-0013 trop affirmative : nuancée (une seule passe de mesure, preuves limitées aux fuites cookie/session).

Mineurs non corrigés (à planifier) :
- capacité périmée non supprimée si la quantité est < 1 (`VariantLogisticsSeeder`) ;
- pas de normalisation NFC (`PackagingUnitParser`) ;
- l'entrypoint ne recharge pas les fixtures si un premier chargement est interrompu (préexistant).

## Points ouverts à présenter

| Point | Impact | Prochaine vérification |
|-------|--------|------------------------|
| `/api/v2/shop/*` répond 404 dans le conteneur prod local | Peut affecter les outils MCP | Vérifier sur Railway |
| Pic mémoire du reset ≈ 1,08 Go | Possible dépassement de la limite Railway | Comparer à la limite du service |
| N+1 Wishlist sur les listes (28 requêtes à 50 produits) | Listes plus lentes | Story ou tâche à créer |
| Reste du reset : `demo_orders` ≈ 2000 requêtes, container_fill 776 SELECT+INSERT | Objectif −50 % non atteint | Candidat sprint 10 |
| Mode worker mesuré en une seule passe | Gain de 40 à 50 % non reproductible | 3 passes minimum si réévalué |

## Feedback stakeholders

### Positif
- {à collecter}

### À améliorer
- {à collecter}

### Nouvelles idées
- {à collecter}

## Impact sur le Backlog

| Action | US | Description |
|--------|-----|-------------|
| Reportée | US-029 | Récepteur de webhooks, au sprint 10 |
| Ajoutées (PR #15 ouverte) | US-045 à US-047 | Backlog futur |
| À créer | - | N+1 Wishlist ; reste du reset ; mineurs de revue |

## Prochaines étapes

1. Obtenir la CI verte sur la PR #16, puis GO utilisateur pour sortir du brouillon.
2. Relever le coût Railway (T-041-15) à partir du 2026-10-23.
3. Vérifier `/api/v2/shop/*` et la mémoire du reset sur Railway.
4. Rétrospective : voir `sprint-retro.md`.
