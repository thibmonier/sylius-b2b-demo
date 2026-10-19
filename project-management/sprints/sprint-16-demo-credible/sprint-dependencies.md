# Dépendances : Sprint 016 (Démo crédible)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S15["Sprint 15 (terminé): dossier de GO"]
    US145["US-145 Images persistantes (7h)"]
    US061["US-061 Crédits des images (2h)"]
    US092["US-092 Photos cohérentes (10,5h)"]
    US142["US-142 Me contacter (10,5h)"]
    US143["US-143 Umami (7h)"]
    US096["US-096 Données vivantes 1/2 (10h)"]
    US097["US-097 Données vivantes 2/2 (10h)"]
    GO["Dossier de GO (US-085) et US-052"]
    S15 --> US145
    S15 --> US142
    S15 --> US096
    US061 -->|"page Crédits"| US092
    US145 -->|"vérification sur la démo"| US092
    US142 -->|"événements des CTA"| US143
    US096 -->|"fixtures, reset"| US097
    US145 -->|"ligne 14, bloquant"| GO
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T14501["T-145-01 spike volume"] --> T14503["T-145-03 volume dans l'IaC"]
    T14503 --> T14508["T-145-08 application (Thibaut)"]
    T06103["T-061-03 page Crédits"] --> T09206["T-092-06 couverture des crédits"]
    T14203["T-142-03 URL et UTM"] --> T14205["T-142-05 emplacements"]
    T14205 --> T14304["T-143-04 événements"]
    T09605["T-096-05 approbation de Paul"] --> T09703["T-097-03 devis"]
    T09706["T-097-06 journal d'audit"] --> T09707["T-097-07 contrôle de données"]
    T14209["T-142-09 URL (Thibaut)"] -.->|"composants visibles"| T14305["T-143-05 rendu réel"]
```

## Chemin critique

US-142 (T-142-02, T-142-03, T-142-04, T-142-05, 7 h) → US-143 (T-143-02 à T-143-05, 4 h) : environ 11 h en série pour la piste thème ; les pistes images (US-145 puis US-061 → US-092, ≈ 20 h en série) et fixtures (US-096 → US-097, 20 h) sont les plus longues et ne partagent aucun fichier avec elle. Les tâches de Thibaut (T-145-08, T-142-09, T-143-08, T-092-10) sortent du chemin critique de développement.

## Ressources partagées

- `.railway/railway.ts`, `docker/Dockerfile`, entrée du conteneur : US-145 seulement.
- `docs/deploiement-railway.md` : US-145, puis US-142 (variables d'URL), puis US-143 (variables Umami) ; un worker à la fois. `docs/dossier-de-go.md` : US-145 seulement.
- Gabarits du thème (barre de démo, accueil, fiche, Plugins, pied de page, confirmations) : US-142 puis US-143 ; page Crédits et pied de page : US-061.
- `SecurityHeadersSubscriber`, `docs/durcissement-demo.md` : US-143 seulement.
- Manifeste d'images (`ImageManifest`), `bin/fetch-product-images`, `docs/images-produits.md` : US-092 (US-061 en lecture).
- Fixtures de commandes, paniers, devis, crédit, journal d'audit et `docs/remise-a-zero-demo.md` : US-096 puis US-097.
- `.bmad/sprint-status.yaml` : orchestrateur seulement (écrivain unique).
- Comptes, GO et actions de Thibaut : T-145-08, T-142-09, T-143-08, T-092-10.
