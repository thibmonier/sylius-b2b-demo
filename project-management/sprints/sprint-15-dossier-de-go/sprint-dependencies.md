# Dépendances : Sprint 015 (Dossier de GO)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S14["Sprint 14 (terminé): bloquants d'ouverture"]
    US139["US-139 Plugins à jour (4h)"]
    US140["US-140 Hôtes de confiance (3,5h)"]
    US141["US-141 Nettoyage des caches (3,5h)"]
    US082["US-082 Durcissement (10h)"]
    US081["US-081 Chaîne d'approvisionnement (10h)"]
    US090["US-090 Paliers sur la fiche (10h)"]
    US091["US-091 Paliers catalogue et panier (6h)"]
    US089["US-089 Accueil connecté (10h)"]
    US078["US-078 SEO et vitesse (10h)"]
    US083["US-083 Licence MCP (2,5h)"]
    US085["US-085 Dossier de GO (4h)"]
    US052["US-052 Ouverture publique (OPS Thibaut)"]
    S14 --> US139
    S14 --> US140
    US139 -->|"composer.lock"| US082
    US082 -->|"Dockerfile, ci.yaml"| US081
    US090 --> US091
    US090 -->|"prix de la société"| US089
    US090 -->|"gabarit de la fiche"| US078
    US140 --> US085
    US141 -.->|"procédure disque"| US085
    US081 --> US085
    US082 --> US085
    US083 --> US085
    US078 --> US085
    US089 --> US085
    US091 --> US085
    US085 -->|"GO de Thibaut"| US052
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T13903["T-139-03 plugins montés"] --> T08206["T-082-06 api-platform, doctrine/orm"]
    T09001["T-090-01 spike D2 (groupe de clients)"] --> T09003["T-090-03 données de prix"]
    T09003 --> T09004["T-090-04 tableau sur la fiche"]
    T09004 --> T09005["T-090-05 prix négocié"]
    T09005 --> T09101["T-091-01 tests catalogue et panier"]
    T09005 --> T08904["T-089-04 promotions négociées"]
    T09004 --> T07804["T-078-04 images de la fiche"]
    T08207["T-082-07 fumée complète"] --> T08101["T-081-01 outil de mises à jour"]
    T14003["T-140-03 IaC corrigée"] --> T14006["T-140-06 application (Thibaut)"]
    T14006 --> T08502["T-085-02 revue de réouverture"]
    T08302["T-083-02 note de décision"] --> T08503["T-085-03 dossier de GO"]
    T08502 --> T08503
```

## Chemin critique

US-090 (T-090-01, T-090-03, T-090-04, T-090-05, 6,5 h) → US-091 (T-091-01, T-091-02, T-091-04, 4 h) en parallèle d'US-089 (T-089-04 à T-089-06, 4 h) → US-085 (T-085-03, T-085-04, 1,5 h). Environ 12 h en série ; le reste est parallélisable si les fichiers ne se recoupent pas. Les tâches de Thibaut (T-140-06, T-083-04, T-085-05) et la bascule d'US-052 sortent du chemin critique de développement.

## Ressources partagées

- `composer.json`, `composer.lock` : US-139 puis US-082 (T-082-06) ; un worker à la fois.
- Serveur web (en-têtes, fichiers servis) : US-082 ; `Dockerfile` et `.github/workflows/ci.yaml` : US-082 puis US-081.
- `.railway/railway.ts`, `docs/sous-domaine-demo.md`, `docs/deploiement-railway.md` : US-140 seulement.
- Gabarits de la fiche produit : US-090 puis US-078 ; liste, panier et devis : US-091 ; accueil : US-089 ; fixtures de prix et groupe de la société de démo : US-090 (T-090-03).
- `bin/`, `docs/tests-locaux.md` : US-141 (script et documentation de `bin/measure-mobile`).
- `docs/dossier-de-go.md` : US-085 seulement ; `docs/licence-plugin-mcp.md` : US-083 seulement.
- `.bmad/sprint-status.yaml` : orchestrateur seulement (écrivain unique).
- Comptes, GO et actions de Thibaut : T-140-06, T-083-04, T-085-05, T-052-03 à T-052-07.
