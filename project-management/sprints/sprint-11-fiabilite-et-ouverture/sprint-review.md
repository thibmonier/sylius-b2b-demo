# Sprint Review - Sprint 11 (Fiabilité et ouverture)

> Préparée le 2026-10-17 à partir de `git log main..HEAD` (14 commits), de la PR #21 (brouillon, ouverte : 74 fichiers, +2 820 / -209), des tâches du sprint et des documents `docs/remise-a-zero-demo.md`, `docs/ouverture-publique.md` et `docs/remplissage-contenants.md`. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-11-27 (matin) |
| Durée | 1 h |
| Branche / PR | `feature/sprint-11-fiabilite-et-ouverture` / PR #21 (brouillon) |
| Commits du sprint | 14 (tous le 2026-10-17) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (workers Claude)

## Sprint Goal

> « Rendre la remise à zéro nocturne fiable (toute erreur fatale est vue par `/healthz`) et préparer l'ouverture publique de la démo : revue de sécurité finale, mode public testé, runbook d'ouverture et retour arrière. »

**Atteint : ✅ OUI côté code et documentation ; la bascule publique reste à décider (GO utilisateur)**

Justification : les trois stories sont en review et vérifiées sur base neuve. Le reset signale toute erreur fatale à `/healthz`, le mode public est testé, le runbook et le retour arrière sont écrits et les constats de la revue de sécurité sont appliqués. Restent des tâches [OPS] qui dépendent de l'utilisateur, par conception hors du chemin critique.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-049 | Fiabilité de la remise à zéro | 3 | ✅ | ✅ En review (T-049-08 OPS en attente) |
| US-050 | Préparer l'ouverture publique de la démo | 5 | ✅ | ✅ En review (T-050-09 à T-050-11 OPS en attente) |
| US-051 | Container-fill : la boîte est l'unité de vente | 2 | ✅ | ✅ En review |

**Livré : 10/10 points en review (100 %)**, en attente de la CI et du GO de merge. US-051 (2 pts) a été ajoutée en cours de sprint, à la suite d'un défaut signalé par l'utilisateur dans le plan de chargement de la liste de Claire : le plan de départ de 8 points est donc passé à 10.

## Éléments non terminés (tâches [OPS] pour l'utilisateur)

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| T-049-08 Relevé de coût Railway complet | Non fait | Pas avant le 2026-10-23, par l'utilisateur | Après le 2026-10-23 |
| T-050-09 Moniteur externe sur `/healthz` | Non fait | Compte de l'utilisateur | Création par l'utilisateur |
| T-050-10 Bascule publique | Non faite | GO explicite requis | Sur GO, éventuellement hors sprint |
| T-050-11 Parcours MCP réel | Non fait | Dépend de la bascule | Après T-050-10 |
| Vérifier `X-Real-IP` après la bascule | Non fait | Dépend de la bascule | Contrôle du runbook, après T-050-10 |

## Détail par story

### US-049 : Fiabilité de la remise à zéro (`docs/remise-a-zero-demo.md`)
- Marqueur d'échec atomique, verrou d'exécution et délais sur les sous-processus : une erreur fatale pendant le reset est vue par `/healthz` (503), un succès retire le marqueur.
- `cache:clear` et `cache:warmup` tournent en sous-processus à mémoire fraîche ; `symfony/process` est déclaré en dépendance.
- Plafond `request_body max_size` côté Caddy pour le récepteur de webhooks.
- Limite de `demo_orders` (1 911 requêtes, cœur Sylius) actée en documentation, sans code, comme décidé.
- Correctif de test : le chemin du marqueur de reset est surchargé dans `services_test.yaml` (0e8dbda), car `services.yaml` l'emportait et le marqueur était écrit dans le vrai `var/`.

