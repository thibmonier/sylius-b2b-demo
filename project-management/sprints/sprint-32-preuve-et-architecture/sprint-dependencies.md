# Dépendances : Sprint 032 (preuve technique, architecture et script d'images)

> Périmètre décidé par Thibaut le 2026-10-28 (9 points engagés). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO31["Rétro S31 : actions 1 à 5"]
    US204["US-204 Page Plugins (livrée, done)"]
    US189["US-186, US-189, US-198 : charte de la home (livrées)"]
    US170["US-170, US-179 : page Architecture et schéma (livrées)"]
    US111["US-111 : page Preuve technique (livrée)"]
    TECH01["T-TECH-32-01 Machine et verrou des tests"]
    TECH02["T-TECH-32-02 Thibaut libère le disque"]
    TECH03["T-TECH-32-03 Build des assets frais"]
    TECH04["T-TECH-32-04 Images produit locales, panier"]
    TECH07["T-TECH-32-07 Reliquats US-203 (image reconstruite)"]
    TECH11["T-TECH-32-11 Suite complète locale"]
    TECH12["T-TECH-32-12 Capture 1280 px relue"]
    US206["US-206 Page Architecture (5 pts, protégée)"]
    US205["US-205 Page Preuve technique (3 pts, sacrifice n°2)"]
    US062["US-062 Script d'images (1 pt, sacrifice n°1)"]
    M205["T-205-03 Maquette Preuve technique"]
    M206["T-206-03 Maquette Architecture"]
    GO205["T-205-04 GO maquette (Thibaut)"]
    GO206["T-206-04 GO maquette (Thibaut)"]
    PR["PR du sprint"]
    PROD["Vérification en production après déploiement de main"]
    PROOF["config/demo/proof.json : bin/proof-rebase"]
    US207["US-207 Indexation (hors sprint, sprint 33)"]
    US209["US-209 Dépôts de plugins (hors sprint, sprint 33)"]

    RETRO31 --> TECH01
    RETRO31 --> TECH02
    RETRO31 --> TECH03
    RETRO31 --> TECH04
    TECH01 --> TECH02
    TECH02 --> TECH04
    TECH03 --> TECH04
    TECH02 --> TECH07
    TECH02 --> TECH11
    US204 --> M205
    US204 --> M206
    US189 --> M205
    US189 --> M206
    US111 --> US205
    US170 --> US206
    M205 --> GO205
    M206 --> GO206
    GO205 --> US205
    GO206 --> US206
    TECH02 --> US205
    TECH02 --> US206
    TECH02 --> US062
    US062 --> PR
    US205 --> TECH12
    US206 --> TECH12
    TECH12 --> PR
    TECH11 --> PR
    TECH07 --> PR
    PR --> PROD
    PR --> PROOF
    US205 -.->|avant| US207
    US206 -.->|avant| US207
    US206 -.->|avant| US209
```

## Lecture

| Dépendance | Nature | Conséquence |
|------------|--------|-------------|
| T-TECH-32-01 et T-TECH-32-02 avant les tests des workers | Action 1 de la rétro du sprint 31 | `bin/check-disk` annonce les runs autorisés ; le disque est libéré par Thibaut ; **aucun test ni rendu local sous 5 Go** : les tâches de test et de rendu d'US-205, US-206 et US-062 sont 📅 « disque » |
| T-TECH-32-03 avant la première capture | Action 2 | `public/build` frais avant toute capture ou rendu ; aucune compilation du SCSS à part |
| T-TECH-32-04 après T-TECH-32-02 et 03 | Action 3 | Images produit locales avant la capture du panier ; négociable |
| T-205-04 avant T-205-05 à T-205-11 | Porte de Thibaut | **Aucun Twig de la page « Preuve technique » avant le GO de la maquette** ; tâches 📅 |
| T-206-04 avant T-206-05 à T-206-12 | Porte de Thibaut | **Aucun Twig de la page « Architecture » avant le GO de la maquette** ; tâches 📅 |
| T-205-05 avant T-205-06 ; T-206-05 avant T-206-06 | Test rouge d'abord | Commit de tests rouges seul, cité dans la PR |
| T-206-06 avant T-206-07 | Coquille puis sections | Le sommaire d'ancres et le schéma d'abord, les quatre autres sections ensuite |
| T-TECH-32-07 après T-TECH-32-02 | Reliquat du sprint 31 | Image reconstruite : demande de la place ; US-203 passe en done ensuite |
| T-TECH-32-11 avant le push de la PR | Action 1 (DoD) | Suite complète locale, ou attente explicite dite dans la PR |
| T-TECH-32-12 avant la PR | Règle 23 | Capture à 1280 px relue (rendu local, ou production si le disque l'interdit) |
| US-205 et US-206 avant US-207 ; US-206 avant US-209 | Décision de Thibaut du 2026-10-28 | Hors sprint (sprint 33) |

## Chemin critique

La file la plus longue est celle d'US-206 : T-206-01 (0,75 h) → T-206-03 (2 h, orchestrateur) → T-206-04 (Thibaut) → T-206-05 (1 h) → T-206-06 (2,25 h) → T-206-07 (1,75 h) → T-206-08 (0,5 h) → T-206-09 (0,75 h, disque) → T-206-10 (0,5 h, disque) → T-TECH-32-12 (0,5 h) → merge → T-206-11 (0,5 h) → T-206-12 (0,25 h), soit environ 11 h de Claude et une attente de Thibaut. La file d'US-205 suit : T-205-03 (1,25 h) → GO → T-205-05 à T-205-09 (3,75 h) → merge → T-205-10 et T-205-11 (0,75 h). US-062 (2 h, sans porte) avance dès le planning. Le disque est la contrainte commune : T-205-08, T-206-09, les captures et la suite complète ne démarrent pas sous 5 Go.
