# Sprint 58 — Données, files et tâches planifiées

**Durée** : 2026-12-24 → 2027-01-07 (créneau prévisionnel, comme les sprints 54 à 57 ; exécution réelle depuis le 2026-11-25)
**Capacité** : 15 points + tâches techniques
**Engagement** : **15 points (3 stories), soit 100 % de la capacité** + 3 tâches techniques (dont deux faites à la planification)

## Sprint Goal

La page de santé montre l'état des données, des files et des tâches planifiées : une migration oubliée, un transport d'échec qui se remplit ou un cron qui ne réussit plus se voient sans ouvrir un terminal.

## Stories

| ID | Titre | Dépôt | Points | MoSCoW |
|----|-------|-------|--------|--------|
| [US-294](../../backlog/user-stories/US-294.md) | Sondes de données : connexion, migrations en attente, dérive du schéma, tables qui grossissent | `sylius-platform-health-plugin` | 5 | Must |
| [US-295](../../backlog/user-stories/US-295.md) | Sondes de files et de cache : messages en attente, âge du plus ancien, transport d'échec, cache, dossiers inscriptibles | `sylius-platform-health-plugin` | 5 | Must |
| [US-296](../../backlog/user-stories/US-296.md) | Battement des tâches planifiées : rythme déclaré, cron qui ne réussit plus | `sylius-platform-health-plugin` | 5 | Must |

Fin du MMF d'EPIC-21 (US-293 livrée au sprint 57). L'ordre ci-dessus est l'ordre d'exécution.

## Décisions de cadrage (2026-11-25)

Arbitrages de Thibaut, le 2026-11-25 :

