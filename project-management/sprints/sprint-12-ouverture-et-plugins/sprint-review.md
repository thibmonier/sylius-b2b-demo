# Sprint Review - Sprint 12 (Ouverture et plugins)

> Préparée le 2026-10-17 à partir de `git log main..HEAD` (14 commits), de la PR #22 (brouillon, ouverte : 49 fichiers, +2 937 / -32) et des tâches du sprint. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-12-11 (matin) |
| Durée | 1 h |
| Branche / PR | `feature/sprint-12-ouverture-et-plugins` / PR #22 (brouillon) |
| Commits du sprint | 14 (tous le 2026-10-17) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (workers Claude)

## Sprint Goal

> « Ouvrir la démo au public sur GO de l'utilisateur et la vérifier de bout en bout, montrer le second protocole PunchOut (OCI) dans le simulateur, poser les fondations du plugin de parc d'équipements client (spec, ADR, verdict Sylius Plus, socle qualité) et solder la dette outillage des rétrospectives. »

**Atteint : ✅ OUI côté code et documentation, sauf l'ouverture publique elle-même, qui reste à l'utilisateur (GO)**

Justification : trois stories sur quatre sont en review et vérifiées sur base neuve. US-052 reste en cours : la partie de Claude est faite (runbook relu, check-list de GO, `bin/verify-public-mode`), mais le moniteur, la bascule, `X-Real-IP`, le parcours MCP réel et le relevé de coût dépendent de l'utilisateur.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-052 | Ouverture publique de la démo | 3 | ✅ (en mode privé) | 🔄 En cours (tâches OPS de l'utilisateur) |
| US-053 | Simulateur PunchOut OCI dans la démo | 4 | ✅ | ✅ En review |
| US-054 | Plugin parc d'équipements client, tranche 1 | 4 | ✅ | ✅ En review |
| US-055 | Actions de rétro et dette | 3 | ✅ | ✅ En review |

**Livré : 11/14 points en review (79 %)** ; US-052 (3 pts) reste in-progress tant que les tâches OPS ne sont pas faites.

## Éléments non terminés (tâches [OPS] pour l'utilisateur)

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| Moniteur externe sur `/healthz` | Non fait | Compte de l'utilisateur | Création par l'utilisateur |
| Bascule publique | Non faite | GO explicite requis (D4) | Sur GO |
| Vérifier `X-Real-IP` | Non fait | Dépend de la bascule | Contrôle du runbook, après la bascule |
| Parcours MCP réel | Non fait | Dépend de la bascule | Après la bascule |
| Relevé de coût Railway | Non fait | Pas avant le 2026-10-23 | Après le 2026-10-23 |
| US-031 (agentic-feeds) | Faite le 2026-10-17 | Publié sur Packagist par l'utilisateur pendant la revue | Dépôt VCS retiré de `composer.json`, lock limité à ce paquet, 117 tests de fumée verts |

## Détail par story

### US-052 : Ouverture publique de la démo (3 pts, en cours)
- Fait par Claude : runbook relu contre l'état réel, check-list de GO ajoutée.
- `bin/verify-public-mode <url>` : script en lecture seule qui rejoue les vérifications du runbook, avec un test ; lancé en `--private` contre Railway : OK.
- Reste à l'utilisateur : moniteur, bascule sur GO, `X-Real-IP`, parcours MCP réel, coût après le 2026-10-23.

### US-053 : Simulateur PunchOut OCI (4 pts, en review)
- Acheteur SAP OCI d'Acme, formulaire `HOOK_URL`, entrée `POST /punchout/oci/<code>` ; le retour `NEW_ITEM-*` est lu et affiché, avec nombre de lignes et longueur des champs bornés.
- Retour protégé par un état signé (HMAC, une heure) à usage unique, sous verrou ; limiteurs `punchout_start` (entrée) et `punchout_oci_return` (retour). Pas de plafond quotidien : OCI ne crée pas de commande (D3).
- Revue de sécurité indépendante : aucun bloquant (47 tests OCI). Le risque résiduel accepté est documenté dans `docs/durcissement-demo.md`.

