# Dépendances : Sprint 028 (noms, photos et import ERP)

> Périmètre validé par Thibaut le 2026-10-24 (13 points engagés). Les dépendances ci-dessous portent sur les stories ; le détail des tâches est dans chaque fichier `tasks/US-XXX-tasks.md`. Sources : [sprint-goal.md](./sprint-goal.md), [tasks/README.md](./tasks/README.md).

```mermaid
graph TD
    RETRO27["Rétro du sprint 27 : actions 1 à 5"]
    ADR17["ADR 0017 : story B (contrat prix et stock)"]
    US185["US-185, US-188, US-193 (livrées) : export, simulateur, page"]
    US195["US-195 Noms sans marque (2 pts, protégée)"]
    US196["US-196 Photos sans marque (3 pts, sacrifice n°2)"]
    US187A["US-187 tranche A : plugin (5 pts)"]
    US187B["US-187 tranche B : démonstration (3 pts, sacrifice n°1)"]
    Q["T-187-02 Questions 3, 4 et 6 (Thibaut)"]
    PLANCHE["T-196-04 Choix sur la planche et GO de téléchargement (Thibaut)"]
    GOPLUGIN["T-187-10 GO push, tag, release du plugin (Thibaut)"]
    TAG["T-187-11 Tag v0.2.0 et T-187-12 Packagist"]
    TECH01["T-TECH-28-01 Un seul cache de rendu, seuil disque"]
    TECH02["T-TECH-28-02 bin/proof-rebase"]
    TECH05["T-TECH-28-05 LCP de la home remesuré"]
    PROD["Vérification en production après déploiement de main"]
    PROOF["config/demo/proof.json : bin/proof-rebase après chaque rebase"]

    RETRO27 --> TECH01
    RETRO27 --> TECH02
    RETRO27 --> TECH05
    ADR17 --> US187A
    US185 --> US187A
    Q --> US187A
    US187A --> GOPLUGIN
    GOPLUGIN --> TAG
    TAG --> US187B
    US185 --> US187B
    US195 --> US196
    US196 --> PLANCHE
    PLANCHE --> US196
    TECH01 --> US196
    US195 --> PROD
    US196 --> PROD
    US187B --> PROD
    TECH05 --> PROD
    TECH02 --> PROOF
    US195 --> PROOF
    US196 --> PROOF
    US187B --> PROOF
```

## Lecture

| Dépendance | Nature | Conséquence |
|------------|--------|-------------|
| T-195-03 avant T-196-03 | Noms affichés sur la planche | US-195 d'abord ; la planche nomme les produits avec leurs nouveaux noms |
| T-196-04 avant T-196-06 | Porte de Thibaut | **Aucun téléchargement avant le GO explicite** ; avant le GO, rien n'est enregistré dans le dépôt |
| T-187-02 avant T-187-03 | Questions ouvertes 3, 4 et 6 de l'ADR | L'affinage de la fiche attend les réponses (propositions fournies) |
| T-187-10 avant T-187-11 | Porte de Thibaut | **Aucun push, tag ni release du plugin sans GO** ; tâches 📅 |
| T-187-12 avant T-187-14 | Version publiée | `composer.json` et `composer.lock` de la démo ne sont commités qu'une fois `v0.2.0` visible sur Packagist ; le développement de la tranche B avance avant sur une copie locale non commitée |
| Tranche A seule | Valeur autonome | Le plugin publié sert sans la tranche B ; la tranche B est sacrifiée la première |
| T-TECH-28-01 avant le premier rendu d'US-196 | Action 1 | Un seul cache de rendu par worker, seuil disque appliqué aux rendus |
| T-TECH-28-02 avant le premier rebase | Action 2 | `bin/proof-rebase` ; PR fusionnées en série |
| Vérification en production après le merge | Action 5 | Mesures consignées le jour du déploiement ; la page du back-office d'US-187 est vérifiée en local |

## Chemin critique

La file la plus longue est celle d'US-187 : T-187-01 (0,5 h) → T-187-02 (Thibaut) → T-187-03 (0,5 h) → T-187-04 (1,75 h) → T-187-05 (2 h) → T-187-07 (2 h) → T-187-08 (0,75 h) → T-187-09 (0,5 h) → T-187-10 (Thibaut) → T-187-11 (0,5 h) → T-187-12 (Thibaut) → T-187-14 (0,25 h) → T-187-16 (1 h) → T-187-17 (1,25 h) → T-187-19 (0,75 h) → merge → T-187-20 (0,5 h) → T-187-21 (0,5 h). Environ 13,25 h de Claude et trois attentes de Thibaut. US-195 puis US-196 (attente de la planche) se déroulent en parallèle.