1. **Périmètre : EPIC-21 complet**, US-294, US-295 et US-296 (15 points, 100 %). **US-303** (mécanisme des sondes facultatives des plugins, EPIC-22) passe au **sprint 59**, ce qui décale US-304 à US-306.
2. **US-296 : battement stocké en base**, table `tmonier_platform_health_heartbeat`, une ligne par commande déclarée, migration livrée par le plugin (première entité du plugin). Un pool de cache aurait perdu l'état à chaque redéploiement et à chaque `cache:clear`, d'où un faux « jamais lancée ».
3. **US-295 : pas de nouvelle catégorie** : sondes Messenger dans `data`, cache et dossiers inscriptibles dans `environment`, conformément aux trois catégories d'EPIC-21.
4. **Seuils (question n° 3 d'EPIC-21)** : défauts opinionnés, configurables sonde par sonde, comme US-293.

Décisions antérieures maintenues : `identifier()` constante littérale (décision A), rythme du battement déclaré en configuration et jamais sur la commande (question n° 2 d'EPIC-21), aucun appel réseau, rien de sensible à l'écran.

### Constat à la planification

La démo n'a **aucun transport d'échec** Messenger (`config/packages/messenger.yaml` : `async` Doctrine et `sync` seulement) : un message qui échoue après ses tentatives disparaît. La sonde `data.messenger_failed` le signalera ; T-TECH-58-03 ajoute un `failure_transport` à la démo.

## Découpage

| Tâche | Story | Type | Contenu | Heures |
|-------|-------|------|---------|--------|
| T-294-01 | US-294 | [TEST] | Tests rouges : base saine, lente, injoignable (DSN masqué), migrations en attente et inconnues, dérive du schéma, dérive trop longue, tables sous et au-delà du seuil, plateforme non gérée | 3 h |
| T-294-02 | US-294 | [BE] | `data.connection`, `data.pending_migrations` | 3 h |
| T-294-03 | US-294 | [BE] | `data.schema_drift` (nombre d'instructions, budget de temps), `data.table_growth` (estimation par le catalogue MySQL ou PostgreSQL, tables déclarées) | 4 h |
| T-294-04 | US-294 | [DOC] | Traductions FR/EN, README (quatre sondes, seuils, tables déclarées), CHANGELOG | 1,5 h |
| T-295-01 | US-295 | [TEST] | Tests rouges : messages en attente, âge du plus ancien, transport non comptable ou non Doctrine (`unknown`), transport d'échec vide, plein, absent, cache, dossier non inscriptible | 3 h |
| T-295-02 | US-295 | [BE] | `data.messenger_pending`, `data.messenger_oldest` (Doctrine seulement), `data.messenger_failed` | 4 h |
| T-295-03 | US-295 | [BE] | `environment.cache_pool` (clé témoin propre au plugin), `environment.writable_dirs` (chemins relatifs) | 2 h |
| T-295-04 | US-295 | [DOC] | Traductions FR/EN, README (cinq sondes, transport d'échec recommandé), CHANGELOG | 1,5 h |
| T-296-01 | US-296 | [TEST] | Tests rouges : seules les commandes déclarées sont enregistrées ; à l'heure, en retard de 2 et 4 battements, en échec, jamais lancée ; panne d'enregistrement sans effet sur le code de sortie | 3 h |
| T-296-02 | US-296 | [DB] | Entité, mapping XML, dépôt (mise à jour ou insertion), migration du plugin | 2,5 h |
| T-296-03 | US-296 | [BE] | Abonné `ConsoleEvents::TERMINATE` et `ERROR`, configuration `heartbeats` validée à la compilation, sonde `scheduled_tasks.heartbeat` | 3,5 h |
| T-296-04 | US-296 | [DOC] | Traductions FR/EN, README (déclaration, exemple avec les crons de la démo, migration), CHANGELOG | 1,5 h |

**Total** : 12 tâches, **32,5 h** de stories (15 points, soit 2,2 h par point ; sprint 57 : 2,7 h par point au-dessus de la capacité, sprint 56 : 1,9 h).

## Tâches techniques

| ID | Titre | Type | Heures |
|----|-------|------|--------|
| T-TECH-58-01 | `current_sprint: 58`, sprint 58 planifié, fiches US-294 à US-296, EPIC-21 à jour | OPS | 1 h (faite) |
| T-TECH-58-02 | **A-57-1 et A-57-2** : règles 30 et 31 dans `project-management/templates/consigne-worker.md` | DOC | 0,5 h (faite) |
| T-TECH-58-03 | **Dernière tâche du sprint (A-57-1)** : CHANGELOG et release v0.4.0 du plugin sur GO de Thibaut ; démo en ^0.4 : migration, `failure_transport`, battements des 16 crons de `docker/crontab`, tables surveillées (journal d'audit, envois de webhooks, journal PunchOut), `compat-refresh` puis `proof-refresh` relu avant la PR (A-57-3) | OPS | 3,5 h |

**Total** : 5 h dont 1,5 h faite à la planification. Avec les stories : **37,5 h**.

## Actions de Thibaut (hors capacité)

- **GO et tag `v0.4.0`** du plugin de santé après la fusion des trois stories, puis Packagist : sans eux, T-TECH-58-03 ne démarre pas. Fusions par Thibaut.
- **A-57-4, 2026-12-01** : relevé Railway par service (`mailpit` sur une semaine entière) et audit des autres projets de l'espace de travail (4,21 $ à expliquer). Convention : `railway usage` jamais par Claude.
- **Proposition de la rétro du sprint 57** : un second compte admin de démo en `en_US`, créé par les fixtures, pour les captures anglaises sans écriture en production. À arbitrer (non planifié ici).

## Contrainte d'exécution

Les trois stories sont dans le **même dépôt** (le plugin de santé) : un seul worker à la fois, un seul worktree, **pas de PR empilée** (règle 27) ; chaque story est fusionnée avant que la suivante démarre. La release n'est posée qu'après la fusion des trois (règle 30). T-TECH-58-03 est dans la démo et démarre après le tag.

## Ordre d'exécution

```mermaid
graph LR
    S294[US-294 donnees] --> S295[US-295 files et cache]
    S295 --> S296[US-296 battement]
    S296 --> REL[CHANGELOG, GO et tag v0.4.0]
    REL --> DEMO[T-TECH-58-03 demo en ^0.4]
```

- US-294 d'abord : elle établit l'accès à Doctrine que reprennent US-295 (transport Doctrine) et US-296 (entité et migration).
- US-296 en dernier : elle introduit la première migration du plugin ; la tester après les sondes de lecture évite de mêler deux sujets dans un rouge.

## Portes

- `main` de la démo et du plugin verts avant de commencer.
- **Rouge valide = échec d'assertion vu en CI, dans le run du SHA poussé, suites unitaire et non unitaire** (règle 26) ; le commit rouge passe PHPStan et ECS, sortie lue sans filtre et code de retour vérifié (règle 31).
- Outils de qualité dans Docker (règle 28) ; écart signalé dès qu'il est décidé (règle 29).
- Aucune sonde qui écrit, hors la clé témoin du cache (US-295) et l'abonné du battement en fin de commande (US-296) ; aucun DSN, aucun SQL, aucun chemin absolu à l'écran.
- Aucun tag sans GO de Thibaut ; fusions par Thibaut ; Packagist par Thibaut.
