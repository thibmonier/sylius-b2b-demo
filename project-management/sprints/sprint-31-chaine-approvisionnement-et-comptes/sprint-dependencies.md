# Dépendances : Sprint 031 (chaîne d'approvisionnement, comptes des plugins et page Plugins)

> Périmètre décidé par Thibaut le 2026-10-27 (13 points engagés). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO30["Rétro S30 : actions 1 à 5"]
    US081["US-081 Dependabot, SHA des actions, SBOM, audit (livrée)"]
    US199["US-199 Mon compte (livrée)"]
    US189["US-186, US-189, US-198 : charte de la home (livrées)"]
    TECH01["T-TECH-31-01 Parallélisme réglé"]
    TECH02["T-TECH-31-02 Suite complète locale avant le push"]
    TECH03["T-TECH-31-03 Cohérence HT et TTC"]
    TECH04["T-TECH-31-04 render-page : vignettes relatives, memory_limit"]
    TECH09["T-TECH-31-09 Capture 1280 px relue avant la PR"]
    US208["US-208 Chaîne d'approvisionnement (5 pts, protégée)"]
    US203["US-203 Comptes des plugins (5 pts, sacrifice n°2)"]
    US204["US-204 Page Plugins (3 pts, sacrifice n°1)"]
    M203["T-203-03 Planche du relevé"]
    M204["T-204-03 Maquette Plugins"]
    GO203["T-203-04 GO planche (Thibaut)"]
    GO204["T-204-04 GO maquette (Thibaut)"]
    DRIFT["T-203-05 Test de dérive des templates"]
    REQ["T-208-11 Zizmor requis sur main (Thibaut)"]
    WISH["T-208-04 Wishlist stable : composer.lock"]
    INST["composer install dans l'arbre partagé (orchestrateur, après la fusion)"]
    PR["PR du sprint"]
    PROD["Vérification en production après déploiement de main"]
    PARALLEL["Vérifications de production S28 à S30 (PR séparée, hors budget)"]
    PROOF["config/demo/proof.json : bin/proof-rebase"]
    US207["US-207 Indexation (hors sprint, après US-204 à US-206)"]
    US209["US-209 Dépôts de plugins (hors sprint, après US-208)"]

    RETRO30 --> TECH01
    RETRO30 --> TECH02
    RETRO30 --> TECH03
    RETRO30 --> TECH04
    TECH01 --> TECH02
    TECH04 --> M203
    TECH04 --> M204
    US081 --> US208
    US199 --> US203
    US189 --> US204
    US208 --> WISH
    US208 --> REQ
    WISH --> INST
    INST --> PR
    M203 --> GO203
    M204 --> GO204
    DRIFT --> US203
    GO203 --> US203
    GO204 --> US204
    TECH03 --> PR
    TECH02 --> PR
    TECH09 --> PR
    US208 --> PR
    US203 --> PR
    US204 --> PR
    PR --> PROD
    PR --> PROOF
    US208 -.->|après| US209
    US204 -.->|après| US207
    PARALLEL -.->|hors budget| PROD
```

## Lecture

| Dépendance | Nature | Conséquence |
|------------|--------|-------------|
| T-TECH-31-04 avant T-203-03 et T-204-03 | Action 4 de la rétro du sprint 30 | Vignettes réelles (URL relatives comprises) avant la première capture ou maquette |
| T-TECH-31-01 avant le lancement des workers | Action 1 | Parallélisme réglé : au plus 2 workers qui lancent des tests au-delà de 2 Go de swap ou sous 7 Go de disque |
| T-203-04 avant T-203-09 et T-203-10 | Porte de Thibaut | **Aucun Twig du relevé avant le GO de la planche** ; le reste d'US-203 (devis, assistants IA, consentement) n'attend pas |
| T-204-04 avant T-204-05 | Porte de Thibaut | **Aucun Twig de la page « Plugins » avant le GO de la maquette** ; tâches 📅 |
| T-203-05 avant T-203-07 | Garde | Le test de dérive est posé avant les surcharges |
| US-208 avant l'intégration des autres stories | Fichiers communs | `composer.lock` et workflows : intégrés d'abord ; `composer install` dans l'arbre partagé **seulement après la fusion** |
| T-208-06 avant T-208-11 | Porte de Thibaut | Le job zizmor est requis sur `main` une fois vert |
| T-TECH-31-02 avant le push de la PR | Action 2 | Suite complète locale, ou attente explicite dite dans la PR |
| US-208 avant US-209 ; US-204 à US-206 avant US-207 | Décision de Thibaut du 2026-10-27 | Hors sprint |
| Vérifications de production S28 à S30 | Registre du 2026-10-27 | Parallèles, PR séparée, hors budget |

## Chemin critique

La file la plus longue est celle d'US-203 : T-TECH-31-04 (0,75 h) → T-203-03 (1,25 h) → T-203-04 (Thibaut) → T-203-09 (0,25 h) → T-203-10 (1,25 h) → T-203-12 (0,75 h) → T-203-13 (0,5 h) → T-TECH-31-09 (0,5 h) → merge → T-203-14 (0,5 h) → T-203-15 (0,25 h), soit environ 6 h de Claude et une attente de Thibaut ; elle est parallèle aux surcharges des devis et des assistants IA (T-203-05 à T-203-08, environ 4 h). La file d'US-204 suit : T-204-03 (1,25 h) → GO → T-204-05 à T-204-09 (3,75 h). US-208 (9,25 h de Claude, sans porte hormis T-208-11) est le travail le plus long en heures et avance dès le planning.