### US-054 : Plugin parc d'équipements, tranche 1 (4 pts, en review)
- Dépôt public créé avec l'accord de l'utilisateur : https://github.com/thibmonier/sylius-customer-asset-management-plugin.
- ADR-0001 accepté : l'équipement appartient au client et il est partagé avec l'entreprise si company-accounts est installé (décision de l'utilisateur).
- Verdict Sylius Plus : fonction non documentée publiquement ; la promotion reste suspendue.
- La première CI a échoué : le dossier `assets/` (entrypoints vides) manquait. Correctif poussé.
- Backlog : US-056 à US-060.

### US-055 : Actions de rétro et dette (3 pts, en review)
- `bin/run-tests` avec un cache jetable par exécution ; un faux échec dû aux compteurs des limiteurs partagés est corrigé.
- Erreur de planification : l'agent de planning a affirmé qu'agentic-feeds était sur Packagist, alors que la v1.0.0 venait du dépôt VCS (Packagist répond 404). US-031 reste bloquée et la soumission revient à l'utilisateur.
- L'action 4 du sprint 10 n'avait pas été faite : US-061 et US-062 sont créées.

## Vérification finale (Scrum Master)

- Base neuve, 2 passages de la suite complète : 861 tests, 8 558 assertions, 0 échec.
- ECS et PHPStan OK.
- CI de la PR #22 : à confirmer avant la séance.

## Incidents du sprint

- Première CI du plugin en échec (dossier `assets/` absent), corrigée.
- Affirmation fausse du planning sur Packagist (404), découverte en cours de sprint.
- Faux échec de test lié aux compteurs des limiteurs partagés, corrigé par `bin/run-tests`.
- Dossiers `var/cache-*` restés en place : `rm -rf` est refusé aux agents.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 14 | sprint 11 : 8 (10 avec US-051) |
| Points en review | 11 | sprint 11 : 10 |
| Taux de complétion | 3 stories sur 4 (US-052 en attente de l'utilisateur) | ↘️ |
| Tests | 861 tests, 8 558 assertions, 0 échec | sprint 11 : 808 tests |
| Commits | 14 | sprint 11 : 14 |

## Démonstration (ordre suggéré)

1. **US-052** (~3 min) : `bin/verify-public-mode --private`, lecture de la check-list de GO.
2. **US-053** (~7 min) : acheteur SAP OCI d'Acme, aller-retour `HOOK_URL` / `NEW_ITEM-*`.
3. **US-054** (~5 min) : dépôt public, SPEC, ADR-0001, verdict Plus.
4. **US-055** (~3 min) : `bin/run-tests` avec cache jetable.

```gherkin
Given je suis l'acheteur SAP OCI d'Acme dans le simulateur
When le panier revient sur le HOOK_URL avec des champs NEW_ITEM-*
Then les lignes sont lues et affichées, dans les bornes
And un second retour avec le même état signé est refusé

Given la démo est en mode privé
When je lance bin/verify-public-mode --private contre l'environnement
Then toutes les vérifications passent sans écrire
```

## Feedback à collecter

1. La check-list de GO suffit-elle pour décider la bascule ? Quelle date ?
2. Le partage de l'équipement avec l'entreprise (ADR-0001) répond-il au besoin ?
3. Ordre des stories US-056 à US-062 pour le sprint 13.
4. (Réglé pendant la revue : agentic-feeds publié sur Packagist, US-031 faite.)

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
| Ajoutées | US-056 à US-060 | Suites du plugin de parc d'équipements |
| Ajoutées | US-061, US-062 | Mineurs du sprint 10 (action 4 non faite) |
| Faite | US-031 | agentic-feeds résolu depuis Packagist |
| Reportées | Tâches OPS d'US-052 | À l'utilisateur |

## Prochaines étapes

1. Confirmer la CI de la PR #22, puis demander le GO de merge à l'utilisateur.
2. L'utilisateur réalise les tâches [OPS] d'US-052 et soumet agentic-feeds à Packagist.
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
