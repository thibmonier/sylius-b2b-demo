# Sprint Review - Sprint 13 (Vitrine des plugins)

> Préparée le 2026-10-17 à partir de `git log main..HEAD` (14 commits), de la PR #23 (brouillon, ouverte : 44 fichiers, +2 667 / -17) et des tâches du sprint. Les champs « Feedback » restent à remplir pendant la séance.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-12-24 (le 25 est férié, D8) |
| Durée | 1 h |
| Branche / PR | `feature/sprint-13-vitrine-des-plugins` / PR #23 (brouillon) |
| Commits du sprint | 14 (tous le 2026-10-17) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (workers Claude)

## Sprint Goal

> « Préparer les 24 plugins à l'ouverture publique : les classer par rapport à Sylius Plus et fixer un ton neutre, les doter d'un README français, d'une référence à la démo B2B et de vraies captures d'écran, et préparer le sous-domaine de la démo, sans publier le site ni les liens de contact. »

**Atteint : ✅ OUI côté code et documentation, sauf deux gestes qui reviennent à l'utilisateur (le GO unique de merge des 24 PR et le choix du domaine)**

Justification : les cinq stories sont en review et vérifiées. Les 24 PR de plugins sont ouvertes mais rien n'est fusionné ni publié sans le GO de l'utilisateur ; le CNAME et le certificat attendent le choix du domaine.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-063 | Classement Sylius Plus des 24 plugins et charte de ton | 2 | ✅ | ✅ En review |
| US-064 | Bloc standard, outil de contrôle et README FR du lot A | 4 | ✅ | ✅ En review |
| US-067 | README FR du lot B et livraison des 24 dépôts | 3 | ✅ | ✅ En review (24 PR en attente du GO) |
| US-065 | Captures d'écran manquantes et vérification des existantes | 4 | ✅ | ✅ En review |
| US-066 | Préparation du sous-domaine de la démo | 2 | ✅ | ✅ En review (tâches OPS en attente du domaine) |

**Livré : 15/15 points en review (100 %)**, dont 5 points (US-067, US-066) qui attendent une décision de l'utilisateur pour être soldés.

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| GO unique de merge des 24 PR (`docs/readme-fr-demo`) | PR ouvertes, rien de fusionné | Décision D6 : un GO de l'utilisateur | Sur GO |
| CNAME et certificat du sous-domaine | Non faits | Domaine du site non choisi | Choix de l'utilisateur : `thibaut-monier.fr` (recommandé par Claude) ou `agentic-agency.fr` (aucune société juridique derrière) |
| US-052 (ouverture publique) | Reportée | Tâches OPS d'ouverture : moniteur, bascule, `X-Real-IP`, MCP réel, coût | À l'utilisateur, reportée au sprint suivant |

## Détail par story

### US-063 : Classement Sylius Plus et charte de ton (2 pts, en review)
- Classement des 24 plugins : 4 concurrents, 3 à confirmer, 3 complémentaires, 14 hors périmètre.
- La documentation technique de Plus n'est pas lisible (authentification) : le classement repose sur les pages publiques, d'où les 3 plugins « à confirmer ».
- Charte de ton neutre appliquée (D2) : 11 phrases de comparaison neutralisées dans 6 README, sur décision de l'orchestrateur.

### US-064 : Bloc standard, outil et README du lot A (4 pts, en review)
- `bin/vitrine-readme` applique et contrôle le bloc standard de la démo ; la note du GO des 24 branches est documentée.
- README FR du lot A (12 plugins) livrés.

### US-067 : README du lot B et livraison (3 pts, en review)
- Lot B (12 plugins) livré ; les 24 PR de plugins sont ouvertes (`docs/readme-fr-demo`) et attendent le GO unique de l'utilisateur.

### US-065 : Captures d'écran (4 pts, en review)
- 8 captures faites, 7 retenues après revue : l'une était un doublon octet pour octet d'une autre et montrait une option du cœur Sylius.
- Le curseur était visible sur 3 captures, refaites ; le gestionnaire de mots de passe du navigateur gênait les formulaires.
- Le simulateur PunchOut est impossible en local (retour HTTPS exigé).
- Un défaut de traduction punchout a été trouvé et mis au backlog (US-070).
- Captures relues par l'orchestrateur ; protocole et audit documentés.

### US-066 : Sous-domaine de la démo (2 pts, en review)
- `TRUSTED_HOSTS` accepte un hôte personnalisé à côté du domaine Railway ; runbook du sous-domaine, références paramétrées, bascule de l'hôte canonique gardée.
- Revue indépendante : aucun bloquant, 3 suites traitées. Correctif PHPStan (cast dans `PublicHostPatternProcessor`) fait par l'orchestrateur : l'hôte public n'est accepté que s'il est une chaîne.

## Vérification finale (Scrum Master)

- 893 tests, 0 échec ; ECS et PHPStan OK.
- Revues indépendantes : US-066 (aucun bloquant), README FR (aucun bloquant), captures relues par l'orchestrateur.

## Incidents du sprint

- Captures : doublon exact avec option du cœur Sylius, curseur visible sur 3 captures, gestionnaire de mots de passe gênant (corrigés avant la revue).
- Erreur PHPStan de cast dans `PublicHostPatternProcessor`, corrigée par l'orchestrateur.
- `var/cache-w13d` reste en place : `rm -rf` est toujours refusé aux agents.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 15 | sprint 12 : 14 |
| Points en review | 15 | sprint 12 : 11 |
| Taux de complétion | 5 stories sur 5 (2 en attente de l'utilisateur) | ↗️ |
| Tests | 893 tests, 0 échec | sprint 12 : 861 tests |
| Commits | 14 | sprint 12 : 14 |

## Démonstration (ordre suggéré)

1. **US-063** (~5 min) : classement des 24 plugins, charte de ton.
2. **US-064 / US-067** (~10 min) : `bin/vitrine-readme`, un README FR rendu sur GitHub, la liste des 24 PR.
3. **US-065** (~8 min) : les 7 captures retenues, le protocole.
4. **US-066** (~4 min) : runbook, hôte personnalisé, choix du domaine.

```gherkin
Given les 24 PR docs/readme-fr-demo sont ouvertes
When je lance bin/vitrine-readme en contrôle
Then chaque README porte le bloc standard et aucune comparaison avec Plus

Given un hôte personnalisé est configuré dans TRUSTED_HOSTS
When la démo reçoit une requête sur cet hôte
Then elle est acceptée, et un hôte inconnu est refusé
```

## Feedback à collecter

1. Le GO de merge des 24 PR : quand ?
2. Domaine du site : `thibaut-monier.fr` (recommandé) ou `agentic-agency.fr` ?
3. Les 3 plugins « à confirmer » face à Plus : conserver le ton sobre ?
4. Priorité d'US-070 (traduction punchout), d'US-068 et d'US-069 au sprint 14.

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
| Ajoutée | US-070 | Défaut de traduction punchout trouvé pendant les captures |
| Reportée | US-052 | Tâches OPS d'ouverture à l'utilisateur |
| Conservées | US-068, US-069 | Liens vers le site et page Plugins, après le choix du domaine |

## Prochaines étapes

1. L'utilisateur donne le GO unique de merge des 24 PR et choisit le domaine.
2. Créer le CNAME et vérifier le certificat une fois le domaine choisi.
3. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
