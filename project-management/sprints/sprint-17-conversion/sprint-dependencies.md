# Dépendances : Sprint 017 (conversion)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S16["Sprint 16 (démo crédible) : US-097, US-143, US-142"]
    TECH["Actions rétro S16 (T-TECH-01 à 08, sans points)"]
    US116["US-116 Charte et UTM (5,5 h)"]
    US129["US-129 Profil LinkedIn (5,5 h)"]
    US122["US-122 Calendrier (5 h)"]
    US123["US-123 Posts lot 1 (9,5 h)"]
    US127["US-127 Suivi de l'annonce (7 h)"]
    US117["US-117 Mesure par campagne (6,5 h)"]
    US120["US-120 Kit média 1/2 (10,5 h)"]
    US105["US-105 Moteur de parcours (18,5 h)"]
    US106["US-106 Contenu des parcours (11 h)"]
    US107["US-107 Page Intégrer (11,5 h)"]
    S16 --> US117
    S16 --> US120
    S16 --> US105
    TECH -->|"verrou, isolation"| US105
    US116 --> US129
    US116 --> US122
    US116 --> US127
    US116 --> US117
    US116 -->|"contrôle de charte"| US107
    US129 -->|"profil à jour avant le post"| US123
    US122 --> US123
    US120 -->|"posts 2 et 3"| US123
    US117 -->|"lien de test Umami"| US127
    US105 --> US106
    US105 -->|"événement de lancement"| US117
    US107 -->|"route de la page Intégrer"| US106
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T11605["T-116-05 contrôle de charte"] --> T12905["T-129-05 contrôle du profil"]
    T11602["T-116-02 conventions UTM"] --> T11701["T-117-01 spike Umami"]
    T11701 --> T11705["T-117-05 lien de test (Thibaut)"]
    T11705 --> T12704["T-127-04 chiffres J+7 (Thibaut)"]
    T12907["T-129-07 profil à jour (Thibaut)"] --> T12304["T-123-04 post 1 publié (Thibaut)"]
    T12003["T-120-03 à 05 captations"] --> T12006["T-120-06 export"]
    T12006 --> T12305["T-123-05 et 06 posts 2 et 3"]
    T10503["T-105-03 catalogue"] --> T10601["T-106-01 trame des parcours"]
    T10506["T-105-06 contrôleur"] --> T11703["T-117-03 propriété campagne"]
    T10704["T-107-04 route Intégrer"] --> T10604["T-106-04 écran de sortie"]
    TECH02["T-TECH-02 verrou"] --> TECH04["T-TECH-04 isolation des tests"]
    TECH04 --> T10505["T-105-05 tests fonctionnels"]
```

## Chemin critique

Deux chemins. **Calendaire (2026-10-26)** : T-116-01 → T-116-05 (5,5 h) → T-129 (profil, 4 h de Claude + 1,5 h de Thibaut) → T-123-01 à T-123-04 (post 1) ; T-117-01 → T-117-05 → T-127-04 → T-127-05 (bilan J+7). **Développement** : T-TECH-01 à T-TECH-04 (5 h) → US-105 (≈ 18,5 h) → US-106 (≈ 11 h) → T-117-03 : environ 36 h en série pour la piste parcours ; la piste « Intégrer » (US-107, 11,5 h) et la piste kit média (US-120, 10,5 h, avant les posts 2 et 3) sont parallèles. Les actions de Thibaut sortent du chemin critique de développement mais pas du chemin calendaire.

## Ressources partagées

- Gabarits du thème (pied de page, page Plugins, barre de démo) et traductions communes : US-105/106 (domaine `journeys`) et US-107 (domaine `integrate`) ; un seul worker à la fois sur un fichier commun.
- `config/demo/journeys.yaml` : US-105 puis US-106. `config/demo/charter_forbidden_terms.yaml` et `src/Demo/Check/EditorialCharterCheck.php` : US-116 puis extension par US-123.
- `src/Demo/Site/ContactCta.php` : US-107 (placement `integrate`) et US-106 (écran de sortie) ; `src/Demo/Analytics/UmamiEvent.php` : US-105/106/117 en lecture, US-117 seule en écriture.
- `bin/run-tests`, `tests/Smoke/LiveDemoDataSmokeTest.php` : actions de la rétro seulement (T-TECH), puis lecture par tous.
- Fixtures et reset : lecture seule ; la captation d'US-120 remet la démo locale à zéro (un worker à la fois sur la base locale).
- `../communication/` : US-116 (charte), US-129, US-122, US-123, US-127, US-120 (un dossier par story, aucun fichier partagé sauf le calendrier lu par US-123 et mis à jour par US-127).
- `.bmad/sprint-status.yaml` : orchestrateur seulement (écrivain unique).
- Actions de Thibaut : publications, mise à jour du profil, relevés LinkedIn et Umami, validation des délais, relecture des textes.
