# Sprint 54 — Thème : zéro violation axe, et des workers qui prouvent leur rouge

**Durée** : 2026-11-12 → 2026-11-26
**Capacité** : 3 points + 3 tâches techniques
**Engagement** : 3 points (1 story) + 3 tâches techniques

## Sprint Goal

Une page thémée ne présente plus aucune violation axe, en clair comme en sombre, et la CI du thème le garantit ; les consignes des workers appliquent les actions A-53-1 et A-53-3.

## Stories

| ID | Titre | Dépôt | Points | MoSCoW |
|----|-------|-------|--------|--------|
| [US-281](../../backlog/user-stories/US-281.md) | Défauts AAA du cœur corrigés et audit axe complet en CI | thème | 3 | Should |

Périmètre choisi par Thibaut le 2026-11-12. Vérifié le même jour, toujours bloquées par l'amont et laissées hors sprint :
- US-275 : `sylius/mcp-server-plugin` en est à v0.3.0, qui exige `symfony/mcp-bundle ^0.13` ;
- US-266 : Sylius 2.3.2 exige `phpunit/phpunit ^12.5` ;
- US-267 : `@sylius-ui/shop` 2.0.0-alpha-1 reste sur `@symfony/webpack-encore ^3.1`.

La démo ne change pas (décision du 2026-11-05).

## Découpage

| Tâche | Story | Type | Contenu | Heures |
|-------|-------|------|---------|--------|
| T-281-01 | US-281 | [TEST] | Audit axe complet (WCAG A/AA/AAA, bonnes pratiques) en clair et en sombre dans le job « projet vierge », accueil et liste avec pagination ; rouge prouvé en CI sur `color-contrast` et `aria-required-parent` | 2 h |
| T-281-02 | US-281 | [FE-WEB] | Couleur de la pagination désactivée (clair et sombre) sous `[data-theme="industry-trade"]`, paires dans `ColorContrastTest` | 1 h |
| T-281-03 | US-281 | [FE-WEB] | Surcharge de `register.html.twig` sans `role="menuitem"`, empreinte dans `fingerprints.json`, test fonctionnel | 1 h |
| T-281-04 | US-281 | [DOC] | CHANGELOG, README si besoin | 0,5 h |

## Tâches techniques

| ID | Titre | Type | Heures |
|----|-------|------|--------|
| T-TECH-54-01 | `current_sprint: 54`, sprint 54 planifié | OPS | 0,25 h |
| T-TECH-54-02 | Consignes des workers (`project-management/worker-brief.md`) : PHPStan jugé sur la CI et sur le run de `main` (A-53-1), commit de test poussé seul puis arrêt (A-53-3), dépôt lu dans `git remote get-url origin` (A-53-4) | DOC | 0,5 h |
| T-TECH-54-03 | Tag v0.3.1 du thème et release après fusion et GO de Thibaut | OPS | 0,25 h |

## Ordre d'exécution

```mermaid
graph LR
    D[T-TECH-54-02 consignes] --> W[worker US-281]
    W --> T1[T-281-01 audit, push seul, rouge CI]
    T1 --> F[T-281-02/03 correctifs poussés par l'orchestrateur]
    F --> TAG[Tag v0.3.1 sur GO]
```

- Les consignes sont écrites avant de lancer le worker et lui sont transmises (A-53-3).
- Le worker pousse le commit de test seul et s'arrête ; l'orchestrateur lit le rouge en CI, puis pousse le correctif.

## Portes

- `main` du thème vert (`7cd4c56`, v0.3.0) et `main` de la démo vert (`299bec7`).
- Branches de worktree créées avec `--no-track`, push explicite `HEAD:<branche>`, `headRefOid` vérifié.
- Rouge valide = échec d'assertion vu en CI (pas ECS, pas PHPStan).
- Aucun tag sans GO de Thibaut ; fusions par Thibaut.
