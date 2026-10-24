# Dépendances : Sprint 027 (fiche produit et ERP fictif)

> Périmètre validé par Thibaut le 2026-10-24 (13 points engagés). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO26["Rétro du sprint 26 : actions 1 à 5"]
    US186["US-186 Refonte de la home (livrée, sprint 26)"]
    US185["US-185 et US-190 Export CSV et page (livrées)"]
    US187["US-187 Import CSV de prix et de stock (backlog, hors sprint)"]
    US189["US-189 Fiche produit (5 pts)"]
    US193["US-193 Extrait réel du fichier plat (2 pts, sacrifice n°2)"]
    US188["US-188 ERP fictif, périmètre réduit (5 pts, sacrifice n°1)"]
    TECH03["T-TECH-27-03 check-disk 6 Go et swap"]
    TECH04["T-TECH-27-04 et 05 503 en mode prod local, documentation"]
    TECH06["T-TECH-27-06 garde de largeur des tables larges"]
    TECH07["T-TECH-27-07 LCP de la home remesuré"]
    MAQUETTE["T-189-03 Maquette Claude Design"]
    GOMAQ["T-189-04 GO de Thibaut sur la maquette"]
    ELEM["T-189-05 Éléments ou photos (Thibaut)"]
    FIXT["T-193-04 Fixtures de commandes et export au chargement"]
    PROD["Vérification en production après déploiement de main"]
    PROOF["config/demo/proof.json : rejeu après chaque rebase"]

    RETRO26 --> TECH03
    RETRO26 --> TECH04
    RETRO26 --> TECH06
    RETRO26 --> TECH07
    US186 --> US189
    TECH04 --> MAQUETTE
    MAQUETTE --> GOMAQ
    GOMAQ --> ELEM
    GOMAQ --> US189
    ELEM --> US189
    US185 --> US193
    US193 --> FIXT
    FIXT --> US188
    US185 --> US188
    US187 -.->|hors sprint| US188
    TECH03 --> US193
    TECH03 --> US188
    TECH03 --> US189
    US189 --> PROD
    US193 --> PROD
    US188 --> PROD
    US189 --> PROOF
    US193 --> PROOF
    US188 --> PROOF
    TECH06 --> PROD
    TECH07 --> PROD
```

## Lecture

| Dépendance | Nature | Conséquence |
|------------|--------|-------------|
| T-TECH-27-04 et 05 avant T-189-03 | Action 4 de la rétro du sprint 26 | La maquette et les captures de la fiche s'appuient sur un rendu du noyau en mode prod ; borné à 1 h, sinon script de captures |
| T-189-04 avant T-189-07 à T-189-13 | Porte de Thibaut | Aucun gabarit Twig avant la validation de la maquette ; tâches 📅 |
| T-189-05 avant T-189-06 et T-189-08 | Éléments de la maquette | Sans objet si la maquette n'en demande pas |
| T-193-04 avant T-188-07 | Fichiers partagés (`DemoResetCommand`, fixtures) | US-193 d'abord, US-188 reprend son résultat ; un seul worker sur ces fichiers à la fois |
| US-187 hors sprint | Périmètre | US-188 est réduite : pas d'import, accusé de réception simulé à la place du CSV de prix et de stock |
| T-TECH-27-03 avant le premier lot | Action 3 | `bin/check-disk` durci avant tout lot ; aucune suite complète sous 6 Go |
| Vérification en production après le merge | Action 5 | Mesures consignées le jour du déploiement ; US-189 reste en review jusque-là ; la page du back-office d'US-188 est vérifiée en local |

## Chemin critique

T-TECH-27-04 (1 h) → T-TECH-27-05 (0,5 h) → T-189-03 (2 h) → T-189-04 (Thibaut) → T-189-07 (1 h) → T-189-08 (2,75 h) → T-189-10 (0,75 h) → merge → T-189-12 (0,75 h, production) → T-189-13 (0,5 h). Les durées de travail s'enchaînent sur environ 9,25 h de Claude ; le temps d'attente de Thibaut n'est pas compté. US-193 puis US-188 se déroulent en parallèle de l'attente de la maquette.