### US-050 : Ouverture publique de la démo (`docs/ouverture-publique.md`)
- Deux modes testés (privé : porte et avertissement MCP ; public : accès libre, inscriptions refusées, `robots.txt` / noindex selon le mode).
- `app:demo:preflight`, bandeaux et guide MCP suivent le mode ; `.railway/railway.ts` préparé sans être appliqué.
- Runbook : variables Railway, vérifications après bascule, retour arrière, moniteur `/healthz`.
- Revue de sécurité indépendante faite avant l'ouverture ; ses constats sont appliqués (a4cd868). Les compteurs du limiteur de débit sont vidés au début de chaque exécution de test.

### US-051 : Container-fill, la boîte est l'unité de vente (`docs/remplissage-contenants.md`)
- « Boîte de N » et « lot de N » sont vendus comme l'unité : 1 unité par colis, aucun multiple, poids du colis égal au poids de la variante ; `PackagingUnitParser` supprimé. Le plugin container-fill n'est pas modifié.
- Revue : aucun point bloquant. Un test a été ajouté pour la variante sans poids ni dimensions (820e382). Les paliers de prix (10 et 50) sont désormais exprimés en boîtes, jugé acceptable.

## Vérification finale (Scrum Master)

- Base neuve, 2 passages de la suite complète : 808 tests, 8 386 assertions, 0 échec, aucun marqueur de reset laissé.
- ECS et PHPStan OK.
- CI de la PR #21 : à confirmer avant la séance.

## Incidents du sprint

- Disque de la machine plein à 100 % à plusieurs reprises (caches de test par worker, OrbStack, VM de l'app Claude) : MySQL arrêté et vérification bloquée.
- Faux échecs liés à des caches de test partagés ou périmés (`var/cache/test` compilé avant un changement de constructeur : 10 erreurs Badge).
- `bin/prepare-test-db` en échec sur un dossier de cache neuf (DoctrineMetadataCacheWarmer), corrigé par `--no-warmup` (4bf5dbf).
- Relecteurs spécialisés (security-auditor, symfony-reviewer) arrivés à leur limite de 6 tours sans rapport, relancés en general-purpose.
- Une commande de mesure collée sur deux lignes, avec une boucle active.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 8 (10 avec US-051 ajoutée) | sprint 10 : 13 |
| Points en review | 10 | sprint 10 : 13 |
| Taux de complétion | 100 % des stories (5 tâches OPS en attente de l'utilisateur) | ↗️ |
| Tests | 808 tests, 8 386 assertions, 0 échec | sprint 10 : 765 tests |
| Commits | 14 | sprint 10 : 19 |

## Démonstration (ordre suggéré)

1. **US-049** (~5 min) : reset avec erreur fatale simulée, `/healthz` en 503, puis reset réussi.
2. **US-050** (~7 min) : bascule des deux modes en préproduction, `robots.txt`, `app:demo:preflight`, lecture du runbook et du retour arrière.
3. **US-051** (~3 min) : plan de chargement de la liste de Claire, paliers en boîtes.

```gherkin
Given une erreur fatale survient pendant la remise à zéro
When j'interroge /healthz
Then la réponse est 503
And après un reset réussi le marqueur est retiré et la réponse est 200

Given la démo est en mode public
When j'ouvre la page d'inscription
Then l'inscription est refusée (403)
And robots.txt n'autorise pas l'indexation
```

## Feedback à collecter

1. Le runbook d'ouverture et le retour arrière sont-ils suffisants pour décider la bascule ?
2. Les paliers de prix en boîtes (10 et 50) conviennent-ils ?
3. Quand fixer la date de bascule publique (T-050-10) ?
4. Priorités du sprint 12.

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
| Ajoutée en cours de sprint | US-051 | Défaut du plan de chargement de la liste de Claire |
| Reportées | T-049-08, T-050-09, T-050-10, T-050-11 | Tâches [OPS] de l'utilisateur |
| À vérifier | `X-Real-IP` | Après la bascule |

## Prochaines étapes

1. Confirmer le résultat de la CI de la PR #21, puis demander le GO de merge à l'utilisateur.
2. L'utilisateur réalise les tâches [OPS] restantes (T-049-08 après le 2026-10-23, moniteur, bascule sur GO, parcours MCP).
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
