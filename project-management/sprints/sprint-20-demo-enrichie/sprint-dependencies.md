# Dépendances : Sprint 020 (démo enrichie)

> Périmètre validé par Thibaut le 2026-10-22 (30 points) ; US-094, US-100 et US-121 sont hors périmètre.

```mermaid
graph TD
    LOCK["Tâche technique : lock démo sur company-insights 0.1.1"]
    US149["US-149 (sprint 19, livrée)"]
    US152["US-152 Ventilation et export CSV (5 pts)"]
    US153["US-153 Approbations et encours (5 pts)"]
    DEC["Décision prise : administratrice approbatrice, approver_roles = approver et admin"]
    US156["US-156 Approbations dans l'en-tête (3 pts)"]
    US113["US-113 Accueil réorganisé (3 pts)"]
    US108["US-108 Page Intégrations (3 pts)"]
    US099["US-099 Simulateur PunchOut (3 pts)"]
    US093["US-093 Catalogue 150 références (8 pts) : sacrifiée, sprint 21"]
    PKGI["Tag 0.2.0 company-insights puis Packagist (Thibaut)"]
    PKGA["Release purchase-approval puis Packagist (Thibaut)"]
    RW["Railway en incident"]
    LOCK --> US152
    US149 --> US152
    US149 --> US153
    US152 --> PKGI
    US153 --> PKGI
    PKGI -.->|"bump du lock de la démo"| RW
    DEC --> US156
    US156 --> PKGA
    PKGA -.->|"bump du lock de la démo"| RW
```

## Ressources partagées

- Un seul worker à la fois ; disque d'environ 5,4 Go libres (`bin/run-tests` refuse sous 5 Go).
- US-152 et US-153 touchent le même dépôt (`sylius-company-insights-plugin`, 0.2.0) : les mener l'une après l'autre ; US-153 lit les plugins optionnels purchase-approval et trade-credit.
- US-156 dans le dépôt `sylius-purchase-approval-plugin`, indépendante des stories de company-insights.
- US-113 et US-108 touchent les fichiers de la démo (accueil, pages) : pas en parallèle (US-093, catalogue, sacrifiée et reportée au sprint 21).
- US-099 touche le simulateur de `punchout-cxml` et la démo : pas en même temps qu'US-113.

## Ordre de publication (action 2 de la rétro S19)

Pour US-152, US-153 et US-156 : tag du plugin, puis Packagist (Thibaut), puis vérification de la version publiée, puis bump du `composer.lock` de la démo ; la PR de la démo s'ouvre après cette vérification.

## Chemin critique

Lock company-insights 0.1.1 puis US-152 puis US-153 (10 points, même dépôt) puis tag 0.2.0 et bump de la démo. US-156 : décision prise le 2026-10-22 ; US-093 (8 points, ≈ 16,5 h) a été sacrifiée le 2026-10-22 et reportée au sprint 21.
