# Sprint 1 — Walking Skeleton

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 1 |
| Nom | walking-skeleton |
| Début | 2026-10-15 |
| Fin | 2026-10-28 |
| Durée | 10 jours ouvrés |
| Points engagés | 26 |

## Sprint Goal

> Walking skeleton : Sylius 2.3 + 21 plugins dans l'image FrankenPHP, environnement local, tâches planifiées, CI verte, canaux B2B/B2C, correctifs des plugins publiés.

À la fin du sprint, l'application se lance en local avec `docker compose up`, les 21 plugins sont enregistrés sans conflit, l'image FrankenPHP est construite en multi-stage, supercronic tourne dans le conteneur, la CI construit l'image et vérifie les pages clés, les canaux B2B et B2C existent en FR/EN, et les correctifs punchout/agentic-feeds sont en patch release.

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS et PHPStan passent sur le code de la démo
- [ ] Libellés en français et en anglais si interface
- [ ] Aucun secret ni identifiant réel dans le dépôt
- [ ] Documentation mise à jour si comportement visible
- [ ] CI verte

## Sprint Backlog

| ID | Titre | Points | Statut |
|----|-------|--------|--------|
| US-001 | Projet Sylius 2.3 avec les 21 plugins | 8 | 🔵 To Do |
| US-031 | Correctifs des plugins publiés | 1 | 🔵 To Do |
| US-002 | Image Docker FrankenPHP multi-stage | 5 | 🔵 To Do |
| US-003 | Environnement local docker compose | 3 | 🔵 To Do |
| US-004 | Tâches planifiées dans le conteneur | 3 | 🔵 To Do |
| US-005 | CI GitHub Actions et tests de fumée | 3 | 🔵 To Do |
| US-007 | Canaux B2B et B2C, FR/EN | 3 | 🔵 To Do |

**Total engagé : 26 points**

## Ordre de réalisation recommandé

1. US-031 (actions immédiates dans les dépôts des plugins)
2. US-001 (socle, bloque tout le reste)
3. US-002 (image, bloque US-003/004/005)
4. US-007 (fixtures de canaux, parallèle à US-002)
5. US-003, US-004 (en parallèle, après US-002)
6. US-005 (CI, dernière étape)

## Dépendances inter-stories

| Story | Dépend de |
|-------|-----------|
| US-002 | US-001 |
| US-003 | US-002 |
| US-004 | US-002 |
| US-005 | US-002 |
| US-007 | US-001 |
| US-031 | — |

## Risques identifiés

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Conflit entre plugins tmonier (versions incompatibles) | Moyenne | Élevé | Résolution Composer dès US-001, un plugin bloquant → épingler une version antérieure |
| wkhtmltox 0.12.6.1 (patched qt) introuvable en ARM | Faible | Moyen | Téléchargement depuis le miroir officiel vérifié ; fallback : build depuis les sources |
| Sylius-Standard 2.3 pas encore stable au démarrage | Faible | Élevé | Utiliser la RC la plus récente et épingler `~2.3` |
| US-007 : conflit hostname B2B/B2C en local | Faible | Faible | Utiliser des préfixes de locale (pas de sous-domaine) en local |
| supercronic non compatible avec bookworm ARM | Faible | Moyen | Utiliser le binaire officiel pour linux/amd64 ; CI sur ubuntu-latest (amd64) |

## Burndown (prévisionnel)

```
Points |
  26   | ●
  23   |   ●
  20   |     ●
  17   |       ●
  14   |         ●
  11   |           ●
   8   |             ●
   5   |               ●
   2   |                 ●
   0   |                   ●
       D1 D2 D3 D4 D5 D6 D7 D8 D9 D10
```

## Notes de planning

- Sprint 1 est le sprint fondateur : tout blocage ici repousse les 5 suivants.
- US-031 peut être fait à tout moment de la semaine 1 (c'est une action dans les dépôts des plugins, indépendante du dépôt de la démo).
- US-001 et US-007 peuvent être développés en parallèle après le scaffolding initial.
- La stratégie de configuration par plugin (`config/packages/tmonier/*.yaml`) doit être mise en place dès US-001 pour éviter la refactorisation au sprint 2.
