# Dépendances — Sprint 013 (Vitrine des plugins)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S12["Sprint 12 (terminé): US-053, US-054, US-055"]
    US052["US-052 reportée: ouverture publique (OPS utilisateur)"]
    US063["US-063 Classement Plus et charte (6,5h)"]
    US064["US-064 Bloc, outil, lot A (9,5h)"]
    US067["US-067 Lot B et livraison (8h)"]
    US065["US-065 Captures (8,5h)"]
    US066["US-066 Sous-domaine (5h)"]
    S12 --> US063
    US063 --> US064
    US063 --> US065
    US064 --> US067
    US067 -.->|"branches des dépôts"| US065
    US052 -.->|"runbook d'ouverture"| US066
    US064 -.->|"hors sprint"| US068["Backlog US-068: liens site et contact (URL inconnue)"]
    US066 -.->|"hors sprint"| US069["Backlog US-069: page Plugins vers le site"]
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T06301["T-063-01 inventaire"] --> T06303["T-063-03 classement"]
    T06302["T-063-02 sources Plus"] --> T06303
    T06303 --> T06304["T-063-04 charte"]
    T06304 --> T06305["T-063-05 vitrine-plugins.md"]
    T06305 --> T06306["T-063-06 revue"]
    T06401["T-064-01 option de livraison"] --> T06402["T-064-02 bloc standard"]
    T06304 --> T06402
    T06402 --> T06403["T-064-03 tests rouges"]
    T06403 --> T06404["T-064-04 bin/vitrine-readme"]
    T06404 --> T06405["T-064-05 lot A"]
    T06405 --> T06406["T-064-06 contrôle"]
    T06406 --> T06407["T-064-07 revue"]
    T06404 --> T06701["T-067-01 lot B"]
    T06701 --> T06702["T-067-02 --check x24"]
    T06405 --> T06702
    T06702 --> T06703["T-067-03 branches locales"]
    T06703 --> T06704["T-067-04 note de GO"]
    T06704 --> T06705["T-067-05 PR et merge (GO)"]
    T06703 --> T06706["T-067-06 revue"]
    T06305 --> T06501["T-065-01 audit images"]
    T06501 --> T06502["T-065-02 protocole"]
    T06503["T-065-03 démo locale"] --> T06504["T-065-04 captures"]
    T06502 --> T06504
    T06504 --> T06505["T-065-05 comblement"]
    T06504 --> T06506["T-065-06 insertion README"]
    T06703 --> T06506
    T06506 --> T06507["T-065-07 contrôle"]
    T06506 --> T06508["T-065-08 revue"]
    T06601["T-066-01 analyse hôtes"] --> T06602["T-066-02 tests"]
    T06602 --> T06603["T-066-03 railway.ts"]
    T06601 --> T06604["T-066-04 runbook"]
    T06604 --> T06605["T-066-05 CNAME (utilisateur)"]
    T06605 --> T06606["T-066-06 certificat"]
    T06603 --> T06607["T-066-07 revue"]
    T06604 --> T06607
```

## Chemin critique

US-063 → US-064 → US-067 : T-063-01 → T-063-03 → T-063-04 → T-064-02 → T-064-03 → T-064-04 → T-067-01 → T-067-02 → T-067-03 (≈ 15 h), puis T-065-06 → T-065-08 (1,5 h). US-065 (captures) et US-066 sont parallèles. T-067-05, T-066-05 et T-066-06 dépendent de l'utilisateur et peuvent sortir du sprint.

## Ressources partagées

- Les 24 dépôts `sylius-*-plugin` : US-064 (T-064-01 en lecture seule), US-067 (branches, `README*.md`) et US-065 (`docs/images/`, sections Screenshots) ; ordre sérialisé par dépôt : US-067 (T-067-03) avant T-065-06. `customer-asset-management` : bloc seulement.
- `bin/vitrine-readme` et ses tests (démo) : US-064 (T-064-03, T-064-04), consommés ensuite par US-067.
- `docs/vitrine-plugins.md` : US-063 (créé), US-065 (état des images).
- `compose.yaml` et la démo locale : US-065 (T-065-03, T-065-04) ; pas d'autre usage simultané des ports.
- `.railway/railway.ts`, `docs/ouverture-publique.md`, `docs/deploiement-railway.md`, `docs/mcp.md` : US-066 seulement ; ne pas modifier pendant la bascule d'US-052.
- `.bmad/sprint-status.yaml` : orchestrateur seulement (écrivain unique).
- Comptes, GO et nom du sous-domaine de l'utilisateur : T-067-05, T-066-05, T-052-03 à T-052-07.
