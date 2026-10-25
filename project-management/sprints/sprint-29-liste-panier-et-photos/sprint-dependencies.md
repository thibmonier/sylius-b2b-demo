# Dépendances : Sprint 029 (liste, panier et photos)

> Périmètre décidé par Thibaut le 2026-10-25 (13 points engagés). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO27["Rétro S27 : actions 1, 2, 5 (reportées)"]
    RETRO28["Rétro S28 : actions 1 à 4 (règles 18 à 20, registre)"]
    US186["US-186 home, US-189 fiche produit (livrées)"]
    US196["US-196 photos (livrée)"]
    US198["US-198 Liste des produits (5 pts, protégée)"]
    US200["US-200 Panier (5 pts, sacrifice n°2)"]
    US197A["US-197 tranche 1 : point focal (sacrifice n°1)"]
    US197B["US-197 tranche 2 : variantes de couleur (sacrifice n°1)"]
    TECH01["T-TECH-29-01 Un seul cache de rendu, seuil disque"]
    TECH02["T-TECH-29-02 bin/proof-rebase"]
    TECH03["T-TECH-29-03 LCP de la home"]
    M198["T-198-03 Maquette de la liste"]
    M200["T-200-03 Maquette du panier"]
    GO198["T-198-04 GO maquette liste (Thibaut)"]
    GO200["T-200-04 GO maquette panier (Thibaut)"]
    PLANCHE["T-197-05 Choix sur la planche et GO de téléchargement (Thibaut)"]
    AVANT198["T-198-02 LCP de la liste avant"]
    PROD["Vérification en production après déploiement de main"]
    RESET["Remise à zéro de 03:00 : photos visibles"]
    PROOF["config/demo/proof.json : bin/proof-rebase après chaque rebase"]

    RETRO27 --> TECH01
    RETRO27 --> TECH02
    RETRO27 --> TECH03
    RETRO28 --> TECH01
    US186 --> US198
    US186 --> US200
    TECH01 --> M198
    TECH01 --> M200
    M198 --> GO198
    M200 --> GO200
    GO198 --> US198
    GO200 --> US200
    AVANT198 --> PROD
    US196 --> US197A
    US197A --> US197B
    US197B --> PLANCHE
    PLANCHE --> US197B
    US197A -.->|composant de cadrage mergé avant T-198-06| US198
    US198 --> PROD
    US200 --> PROD
    US197B --> RESET
    RESET --> PROD
    TECH03 --> PROD
    TECH02 --> PROOF
    US198 --> PROOF
    US200 --> PROOF
    US197A --> PROOF
```

## Lecture

| Dépendance | Nature | Conséquence |
|------------|--------|-------------|
| T-TECH-29-01 avant T-198-03 et T-200-03 | Action 1 de la rétro du sprint 27 | Un seul cache de rendu par worker et seuil disque avant la première capture de maquette |
| T-198-04 avant T-198-05 | Porte de Thibaut | **Aucun Twig de la liste avant le GO de la maquette** ; tâches de code 📅 |
| T-200-04 avant T-200-05 | Porte de Thibaut | **Aucun Twig du panier avant le GO de la maquette** ; tâches de code 📅 |
| T-197-05 avant T-197-06 | Porte de Thibaut | **Aucun téléchargement avant le GO explicite** ; avant le GO, rien n'est enregistré dans le dépôt |
| T-197-03 avant T-198-06 | Composant de cadrage partagé | La carte d'US-198 réutilise le point focal ; sinon elle garde le cadrage actuel |
| T-198-02 avant T-198-06 | Mesure d'avant | LCP de la liste mesuré avant le premier gabarit, au même protocole que « après » |
| Remise à zéro de 03:00 avant T-197-09 et T-198-10 | Règle 16 (la base survit aux déploiements) | Les nouvelles photos ne sont visibles en production qu'après la remise à zéro suivante ; contrôle le lendemain du déploiement |
| T-TECH-29-02 avant le premier rebase | Action 2 | `bin/proof-rebase` ; PR fusionnées en série |
| Tranche 1 d'US-197 seule | Valeur autonome | Le point focal sert sans les variantes de couleur ; la tranche 2 est sacrifiée avec la story |

## Chemin critique

La file la plus longue est celle d'US-198, puis celle d'US-200 : T-TECH-29-01 (1,25 h) → T-198-01 (0,75 h) → T-198-03 (2 h) → T-198-04 (Thibaut) → T-198-05 (1 h) → T-198-06 (3 h) → T-198-07 (0,5 h) → T-198-08 (0,75 h) → T-198-09 (0,5 h) → merge → T-198-10 (0,75 h) → T-198-11 (0,5 h). Environ 11 h de Claude et une attente de Thibaut. La file d'US-200 est du même ordre (10,25 h) et se déroule après celle d'US-198 si un seul worker de code est disponible ; les deux maquettes sont faites l'une après l'autre par l'orchestrateur (4 h), pendant que US-197 tranche 1 avance. US-197 tranche 2 attend le GO de la planche sur son propre chemin.
