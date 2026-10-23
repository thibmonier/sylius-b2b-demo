# Dépendances : Sprint 022 (finalisation)

> Périmètre du plan du 2026-10-23 (27 points). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    AUDIT["Audits du 2026-10-22 (qualité, sécurité, performance)"]
    P21["Sprints 17 à 21 : stories en review"]
    GOT["Actions de Thibaut : GO, merges de plugins, tags, Packagist, déploiement"]
    TECH["T-TECH-22-01 Clôture des reviews : vérification en production puis done"]
    LOCK["T-TECH-22-02 Ordre d'écriture du composer.lock"]
    DISK["T-TECH-22-04 Budget disque, un seul worker de plugin à la fois"]
    US163["US-163 (2 pts) : faite le 2026-10-23"]
    US164["US-164 Bornes et limitation de débit (3 pts)"]
    US162["US-162 Anomalies mineures (1 pt)"]
    US161["US-161 Solde de la rétro S17 (2 pts)"]
    US084["US-084 Rector en CI, couverture, proof-refresh (5 pts)"]
    US165["US-165 Dettes qualité (3 pts)"]
    US167["US-167 Performance à faible effort (2 pts)"]
    US088["US-088 Journal d'audit lisible (3 pts)"]
    US098["US-098 Jauge condensée (2 pts)"]
    US154["US-154 Company-insights devis et économies (3 pts)"]
    US062["US-062 Mineurs du script d'images (1 pt)"]
    CAddy["docker/Caddyfile partagé"]
    RECTOR["Application de Rector sur src et tests"]
    P21 --> TECH
    GOT --> TECH
    GOT --> LOCK
    LOCK --> US154
    AUDIT --> US164
    AUDIT --> US165
    AUDIT --> US167
    AUDIT --> US084
    US162 --> CAddy
    CAddy --> US167
    US084 --> RECTOR
    RECTOR -.->|"à merger avant ou après, jamais en parallèle"| US164
    RECTOR -.-> US165
    RECTOR -.-> US088
    RECTOR -.-> US062
    DISK --> US154
    DISK --> US084
    US163 -.->|"livrée"| TECH
```

## Ordre recommandé

1. **Jour 1** : T-TECH-22-04 (disque), T-TECH-22-01a (liste des stories), T-161-06 (checklist d'intégration), cadrages (T-164-01, T-162-01, T-084-01) ; Thibaut est sollicité tôt pour T-TECH-22-01b (GO et déploiement).
2. **Chemin des Must** : US-164, US-162 puis US-167 (Caddyfile : US-162 avant US-167), US-161 (solde), clôture des reviews dès que le déploiement est fait.
3. **US-084** : l'application de Rector (T-084-03) est séquencée avec les autres branches de code (avant leurs créations ou après leurs merges) ; l'étape CI bloquante vient en dernier.
4. **Sacrifiables** (US-165, US-088, US-098, US-154, US-062) : engagées seulement si le point d'étape du 2026-10-30 le permet ; US-154 attend en plus le GO de Thibaut et l'ordre d'écriture du lock (T-TECH-22-02).

## Dépendances externes

| Dépendance | Type | Effet |
|------------|------|-------|
| GO de Thibaut (merges, tags, Packagist, déploiement) | Action humaine | Débloque la vérification en production et les montées du lock |
| Relectures de Thibaut (kit média, profil, posts, captures) | Action humaine | Nécessaires au passage à `done` de plusieurs stories de communication |
| Transcript T-103-03 | Action humaine | Conditionne US-103 et US-121 (sprint 23), sans effet sur le sprint 22 |
| CI GitHub | Plateforme | « Quality and smoke tests » et « Docker image » requises avant merge (`main` protégée) |
