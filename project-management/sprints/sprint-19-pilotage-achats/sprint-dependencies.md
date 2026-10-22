# Dépendances : Sprint 019 (pilotage des achats)

```mermaid
graph TD
    S18["Sprint 18 : rétro, revue, US-103"]
    T10303["T-103-03 conversation réelle (Thibaut)"]
    US149["US-149 Squelette company-insights (5 pts)"]
    US150["US-150 Achats par mois (5 pts)"]
    US151["US-151 Tops (5 pts)"]
    US155["US-155 Historique et intégration à la démo (5 pts)"]
    US121["US-121 Kit média 2/2 (5 pts)"]
    US146["US-146 Adresses en création de commande (5 pts)"]
    US099["US-099 Simulateur PunchOut (3 pts)"]
    PKG["Soumission à Packagist (Thibaut)"]
    RW["Railway en incident"]
    S18 --> T10303
    T10303 --> US121
    US149 --> US150
    US149 --> US151
    US150 --> US155
    US151 --> US155
    US155 --> PKG
    US155 -.->|"redéploiement de la démo"| RW
```

## Ressources partagées

- Un seul worker à la fois ; disque d'environ 7 Go libres.
- US-146 dans le dépôt `sylius-admin-order-creation-plugin`, indépendante des autres stories.
- US-099 touche le simulateur de `punchout-cxml` et la démo : ne pas la mener en même temps qu'US-155 (fichiers de la démo).

## Chemin critique

US-149 → US-150 et US-151 → US-155 (20 points, dans cet ordre). US-146 et US-099 se placent entre ces étapes ; US-121 attend T-103-03.
